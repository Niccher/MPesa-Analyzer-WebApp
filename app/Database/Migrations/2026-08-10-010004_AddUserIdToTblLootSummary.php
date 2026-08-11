<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Adds user_id to tbl_Loot_Summary and backfills it from the parent loot
 * record (requires tbl_Loot.loot_user_id to be populated first).
 *
 * Idempotent: column/index additions are skipped when already present.
 */
class AddUserIdToTblLootSummary extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('user_id', 'tbl_Loot_Summary')) {
            $this->forge->addColumn('tbl_Loot_Summary', [
                'user_id' => [
                    'type'     => 'INT',
                    'unsigned' => true,
                    'null'     => true,
                    'after'    => 'loot_Uuid',
                ],
            ]);
            $this->forge->addKey('user_id', false, false, 'idx_summary_user_id');
            $this->forge->processIndexes('tbl_Loot_Summary');
        }

        $this->db->query(
            'UPDATE tbl_Loot_Summary ls
                JOIN tbl_Loot l ON l.loot_Uuid = ls.loot_Uuid
                SET ls.user_id = l.loot_user_id'
        );
    }

    public function down()
    {
        $this->db->query('ALTER TABLE tbl_Loot_Summary DROP COLUMN user_id');
    }
}
