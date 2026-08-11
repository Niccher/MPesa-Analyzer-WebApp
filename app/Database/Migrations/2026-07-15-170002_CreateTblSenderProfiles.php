<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Sender profile aggregates (owner + number with finance flag).
 */
class CreateTblSenderProfiles extends Migration
{
    public function up()
    {
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
    }

    public function down()
    {
        $this->forge->dropTable('tbl_Sender_Profiles', true);
    }
}
