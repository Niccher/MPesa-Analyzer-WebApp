<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Shield\Authentication\Authenticators\AccessTokens;

/**
 * Adds numeric user_id tracking columns to the data tables so rows can be
 * tracked per user without re-deriving ownership through auth_identities.
 * Also replaces the (previously not-applied) owner-based dedup with a stable
 * per-device unique key (sms_device, sms__id) that survives token rotation.
 */
class AddUserIdAndDedupKey extends Migration
{
    public function up()
    {
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

        $this->forge->addColumn('tbl_Loot', [
            'loot_user_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
                'after'    => 'loot_Owner',
            ],
        ]);
        $this->forge->addKey('loot_user_id', false, false, 'idx_loot_user_id');
        $this->forge->processIndexes('tbl_Loot');

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

        $this->forge->addColumn('tbl_Loot_Summary', [
            'user_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
                'after'    => 'loot_Uuid',
            ],
        ]);
        $this->forge->addKey('user_id', false, false, 'idx_summary_user_id');
        $this->forge->processIndexes('tbl_Loot_Summary');

        $this->forge->addColumn('tbl_Devices', [
            'device_user_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
                'after'    => 'device_Uuid',
            ],
        ]);
        $this->forge->addKey('device_user_id', false, false, 'idx_device_user_id');
        $this->forge->processIndexes('tbl_Devices');

        // --- Backfill user_id from auth_identities where the raw token still maps ---
        $tokenType = AccessTokens::ID_TYPE_ACCESS_TOKEN;

        $this->db->query(
            'UPDATE tbl_Sms s
                LEFT JOIN auth_identities i
                    ON i.type = ? AND i.secret = SHA2(s.sms_owner, 256)
                SET s.sms_user_id = i.user_id',
            [$tokenType]
        );

        $this->db->query(
            'UPDATE tbl_Loot l
                LEFT JOIN auth_identities i
                    ON i.type = ? AND i.secret = SHA2(l.loot_Owner, 256)
                SET l.loot_user_id = i.user_id',
            [$tokenType]
        );

        $this->db->query(
            'UPDATE tbl_Sms_Classification c
                JOIN tbl_Sms s ON s.id = c.sms_id
                SET c.user_id = s.sms_user_id'
        );

        $this->db->query(
            'UPDATE tbl_Loot_Summary ls
                JOIN tbl_Loot l ON l.loot_Uuid = ls.loot_Uuid
                SET ls.user_id = l.loot_user_id'
        );

        // Devices carry no token of their own; derive from the device that uploaded data
        $this->db->query(
            'UPDATE tbl_Devices d
                LEFT JOIN (
                    SELECT loot_Device, MAX(loot_user_id) AS uid
                    FROM tbl_Loot
                    WHERE loot_user_id IS NOT NULL
                    GROUP BY loot_Device
                ) lu ON lu.loot_Device = d.device_Uuid
                SET d.device_user_id = lu.uid'
        );

        // --- Dedupe historical rows then add the stable per-device unique key ---
        $this->db->query(
            "DELETE t1 FROM tbl_Sms t1
                INNER JOIN tbl_Sms t2
                    ON t1.sms_device = t2.sms_device AND t1.sms__id = t2.sms__id
                WHERE t1.id > t2.id
                  AND t1.sms_device IS NOT NULL
                  AND t1.sms__id IS NOT NULL AND t1.sms__id <> ''"
        );

        $this->forge->addUniqueKey(['sms_device', 'sms__id'], 'uq_tbl_Sms_sms_device_sms__id');
        $this->forge->processIndexes('tbl_Sms');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE tbl_Sms DROP INDEX uq_tbl_Sms_sms_device_sms__id, DROP COLUMN sms_user_id');
        $this->db->query('ALTER TABLE tbl_Loot DROP COLUMN loot_user_id');
        $this->db->query('ALTER TABLE tbl_Sms_Classification DROP COLUMN user_id');
        $this->db->query('ALTER TABLE tbl_Loot_Summary DROP COLUMN user_id');
        $this->db->query('ALTER TABLE tbl_Devices DROP COLUMN device_user_id');
    }
}
