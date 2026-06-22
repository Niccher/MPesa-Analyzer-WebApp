<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblSms extends Migration
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
            'sms_type' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
            ],
            'sms_number' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
            ],
            'sms_thread_id' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'sms_time' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
            ],
            'sms_category' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'sms_seen' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
            ],
            'sms__id' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
            ],
            'sms_body' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'sms_loot_source' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'sms_owner' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'sms_device' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('sms_time');
        $this->forge->addKey('sms_category');
        $this->forge->addKey('sms_number');
        $this->forge->addKey('sms_owner');
        $this->forge->addUniqueKey(['sms_owner', 'sms__id', 'sms_time']);
        $this->forge->createTable('tbl_Sms', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_Sms', true);
    }
}
