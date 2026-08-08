<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserSettingsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'unique' => true,
            ],
            'currency' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'default' => 'KES',
            ],
            'date_format' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'Y-m-d',
            ],
            'time_format' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'default' => 'H:i',
            ],
            'default_budget_period' => [
                'type' => 'ENUM',
                'constraint' => ['monthly', 'weekly'],
                'default' => 'monthly',
            ],
            'budget_alert_threshold' => [
                'type' => 'INT',
                'constraint' => 3,
                'default' => 80,
            ],
            'dashboard_widgets' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'notify_email_alerts' => [
                'type' => 'BOOLEAN',
                'default' => true,
            ],
            'notify_budget_alerts' => [
                'type' => 'BOOLEAN',
                'default' => true,
            ],
            'notify_low_balance' => [
                'type' => 'BOOLEAN',
                'default' => true,
            ],
            'notify_unusual_activity' => [
                'type' => 'BOOLEAN',
                'default' => true,
            ],
            'export_default_format' => [
                'type' => 'ENUM',
                'constraint' => ['csv', 'json'],
                'default' => 'csv',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tbl_User_Settings');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_User_Settings', true);
    }
}
