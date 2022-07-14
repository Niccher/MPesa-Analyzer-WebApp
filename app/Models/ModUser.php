<?php

namespace App\Models;

use CodeIgniter\API\ResponseTrait;

use CodeIgniter\Model;

class ModUser extends Model
{
    use ResponseTrait;
    protected $table = "tbl_users";

    public function user_register($data){
        return $this->db ->table('tbl_users')->insert($data);
    }

    public function user_info($userid){
        $builder = $this->db->table('tbl_users');
        $result = $builder->select('user_Name, user_Email')
                ->where('user_Id', $userid)
                ->get();
        return $result->getResult();
    }

    public function user_login($userid){
        $table = 'tbl_users';
    }
}
