<?php

namespace App\Controllers;

use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserModel;
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

    public function new_user(){

        /*$users = model('UserModel');
        $user = new User([
            'username' => 'foo-bar',
            'email'    => 'foo.bar@example.com',
            'password' => 'secret plain text password',
        ]);
        $users->save($user);

        // To get the complete user object with ID, we need to get from the database
        $user = $users->findById($users->getInsertID());

        // Add to default group
        $users->addToDefaultGroup($user);*/

        $credentials = [
            'email' => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'),
        ];

        if (auth()->loggedIn()){
            auth()->logout();
        }

        $login_attempt = auth()->attempt($credentials);

        if (!$login_attempt->isOK()){
            return $this->fail($login_attempt->reason(), 400);
        }else{
            $user = new UserModel();
            $userinfo = $user->find(auth()->id());

            //print(auth()->user()->id);
            //print(auth()->user()->email);

            $token = $userinfo->generateAccessToken("TokenName");
            $user_auth_token = $token->raw_token;
            return $this->respond(["token" => $user_auth_token]);
        }
    }
}
