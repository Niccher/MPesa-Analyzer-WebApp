<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Category override rules (keyword -> correct_category).
 *
 * Previously created at runtime inside Analyse::ensureCategoryRulesTable()
 * and Settings.php. Added here so schema is fully managed by migrations and
 * the runtime creation logic can be removed. Idempotent via IF NOT EXISTS.
 */
class CreateTblCategoryRules extends Migration
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
            'keyword' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'unique'     => true,
            ],
            'correct_category' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('tbl_Category_Rules', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_Category_Rules', true);
    }
}