<?php

namespace App\Controllers;

class History extends BaseController
{
    public function index()
    {
        helper('mpesa_date');
        $db = \Config\Database::connect();

        $summaries = $db->table('tbl_Loot_Summary')
            ->orderBy('loot_Created', 'DESC')
            ->limit(50)
            ->get()
            ->getResult();

        $history = [];
        foreach ($summaries as $s) {
            $uuid = $s->loot_Uuid;
            $total = (int)$s->info_All;

            // Category distribution — use the LATEST classification per SMS
            $catRows = $db->query("
                SELECT COALESCE(latest.cat, 'Unclassified') as cat, COUNT(*) as cnt
                FROM tbl_Sms s
                LEFT JOIN (
                    SELECT sc1.sms_id, sc1.category as cat
                    FROM tbl_Sms_Classification sc1
                    INNER JOIN (
                        SELECT sms_id, MAX(created_at) as max_created
                        FROM tbl_Sms_Classification
                        GROUP BY sms_id
                    ) sc2 ON sc1.sms_id = sc2.sms_id AND sc1.created_at = sc2.max_created
                ) latest ON latest.sms_id = s.id
                WHERE s.sms_loot_source = ?
                GROUP BY cat
                ORDER BY cnt DESC
            ", [$uuid])->getResult();

            $categories = [];
            $classified = 0;
            foreach ($catRows as $r) {
                $categories[$r->cat] = (int)$r->cnt;
                if ($r->cat !== 'Unclassified') {
                    $classified += (int)$r->cnt;
                }
            }

            // Total amount and counterparty count from analyzed transactions
            $finData = $db->query("
                SELECT
                    COALESCE(SUM(a.amount), 0) as total_amount,
                    COUNT(DISTINCT a.counterparty) as counterparties,
                    COUNT(a.id) as analyzed_count
                FROM tbl_Analyzed_Transactions a
                INNER JOIN tbl_Sms s ON s.id = a.orig_sms_int_id OR s.sms__id = a.orig_sms_id
                WHERE s.sms_loot_source = ?
            ", [$uuid])->getRow();

            $history[] = (object)[
                'loot_Uuid'       => $uuid,
                'loot_Created'    => $s->loot_Created,
                'total'           => $total,
                'classified'      => $classified,
                'total_amount'    => (float)($finData->total_amount ?? 0),
                'counterparties'  => (int)($finData->counterparties ?? 0),
                'analyzed_count'  => (int)($finData->analyzed_count ?? 0),
                'categories'      => $categories,
            ];
        }

        $data = [
            'history'  => $history,
            'bg_color' => '#B1B8ED'
        ];

        return view('Dash/history', $data);
    }
}
