<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLLMBreakdownColumns extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('tbl_Loot_Summary')) {
            return;
        }

        $fields = $this->db->getFieldNames('tbl_Loot_Summary');

        // Add direction_breakdown column (JSON encoded map: "incoming" => count, "outgoing" => count, "none" => count)
        if (!in_array('direction_breakdown', $fields, true)) {
            $this->forge->addColumn('tbl_Loot_Summary', [
                'direction_breakdown' => [
                    'type'       => 'TEXT',
                    'null'       => true,
                    'after'      => 'info_Unknown',
                ],
            ]);
        }

        // Add transaction_type_breakdown column (JSON encoded map: "transfer" => count, "payment" => count, etc.)
        if (!in_array('transaction_type_breakdown', $fields, true)) {
            $this->forge->addColumn('tbl_Loot_Summary', [
                'transaction_type_breakdown' => [
                    'type'       => 'TEXT',
                    'null'       => true,
                    'after'      => 'direction_breakdown',
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

        if (in_array('direction_breakdown', $fields, true)) {
            $this->forge->dropColumn('tbl_Loot_Summary', 'direction_breakdown');
        }

        if (in_array('transaction_type_breakdown', $fields, true)) {
            $this->forge->dropColumn('tbl_Loot_Summary', 'transaction_type_breakdown');
        }
    }
}
