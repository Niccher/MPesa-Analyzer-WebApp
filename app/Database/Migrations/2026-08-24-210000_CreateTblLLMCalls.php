<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Creates tbl_LLM_Calls — per-call audit log for every LLM API invocation.
 * Tracks model, call type, token counts, latency, batch size, and job linkage.
 * Used for cost monitoring, latency graphs, and fallback frequency analysis.
 */
class CreateTblLLMCalls extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('tbl_LLM_Calls')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'job_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'tbl_Processing_Jobs.id — null for background poller calls',
            ],
            'call_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
                'comment'    => 'classify | extract',
            ],
            'model' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'provider' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'prompt_tokens' => [
                'type'    => 'INT',
                'null'    => true,
            ],
            'reply_tokens' => [
                'type'    => 'INT',
                'null'    => true,
            ],
            'latency_ms' => [
                'type'    => 'INT',
                'null'    => true,
                'comment' => 'Wall-clock time for the HTTP call in milliseconds',
            ],
            'batch_size' => [
                'type'    => 'INT',
                'null'    => true,
                'comment' => 'Number of SMS messages sent in this call',
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'ok',
                'comment'    => 'ok | error | fallback',
            ],
            'error_message' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('job_id', false, false, 'idx_llmc_job');
        $this->forge->addKey('call_type', false, false, 'idx_llmc_type');
        $this->forge->addKey('created_at', false, false, 'idx_llmc_created');
        $this->forge->createTable('tbl_LLM_Calls', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_LLM_Calls', true);
    }
}
