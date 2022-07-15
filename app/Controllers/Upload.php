<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Controller;

use App\Models\ModUser;


class Upload extends BaseController//\IonAuth\Controllers\Auth
{
    use ResponseTrait;

    public function upload(){
        $mod_user = new ModUser();
        $session = session();

        if (empty($session->get('user_name'))){
            return "Not Logged In";
        }
        
        if ($this->request->getPost()){

            $lg_eml = $this->request->getVar('varEmail');
            $lg_pwd = $this->request->getVar('varPassword');

            $data = $mod_user->where('user_Email', $lg_eml)->first();

            if($data){
                $pass = $data['user_Password'];
                $verify_pass = password_verify($lg_pwd, $pass);
                if($verify_pass){
                    $ses_data = [
                        'user_id'       => $data['user_Id'],
                        'user_name'     => $data['user_Name'],
                        'user_email'    => $data['user_Email'],
                        'logged_in'     => TRUE
                    ];
                    $session->set($ses_data);
                    return "Passed credentials";//redirect()->to('/home');
                }else{
                    $session->setFlashdata('msg', 'Wrong Password');
                    return "Wrong credentials";//redirect()->to('/login');
                }
            }else{
                $session->setFlashdata('msg', 'Email not Found');
                return "No such user";// redirect()->to('/login');
            }
        }else{
            //echo "Ordinary Get";
        }
    }
}
