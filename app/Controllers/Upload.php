<?php

namespace App\Controllers;

use App\Models\ModUploads;
use App\Models\ModUser;
use App\Models\ModCryption;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Files\File;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Controller;

class Upload extends BaseController
{
    use ResponseTrait;

    // Constants for response statuses
    private const STATUS_SUCCESS = 1;
    private const STATUS_ERROR = 0;
    private const STATUS_INVALID_REQUEST = 2;

    protected $db;

    public function __construct() {
        // Load the database service (CI4 way)
        $this->db = \Config\Database::connect();
    }

    /**
     * Upload and process SMS data file
     */
    public function upload(): ResponseInterface {
        helper('text');
        $modUpload = new ModUploads();
        $dated = time(); // Use Unix timestamp for BIGINT column in tbl_Loot
        $uuid = random_string('alnum', 16);
        $token = (string) $this->request->getPost('varToken');
        $devId = (string) $this->request->getPost('varDevId');
        
        // Enhanced logging for debugging
        log_message('info', "=== UPLOAD REQUEST START ===");
        log_message('info', 'Upload request: token=' . substr($token, 0, 8) . '... devId=' . $devId);
        log_message('debug', 'Request method: ' . $this->request->getMethod());
        log_message('debug', 'POST keys: ' . json_encode(array_keys($this->request->getPost())));
        log_message('debug', 'FILES keys: ' . json_encode(array_keys($_FILES ?? [])));

        // Validate request method
        if (!$this->request->is('post')) {
            log_message('warning', 'Upload: Method not allowed (not POST)');
            return $this->respond([
                'status' => self::STATUS_INVALID_REQUEST,
                'time' => $dated,
                'message' => 'Method not allowed'
            ], 405);
        }

        // Validate input
        $validation = $this->validate([
            'varToken' => 'required|string|min_length[8]|max_length[64]',
            'varDevId' => 'required|string|min_length[8]|max_length[64]',
            'varLoot' => 'uploaded[varLoot]|max_size[varLoot,51200]',
        ]);

        if (!$validation) {
            $errors = $this->validator->getErrors();
            log_message('error', 'Upload validation failed: ' . json_encode($errors));
            return $this->respond([
                'status' => self::STATUS_INVALID_REQUEST,
                'time' => $dated,
                'message' => 'Validation failed: ' . implode('; ', $errors),
                'errors' => $errors
            ], 422);
        }

        if ($devId === 'nullable' || $token === 'nullable') {
            log_message('error', 'Upload rejected: token or device not registered (nullable)');
            return $this->respond([
                'status' => self::STATUS_INVALID_REQUEST,
                'time' => $dated,
                'message' => 'Device not registered. Re-link the app with your token (device fingerprint required).'
            ], 422);
        }

        try {
            $file = $this->request->getFile('varLoot');
            
            if (!$file->isValid()) {
                $fileErrors = $file->getErrorString();
                log_message('error', 'Upload file invalid: ' . $fileErrors);
                throw new \RuntimeException('Invalid file upload: ' . $fileErrors);
            }
            
            log_message('info', 'Upload file received: name=' . $file->getClientName() . ' size=' . $file->getSize() . ' type=' . $file->getClientMimeType());
            
            $newName = $file->getRandomName();
            $uploadPath = WRITEPATH . 'uploads/txt_loot/';

            if (!is_dir($uploadPath)) {
                if (!mkdir($uploadPath, 0755, true)) {
                    log_message('error', 'Failed to create upload directory: ' . $uploadPath);
                    throw new \RuntimeException('Failed to create upload directory');
                }
                log_message('info', 'Created upload directory: ' . $uploadPath);
            }

            if (!$file->move($uploadPath, $newName)) {
                log_message('error', 'File move failed to ' . $uploadPath . $newName);
                throw new \RuntimeException('File move failed');
            }
            
            log_message('info', 'File moved successfully to: ' . $uploadPath . $newName);

            $data = [
                'loot_Name' => $newName,
                'loot_Owner' => $token,
                'loot_Uuid' => $uuid,
                'loot_Device' => $devId,
                'loot_Created' => $dated
            ];

            $this->db->transStart();

            if (!$modUpload->file_upload($data)) {
                log_message('error', 'Database insert failed for loot record');
                throw new \RuntimeException('Database insert failed for loot record');
            }

            log_message('info', 'Loot record inserted, starting SMS parse for uuid=' . $uuid);

            // Throws on decrypt/parse/summary failure — do not ignore result
            $parseResult = $modUpload->loot_parse_sms($uuid, $data['loot_Owner'], $data['loot_Device'], $dated);

            if (($parseResult['status'] ?? '') !== 'success') {
                log_message('error', 'SMS parse failed: ' . ($parseResult['message'] ?? 'unknown'));
                throw new \RuntimeException($parseResult['message'] ?? 'SMS parse failed');
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                log_message('error', 'Upload transaction failed');
                throw new \RuntimeException('Upload transaction failed');
            }

            log_message('info', 'Upload success uuid=' . $uuid . ' processed=' . ($parseResult['processed'] ?? 0));

            return $this->respond([
                'status' => self::STATUS_SUCCESS,
                'time' => $dated,
                'message' => 'File uploaded and processed successfully',
                'uuid' => $uuid,
                'processed' => $parseResult['processed'] ?? 0
            ], 200);

        } catch (\Throwable $e) {
            log_message('error', 'Upload Error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            return $this->respond([
                'status' => self::STATUS_ERROR,
                'time' => $dated,
                'message' => 'File processing failed: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Register or identify a device
     */
    public function device_print(): ResponseInterface {
        helper('text');
        $modUpload = new ModUploads();
        $dated = date('Y-m-d H:i:s');
        $uuid = random_string('alnum', 16);

        if (!$this->request->is('post')) {
            return $this->fail('Method not allowed', 405);
        }

        $fingerprint = (string) $this->request->getPost('device_Fingerprint');
        $model = (string) $this->request->getPost('device_Model');
        $brand = (string) $this->request->getPost('device_Brand');

        $deviceData = [
            'device_Device' => (string) $this->request->getPost('device_Device'),
            'device_Uuid' => $uuid,
            'device_Created_At' => $dated,
            'device_Product' => (string) $this->request->getPost('device_Product'),
            'device_Bootloader' => (string) $this->request->getPost('device_Bootloader'),
            'device_Type' => (string) $this->request->getPost('device_Type'),
            'device_Tags' => (string) $this->request->getPost('device_Tags'),
            'device_Host' => (string) $this->request->getPost('device_Host'),
            'device_Display' => (string) $this->request->getPost('device_Display'),
            'device_Hardware' => (string) $this->request->getPost('device_Hardware'),
            'device_Fingerprint' => $fingerprint,
            'device_Manufacturer' => (string) $this->request->getPost('device_Manufacturer'),
            'device_Brand' => $brand,
            'device_Board' => (string) $this->request->getPost('device_Board'),
            'device_User' => (string) $this->request->getPost('device_User'),
            'device_Model' => $model,
            'device_Time' => $this->parseLongInt($this->request->getPost('device_Time')),
            'device_Serial' => (string) $this->request->getPost('device_Serial')
        ];

        try {
            // Match on stable identity fields only (never include the freshly generated UUID)
            $existingDevice = $modUpload->device_find_by_fingerprint($fingerprint, $model, $brand);

            if (empty($existingDevice)) {
                if (!$modUpload->device_make_print($deviceData)) {
                    throw new \RuntimeException('Device registration failed');
                }
                $existingDevice = $modUpload->device_find_by_fingerprint($fingerprint, $model, $brand);
                $message = 'New device registered';
                log_message('info', 'New device registered print_id=' . ($existingDevice[0]->device_Uuid ?? ''));
            } else {
                $message = 'Existing device identified';
                log_message('info', 'Existing device print_id=' . $existingDevice[0]->device_Uuid);
            }

            if (empty($existingDevice)) {
                throw new \RuntimeException('Device registration succeeded but could not re-read print_id');
            }

            return $this->respond([
                'status' => self::STATUS_SUCCESS,
                'message' => $message,
                'print_id' => $existingDevice[0]->device_Uuid,
                'time' => $dated,
            ]);

        } catch (\Throwable $e) {
            log_message('error', 'Device Print Error: ' . $e->getMessage());
            return $this->respond([
                'status' => self::STATUS_ERROR,
                'time' => $dated,
                'message' => 'Device processing failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get list of uploads for a user/device
     */
    public function upload_listing(): ResponseInterface {
        $modUpload = new ModUploads();
        $dated = date('Y-m-d H:i:s');

        if (!$this->request->is('post')) {
            return $this->fail('Method not allowed', 405);
        }

        try {
            $ownerUuid = (string) $this->request->getPost('varUser');
            $deviceId = (string) $this->request->getPost('varDev');

            $uploads = $modUpload->file_listing($ownerUuid, $deviceId);
            $result = [];

            foreach ($uploads as $upload) {
                $summary = $modUpload->loot_summary($upload->loot_Uuid);
                
                // Convert loot_Created (BIGINT Unix timestamp) to formatted date string for Android
                $createdRaw = $upload->loot_Created;
                $summaryCreated = is_numeric($createdRaw) 
                    ? date('Y-m-d H:i:s', (int)$createdRaw) 
                    : $createdRaw;
                
                $result[] = [
                    'summary_Loot_Uuid' => $upload->loot_Uuid,
                    'summary_Created' => $summaryCreated,
                    'summary_Count' => $summary[0]->info_All ?? 0,
                    'summary_Received' => $summary[0]->info_Get_from_MPESA ?? 0,
                    'summary_Sent' => $summary[0]->info_Sent_to_MPESA ?? 0,
                    'summary_Unknown' => $summary[0]->info_Unknown ?? 0
                ];
            }

            return $this->respond([
                'status' => self::STATUS_SUCCESS,
                'time' => $dated,
                'summarizer' => $result
            ]);

        } catch (\Throwable $e) {
            log_message('error', 'Upload Listing Error: ' . $e->getMessage());
            return $this->respond([
                'status' => self::STATUS_ERROR,
                'time' => $dated,
                'message' => 'Failed to retrieve upload list',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get detailed SMS statistics last 3 uploads
     */
    public function upload_list_graph(){
        $mod_upload = new ModUploads();
        $mod_cryption = new ModCryption();
        $mod_user = new ModUser();

        $session = session();

        if (empty($session->get('user_name'))){
            #echo "Not Logged In";
        }

        if ($this->request->getPost()){
            $summ_owner_uuid = $this->request->getVar('varUser');
            $summ_device = $this->request->getVar('varDev');
            $summ_owner = $mod_user->user_get_id($summ_owner_uuid);

            $loot_array = array();
            $loot_list = $mod_upload->file_listing($summ_owner_uuid, $summ_device);
            echo '{ "summarizer":[';
            $counter = 0;
            $loot_size = count($loot_list);
            foreach ($loot_list as $loot_info) {
                $counter +=1;
                $loot_meta = $mod_upload->loot_summary($loot_info->loot_Uuid); //tbl_Loot_Summary
                $data = [
                    'summary_Loot_Uuid'  => $loot_info->loot_Uuid,
                    'summary_Created'  => $loot_info->loot_Created,
                    'summary_Count'  => $loot_meta[0]->info_All,
                    'summary_Received'  => $loot_meta[0]->info_Get_from_MPESA,
                    'summary_Sent'  => $loot_meta[0]->info_Sent_to_MPESA,
                    'summary_Unknown' => $loot_meta[0]->info_Unknown
                ];
                array_push($loot_array,$data);

                if ($counter == 4) {
                    print json_encode($data);
                    break; // Exit the loop after the third item
                } else {
                    print json_encode($data).",";
                }
            }
            echo "]}";
        }
    }

    /**
     * Get detailed SMS statistics for a specific upload
     */
    public function upload_summary_calculation(): ResponseInterface {
        $modUpload = new ModUploads();
        $dated = date('Y-m-d H:i:s');

        if (!$this->request->is('post')) {
            return $this->fail('Method not allowed', 405);
        }

        try {
            $lootUuid = (string) $this->request->getPost('varLootUuid');
            $summary = $modUpload->loot_summary($lootUuid);

            if (empty($summary)) {
                return $this->respond([
                    'status' => self::STATUS_ERROR,
                    'time' => $dated,
                    'message' => 'No data found for this upload'
                ]);
            }

            //////////////////////
            ///
            $response_data = ['status' => self::STATUS_SUCCESS,
                'time' => $dated,
                'loot_summarizer' => $this->formatSummaryData($summary[0])];

            $data = json_encode($response_data, JSON_PRETTY_PRINT);

            // Check if the 'loot_summarizer' key exists
            if (isset($data['loot_summarizer'])) {
                // Assign the 'loot_summarizer' subarray to be the new data
                // Encode the transformed array back into a JSON string
                $transformedJson = json_encode($data['loot_summarizer'], JSON_PRETTY_PRINT);

                print_r($transformedJson);
                echo gettype($transformedJson);
            }

            return $this->respond([
//                'status' => self::STATUS_SUCCESS,
//                'time' => $dated,
//                'loot_summarizer' => $this->formatSummaryData($summary[0]),
                'loot_summarizer' => $response_data['loot_summarizer']
            ]);

        } catch (\Throwable $e) {
            log_message('error', 'Summary Calculation Error: ' . $e->getMessage());
            return $this->respond([
                'status' => self::STATUS_ERROR,
                'time' => $dated,
                'message' => 'Failed to calculate summary',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Count uploads for a user/device
     */
    public function loot_uploaded_count(): ResponseInterface {
        $modUpload = new ModUploads();
        $dated = date('Y-m-d H:i:s');

        if (!$this->request->is('post')) {
            return $this->fail('Method not allowed', 405);
        }

        try {
            $ownerUuid = (string) $this->request->getPost('varUser');
            $deviceId = (string) $this->request->getPost('varDev');
            $uploads = $modUpload->file_listing($ownerUuid, $deviceId);

            return $this->respond([
                'msg_status' => self::STATUS_SUCCESS,
                'msg_count' => count($uploads),
                'msg_time' => $dated,
                'msg_state' => empty($uploads) ? 'empty' : 'found'
            ]);

        } catch (\Throwable $e) {
            log_message('error', 'Upload Count Error: ' . $e->getMessage());
            return $this->respond([
                'msg_status' => self::STATUS_ERROR,
                'msg_time' => $dated,
                'message' => 'Failed to count uploads',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get aggregated category counts across uploads
     */
    public function loot_uploaded_category_count(): ResponseInterface {
        $modUpload = new ModUploads();
        $dated = date('Y-m-d H:i:s');

        if (!$this->request->is('post')) {
            return $this->fail('Method not allowed', 405);
        }

        try {
            $ownerUuid = (string) $this->request->getPost('varUser');
            $deviceId = (string) $this->request->getPost('varDev');

            $uploads = $modUpload->file_listing($ownerUuid, $deviceId);
            $uuids = array_column($uploads, 'loot_Uuid');

            $summaries = $modUpload->get_loot_summary_from_uuids($uuids);
            $result = [];

            foreach ($summaries as $summary) {
                $result[] = $this->formatSummaryData($summary);
            }

            return $this->respond([
                'status' => self::STATUS_SUCCESS,
                'time' => $dated,
                'loot_summarizer' => ['loot_summarizer' => $result]
            ]);

        } catch (\Throwable $e) {
            log_message('error', 'Category Count Error: ' . $e->getMessage());
            return $this->respond([
                'status' => self::STATUS_ERROR,
                'time' => $dated,
                'message' => 'Failed to retrieve category counts',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Delete upload by UUID
     */
    public function loot_delete_by_uuid(): ResponseInterface {
        $modUpload = new ModUploads();
        $dated = date('Y-m-d H:i:s');

        if (!$this->request->is('post')) {
            return $this->fail('Method not allowed', 405);
        }

        try {
            $lootUuid = (string) $this->request->getPost('varLootUuid');
            // Implement actual deletion logic here
            // $success = $modUpload->deleteByUuid($lootUuid);

            return $this->respond([
                'status' => self::STATUS_SUCCESS,
                'time' => $dated,
                'message' => 'Deletion endpoint not yet implemented'
            ]);

        } catch (\Throwable $e) {
            log_message('error', 'Delete Error: ' . $e->getMessage());
            return $this->respond([
                'status' => self::STATUS_ERROR,
                'time' => $dated,
                'message' => 'Deletion failed',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Format summary data consistently
     */
    private function formatSummaryData(object $summary): array {
        return [
            'status' => 1,
            'count_Get_from_MPESA' => $summary->info_Get_from_MPESA,
            'count_Get_from_KCB' => $summary->info_Get_from_KCB,
            'count_Get_from_Mshwari' => $summary->info_Get_from_Mshwari,
            'count_Get_from_NCBA' => $summary->info_Get_from_NCBA,
            'count_Get_from_IM' => $summary->info_Get_from_IM,
            'count_Get_from_Reversal' => $summary->info_Get_from_Reversal,
            'count_Get_Bal_MPESA' => $summary->info_Get_Bal_MPESA,
            'count_Get_Bal_KCB' => $summary->info_Get_Bal_KCB,
            'count_Get_Bal_Mshwari' => $summary->info_Get_Bal_Mshwari,
            'count_Loan_Limit' => $summary->info_Loan_Limit,
            'count_Sent_to_MPESA' => $summary->info_Sent_to_MPESA,
            'count_Sent_to_Mshwari' => $summary->info_Sent_to_Mshwari,
            'count_Sent_to_LNM' => $summary->info_Sent_to_LNM,
            'count_Sent_Mini' => $summary->info_Sent_Mini,
            'count_Sent_Cancel' => $summary->info_Sent_Cancel,
            'count_Error_Failed' => $summary->info_Error_Failed,
            'count_Error_Pin' => $summary->info_Error_Pin,
            'count_Error_Less' => $summary->info_Error_Less,
            'count_Error_Receiver' => $summary->info_Error_Receiver,
            'count_Error_Receiver_Org' => $summary->info_Error_Receiver_Org,
            'count_Withdraw' => $summary->info_Withdraw,
            'count_Fuliza_Opt_Out' => $summary->info_Fuliza_Opt_Out,
            'count_Fuliza_Opt_In' => $summary->info_Fuliza_Opt_In,
            'count_Fuliza_Limit' => $summary->info_Fuliza_Limit,
            'count_Fuliza_Loan_Paid' => $summary->info_Fuliza_Loan_Paid,
            'count_Fuliza_Mini_Statement' => $summary->info_Fuliza_Mini_Statement,
            'count_Fuliza_Loan_Taken' => $summary->info_Fuliza_Loan_Taken,
            'count_Similar_Transaction' => $summary->info_Similar_Transaction,
            'count_All' => $summary->info_All,
            'count_Unknown' => $summary->info_Unknown,
            'loot_Created' => $summary->loot_Created,
            'loot_Uuid' => $summary->loot_Uuid,//
        ];
    }

    /**
     * Safely parse long integer from input
     */
    private function parseLongInt($value): int {
        $clean = preg_replace('/[^0-9]/', '', (string) $value);
        return is_numeric($clean) ? (int) $clean : time() * 1000;
    }
}