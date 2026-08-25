<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Adds the `metadata` JSON column to tbl_Processing_Jobs if missing.
 * The Python ML service adds this column on-the-fly; this migration ensures
 * CI owns it declaratively.
 */
class AddMetadataToProcessingJobs extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('tbl_Processing_Jobs')) {
            return;
        }

        $fields = $this->db->getFieldNames('tbl_Processing_Jobs');

        if (!in_array('metadata', $fields, true)) {
            $this->forge->addColumn('tbl_Processing_Jobs', [
                'metadata' => [
                    'type' => 'JSON',
                    'null' => true,
                    'after' => 'errors',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->tableExists('tbl_Processing_Jobs')) {
            $this->forge->dropColumn('tbl_Processing_Jobs', 'metadata');
        }
    }
}
