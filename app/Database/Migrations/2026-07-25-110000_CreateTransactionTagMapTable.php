<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTransactionTagMapTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'tag_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'sms_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'trans_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('tag_id', 'tbl_Transaction_Tags', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tbl_Transaction_Tag_Map');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_Transaction_Tag_Map', true);
    }
}
