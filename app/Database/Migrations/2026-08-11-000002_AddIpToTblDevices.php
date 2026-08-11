<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Adds device_ip (caller IP) to tbl_Devices for audit / device tracking.
 *
 * Idempotent: skipped when the column already exists.
 */
class AddIpToTblDevices extends Migration
{
    public function up()
    {
        if ($this->db->fieldExists('device_ip', 'tbl_Devices')) {
            return;
        }

        $this->forge->addColumn('tbl_Devices', [
            'device_ip' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => true,
                'after'      => 'device_user_id',
            ],
        ]);
        $this->forge->addKey('device_ip', false, false, 'idx_device_ip');
        $this->forge->processIndexes('tbl_Devices');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE tbl_Devices DROP INDEX idx_device_ip, DROP COLUMN device_ip');
    }
}
