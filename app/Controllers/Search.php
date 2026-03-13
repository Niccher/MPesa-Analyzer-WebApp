<?php

namespace App\Controllers;

use App\Models\ModUploads;

class Search extends BaseController
{
    public function index()
    {
        $mod_uploads = new ModUploads();
        helper(['mpesa_date', 'form']);

        $lastUploadDate = $mod_uploads->getLastUploadDate();
        $defaultDate = $lastUploadDate ? date('Y-m-d', strtotime($lastUploadDate)) : date('Y-m-d');

        $filters = [
            'date_from'  => $this->request->getGet('date_from') ?? $defaultDate,
            'date_to'    => $this->request->getGet('date_to') ?? $defaultDate,
            'category'   => $this->request->getGet('category'),
            'search'     => $this->request->getGet('search'),
            'min_amount' => $this->request->getGet('min_amount'),
            'max_amount' => $this->request->getGet('max_amount'),
        ];

        // Fetch categories for the filter dropdown
        $db = \Config\Database::connect();
        $categories = $db->table('tbl_Sms')->select('sms_category')->distinct()->get()->getResultArray();
        $categories = array_column($categories, 'sms_category');

        $transactions = $mod_uploads->getFilteredTransactions($filters);

        $data = [
            'transactions' => $transactions,
            'categories'   => $categories,
            'filters'      => $filters,
            'bg_color'     => '#B1B8ED'
        ];

        return view('Search/index', $data);
    }
}
