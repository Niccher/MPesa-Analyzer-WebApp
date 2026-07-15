<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;

class Home extends BaseController
{
    use ResponseTrait;
    public function index()
    {
        return view('landing');
    }

    public function setView(string $view)
    {
        if (in_array($view, ['mpesa', 'finance'])) {
            session()->set('sms_view', $view);
        }
        return $this->response->setJSON(['status' => 'ok']);
    }

    public function rescan()
    {
        $userId = auth()->user()->id;
        $fastApiUrl = 'http://mpesa-analyser-docker:9050/process/for-user/' . $userId;

        $client = \Config\Services::curlrequest();
        try {
            $resp = $client->post($fastApiUrl, ['timeout' => 5]);
            $body = json_decode($resp->getBody(), true);
            if ($body && ($body['status'] === 'done' || isset($body['messages_processed']))) {
                return $this->response->setJSON([
                    'status'  => 'started',
                    'message' => 'LLM analysis triggered. ' . ($body['messages_processed'] ?? 0) . ' messages processed.',
                ]);
            }
            if ($body && $body['status'] === 'skipped') {
                return $this->response->setJSON([
                    'status'  => 'started',
                    'message' => 'A scan is already in progress. Check back shortly.',
                ]);
            }
            return $this->response->setJSON([
                'status'  => 'started',
                'message' => 'LLM analysis has been queued.',
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Rescan failed: ' . $e->getMessage());
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Could not reach the analysis service. Please try again later.',
            ]);
        }
    }
}
