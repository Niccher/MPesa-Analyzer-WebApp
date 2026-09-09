<?= $this->extend('Layouts/superadmin') ?>
<?= $this->section('title') ?> Container Telemetry - Mpesa Analyzer <?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .telemetry-card {
        border: none;
        border-radius: 6px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.04);
        background: var(--card-bg, #ffffff);
        border: 1px solid var(--card-border, #e2e8f0);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .telemetry-header-card {
        background: linear-gradient(135deg, #438EB9 0%, #2e6280 100%);
        color: #ffffff;
        border: none;
        border-radius: 6px;
    }
    .live-dot {
        display: inline-block;
        width: 10px;
        height: 10px;
        background-color: #28a745;
        border-radius: 50%;
        box-shadow: 0 0 0 rgba(40, 167, 69, 0.4);
        animation: pulse 1.8s infinite;
    }
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.6); }
        70% { box-shadow: 0 0 0 8px rgba(40, 167, 69, 0); }
        100% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0); }
    }
    .metric-badge {
        font-size: 0.72rem;
        padding: 0.25rem 0.6rem;
        border-radius: 50rem;
        font-weight: 600;
    }
    .progress-bar {
        transition: width 0.4s ease;
    }
    .stat-pill {
        background: rgba(0,0,0,0.03);
        border: 1px solid rgba(0,0,0,0.05);
        border-radius: 4px;
        padding: 0.5rem 0.75rem;
    }
    [data-bs-theme="dark"] .stat-pill {
        background: rgba(255,255,255,0.04);
        border: 1px solid rgba(255,255,255,0.08);
    }
    .telemetry-sparkline {
        height: 28px;
        width: 100%;
        overflow: visible;
        display: block;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);">
            <i class="fa-solid fa-chart-line me-2"></i> Container Telemetry
        </h2>
        <p class="text-secondary mb-0">Low-overhead live resource monitoring across WebApp, MySQL, and ML Inference containers.</p>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <div class="d-flex align-items-center gap-2 bg-body-tertiary px-3 py-1 rounded border small">
            <span class="live-dot" id="livePulse"></span>
            <span class="fw-semibold" id="liveStatusText">Live Monitoring</span>
            <span class="text-muted" style="font-size: 0.75rem;" id="lastUpdatedText">Just now</span>
        </div>
        <select class="form-select form-select-sm w-auto" id="pollIntervalSelect" title="Auto-refresh rate">
            <option value="5000" selected>Refresh: 5s (Optimal)</option>
            <option value="10000">Refresh: 10s</option>
            <option value="30000">Refresh: 30s</option>
            <option value="0">Paused</option>
        </select>
        <button class="btn btn-sm btn-primary rounded-pill px-3" id="manualRefreshBtn">
            <i class="fa-solid fa-rotate me-1" id="refreshIcon"></i> Refresh Now
        </button>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
$web = $metrics['web'] ?? [];
$mysql = $metrics['mysql'] ?? [];
$ml = $metrics['ml'] ?? [];
?>

