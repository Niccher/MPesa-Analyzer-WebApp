<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Adds financial-analysis columns to tbl_Sms.
 *
 * Idempotent: each column is only added if it does not already exist, so
 * this migration is safe on environments that previously ran the legacy
 * combined ExtendTblSmsForFinance migration.
 */
class AddFinanceColumnsToTblSms extends Migration
{
    public function up()
    {
        $columns = [
            'sms_direction' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'comment'    => 'sent | received | none',
            ],
            'sms_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'null'       => true,
            ],
            'sms_balance' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'null'       => true,
            ],
            'sms_counterparty' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'sms_transaction_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'sms_is_transactional' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => true,
            ],
        ];

        $missing = [];
        foreach (array_keys($columns) as $col) {
            if (!$this->db->fieldExists($col, 'tbl_Sms')) {
                $missing[$col] = $columns[$col];
            }
        }

        if (!empty($missing)) {
            $this->forge->addColumn('tbl_Sms', $missing);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('tbl_Sms', [
            'sms_direction',
            'sms_amount',
            'sms_balance',
            'sms_counterparty',
            'sms_transaction_type',
            'sms_is_transactional',
        ]);
    }
}
