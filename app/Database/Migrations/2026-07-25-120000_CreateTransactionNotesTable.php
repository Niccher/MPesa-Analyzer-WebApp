<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTransactionNotesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'sms_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'trans_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'note' => ['type' => 'TEXT'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tbl_Transaction_Notes');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_Transaction_Notes', true);
    }
}
