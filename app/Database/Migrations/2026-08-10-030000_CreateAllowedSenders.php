<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Global allowlist of senders always treated as finance-related
 * ("allowed by default"). The ML backend checks this table first; when it is
 * empty it falls back to its hardcoded list. Empty by default so the
 * hardcoded fallback governs until an admin adds entries.
 */
class CreateAllowedSenders extends Migration
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
            'sender' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'category' => [
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
        $this->forge->addUniqueKey('sender');
        $this->forge->addKey('category');
        $this->forge->createTable('tbl_Allowed_Senders', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_Allowed_Senders', true);
    }
}
