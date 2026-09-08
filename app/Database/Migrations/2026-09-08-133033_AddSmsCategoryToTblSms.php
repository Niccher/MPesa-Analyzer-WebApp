<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSmsCategoryToTblSms extends Migration
{
    public function up(): void
    {
        $fields = [
            'sms_category' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'default'    => 'Unclassified',
                'after'      => 'sms_transaction_type',
            ],
        ];
        $this->forge->addColumn('tbl_Sms', $fields);
    }

    public function down(): void
    {
        $this->forge->dropColumn('tbl_Sms', 'sms_category');
    }
}
