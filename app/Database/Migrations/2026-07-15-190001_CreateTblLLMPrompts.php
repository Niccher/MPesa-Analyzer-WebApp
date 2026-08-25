<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Creates tbl_LLM_Prompts — versioned prompt overrides used by the ML service.
 * A DB-active prompt for a key takes precedence over the ML service's
 * hardcoded default template.
 */
class CreateTblLLMPrompts extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('tbl_LLM_Prompts')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'prompt_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'version' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => false,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'default'    => '',
            ],
            'body' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['prompt_key', 'version'], 'uq_key_version');
        $this->forge->addKey(['prompt_key', 'is_active'], 'idx_key_active');
        $this->forge->createTable('tbl_LLM_Prompts', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_LLM_Prompts', true);
    }
}
