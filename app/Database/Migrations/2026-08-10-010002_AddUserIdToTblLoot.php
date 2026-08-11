<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Shield\Authentication\Authenticators\AccessTokens;

/**
 * Adds loot_user_id to tbl_Loot and backfills it from auth_identities.
 *
 * Idempotent: column/index additions are skipped when already present.
 */
class AddUserIdToTblLoot extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('loot_user_id', 'tbl_Loot')) {
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
        }

        $tokenType = AccessTokens::ID_TYPE_ACCESS_TOKEN;
        $this->db->query(
            'UPDATE tbl_Loot l
                LEFT JOIN auth_identities i
                    ON i.type = ? AND i.secret = SHA2(l.loot_Owner, 256)
                SET l.loot_user_id = i.user_id',
            [$tokenType]
        );
    }

    public function down()
    {
        $this->db->query('ALTER TABLE tbl_Loot DROP COLUMN loot_user_id');
    }
}
