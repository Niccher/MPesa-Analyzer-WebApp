<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;

class Home extends BaseController
{
    use ResponseTrait;
    public function index(){
        $session = session();

        if (empty($session->get('user_name'))){
            return "Not Logged In";
        }
        echo "<br>Welcome user_name back, ".$session->get('user_name');
        echo "<br>Welcome user_email back, ".$session->get('user_email');
        echo "<br>Welcome user_Id back, ".$session->get('user_id');
        //return view('welcome_message');
    }
}
