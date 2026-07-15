<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ExtendTblSmsForFinance extends Migration
{
    public function up()
    {
        // ── 1. Add financial columns to tbl_Sms ─────────────────
        $this->forge->addColumn('tbl_Sms', [
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
        ]);

        // ── 2. Create tbl_Sender_Profiles ──────────────────────
        $this->forge->addField([
            'sp_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'sp_owner' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'sp_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'sp_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'sp_category' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'sp_is_finance' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'sp_confidence' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,4',
                'default'    => 0.0000,
            ],
            'sp_created' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'sp_updated' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('sp_id', true);
        $this->forge->addUniqueKey(['sp_owner', 'sp_number']);
        $this->forge->createTable('tbl_Sender_Profiles', true);

        // ── 3. Create tbl_Sms_Processing (tracking) ───────────
        $this->forge->addField([
            'sms_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'pending',
            ],
            'attempt_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'last_error' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'processed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('sms_id', true);
        $this->forge->addKey('status');
        $this->forge->createTable('tbl_Sms_Processing', true);

        // ── 4. Add orig_sms_int_id to tbl_Analyzed_Transactions ─
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
        $this->forge->dropTable('tbl_Sender_Profiles', true);
        $this->forge->dropTable('tbl_Sms_Processing', true);
        $this->forge->dropColumn('tbl_Analyzed_Transactions', ['orig_sms_int_id']);
    }
}
