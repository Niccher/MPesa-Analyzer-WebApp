<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddExtractedFinanceFields extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('tbl_Sms')) {
            return;
        }

        $fields = $this->db->getFieldNames('tbl_Sms');
        $newFields = [];

        if (!in_array('sms_fee', $fields, true)) {
            $newFields['sms_fee'] = [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
                'null'       => true,
            ];
        }

        if (!in_array('sms_is_reversal', $fields, true)) {
            $newFields['sms_is_reversal'] = [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => true,
            ];
        }

        if (!in_array('sms_is_loan', $fields, true)) {
            $newFields['sms_is_loan'] = [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => true,
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
            'sms_fee',
            'sms_is_reversal',
            'sms_is_loan'
        ]);
    }
}
