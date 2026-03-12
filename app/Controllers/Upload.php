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
        $modUpload = new ModUploads();
        $dated = date('Y-m-d H:i:s');
        $uuid = random_string('alnum', 16);

        // Validate request method
        if (!$this->request->is('post')) {
            return $this->respond([
                'status' => self::STATUS_INVALID_REQUEST,
                'time' => $dated,
                'message' => 'Method not allowed'
            ]);
        }

        // Validate input
        $validation = $this->validate([
            'varToken' => 'required|string|min_length[8]|max_length[64]',
            'varDevId' => 'required|string|min_length[8]|max_length[64]',
            'varLoot' => 'uploaded[varLoot]|max_size[varLoot,51200]',
        ]);

        if (!$validation) {
            return $this->respond([
                'status' => self::STATUS_INVALID_REQUEST,
                'time' => $dated,
                'message' => 'Validation failed',
                'errors' => $this->validator->getErrors()
            ]);
        }

        try {
            $file = $this->request->getFile('varLoot');
            $newName = $file->getRandomName();
            $uploadPath = WRITEPATH . 'uploads/txt_loot/';

            if (!$file->move($uploadPath, $newName)) {
                throw new \RuntimeException('File move failed');
            }

            $data = [
                'loot_Name' => $newName,
                'loot_Type' => $file->getClientMimeType(),
                'loot_Extension' => $file->getClientExtension(),
                'loot_Size' => $file->getSize(),
                'loot_Owner' => (string) $this->request->getPost('varToken'),
                'loot_Uuid' => $uuid,
                'loot_Device' => (string) $this->request->getPost('varDevId'),
                'loot_Created' => $dated
            ];

            $this->db->transStart();

            if (!$modUpload->file_upload($data)) {
                throw new \RuntimeException('Database insert failed');
            }

            $modUpload->loot_parse_sms($uuid, $data['loot_Owner'], $data['loot_Device'], $dated);

            $this->db->transComplete();

            return $this->respond([
                'status' => self::STATUS_SUCCESS,
                'time' => $dated,
                'message' => 'File uploaded and processed successfully',
                'uuid' => $uuid
            ]);

        } catch (\Throwable $e) {
            log_message('error', 'Upload Error: ' . $e->getMessage());
            return $this->respond([
                'status' => self::STATUS_ERROR,
                'time' => $dated,
                'message' => 'File processing failed',
                'error' => ENVIRONMENT === 'development' ? $e->getMessage() : null
            ]);
        }
    }

    /**
     * Register or identify a device
     */
    public function device_print(): ResponseInterface {
        $modUpload = new ModUploads();
        $dated = date('Y-m-d H:i:s');
        $uuid = random_string('alnum', 16);

        if (!$this->request->is('post')) {
            return $this->fail('Method not allowed', 405);
        }

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
            'device_Fingerprint' => (string) $this->request->getPost('device_Fingerprint'),
            'device_Manufacturer' => (string) $this->request->getPost('device_Manufacturer'),
            'device_Brand' => (string) $this->request->getPost('device_Brand'),
            'device_Board' => (string) $this->request->getPost('device_Board'),
            'device_User' => (string) $this->request->getPost('device_User'),
            'device_Model' => (string) $this->request->getPost('device_Model'),
            'device_Time' => $this->parseLongInt($this->request->getPost('device_Time')),
            'device_Serial' => (string) $this->request->getPost('device_Serial')
        ];

        try {
            $existingDevice = $modUpload->device_check_print($deviceData);

            if (empty($existingDevice)) {
                if (!$modUpload->device_make_print($deviceData)) {
                    throw new \RuntimeException('Device registration failed');
                }
                $existingDevice = $modUpload->device_check_print($deviceData);
                $message = 'New device registered';
            } else {
                $message = 'Existing device identified';
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
                'message' => 'Device processing failed'
            ]);
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
                $result[] = [
                    'summary_Loot_Uuid' => $upload->loot_Uuid,
                    'summary_Created' => $upload->loot_Created,
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