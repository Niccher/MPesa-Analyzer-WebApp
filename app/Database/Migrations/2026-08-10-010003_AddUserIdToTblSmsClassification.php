<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Adds user_id to tbl_Sms_Classification and backfills it from the SMS
 * owner (requires tbl_Sms.sms_user_id to be populated first).
 *
 * Idempotent: column/index additions are skipped when already present.
 */
class AddUserIdToTblSmsClassification extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('user_id', 'tbl_Sms_Classification')) {
            $this->forge->addColumn('tbl_Sms_Classification', [
                'user_id' => [
                    'type'     => 'INT',
                    'unsigned' => true,
                    'null'     => true,
                    'after'    => 'sms_id',
                ],
            ]);
            $this->forge->addKey('user_id', false, false, 'idx_classification_user_id');
            $this->forge->processIndexes('tbl_Sms_Classification');
        }

        $this->db->query(
            'UPDATE tbl_Sms_Classification c
                JOIN tbl_Sms s ON s.id = c.sms_id
                SET c.user_id = s.sms_user_id'
        );
    }

    public function down()
    {
        $this->db->query('ALTER TABLE tbl_Sms_Classification DROP COLUMN user_id');
    }
}
