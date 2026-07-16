<?php

namespace App\Controllers;

use App\Models\ModUploads;

class Analyse extends BaseController
{
    public function index()
    {
        try {
            $limit = $this->request->getGet('limit') ?? 1000;
            $mod_uploads = new ModUploads();
            $db = \Config\Database::connect();

            // 1. Ensure output table exists
            $this->ensureAnalysisTable($db);
            $this->ensureCategoryRulesTable($db);

            // Fetch Custom Rules
            $categoryRules = [];
            $rulesQuery = $db->table('tbl_Category_Rules')->get()->getResultArray();
            foreach ($rulesQuery as $r) {
                $categoryRules[strtolower($r['keyword'])] = $r['correct_category'];
            }

            // 2. Fetch SMS not yet analyzed
            $smsList = $db->table('tbl_Sms s')
                ->select('s.*')
                ->join('tbl_Analyzed_Transactions a', 'a.orig_sms_id = s.sms__id', 'left')
                ->where('a.id', null)
                ->orderBy('s.sms_time', 'DESC')
                ->limit($limit)
                ->get()
                ->getResult();

            $processed = 0;
            $results = [];

            foreach ($smsList as $sms) {
                if (empty($sms->sms_body)) continue;
                
                $body = base64_decode($sms->sms_body);
                if ($body === false) continue;

                $parsed = $this->deepParse($body, $sms->sms_category ?? $sms->cl_category ?? '', $categoryRules);
                
                // Normalize the date: handle numeric timestamps
                $transDate = $this->normalizeDate($sms->sms_time);

                $results[] = [
                    'orig_sms_id'  => $sms->sms__id,
                    'trans_id'     => $parsed['trans_id'],
                    'amount'       => $parsed['amount'],
                    'counterparty' => $parsed['counterparty'],
                    'description'  => $parsed['description'],
                    'trans_date'   => $transDate,
                    'created_at'   => date('Y-m-d H:i:s')
                ];
                $processed++;
            }

            if (!empty($results)) {
                $db->table('tbl_Analyzed_Transactions')->insertBatch($results);
            }

            @session_write_close();
            if (ob_get_length()) ob_clean();

            return $this->response->setJSON([
                'status'  => 'success',
                'message' => "Successfully analyzed $processed transactions.",
                'count'   => $processed
            ]);

        } catch (\Exception $e) {
            if (ob_get_length()) ob_clean();
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Normalize any date/timestamp format to Y-m-d H:i:s
     */
    private function normalizeDate($time): string {
        if (empty($time)) return date('Y-m-d H:i:s');
        // Handle JavaScript millisecond timestamps
        if (is_numeric($time) && $time > 1000000000000) {
            return date('Y-m-d H:i:s', (int)($time / 1000));
        }
        $timestamp = is_numeric($time) ? (int)$time : strtotime((string)$time);
        return date('Y-m-d H:i:s', $timestamp ?: time());
    }

    private function ensureAnalysisTable($db)
    {
        if ($db->tableExists('tbl_Analyzed_Transactions')) {
            return;
        }

        $forge = \Config\Database::forge();
        $forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'orig_sms_id' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
            ],
            'trans_id' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'null'       => true,
            ],
            'amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
            ],
            'counterparty' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'trans_date' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $forge->addKey('id', true);
        $forge->addKey('orig_sms_id');
        $forge->addKey('trans_date');
        $forge->createTable('tbl_Analyzed_Transactions');
    }

    private function ensureCategoryRulesTable($db)
    {
        if ($db->tableExists('tbl_Category_Rules')) {
            return;
        }

        $forge = \Config\Database::forge();
        $forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'keyword' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'unique'     => true
            ],
            'correct_category' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $forge->addKey('id', true);
        $forge->createTable('tbl_Category_Rules');
    }

    private function deepParse($body, $category, $categoryRules = []): array
    {
        $data = [
            'trans_id'     => '',
            'amount'       => 0,
            'counterparty' => 'Unknown',
            'description'  => $category
        ];

        // 1. Extract Transaction ID (e.g., RK12345678 - 10 uppercase alphanumeric chars)
        if (preg_match('/\b([A-Z0-9]{10})\s+(Confirmed|transferred|sent)/i', $body, $m)) {
            $data['trans_id'] = $m[1];
        }

        // 2. Extract Amount (matches Ksh 1,200.00 or Ksh1200)
        if (preg_match('/ksh\.?\s*([\d,]+\.?\d{0,2})/i', $body, $m)) {
            $data['amount'] = (float)str_replace(',', '', $m[1]);
        }

        // 3. Extract Counterparty based on content patterns
        if (preg_match('/sent to\s+([A-Z][^0-9]+?)\s+[\d]/i', $body, $m)) {
            // "Sent to JOHN DOE 0712..."
            $data['counterparty'] = trim($m[1]);
        } elseif (preg_match('/paid to\s+([^.]+)\./i', $body, $m)) {
            // "Paid to SAFARICOM FIBER."
            $data['counterparty'] = trim($m[1]);
        } elseif (preg_match('/received\s+ksh.*?\s+from\s+([A-Z][^0-9]+?)\s+[\d]/i', $body, $m)) {
            // "received Ksh 500 from JOHN DOE 0712..."
            $data['counterparty'] = trim($m[1]);
        } elseif (preg_match('/from\s+([A-Z][A-Z ]+(?:BANK|LIMITED|GROUP|SACCO|KENYA)?)\s+on/i', $body, $m)) {
            // "from FAMILY BANK on..."
            $data['counterparty'] = trim($m[1]);
        } elseif (preg_match('/transferred\s+to\s+([A-Z][^0-9.]+?)[\s.]/i', $body, $m)) {
            $data['counterparty'] = trim($m[1]);
        } elseif (preg_match('/withdrawn\s+from\s+([A-Z][A-Z ]+?)\s+agent/i', $body, $m)) {
            $data['counterparty'] = trim($m[1]);
        }

        // Clean up counterparty
        if ($data['counterparty'] !== 'Unknown') {
            $data['counterparty'] = preg_replace('/\s+(on|via|using|for)$/i', '', $data['counterparty']);
            $data['counterparty'] = trim($data['counterparty'], " .,");
        }

        // Apply Smart Auto-Fix Rules
        $cp = strtolower($data['counterparty']);
        if (isset($categoryRules[$cp])) {
            $data['description'] = $categoryRules[$cp];
        }

        return $data;
    }

    public function saveRule()
    {
        $keyword = $this->request->getPost('keyword');
        $category = $this->request->getPost('category');
        $transId = $this->request->getPost('trans_id'); // Just to update the specific transaction immediately

        if (empty($keyword) || empty($category)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Keyword and Category required.']);
        }

        $db = \Config\Database::connect();
        $this->ensureCategoryRulesTable($db);

        // Upsert Rule
        $builder = $db->table('tbl_Category_Rules');
        $existing = $builder->where('keyword', $keyword)->get()->getRow();

        if ($existing) {
            $builder->where('id', $existing->id)->update(['correct_category' => $category]);
        } else {
            $builder->insert([
                'keyword'          => $keyword,
                'correct_category' => $category,
                'created_at'       => date('Y-m-d H:i:s')
            ]);
        }

        // Apply immediately to the analyzed table
        if (!empty($transId)) {
            $db->table('tbl_Analyzed_Transactions')->where('orig_sms_id', $transId)->update(['description' => $category]);
        }

        // Retroactively apply to past transactions with the same counterparty
        $db->table('tbl_Analyzed_Transactions')->where('counterparty', $keyword)->update(['description' => $category]);

        // Also update tbl_Sms_Classification for matching SMS (aligns with LLM categories)
        if ($db->tableExists('tbl_Sms_Classification')) {
            $smsIds = $db->table('tbl_Analyzed_Transactions')
                ->select('orig_sms_id')
                ->where('counterparty', $keyword)
                ->get()
                ->getResultArray();
            $ids = array_filter(array_column($smsIds, 'orig_sms_id'));
            if (!empty($ids)) {
                $db->table('tbl_Sms_Classification')
                    ->whereIn('sms_id', $ids)
                    ->update(['category' => $category]);
            }
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => "Rule saved! Future and past transactions for '$keyword' will be mapped to '$category'."
        ]);
    }
}
