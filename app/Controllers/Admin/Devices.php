<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ModDevices;
use CodeIgniter\API\ResponseTrait;

class Devices extends BaseController
{
    use ResponseTrait;

    public function index()
    {
        $mod = new ModDevices();
        $all = $mod->getAllDevices();

        $data = [
            'devices' => $all['devices'],
            'orphans' => $all['orphans'],
            'bg_color' => '#B1B8ED',
        ];

        return view('Admin/Devices/index', $data);
    }

    public function detail(string $deviceUuid)
    {
        $mod     = new ModDevices();
        $metrics = $mod->getDeviceMetrics(null, $deviceUuid);

        if ($metrics === null) {
            return redirect()->to(base_url('admin/devices'))->with('error', 'Device not found.');
        }

        $data = [
            'metrics'  => $metrics,
            'bg_color' => '#B1B8ED',
        ];

        return view('Admin/Devices/detail', $data);
    }
}
