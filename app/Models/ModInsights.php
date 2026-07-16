<?php

namespace App\Models;

use CodeIgniter\Model;

class ModInsights extends Model
{
    private function extractAmount(string $body): float {
        if (preg_match('/ksh\.?\s*([\d,]+\.?\d{0,2})/i', $body, $m)) {
            return (float)str_replace(',', '', $m[1]);
        }
        return 0.0;
    }

    private function extractBalance(string $body): float {
        if (preg_match('/balance is ksh\.?\s*([\d,]+\.?\d{0,2})/i', $body, $m) || 
            preg_match('/new m-pesa balance is ksh\.?\s*([\d,]+\.?\d{0,2})/i', $body, $m)) {
            return (float)str_replace(',', '', $m[1]);
        }
        return -1.0; // Unknown
    }

    private function normalizeTimestamp($smsTime): int {
        if (is_numeric($smsTime) && $smsTime > 1000000000000) {
            return (int)($smsTime / 1000);
        }
        return is_numeric($smsTime) ? (int)$smsTime : strtotime((string)$smsTime);
    }

    /**
     * 2.3 Spending Trends (This month vs Last month outflow)
     */
    public function getSpendingTrends(?string $deviceToken = null): array {
        $now = time();
        $thisMonthStart = mktime(0, 0, 0, (int)date('m', $now), 1, (int)date('Y', $now));
        $lastMonthStart = mktime(0, 0, 0, (int)date('m', $now) - 1, 1, (int)date('Y', $now));
        
        $thisMonthOutflow = 0.0;
        $lastMonthOutflow = 0.0;
        
        $builder = $this->db->table('tbl_Sms s')
            ->select('s.*, sc.direction as cl_direction')
            ->join('tbl_Sms_Classification sc', 'sc.sms_id = s.id', 'left');
        if ($deviceToken) $builder->where('s.sms_owner', $deviceToken);
        $allSms = $builder->get()->getResult();
        
        foreach ($allSms as $sms) {
            $dir = strtolower($sms->cl_direction ?? '');
            if ($dir !== 'outgoing') continue;
            
            $ts = $this->normalizeTimestamp($sms->sms_time);
            $amount = $this->extractAmount(strtolower(base64_decode($sms->sms_body)));
            
            if ($ts >= $thisMonthStart) {
                $thisMonthOutflow += $amount;
            } elseif ($ts >= $lastMonthStart && $ts < $thisMonthStart) {
                $lastMonthOutflow += $amount;
            }
        }
        
        $diff = $thisMonthOutflow - $lastMonthOutflow;
        $percentage = $lastMonthOutflow > 0 ? ($diff / $lastMonthOutflow) * 100 : 0;
        
        return [
            'this_month' => $thisMonthOutflow,
            'last_month' => $lastMonthOutflow,
            'percentage' => round($percentage, 1),
            'trend'      => $diff > 0 ? 'up' : ($diff < 0 ? 'down' : 'flat')
        ];
    }

    /**
     * 2.4 Recurring Payments Detector
     */
    public function getRecurringPayments(?string $deviceToken = null): array {
        if (!$this->db->tableExists('tbl_Analyzed_Transactions')) return [];
        
        $sixMonthsAgo = date('Y-m-d H:i:s', strtotime('-6 months'));
        
        // Find transactions with same counterparty and same amount appearing 3+ times
        $db = \Config\Database::connect();
        
        $sql = "
            SELECT a.counterparty, a.amount, COUNT(*) as occurs, MAX(a.trans_date) as last_paid
            FROM tbl_Analyzed_Transactions a
            LEFT JOIN tbl_Sms s ON s.sms__id = a.orig_sms_id
            WHERE a.description IN ('Paybill', 'Till', 'Sent', 'Sent to LNM', 'Sent to Mobile')
              AND a.trans_date >= ?
              AND a.counterparty != 'Unknown'
        ";
        $params = [$sixMonthsAgo];
        
        if ($deviceToken) {
            $sql .= " AND s.sms_owner = ? ";
            $params[] = $deviceToken;
        }
        
        $sql .= "
            GROUP BY a.counterparty, a.amount
            HAVING occurs >= 3
            ORDER BY last_paid DESC
            LIMIT 10
        ";
        
        $query = $db->query($sql, $params);

        return $query->getResult();
    }

