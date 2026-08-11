<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Links Shield auth users to their device tokens.
 */
class CreateTblUserDevices extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
            ],
            'device_token' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'device_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('device_token');
        $this->forge->addKey(['user_id', 'device_token']);
        $this->forge->createTable('tbl_User_Devices', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_User_Devices', true);
    }
}
