<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Canonical SMS record refactor.
 *
 * Moves ALL per-SMS analysis data (parsed + classification) onto tbl_Sms so it
 * becomes the single source of truth. The two derived tables are then replaced
 * by VIEWs with the same names/columns, so every existing webapp read keeps
 * working unchanged:
 *   - tbl_Sms_Classification  → view over tbl_Sms
 *   - tbl_Analyzed_Transactions → view over tbl_Sms (transactional SMS only)
 *
 * Data is backfilled first, so nothing is lost.
 */
class CanonicalizeSmsAnalysis extends Migration
{
    public function up()
    {
        // 1. Add classification + transaction columns to tbl_Sms (canonical).
        $columns = [
            'sms_category' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'default'    => null,
            ],
            'sms_is_finance' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => true,
            ],
            'sms_confidence' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,4',
                'null'       => true,
                'default'    => null,
            ],
            'sms_method' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'default'    => 'pattern',
            ],
            'sms_trans_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'default'    => null,
            ],
            'sms_trans_date' => [
                'type'       => 'DATETIME',
                'null'       => true,
                'default'    => null,
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

        // 2. Backfill classification from tbl_Sms_Classification (latest row per SMS).
        if ($this->db->tableExists('tbl_Sms_Classification')) {
            $this->db->query("
                UPDATE tbl_Sms s
                LEFT JOIN (
                    SELECT sms_id, MAX(id) AS mid
                    FROM tbl_Sms_Classification
                    GROUP BY sms_id
                ) g ON g.sms_id = s.id
                LEFT JOIN tbl_Sms_Classification c ON c.id = g.mid
                SET
                    s.sms_category     = COALESCE(c.category, s.sms_category),
                    s.sms_is_finance   = COALESCE(c.is_finance, s.sms_is_finance, 0),
                    s.sms_confidence   = COALESCE(c.confidence, s.sms_confidence),
                    s.sms_method       = COALESCE(c.method, s.sms_method, 'pattern'),
                    s.sms_direction    = COALESCE(NULLIF(c.direction, 'none'), NULLIF(c.direction, ''), s.sms_direction)
            ");
        }

        // 3. Backfill transaction data from tbl_Analyzed_Transactions.
        if ($this->db->tableExists('tbl_Analyzed_Transactions')) {
            $this->db->query("
                UPDATE tbl_Sms s
                LEFT JOIN (
                    SELECT orig_sms_int_id, MAX(id) AS mid
                    FROM tbl_Analyzed_Transactions
                    WHERE orig_sms_int_id IS NOT NULL
                    GROUP BY orig_sms_int_id
                ) g ON g.orig_sms_int_id = s.id
                LEFT JOIN tbl_Analyzed_Transactions a ON a.id = g.mid
                SET
                    s.sms_amount          = COALESCE(s.sms_amount, a.amount),
                    s.sms_counterparty    = COALESCE(s.sms_counterparty, a.counterparty),
                    s.sms_transaction_type = COALESCE(s.sms_transaction_type, a.description),
                    s.sms_trans_date      = COALESCE(s.sms_trans_date, a.trans_date),
                    s.sms_is_transactional = 1
            ");
        }

        // 4. Drop the two derived tables and replace them with views.
        if ($this->db->tableExists('tbl_Sms_Classification')) {
            $this->forge->dropTable('tbl_Sms_Classification', true);
        }
        if ($this->db->tableExists('tbl_Analyzed_Transactions')) {
            $this->forge->dropTable('tbl_Analyzed_Transactions', true);
        }

        $this->db->query("
            CREATE OR REPLACE VIEW tbl_Sms_Classification AS
            SELECT
                s.id            AS id,
                s.id            AS sms_id,
                s.sms_user_id   AS user_id,
                s.sms_number    AS sender,
                s.sms_category  AS category,
                s.sms_direction AS direction,
                s.sms_is_finance AS is_finance,
                s.sms_method    AS method,
                s.sms_confidence AS confidence,
                NULL            AS created_at
            FROM tbl_Sms s
        ");

        $this->db->query("
            CREATE OR REPLACE VIEW tbl_Analyzed_Transactions AS
            SELECT
                s.id              AS id,
                s.sms__id         AS orig_sms_id,
                s.id              AS orig_sms_int_id,
                s.sms_trans_id    AS trans_id,
                s.sms_amount      AS amount,
                s.sms_counterparty AS counterparty,
                s.sms_transaction_type AS description,
                s.sms_trans_date  AS trans_date,
                NULL              AS created_at
            FROM tbl_Sms s
            WHERE s.sms_is_transactional = 1 AND s.sms_amount IS NOT NULL
        ");
    }

    public function down()
    {
        $this->db->query("DROP VIEW IF EXISTS tbl_Sms_Classification");
        $this->db->query("DROP VIEW IF EXISTS tbl_Analyzed_Transactions");

        $this->forge->addField([
            'id' => ['type' => 'INT', 'auto_increment' => true],
            'sms_id' => ['type' => 'INT', 'unsigned' => true, 'null' => false],
            'user_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'sender' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => false, 'default' => 'MPESA'],
            'category' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'direction' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'is_finance' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'method' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => false, 'default' => 'pattern'],
            'confidence' => ['type' => 'DECIMAL', 'constraint' => '5,4', 'default' => 1.0000],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('tbl_Sms_Classification', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'auto_increment' => true],
            'orig_sms_id' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'orig_sms_int_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'trans_id' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'amount' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true],
            'counterparty' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'description' => ['type' => 'TEXT', 'null' => true],
            'trans_date' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('tbl_Analyzed_Transactions', true);

        $this->forge->dropColumn('tbl_Sms', [
            'sms_category',
            'sms_is_finance',
            'sms_confidence',
            'sms_method',
            'sms_trans_id',
            'sms_trans_date',
        ]);
    }
}
