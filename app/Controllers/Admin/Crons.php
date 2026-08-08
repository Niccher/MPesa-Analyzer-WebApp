<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class Crons extends BaseController
{
    use ResponseTrait;

    private const ALLOWED = ['cleanup_old_sessions', 'process_pending_llm', 'cleanup_old_uploads', 'send_scheduled_reports'];

    public function index()
    {
        $db = \Config\Database::connect();
        $jobs = $db->table('tbl_Settings')
            ->where('`key` LIKE', 'cron_%')
            ->get()
            ->getResultArray();

        $cronJobs = [];
        foreach ($jobs as $job) {
            $key = str_replace('cron_', '', $job['key']);
            $cronJobs[$key] = json_decode($job['value'] ?? '{}', true);
        }

        $defaults = [
            'cleanup_old_sessions' => ['schedule' => '0 2 * * *', 'command' => 'session:gc', 'enabled' => true, 'description' => 'Clean expired sessions daily at 2 AM'],
            'process_pending_llm' => ['schedule' => '*/5 * * * *', 'command' => 'llm:process', 'enabled' => true, 'description' => 'Process pending LLM jobs every 5 minutes'],
            'cleanup_old_uploads' => ['schedule' => '0 3 * * 0', 'command' => 'uploads:cleanup', 'enabled' => false, 'description' => 'Clean old uploads weekly'],
            'send_scheduled_reports' => ['schedule' => '0 6 * * 1', 'command' => 'reports:send', 'enabled' => true, 'description' => 'Send weekly reports Monday 6 AM'],
        ];

        foreach ($defaults as $key => $def) {
            if (!isset($cronJobs[$key])) {
                $cronJobs[$key] = $def;
            }
        }

        return view('Admin/Crons/index', [
            'bg_color' => '#B1B8ED',
            'cron_jobs' => $cronJobs,
        ]);
    }

    public function save()
    {
        $key = $this->request->getPost('job_key');
        $schedule = $this->request->getPost('schedule');
        $command = $this->request->getPost('command');
        $description = $this->request->getPost('description');
        $enabled = (bool)$this->request->getPost('enabled');

        if (!in_array($key, self::ALLOWED, true)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid job key']);
        }

        $existing = [];
        $db = \Config\Database::connect();
        $row = $db->table('tbl_Settings')->where('`key`', 'cron_' . $key)->get()->getRow();
        if ($row) {
            $existing = json_decode($row->value ?? '{}', true) ?: [];
        }

        $db->table('tbl_Settings')->upsert([
            'key' => 'cron_' . $key,
            'value' => json_encode(array_merge($existing, [
                'schedule' => $schedule,
                'command' => $command,
                'description' => $description,
                'enabled' => $enabled,
            ])),
            'type' => 'json',
            'description' => 'Cron job: ' . $key,
        ]);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Cron job saved. Update the host crontab to match the new schedule.',
        ]);
    }
}
