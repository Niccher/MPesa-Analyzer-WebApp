<?php

namespace App\Controllers;

use App\Models\ModUploads;

class Graph extends BaseController
{
    public function index()
    {
        helper('mpesa_date');
        $mod_uploads = new ModUploads();
        $lastDate = $mod_uploads->getLastUploadDate() ?? date('Y-m-d H:i:s');
        $analytics = $mod_uploads->getAnalyticsData30Days($lastDate);
        
        $data = [
            'analytics' => $analytics,
            'bg_color' => '#B1B8ED'
        ];

        return view('Dash/graph', $data);
    }
}
