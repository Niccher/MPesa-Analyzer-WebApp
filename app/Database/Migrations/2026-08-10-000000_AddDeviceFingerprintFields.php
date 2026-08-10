<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDeviceFingerprintFields extends Migration
{
    public function up()
    {
        // Permission-free signals collected by the Android app to make the
        // device fingerprint unique per physical handset (see DeviceFingerprint.kt)
        $this->forge->addColumn('tbl_Devices', [
            'device_AndroidId'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'after' => 'device_Serial'],
            'device_AppCertHash'      => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'device_AppVersion'       => ['type' => 'VARCHAR', 'constraint' => 50,  'null' => true],
            'device_FirstInstallTime' => ['type' => 'VARCHAR', 'constraint' => 50,  'null' => true],
            'device_LastUpdateTime'   => ['type' => 'VARCHAR', 'constraint' => 50,  'null' => true],
            'device_Sensors'          => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'device_ScreenWidth'      => ['type' => 'INT',    'null' => true],
            'device_ScreenHeight'     => ['type' => 'INT',    'null' => true],
            'device_DensityDpi'       => ['type' => 'INT',    'null' => true],
            'device_Xdpi'             => ['type' => 'FLOAT',  'null' => true],
            'device_Ydpi'             => ['type' => 'FLOAT',  'null' => true],
            'device_Locale'           => ['type' => 'VARCHAR', 'constraint' => 50,  'null' => true],
            'device_Timezone'         => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'device_CpuCount'         => ['type' => 'INT',    'null' => true],
            'device_Abis'             => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'device_StorageTotal'     => ['type' => 'BIGINT', 'null' => true],
            'device_StorageAvailable' => ['type' => 'BIGINT', 'null' => true],
            'device_BatteryCapacity'  => ['type' => 'INT',    'null' => true],
        ]);

        $this->forge->addKey('device_AndroidId');
        $this->forge->processIndexes('tbl_Devices');
    }

    public function down()
    {
        $this->forge->dropColumn('tbl_Devices', [
            'device_AndroidId',
            'device_AppCertHash',
            'device_AppVersion',
            'device_FirstInstallTime',
            'device_LastUpdateTime',
            'device_Sensors',
            'device_ScreenWidth',
            'device_ScreenHeight',
            'device_DensityDpi',
            'device_Xdpi',
            'device_Ydpi',
            'device_Locale',
            'device_Timezone',
            'device_CpuCount',
            'device_Abis',
            'device_StorageTotal',
            'device_StorageAvailable',
            'device_BatteryCapacity',
        ]);
    }
}
