<?php

namespace App\Controllers;

use App\Models\ModUploads;

class Info extends BaseController
{
    public function index()
    {
        $mod_uploads = new ModUploads();
        
        $data = [
            'total_processed' => $mod_uploads->countAll(),
            'bg_color' => '#B1B8ED'
        ];

        return view('Dash/info', $data);
    }
}
