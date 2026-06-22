<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMissingMpesaTables extends Migration
{
    public function up()
    {
        // ── tbl_Loot ──────────────────────────────────────────────────────────
        // Primary file/package upload record (ModUploads primary model table)
        $this->forge->addField([
            'loot_Id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'loot_Name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'loot_Device' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'loot_Owner' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'loot_Uuid' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'loot_Created' => [
                'type' => 'BIGINT',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('loot_Id', true);
        $this->forge->addKey('loot_Uuid');
        $this->forge->addKey('loot_Owner');
        $this->forge->createTable('tbl_Loot', true);

        // ── tbl_Loot_Summary ──────────────────────────────────────────────────
        // Aggregated SMS category counts per loot upload
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'loot_Uuid'                  => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'loot_Created'               => ['type' => 'DATETIME', 'null' => true],
            'info_Get_Received'          => ['type' => 'INT', 'default' => 0],
            'info_Get_from_Mshwari'      => ['type' => 'INT', 'default' => 0],
            'info_Get_from_NCBA'         => ['type' => 'INT', 'default' => 0],
            'info_Get_from_KCB'          => ['type' => 'INT', 'default' => 0],
            'info_Get_from_IM'           => ['type' => 'INT', 'default' => 0],
            'info_Get_from_Reversal'     => ['type' => 'INT', 'default' => 0],
            'info_Get_Bal_MPESA'         => ['type' => 'INT', 'default' => 0],
            'info_Get_Bal_KCB'           => ['type' => 'INT', 'default' => 0],
            'info_Get_Bal_Mshwari'       => ['type' => 'INT', 'default' => 0],
            'info_Loan_Limit'            => ['type' => 'INT', 'default' => 0],
            'info_Sent_to_MPESA'         => ['type' => 'INT', 'default' => 0],
            'info_Sent_to_LNM'           => ['type' => 'INT', 'default' => 0],
            'info_Sent_Mini'             => ['type' => 'INT', 'default' => 0],
            'info_Sent_to_Mshwari'       => ['type' => 'INT', 'default' => 0],
            'info_Sent_Cancel'           => ['type' => 'INT', 'default' => 0],
            'info_Error_Failed'          => ['type' => 'INT', 'default' => 0],
            'info_Error_Pin'             => ['type' => 'INT', 'default' => 0],
            'info_Error_Less'            => ['type' => 'INT', 'default' => 0],
            'info_Error_Receiver'        => ['type' => 'INT', 'default' => 0],
            'info_Error_Receiver_Org'    => ['type' => 'INT', 'default' => 0],
            'info_Withdraw'              => ['type' => 'INT', 'default' => 0],
            'info_Fuliza_Opt_Out'        => ['type' => 'INT', 'default' => 0],
            'info_Fuliza_Opt_In'         => ['type' => 'INT', 'default' => 0],
            'info_Fuliza_Limit'          => ['type' => 'INT', 'default' => 0],
            'info_Fuliza_Loan_Paid'      => ['type' => 'INT', 'default' => 0],
            'info_Fuliza_Mini_Statement' => ['type' => 'INT', 'default' => 0],
            'info_Fuliza_Loan_Taken'     => ['type' => 'INT', 'default' => 0],
            'info_Similar_Transaction'   => ['type' => 'INT', 'default' => 0],
            'info_Unknown'               => ['type' => 'INT', 'default' => 0],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('loot_Uuid');
        $this->forge->createTable('tbl_Loot_Summary', true);

        // ── tbl_Devices ───────────────────────────────────────────────────────
        // Android device fingerprint info sent from the mobile app
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

        // ── tbl_User_Devices ─────────────────────────────────────────────────
        // Links Shield auth users to their device tokens
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
        $this->forge->dropTable('tbl_Devices', true);
        $this->forge->dropTable('tbl_Loot_Summary', true);
        $this->forge->dropTable('tbl_Loot', true);
    }
}
