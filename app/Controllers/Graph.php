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

        // Fetch latest 20 transactions for the table below the graph
        $recent = $mod_uploads->getFilteredTransactions([], 20, 0);

        // Dashboard metrics
        $metrics = $mod_uploads->getDashboardMetrics30Days($lastDate);
        $sent    = $mod_uploads->getSentSummary30Days($lastDate);
        $received = $mod_uploads->getReceivedSummary30Days($lastDate);

        $data = [
            'analytics'        => $analytics,
            'ai_observations'  => $ai_observations,
            'recent'           => $recent,
            'metrics'          => $metrics,
            'sent'             => $sent,
            'received'         => $received,
            'bg_color'         => '#B1B8ED',
        ];

        return view('Dash/graph', $data);
    }
}
