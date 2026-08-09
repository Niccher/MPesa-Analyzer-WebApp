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
            ->whereIn('`key`', ['maintenance_mode', 'maintenance_message', 'maintenance_scheduled', 'maintenance_schedule_start', 'maintenance_schedule_end'])
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

        // Maintenance history (most recent first)
        $history = $db->table('tbl_Maintenance_Log')
            ->orderBy('id', 'DESC')
            ->limit(200)
            ->get()
            ->getResultArray();

        $data = [
            'is_maintenance' => $isMaintenance,
            'maintenance_message' => $config['maintenance_message'] ?? 'We are performing scheduled maintenance. Please check back soon.',
            'scheduled' => $scheduled,
            'schedule_start' => $scheduleStart,
            'schedule_end' => $scheduleEnd,
            'maintenance_history' => $history,
            'bg_color' => '#B1B8ED',
        ];

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

        $message = trim((string)$this->request->getPost('maintenance_message'));
        if ($message !== '') {
            $db->table('tbl_Settings')->upsert([
                'key' => 'maintenance_message',
                'value' => $message,
                'type' => 'string',
                'description' => 'Maintenance mode message',
            ]);
        }

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

        $updates = [
            ['key' => 'maintenance_scheduled', 'value' => $enabled ? '1' : '0', 'type' => 'boolean', 'description' => 'Scheduled maintenance enabled'],
            ['key' => 'maintenance_schedule_start', 'value' => $start, 'type' => 'string', 'description' => 'Maintenance window start (UTC)'],
            ['key' => 'maintenance_schedule_end', 'value' => $end, 'type' => 'string', 'description' => 'Maintenance window end (UTC)'],
            ['key' => 'maintenance_message', 'value' => $message, 'type' => 'string', 'description' => 'Maintenance mode message'],
        ];

        foreach ($updates as $u) {
            $db->table('tbl_Settings')->upsert($u);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Maintenance schedule saved',
        ]);
    }
}