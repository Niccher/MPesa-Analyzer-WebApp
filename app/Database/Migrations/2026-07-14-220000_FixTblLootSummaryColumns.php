<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Align tbl_Loot_Summary columns with ModUploads / dashboard expectations.
 * - Code writes info_Get_from_MPESA and info_All
 * - Original migration only created info_Get_Received (no info_All)
 */
class FixTblLootSummaryColumns extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('tbl_Loot_Summary')) {
            return;
        }

        $fields = $this->db->getFieldNames('tbl_Loot_Summary');

        // Add the column the app expects for "received from MPESA"
        if (!in_array('info_Get_from_MPESA', $fields, true)) {
            $this->forge->addColumn('tbl_Loot_Summary', [
                'info_Get_from_MPESA' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 0,
                    'null'       => false,
                    'after'      => 'loot_Created',
                ],
            ]);

            // Backfill from legacy column if present
            if (in_array('info_Get_Received', $fields, true)) {
                $this->db->query(
                    'UPDATE tbl_Loot_Summary SET info_Get_from_MPESA = COALESCE(info_Get_Received, 0)'
                );
            }
        }

        // Total SMS count per upload
        $fields = $this->db->getFieldNames('tbl_Loot_Summary');
        if (!in_array('info_All', $fields, true)) {
            $this->forge->addColumn('tbl_Loot_Summary', [
                'info_All' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 0,
                    'null'       => false,
                    'after'      => 'info_Unknown',
                ],
            ]);
        }
    }

    public function down()
    {
        if (!$this->db->tableExists('tbl_Loot_Summary')) {
            return;
        }

        $fields = $this->db->getFieldNames('tbl_Loot_Summary');

        if (in_array('info_All', $fields, true)) {
            $this->forge->dropColumn('tbl_Loot_Summary', 'info_All');
        }

        // Keep info_Get_from_MPESA if info_Get_Received exists (safer rollback only drops when legacy present)
        if (
            in_array('info_Get_from_MPESA', $fields, true)
            && in_array('info_Get_Received', $fields, true)
        ) {
            $this->forge->dropColumn('tbl_Loot_Summary', 'info_Get_from_MPESA');
        }
    }
}
