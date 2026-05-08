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

        // Count total rows for pagination
        $countBuilder = $db->table('tbl_Sms s')
            ->join('tbl_Analyzed_Transactions a', 's.sms__id = a.orig_sms_id', 'left');
        if (!empty($category)) {
            $countBuilder->like('s.sms_category', $category);
        }
        $total = $countBuilder->countAllResults();

        // Fetch just this page
        $builder = $db->table('tbl_Sms s')
            ->select('s.*, a.counterparty, a.description as analyzed_category, a.amount as analyzed_amount')
            ->join('tbl_Analyzed_Transactions a', 's.sms__id = a.orig_sms_id', 'left');
        if (!empty($category)) {
            $builder->like('s.sms_category', $category);
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
            'bg_color'     => '#B1B8ED'
        ];

        return view('Dash/transactions', $data);
    }

    public function export()
    {
        $db = \Config\Database::connect();
        $category = $this->request->getGet('category') ?? '';

        $builder = $db->table('tbl_Sms s')
            ->select('s.sms_time, s.sms_category, a.counterparty, a.amount as analyzed_amount, s.sms_body')
            ->join('tbl_Analyzed_Transactions a', 's.sms__id = a.orig_sms_id', 'left');
        
        if (!empty($category)) {
            $builder->like('s.sms_category', $category);
        }

        $transactions = $builder->orderBy('s.sms_time', 'DESC')->get()->getResult();

        $filename = "mpesa_transactions_" . date('Ymd_His') . ".csv";
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        
        // CSV Headers
        fputcsv($output, ['Date & Time', 'Category', 'Counterparty', 'Amount (Ksh)', 'Message Body']);

        foreach ($transactions as $tx) {
            $body = base64_decode($tx->sms_body);
            // Remove newlines and excess whitespace for CSV safety
            $body = trim(preg_replace('/\s+/', ' ', $body));
            
            fputcsv($output, [
                $tx->sms_time,
                $tx->sms_category,
                $tx->counterparty ?? 'Unknown',
                number_format((float)($tx->analyzed_amount ?? 0), 2, '.', ''),
                $body
            ]);
        }

        fclose($output);
        exit;
    }
}
