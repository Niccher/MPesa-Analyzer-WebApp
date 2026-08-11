<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Primary file/package upload record (ModUploads primary model table).
 */
class CreateTblLoot extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'loot_Id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'loot_Name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'loot_Device' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'loot_Owner' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'loot_Uuid' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'loot_Created' => [
                'type' => 'BIGINT',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('loot_Id', true);
        $this->forge->addKey('loot_Uuid');
        $this->forge->addKey('loot_Owner');
        $this->forge->createTable('tbl_Loot', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_Loot', true);
    }
}