    /**
     * 3. Smart Alerts Engine
     */
    public function getSmartAlerts(?string $deviceToken = null): array {
        $alerts = [];
        $builder = $this->db->table('tbl_Sms s')
            ->select('s.*, sc.direction as cl_direction')
            ->join('tbl_Sms_Classification sc', 'sc.sms_id = s.id', 'left')
            ->orderBy('s.sms_time', 'DESC');
        if ($deviceToken) $builder->where('s.sms_owner', $deviceToken);
        $allSms = $builder->get()->getResult();
        
        if (empty($allSms)) return $alerts;
        
        // 1. Low Balance Warning
        $latestBal = -1.0;
        foreach ($allSms as $sms) {
            $bal = $this->extractBalance(strtolower(base64_decode($sms->sms_body)));
            if ($bal >= 0) {
                $latestBal = $bal;
                break;
            }
        }
        
        if ($latestBal >= 0 && $latestBal < 1000) {
            $alerts[] = [
                'type'    => 'low_balance',
                'level'   => $latestBal < 200 ? 'danger' : 'warning',
                'title'   => 'Low Balance Warning',
                'message' => 'Your current M-Pesa balance is low: Ksh ' . number_format($latestBal, 2)
            ];
        }

        // 2 & 3. Unusual Activity & Fuliza Index (Analyze last 60 days)
        $sixtyDaysAgo = strtotime('-60 days');
        $outflowAmounts = [];
        $totalOutflow = 0.0;
        $fulizaTaken = 0.0;
        
        $recentTransactions = [];
        $fortyEightHoursAgo = strtotime('-48 hours');

        foreach ($allSms as $sms) {
            $ts = $this->normalizeTimestamp($sms->sms_time);
            if ($ts < $sixtyDaysAgo) continue;
            
            $dir = strtolower($sms->cl_direction ?? '');
            $body = strtolower(base64_decode($sms->sms_body));
            $amount = $this->extractAmount($body);
            
            if ($dir === 'outgoing') {
                $outflowAmounts[] = $amount;
                $totalOutflow += $amount;
                
                if ($ts >= $fortyEightHoursAgo && $amount > 0) {
                    $recentTransactions[] = ['amount' => $amount, 'sms' => $sms];
                }
            }
            
            if (strpos($body, 'fuliza') !== false && strpos($body, 'taken') !== false) {
                $fulizaTaken += $amount;
            }
        }
        
        // Processing Unusual Activity
        if (count($outflowAmounts) > 10) {
            $avgOutflow = array_sum($outflowAmounts) / count($outflowAmounts);
            $anomalyThreshold = $avgOutflow * 4; // 4x average is unusual
            
            foreach ($recentTransactions as $rt) {
                if ($rt['amount'] > $anomalyThreshold && $rt['amount'] > 5000) {
                    $alerts[] = [
                        'type'    => 'unusual_activity',
                        'level'   => 'danger',
                        'title'   => 'Unusual Activity Detected',
                        'message' => 'Large transaction of Ksh ' . number_format($rt['amount'], 2) . ' detected recently. Average is Ksh ' . number_format($avgOutflow, 0) . '.'
                    ];
                    break; // Only show one anomaly alert
                }
            }
        }
        
        // Processing Fuliza Dependency Index
        if ($totalOutflow > 0) {
            $fulizaRatio = ($fulizaTaken / $totalOutflow) * 100;
            if ($fulizaRatio > 25) {
                $alerts[] = [
                    'type'    => 'fuliza_index',
                    'level'   => $fulizaRatio > 50 ? 'danger' : 'warning',
                    'title'   => 'High Fuliza Dependency',
                    'message' => round($fulizaRatio, 1) . '% of your spending in the last 60 days came from Fuliza (Ksh ' . number_format($fulizaTaken, 0) . ').'
                ];
            }
        }

        return $alerts;
    }

