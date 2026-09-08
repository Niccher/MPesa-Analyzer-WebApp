<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class MlBackend extends BaseConfig
{
    /**
     * Base URL of the ML (FastAPI) backend reachable from within Docker.
     */
    public string $baseUrl = '';

    public function __construct()
    {
        parent::__construct();

        $dbUrl = '';
        try {
            $db = \Config\Database::connect();
            if ($db->tableExists('tbl_Settings')) {
                $row = $db->table('tbl_Settings')->where('`key`', 'ml_backend_url')->get()->getRow();
                if ($row && !empty($row->value)) {
                    $dbUrl = trim((string)$row->value);
                }
            }
        } catch (\Throwable $e) {}

        $url = $dbUrl !== '' ? $dbUrl : (string) env('ML_BACKEND_URL', 'http://ml-mpesa-analyzer:9050');
        $url = trim($url);
        if ($url !== '' && !preg_match('#^https?://#i', $url)) {
            $url = 'http://' . $url;
        }
        $this->baseUrl = rtrim($url, '/');
    }
}
