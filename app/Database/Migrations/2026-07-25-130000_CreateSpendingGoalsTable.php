<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSpendingGoalsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'category' => ['type' => 'VARCHAR', 'constraint' => 100],
            'label' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'target_amount' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0.00],
            'period' => ['type' => 'ENUM', 'constraint' => ['monthly', 'weekly'], 'default' => 'monthly'],
            'rollover' => ['type' => 'BOOLEAN', 'default' => false],
            'active' => ['type' => 'BOOLEAN', 'default' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tbl_Spending_Goals');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_Spending_Goals', true);
    }
}
