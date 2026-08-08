<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBudgetRolloverColumn extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('tbl_Budgets')) {
            $fields = $this->db->getFieldNames('tbl_Budgets');
            if (!in_array('rollover', $fields, true)) {
                $this->forge->addColumn('tbl_Budgets', [
                    'rollover' => ['type' => 'BOOLEAN', 'default' => false, 'after' => 'period'],
                ]);
            }
            if (!in_array('user_id', $fields, true)) {
                $this->forge->addColumn('tbl_Budgets', [
                    'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'after' => 'id'],
                ]);
            }
        }
    }

    public function down()
    {
        if ($this->db->tableExists('tbl_Budgets')) {
            $fields = $this->db->getFieldNames('tbl_Budgets');
            if (in_array('rollover', $fields, true)) {
                $this->forge->dropColumn('tbl_Budgets', 'rollover');
            }
            if (in_array('user_id', $fields, true)) {
                $this->forge->dropColumn('tbl_Budgets', 'user_id');
            }
        }
    }
}
