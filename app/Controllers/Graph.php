<?php

namespace App\Controllers;

use App\Models\ModUploads;
use App\Models\ModInsights;

class Graph extends BaseController
{
    public function index()
    {
        helper('mpesa_date');
        $mod_uploads  = new ModUploads();
        $mod_insights = new ModInsights();

        $lastDate      = $mod_uploads->getLatestTransactionDate() ?? date('Y-m-d H:i:s');
        $analytics     = $mod_uploads->getAnalyticsData30Days($lastDate);
        $ai_observations = $mod_insights->getAIObservations();

        $data = [
            'analytics'        => $analytics,
            'ai_observations'  => $ai_observations,
            'bg_color'         => '#B1B8ED',
        ];

        return view('Dash/graph', $data);
    }
}
