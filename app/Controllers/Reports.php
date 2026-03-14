<?php

namespace App\Controllers;

use App\Models\ModUploads;
use App\Models\ModInsights;

class Reports extends BaseController
{
    public function index()
    {
        helper('mpesa_date');
        $mod = new ModUploads();
        $modInsights = new ModInsights();

        $year  = (int)($this->request->getGet('year')  ?? date('Y'));
        $month = (int)($this->request->getGet('month') ?? date('m'));
        $month = max(1, min(12, $month));

        $report = $mod->getReportData($year, $month);
        $trends = $modInsights->getSpendingTrends();
        $recurring = $modInsights->getRecurringPayments();

        // Build available months for selector
        $months = [];
        for ($i = 0; $i < 12; $i++) {
            $ts = strtotime("-$i months");
            $months[] = [
                'value' => date('Y-m', $ts),
                'label' => date('F Y', $ts),
                'y'     => (int)date('Y', $ts),
                'm'     => (int)date('m', $ts),
            ];
        }

        $data = [
            'report'      => $report,
            'trends'      => $trends,
            'recurring'   => $recurring,
            'months'      => $months,
            'selectedKey' => sprintf('%04d-%02d', $year, $month),
            'bg_color'    => '#B1B8ED',
        ];

        return view('Reports/index', $data);
    }

    public function printView()
    {
        helper('mpesa_date');
        $mod = new ModUploads();

        $year  = (int)($this->request->getGet('year')  ?? date('Y'));
        $month = (int)($this->request->getGet('month') ?? date('m'));
        $month = max(1, min(12, $month));

        $report = $mod->getReportData($year, $month);

        return view('Reports/print', ['report' => $report]);
    }
}
