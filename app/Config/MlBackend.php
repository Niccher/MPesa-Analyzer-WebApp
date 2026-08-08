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

        $this->baseUrl = rtrim((string) env('ML_BACKEND_URL', 'http://ml-mpesa-analyzer:9050'), '/');
    }
}
