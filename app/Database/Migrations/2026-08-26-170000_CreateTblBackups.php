<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblBackups extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('tbl_Backups')) {
            $this->forge->addField([
                'id'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'filename'       => ['type' => 'VARCHAR', 'constraint' => 255],
                'filepath'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'file_size'      => ['type' => 'BIGINT', 'unsigned' => true, 'default' => 0],
                'structure_only' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'compressed'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'type'           => ['type' => 'ENUM', 'constraint' => ['manual', 'scheduled'], 'default' => 'manual'],
                'created_by'     => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
                'created_at'     => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('tbl_Backups');
        }
    }

    public function down()
    {
        $this->forge->dropTable('tbl_Backups', true);
    }
}
