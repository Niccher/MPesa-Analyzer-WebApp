<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ModUploads;

class Dash extends BaseController
{
    public function index()
    {
        $mod_uploads = new ModUploads();
        
        // Fetch some basic stats for the dashboard
        // In a real scenario, we would filter by user_id
        $data = [
            'total_uploads' => $mod_uploads->countAll(),
            'recent_uploads' => $mod_uploads->orderBy('loot_Id', 'DESC')->limit(5)->find(),
            // Background color from Android app
            'bg_color' => '#B1B8ED'
        ];

        return view('Dash/index', $data);
    }
}
