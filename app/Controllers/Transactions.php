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
        $countBuilder = $db->table('tbl_Sms s');
        if (!empty($category)) {
            $countBuilder->join('tbl_Sms_Classification sc', 'sc.sms_id = s.id', 'inner');
            $this->applyCategoryFilter($countBuilder, $category);
        }
        $total = $countBuilder->countAllResults();

        // Fetch just this page
        $builder = $db->table('tbl_Sms s')
            ->select('s.*, a.counterparty, a.description as analyzed_category, a.amount as analyzed_amount, sc.category as cl_category, sc.direction as cl_direction')
            ->join('tbl_Analyzed_Transactions a', 's.sms__id = a.orig_sms_id', 'left')
            ->join('tbl_Sms_Classification sc', 'sc.sms_id = s.id', 'left');
        $this->applyCategoryFilter($builder, $category);
        $transactions = $builder
            ->orderBy('s.sms_time', 'DESC')
            ->limit($this->perPage, $offset)
            ->get()
            ->getResult();

        $totalPages = (int)ceil($total / $this->perPage);

        // Fetch LLM categories for the Fix dropdown
        $llmCategories = $db->table('tbl_Sms_Classification')
            ->select('category')
            ->distinct()
            ->where('category IS NOT NULL')
            ->where("category != ''")
            ->get()
            ->getResultArray();
        $llmCategories = array_unique(array_column($llmCategories, 'category'));
        sort($llmCategories);

        $data = [
            'transactions'   => $transactions,
            'total'          => $total,
            'page'           => $page,
            'perPage'        => $this->perPage,
            'totalPages'     => $totalPages,
            'category'       => $category,
            'llm_categories' => $llmCategories,
            'bg_color'       => '#B1B8ED'
        ];

        return view('Dash/transactions', $data);
    }

    private function applyCategoryFilter($builder, string $category): void
    {
        switch ($category) {
            case 'finances':
                $builder->where('sc.is_finance', 1);
                break;
            case 'money_in':
                $builder->where('sc.direction', 'incoming');
                break;
            case 'money_out':
                $builder->where('sc.direction', 'outgoing');
                break;
            case 'notifications':
                $builder->where('sc.is_finance', 0);
                break;
            default:
                // no filter — show all
                break;
        }
    }

    public function export()
    {
        $db = \Config\Database::connect();
        $category = $this->request->getGet('category') ?? '';

        $builder = $db->table('tbl_Sms s')
            ->select('s.sms_time, a.counterparty, a.amount as analyzed_amount, s.sms_body, sc.category as cl_category, sc.direction as cl_direction')
            ->join('tbl_Analyzed_Transactions a', 's.sms__id = a.orig_sms_id', 'left')
            ->join('tbl_Sms_Classification sc', 'sc.sms_id = s.id', 'left');

        $this->applyCategoryFilter($builder, $category);

        $transactions = $builder->orderBy('s.sms_time', 'DESC')->get()->getResult();

        $filename = "all_transactions_" . date('Ymd_His') . ".csv";

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        fputcsv($output, ['Date & Time', 'Category', 'Direction', 'Counterparty', 'Amount (Ksh)', 'Sender', 'Message Body']);

        foreach ($transactions as $tx) {
            $body = base64_decode($tx->sms_body);
            $body = trim(preg_replace('/\s+/', ' ', $body));

            fputcsv($output, [
                $tx->sms_time,
                $tx->cl_category ?? '',
                $tx->cl_direction ?? '',
                $tx->counterparty ?? 'Unknown',
                number_format((float)($tx->analyzed_amount ?? 0), 2, '.', ''),
                $tx->sms_number ?? '',
                $body
            ]);
        }

        fclose($output);
        exit;
    }
}
