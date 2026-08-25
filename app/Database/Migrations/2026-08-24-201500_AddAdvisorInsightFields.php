<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAdvisorInsightFields extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('tbl_Sms')) {
            return;
        }

        $fields = $this->db->getFieldNames('tbl_Sms');
        $newFields = [];

        if (!in_array('sms_counterparty_type', $fields, true)) {
            $newFields['sms_counterparty_type'] = [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ];
        }

        if (!in_array('sms_is_abnormal', $fields, true)) {
            $newFields['sms_is_abnormal'] = [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => true,
            ];
        }

        if (!in_array('sms_normality_assessment', $fields, true)) {
            $newFields['sms_normality_assessment'] = [
                'type' => 'TEXT',
                'null' => true,
            ];
        }

        if (!in_array('sms_savings_impact', $fields, true)) {
            $newFields['sms_savings_impact'] = [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ];
        }

        if (!in_array('sms_advisor_insight', $fields, true)) {
            $newFields['sms_advisor_insight'] = [
                'type' => 'TEXT',
                'null' => true,
            ];
        }

        if (!empty($newFields)) {
            $this->forge->addColumn('tbl_Sms', $newFields);
        }
    }

    public function down()
    {
        if (!$this->db->tableExists('tbl_Sms')) {
            return;
        }

        $this->forge->dropColumn('tbl_Sms', [
            'sms_counterparty_type',
            'sms_is_abnormal',
            'sms_normality_assessment',
            'sms_savings_impact',
            'sms_advisor_insight'
        ]);
    }
}
