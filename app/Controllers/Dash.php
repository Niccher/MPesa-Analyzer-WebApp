<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ModUploads;
use App\Models\ModBudget;
use App\Models\ModInsights;

class Dash extends BaseController
{
    public function index()
    {
        $mod_uploads = new ModUploads();
        helper('mpesa_date');
        
        $lastDate = $mod_uploads->getLatestTransactionDate() ?? date('Y-m-d H:i:s');
        
        $metrics = $mod_uploads->getDashboardMetrics30Days($lastDate);
        $sent_summary = $mod_uploads->getSentSummary30Days($lastDate);
        $received_summary = $mod_uploads->getReceivedSummary30Days($lastDate);
        $recent_transactions = $mod_uploads->getRecentTransactions(10);
        
        $modBudget   = new ModBudget();
        $modInsights = new ModInsights();

        $data = [
            'total_uploads'       => $mod_uploads->countAll(),
            'metrics'             => $metrics,
            'sent_summary'        => $sent_summary,
            'received_summary'    => $received_summary,
            'recent_transactions' => $recent_transactions,
            'top_counterparties'  => $mod_uploads->getTopCounterparties(5),
            'budget_alerts'       => $modBudget->getBudgetProgress($modBudget->getBudgets()),
            'smart_alerts'        => $modInsights->getSmartAlerts(),
            'trends'              => $modInsights->getSpendingTrends(),
            'bg_color'            => '#B1B8ED'
        ];

        return view('Dash/index', $data);
    }
}
