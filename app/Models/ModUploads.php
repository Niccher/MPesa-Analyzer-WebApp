<?php

namespace App\Models;

use CodeIgniter\API\ResponseTrait;

use CodeIgniter\Model;

class ModUploads extends Model
{
    use ResponseTrait;
    protected $table = "tbl_Loot";

    public function file_upload($data){
        return $this->db->table('tbl_Loot')->insert($data);
    }

    public function file_info($userid){
        $builder = $this->db->table('tbl_users');
        $result = $builder->select('user_Name, user_Email')
            ->where('user_Id', $userid)
            ->get();
        return $result->getResult();
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