    /**
     * AI Observations — Real computed insights for the Analytics page
     */
    public function getAIObservations(?string $deviceToken = null): array {
        $observations = [];

        $builder = $this->db->table('tbl_Sms s')
            ->select('s.*, sc.direction as cl_direction')
            ->join('tbl_Sms_Classification sc', 'sc.sms_id = s.id', 'left')
            ->orderBy('s.sms_time', 'DESC');
        if ($deviceToken) $builder->where('s.sms_owner', $deviceToken);
        $allSms = $builder->get()->getResult();

        if (empty($allSms)) {
            return [
                ['type' => 'neutral', 'label' => 'NO DATA YET', 'icon' => 'fa-circle-info',
                 'text' => 'No transactions have been synced yet. Upload data from your Android device to get personalized insights.'],
            ];
        }

        // --- 1. Peak Spending Day of the Week ---
        $dayTotals = array_fill(0, 7, 0); // 0=Sun, 1=Mon ... 6=Sat
        $dayNames  = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $sixtyDaysAgo = strtotime('-60 days');

        foreach ($allSms as $sms) {
            $ts = $this->normalizeTimestamp($sms->sms_time);
            if ($ts < $sixtyDaysAgo) continue;
            $dir = strtolower($sms->cl_direction ?? '');
            if ($dir !== 'outgoing') continue;
            $amount = $this->extractAmount(strtolower(base64_decode($sms->sms_body)));
            $dow = (int) date('w', $ts); // 0 = Sunday
            $dayTotals[$dow] += $amount;
        }

        $peakDayIndex = array_search(max($dayTotals), $dayTotals);
        $peakDayTotal = max($dayTotals);
        if ($peakDayTotal > 0) {
            $observations[] = [
                'type'  => 'warning',
                'label' => 'PEAK SPENDING DAY',
                'icon'  => 'fa-calendar-day',
                'text'  => 'You spend the most on <strong>' . $dayNames[$peakDayIndex] . 's</strong>. '
                         . 'Total outflow on ' . $dayNames[$peakDayIndex] . 's over the last 60 days: '
                         . '<strong>Ksh ' . number_format($peakDayTotal, 0) . '</strong>. '
                         . 'Consider setting a ' . $dayNames[$peakDayIndex] . ' budget.'
            ];
        }

        // --- 2. Month-over-Month Spending Trend ---
        $trends = $this->getSpendingTrends($deviceToken);
        if ($trends['last_month'] > 0) {
            $pct = abs($trends['percentage']);
            if ($trends['trend'] === 'down') {
                $observations[] = [
                    'type'  => 'success',
                    'label' => 'SAVINGS OPPORTUNITY',
                    'icon'  => 'fa-arrow-trend-down',
                    'text'  => 'Great news! Your spending this month is <strong>' . number_format($pct, 1) . '% lower</strong> '
                             . 'than last month (Ksh ' . number_format($trends['last_month'], 0) . ' → '
                             . 'Ksh ' . number_format($trends['this_month'], 0) . '). Keep it up!'
                ];
            } elseif ($trends['trend'] === 'up') {
                $observations[] = [
                    'type'  => 'danger',
                    'label' => 'SPENDING INCREASE',
                    'icon'  => 'fa-arrow-trend-up',
                    'text'  => 'Your spending this month is <strong>' . number_format($pct, 1) . '% higher</strong> '
                             . 'than last month (Ksh ' . number_format($trends['last_month'], 0) . ' → '
                             . 'Ksh ' . number_format($trends['this_month'], 0) . '). Review your outgoings.'
                ];
            } else {
                $observations[] = [
                    'type'  => 'neutral',
                    'label' => 'SPENDING STABLE',
                    'icon'  => 'fa-equals',
                    'text'  => 'Your spending is consistent with last month at around Ksh ' . number_format($trends['this_month'], 0) . '.'
                ];
            }
        }

        // --- 3. Fuliza Dependency ---
        $totalOutflow = 0.0;
        $fulizaTaken  = 0.0;
        foreach ($allSms as $sms) {
            $ts = $this->normalizeTimestamp($sms->sms_time);
            if ($ts < $sixtyDaysAgo) continue;
            $dir    = strtolower($sms->cl_direction ?? '');
            $body   = strtolower(base64_decode($sms->sms_body));
            $amount = $this->extractAmount($body);
            if ($dir === 'outgoing') $totalOutflow += $amount;
            if (strpos($body, 'fuliza') !== false && strpos($body, 'taken') !== false) $fulizaTaken += $amount;
        }

        if ($totalOutflow > 0) {
            $fulizaRatio = ($fulizaTaken / $totalOutflow) * 100;
            if ($fulizaRatio > 0) {
                $severity = $fulizaRatio > 40 ? 'danger' : ($fulizaRatio > 15 ? 'warning' : 'success');
                $assessment = $fulizaRatio > 40 ? 'Heavy reliance — consider reducing Fuliza usage to avoid compounding debt.'
                    : ($fulizaRatio > 15 ? 'Moderate usage — monitor closely to keep debt in check.'
                    : 'Low usage — you are managing Fuliza responsibly.');
                $observations[] = [
                    'type'  => $severity,
                    'label' => 'FULIZA USAGE',
                    'icon'  => 'fa-percent',
                    'text'  => '<strong>' . number_format($fulizaRatio, 1) . '%</strong> of your last 60-day outflow '
                             . '(Ksh ' . number_format($fulizaTaken, 0) . ') came from Fuliza. ' . $assessment
                ];
            } else {
                $observations[] = [
                    'type'  => 'success',
                    'label' => 'FULIZA USAGE',
                    'icon'  => 'fa-circle-check',
                    'text'  => 'No Fuliza usage detected in the last 60 days. Excellent financial discipline!'
                ];
            }
        }

        return $observations;
    }

