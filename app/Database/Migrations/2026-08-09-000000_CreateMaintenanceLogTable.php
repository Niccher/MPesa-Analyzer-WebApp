<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMaintenanceLogTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('tbl_Maintenance_Log')) {
            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'action' => ['type' => 'ENUM', 'constraint' => ['start', 'stop'], 'default' => 'start'],
            'source' => ['type' => 'ENUM', 'constraint' => ['manual', 'cron'], 'default' => 'manual'],
            'message' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('created_at');
        $this->forge->createTable('tbl_Maintenance_Log');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_Maintenance_Log', true);
    }
}
