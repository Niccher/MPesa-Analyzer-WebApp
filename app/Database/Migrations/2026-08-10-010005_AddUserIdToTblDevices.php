<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Adds device_user_id to tbl_Devices and backfills it from the device that
 * uploaded data (requires tbl_Loot.loot_user_id to be populated first).
 *
 * Idempotent: column/index additions are skipped when already present.
 */
class AddUserIdToTblDevices extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('device_user_id', 'tbl_Devices')) {
            $this->forge->addColumn('tbl_Devices', [
                'device_user_id' => [
                    'type'     => 'INT',
                    'unsigned' => true,
                    'null'     => true,
                    'after'    => 'device_Uuid',
                ],
            ]);
            $this->forge->addKey('device_user_id', false, false, 'idx_device_user_id');
            $this->forge->processIndexes('tbl_Devices');
        }

        // Devices carry no token of their own; derive from the device that uploaded data.
        $this->db->query(
            'UPDATE tbl_Devices d
                LEFT JOIN (
                    SELECT loot_Device, MAX(loot_user_id) AS uid
                    FROM tbl_Loot
                    WHERE loot_user_id IS NOT NULL
                    GROUP BY loot_Device
                ) lu ON lu.loot_Device = d.device_Uuid
                SET d.device_user_id = lu.uid'
        );
    }

    public function down()
    {
        $this->db->query('ALTER TABLE tbl_Devices DROP COLUMN device_user_id');
    }
}
