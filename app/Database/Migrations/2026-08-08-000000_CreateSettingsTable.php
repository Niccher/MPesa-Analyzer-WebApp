<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSettingsTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('tbl_Settings')) {
            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'key' => ['type' => 'VARCHAR', 'constraint' => 100, 'unique' => true],
            'value' => ['type' => 'TEXT', 'null' => true],
            'type' => ['type' => 'ENUM', 'constraint' => ['string', 'boolean', 'integer', 'json'], 'default' => 'string'],
            'description' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('tbl_Settings');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_Settings', true);
    }
}