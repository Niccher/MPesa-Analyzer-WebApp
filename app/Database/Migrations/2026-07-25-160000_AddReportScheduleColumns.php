<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddReportScheduleColumns extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('tbl_User_Settings')) {
            $fields = $this->db->getFieldNames('tbl_User_Settings');
            $add = [];
            if (!in_array('report_schedule_enabled', $fields, true)) {
                $add['report_schedule_enabled'] = ['type' => 'BOOLEAN', 'default' => false];
            }
            if (!in_array('report_schedule_frequency', $fields, true)) {
                $add['report_schedule_frequency'] = ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'monthly'];
            }
            if (!in_array('report_schedule_email', $fields, true)) {
                $add['report_schedule_email'] = ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true];
            }
            if (!in_array('report_schedule_day', $fields, true)) {
                $add['report_schedule_day'] = ['type' => 'INT', 'constraint' => 2, 'default' => 1];
            }
            if (!in_array('report_schedule_format', $fields, true)) {
                $add['report_schedule_format'] = ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'pdf'];
            }
            if (!empty($add)) {
                $this->forge->addColumn('tbl_User_Settings', $add);
            }
        }
    }

    public function down()
    {
        if ($this->db->tableExists('tbl_User_Settings')) {
            $fields = $this->db->getFieldNames('tbl_User_Settings');
            foreach (['report_schedule_enabled', 'report_schedule_frequency', 'report_schedule_email', 'report_schedule_day', 'report_schedule_format'] as $col) {
                if (in_array($col, $fields, true)) {
                    $this->forge->dropColumn('tbl_User_Settings', $col);
                }
            }
        }
    }
}
