<?php

namespace App\Controllers;

use App\Models\ModUploads;

class Blocklist extends BaseController
{
    private ModUploads $uploadsModel;

    public function __construct()
    {
        $this->uploadsModel = new ModUploads();
    }

    /**
     * Default landing — the Blocked senders page.
     */
    public function index()
    {
        return redirect()->to('/dashboard/blocklist/blocked');
    }

    public function blocked()
    {
        return $this->renderList('blocked');
    }

    public function allowed()
    {
        return $this->renderList('allowed');
    }

    public function unknown()
    {
        return $this->renderList('unknown');
    }

    /**
     * Render a single Blocklist sub-page (tab). Each tab is its own URL but
     * shares the same top navigation, like /admin/ml/models.
     */
    private function renderList(string $tab)
    {
        $senders = $this->getCategorizedSenders(auth()->user()->id);

        $list = [];
        $listMeta = [
            'blocked' => ['title' => 'Blocked Senders', 'icon' => 'fa-ban', 'desc' => 'Senders whose SMS is excluded from your finance intelligence.'],
            'allowed' => ['title' => 'Allowed Senders', 'icon' => 'fa-star', 'desc' => 'Known finance senders allowed by default. Block any you want excluded.'],
            'unknown' => ['title' => 'Unknown Senders', 'icon' => 'fa-question-circle', 'desc' => 'Senders not yet determined — these are subject to classification as finance or non-finance.'],
        ];

        foreach ($senders as $s) {
            if ($tab === 'blocked' && $s['blocked']) {
                $list[] = $s;
            } elseif ($tab === 'allowed' && !$s['blocked'] && $s['allowed']) {
                $list[] = $s;
            } elseif ($tab === 'unknown' && !$s['blocked'] && !$s['allowed']) {
                $list[] = $s;
            }
        }

        // Counts for the tab badges.
        $counts = ['blocked' => 0, 'allowed' => 0, 'unknown' => 0];
        foreach ($senders as $s) {
            if ($s['blocked']) {
                $counts['blocked']++;
            } elseif ($s['allowed']) {
                $counts['allowed']++;
            } else {
                $counts['unknown']++;
            }
        }

        $data = [
            'bg_color' => '#B1B8ED',
            'active_tab' => $tab,
            'meta' => $listMeta[$tab],
            'senders' => $list,
            'counts' => $counts,
            'total' => count($senders),
        ];

        return view('Blocklist/list', $data);
    }

    /**
     * Block a single sender (persist + re-categorise as non-finance).
     */
    public function block()
    {
        $senders = $this->request->getPost('senders')
            ? (array) $this->request->getPost('senders')
            : [$this->request->getPost('sender')];

        $senders = array_values(array_filter(array_map(
            static fn ($s) => strtoupper(trim((string) $s)),
            $senders
        )));

        if (empty($senders)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No sender provided.']);
        }

        return $this->response->setJSON($this->applyBlock(auth()->user()->id, $senders));
    }

    /**
     * Unblock a single sender.
     */
    public function unblock()
    {
        $senders = $this->request->getPost('senders')
            ? (array) $this->request->getPost('senders')
            : [$this->request->getPost('sender')];

        $senders = array_values(array_filter(array_map(
            static fn ($s) => strtoupper(trim((string) $s)),
            $senders
        )));

        if (empty($senders)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No sender provided.']);
        }

        return $this->response->setJSON($this->applyUnblock(auth()->user()->id, $senders));
    }

    /**
     * Bulk block multiple senders.
     */
    public function bulkBlock()
    {
        $senders = $this->request->getPost('senders');
        if (empty($senders) || !is_array($senders)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No senders selected.']);
        }

        $senders = array_values(array_filter(array_map(
            static fn ($s) => strtoupper(trim((string) $s)),
            $senders
        )));

        if (empty($senders)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No senders selected.']);
        }

        return $this->response->setJSON($this->applyBlock(auth()->user()->id, $senders));
    }

    /**
     * Bulk unblock multiple senders.
     */
    public function bulkUnblock()
    {
        $senders = $this->request->getPost('senders');
        if (empty($senders) || !is_array($senders)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No senders selected.']);
        }

        $senders = array_values(array_filter(array_map(
            static fn ($s) => strtoupper(trim((string) $s)),
            $senders
        )));

        if (empty($senders)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No senders selected.']);
        }

        return $this->response->setJSON($this->applyUnblock(auth()->user()->id, $senders));
    }