<!-- Quick Overview KPIs with Live Sparklines -->
<div class="row g-3 mb-4">
    <!-- WebApp Container -->
    <div class="col-md-4">
        <div class="card telemetry-card p-3 h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-bold text-uppercase"><i class="fa-brands fa-php me-1 text-primary"></i> WebApp Container</span>
                <div class="d-flex gap-1 align-items-center">
                    <span class="badge bg-danger metric-badge <?= !empty($web['memory']['container_oom_warning']) ? '' : 'd-none' ?>" id="kpiWebOomBadge" title="Approaching Railway memory limit">
                        <i class="fa-solid fa-triangle-exclamation"></i> 80%+ OOM Risk
                    </span>
                    <span class="badge bg-success metric-badge" id="kpiWebStatus"><?= esc($web['status'] ?? 'online') ?></span>
                </div>
            </div>
            <div class="d-flex align-items-baseline gap-2 mb-2">
                <h3 class="fw-bold mb-0" id="kpiWebCpu"><?= esc($web['cpu']['load_pct'] ?? 0) ?>%</h3>
                <span class="text-muted small">CPU Load</span>
            </div>
            <div class="small text-muted d-flex justify-content-between mb-2">
                <span>RAM: <strong id="kpiWebRam"><?= number_format($web['memory']['container_used_mb'] ?? 0) ?> MB</strong></span>
                <span>Uptime: <strong id="kpiWebUptime"><?= esc($web['uptime_formatted'] ?? '0s') ?></strong></span>
            </div>
            <!-- Live CPU Sparkline -->
            <div class="pt-2 border-top">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="text-muted" style="font-size: 0.7rem;">CPU Load Trend</span>
                    <span class="text-muted font-monospace" style="font-size: 0.7rem;" id="sparkWebCpuVal"><?= esc($web['cpu']['load_pct'] ?? 0) ?>%</span>
                </div>
                <svg id="sparkWebCpu" class="telemetry-sparkline"></svg>
            </div>
        </div>
    </div>

    <!-- MySQL Container -->
    <div class="col-md-4">
        <div class="card telemetry-card p-3 h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-bold text-uppercase"><i class="fa-solid fa-database me-1 text-info"></i> MySQL Container</span>
                <span class="badge bg-success metric-badge" id="kpiMysqlStatus"><?= esc($mysql['status'] ?? 'online') ?></span>
            </div>
            <div class="d-flex align-items-baseline gap-2 mb-2">
                <h3 class="fw-bold mb-0" id="kpiMysqlConn"><?= esc($mysql['connections']['connected'] ?? 0) ?></h3>
                <span class="text-muted small">Connections (<?= esc($mysql['connections']['used_pct'] ?? 0) ?>%)</span>
            </div>
            <div class="small text-muted d-flex justify-content-between mb-2">
                <span>QPS: <strong id="kpiMysqlQps"><?= esc($mysql['throughput']['qps'] ?? 0) ?></strong></span>
                <span>DB Size: <strong id="kpiMysqlDbSize"><?= number_format($mysql['database_size_mb'] ?? 0, 1) ?> MB</strong></span>
            </div>
            <!-- Live QPS Sparkline -->
            <div class="pt-2 border-top">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="text-muted" style="font-size: 0.7rem;">QPS Throughput Trend</span>
                    <span class="text-muted font-monospace" style="font-size: 0.7rem;" id="sparkMysqlQpsVal"><?= esc($mysql['throughput']['qps'] ?? 0) ?> QPS</span>
                </div>
                <svg id="sparkMysqlQps" class="telemetry-sparkline"></svg>
            </div>
        </div>
    </div>

    <!-- ML Engine Container -->
    <div class="col-md-4">
        <div class="card telemetry-card p-3 h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-bold text-uppercase"><i class="fa-solid fa-microchip me-1 text-warning"></i> ML Engine Container</span>
                <span class="badge bg-<?= ($ml['status'] ?? '') === 'online' ? 'success' : 'danger' ?> metric-badge" id="kpiMlStatus"><?= esc($ml['status'] ?? 'offline') ?></span>
            </div>
            <div class="d-flex align-items-baseline gap-2 mb-2">
                <h3 class="fw-bold mb-0" id="kpiMlMode">
                    <?= !empty($ml['gpu']['has_gpu']) ? 'GPU' : 'CPU' ?>
                </h3>
                <span class="text-muted small" id="kpiMlLatency">(<?= esc($ml['latency_ms'] ?? 0) ?> ms API latency)</span>
            </div>
            <div class="small text-muted d-flex justify-content-between mb-2">
                <span>Active: <strong id="kpiMlModel" class="text-truncate" style="max-width: 130px;"><?= esc($ml['inference']['active_model'] ?? 'None') ?></strong></span>
                <span>Queued: <strong id="kpiMlQueue"><?= esc($ml['queue']['active_jobs'] ?? 0) ?> active</strong></span>
            </div>
            <!-- Live Latency Sparkline -->
            <div class="pt-2 border-top">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="text-muted" style="font-size: 0.7rem;">Inference Latency Trend</span>
                    <span class="text-muted font-monospace" style="font-size: 0.7rem;" id="sparkMlLatencyVal"><?= esc($ml['latency_ms'] ?? 0) ?> ms</span>
                </div>
                <svg id="sparkMlLatency" class="telemetry-sparkline"></svg>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Metrics Sections -->
