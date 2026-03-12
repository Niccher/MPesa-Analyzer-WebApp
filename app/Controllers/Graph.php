<?php

namespace App\Controllers;

use App\Models\ModUploads;

class Graph extends BaseController
{
    public function index()
    {
        $mod_uploads = new ModUploads();
        
        // In a real app, this would filter by the logged-in user's UUID.
        // For now, we'll fetch global data to ensure the charts render something, 
        // or aggregate data from tbl_Loot_Summary.
        
        // We'll pass some mockup visualization data over to the view
        $data = [
            'bg_color' => '#B1B8ED'
        ];

        return view('Dash/graph', $data);
    }
}
