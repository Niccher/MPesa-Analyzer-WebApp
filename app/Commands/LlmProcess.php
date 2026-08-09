<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class LlmProcess extends BaseCommand
{
    protected $group       = 'Maintenance';
    protected $name        = 'llm:process';
    protected $description = 'Process queued LLM analysis jobs by triggering the ML backend for each user.';

    public function run(array $params)
    {
        $db = \Config\Database::connect();

        if (!$db->tableExists('tbl_Processing_Jobs')) {
            CLI::write('No processing jobs table found. Nothing to do.', 'yellow');

            return;
        }

        $queued = $db->table('tbl_Processing_Jobs')
            ->where('status', 'queued')
            ->get()
            ->getResultArray();

        if (empty($queued)) {
            CLI::write('No queued processing jobs.', 'green');

            return;
        }

        $client = \Config\Services::curlrequest();
        $triggered = 0;
        $failed = 0;

        foreach ($queued as $job) {
            $userId = $job['user_id'];
            $jobId = $job['id'];

            $db->table('tbl_Processing_Jobs')
                ->where('id', $jobId)
                ->update(['status' => 'processing', 'started_at' => date('Y-m-d H:i:s')]);

            try {
                $mlBase = (string) config('MlBackend')->baseUrl;
                $resp = $client->post(
                    $mlBase . '/process/for-user/' . $userId,
                    ['timeout' => 10]
                );
                $body = json_decode($resp->getBody(), true);

                $messagesProcessed = (int)($body['messages_processed'] ?? 0);

                $db->table('tbl_Processing_Jobs')
                    ->where('id', $jobId)
                    ->update([
                        'status' => $body['status'] === 'skipped' ? 'queued' : 'completed',
                        'completed_at' => date('Y-m-d H:i:s'),
                        'duration_seconds' => 0,
                        'messages_processed' => $messagesProcessed,
                        'errors' => 0,
                    ]);

                if ($body['status'] === 'skipped') {
                    CLI::write('Job #' . $jobId . ' (user ' . $userId . '): scan already in progress, re-queued.', 'yellow');
                } else {
                    CLI::write('Job #' . $jobId . ' (user ' . $userId . '): processed ' . $messagesProcessed . ' messages.', 'green');
                    $triggered++;

                    if (\App\Libraries\Notifier::isTriggerEnabled('ml_complete')) {
                        $user = \auth()->getProvider()->findById($userId);
                        $userEmail = $user ? $user->email : '';
                        if (!empty($userEmail)) {
                            \App\Libraries\Notifier::send(
                                $userEmail,
                                'Your Mpesa Analyzer analysis is ready',
                                'Your spending analysis has finished processing. Log in to view your updated insights and results.'
                            );
                        }
                    }
                }
            } catch (\Throwable $e) {
                $db->table('tbl_Processing_Jobs')
                    ->where('id', $jobId)
                    ->update(['status' => 'failed', 'errors' => 1, 'completed_at' => date('Y-m-d H:i:s')]);
                CLI::write('Job #' . $jobId . ' (user ' . $userId . '): ' . $e->getMessage(), 'red');
                $failed++;
            }
        }

        CLI::write('Done. Triggered: ' . $triggered . ', failed: ' . $failed . '.', 'green');
    }
}
