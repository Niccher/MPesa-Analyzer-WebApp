<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Per-user blocklist of SMS senders. Blocked senders are excluded from
 * finance intelligence — their SMS is categorised as non-finance.
 */
class CreateBlockedSenders extends Migration
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
                'null'       => false,
            ],
            'sender' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['user_id', 'sender']);
        $this->forge->addKey('sender');
        $this->forge->createTable('tbl_Blocked_Senders', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_Blocked_Senders', true);
    }
}
