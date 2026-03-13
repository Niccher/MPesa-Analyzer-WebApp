<?php

namespace App\Controllers;

class History extends BaseController
{
    public function index()
    {
        helper('mpesa_date');
        $db = \Config\Database::connect();
        
        // Fetch raw upload history logs
        $builder = $db->table('tbl_Loot_Summary');
        $builder->orderBy('loot_Created', 'DESC');
        $builder->limit(50);
        
        $data = [
            'history' => $builder->get()->getResult(),
            'bg_color' => '#B1B8ED' // Theme consistency
        ];

        return view('Dash/history', $data);
    }
}
