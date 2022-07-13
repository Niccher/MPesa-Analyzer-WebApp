<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function __construct(){
        //parent::__construct();
    }
    public function index(){
        return view('welcome_message');
    }
}
