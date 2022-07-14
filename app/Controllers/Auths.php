<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Controller;

use App\Models\ModUser;


class Auths extends BaseController//\IonAuth\Controllers\Auth
{
    use ResponseTrait;

    public function login(){
        $mod_user = new ModUser();
        $session = session();
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
    public function register(){
        $mod_user = new ModUser();

        if ($this->request->getPost()){
            $data = [
                'user_Name' => $this->request->getVar('varUsername'),
                'user_Email' => $this->request->getVar('varEmail'),
                'user_Password' => password_hash($this->request->getVar('varPassword'), PASSWORD_DEFAULT),
                'user_Created' => time(),
            ];
            $pushed = $mod_user->user_register($data);

            if ($pushed){
                return $this->respond([
                    'status' => 1,
                    'message' => "User Added to Database"
                ]);
            }else{
                return $this->respond([
                    'status' => 0,
                    'message' => "User Not Added to Database"
                ]);
            }
        }else{
            //echo "Ordinary Get";
        }
    }

    public function user_info(){
        $mod_user = new ModUser();
        if ($this->request->getPost()){
            $u_id = $this->request->getVar('varId');
            $pushed = $mod_user->user_info($u_id);
            if ($pushed){
                return $this->respond([
                    'status' => 1,
                    'message' => $pushed
                ]);
            }else{
                return $this->respond([
                    'status' => 0,
                    'message' => "No Such user Added to Database"
                ]);
            }
        }else{
            //echo "Ordinary Get";
        }
    }

    public function user_logout(){
        $session = session();
        $session->destroy();
        return redirect()->to('/auth/login');
    }
}
