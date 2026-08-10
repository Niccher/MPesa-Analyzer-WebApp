<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Adds IP capture so uploads and device registrations record the caller's IP
 * for audit / device tracking. Historical rows stay NULL (forward-fill only).
 */
class AddDeviceTrackingColumns extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tbl_Loot', [
            'loot_ip' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => true,
                'after'      => 'loot_user_id',
            ],
        ]);
        $this->forge->addKey('loot_ip', false, false, 'idx_loot_ip');
        $this->forge->processIndexes('tbl_Loot');

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
        $this->db->query('ALTER TABLE tbl_Loot DROP INDEX idx_loot_ip, DROP COLUMN loot_ip');
        $this->db->query('ALTER TABLE tbl_Devices DROP INDEX idx_device_ip, DROP COLUMN device_ip');
    }
}
