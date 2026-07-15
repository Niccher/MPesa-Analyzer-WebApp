<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;

class Home extends BaseController
{
    use ResponseTrait;
    public function index()
    {
        return view('landing');
    }

    public function setView(string $view)
    {
        if (in_array($view, ['mpesa', 'finance'])) {
            session()->set('sms_view', $view);
        }
        return $this->response->setJSON(['status' => 'ok']);
    }
}
