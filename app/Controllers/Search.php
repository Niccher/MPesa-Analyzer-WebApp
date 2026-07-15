<?php

namespace App\Controllers;

use App\Models\ModUploads;

class Search extends BaseController
{
    public function index()
    {
        $mod_uploads = new ModUploads();
        helper(['mpesa_date', 'form']);

        $filters = [
            'date_from'  => $this->request->getGet('date_from'),   // null = no filter
            'date_to'    => $this->request->getGet('date_to'),      // null = no filter
            'category'   => $this->request->getGet('category'),
            'search'     => $this->request->getGet('search'),
            'min_amount' => $this->request->getGet('min_amount'),
            'max_amount' => $this->request->getGet('max_amount'),
        ];

        // Fetch categories for the filter dropdown
        $db = \Config\Database::connect();
        $view = $this->getSmsView();
        $catBuilder = $db->table('tbl_Sms s')
            ->select('sc.category')
            ->join('tbl_Sms_Classification sc', 'sc.sms_id = s.id', 'inner');
        if ($view === 'mpesa') {
            $catBuilder->where('s.sms_number', 'MPESA');
        } else {
            $catBuilder->where('sc.is_finance', 1);
        }
        $categories = $catBuilder->distinct()->get()->getResultArray();
        $categories = array_unique(array_column($categories, 'category'));

        $transactions = $mod_uploads->getFilteredTransactions($filters, 500);

        $data = [
            'transactions' => $transactions,
            'categories'   => $categories,
            'filters'      => $filters,
            'bg_color'     => '#B1B8ED'
        ];

        return view('Search/index', $data);
    }
}
