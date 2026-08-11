<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Budgets table (categories + monthly/weekly amount limits).
 *
 * Previously created outside the migration system (only an alter migration,
 * AddBudgetRolloverColumn, referenced it). Added here so schema is fully
 * managed by migrations. Idempotent via IF NOT EXISTS.
 */
class CreateTblBudgets extends Migration
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
                'null'       => true,
            ],
            'category' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'label' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'amount_limit' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0.00,
                'null'       => false,
            ],
            'period' => [
                'type'       => 'ENUM',
                'constraint' => "'monthly','weekly'",
                'default'    => 'monthly',
                'null'       => false,
            ],
            'rollover' => [
                'type'    => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('tbl_Budgets', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_Budgets', true);
    }
}