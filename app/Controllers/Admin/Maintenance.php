<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class Maintenance extends BaseController
{
    use ResponseTrait;

    public function index()
    {
        $db = \Config\Database::connect();
        $settings = $db->table('tbl_Settings')
            ->whereIn('`key`', ['maintenance_mode', 'maintenance_message', 'maintenance_scheduled', 'maintenance_schedule_start', 'maintenance_schedule_end', 'maintenance_allowed_ips'])
            ->get()
            ->getResultArray();

        $config = [];
        foreach ($settings as $s) {
            $config[$s['key']] = $s['value'];
        }

        $isMaintenance = (bool)($config['maintenance_mode'] ?? false);
        $scheduled = (bool)($config['maintenance_scheduled'] ?? false);
        $scheduleStart = $config['maintenance_schedule_start'] ?? '';
        $scheduleEnd = $config['maintenance_schedule_end'] ?? '';
        $allowedIps = $config['maintenance_allowed_ips'] ?? '';

        $data = [
            'is_maintenance' => $isMaintenance,
            'maintenance_message' => $config['maintenance_message'] ?? 'We are performing scheduled maintenance. Please check back soon.',
            'scheduled' => $scheduled,
            'schedule_start' => $scheduleStart,
            'schedule_end' => $scheduleEnd,
            'allowed_ips' => $allowedIps,
            'bg_color' => '#B1B8ED',
        ];

        // Tab data: DB info
        $tables = $db->listTables();
        $tableInfo = [];
        foreach ($tables as $table) {
            $fields = $db->getFieldNames($table);
            $count = $db->table($table)->countAllResults(false);
            $tableInfo[] = [
                'name' => $table,
                'fields' => count($fields),
                'rows' => $count,
                'field_list' => implode(', ', array_slice($fields, 0, 10)) . (count($fields) > 10 ? '...' : ''),
            ];
        }

        $dbSize = $db->query("
            SELECT table_schema AS db_name,
                   ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
            FROM information_schema.tables
            WHERE table_schema = DATABASE()
            GROUP BY table_schema
        ")->getRow();

        $data['tables'] = $tableInfo;
        $data['total_tables'] = count($tables);
        $data['db_size_mb'] = $dbSize->size_mb ?? 'Unknown';
        $data['db_name'] = $dbSize->db_name ?? 'Unknown';

        // Tab data: cron jobs
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
            'cleanup_old_sessions' => ['schedule' => '0 2 * * *', 'command' => 'php spark session:gc', 'enabled' => true, 'description' => 'Clean expired sessions daily at 2 AM'],
            'process_pending_llm' => ['schedule' => '*/5 * * * *', 'command' => 'php spark llm:process', 'enabled' => true, 'description' => 'Process pending LLM jobs every 5 minutes'],
            'cleanup_old_uploads' => ['schedule' => '0 3 * * 0', 'command' => 'php spark uploads:cleanup', 'enabled' => false, 'description' => 'Clean old uploads weekly'],
            'send_scheduled_reports' => ['schedule' => '0 6 * * 1', 'command' => 'php spark reports:send', 'enabled' => true, 'description' => 'Send weekly reports Monday 6 AM'],
        ];

        foreach ($defaults as $key => $def) {
            if (!isset($cronJobs[$key])) {
                $cronJobs[$key] = $def;
            }
        }

        $data['cron_jobs'] = $cronJobs;

        return view('Admin/Maintenance/index', $data);
    }

    public function toggle()
    {
        $db = \Config\Database::connect();
        $enabled = (bool)$this->request->getPost('maintenance_mode');

        $db->table('tbl_Settings')->upsert([
            'key' => 'maintenance_mode',
            'value' => $enabled ? '1' : '0',
            'type' => 'boolean',
            'description' => 'Enable maintenance mode',
        ]);

        // If disabling, also clear scheduled
        if (!$enabled) {
            $db->table('tbl_Settings')->upsert([
                'key' => 'maintenance_scheduled',
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Scheduled maintenance enabled',
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => $enabled ? 'Maintenance mode enabled' : 'Maintenance mode disabled',
            'maintenance_mode' => $enabled,
        ]);
    }

    public function schedule()
    {
        $db = \Config\Database::connect();

        $enabled = (bool)$this->request->getPost('scheduled');
        $start = $this->request->getPost('schedule_start');
        $end = $this->request->getPost('schedule_end');
        $message = $this->request->getPost('maintenance_message');
        $allowedIps = $this->request->getPost('allowed_ips');

        $updates = [
            ['key' => 'maintenance_scheduled', 'value' => $enabled ? '1' : '0', 'type' => 'boolean', 'description' => 'Scheduled maintenance enabled'],
            ['key' => 'maintenance_schedule_start', 'value' => $start, 'type' => 'string', 'description' => 'Maintenance window start (UTC)'],
            ['key' => 'maintenance_schedule_end', 'value' => $end, 'type' => 'string', 'description' => 'Maintenance window end (UTC)'],
            ['key' => 'maintenance_message', 'value' => $message, 'type' => 'string', 'description' => 'Maintenance mode message'],
            ['key' => 'maintenance_allowed_ips', 'value' => $allowedIps, 'type' => 'string', 'description' => 'Comma-separated IPs allowed during maintenance'],
        ];

        foreach ($updates as $u) {
            $db->table('tbl_Settings')->upsert($u);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Maintenance schedule saved',
        ]);
    }

    public function dbInfo()
    {
        $db = \Config\Database::connect();

        $tables = $db->listTables();
        $tableInfo = [];

        foreach ($tables as $table) {
            $fields = $db->getFieldNames($table);
            $count = $db->table($table)->countAllResults(false);
            $tableInfo[] = [
                'name' => $table,
                'fields' => count($fields),
                'rows' => $count,
                'field_list' => implode(', ', array_slice($fields, 0, 10)) . (count($fields) > 10 ? '...' : ''),
            ];
        }

        $dbSize = $db->query("
            SELECT table_schema AS db_name,
                   ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
            FROM information_schema.tables
            WHERE table_schema = DATABASE()
            GROUP BY table_schema
        ")->getRow();

        $data = [
            'tables' => $tableInfo,
            'total_tables' => count($tables),
            'db_size_mb' => $dbSize->size_mb ?? 'Unknown',
            'db_name' => $dbSize->db_name ?? 'Unknown',
            'bg_color' => '#B1B8ED',
        ];

        return view('Admin/Maintenance/db_info', $data);
    }

    public function cron()
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

        // Default cron jobs if not configured
        $defaults = [
            'cleanup_old_sessions' => ['schedule' => '0 2 * * *', 'command' => 'php spark session:gc', 'enabled' => true, 'description' => 'Clean expired sessions daily at 2 AM'],
            'process_pending_llm' => ['schedule' => '*/5 * * * *', 'command' => 'php spark llm:process', 'enabled' => true, 'description' => 'Process pending LLM jobs every 5 minutes'],
            'cleanup_old_uploads' => ['schedule' => '0 3 * * 0', 'command' => 'php spark uploads:cleanup', 'enabled' => false, 'description' => 'Clean old uploads weekly'],
            'send_scheduled_reports' => ['schedule' => '0 6 * * 1', 'command' => 'php spark reports:send', 'enabled' => true, 'description' => 'Send weekly reports Monday 6 AM'],
        ];

        foreach ($defaults as $key => $def) {
            if (!isset($cronJobs[$key])) {
                $cronJobs[$key] = $def;
            }
        }

        $data = [
            'cron_jobs' => $cronJobs,
            'bg_color' => '#B1B8ED',
        ];

        return view('Admin/Maintenance/cron', $data);
    }

    public function runCron()
    {
        $jobKey = $this->request->getPost('job_key');
        $commands = [
            'cleanup_old_sessions' => 'session:gc',
            'process_pending_llm' => 'llm:process',
            'cleanup_old_uploads' => 'uploads:cleanup',
            'send_scheduled_reports' => 'reports:send',
        ];

        if (!isset($commands[$jobKey])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid job']);
        }

        // Run the spark command
        $output = [];
        $returnVar = 0;
        $command = 'cd /var/www/html && php spark ' . $commands[$jobKey] . ' 2>&1';
        exec($command, $output, $returnVar);

        // Update last run time
        $db = \Config\Database::connect();
        $db->table('tbl_Settings')->upsert([
            'key' => 'cron_' . $jobKey,
            'value' => json_encode([
                'last_run' => date('Y-m-d H:i:s'),
                'last_status' => $returnVar === 0 ? 'success' : 'failed',
                'last_output' => implode("\n", array_slice($output, -10)),
            ]),
            'type' => 'json',
            'description' => 'Cron job: ' . $jobKey,
        ]);

        return $this->response->setJSON([
            'status' => $returnVar === 0 ? 'success' : 'error',
            'message' => $returnVar === 0 ? 'Job completed successfully' : 'Job failed',
            'output' => implode("\n", $output),
        ]);
    }
}