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

    /**
     * ML job history & statistics (time taken, SMS classified, errors, status).
     */
    public function jobs()
    {
        $db = \Config\Database::connect();

        if (!$db->tableExists('tbl_Processing_Jobs')) {
            return view('Admin/Ml/jobs', [
                'bg_color' => '#B1B8ED',
                'status' => $this->fetchStatus(),
                'jobs' => [],
                'totals' => ['jobs' => 0, 'msgs' => 0, 'errors' => 0, 'time' => 0],
                'status_counts' => [],
            ]);
        }

        $jobs = $db->table('tbl_Processing_Jobs')
            ->orderBy('id', 'DESC')
            ->limit(200)
            ->get()
            ->getResultArray();

        // Resolve user_id -> username for display.
        $usernames = [];
        $userRows = $db->table('users')->select('id, username')->get()->getResult();
        foreach ($userRows as $r) {
            $usernames[(string) $r->id] = $r->username;
        }

        $totals = ['jobs' => 0, 'msgs' => 0, 'errors' => 0, 'time' => 0];
        $statusCounts = [];
        foreach ($jobs as $key => $j) {
            $totals['jobs']++;
            $totals['msgs'] += (int) $j['messages_processed'];
            $totals['errors'] += (int) $j['errors'];
            $totals['time'] += (int) $j['duration_seconds'];
            $statusCounts[$j['status']] = ($statusCounts[$j['status']] ?? 0) + 1;
            $j['username'] = $usernames[(string) $j['user_id']] ?? ($j['user_id'] ? substr((string) $j['user_id'], 0, 8) . '…' : '—');
            $jobs[$key] = $j;
        }

        $data = [
            'bg_color' => '#B1B8ED',
            'status' => $this->fetchStatus(),
            'jobs' => $jobs,
            'totals' => $totals,
            'status_counts' => $statusCounts,
        ];

        return view('Admin/Ml/jobs', $data);
    }

    /**
     * Admin console to view senders and override their finance flag
     * (e.g. mark a sender as non-finance).
     */
    public function senders()
    {
        $data = [
            'bg_color' => '#B1B8ED',
            'status' => $this->fetchStatus(),
            'senders' => $this->loadSenders(),
        ];

        return view('Admin/Ml/senders', $data);
    }

    /**
     * Set a sender's finance flag across all sender profiles and existing
     * SMS classifications. 0 = non-finance override.
     */
    public function setSenderFinance()
    {
        $number = trim((string) $this->request->getPost('sender'));
        $isFinance = (int) $this->request->getPost('is_finance') === 1;

        if ($number === '') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No sender provided.']);
        }

        $db = \Config\Database::connect();

        $db->transStart();

        $updatedProfiles = 0;
        if ($db->tableExists('tbl_Sender_Profiles')) {
            $updatedProfiles = $db->table('tbl_Sender_Profiles')
                ->where('sp_number', $number)
                ->update([
                    'sp_is_finance' => $isFinance ? 1 : 0,
                    'sp_updated' => date('Y-m-d H:i:s'),
                ]);
        }

        $updatedClassifications = 0;
        if ($db->tableExists('tbl_Sms_Classification')) {
            $updatedClassifications = $db->table('tbl_Sms_Classification')
                ->where('sender', $number)
                ->update(['is_finance' => $isFinance ? 1 : 0]);
        }

        $db->transComplete();

        \App\Libraries\Audit::log('ml_sender_finance', 'config', 'Set sender finance flag', [
            'sender' => $number,
            'is_finance' => $isFinance,
            'profiles_updated' => $updatedProfiles,
            'classifications_updated' => $updatedClassifications,
        ]);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Sender "' . $number . '" marked as ' . ($isFinance ? 'finance' : 'non-finance')
                . " ({$updatedProfiles} profiles, {$updatedClassifications} SMS classifications updated).",
        ]);
    }

    /**
     * List distinct senders with aggregate finance status from sender profiles.
     */
    private function loadSenders(): array
    {
        $db = \Config\Database::connect();

        if (!$db->tableExists('tbl_Sender_Profiles')) {
            return [];
        }

        $rows = $db->query(
            "SELECT sp_number,
                    MAX(sp_name) AS sp_name,
                    MAX(sp_category) AS sp_category,
                    COUNT(*) AS total,
                    COALESCE(SUM(sp_is_finance), 0) AS finance_count
             FROM tbl_Sender_Profiles
             GROUP BY sp_number
             ORDER BY finance_count DESC, sp_number ASC"
        )->getResultArray();

        $senders = [];
        foreach ($rows as $row) {
            $senders[] = [
                'number' => $row['sp_number'],
                'name' => $row['sp_name'] ?? '',
                'category' => $row['sp_category'] ?? '',
                'total' => (int) $row['total'],
                'finance_count' => (int) $row['finance_count'],
                'is_finance' => (int) $row['finance_count'] > 0,
            ];
        }

        return $senders;
    }

    /**
     * Global allowlist of senders always treated as finance-related
     * ("allowed by default"). The ML backend reads this table first and
     * falls back to its hardcoded list when the table is empty.
     */
    public function allowed()
    {
        $data = [
            'bg_color' => '#B1B8ED',
            'status' => $this->fetchStatus(),
            'allowed' => $this->loadAllowed(),
            'fallback_active' => $this->isFallbackActive(),
        ];

        return view('Admin/Ml/allowed', $data);
    }

    /**
     * Add a sender to the global allowed list.
     */
    public function allowedAdd()
    {
        $sender = strtoupper(trim((string) $this->request->getPost('sender')));
        $category = trim((string) $this->request->getPost('category'));

        if ($sender === '') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No sender provided.']);
        }

        $db = \Config\Database::connect();
        $exists = $db->table('tbl_Allowed_Senders')
            ->where('sender', $sender)
            ->get()
            ->getRow();

        if ($exists) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Sender "' . $sender . '" is already on the allowed list.']);
        }

        $db->table('tbl_Allowed_Senders')->insert([
            'sender' => $sender,
            'category' => $category ?: null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        \App\Libraries\Audit::log('ml_allowed_add', 'config', 'Added sender to allowed list', [
            'sender' => $sender,
            'category' => $category,
        ]);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Sender "' . $sender . '" added to the allowed list. The ML backend will treat it as finance on its next processing cycle.',
        ]);
    }

    /**
     * Remove a sender from the global allowed list.
     */
    public function allowedRemove()
    {
        $sender = strtoupper(trim((string) $this->request->getPost('sender')));

        if ($sender === '') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No sender provided.']);
        }

        $db = \Config\Database::connect();
        $db->table('tbl_Allowed_Senders')->where('sender', $sender)->delete();

        \App\Libraries\Audit::log('ml_allowed_remove', 'config', 'Removed sender from allowed list', [
            'sender' => $sender,
        ]);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Sender "' . $sender . '" removed from the allowed list.',
        ]);
    }

    /**
     * Clear the whole allowed list so the ML backend returns to its
     * hardcoded fallback defaults.
     */
    public function allowedReset()
    {
        $db = \Config\Database::connect();
        $db->table('tbl_Allowed_Senders')->truncate();

        \App\Libraries\Audit::log('ml_allowed_reset', 'config', 'Cleared allowed list to use hardcoded fallback');

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Allowed list cleared. The ML backend will fall back to its hardcoded defaults.',
        ]);
    }

    private function loadAllowed(): array
    {
        $db = \Config\Database::connect();
        if (!$db->tableExists('tbl_Allowed_Senders')) {
            return [];
        }

        return $db->table('tbl_Allowed_Senders')
            ->select('sender, category, created_at')
            ->orderBy('sender', 'ASC')
            ->get()
            ->getResultArray();
    }

    private function isFallbackActive(): bool
    {
        $db = \Config\Database::connect();
        if (!$db->tableExists('tbl_Allowed_Senders')) {
            return true;
        }

        return $db->table('tbl_Allowed_Senders')->countAllResults() === 0;
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
