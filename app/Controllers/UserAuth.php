<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Shield\Result;
use CodeIgniter\API\ResponseTrait;

class UserAuth extends BaseController{

    use ResponseTrait;

    public function user_login(){
        $credentials = [
            'email' => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'),
        ];

        if (auth()->loggedIn()){
            auth()->logout();
        }

        $login_attempt = auth()->attempt($credentials);

        if (!$login_attempt->isOK()){
            $reason = $login_attempt->reason();
            //return $this->fail($reason, 400);
            return $this->respond(["status" => "Invalid","message" => $reason]);
        }else{
            $user = new UserModel();
            $userinfo = $user->find(auth()->id());

            //print(auth()->user()->id, auth()->user()->email);

            $token = $userinfo->generateAccessToken("TokenName");
            $user_auth_token = $token->raw_token;

            return $this->respond(["status" => "Valid","token" => $user_auth_token]);
        }
    }

    public function user_register(){
        $users = new UserModel();
        //$users = auth()->getProvider();

        //$this->request->getVar('username') $this->request->getPost('username')
        $creds['username'] = $this->request->getPost('username');
        $creds['email'] = $this->request->getPost('email');
        $creds['password'] = $this->request->getPost('password');

        $newUser = new User([
            'username' => $this->request->getPost('username'),
            'email' => $this->request->getPost('email'),
            'password' => $this->request->getPost('password')
        ]);
        //$newUser = new User($creds);

        //return $this->respond(["status" => "Invalid", "postData" => $_POST, "newUser" => $newUser,"userCred" => $creds, "error" => "Unexpected error"]);

        try {
            $register_attempt = $users->save($newUser);

            $newUser = $users->findById($users->getInsertID());
            $users->addToDefaultGroup($newUser);

            if (!$register_attempt){
                return $this->respond(["status" => "Invalid", "error" => "Unexpected error occurred.0"]);
            }else{
                return $this->respond(["status" => "Valid", "message" => "New User added."]);
            }
        }catch (Exception $ex) {
            return $this->respond(["status" => "Invalid", "error" => "Unexpected error occurred.1"]);
        }

    }

    public function verify_token(){
        $tokenStr = $this->request->getPost('token');
        if (empty($tokenStr)) {
            return $this->respond(["status" => "0", "message" => "Token is required"]);
        }

        $hashedToken = hash('sha256', $tokenStr);
        $db = \Config\Database::connect();
        
        $identity = $db->table('auth_identities')
            ->where('type', \CodeIgniter\Shield\Authentication\Authenticators\AccessTokens::ID_TYPE_ACCESS_TOKEN)
            ->where('secret', $hashedToken)
            ->get()
            ->getRow();

        if ($identity) {
            $userModel = new UserModel();
            $user = $userModel->find($identity->user_id);
            if ($user) {
                // Return payload matching the expected Android format
                return $this->respond([
                    "status" => "1",
                    "message" => "Token Valid",
                    "time" => date('Y-m-d H:i:s'),
                    "userid" => strval($user->id),
                    "uuid" => "device_paired", // Place-holder for UUID
                    "user_name" => $user->username,
                    "user_email" => $user->email ?? "null"
                ]);
            }
        }
        
        return $this->respond(["status" => "0", "message" => "Invalid Token"]);
    }
}
