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
    public function getSpendingTrends(): array {
        $now = time();
        $thisMonthStart = mktime(0, 0, 0, (int)date('m', $now), 1, (int)date('Y', $now));
        $lastMonthStart = mktime(0, 0, 0, (int)date('m', $now) - 1, 1, (int)date('Y', $now));
        
        $thisMonthOutflow = 0.0;
        $lastMonthOutflow = 0.0;
        
        $allSms = $this->db->table('tbl_Sms')->get()->getResult();
        
        foreach ($allSms as $sms) {
            $cat = strtolower($sms->sms_category);
            if (!in_array($cat, ['sent', 'sent to lnm', 'withdraw'])) continue;
            
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
    public function getRecurringPayments(): array {
        if (!$this->db->tableExists('tbl_Analyzed_Transactions')) return [];
        
        $sixMonthsAgo = date('Y-m-d H:i:s', strtotime('-6 months'));
        
        // Find transactions with same counterparty and same amount appearing 3+ times
        $db = \Config\Database::connect();
        $query = $db->query("
            SELECT counterparty, amount, COUNT(*) as occurs, MAX(trans_date) as last_paid
            FROM tbl_Analyzed_Transactions
            WHERE description IN ('Paybill', 'Till', 'Sent', 'Sent to LNM', 'Sent to Mobile')
              AND trans_date >= ?
              AND counterparty != 'Unknown'
            GROUP BY counterparty, amount
            HAVING occurs >= 3
            ORDER BY last_paid DESC
            LIMIT 10
        ", [$sixMonthsAgo]);

        return $query->getResult();
    }

    /**
     * 3. Smart Alerts Engine
     */
    public function getSmartAlerts(): array {
        $alerts = [];
        $allSms = $this->db->table('tbl_Sms')->orderBy('sms_time', 'DESC')->get()->getResult();
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
            
            $cat = strtolower($sms->sms_category);
            $amount = $this->extractAmount(strtolower(base64_decode($sms->sms_body)));
            
            if (in_array($cat, ['sent', 'sent to lnm', 'withdraw'])) {
                $outflowAmounts[] = $amount;
                $totalOutflow += $amount;
                
                if ($ts >= $fortyEightHoursAgo && $amount > 0) {
                    $recentTransactions[] = ['amount' => $amount, 'sms' => $sms];
                }
            } elseif ($cat === 'fuliza loan taken') {
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
}
