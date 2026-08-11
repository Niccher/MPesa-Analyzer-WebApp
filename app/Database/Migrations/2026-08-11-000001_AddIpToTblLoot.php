<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Adds loot_ip (caller IP) to tbl_Loot for audit / device tracking.
 *
 * Idempotent: skipped when the column already exists.
 */
class AddIpToTblLoot extends Migration
{
    public function up()
    {
        if ($this->db->fieldExists('loot_ip', 'tbl_Loot')) {
            return;
        }

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
    }

    public function down()
    {
        $this->db->query('ALTER TABLE tbl_Loot DROP INDEX idx_loot_ip, DROP COLUMN loot_ip');
    }
}
