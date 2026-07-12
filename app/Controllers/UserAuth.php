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

    public function delete_account(){
        return $this->perform_deletion(true);
    }

    public function delete_data(){
        return $this->perform_deletion(false);
    }

    private function perform_deletion(bool $deleteAccount = false) {
        $db = \Config\Database::connect();
        $isApi = $this->request->getPost('varToken') ? true : false;
        $user_id = null;
        $rawTokens = [];

        if ($isApi) {
            $tokenStr = $this->request->getPost('varToken');
            $hashedToken = hash('sha256', $tokenStr);
            $identity = $db->table('auth_identities')
                ->where('type', \CodeIgniter\Shield\Authentication\Authenticators\AccessTokens::ID_TYPE_ACCESS_TOKEN)
                ->where('secret', $hashedToken)
                ->get()
                ->getRow();

            if (!$identity) {
                return $this->respond(["status" => "0", "message" => "Invalid Token"]);
            }
            $user_id = $identity->user_id;
            $rawTokens[] = $tokenStr;
        } else {
            if (!auth()->loggedIn()) {
                return redirect()->to('login');
            }
            $user_id = auth()->id();
        }

        if ($db->tableExists('tbl_User_Devices')) {
            $userDevices = $db->table('tbl_User_Devices')->where('user_id', $user_id)->get()->getResult();
            $dbTokens = array_column($userDevices, 'device_token');
            if (!empty($dbTokens)) {
                $rawTokens = array_unique(array_merge($rawTokens, $dbTokens));
            }
        }

        $db->transStart();

        if (!empty($rawTokens)) {
            // Find all loot entries for these tokens to delete physical files
            if ($db->tableExists('tbl_Loot')) {
                $loots = $db->table('tbl_Loot')->whereIn('loot_Owner', $rawTokens)->get()->getResult();
                foreach ($loots as $loot) {
                    $filePathTxt = WRITEPATH . 'uploads/txt_loot/' . ltrim($loot->loot_Name, '/');
                    $filePathJson = str_replace('.txt', '.json', $filePathTxt);
                    if (file_exists($filePathTxt)) unlink($filePathTxt);
                    if (file_exists($filePathJson)) unlink($filePathJson);
                }
            }

            // Delete analyzed transactions
            if ($db->tableExists('tbl_Analyzed_Transactions') && $db->tableExists('tbl_Sms')) {
                $db->table('tbl_Analyzed_Transactions')
                   ->whereIn('orig_sms_id', function(\CodeIgniter\Database\BaseBuilder $builder) use ($rawTokens) {
                       return $builder->select('sms__id')->from('tbl_Sms')->whereIn('sms_owner', $rawTokens);
                   })->delete();
            }

            // Delete raw SMS
            if ($db->tableExists('tbl_Sms')) {
                $db->table('tbl_Sms')->whereIn('sms_owner', $rawTokens)->delete();
            }

            // Delete Loot Summary
            if ($db->tableExists('tbl_Loot_Summary') && $db->tableExists('tbl_Loot')) {
                $db->table('tbl_Loot_Summary')->whereIn('loot_Uuid', function(\CodeIgniter\Database\BaseBuilder $builder) use ($rawTokens) {
                    return $builder->select('loot_Uuid')->from('tbl_Loot')->whereIn('loot_Owner', $rawTokens);
                })->delete();
            }

            // Delete Loot
            if ($db->tableExists('tbl_Loot')) {
                $db->table('tbl_Loot')->whereIn('loot_Owner', $rawTokens)->delete();
            }
        }

        if ($deleteAccount) {
            // Delete user devices
            if ($db->tableExists('tbl_User_Devices')) {
                $db->table('tbl_User_Devices')->where('user_id', $user_id)->delete();
            }

            // Delete Shield User completely
            $db->table('auth_identities')->where('user_id', $user_id)->delete();
            $db->table('auth_logins')->where('user_id', $user_id)->delete();
            $db->table('auth_groups_users')->where('user_id', $user_id)->delete();
            $db->table('auth_permissions_users')->where('user_id', $user_id)->delete();
            $db->table('users')->where('id', $user_id)->delete();
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            if ($isApi) {
                return $this->respond(["status" => "0", "message" => "Failed to perform operation. Database error."]);
            }
            return redirect()->back()->with('error', 'Failed to perform operation. Database error.');
        }

        if ($isApi) {
            return $this->respond(["status" => "1", "message" => "Operation successfully completed."]);
        }

        if ($deleteAccount) {
            auth()->logout();
            return redirect()->to('login')->with('message', 'Your account has been deleted permanently.');
        } else {
            return redirect()->back()->with('message', 'Your data has been successfully deleted. Your account was preserved.');
        }
    }
}
