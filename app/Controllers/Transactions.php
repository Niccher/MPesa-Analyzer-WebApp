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
}
