<?php

namespace App\Controllers;

class History extends BaseController
{
    public function index()
    {
        helper('mpesa_date');
        $db = \Config\Database::connect();

        $userId = auth()->user()->id;
        $tokenType = \CodeIgniter\Shield\Authentication\Authenticators\AccessTokens::ID_TYPE_ACCESS_TOKEN;

        $rawTokens = [];
        $tokenRows = $db->query("
            SELECT DISTINCT s.sms_owner AS tk FROM tbl_Sms s
            INNER JOIN auth_identities i ON i.secret = SHA2(s.sms_owner, 256)
            WHERE i.user_id = ? AND i.type = ?
            UNION
            SELECT DISTINCT l.loot_Owner AS tk FROM tbl_Loot l
            INNER JOIN auth_identities i ON i.secret = SHA2(l.loot_Owner, 256)
            WHERE i.user_id = ? AND i.type = ?
        ", [$userId, $tokenType, $userId, $tokenType])->getResult();

        $rawTokens = array_values(array_filter(array_map(fn($r) => $r->tk ?? '', $tokenRows)));

        $batches = [];
        $totalSmsAll = 0;
        $totalClassifiedAll = 0;
        $totalAmountAll = 0;

        if (!empty($rawTokens)) {
            $escapedTokens = array_map(fn($t) => $db->escape($t), $rawTokens);
            $inClause = implode(',', $escapedTokens);

            // Get upload batches that belong to this user's tokens
            $summaries = $db->query("
                SELECT ls.loot_Uuid, ls.loot_Created, ls.info_All
                FROM tbl_Loot_Summary ls
                INNER JOIN tbl_Sms s ON s.sms_loot_source = ls.loot_Uuid
                WHERE s.sms_owner IN ($inClause)
                GROUP BY ls.loot_Uuid, ls.loot_Created, ls.info_All
                ORDER BY ls.loot_Created DESC
                LIMIT 50
            ")->getResult();

            foreach ($summaries as $s) {
                $uuid = $s->loot_Uuid;
                $total = (int)$s->info_All;

                // LLM-classified count
                $classified = 0;
                if ($db->tableExists('tbl_Sms_Classification')) {
                    $cr = $db->query("
                        SELECT COUNT(*) as cnt FROM tbl_Sms_Classification sc
                        INNER JOIN tbl_Sms s ON s.id = sc.sms_id
                        WHERE sc.method = 'llm' AND s.sms_loot_source = ?
                    ", [$uuid])->getRow();
                    $classified = (int)$cr->cnt;
                }

                // Category breakdown (latest classification per SMS)
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
                foreach ($catRows as $r) {
                    $categories[$r->cat] = (int)$r->cnt;
                }

                // Financial summary
                $finData = $db->query("
                    SELECT
                        COALESCE(SUM(a.amount), 0) as total_amount,
                        COUNT(DISTINCT a.counterparty) as counterparties,
                        COUNT(a.id) as analyzed_count
                    FROM tbl_Analyzed_Transactions a
                    INNER JOIN tbl_Sms s ON s.id = a.orig_sms_int_id OR s.sms__id = a.orig_sms_id
                    WHERE s.sms_loot_source = ?
                ", [$uuid])->getRow();

                $totalAmount = (float)($finData->total_amount ?? 0);
                $counterparties = (int)($finData->counterparties ?? 0);

                $totalSmsAll += $total;
                $totalClassifiedAll += $classified;
                $totalAmountAll += $totalAmount;

                $batches[] = (object)[
                    'uuid'           => $uuid,
                    'created_at'     => $s->loot_Created,
                    'total'          => $total,
                    'classified'     => $classified,
                    'total_amount'   => $totalAmount,
                    'counterparties' => $counterparties,
                    'categories'     => $categories,
                ];
            }
        }

        $data = [
            'batches'           => $batches,
            'total_sms_all'     => $totalSmsAll,
            'total_classified_all' => $totalClassifiedAll,
            'total_amount_all'  => $totalAmountAll,
            'bg_color'          => '#B1B8ED'
        ];

        return view('Dash/history', $data);
    }
}
