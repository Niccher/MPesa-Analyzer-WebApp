<?php

namespace App\Controllers;

use App\Models\ModUser;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Controller;

class Testar extends BaseController
{
    use ResponseTrait;

    public function random(){
        $mod_user = new ModUser();
        return $this->respond([
            'status' => 1,
            'code' => random_string('alnum', 16),
            //'uuid_id' => $mod_user->user_get_id("KTQiZqm5PcNFdzgm"),
            'message' => "File Uploaded Successfully"
        ]);
    }

}
