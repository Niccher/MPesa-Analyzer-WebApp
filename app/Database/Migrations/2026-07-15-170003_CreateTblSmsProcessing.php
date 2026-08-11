<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Per-SMS processing/tracking state.
 */
class CreateTblSmsProcessing extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'sms_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'pending',
            ],
            'attempt_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'last_error' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'processed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('sms_id', true);
        $this->forge->addKey('status');
        $this->forge->createTable('tbl_Sms_Processing', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_Sms_Processing', true);
    }
}
