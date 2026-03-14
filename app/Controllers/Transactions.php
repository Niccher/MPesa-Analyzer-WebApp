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
        $countBuilder = $db->table('tbl_Sms');
        if (!empty($category)) {
            $countBuilder->like('sms_category', $category);
        }
        $total = $countBuilder->countAllResults();

        // Fetch just this page
        $builder = $db->table('tbl_Sms');
        if (!empty($category)) {
            $builder->like('sms_category', $category);
        }
        $transactions = $builder
            ->orderBy('sms_time', 'DESC')
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
