<?php

namespace App\Controllers;

class Transactions extends BaseController
{
    public function index()
    {
        helper('mpesa_date');
        // Load the database service (CI4 way)
        $db = \Config\Database::connect();
        
        // Fetch raw SMS data
        // In reality, this should be filtered by the logged-in user via `sms_owner` or device
        $builder = $db->table('tbl_Sms');
        
        // Let's get the 100 most recent transactions for the table
        $builder->orderBy('sms_time', 'DESC');
        $builder->limit(100);
        
        $data = [
            'transactions' => $builder->get()->getResult(),
            'bg_color' => '#B1B8ED'
        ];

        return view('Dash/transactions', $data);
    }
}
