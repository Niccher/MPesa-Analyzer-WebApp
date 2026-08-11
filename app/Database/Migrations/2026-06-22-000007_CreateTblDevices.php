<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Android device fingerprint info sent from the mobile app.
 */
class CreateTblDevices extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'device_Uuid'         => ['type' => 'VARCHAR', 'constraint' => 100,  'null' => true],
            'device_Device'       => ['type' => 'VARCHAR', 'constraint' => 255,  'null' => true],
            'device_Created_At'   => ['type' => 'DATETIME', 'null' => true],
            'device_Product'      => ['type' => 'VARCHAR', 'constraint' => 100,  'null' => true],
            'device_Bootloader'   => ['type' => 'VARCHAR', 'constraint' => 100,  'null' => true],
            'device_Type'         => ['type' => 'VARCHAR', 'constraint' => 50,   'null' => true],
            'device_Tags'         => ['type' => 'TEXT', 'null' => true],
            'device_Host'         => ['type' => 'VARCHAR', 'constraint' => 255,  'null' => true],
            'device_Display'      => ['type' => 'VARCHAR', 'constraint' => 255,  'null' => true],
            'device_Hardware'     => ['type' => 'VARCHAR', 'constraint' => 100,  'null' => true],
            'device_Fingerprint'  => ['type' => 'TEXT', 'null' => true],
            'device_Manufacturer' => ['type' => 'VARCHAR', 'constraint' => 100,  'null' => true],
            'device_Brand'        => ['type' => 'VARCHAR', 'constraint' => 100,  'null' => true],
            'device_Board'        => ['type' => 'VARCHAR', 'constraint' => 100,  'null' => true],
            'device_User'         => ['type' => 'VARCHAR', 'constraint' => 100,  'null' => true],
            'device_Model'        => ['type' => 'VARCHAR', 'constraint' => 100,  'null' => true],
            'device_Time'         => ['type' => 'BIGINT', 'null' => true],
            'device_Serial'       => ['type' => 'VARCHAR', 'constraint' => 100,  'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('device_Uuid');
        $this->forge->createTable('tbl_Devices', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_Devices', true);
    }
}
