<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Shield\Authentication\Authenticators\AccessTokens;

/**
 * Adds sms_user_id to tbl_Sms, backfills it from auth_identities, dedupes
 * historical rows and installs the stable per-device unique key.
 *
 * Idempotent: column/index additions are skipped when already present.
 */
class AddUserIdToTblSms extends Migration
{
    public function up()
    {
        $columnAdded = false;
        if (!$this->db->fieldExists('sms_user_id', 'tbl_Sms')) {
            $this->forge->addColumn('tbl_Sms', [
                'sms_user_id' => [
                    'type'     => 'INT',
                    'unsigned' => true,
                    'null'     => true,
                    'after'    => 'sms_owner',
                ],
            ]);
            $this->forge->addKey('sms_user_id', false, false, 'idx_sms_user_id');
            $this->forge->processIndexes('tbl_Sms');
            $columnAdded = true;
        }

        // Backfill user_id from auth_identities where the raw token still maps.
        $tokenType = AccessTokens::ID_TYPE_ACCESS_TOKEN;
        $this->db->query(
            'UPDATE tbl_Sms s
                LEFT JOIN auth_identities i
                    ON i.type = ? AND i.secret = SHA2(s.sms_owner, 256)
                SET s.sms_user_id = i.user_id',
            [$tokenType]
        );

        // Dedupe historical rows then add the stable per-device unique key.
        $this->db->query(
            "DELETE t1 FROM tbl_Sms t1
                INNER JOIN tbl_Sms t2
                    ON t1.sms_device = t2.sms_device AND t1.sms__id = t2.sms__id
                WHERE t1.id > t2.id
                  AND t1.sms_device IS NOT NULL
                  AND t1.sms__id IS NOT NULL AND t1.sms__id <> ''"
        );

        if (!$this->indexExists('tbl_Sms', 'uq_tbl_Sms_sms_device_sms__id')) {
            $this->forge->addUniqueKey(['sms_device', 'sms__id'], 'uq_tbl_Sms_sms_device_sms__id');
            $this->forge->processIndexes('tbl_Sms');
        }
    }

    public function down()
    {
        $this->db->query('ALTER TABLE tbl_Sms DROP INDEX uq_tbl_Sms_sms_device_sms__id, DROP COLUMN sms_user_id');
    }

    private function indexExists(string $table, string $index): bool
    {
        $row = $this->db->query(
            'SELECT COUNT(*) AS c FROM information_schema.statistics
             WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?',
            [$table, $index]
        )->getRow();
        return (int) ($row->c ?? 0) > 0;
    }
}
