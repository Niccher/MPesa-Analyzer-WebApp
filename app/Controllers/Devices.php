<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ModDevices;

class Devices extends BaseController
{
    public function index()
    {
        $userId = auth()->user()->id;
        $mod    = new ModDevices();

        $devices = $mod->getUserDevices($userId);

        $data = [
            'devices'   => $devices,
            'bg_color'  => '#B1B8ED',
            'page'      => 'devices',
        ];

        return view('Devices/index', $data);
    }

    public function detail(string $deviceUuid)
    {
        $userId = auth()->user()->id;
        $mod    = new ModDevices();

        $metrics = $mod->getDeviceMetrics($userId, $deviceUuid);
        if ($metrics === null) {
            return redirect()->to(base_url('dashboard/devices'))->with('error', 'Device not found.');
        }

        $data = [
            'metrics'  => $metrics,
            'bg_color' => '#B1B8ED',
            'page'     => 'devices',
        ];

        return view('Devices/detail', $data);
    }

    public function tokenUsage()
    {
        $userId = auth()->user()->id;
        $mod    = new ModDevices();

        $data = [
            'tokens'   => $mod->getTokenUsage($userId),
            'bg_color' => '#B1B8ED',
            'page'     => 'devices',
        ];

        return view('Devices/tokens', $data);
    }

    public function apiActivity()
    {
        $userId = auth()->user()->id;
        $mod    = new ModDevices();
        $limit  = (int) ($this->request->getGet('limit') ?: 25);
        $limit  = max(10, min($limit, 200));

        $data = [
            'activity' => $mod->getApiActivity($userId, $limit),
            'limit'    => $limit,
            'bg_color' => '#B1B8ED',
            'page'     => 'devices',
        ];

        return view('Devices/activity', $data);
    }
}
