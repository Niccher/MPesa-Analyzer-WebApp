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
        $allSms  = $this->db->table('tbl_Sms')->get()->getResult();

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

                $smsCat = strtolower($sms->sms_category ?? $sms->cl_category ?? '');
                $body   = strtolower(base64_decode($sms->sms_body));

                $matches = false;
                if ($catLower === 'total outflow') {
                    $matches = in_array($smsCat, ['sent', 'sent to lnm', 'withdraw']);
                } elseif ($catLower === 'received') {
                    $matches = $smsCat === 'received';
                } elseif ($catLower === 'paybill') {
                    $matches = $smsCat === 'sent to lnm' && strpos($body, 'paybill') !== false;
                } elseif ($catLower === 'till') {
                    $matches = $smsCat === 'sent to lnm' && strpos($body, 'paybill') === false;
                } elseif ($catLower === 'sent to mobile') {
                    $matches = $smsCat === 'sent';
                } elseif ($catLower === 'withdrawal') {
                    $matches = $smsCat === 'withdraw';
                } elseif ($catLower === 'fuliza') {
                    $matches = strpos($smsCat, 'fuliza') !== false;
                } else {
                    $matches = strpos($smsCat, $catLower) !== false;
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
