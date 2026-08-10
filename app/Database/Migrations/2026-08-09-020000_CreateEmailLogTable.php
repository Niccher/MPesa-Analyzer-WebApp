<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEmailLogTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('tbl_Email_Log')) {
            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'trigger' => ['type' => 'VARCHAR', 'constraint' => 100, 'default' => ''],
            'to_email' => ['type' => 'VARCHAR', 'constraint' => 255, 'default' => ''],
            'subject' => ['type' => 'VARCHAR', 'constraint' => 255, 'default' => ''],
            'status' => ['type' => 'ENUM', 'constraint' => ['success', 'error'], 'default' => 'error'],
            'message' => ['type' => 'TEXT', 'null' => true],
            'sent_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('trigger');
        $this->forge->addKey('to_email');
        $this->forge->addKey('status');
        $this->forge->addKey('sent_at');
        $this->forge->createTable('tbl_Email_Log');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_Email_Log', true);
    }
}
