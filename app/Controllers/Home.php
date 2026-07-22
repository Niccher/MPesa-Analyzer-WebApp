<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;

class Home extends BaseController
{
    use ResponseTrait;
    public function index()
    {
        return view('landing');
    }

    public function androidApp()
    {
        return view('android_app');
    }

    public function mlBackend()
    {
        return view('ml_backend');
    }

    public function setup()
    {
        return view('setup');
    }

    public function faq()
    {
        return view('faq');
    }

    public function rescan()
    {
        $userId = auth()->user()->id;
        $fastApiUrl = 'http://mpesa-analyser-docker:9050/process/for-user/' . $userId;

        $client = \Config\Services::curlrequest();
        try {
            $resp = $client->post($fastApiUrl, ['timeout' => 5]);
            $body = json_decode($resp->getBody(), true);
            if ($body && ($body['status'] === 'done' || isset($body['messages_processed']))) {
                return $this->response->setJSON([
                    'status'  => 'started',
                    'message' => 'LLM analysis triggered. ' . ($body['messages_processed'] ?? 0) . ' messages processed.',
                ]);
            }
            if ($body && $body['status'] === 'skipped') {
                return $this->response->setJSON([
                    'status'  => 'started',
                    'message' => 'A scan is already in progress. Check back shortly.',
                ]);
            }
            return $this->response->setJSON([
                'status'  => 'started',
                'message' => 'LLM analysis has been queued.',
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Rescan failed: ' . $e->getMessage());
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Could not reach the analysis service. Please try again later.',
            ]);
        }
    }

    public function progress()
    {
        $userId = auth()->user()->id;
        $db = \Config\Database::connect();
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

        foreach ($tokenRows as $r) {
            if (!empty($r->tk)) $rawTokens[] = $r->tk;
        }

        $total = 0;
        $statuses = [];
        $job = null;
        $llmClassified = 0;

        if (!empty($rawTokens)) {
            $total = $db->table('tbl_Sms')
                ->whereIn('sms_owner', $rawTokens)
                ->countAllResults();

            // Count SMS classified by the LLM (method = 'llm')
            if ($db->tableExists('tbl_Sms_Classification')) {
                $llmRow = $db->table('tbl_Sms_Classification sc')
                    ->select('COUNT(*) as cnt')
                    ->join('tbl_Sms s', 's.id = sc.sms_id')
                    ->whereIn('s.sms_owner', $rawTokens)
                    ->where('sc.method', 'llm')
                    ->get()
                    ->getRow();
                $llmClassified = (int)($llmRow->cnt ?? 0);
            }

            // Count per-SMS processing status (populated by FastAPI on some setups)
            if ($db->tableExists('tbl_Sms_Processing')) {
                $statusRows = $db->table('tbl_Sms_Processing sp')
                    ->select('sp.status, COUNT(*) as cnt')
                    ->join('tbl_Sms s', 's.id = sp.sms_id')
                    ->whereIn('s.sms_owner', $rawTokens)
                    ->groupBy('sp.status')
                    ->get()
                    ->getResult();
                foreach ($statusRows as $r) {
                    $statuses[$r->status] = (int)$r->cnt;
                }
            }
        }

        if ($db->tableExists('tbl_Processing_Jobs')) {
            $job = $db->table('tbl_Processing_Jobs')
                ->where('user_id', (string)$userId)
                ->orderBy('id', 'DESC')
                ->limit(1)
                ->get()
                ->getRowArray();
        }

        // Use the better of the two progress signals
        $processingDone = ($statuses['completed'] ?? 0) + ($statuses['error'] ?? 0);
        $processed = max($processingDone, $llmClassified);

        // Determine if a scan is actively running
        $hasProcessingRows = ($statuses['processing'] ?? 0) > 0;
        $jobActive = $job && in_array($job['status'], ['queued', 'processing'], true);
        $jobJustCompleted = $job && in_array($job['status'], ['completed', 'failed'], true);
        $hasPendingWork = $processed > 0 && $processed < $total && $total > 0;

        $running = $hasProcessingRows || $jobActive || ($hasPendingWork && !$jobJustCompleted);

        return $this->response->setJSON([
            'status'          => $job['status'] ?? 'idle',
            'total'           => $total,
            'processed'       => $processed,
            'llm_classified'  => $llmClassified,
            'completed'       => (int)($statuses['completed'] ?? 0),
            'errors'          => (int)($statuses['error'] ?? 0) + (int)($job['errors'] ?? 0),
            'pending'         => (int)($statuses['pending'] ?? 0),
            'running'         => $running,
            'job'             => $job,
        ]);
    }

