<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBudgetThresholdAlerts extends Migration
{
    public function up()
    {
        $fields = [
            'threshold_alert_80' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
                'after'      => 'rollover'
            ],
            'threshold_alert_100' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
                'after'      => 'threshold_alert_80'
            ],
            'alert_sent_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'threshold_alert_100'
            ]
        ];

        $this->forge->addColumn('tbl_Budgets', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('tbl_Budgets', ['threshold_alert_80', 'threshold_alert_100', 'alert_sent_at']);
    }
}