    /**
     * 4. AI Financial Health Score (0-100)
     */
    public function getFinancialHealthScore(?string $deviceToken = null): array {
        $builder = $this->db->table('tbl_Sms s')
            ->select('s.*, sc.direction as cl_direction')
            ->join('tbl_Sms_Classification sc', 'sc.sms_id = s.id', 'left')
            ->orderBy('s.sms_time', 'DESC');
        if ($deviceToken) $builder->where('s.sms_owner', $deviceToken);
        $allSms = $builder->get()->getResult();
        
        $score = 100;
        $inflow = 0.0;
        $outflow = 0.0;
        $fuliza = 0.0;

        $sixtyDaysAgo = strtotime('-60 days');

        foreach ($allSms as $sms) {
            $ts = $this->normalizeTimestamp($sms->sms_time);
            if ($ts < $sixtyDaysAgo) continue;

            $dir   = strtolower($sms->cl_direction ?? '');
            $body  = strtolower(base64_decode($sms->sms_body));
            $amount = $this->extractAmount($body);

            if ($dir === 'outgoing') {
                $outflow += $amount;
            } elseif ($dir === 'incoming') {
                $inflow += $amount;
            }
            
            if (strpos($body, 'fuliza') !== false && strpos($body, 'taken') !== false) {
                $fuliza += $amount;
            }
        }

        $tips = [];

        // 1. Savings Ratio (40 points)
        if ($outflow > $inflow) {
            $ratio = $inflow == 0 ? 2 : ($outflow / $inflow);
            if ($ratio > 1.5) { $score -= 35; $tips[] = "Spending significantly exceeds income (High Risk)."; }
            elseif ($ratio > 1.1) { $score -= 20; $tips[] = "Spending slightly higher than income (Moderate Risk)."; }
            elseif ($ratio > 1.0) { $score -= 10; }
        } else {
            $tips[] = "Excellent savings ratio (Inflow > Outflow).";
        }

        // 2. Fuliza Dependency (40 points)
        if ($outflow > 0) {
            $fulizaRatio = $fuliza / $outflow;
            if ($fulizaRatio > 0.4) { $score -= 40; $tips[] = "Heavy Fuliza reliance detected (>40% of spend)."; }
            elseif ($fulizaRatio > 0.2) { $score -= 20; $tips[] = "Moderate Fuliza usage (>20% of spend)."; }
            elseif ($fulizaRatio > 0.05) { $score -= 5; }
        } else {
            $tips[] = "Healthy independent spending (No/Low Fuliza).";
        }

        // 3. Activity Consistency (20 points)
        if ($outflow == 0 && $inflow == 0) {
            $score -= 20;
            $tips[] = "Not enough recent data to calculate a robust score.";
        }

        $score = max(0, min(100, $score));
        $color = $score >= 80 ? '#2ED573' : ($score >= 50 ? '#FFA502' : '#FF4757');

        return [
            'score' => $score,
            'color' => $color,
            'tips'  => $tips
        ];
    }
}
