<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class Telemetry extends BaseController
{
    use ResponseTrait;

    public function index()
    {
        $metrics = $this->collectAllMetrics();

        $data = [
            'title'     => 'Container Telemetry - SuperAdmin',
            'bg_color'  => '#B1B8ED',
            'metrics'   => $metrics,
        ];

        return view('Admin/Telemetry/index', $data);
    }

    public function live()
    {
        return $this->response->setJSON($this->collectAllMetrics());
    }

    private function collectAllMetrics(): array
    {
        return [
            'status'    => 'ok',
            'timestamp' => date('c'),
            'web'       => $this->getWebAppMetrics(),
            'mysql'     => $this->getMySqlMetrics(),
            'ml'        => $this->getMlMetrics(),
        ];
    }

    /**
     * Gather WebApp Container (PHP/System) metrics.
     */
    private function getWebAppMetrics(): array
    {
        // 1. CPU & Load
        $load = function_exists('sys_getloadavg') ? sys_getloadavg() : [0, 0, 0];
        $cpuCores = 1;
        if (is_readable('/proc/cpuinfo')) {
            $cpuinfo = @file_get_contents('/proc/cpuinfo');
            $cpuCores = max(1, substr_count((string)$cpuinfo, 'processor'));
        }
        $loadPct = $cpuCores > 0 ? min(100, round(($load[0] / $cpuCores) * 100, 1)) : 0;

        // 2. Host & Container RAM
        $memTotalMb = 0;
        $memAvailMb = 0;
        $memUsedMb = 0;
        if (is_readable('/proc/meminfo')) {
            $meminfo = @file_get_contents('/proc/meminfo');
            if ($meminfo) {
                $lines = explode("\n", $meminfo);
                $memData = [];
                foreach ($lines as $line) {
                    if (str_contains($line, ':')) {
                        [$k, $v] = explode(':', $line, 2);
                        $memData[trim($k)] = (int) filter_var($v, FILTER_SANITIZE_NUMBER_INT);
                    }
                }
                if (isset($memData['MemTotal'])) {
                    $memTotalMb = round($memData['MemTotal'] / 1024, 1);
                    $avail = $memData['MemAvailable'] ?? $memData['MemFree'] ?? 0;
                    $memAvailMb = round($avail / 1024, 1);
                    $memUsedMb = max(0, round($memTotalMb - $memAvailMb, 1));
                }
            }
        }

        // cgroup container memory limit & usage
        $containerMemUsedMb = null;
        $containerMemLimitMb = null;

        // cgroup v2
        if (is_readable('/sys/fs/cgroup/memory.current')) {
            $cur = trim((string) @file_get_contents('/sys/fs/cgroup/memory.current'));
            if (is_numeric($cur)) $containerMemUsedMb = round($cur / 1048576, 1);
        }
        if (is_readable('/sys/fs/cgroup/memory.max')) {
            $max = trim((string) @file_get_contents('/sys/fs/cgroup/memory.max'));
            if (is_numeric($max)) $containerMemLimitMb = round($max / 1048576, 1);
        }

        // cgroup v1 fallback
        if ($containerMemUsedMb === null && is_readable('/sys/fs/cgroup/memory/memory.usage_in_bytes')) {
            $cur = trim((string) @file_get_contents('/sys/fs/cgroup/memory/memory.usage_in_bytes'));
            if (is_numeric($cur)) $containerMemUsedMb = round($cur / 1048576, 1);
        }
        if ($containerMemLimitMb === null && is_readable('/sys/fs/cgroup/memory/memory.limit_in_bytes')) {
            $max = trim((string) @file_get_contents('/sys/fs/cgroup/memory/memory.limit_in_bytes'));
            if (is_numeric($max) && (float)$max < 9223372036854771712) {
                $containerMemLimitMb = round($max / 1048576, 1);
            }
        }

        // 3. PHP Engine Memory
        $phpMemAllocatedMb = round(memory_get_usage(true) / 1048576, 1);
        $phpMemPeakMb      = round(memory_get_peak_usage(true) / 1048576, 1);
        $phpMemLimit       = ini_get('memory_limit');

        // 4. Disk Storage
        $rootTotalMb = @disk_total_space('/') ? round(@disk_total_space('/') / 1048576, 1) : 0;
        $rootFreeMb  = @disk_free_space('/') ? round(@disk_free_space('/') / 1048576, 1) : 0;
        $rootUsedMb  = max(0, $rootTotalMb - $rootFreeMb);
        $rootUsedPct = $rootTotalMb > 0 ? round(($rootUsedMb / $rootTotalMb) * 100, 1) : 0;

        // 5. Uptime
        $uptimeSeconds = 0;
        if (is_readable('/proc/uptime')) {
            $up = @file_get_contents('/proc/uptime');
            if ($up) $uptimeSeconds = (int) explode(' ', trim($up))[0];
        }

        // 6. Active sessions count
        $sessionCount = 0;
        $sessionPath = WRITEPATH . 'session';
        if (is_dir($sessionPath)) {
            $files = @scandir($sessionPath);
            if ($files) $sessionCount = max(0, count($files) - 2); // subtract . and ..
        }

        return [
            'status'             => 'online',
            'uptime_seconds'     => $uptimeSeconds,
            'uptime_formatted'   => $this->formatUptime($uptimeSeconds),
            'cpu' => [
                'cores'    => $cpuCores,
                'load_1m'  => round($load[0], 2),
                'load_5m'  => round($load[1], 2),
                'load_15m' => round($load[2], 2),
                'load_pct' => $loadPct,
            ],
            'memory' => [
                'host_total_mb'      => $memTotalMb,
                'host_used_mb'       => $memUsedMb,
                'host_avail_mb'      => $memAvailMb,
                'host_used_pct'      => $memTotalMb > 0 ? round(($memUsedMb / $memTotalMb) * 100, 1) : 0,
                'container_used_mb'  => $containerMemUsedMb ?? $memUsedMb,
                'container_limit_mb' => $containerMemLimitMb,
                'container_used_pct' => ($containerMemLimitMb && $containerMemLimitMb > 0) ? min(100, round((($containerMemUsedMb ?? $memUsedMb) / $containerMemLimitMb) * 100, 1)) : ($memTotalMb > 0 ? round(($memUsedMb / $memTotalMb) * 100, 1) : 0),
                'php_allocated_mb'   => $phpMemAllocatedMb,
                'php_peak_mb'        => $phpMemPeakMb,
                'php_limit'          => $phpMemLimit,
            ],
            'disk' => [
                'total_mb' => $rootTotalMb,
                'used_mb'  => $rootUsedMb,
                'free_mb'  => $rootFreeMb,
                'used_pct' => $rootUsedPct,
            ],
            'runtime' => [
                'php_version' => PHP_VERSION,
                'sapi'        => php_sapi_name(),
                'server'      => $_SERVER['SERVER_SOFTWARE'] ?? 'Nginx / Web Container',
                'sessions'    => $sessionCount,
            ]
        ];
    }

    /**
     * Gather MySQL Database metrics.
     */
    private function getMySqlMetrics(): array
    {
        try {
            $start = microtime(true);
            $db = \Config\Database::connect();
            $schema = $db->database;

            // Ping connection
            $ping = $db->query('SELECT 1')->getRow();
            $queryLatencyMs = round((microtime(true) - $start) * 1000, 1);

            // Fetch GLOBAL STATUS
            $statusRows = $db->query('SHOW GLOBAL STATUS')->getResultArray();
            $status = [];
            foreach ($statusRows as $row) {
                $status[$row['Variable_name']] = $row['Value'];
            }

            // Fetch GLOBAL VARIABLES
            $varRows = $db->query('SHOW GLOBAL VARIABLES')->getResultArray();
            $variables = [];
            foreach ($varRows as $row) {
                $variables[$row['Variable_name']] = $row['Value'];
            }

            // Storage & Tables info
            $statsRow = $db->query(
                "SELECT
                    COUNT(*) AS table_count,
                    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb,
                    ROUND(SUM(data_length) / 1024 / 1024, 2) AS data_mb,
                    ROUND(SUM(index_length) / 1024 / 1024, 2) AS index_mb,
                    COALESCE(SUM(table_rows), 0) AS approx_rows
                FROM information_schema.tables
                WHERE table_schema = ?",
                [$schema]
            )->getRowArray();

            $uptime = (int) ($status['Uptime'] ?? 0);
            $questions = (int) ($status['Questions'] ?? $status['Queries'] ?? 0);
            $qps = $uptime > 0 ? round($questions / $uptime, 2) : 0;

            // Connections
            $connected = (int) ($status['Threads_connected'] ?? 0);
            $running   = (int) ($status['Threads_running'] ?? 0);
            $maxConn   = (int) ($variables['max_connections'] ?? 151);
            $connPct   = $maxConn > 0 ? min(100, round(($connected / $maxConn) * 100, 1)) : 0;

            // InnoDB Buffer Pool
            $bpSize = (int) ($variables['innodb_buffer_pool_size'] ?? 134217728);
            $bpData = (int) ($status['Innodb_buffer_pool_bytes_data'] ?? 0);
            $bpSizeMb = round($bpSize / 1048576, 1);
            $bpDataMb = round($bpData / 1048576, 1);
            $bpUsedPct = $bpSize > 0 ? min(100, round(($bpData / $bpSize) * 100, 1)) : 0;

            // Network I/O
            $bytesRecvMb = round(((float)($status['Bytes_received'] ?? 0)) / 1048576, 2);
            $bytesSentMb = round(((float)($status['Bytes_sent'] ?? 0)) / 1048576, 2);

            return [
                'status'           => 'online',
                'latency_ms'       => $queryLatencyMs,
                'version'          => $variables['version'] ?? 'Unknown',
                'version_comment'  => $variables['version_comment'] ?? '',
                'uptime_seconds'   => $uptime,
                'uptime_formatted' => $this->formatUptime($uptime),
                'database'         => $schema,
                'tables_count'     => (int) ($statsRow['table_count'] ?? 0),
                'database_size_mb' => (float) ($statsRow['size_mb'] ?? 0),
                'approx_rows'      => (int) ($statsRow['approx_rows'] ?? 0),
                'connections' => [
                    'connected'    => $connected,
                    'running'      => $running,
                    'max'          => $maxConn,
                    'used_pct'     => $connPct,
                    'max_used'     => (int) ($status['Max_used_connections'] ?? $connected),
                ],
                'throughput' => [
                    'questions'    => $questions,
                    'qps'          => $qps,
                    'bytes_received_mb' => $bytesRecvMb,
                    'bytes_sent_mb'     => $bytesSentMb,
                ],
                'buffer_pool' => [
                    'size_mb'      => $bpSizeMb,
                    'data_mb'      => $bpDataMb,
                    'used_pct'     => $bpUsedPct,
                ]
            ];
        } catch (\Throwable $e) {
            return [
                'status'     => 'offline',
                'error'      => $e->getMessage(),
                'latency_ms' => 0,
            ];
        }
    }

    /**
     * Gather ML Engine Container metrics from FastAPI.
     */
    private function getMlMetrics(): array
    {
        $baseUrl = rtrim((string) config('MlBackend')->baseUrl, '/');
        $url = $baseUrl . '/admin/telemetry';

        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);

            $start = microtime(true);
            $res = curl_exec($ch);
            $latencyMs = round((microtime(true) - $start) * 1000, 1);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr = curl_error($ch);
            curl_close($ch);

            if ($curlErr || $httpCode !== 200) {
                // Fallback to basic /health or /admin/status if /telemetry endpoint isn't deployed yet
                return $this->getMlFallbackMetrics($baseUrl, $curlErr ?: ("HTTP " . $httpCode));
            }

            $data = json_decode($res, true);
            if (!is_array($data) || ($data['status'] ?? '') !== 'ok') {
                return $this->getMlFallbackMetrics($baseUrl, 'Invalid telemetry JSON response');
            }

            $data['status']     = 'online';
            $data['latency_ms'] = $latencyMs;
            $data['uptime_formatted'] = $this->formatUptime((int)($data['uptime_seconds'] ?? 0));

            return $data;
        } catch (\Throwable $e) {
            return [
                'status'     => 'offline',
                'error'      => $e->getMessage(),
                'latency_ms' => 0,
            ];
        }
    }

    private function getMlFallbackMetrics(string $baseUrl, string $originalError): array
    {
        try {
            $ch = curl_init($baseUrl . '/admin/status');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            $res = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200 && $res) {
                $status = json_decode($res, true);
                return [
                    'status' => 'online',
                    'latency_ms' => 50,
                    'cpu' => ['cores' => 1, 'load_1m' => 0, 'load_pct' => 0],
                    'memory' => ['host_total_mb' => 0, 'host_used_mb' => 0, 'container_used_mb' => 0],
                    'gpu' => ['has_gpu' => false, 'mode' => 'CPU Inference (AVX/OpenMP)'],
                    'inference' => [
                        'engine' => $status['app']['llm_engine'] ?? 'local_gguf',
                        'active_model' => basename($status['app']['model_path'] ?? 'qwen2.5-1.5b-instruct-q4_k_m.gguf'),
                        'llama_running' => ($status['llama'] ?? '') === 'ok',
                        'ctx_size' => $status['app']['llm_ctx_size'] ?? 16384,
                    ],
                    'queue' => ['active_jobs' => 0, 'queued_jobs' => 0, 'completed_today' => 0]
                ];
            }
        } catch (\Throwable) {}

        return [
            'status'     => 'offline',
            'error'      => $originalError,
            'latency_ms' => 0,
        ];
    }

    private function formatUptime(int $seconds): string
    {
        if ($seconds <= 0) return '0s';
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $mins = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        $parts = [];
        if ($days > 0) $parts[] = "{$days}d";
        if ($hours > 0) $parts[] = "{$hours}h";
        if ($mins > 0) $parts[] = "{$mins}m";
        if ($days == 0 && count($parts) < 2) $parts[] = "{$secs}s";

        return implode(' ', $parts);
    }
}
