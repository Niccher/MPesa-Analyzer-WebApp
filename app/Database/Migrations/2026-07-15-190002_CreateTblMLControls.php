<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Creates tbl_ML_Controls — key/value control flags for the ML service.
 * Used by the ML service to toggle auto-processing jobs on/off and store
 * runtime configuration.
 */
class CreateTblMLControls extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('tbl_ML_Controls')) {
            return;
        }

        $this->forge->addField([
            'control_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'control_value' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
        ]);

        $this->forge->addKey('control_key', true);
        $this->forge->createTable('tbl_ML_Controls', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_ML_Controls', true);
    }
}
