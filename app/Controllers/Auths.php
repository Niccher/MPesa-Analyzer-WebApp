<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Controller;

use App\Models\ModUser;


class Auths extends BaseController
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
                    return $this->respond([
                        'status' => 1,
                        'message' => "Logged Succesfully",
                        'time' => time(),
                        'userid' => $data['user_Id']
                    ]);
                }else{
                    //$session->setFlashdata('msg', 'Wrong Password');
                    return $this->respond([
                        'status' => 0,
                        'message' => "Wrong credentials",
                        'time' => time(),
                        'userid' => "null"
                    ]);
                }
            }else{
                //$session->setFlashdata('msg', 'Email not Found');
                return $this->respond([
                    'status' => 0,
                    'message' => "No such user",
                    'time' => time(),
                    'userid' => "null"
                ]);
            }
        }else{
            return $this->respond([
                'status' => 2,
                'message' => "Unexpected request sent",
                'time' => time()
            ]);
        }
    }

    public function register(){
        $mod_user = new ModUser();
        helper(['form']);

        if ($this->request->getPost()){

            $rules = [
                'varUsername'=> 'required|min_length[5]|max_length[20]',
                'varEmail'=> 'required|min_length[8]|max_length[30]|valid_email|is_unique[tbl_users.user_Email]',
                'varPassword'=> 'required|min_length[3]|max_length[20]'
            ];

            if($this->validate($rules)){
                $data = [
                    'user_Name' => $this->request->getVar('varUsername'),
                    'user_Email' => $this->request->getVar('varEmail'),
                    'user_Password' => password_hash($this->request->getVar('varPassword'), PASSWORD_DEFAULT),
                    'user_Created' => time(),
                ];
                $pushed = $mod_user->user_register($data);
                if ($pushed){
                    $user_id = $mod_user->user_last_created();
                    return $this->respond([
                        'status' => 1,
                        'message' => "User Added to Database",
                        'time' => time(),
                        'userid' => $user_id
                    ]);
                }else{
                    return $this->respond([
                        'status' => 0,
                        'message' => "Unknown Error, Please try again",
                        'time' => time(),
                        'userid' => "Null"
                    ]);
                }

            }else{
                $validation = $this->validator;
                return $this->respond([
                    'status' => 0,
                    'message' => trim(strip_tags($validation->listErrors()))
                ]);
            }

        }else{
            return $this->respond([
                'status' => 2,
                'message' => "Unexpected request sent",
                'time' => time()
            ]);
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
            return $this->respond([
                'status' => 2,
                'message' => "Unexpected request sent"
            ]);
        }
    }

    public function user_logout(){
        $session = session();
        $session->destroy();
        return redirect()->to('/auth/login');
    }
}
