<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuditLogTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'ip' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => true,
            ],
            'user_agent' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'action' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'action_category' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'metadata' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->addKey('action_category');
        $this->forge->addKey('created_at');
        $this->forge->createTable('tbl_Audit_Log', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_Audit_Log', true);
    }
}