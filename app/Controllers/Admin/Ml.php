<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class Ml extends BaseController
{
    use ResponseTrait;

    private function baseUrl(): string
    {
        return (string) config('MlBackend')->baseUrl;
    }

    private function client()
    {
        return \Config\Services::curlrequest();
    }

    public function index()
    {
        $data = [
            'bg_color' => '#B1B8ED',
            'status' => $this->fetchStatus(),
        ];

        return view('Admin/Ml/index', $data);
    }

    public function models()
    {
        $data = [
            'bg_color' => '#B1B8ED',
            'status' => $this->fetchStatus(),
        ];

        return view('Admin/Ml/models', $data);
    }

    public function config()
    {
        $data = [
            'bg_color' => '#B1B8ED',
            'status' => $this->fetchStatus(),
        ];

        return view('Admin/Ml/config', $data);
    }

    public function test()
    {
        $data = [
            'bg_color' => '#B1B8ED',
            'status' => $this->fetchStatus(),
        ];

        return view('Admin/Ml/test', $data);
    }

    public function saveConfig()
    {
        $payload = [
            'llm_model' => $this->request->getPost('llm_model') ?: null,
            'llm_max_tokens' => $this->request->getPost('llm_max_tokens') ? (int)$this->request->getPost('llm_max_tokens') : null,
            'batch_size' => $this->request->getPost('batch_size') ? (int)$this->request->getPost('batch_size') : null,
            'max_retries' => $this->request->getPost('max_retries') ? (int)$this->request->getPost('max_retries') : null,
            'poll_interval' => $this->request->getPost('poll_interval') ? (int)$this->request->getPost('poll_interval') : null,
        ];

        try {
            $resp = $this->client()->post($this->baseUrl() . '/admin/config', [
                'json' => array_filter($payload, fn($v) => $v !== null),
                'timeout' => 10,
            ]);
            $body = json_decode($resp->getBody(), true);

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Config saved. ' . ($body['note'] ?? ''),
                'applied' => $body['applied'] ?? [],
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to reach ML backend: ' . $e->getMessage(),
            ]);
        }
    }

    public function activateModel()
    {
        $filename = $this->request->getPost('filename');
        $llmModel = $this->request->getPost('llm_model');

        if (empty($filename)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No model selected']);
        }

        try {
            $resp = $this->client()->post($this->baseUrl() . '/admin/models/activate', [
                'json' => ['filename' => $filename, 'llm_model' => $llmModel ?: null],
                'timeout' => 10,
            ]);
            $body = json_decode($resp->getBody(), true);

            return $this->response->setJSON([
                'status' => $body['status'] ?? 'error',
                'message' => $body['message'] ?? 'Unknown response',
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to reach ML backend: ' . $e->getMessage(),
            ]);
        }
    }

    public function runTest()
    {
        $sender = $this->request->getPost('sender') ?: 'MPESA';
        $message = $this->request->getPost('message');

        if (empty($message)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Please enter a test message']);
        }

        try {
            $resp = $this->client()->post($this->baseUrl() . '/admin/test-prompt', [
                'json' => ['sender' => $sender, 'messages' => [$message]],
                'timeout' => 120,
            ]);
            $body = json_decode($resp->getBody(), true);

            return $this->response->setJSON([
                'status' => $body['status'] ?? 'error',
                'message' => $body['status'] === 'ok' ? 'LLM responded in ' . ($body['elapsed_ms'] ?? '?') . 'ms' : ($body['message'] ?? 'Error'),
                'result' => $body,
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'LLM request failed: ' . $e->getMessage(),
            ]);
        }
    }

    private function fetchStatus(): array
    {
        try {
            $start = microtime(true);
            $resp = $this->client()->get($this->baseUrl() . '/admin/status', ['timeout' => 5]);
            $latency = round((microtime(true) - $start) * 1000);
            $body = json_decode($resp->getBody(), true);

            return [
                'reachable' => true,
                'latency_ms' => $latency,
                'app' => $body['app'] ?? [],
                'llama' => $body['llama'] ?? 'unknown',
                'db_configured' => $body['db_configured'] ?? false,
                'uptime' => $body['uptime'] ?? null,
                'models' => $body['models'] ?? [],
                'error' => null,
            ];
        } catch (\Throwable $e) {
            return [
                'reachable' => false,
                'latency_ms' => 0,
                'error' => $e->getMessage(),
            ];
        }
    }
}