    private function applyBlock(int $userId, array $senders): array
    {
        $db = \Config\Database::connect();
        $tokens = $this->uploadsModel->getOwnedRawTokens($userId);

        foreach ($senders as $sender) {
            $db->table('tbl_Blocked_Senders')->upsert([
                'user_id' => $userId,
                'sender' => $sender,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $smsUpdated = 0;
        $profilesUpdated = 0;

        if (!empty($tokens)) {
            if ($db->tableExists('tbl_Sms')) {
                $ownedSms = $db->table('tbl_Sms')
                    ->select('id')
                    ->whereIn('sms_owner', $tokens);

                $smsUpdated = $db->table('tbl_Sms')
                    ->whereIn('sms_number', $senders)
                    ->whereIn('id', $ownedSms)
                    ->set('sms_is_finance', 0)
                    ->update();
            }

            if ($db->tableExists('tbl_Sender_Profiles')) {
                $profilesUpdated = $db->table('tbl_Sender_Profiles')
                    ->whereIn('sp_number', $senders)
                    ->whereIn('sp_owner', $tokens)
                    ->set('sp_is_finance', 0)
                    ->set('sp_updated', date('Y-m-d H:i:s'))
                    ->update();
            }
        }

        return [
            'status' => 'success',
            'message' => count($senders) . ' sender(s) blocked. Their SMS is now excluded from finance intelligence'
                . " ({$smsUpdated} SMS classifications, {$profilesUpdated} sender profiles updated).",
        ];
    }

    private function applyUnblock(int $userId, array $senders): array
    {
        $db = \Config\Database::connect();

        $deleted = $db->table('tbl_Blocked_Senders')
            ->where('user_id', $userId)
            ->whereIn('sender', $senders)
            ->delete();

        return [
            'status' => 'success',
            'message' => count($senders) . ' sender(s) unblocked. Run a rescan to reclassify their SMS.',
        ];
    }

    /**
     * Senders currently on this user's blocklist, keyed by sender.
     */
    private function getBlockedMap(int $userId): array
    {
        $db = \Config\Database::connect();
        $rows = $db->table('tbl_Blocked_Senders')
            ->where('user_id', $userId)
            ->get()
            ->getResultArray();

        $map = [];
        foreach ($rows as $row) {
            $map[$row['sender']] = $row['created_at'];
        }
        return $map;
    }

    /**
     * Global allowed-sender set (the ML allowlist), keyed by sender.
     *
     * Combines:
     *  1. The DB allowlist (tbl_Allowed_Senders) — takes precedence.
     *  2. The ML backend's hardcoded default finance senders (fallback list).
     * This way "preselected good senders" from the ML code show up as allowed
     * even if the user has never uploaded SMS from them.
     */
    private function getGlobalAllowedMap(): array
    {
        $db = \Config\Database::connect();
        $rows = $db->table('tbl_Allowed_Senders')
            ->select('sender, category')
            ->get()
            ->getResultArray();

        $map = [];
        foreach ($rows as $row) {
            $map[$row['sender']] = $row['category'] ?? '';
        }

        // Merge the hardcoded defaults (they never override an explicit DB entry).
        foreach ($this->getHardcodedDefaults() as $sender => $category) {
            if (!isset($map[$sender])) {
                $map[$sender] = $category;
            }
        }

        return $map;
    }

    /**
     * The ML backend's hardcoded default finance senders, keyed by sender.
     * Returns [] if the backend is unreachable.
     */
    private function getHardcodedDefaults(): array
    {
        try {
            $base = rtrim((string) config('MlBackend')->baseUrl, '/');
            $resp = \Config\Services::curlrequest()->get($base . '/admin/allowed/defaults', ['timeout' => 5]);
            $body = json_decode($resp->getBody(), true);
            $defaults = $body['defaults'] ?? [];
            $map = [];
            foreach ($defaults as $d) {
                $map[$d['sender']] = $d['category'] ?? '';
            }
            return $map;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Every unique sender belonging to this user, categorised with the flags
     * needed to split them into blocked / allowed / unknown lists.
     */
    private function getCategorizedSenders(int $userId): array
    {
        $tokens = $this->uploadsModel->getOwnedRawTokens($userId);
        if (empty($tokens)) {
            return [];
        }

        $db = \Config\Database::connect();
        $blockedMap = $this->getBlockedMap($userId);
        $globalAllowed = $this->getGlobalAllowedMap();

        $bySender = [];
        if ($db->tableExists('tbl_Sender_Profiles')) {
            $profiles = $db->table('tbl_Sender_Profiles')
                ->select('sp_number, sp_name, sp_category, sp_is_finance')
                ->whereIn('sp_owner', $tokens)
                ->get()
                ->getResultArray();

            foreach ($profiles as $p) {
                $sender = $p['sp_number'];
                if (!isset($bySender[$sender])) {
                    $bySender[$sender] = [
                        'sender' => $sender,
                        'name' => $p['sp_name'] ?? '',
                        'category' => $p['sp_category'] ?? '',
                        'is_finance' => (int) $p['sp_is_finance'],
                    ];
                }
            }
        }

        if ($db->tableExists('tbl_Sms_Classification') && $db->tableExists('tbl_Sms')) {
            $classification = $db->table('tbl_Sms_Classification c')
                ->select('c.sender, MAX(c.is_finance) AS is_finance')
                ->join('tbl_Sms s', 's.id = c.sms_id')
                ->whereIn('s.sms_owner', $tokens)
                ->groupBy('c.sender')
                ->get()
                ->getResultArray();

            foreach ($classification as $c) {
                $sender = $c['sender'];
                if (!isset($bySender[$sender])) {
                    $bySender[$sender] = [
                        'sender' => $sender,
                        'name' => '',
                        'category' => '',
                        'is_finance' => (int) $c['is_finance'],
                    ];
                }
            }
        }

        $senders = [];
        foreach ($bySender as $s) {
            $allowed = (bool) $s['is_finance'] || isset($globalAllowed[$s['sender']]);
            $senders[] = [
                'sender' => $s['sender'],
                'name' => $s['name'],
                'category' => $s['category'] ?: ($globalAllowed[$s['sender']] ?? ''),
                'is_finance' => (bool) $s['is_finance'],
                'allowed' => $allowed,
                'blocked' => isset($blockedMap[$s['sender']]),
                'blocked_at' => $blockedMap[$s['sender']] ?? null,
            ];
        }

        // Add hardcoded default finance senders that the user has no data for,
        // so the Allowed tab lists every "preselected good sender".
        foreach ($globalAllowed as $sender => $category) {
            if (!isset($bySender[$sender])) {
                $senders[] = [
                    'sender' => $sender,
                    'name' => '',
                    'category' => $category ?? '',
                    'is_finance' => true,
                    'allowed' => true,
                    'blocked' => isset($blockedMap[$sender]),
                    'blocked_at' => $blockedMap[$sender] ?? null,
                ];
            }
        }

        usort($senders, static fn ($a, $b) => strcasecmp($a['sender'], $b['sender']));

        return $senders;
    }
}