    /**
     * Full reprocess: resets all processing flags and re-triggers LLM.
     * This clears tbl_Sms_Processing, tbl_Analyzed_Transactions,
     * tbl_Sms_Classification (llm-method), and tbl_Sender_Profiles
     * for the current user's tokens, so the LLM re-classifies everything from scratch.
     */
    public function rescanAll()
    {
        $userId = auth()->user()->id;
        $db = \Config\Database::connect();
        $tokenType = \CodeIgniter\Shield\Authentication\Authenticators\AccessTokens::ID_TYPE_ACCESS_TOKEN;

        // Discover raw tokens via SHA2 match
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

        foreach ($tokenRows as $r) {
            if (!empty($r->tk)) $rawTokens[] = $r->tk;
        }

        if (empty($rawTokens)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'No uploaded data found for this account.',
            ]);
        }

        $smsIds = [];
        $smsRows = $db->table('tbl_Sms')->select('id')->whereIn('sms_owner', $rawTokens)->get()->getResult();
        foreach ($smsRows as $r) $smsIds[] = $r->id;

        $db->transStart();

        // Reset processing tracking
        if (!empty($smsIds) && $db->tableExists('tbl_Sms_Processing')) {
            $db->table('tbl_Sms_Processing')->whereIn('sms_id', $smsIds)->delete();
        }

        // Remove LLM classifications (keep upload-method ones as fallback)
        if (!empty($smsIds) && $db->tableExists('tbl_Sms_Classification')) {
            $db->table('tbl_Sms_Classification')
               ->whereIn('sms_id', $smsIds)
               ->where('method', 'llm')
               ->delete();
        }

        // Remove analyzed transactions
        if ($db->tableExists('tbl_Analyzed_Transactions') && $db->tableExists('tbl_Sms')) {
            $db->query("
                DELETE a FROM tbl_Analyzed_Transactions a
                INNER JOIN tbl_Sms s ON s.id = a.orig_sms_int_id OR s.sms__id = a.orig_sms_id
                INNER JOIN auth_identities i ON i.secret = SHA2(s.sms_owner, 256)
                WHERE i.user_id = ? AND i.type = ?
            ", [$userId, $tokenType]);
        }

        // Remove sender profiles
        if ($db->tableExists('tbl_Sender_Profiles')) {
            $db->table('tbl_Sender_Profiles')->whereIn('sp_owner', $rawTokens)->delete();
        }

        $db->transComplete();

        // Now trigger the LLM pipeline
        $fastApiUrl = 'http://mpesa-analyser-docker:9050/process/for-user/' . $userId;
        $client = \Config\Services::curlrequest();
        try {
            $resp = $client->post($fastApiUrl, ['timeout' => 10]);
            $body = json_decode($resp->getBody(), true);
            $count = $body['messages_processed'] ?? 0;
            return $this->response->setJSON([
                'status'  => 'started',
                'message' => "Full reprocess triggered. Processing {$count} messages.",
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Reprocess LLM trigger failed: ' . $e->getMessage());
            return $this->response->setJSON([
                'status'  => 'started',
                'message' => 'Processing flags reset. Triggering LLM analysis failed — it may auto-start on next poll cycle.',
            ]);
        }
    }
}
