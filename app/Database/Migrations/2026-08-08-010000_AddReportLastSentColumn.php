<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddReportLastSentColumn extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('tbl_User_Settings')) {
            $fields = $this->db->getFieldNames('tbl_User_Settings');
            if (!in_array('report_schedule_last_sent', $fields, true)) {
                $this->forge->addColumn('tbl_User_Settings', [
                    'report_schedule_last_sent' => ['type' => 'DATE', 'null' => true],
                ]);
            }
        }
    }

    public function down()
    {
        if ($this->db->tableExists('tbl_User_Settings')) {
            $fields = $this->db->getFieldNames('tbl_User_Settings');
            if (in_array('report_schedule_last_sent', $fields, true)) {
                $this->forge->dropColumn('tbl_User_Settings', 'report_schedule_last_sent');
            }
        }
    }
}
