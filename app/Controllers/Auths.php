<?php

namespace App\Controllers;

class Auths extends \IonAuth\Controllers\Auth
{
    public function index(){
        if (! $this->ionAuth->loggedIn()){
            return redirect()->to('/auth/login');
        }else{
            return redirect()->to('/home');
        }
    }
}
