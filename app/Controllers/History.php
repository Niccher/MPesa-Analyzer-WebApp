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

        // ── Overall ML stats summary (all uploads) ─────────────
        $stats = [
            'all_sms'        => 0,
            'finance_sms'    => 0,
            'non_finance_sms'=> 0,
            'unclassified'   => 0,
            'all_senders'    => 0,
            'finance_senders'=> 0,
            'non_finance_senders' => 0,
            'banks'          => 0,
            'sent'           => 0,
            'received'       => 0,
            'none'           => 0,
            'transactions'   => 0,
            'total_value'    => 0.0,
            'oldest'         => null,
            'newest'         => null,
        ];

        if (!empty($rawTokens)) {
            $escapedTokens = array_map(fn($t) => $db->escape($t), $rawTokens);
            $inClause = implode(',', $escapedTokens);

            // ── Global SMS/sender breakdown from tbl_Sms (canonical) ──
            if ($db->tableExists('tbl_Sms')) {
                $g = $db->query("
                    SELECT
                        COUNT(*) AS all_sms,
                        COALESCE(SUM(CASE WHEN sms_is_finance = 1 THEN 1 ELSE 0 END), 0) AS finance_sms,
                        COALESCE(SUM(CASE WHEN sms_is_finance IS NULL OR sms_is_finance = 0 THEN 1 ELSE 0 END), 0) AS non_finance_sms,
                        COUNT(DISTINCT sms_number) AS all_senders,
                        COUNT(DISTINCT CASE WHEN sms_is_finance = 1 THEN sms_number END) AS finance_senders,
                        COUNT(DISTINCT CASE WHEN sms_is_finance IS NULL OR sms_is_finance = 0 THEN sms_number END) AS non_finance_senders,
                        COALESCE(SUM(CASE WHEN sms_direction = 'incoming' THEN 1 ELSE 0 END), 0) AS received,
                        COALESCE(SUM(CASE WHEN sms_direction = 'outgoing' THEN 1 ELSE 0 END), 0) AS sent,
                        COALESCE(SUM(CASE WHEN sms_direction IS NULL OR sms_direction = 'none' THEN 1 ELSE 0 END), 0) AS none,
                        COALESCE(SUM(CASE WHEN sms_is_transactional = 1 AND sms_amount IS NOT NULL THEN 1 ELSE 0 END), 0) AS transactions,
                        COALESCE(SUM(sms_amount), 0) AS total_value,
                        MIN(sms_time) AS oldest,
                        MAX(sms_time) AS newest
                    FROM tbl_Sms
                    WHERE sms_owner IN ($inClause)
                ")->getRow();

                if ($g) {
                    $stats['all_sms']        = (int)$g->all_sms;
                    $stats['finance_sms']    = (int)$g->finance_sms;
                    $stats['non_finance_sms'] = (int)$g->non_finance_sms;
                    $stats['all_senders']    = (int)$g->all_senders;
                    $stats['finance_senders']= (int)$g->finance_senders;
                    $stats['non_finance_senders'] = (int)$g->non_finance_senders;
                    $stats['sent']           = (int)$g->sent;
                    $stats['received']       = (int)$g->received;
                    $stats['none']           = (int)$g->none;
                    $stats['transactions']   = (int)$g->transactions;
                    $stats['total_value']    = (float)$g->total_value;
                    $stats['oldest']         = $g->oldest;
                    $stats['newest']         = $g->newest;
                }
            }

            // ── Bank sender count ──
            // Category is "Bank", or the sender name obviously looks like a bank.
            if ($db->tableExists('tbl_Sender_Profiles')) {
                $b = $db->query("
                    SELECT COUNT(DISTINCT sp_number) AS banks
                    FROM tbl_Sender_Profiles
                    WHERE sp_owner IN ($inClause)
                      AND sp_is_finance = 1
                      AND (
                          sp_category = 'Bank'
                          OR sp_name LIKE '%BANK%'
                          OR sp_name LIKE '%KCB%'
                          OR sp_name LIKE '%EQUITY%'
                          OR sp_name LIKE '%NCBA%'
                          OR sp_name LIKE '%COOP%'
                          OR sp_name LIKE '%ABSA%'
                          OR sp_name LIKE '%STANBIC%'
                          OR sp_name LIKE '%DTB%'
                          OR sp_name LIKE '%I%M%'
                      )
                ")->getRow();
                $stats['banks'] = (int)($b->banks ?? 0);
            }

            // Unclassified = all SMS minus those with an llm classification
            $stats['unclassified'] = max(0, $stats['all_sms'] - $stats['finance_sms'] - $stats['non_finance_sms']);

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

                // Category breakdown (canonical: category lives on tbl_Sms)
                $catRows = $db->query("
                    SELECT COALESCE(sms_category, 'Unclassified') as cat, COUNT(*) as cnt
                    FROM tbl_Sms
                    WHERE sms_loot_source = ?
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
            'stats'             => $stats,
            'active_tab'        => 'uploads',
            'jobs'              => $this->fetchUserJobs(),
            'bg_color'          => '#B1B8ED'
        ];

        return view('Dash/history', $data);
    }

    /**
     * ML Jobs tab: this user's processing jobs with full metadata.
     */
    public function jobs()
    {
        helper('mpesa_date');
        $db = \Config\Database::connect();
        $userId = auth()->user()->id;

        $data = [
            'batches'           => [],
            'total_sms_all'     => 0,
            'total_classified_all' => 0,
            'total_amount_all'  => 0,
            'stats'             => $this->computeStats([]),
            'active_tab'        => 'jobs',
            'jobs'              => $this->fetchUserJobs(),
            'bg_color'          => '#B1B8ED'
        ];

        return view('Dash/history', $data);
    }

    /**
     * Fetch the current user's ML processing jobs with decoded metadata.
     */
    private function fetchUserJobs(): array
    {
        $db = \Config\Database::connect();
        $userId = auth()->user()->id;

        if (!$db->tableExists('tbl_Processing_Jobs')) {
            return [];
        }

        $jobs = $db->table('tbl_Processing_Jobs')
            ->where('user_id', $userId)
            ->orderBy('id', 'DESC')
            ->limit(100)
            ->get()
            ->getResultArray();

        foreach ($jobs as $key => $j) {
            $md = $j['metadata'] ?? null;
            if (is_string($md) && $md !== '') {
                $md = json_decode($md, true);
            }
            $md = is_array($md) ? $md : [];
            $jobs[$key]['metadata'] = $md;
        }

        return $jobs;
    }

    /**
     * Default stats (empty) used by the Jobs tab before any summary is loaded.
     */
    private function computeStats(array $tokens): array
    {
        return [
            'all_sms' => 0, 'finance_sms' => 0, 'non_finance_sms' => 0,
            'unclassified' => 0, 'all_senders' => 0, 'finance_senders' => 0,
            'non_finance_senders' => 0, 'banks' => 0, 'sent' => 0,
            'received' => 0, 'none' => 0, 'transactions' => 0,
            'total_value' => 0.0, 'oldest' => null, 'newest' => null,
        ];
    }
}