<div class="row g-4">
    <!-- 1. WebApp Container Deep Diagnostics -->
    <div class="col-lg-6">
        <div class="card telemetry-card mb-4 h-100">
            <div class="card-header bg-transparent border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-primary">
                    <i class="fa-brands fa-php me-2"></i> WebApp Container Diagnostics
                </h5>
                <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1">PHP <?= esc($web['runtime']['php_version'] ?? PHP_VERSION) ?></span>
            </div>
            <div class="card-body">
                <!-- CPU Load -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small fw-semibold">CPU Load (<?= esc($web['cpu']['cores'] ?? 1) ?> Core<?= ($web['cpu']['cores'] ?? 1) > 1 ? 's' : '' ?>)</span>
                        <span class="small fw-bold" id="webCpuPctText"><?= esc($web['cpu']['load_pct'] ?? 0) ?>%</span>
                    </div>
                    <div class="progress mb-2" style="height: 8px;">
                        <div class="progress-bar bg-primary" id="webCpuBar" style="width: <?= esc($web['cpu']['load_pct'] ?? 0) ?>%;"></div>
                    </div>
                    <div class="d-flex gap-2">
                        <span class="stat-pill small text-muted flex-fill text-center">1m: <strong id="webLoad1m"><?= esc($web['cpu']['load_1m'] ?? 0) ?></strong></span>
                        <span class="stat-pill small text-muted flex-fill text-center">5m: <strong id="webLoad5m"><?= esc($web['cpu']['load_5m'] ?? 0) ?></strong></span>
                        <span class="stat-pill small text-muted flex-fill text-center">15m: <strong id="webLoad15m"><?= esc($web['cpu']['load_15m'] ?? 0) ?></strong></span>
                    </div>
                </div>

                <!-- Memory (RAM) -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small fw-semibold">Container RAM Allocation</span>
                        <span class="small fw-bold" id="webRamText">
                            <?= number_format($web['memory']['container_used_mb'] ?? 0) ?> MB / <?= $web['memory']['container_limit_mb'] ? number_format($web['memory']['container_limit_mb']) . ' MB' : number_format($web['memory']['host_total_mb'] ?? 0) . ' MB (Host)' ?>
                        </span>
                    </div>
                    <div class="progress mb-2" style="height: 8px;">
                        <div class="progress-bar bg-info" id="webRamBar" style="width: <?= esc($web['memory']['container_used_pct'] ?? 0) ?>%;"></div>
                    </div>
                    <div class="row g-2 text-center small text-muted">
                        <div class="col-4 stat-pill">
                            <div>PHP Current</div>
                            <strong class="text-dark" id="webPhpAlloc"><?= number_format($web['memory']['php_allocated_mb'] ?? 0) ?> MB</strong>
                        </div>
                        <div class="col-4 stat-pill">
                            <div>PHP Peak</div>
                            <strong class="text-dark" id="webPhpPeak"><?= number_format($web['memory']['php_peak_mb'] ?? 0) ?> MB</strong>
                        </div>
                        <div class="col-4 stat-pill">
                            <div>PHP Limit</div>
                            <strong class="text-dark" id="webPhpLimit"><?= esc($web['memory']['php_limit'] ?? 'N/A') ?></strong>
                        </div>
                    </div>
                </div>

                <!-- Disk Space -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small fw-semibold">Disk Storage (Root Volume)</span>
                        <span class="small fw-bold" id="webDiskText">
                            <?= number_format(($web['disk']['used_mb'] ?? 0) / 1024, 2) ?> GB / <?= number_format(($web['disk']['total_mb'] ?? 0) / 1024, 2) ?> GB
                        </span>
                    </div>
                    <div class="progress mb-2" style="height: 8px;">
                        <div class="progress-bar bg-warning" id="webDiskBar" style="width: <?= esc($web['disk']['used_pct'] ?? 0) ?>%;"></div>
                    </div>
                    <div class="small text-muted d-flex justify-content-between">
                        <span>Free Space: <strong id="webDiskFree"><?= number_format(($web['disk']['free_mb'] ?? 0) / 1024, 2) ?> GB</strong></span>
                        <span>Usage: <strong id="webDiskPct"><?= esc($web['disk']['used_pct'] ?? 0) ?>%</strong></span>
                    </div>
                </div>

                <hr class="my-3 opacity-25">
                <div class="row g-2 text-muted small">
                    <div class="col-6">Server: <strong class="text-dark" id="webServer"><?= esc($web['runtime']['server'] ?? 'Nginx Container') ?></strong></div>
                    <div class="col-6 text-end">Active Sessions: <strong class="text-dark" id="webSessions"><?= esc($web['runtime']['sessions'] ?? 0) ?></strong></div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. MySQL Container Diagnostics -->
    <div class="col-lg-6">
        <div class="card telemetry-card mb-4 h-100">
            <div class="card-header bg-transparent border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-info">
                    <i class="fa-solid fa-database me-2"></i> MySQL Container Diagnostics
                </h5>
                <span class="badge bg-info-subtle text-info rounded-pill px-3 py-1" id="mysqlVersion">
                    <?= esc($mysql['version'] ?? 'MySQL') ?>
                </span>
            </div>
            <div class="card-body">
                <!-- Client Connection Pool -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small fw-semibold">Client Connection Pool</span>
                        <span class="small fw-bold" id="mysqlConnText">
                            <?= esc($mysql['connections']['connected'] ?? 0) ?> / <?= esc($mysql['connections']['max'] ?? 151) ?> max (<?= esc($mysql['connections']['used_pct'] ?? 0) ?>%)
                        </span>
                    </div>
                    <div class="progress mb-2" style="height: 8px;">
                        <div class="progress-bar bg-info" id="mysqlConnBar" style="width: <?= esc($mysql['connections']['used_pct'] ?? 0) ?>%;"></div>
                    </div>
                    <div class="d-flex gap-2">
                        <span class="stat-pill small text-muted flex-fill text-center">Active Running: <strong id="mysqlThreadsRunning"><?= esc($mysql['connections']['running'] ?? 0) ?></strong></span>
                        <span class="stat-pill small text-muted flex-fill text-center">Peak Used: <strong id="mysqlMaxUsed"><?= esc($mysql['connections']['max_used'] ?? 0) ?></strong></span>
                        <span class="stat-pill small text-muted flex-fill text-center">Latency: <strong id="mysqlLatency"><?= esc($mysql['latency_ms'] ?? 0) ?> ms</strong></span>
                    </div>
                </div>

                <!-- InnoDB Buffer Pool RAM -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small fw-semibold">InnoDB Buffer Pool Memory (RAM)</span>
                        <span class="small fw-bold" id="mysqlBpText">
                            <?= number_format($mysql['buffer_pool']['data_mb'] ?? 0, 1) ?> MB / <?= number_format($mysql['buffer_pool']['size_mb'] ?? 0, 1) ?> MB (<?= esc($mysql['buffer_pool']['used_pct'] ?? 0) ?>%)
                        </span>
                    </div>
                    <div class="progress mb-2" style="height: 8px;">
                        <div class="progress-bar bg-success" id="mysqlBpBar" style="width: <?= esc($mysql['buffer_pool']['used_pct'] ?? 0) ?>%;"></div>
                    </div>
                    <div class="row g-2 text-center small text-muted">
                        <div class="col-4 stat-pill">
                            <div>Total Queries</div>
                            <strong class="text-dark" id="mysqlTotalQ"><?= number_format($mysql['throughput']['questions'] ?? 0) ?></strong>
                        </div>
                        <div class="col-4 stat-pill">
                            <div>Queries / Sec</div>
                            <strong class="text-dark text-success" id="mysqlQps"><?= esc($mysql['throughput']['qps'] ?? 0) ?></strong>
                        </div>
                        <div class="col-4 stat-pill">
                            <div>I/O Received</div>
                            <strong class="text-dark" id="mysqlRecv"><?= number_format($mysql['throughput']['bytes_received_mb'] ?? 0, 1) ?> MB</strong>
                        </div>
                    <div class="row g-2 text-center small text-muted mt-1">
                        <div class="col-6 stat-pill">
                            <div>Slow Queries (Unindexed)</div>
                            <strong class="text-dark <?= !empty($mysql['throughput']['slow_queries']) ? 'text-danger' : '' ?>" id="mysqlSlowQ"><?= number_format($mysql['throughput']['slow_queries'] ?? 0) ?></strong>
                        </div>
                        <div class="col-6 stat-pill">
                            <div>Aborted Connects</div>
                            <strong class="text-dark" id="mysqlAbortedConnects"><?= number_format($mysql['connections']['aborted'] ?? 0) ?></strong>
                        </div>
                    </div>
                </div>

                <!-- Database Size and Tables -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small fw-semibold">Database Schema Storage Size</span>
                        <span class="small fw-bold" id="mysqlDbSizeText"><?= number_format($mysql['database_size_mb'] ?? 0, 2) ?> MB</span>
                    </div>
                    <div class="d-flex justify-content-between small text-muted">
                        <span>Tables Count: <strong id="mysqlTables"><?= esc($mysql['tables_count'] ?? 0) ?> tables</strong></span>
                        <span>Approx Total Rows: <strong id="mysqlRows"><?= number_format($mysql['approx_rows'] ?? 0) ?></strong></span>
                    </div>
                </div>

                <hr class="my-3 opacity-25">
                <div class="row g-2 text-muted small">
                    <div class="col-6">Schema: <strong class="text-dark"><?= esc($mysql['database'] ?? 'mpesa_analyzer') ?></strong></div>
                    <div class="col-6 text-end">Uptime: <strong class="text-dark" id="mysqlUptime"><?= esc($mysql['uptime_formatted'] ?? '0s') ?></strong></div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. ML Classifier Engine Container -->
    <div class="col-12">
        <div class="card telemetry-card">
            <div class="card-header bg-transparent border-0 pt-3 pb-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="fw-bold mb-0 text-warning">
                    <i class="fa-solid fa-microchip me-2"></i> ML Classifier Engine &amp; Hardware Diagnostics
                </h5>
                <div class="d-flex gap-2 align-items-center">
                    <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-3 py-1" id="mlLlamaStatus">
                        Llama Server: <?= !empty($ml['inference']['llama_running']) ? 'Running (PID ' . esc($ml['inference']['llama_pid']) . ')' : 'Standby / Stopped' ?>
                    </span>
                    <span class="badge bg-secondary rounded-pill px-3 py-1" id="mlLatencyBadge">
                        API: <?= esc($ml['latency_ms'] ?? 0) ?> ms
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <!-- Left: Hardware & RAM -->
                    <div class="col-lg-6">
                        <h6 class="fw-bold mb-3 small text-muted text-uppercase">Hardware &amp; Execution Environment</h6>

                        <!-- GPU / Hardware Acceleration Badge -->
                        <div class="p-3 border rounded mb-3" id="gpuCardWrapper">
                            <?php if (!empty($ml['gpu']['has_gpu'])): ?>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold text-success"><i class="fa-solid fa-bolt me-1"></i> <?= esc($ml['gpu']['gpus'][0]['name'] ?? 'NVIDIA GPU') ?></span>
                                    <span class="badge bg-success">GPU Accelerated</span>
                                </div>
                                <div class="small text-muted mb-2">
                                    Driver: <?= esc($ml['gpu']['gpus'][0]['driver_version'] ?? 'N/A') ?> | Temp: <?= esc($ml['gpu']['gpus'][0]['temperature_c'] ?? 0) ?>°C
                                </div>
                                <div class="d-flex justify-content-between small fw-semibold mb-1">
                                    <span>VRAM Memory</span>
                                    <span><?= number_format($ml['gpu']['gpus'][0]['memory_used_mb'] ?? 0) ?> MB / <?= number_format($ml['gpu']['gpus'][0]['memory_total_mb'] ?? 0) ?> MB</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <?php
                                    $vTot = $ml['gpu']['gpus'][0]['memory_total_mb'] ?? 1;
                                    $vUsed = $ml['gpu']['gpus'][0]['memory_used_mb'] ?? 0;
                                    $vPct = min(100, round(($vUsed / $vTot) * 100));
                                    ?>
                                    <div class="progress-bar bg-success" style="width: <?= $vPct ?>%;"></div>
                                </div>
                            <?php else: ?>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-bold text-primary"><i class="fa-solid fa-microchip me-1"></i> CPU Vectorized Inference (AVX2 / OpenMP)</div>
                                        <div class="small text-muted">Running in optimized CPU container mode. No discrete NVIDIA GPU detected.</div>
                                    </div>
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-1">CPU Mode</span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- ML RAM Breakdown -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1 small fw-semibold">
                                <span>Container RAM Usage</span>
                                <span id="mlRamText"><?= number_format($ml['memory']['container_used_mb'] ?? 0) ?> MB (Python + Model Server)</span>
                            </div>
                            <div class="progress mb-2" style="height: 8px;">
                                <div class="progress-bar bg-warning" id="mlRamBar" style="width: <?= esc($ml['memory']['host_used_pct'] ?? 20) ?>%;"></div>
                            </div>
                            <div class="row g-2 text-center small text-muted">
                                <div class="col-6 stat-pill">
                                    <div>Python FastApi Worker</div>
                                    <strong class="text-dark" id="mlPythonRss"><?= number_format($ml['memory']['python_rss_mb'] ?? 0) ?> MB</strong>
                                </div>
                                <div class="col-6 stat-pill">
                                    <div>Llama C++ Server</div>
                                    <strong class="text-dark" id="mlLlamaRss"><?= number_format($ml['memory']['llama_rss_mb'] ?? 0) ?> MB</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Model in Memory & Job Queues -->
                    <div class="col-lg-6">
                        <h6 class="fw-bold mb-3 small text-muted text-uppercase">Active Model &amp; Processing Queue</h6>

                        <div class="p-3 border rounded mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold text-dark"><i class="fa-solid fa-cube me-1 text-primary"></i> Active GGUF Model</span>
                                <span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-0" id="mlModelEngine">
                                    <?= esc($ml['inference']['engine'] ?? 'local_gguf') ?>
                                </span>
                            </div>
                            <div class="fw-semibold text-primary mb-1 text-break" id="mlModelName">
                                <?= esc($ml['inference']['active_model'] ?? 'None') ?>
                            </div>
                            <div class="row g-2 text-muted small mt-2">
                                <div class="col-4">Size on Disk: <strong class="text-dark" id="mlModelSize"><?= number_format($ml['inference']['model_size_mb'] ?? 0) ?> MB</strong></div>
                                <div class="col-4">Context: <strong class="text-dark" id="mlCtxSize"><?= number_format($ml['inference']['ctx_size'] ?? 16384) ?> tok</strong></div>
                                <div class="col-4 text-end">Batch: <strong class="text-dark" id="mlBatchSize"><?= esc($ml['inference']['batch_size'] ?? 512) ?></strong></div>
                            </div>
                        </div>

                        <!-- Job Queues Stats -->
                        <div class="row g-2 text-center small">
                            <div class="col-4">
                                <div class="p-2 border rounded bg-body-tertiary">
                                    <div class="text-muted" style="font-size: 0.72rem;">Active Processing</div>
                                    <div class="fw-bold fs-5 text-warning" id="mlQueueActive"><?= esc($ml['queue']['active_jobs'] ?? 0) ?></div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 border rounded bg-body-tertiary">
                                    <div class="text-muted" style="font-size: 0.72rem;">Queued in DB</div>
                                    <div class="fw-bold fs-5 text-info" id="mlQueueQueued"><?= esc($ml['queue']['queued_jobs'] ?? 0) ?></div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 border rounded bg-body-tertiary">
                                    <div class="text-muted" style="font-size: 0.72rem;">Completed Today</div>
                                    <div class="fw-bold fs-5 text-success" id="mlQueueCompleted"><?= esc($ml['queue']['completed_today'] ?? 0) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Route guard: strictly run only when on /admin/telemetry
    if (!window.location.pathname.includes('/admin/telemetry')) {
        return;
    }

    let pollTimer = null;
    let pollInterval = 5000; // Optimal 5s polling rate (low container overhead)

    // Rolling history buffers for real-time client-side sparklines (max 20 points)
    const MAX_HISTORY = 20;
    const historyWebCpu = [];
    const historyMysqlQps = [];
    const historyMlLatency = [];

    const intervalSelect = document.getElementById('pollIntervalSelect');
    const manualRefreshBtn = document.getElementById('manualRefreshBtn');
    const refreshIcon = document.getElementById('refreshIcon');
    const lastUpdatedText = document.getElementById('lastUpdatedText');
    const livePulse = document.getElementById('livePulse');
    const liveStatusText = document.getElementById('liveStatusText');

    function escapeHtml(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    // Lightweight client-side SVG sparkline renderer (zero external dependencies)
    function drawSparkline(svgId, dataPoints, minVal = 0, maxVal = null, strokeColor = '#438EB9', fillColor = 'rgba(67, 142, 185, 0.15)') {
        const svg = document.getElementById(svgId);
        if (!svg) return;
        const width = svg.clientWidth || 160;
        const height = 28;
        svg.setAttribute('viewBox', `0 0 ${width} ${height}`);

        if (!dataPoints || dataPoints.length === 0) {
            svg.innerHTML = '';
            return;
        }

        if (dataPoints.length === 1) {
            svg.innerHTML = `<circle cx="${width - 5}" cy="${height / 2}" r="3" fill="${strokeColor}" />`;
            return;
        }

        let min = minVal !== null ? minVal : Math.min(...dataPoints);
        let max = maxVal !== null ? maxVal : Math.max(...dataPoints);
        if (max <= min) max = min + 1;

        const points = dataPoints.map((val, idx) => {
            const x = (idx / (dataPoints.length - 1)) * (width - 8) + 4;
            const normalized = Math.max(0, Math.min(1, (val - min) / (max - min)));
            const y = (height - 6) - (normalized * (height - 10)) + 3;
            return `${x.toFixed(1)},${y.toFixed(1)}`;
        });

        const polyline = points.join(' ');
        const firstX = points[0].split(',')[0];
        const lastX = points[points.length - 1].split(',')[0];
        const polygon = `${firstX},${height} ` + polyline + ` ${lastX},${height}`;
        const lastPt = points[points.length - 1].split(',');

        svg.innerHTML = `
            <polygon points="${polygon}" fill="${fillColor}" />
            <polyline points="${polyline}" fill="none" stroke="${strokeColor}" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
            <circle cx="${lastPt[0]}" cy="${lastPt[1]}" r="3" fill="${strokeColor}" />
        `;
    }

    function fetchTelemetry() {
        if (refreshIcon) refreshIcon.classList.add('fa-spin');

        fetch('<?= base_url('admin/telemetry/live') ?>')
            .then(r => r.json())
            .then(data => {
                updateUI(data);
                if (lastUpdatedText) {
                    const now = new Date();
                    lastUpdatedText.textContent = now.toLocaleTimeString();
                }
            })
            .catch(err => {
                console.warn('Telemetry fetch error:', err);
                if (livePulse) livePulse.style.backgroundColor = '#dc3545';
                if (liveStatusText) liveStatusText.textContent = 'Connection Issue';
            })
            .finally(() => {
                if (refreshIcon) refreshIcon.classList.remove('fa-spin');
            });
    }

    function updateUI(data) {
        if (!data) return;

        const web = data.web || {};
        const mysql = data.mysql || {};
        const ml = data.ml || {};

        if (livePulse) livePulse.style.backgroundColor = '#28a745';
        if (liveStatusText) liveStatusText.textContent = 'Live Monitoring';

        // 1. WebApp Container
        const webCpuPct = web.cpu?.load_pct || 0;
        const webRamUsed = web.memory?.container_used_mb || 0;
        const webRamPct = web.memory?.container_used_pct || 0;

        document.getElementById('kpiWebCpu').textContent = webCpuPct + '%';
        document.getElementById('kpiWebRam').textContent = Math.round(webRamUsed).toLocaleString() + ' MB';
        document.getElementById('kpiWebUptime').textContent = web.uptime_formatted || '0s';

        // OOM Risk warning badge
        const kpiWebOomBadge = document.getElementById('kpiWebOomBadge');
        if (kpiWebOomBadge) {
            if (web.memory?.container_oom_warning) {
                kpiWebOomBadge.classList.remove('d-none');
            } else {
                kpiWebOomBadge.classList.add('d-none');
            }
        }

        // WebApp CPU Sparkline
        historyWebCpu.push(webCpuPct);
        if (historyWebCpu.length > MAX_HISTORY) historyWebCpu.shift();
        const sparkCpuColor = webCpuPct > 85 ? '#dc3545' : (webCpuPct > 65 ? '#ffc107' : '#0d6efd');
        const sparkCpuFill = webCpuPct > 85 ? 'rgba(220, 53, 69, 0.15)' : 'rgba(13, 110, 253, 0.12)';
        drawSparkline('sparkWebCpu', historyWebCpu, 0, 100, sparkCpuColor, sparkCpuFill);
        const sparkWebCpuVal = document.getElementById('sparkWebCpuVal');
        if (sparkWebCpuVal) sparkWebCpuVal.textContent = webCpuPct + '%';

        document.getElementById('webCpuPctText').textContent = webCpuPct + '%';
        const webCpuBar = document.getElementById('webCpuBar');
        if (webCpuBar) {
            webCpuBar.style.width = webCpuPct + '%';
            webCpuBar.className = 'progress-bar ' + (webCpuPct > 85 ? 'bg-danger' : (webCpuPct > 65 ? 'bg-warning' : 'bg-primary'));
        }

        document.getElementById('webLoad1m').textContent = web.cpu?.load_1m || 0;
        document.getElementById('webLoad5m').textContent = web.cpu?.load_5m || 0;
        document.getElementById('webLoad15m').textContent = web.cpu?.load_15m || 0;

        document.getElementById('webRamText').textContent = Math.round(webRamUsed).toLocaleString() + ' MB / ' + (web.memory?.container_limit_mb ? Math.round(web.memory.container_limit_mb).toLocaleString() + ' MB' : Math.round(web.memory?.host_total_mb || 0).toLocaleString() + ' MB');
        const webRamBar = document.getElementById('webRamBar');
        if (webRamBar) webRamBar.style.width = webRamPct + '%';

        document.getElementById('webPhpAlloc').textContent = (web.memory?.php_allocated_mb || 0) + ' MB';
        document.getElementById('webPhpPeak').textContent = (web.memory?.php_peak_mb || 0) + ' MB';
        document.getElementById('webSessions').textContent = web.runtime?.sessions || 0;

        // 2. MySQL Container
        const mysqlConn = mysql.connections?.connected || 0;
        const mysqlConnPct = mysql.connections?.used_pct || 0;
        const mysqlQps = mysql.throughput?.qps || 0;
        document.getElementById('kpiMysqlConn').textContent = mysqlConn;
        document.getElementById('kpiMysqlQps').textContent = mysqlQps;
        document.getElementById('kpiMysqlDbSize').textContent = (mysql.database_size_mb || 0).toFixed(1) + ' MB';

        // MySQL QPS Sparkline
        historyMysqlQps.push(mysqlQps);
        if (historyMysqlQps.length > MAX_HISTORY) historyMysqlQps.shift();
        drawSparkline('sparkMysqlQps', historyMysqlQps, 0, null, '#0dcaf0', 'rgba(13, 202, 240, 0.12)');
        const sparkMysqlQpsVal = document.getElementById('sparkMysqlQpsVal');
        if (sparkMysqlQpsVal) sparkMysqlQpsVal.textContent = mysqlQps + ' QPS';

        // Slow Queries & Aborted Connects
        const slowQ = mysql.throughput?.slow_queries || 0;
        const abortedConn = mysql.connections?.aborted || 0;
        const kpiMysqlSlowQ = document.getElementById('kpiMysqlSlowQ');
        if (kpiMysqlSlowQ) {
            kpiMysqlSlowQ.textContent = slowQ;
            kpiMysqlSlowQ.className = slowQ > 0 ? 'text-danger fw-bold' : 'text-dark';
        }
        const mysqlSlowQ = document.getElementById('mysqlSlowQ');
        if (mysqlSlowQ) {
            mysqlSlowQ.textContent = slowQ;
            mysqlSlowQ.className = 'text-dark ' + (slowQ > 0 ? 'text-danger' : '');
        }
        const mysqlAbortedConnects = document.getElementById('mysqlAbortedConnects');
        if (mysqlAbortedConnects) mysqlAbortedConnects.textContent = abortedConn;

        document.getElementById('mysqlConnText').textContent = mysqlConn + ' / ' + (mysql.connections?.max || 151) + ' (' + mysqlConnPct + '%)';
        const mysqlConnBar = document.getElementById('mysqlConnBar');
        if (mysqlConnBar) mysqlConnBar.style.width = mysqlConnPct + '%';

        document.getElementById('mysqlThreadsRunning').textContent = mysql.connections?.running || 0;
        document.getElementById('mysqlMaxUsed').textContent = mysql.connections?.max_used || 0;
        document.getElementById('mysqlLatency').textContent = (mysql.latency_ms || 0) + ' ms';

        document.getElementById('mysqlTotalQ').textContent = (mysql.throughput?.questions || 0).toLocaleString();
        document.getElementById('mysqlQps').textContent = mysqlQps;
        document.getElementById('mysqlRecv').textContent = (mysql.throughput?.bytes_received_mb || 0).toFixed(1) + ' MB';

        const bpDataMb = mysql.buffer_pool?.data_mb || 0;
        const bpSizeMb = mysql.buffer_pool?.size_mb || 0;
        const bpPct = mysql.buffer_pool?.used_pct || 0;
        document.getElementById('mysqlBpText').textContent = bpDataMb.toFixed(1) + ' MB / ' + bpSizeMb.toFixed(1) + ' MB (' + bpPct + '%)';
        const mysqlBpBar = document.getElementById('mysqlBpBar');
        if (mysqlBpBar) mysqlBpBar.style.width = bpPct + '%';

        document.getElementById('mysqlUptime').textContent = mysql.uptime_formatted || '0s';

        // 3. ML Engine Container
        const mlOnline = ml.status === 'online';
        const mlLatency = ml.latency_ms || 0;
        const kpiMlStatus = document.getElementById('kpiMlStatus');
        if (kpiMlStatus) {
            kpiMlStatus.textContent = mlOnline ? 'online' : 'offline';
            kpiMlStatus.className = 'badge metric-badge ' + (mlOnline ? 'bg-success' : 'bg-danger');
        }

        const mlLlamaStatus = document.getElementById('mlLlamaStatus');
        if (mlLlamaStatus) {
            const isLlamaRunning = !!(ml.inference?.llama_running);
            mlLlamaStatus.textContent = 'Llama Server: ' + (isLlamaRunning ? ('Running (PID ' + (ml.inference?.llama_pid || 'Active') + ')') : 'Standby / Stopped');
            mlLlamaStatus.className = 'badge rounded-pill px-3 py-1 ' + (isLlamaRunning ? 'bg-success-subtle text-success-emphasis' : 'bg-warning-subtle text-warning-emphasis');
        }

        document.getElementById('kpiMlMode').textContent = ml.gpu?.has_gpu ? 'GPU' : 'CPU';
        document.getElementById('kpiMlLatency').textContent = '(' + mlLatency + ' ms API latency)';
        document.getElementById('mlLatencyBadge').textContent = 'API: ' + mlLatency + ' ms';

        // ML Latency Sparkline
        historyMlLatency.push(mlLatency);
        if (historyMlLatency.length > MAX_HISTORY) historyMlLatency.shift();
        drawSparkline('sparkMlLatency', historyMlLatency, 0, null, '#ffc107', 'rgba(255, 193, 7, 0.15)');
        const sparkMlLatencyVal = document.getElementById('sparkMlLatencyVal');
        if (sparkMlLatencyVal) sparkMlLatencyVal.textContent = mlLatency + ' ms';

        document.getElementById('kpiMlModel').textContent = ml.inference?.active_model || 'None';
        document.getElementById('kpiMlQueue').textContent = (ml.queue?.active_jobs || 0) + ' active';

        // Dynamic GPU status update
        const gpuCardWrapper = document.getElementById('gpuCardWrapper');
        if (gpuCardWrapper && ml.gpu) {
            if (ml.gpu.has_gpu && ml.gpu.gpus && ml.gpu.gpus.length > 0) {
                const g = ml.gpu.gpus[0];
                const vTot = g.memory_total_mb || 1;
                const vUsed = g.memory_used_mb || 0;
                const vPct = Math.min(100, Math.round((vUsed / vTot) * 100));
                gpuCardWrapper.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold text-success"><i class="fa-solid fa-bolt me-1"></i> ${escapeHtml(g.name || 'NVIDIA GPU')}</span>
                        <span class="badge bg-success">GPU Accelerated</span>
                    </div>
                    <div class="small text-muted mb-2">
                        Driver: ${escapeHtml(g.driver_version || 'N/A')} | Temp: ${g.temperature_c || 0}°C
                    </div>
                    <div class="d-flex justify-content-between small fw-semibold mb-1">
                        <span>VRAM Memory</span>
                        <span>${Math.round(vUsed).toLocaleString()} MB / ${Math.round(vTot).toLocaleString()} MB</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" style="width: ${vPct}%;"></div>
                    </div>
                `;
            } else {
                gpuCardWrapper.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold text-primary"><i class="fa-solid fa-microchip me-1"></i> CPU Vectorized Inference (AVX2 / OpenMP)</div>
                            <div class="small text-muted">Running in optimized CPU container mode. No discrete NVIDIA GPU detected.</div>
                        </div>
                        <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-1">CPU Mode</span>
                    </div>
                `;
            }
        }

        // ML RAM & Process details
        const mlRamUsed = ml.memory?.container_used_mb || (ml.memory?.python_rss_mb || 0) + (ml.memory?.llama_rss_mb || 0);
        const mlRamText = document.getElementById('mlRamText');
        if (mlRamText) {
            mlRamText.textContent = Math.round(mlRamUsed).toLocaleString() + ' MB (Python + Model Server)';
        }
        const mlRamBar = document.getElementById('mlRamBar');
        if (mlRamBar) {
            mlRamBar.style.width = (ml.memory?.host_used_pct || 20) + '%';
        }

        document.getElementById('mlModelName').textContent = ml.inference?.active_model || 'None';
        document.getElementById('mlModelSize').textContent = Math.round(ml.inference?.model_size_mb || 0) + ' MB';
        document.getElementById('mlCtxSize').textContent = (ml.inference?.ctx_size || 16384).toLocaleString() + ' tok';
        document.getElementById('mlBatchSize').textContent = ml.inference?.batch_size || 512;

        document.getElementById('mlPythonRss').textContent = (ml.memory?.python_rss_mb || 0) + ' MB';
        document.getElementById('mlLlamaRss').textContent = (ml.memory?.llama_rss_mb || 0) + ' MB';

        document.getElementById('mlQueueActive').textContent = ml.queue?.active_jobs || 0;
        document.getElementById('mlQueueQueued').textContent = ml.queue?.queued_jobs || 0;
        document.getElementById('mlQueueCompleted').textContent = ml.queue?.completed_today || 0;
    }

    function resetTimer() {
        if (pollTimer) clearInterval(pollTimer);
        if (pollInterval > 0) {
            pollTimer = setInterval(fetchTelemetry, pollInterval);
        }
    }

    intervalSelect?.addEventListener('change', (e) => {
        pollInterval = parseInt(e.target.value, 10);
        resetTimer();
    });

    manualRefreshBtn?.addEventListener('click', () => {
        fetchTelemetry();
    });

    // Pause polling when browser tab is inactive / backgrounded to save bandwidth & container CPU
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            if (pollTimer) {
                clearInterval(pollTimer);
                pollTimer = null;
            }
        } else {
            resetTimer();
            fetchTelemetry();
        }
    });

    // Initial render of first sparkline point from preloaded PHP data
    const initialWebCpu = <?= json_encode($web['cpu']['load_pct'] ?? 0) ?>;
    const initialMysqlQps = <?= json_encode($mysql['throughput']['qps'] ?? 0) ?>;
    const initialMlLat = <?= json_encode($ml['latency_ms'] ?? 0) ?>;
    historyWebCpu.push(initialWebCpu);
    historyMysqlQps.push(initialMysqlQps);
    historyMlLatency.push(initialMlLat);
    drawSparkline('sparkWebCpu', historyWebCpu, 0, 100, '#0d6efd', 'rgba(13, 110, 253, 0.12)');
    drawSparkline('sparkMysqlQps', historyMysqlQps, 0, null, '#0dcaf0', 'rgba(13, 202, 240, 0.12)');
    drawSparkline('sparkMlLatency', historyMlLatency, 0, null, '#ffc107', 'rgba(255, 193, 7, 0.15)');

    // Start polling on load
    resetTimer();
});
</script>
<?= $this->endSection() ?>
