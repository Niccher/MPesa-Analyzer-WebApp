<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProcessingJobs extends Migration
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
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'queued',
            ],
            'started_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'completed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'duration_seconds' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'messages_processed' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'errors' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['user_id', 'status']);
        $this->forge->createTable('tbl_Processing_Jobs', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_Processing_Jobs', true);
    }
}
