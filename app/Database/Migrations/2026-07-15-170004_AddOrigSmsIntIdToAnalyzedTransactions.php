<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Adds orig_sms_int_id (FK to tbl_Sms) on tbl_Analyzed_Transactions.
 *
 * Idempotent: only added if it does not already exist, so this migration is
 * safe on environments that previously ran the legacy combined
 * ExtendTblSmsForFinance migration.
 */
class AddOrigSmsIntIdToAnalyzedTransactions extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('orig_sms_int_id', 'tbl_Analyzed_Transactions')) {
            $this->forge->addColumn('tbl_Analyzed_Transactions', [
                'orig_sms_int_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'orig_sms_id',
                ],
            ]);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('tbl_Analyzed_Transactions', ['orig_sms_int_id']);
    }
}
