<?php

namespace App\Controllers;

class Transactions extends BaseController
{
    protected int $perPage = 25;

    public function index()
    {
        helper('mpesa_date');
        $db = \Config\Database::connect();

        $page    = (int)($this->request->getGet('page') ?? 1);
        $page    = max(1, $page);
        $offset  = ($page - 1) * $this->perPage;

        $category = $this->request->getGet('category') ?? '';
        $view = $this->getSmsView();
        $vf = $this->smsViewFilter($view);

        // Count total rows for pagination
        $countBuilder = $db->table('tbl_Sms s')
            ->join('tbl_Analyzed_Transactions a', 's.sms__id = a.orig_sms_id', 'left');
        if ($vf['join']) {
            $countBuilder->join('tbl_Sms_Classification sc', 'sc.sms_id = s.id AND sc.is_finance = 1', 'inner');
        }
        if (!empty($category)) {
            $countBuilder->like('s.sms_category', $category);
        }
        if ($view === 'mpesa') {
            $countBuilder->where("s.sms_number = 'MPESA'");
        }
        $total = $countBuilder->countAllResults();

        // Fetch just this page
        $builder = $db->table('tbl_Sms s')
            ->select('s.*, a.counterparty, a.description as analyzed_category, a.amount as analyzed_amount, sc.category as cl_category, sc.direction as cl_direction')
            ->join('tbl_Analyzed_Transactions a', 's.sms__id = a.orig_sms_id', 'left')
            ->join('tbl_Sms_Classification sc', 'sc.sms_id = s.id', 'left');
        if (!empty($category)) {
            $builder->like('s.sms_category', $category);
        }
        if ($view === 'finance') {
            $builder->where('sc.is_finance', 1);
        } else {
            $builder->where("s.sms_number = 'MPESA'");
        }
        $transactions = $builder
            ->orderBy('s.sms_time', 'DESC')
            ->limit($this->perPage, $offset)
            ->get()
            ->getResult();

        $totalPages = (int)ceil($total / $this->perPage);

        $data = [
            'transactions' => $transactions,
            'total'        => $total,
            'page'         => $page,
            'perPage'      => $this->perPage,
            'totalPages'   => $totalPages,
            'category'     => $category,
            'view'         => $view,
            'bg_color'     => '#B1B8ED'
        ];

        return view('Dash/transactions', $data);
    }

    public function export()
    {
        $db = \Config\Database::connect();
        $category = $this->request->getGet('category') ?? '';
        $view = $this->getSmsView();

        $builder = $db->table('tbl_Sms s')
            ->select('s.sms_time, s.sms_category, a.counterparty, a.amount as analyzed_amount, s.sms_body, sc.category as cl_category, sc.direction as cl_direction')
            ->join('tbl_Analyzed_Transactions a', 's.sms__id = a.orig_sms_id', 'left')
            ->join('tbl_Sms_Classification sc', 'sc.sms_id = s.id', 'left');
        
        if (!empty($category)) {
            $builder->like('s.sms_category', $category);
        }
        if ($view === 'finance') {
            $builder->where('sc.is_finance', 1);
        } else {
            $builder->where("s.sms_number = 'MPESA'");
        }

        $transactions = $builder->orderBy('s.sms_time', 'DESC')->get()->getResult();

        $filename = ($view === 'finance' ? 'finance' : 'mpesa') . "_transactions_" . date('Ymd_His') . ".csv";
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        
        // CSV Headers
        fputcsv($output, ['Date & Time', 'Category', 'Direction', 'Counterparty', 'Amount (Ksh)', 'Message Body']);

        foreach ($transactions as $tx) {
            $body = base64_decode($tx->sms_body);
            $body = trim(preg_replace('/\s+/', ' ', $body));
            
            fputcsv($output, [
                $tx->sms_time,
                $tx->cl_category ?? $tx->sms_category,
                $tx->cl_direction ?? '',
                $tx->counterparty ?? 'Unknown',
                number_format((float)($tx->analyzed_amount ?? 0), 2, '.', ''),
                $body
            ]);
        }

        fclose($output);
        exit;
    }
}
