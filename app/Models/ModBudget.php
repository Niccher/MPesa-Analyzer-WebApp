<?php

namespace App\Models;

use CodeIgniter\Model;

class ModBudget extends Model
{
    protected $table      = 'tbl_Budgets';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'category', 'label', 'amount_limit', 'period', 'created_at'
    ];

    public function __construct()
    {
        parent::__construct();
        $this->ensureTable();
    }

    private function ensureTable(): void
    {
        if ($this->db->tableExists('tbl_Budgets')) return;

        $forge = \Config\Database::forge();
        $forge->addField([
            'id'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'category'     => ['type' => 'VARCHAR', 'constraint' => 100],
            'label'        => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'amount_limit' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0.00],
            'period'       => ['type' => 'ENUM', 'constraint' => ['monthly', 'weekly'], 'default' => 'monthly'],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $forge->addKey('id', true);
        $forge->createTable('tbl_Budgets');
    }

    public function getBudgets(): array
    {
        return $this->findAll();
    }

    public function saveBudget(array $data): bool
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        if (!empty($data['id'])) {
            return $this->update($data['id'], $data);
        }
        return $this->insert($data) !== false;
    }

    public function deleteBudget(int $id): bool
    {
        return $this->delete($id);
    }

    public function getBudgetProgress(array $budgets): array
    {
        $results = [];
        $now     = time();
        $allSms  = $this->db->table('tbl_Sms s')
            ->select('s.*, sc.direction as cl_direction')
            ->join('tbl_Sms_Classification sc', 'sc.sms_id = s.id', 'left')
            ->get()->getResult();

        foreach ($budgets as $budget) {
            if ($budget['period'] === 'weekly') {
                $windowStart = strtotime('last monday', $now);
                if (date('N', $now) == 1) $windowStart = strtotime('today', $now);
            } else {
                $windowStart = mktime(0, 0, 0, (int)date('m'), 1);
            }

            $spent = 0.0;
            $catLower = strtolower($budget['category']);

            foreach ($allSms as $sms) {
                $ts = is_numeric($sms->sms_time) && $sms->sms_time > 1000000000000
                    ? (int)($sms->sms_time / 1000)
                    : (is_numeric($sms->sms_time) ? (int)$sms->sms_time : strtotime((string)$sms->sms_time));

                if ($ts < $windowStart) continue;

                $dir  = strtolower($sms->cl_direction ?? '');
                $body = strtolower(base64_decode($sms->sms_body));

                $matches = false;
                if ($catLower === 'total outflow') {
                    $matches = $dir === 'outgoing';
                } elseif ($catLower === 'received') {
                    $matches = $dir === 'incoming';
                } elseif ($catLower === 'paybill') {
                    $matches = $dir === 'outgoing' && strpos($body, 'paybill') !== false;
                } elseif ($catLower === 'till') {
                    $matches = $dir === 'outgoing' && strpos($body, 'till') !== false;
                } elseif ($catLower === 'sent to mobile') {
                    $matches = $dir === 'outgoing';
                } elseif ($catLower === 'withdrawal') {
                    $matches = $dir === 'outgoing';
                } elseif ($catLower === 'fuliza') {
                    $matches = strpos($body, 'fuliza') !== false && strpos($body, 'taken') !== false;
                } else {
                    $matches = $dir === 'outgoing' || $dir === 'incoming';
                }

                if ($matches) {
                    if (preg_match('/ksh\.?\s*([\d,]+\.?\d{0,2})/i', $body, $m)) {
                        $spent += (float)str_replace(',', '', $m[1]);
                    }
                }
            }

            $limit      = (float)$budget['amount_limit'];
            $percentage = $limit > 0 ? min(round(($spent / $limit) * 100, 1), 100) : 0;

            $results[] = array_merge($budget, [
                'spent'      => $spent,
                'percentage' => $percentage,
                'remaining'  => max(0, $limit - $spent),
                'over_limit' => $spent > $limit,
            ]);
        }

        return $results;
    }
}
