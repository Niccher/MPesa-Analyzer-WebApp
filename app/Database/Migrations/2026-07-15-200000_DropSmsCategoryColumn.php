<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DropSmsCategoryColumn extends Migration
{
    public function up()
    {
        $this->forge->dropColumn('tbl_Sms', 'sms_category');
    }

    public function down()
    {
        $this->forge->addColumn('tbl_Sms', [
            'sms_category' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'default'    => null,
            ],
        ]);
        $this->forge->addKey('sms_category');
    }
}
