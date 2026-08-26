<?php
$versionData = [];
if (file_exists(APPPATH . 'Config/version.json')) {
    $versionData = json_decode(file_get_contents(APPPATH . 'Config/version.json'), true);
}
$systemVersion = $versionData['version'] ?? '3.2.0';
$systemChangelog = $versionData['changelog'] ?? [];
$systemGithub = $versionData['github_url'] ?? 'https://github.com/niccher/Mpesa_Analyzer_App';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= $this->renderSection('title') ?? 'Mpesa Analyzer' ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= base_url('favicon.png') ?>">
    <link rel="apple-touch-icon" href="<?= base_url('favicon.png') ?>">

    <!-- Prevent Light Flash -->
    <script>
        const theme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        if (theme === 'dark') document.documentElement.setAttribute('data-bs-theme', 'dark');
    </script>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- FontAwesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    
    <style>
        :root {
            --primary: #5D5FEF;
            --bg-color: #B1B8ED;
            --sidebar-width: 260px;
            --card-bg: rgba(255, 255, 255, 0.9);
            --card-border: rgba(255, 255, 255, 0.4);
            --text-main: #2d3436;
            --text-muted: #636e72;
            --bs-card-border-radius: 4px;
            --bs-border-radius-xl: 4px;
            --bs-border-radius-lg: 4px;
            --bs-border-radius-sm: 3px;
            --bs-border-radius: 4px;
        }

        [data-bs-theme="dark"] {
            --bg-color: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.8);
            --card-border: rgba(255, 255, 255, 0.1);
            --text-main: #f1f5f9;
            --text-muted: #94a3b8;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            overflow: hidden; 
            transition: all 0.3s ease;
        }

        [data-bs-theme="dark"] body {
            background-color: var(--bg-color);
        }

        /* Card Theme Overrides */
        .glass-card, .trx-card, .history-card, .info-card, .card {
            background-color: var(--card-bg) !important;
            border-color: var(--card-border) !important;
            color: var(--text-main);
        }

        [data-bs-theme="dark"] .text-dark { color: #f1f5f9 !important; }
        [data-bs-theme="dark"] .text-secondary { color: #94a3b8 !important; }
        [data-bs-theme="dark"] .table { color: #f1f5f9; border-color: #334155; }
        [data-bs-theme="dark"] .table thead th { background-color: #1e293b !important; color: #f1f5f9 !important; border-color: #334155 !important; }
        [data-bs-theme="dark"] .table-light { --bs-table-bg: #1e293b; --bs-table-color: #f1f5f9; }
        [data-bs-theme="dark"] .form-control, [data-bs-theme="dark"] .form-select { background-color: #0f172a; border-color: #334155; color: #f1f5f9; }
        [data-bs-theme="dark"] .form-control:focus, [data-bs-theme="dark"] .form-select:focus { background-color: #1e293b; color: #fff; }
        [data-bs-theme="dark"] .dropdown-menu { background-color: #1e293b; border-color: #334155; box-shadow: 0 10px 30px rgba(0,0,0,0.5) !important; }
        [data-bs-theme="dark"] .dropdown-item { color: #f1f5f9; }
        [data-bs-theme="dark"] .dropdown-item:hover { background-color: #334155; color: #fff; }

        [data-bs-theme="dark"] #sidebar, 
        [data-bs-theme="dark"] .navbar,
        [data-bs-theme="dark"] .dashboard-footer {
            background-color: #1e293b !important;
            border-color: #334155 !important;
        }

        /* Sidebar Styling */
        #sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1045;
            transition: all 0.3s;
            box-shadow: 4px 0 15px rgba(0,0,0,0.05);
            background-color: #fff;
        }

        #sidebar .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid #f0f0f0;
        }

        #sidebar .sidebar-header h4 {
            color: var(--primary);
            font-weight: 700;
            margin: 0;
        }

        #sidebar .nav-link {
            color: #666;
            font-weight: 500;
            padding: 12px 20px;
            border-radius: 4px;
            margin: 5px 15px;
            transition: all 0.2s;
        }

        #sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        #sidebar .nav-link:hover, #sidebar .nav-link.active {
            background-color: var(--primary);
            color: #fff;
            box-shadow: 0 4px 10px rgba(93, 95, 239, 0.3);
        }

        .sidebar-footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            padding: 15px;
            border-top: 1px solid #f0f0f0;
        }

        .sidebar-footer .nav-link {
            color: #dc3545; /* Bootstrap Danger */
        }
        
        .sidebar-footer .nav-link:hover {
            background-color: #dc3545;
            color: white;
            box-shadow: 0 4px 10px rgba(220, 53, 69, 0.3);
        }

        /* Page Content Styling */
        #page-content-wrapper {
            margin-left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
            height: 100vh;
            display: flex;
            flex-direction: column;
            transition: all 0.3s;
        }

        .navbar {
            background: rgba(255, 255, 255, 0.8) !important;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255,255,255,0.2);
            padding-left: 30px;
            padding-right: 30px;
            flex-shrink: 0;
        }

        @media (max-width: 991.98px) {
            #sidebar {
                margin-left: calc(var(--sidebar-width) * -1);
            }
            #sidebar.toggled {
                margin-left: 0;
            }
            #page-content-wrapper {
                margin-left: 0;
                width: 100%;
            }
        }

        .dashboard-footer {
            border-top: 1px solid rgba(0,0,0,0.05);
            background-color: rgba(255, 255, 255, 0.5);
        }

        /* Clean table design */
        .table {
            border: 1px solid #e0e0e0;
            margin-bottom: 0;
        }
        .table thead th {
            border-bottom: 2px solid #e0e0e0;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #636e72;
            background-color: #f8f9fa;
        }
        .table td, .table th {
            border-color: #e0e0e0;
            vertical-align: middle;
        }
        .card-body.p-3 .table {
            border: 1px solid #e0e0e0;
        }
    </style>
    <?= $this->renderSection('styles') ?>
</head>
<body>

    <div class="d-flex" id="wrapper">
        
        <!-- Sidebar -->
        <nav id="sidebar">
            <div class="sidebar-header d-flex align-items-center justify-content-between">
                <h4><i class="fa-solid fa-wallet"></i> Analyzer</h4>
                <button class="btn btn-sm btn-close d-lg-none" id="sidebarClose"></button>
            </div>
            
            <ul class="nav flex-column mt-3">
                <?php $currentURL = uri_string(); ?>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentURL == 'dashboard' || $currentURL == '') ? 'active' : '' ?>" href="<?= url_to('DashboardController::index') ?>">
                        <i class="fa-solid fa-house"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentURL == 'dashboard/graph') ? 'active' : '' ?>" href="<?= url_to('Graph::index') ?>">
                        <i class="fa-solid fa-chart-pie"></i> Analytics
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= strpos($currentURL, 'dashboard/transactions') !== false ? 'active' : '' ?>" href="<?= url_to('Transactions::index') ?>">
                        <i class="fa-solid fa-list-check"></i> Transaction Ledger
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= strpos($currentURL, 'dashboard/reports') !== false ? 'active' : '' ?>" href="<?= base_url('dashboard/reports') ?>">
                        <i class="fa-solid fa-file-invoice-dollar"></i> Reports & Insights
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= strpos($currentURL, 'dashboard/budget') !== false ? 'active' : '' ?>" href="<?= base_url('dashboard/budget') ?>">
                        <i class="fa-solid fa-bullseye"></i> Budget Tracker
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (strpos($currentURL, 'dashboard/settings') !== false || strpos($currentURL, 'dashboard/devices') !== false || $currentURL == 'dashboard/info') ? 'active' : '' ?>" href="<?= base_url('dashboard/settings/profile') ?>">
                        <i class="fa-solid fa-sliders"></i> Control Center
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= strpos($currentURL, 'dashboard/history') !== false ? 'active' : '' ?>" href="<?= url_to('HistoryController::index') ?>">
                        <i class="fa-solid fa-clock-rotate-left"></i> History
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= strpos($currentURL, 'dashboard/blocklist') !== false ? 'active' : '' ?>" href="<?= base_url('dashboard/blocklist') ?>">
                        <i class="fa-solid fa-ban"></i> Blocklist
                    </a>
                </li>
            </ul>

            <div class="sidebar-footer">
                <a class="nav-link" href="<?= url_to('logout') ?>">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                </a>
            </div>
        </nav>

        <!-- Page Content -->
        <div id="page-content-wrapper">
            
            <?php $baseUrl = base_url(); ?>

            <!-- Top Navigation -->
            <nav class="navbar navbar-expand-lg navbar-light py-3">
                <div class="container-fluid px-0">
                    <button class="btn btn-outline-primary d-lg-none me-2" id="sidebarToggle">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    
                    <h5 class="mb-0 fw-bold text-dark d-none d-sm-block me-3">Dashboard</h5>

                    <!-- Rescan Buttons -->
                    <div class="btn-group btn-group-sm me-3" role="group">
                        <button class="btn btn-outline-primary fw-semibold d-flex align-items-center gap-1"
                                id="rescanBtn" title="Process only new/unprocessed SMS">
                            <i class="fa-solid fa-arrows-rotate"></i> Rescan
                        </button>
                        <button class="btn btn-outline-secondary fw-semibold d-flex align-items-center gap-1"
                                id="rescanAllBtn" title="Reprocess ALL SMS from scratch (clears existing analysis)">
                            <i class="fa-solid fa-rotate"></i> Full
                        </button>
                    </div>

                    <!-- Persistent Scan Status Badge (clickable) -->
                    <button id="scanStatusBadge"
                            class="btn btn-sm d-flex align-items-center gap-2 fw-semibold rounded-pill px-3 me-3 d-none"
                            title="Click for details" data-bs-toggle="modal" data-bs-target="#scanProgressModal">
                        <span id="scanStatusIcon" class="spinner-border spinner-border-sm text-warning" role="status"></span>
                        <span id="scanStatusText">Scanning...</span>
                    </button>

                    <div class="ms-auto d-flex align-items-center">
                        <div class="dropdown">
                            <a class="nav-link dropdown-toggle text-dark fw-semibold d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <?php $username = auth()->user()->username ?? 'User'; ?>
                                <div class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 32px; height: 32px; background-color: var(--primary);">
                                    <?= strtoupper(substr($username, 0, 1)) ?>
                                </div>
                                <?= $username ?>
                            </a>

                            <script>
                            let pollInterval = null;
                            const scanBadge = document.getElementById('scanStatusBadge');
                            const scanIcon = document.getElementById('scanStatusIcon');
                            const scanText = document.getElementById('scanStatusText');

                            function startPolling() {
                                stopPolling();
                                scanBadge.classList.remove('d-none');
                                pollInterval = setInterval(pollProgress, 2000);
                                pollProgress();
                            }

                            function stopPolling() {
                                if (pollInterval) {
                                    clearInterval(pollInterval);
                                    pollInterval = null;
                                }
                            }

                            function setBadgeState(state, label) {
                                scanBadge.classList.remove('d-none');
                                if (state === 'scanning') {
                                    scanIcon.className = 'spinner-border spinner-border-sm text-warning';
                                    scanText.textContent = label || 'Scanning...';
                                    scanBadge.className = 'btn btn-sm d-flex align-items-center gap-2 fw-semibold rounded-pill px-3 me-3 btn-outline-warning';
                                } else if (state === 'idle') {
                                    scanIcon.className = 'fa-solid fa-circle text-secondary';
                                    scanText.textContent = label || 'No scan running';
                                    scanBadge.className = 'btn btn-sm d-flex align-items-center gap-2 fw-semibold rounded-pill px-3 me-3 btn-outline-secondary';
                                } else if (state === 'complete') {
                                    scanIcon.className = 'fa-solid fa-circle-check text-success';
                                    scanText.textContent = label || 'Scan complete';
                                    scanBadge.className = 'btn btn-sm d-flex align-items-center gap-2 fw-semibold rounded-pill px-3 me-3 btn-outline-success';
                                } else if (state === 'failed') {
                                    scanIcon.className = 'fa-solid fa-circle-exclamation text-danger';
                                    scanText.textContent = label || 'Scan failed';
                                    scanBadge.className = 'btn btn-sm d-flex align-items-center gap-2 fw-semibold rounded-pill px-3 me-3 btn-outline-danger';
                                }
                            }

                             function populateModal(data) {
                                 const total = data.total || 0;
                                 const processed = data.processed || 0;
                                 const errors = data.errors || 0;
                                 const pct = total > 0 ? Math.min(100, Math.round((processed / total) * 100)) : 0;

                                 const bar = document.getElementById('modalProgressBar');
                                 bar.style.width = pct + '%';
                                 document.getElementById('modalProgressCount').textContent = processed + ' / ' + total;

                                 // Different colors for progressbar at different stages
                                 bar.classList.remove('bg-danger', 'bg-warning', 'bg-info', 'bg-success');
                                 if (pct < 30) {
                                     bar.classList.add('bg-danger');
                                 } else if (pct < 70) {
                                     bar.classList.add('bg-warning');
                                 } else if (pct < 100) {
                                     bar.classList.add('bg-info');
                                 } else {
                                     bar.classList.add('bg-success');
                                 }

                                 // SMS Stats
                                 document.getElementById('smsTotalCount').textContent = total;
                                 document.getElementById('smsFinanceCount').textContent = data.finance_sms || 0;
                                 document.getElementById('smsBadCount').textContent = data.skipped || 0;
                                 document.getElementById('smsFinanceProgress').textContent = data.completed + ' of ' + (data.finance_sms || 0);

                                 // Sender Stats
                                 document.getElementById('sendersTotalCount').textContent = data.total_senders || 0;
                                 document.getElementById('sendersFinanceCount').textContent = data.finance_senders || 0;
                                 document.getElementById('sendersBadCount').textContent = data.bad_senders || 0;
                                 document.getElementById('sendersProgress').textContent = data.processed_senders + ' of ' + (data.total_senders || 0);

                                 // Errors Count
                                 document.getElementById('modalErrors').innerHTML = '<i class="fa-solid fa-circle-xmark text-danger me-1 small"></i>' + errors;

                                 // Speed & ETA calculations
                                 let speedText = '—';
                                 let etaText = '—';
                                 let startedText = '—';
                                 let engineText = '—';

                                 if (data.job) {
                                      const j = data.job;
                                      const isTerminal = ['completed', 'done', 'failed', 'error', 'cancelled'].includes(j.status);
                                      if (j.started_at) {
                                          startedText = j.started_at;
                                          const dateStr = j.started_at.trim().replace(' ', 'T');
                                          const startMs = new Date(dateStr.indexOf('Z') === -1 && dateStr.indexOf('+') === -1 ? dateStr + 'Z' : dateStr).getTime();
                                          
                                          // Use completed_at if terminal and available, otherwise current local time in UTC
                                          let endMs = new Date().getTime();
                                          if (isTerminal && j.completed_at) {
                                              const compDateStr = j.completed_at.trim().replace(' ', 'T');
                                              endMs = new Date(compDateStr.indexOf('Z') === -1 && compDateStr.indexOf('+') === -1 ? compDateStr + 'Z' : compDateStr).getTime();
                                          }
                                          
                                          const elapsedSec = Math.max(1, Math.round((endMs - startMs) / 1000));

                                          const financeSms = data.finance_sms || 0;
                                          const completed = data.completed || 0;

                                          if (processed > 0) {
                                              const overallSpeed = (processed / elapsedSec).toFixed(1);
                                              
                                              // Compute financial SMS processing speed and base ETA on remaining financial SMS
                                              if (completed > 0) {
                                                  const extractionSpeed = completed / elapsedSec; // financial SMS per second
                                                  speedText = overallSpeed + ' SMS/s <br><small class="text-muted" style="font-size:0.7rem;">(' + (extractionSpeed * 60).toFixed(1) + ' finance/min)</small>';

                                                  if (isTerminal) {
                                                      etaText = (j.status === 'completed' || j.status === 'done') ? 'Done' : '—';
                                                  } else if (financeSms > completed) {
                                                      const remainingFinance = financeSms - completed;
                                                      const etaSec = Math.round(remainingFinance / extractionSpeed);
                                                      if (etaSec < 60) {
                                                          etaText = etaSec + 's';
                                                      } else {
                                                          const mins = Math.floor(etaSec / 60);
                                                          const secs = etaSec % 60;
                                                          etaText = mins + 'm ' + secs + 's';
                                                      }
                                                  } else {
                                                      etaText = 'Done';
                                                  }
                                              } else {
                                                  speedText = overallSpeed + ' SMS/s';
                                                  if (isTerminal) {
                                                      etaText = (j.status === 'completed' || j.status === 'done') ? 'Done' : '—';
                                                  } else if (total > processed) {
                                                      const remaining = total - processed;
                                                      const etaSec = Math.round(remaining / (processed / elapsedSec));
                                                      if (etaSec < 60) {
                                                          etaText = etaSec + 's';
                                                      } else {
                                                          const mins = Math.floor(etaSec / 60);
                                                          const secs = etaSec % 60;
                                                          etaText = mins + 'm ' + secs + 's';
                                                      }
                                                  } else {
                                                      etaText = 'Done';
                                                  }
                                              }
                                          }
                                      }

                                     // Parse metadata for Engine details and Current Senders
                                     if (j.metadata) {
                                         try {
                                             const meta = typeof j.metadata === 'string' ? JSON.parse(j.metadata) : j.metadata;
                                             if (meta.llm_engine) {
                                                 engineText = meta.llm_engine;
                                                 if (meta.model) {
                                                     engineText += ' (' + meta.model + ')';
                                                 }
                                                 if (meta.model_provider) {
                                                     engineText = meta.model_provider + ' / ' + engineText;
                                                 }
                                             }
                                             if (meta.current_senders && meta.current_senders.length > 0) {
                                                 document.getElementById('modalCurrentSenders').textContent = meta.current_senders.join(', ');
                                                 document.getElementById('modalCurrentSendersContainer').classList.remove('d-none');
                                             } else {
                                                 document.getElementById('modalCurrentSendersContainer').classList.add('d-none');
                                             }
                                         } catch (e) {
                                             document.getElementById('modalCurrentSendersContainer').classList.add('d-none');
                                         }
                                     } else {
                                         document.getElementById('modalCurrentSendersContainer').classList.add('d-none');
                                     }

                                     let st = 'Status: <strong>' + j.status + '</strong>';
                                     if (processed) st += ' | ' + processed + ' processed';
                                     if (errors > 0) st += ' | ' + errors + ' errors';
                                     if (j.duration_seconds) st += ' | ' + j.duration_seconds + 's elapsed';
                                     document.getElementById('modalStatusText').innerHTML = '<i class="fa-solid fa-circle-info me-1"></i>' + st;
                                 } else {
                                     document.getElementById('modalStatusText').innerHTML = '<i class="fa-solid fa-circle-info me-1"></i>Total SMS: ' + total + ', Completed: ' + processed + ', Errors: ' + errors;
                                     document.getElementById('modalCurrentSendersContainer').classList.add('d-none');
                                 }

                                 document.getElementById('modalStartedAt').textContent = startedText;
                                 document.getElementById('modalEngine').textContent = engineText;
                                 document.getElementById('modalSpeed').innerHTML = '<i class="fa-solid fa-gauge-high text-info me-1 small"></i>' + speedText;
                                 document.getElementById('modalETA').innerHTML = '<i class="fa-solid fa-hourglass-half text-primary me-1 small"></i>' + etaText;

                                 const stopBtn = document.getElementById('modalStopJobBtn');
                                 if (stopBtn && data.job && ['queued', 'processing', 'starting'].includes(data.job.status)) {
                                     stopBtn.classList.remove('d-none');
                                     stopBtn.setAttribute('data-job-id', data.job.id);
                                 } else if (stopBtn) {
                                     stopBtn.classList.add('d-none');
                                 }

                                 if (total > 0 && !data.running && (data.job && (data.job.status === 'completed' || data.job.status === 'done' || data.job.status === 'failed'))) {
                                    bar.classList.remove('progress-bar-animated');
                                } else if (total > 0 && processed >= total) {
                                    bar.classList.remove('progress-bar-animated');
                                }
                            }

                            function pollProgress() {
                                fetch('<?= $baseUrl ?>dashboard/rescan/progress')
                                    .then(r => r.json())
                                    .then(data => {
                                        const total = data.total || 0;
                                        const processed = data.processed || 0;
                                        const errors = data.errors || 0;

                                        const terminalStatuses = ['done', 'error', 'failed', 'cancelled', 'disabled', 'completed'];
                                        const isTerminal = data.job && terminalStatuses.includes(data.job.status);

                                        if (isTerminal) {
                                            stopPolling();
                                            if (data.job.status === 'done' || data.job.status === 'completed') {
                                                setBadgeState('complete', processed + ' processed');
                                                showAlert('Scan Complete', processed + ' messages processed.', 'success');
                                            } else {
                                                setBadgeState('failed', 'Scan stopped/failed (' + data.job.status + ')');
                                                showAlert('Scan Stopped', 'Job status: ' + data.job.status, 'warning');
                                            }
                                            populateModal(data);
                                        } else if (total > 0 && processed >= total) {
                                            stopPolling();
                                            setBadgeState('complete', processed + ' processed');
                                            showAlert('Scan Complete', processed + ' messages processed.', 'success');
                                            populateModal(data);
                                            setTimeout(() => window.location.reload(), 3000);
                                        } else if (data.running) {
                                            const pct = total > 0 ? Math.min(100, Math.round((processed / total) * 100)) : 0;
                                            setBadgeState('scanning', pct + '% - ' + processed + '/' + total);
                                            populateModal(data);
                                        } else {
                                            setBadgeState('idle', 'No scan running');
                                            document.getElementById('modalProgressLabel').textContent = 'Idle';
                                            populateModal(data);
                                        }
                                    })
                                    .catch(() => {});
                            }

                            // Check status on every page load
                            document.addEventListener('DOMContentLoaded', function() {
                                fetch('<?= $baseUrl ?>dashboard/rescan/progress')
                                    .then(r => r.json())
                                    .then(data => {
                                        const total = data.total || 0;
                                        const processed = data.processed || 0;
                                        const terminalStatuses = ['done', 'error', 'failed', 'cancelled', 'disabled', 'completed'];
                                        const isTerminal = data.job && terminalStatuses.includes(data.job.status);

                                        if (data.running && !isTerminal) {
                                            startPolling();
                                        } else if (data.job && (data.job.status === 'completed' || data.job.status === 'done')) {
                                            setBadgeState('complete', processed + ' processed');
                                        } else if (isTerminal) {
                                            setBadgeState('failed', 'Scan stopped/failed (' + data.job.status + ')');
                                        }
                                    })
                                    .catch(() => {});
                            });

                            document.getElementById('modalStopJobBtn')?.addEventListener('click', function(e) {
                                e.preventDefault();
                                const jobId = this.getAttribute('data-job-id');
                                if (!jobId) return;

                                Swal.fire({
                                    title: 'Stop ML Job?',
                                    text: 'Are you sure you want to stop/cancel Job #' + jobId + '?',
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonColor: '#d33',
                                    cancelButtonColor: '#3085d6',
                                    confirmButtonText: 'Yes, Stop It!',
                                    cancelButtonText: 'Cancel'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        this.disabled = true;
                                        const formData = new FormData();
                                        formData.append('job_id', jobId);

                                        fetch('<?= $baseUrl ?>dashboard/history/jobs/stop', {
                                            method: 'POST',
                                            body: formData,
                                            headers: {
                                                'X-Requested-With': 'XMLHttpRequest'
                                            }
                                        })
                                        .then(response => response.json())
                                        .then(data => {
                                            if (data.status === 'success') {
                                                Swal.fire({
                                                    title: 'Stopped!',
                                                    text: data.message,
                                                    icon: 'success',
                                                    timer: 2000,
                                                    showConfirmButton: false
                                                });
                                                setTimeout(() => window.location.reload(), 1500);
                                            } else {
                                                Swal.fire('Error', data.message, 'error');
                                                this.disabled = false;
                                            }
                                        })
                                        .catch(err => {
                                            Swal.fire('Error', 'Failed to send request: ' + err.message, 'error');
                                            this.disabled = false;
                                        });
                                    }
                                });
                            });

                            document.getElementById('rescanBtn')?.addEventListener('click', function(e) {
                                e.preventDefault();
                                const btn = this;
                                Swal.fire({
                                    title: 'Start Rescan?',
                                    text: 'This will analyze all new/unprocessed SMS messages using the LLM. It may take several minutes depending on the volume.',
                                    icon: 'question',
                                    showCancelButton: true,
                                    confirmButtonColor: 'var(--primary)',
                                    cancelButtonColor: '#6c757d',
                                    confirmButtonText: 'Yes, start!'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        btn.disabled = true;
                                        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Scanning...';
                                        fetch('<?= $baseUrl ?>dashboard/rescan', { method: 'POST' })
                                            .then(r => r.json())
                                            .then(data => {
                                                if (data.status === 'started') {
                                                    showAlert('Rescan Started', data.message || 'LLM analysis is running in the background.', 'info');
                                                    startPolling();
                                                } else {
                                                    showAlert('Notice', data.message || 'No unprocessed SMS found.', 'warning');
                                                    setBadgeState('idle', 'No unprocessed SMS');
                                                }
                                            })
                                            .catch(err => showAlert('Error', 'Failed to start rescan: ' + err.message, 'danger'))
                                            .finally(() => {
                                                btn.disabled = false;
                                                btn.innerHTML = '<i class="fa-solid fa-arrows-rotate"></i> Rescan';
                                            });
                                    }
                                });
                            });

                            document.getElementById('rescanAllBtn')?.addEventListener('click', function(e) {
                                e.preventDefault();
                                const btn = this;
                                Swal.fire({
                                    title: 'Reset & Reprocess All?',
                                    text: 'WARNING: This will DELETE all existing LLM analysis and re-analyze every single SMS from scratch. This cannot be undone and may take a long time.',
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonColor: '#dc3545',
                                    cancelButtonColor: '#6c757d',
                                    confirmButtonText: 'Yes, reset everything!'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        btn.disabled = true;
                                        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Resetting...';
                                        fetch('<?= $baseUrl ?>dashboard/rescan/all', { method: 'POST' })
                                            .then(r => r.json())
                                            .then(data => {
                                                showAlert(data.status === 'started' ? 'Reprocess Started' : 'Notice', data.message || 'Processing triggered.', data.status === 'started' ? 'info' : 'warning');
                                                if (data.status === 'started') startPolling();
                                            })
                                            .catch(err => showAlert('Error', 'Failed: ' + err.message, 'danger'))
                                            .finally(() => {
                                                btn.disabled = false;
                                                btn.innerHTML = '<i class="fa-solid fa-rotate"></i> Full';
                                            });
                                    }
                                });
                            });
                            </script>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                <li>
                                    <button class="dropdown-item d-flex align-items-center" id="themeToggleBtn">
                                        <i class="fa-solid fa-moon me-2" id="themeIcon"></i> <span id="themeText">Dark Mode</span>
                                    </button>
                                </li>
                                <li><a class="dropdown-item" href="<?= url_to('Info::index') ?>"><i class="fa-solid fa-user me-2"></i> Profile</a></li>
                                <li><a class="dropdown-item" href="<?= base_url('dashboard/settings') ?>"><i class="fa-solid fa-gear me-2"></i> Settings</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="<?= url_to('logout') ?>"><i class="fa-solid fa-right-from-bracket me-2"></i> Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Main Content Area (Scrollable) -->
            <main class="container-fluid px-4 pb-4 pt-0 flex-grow-1 overflow-auto d-flex flex-column">
                <div class="pt-4 mb-2 flex-shrink-0">
                    <?= $this->renderSection('page_header') ?>
                </div>
                <div class="flex-grow-1">
                    <?= $this->renderSection('content') ?>
                </div>
                
                <!-- Footer Section -->
                <footer class="dashboard-footer py-3 px-2 mt-4">
                    <div class="d-flex flex-md-row flex-column justify-content-between align-items-center opacity-75 small">
                        <div class="mb-2 mb-md-0">
                            &copy; <?= date('Y') ?> <span class="fw-bold text-primary">Mpesa Analyzer</span>. 
                            <span class="d-none d-sm-inline">All rights reserved.</span>
                        </div>
                        <div class="d-flex gap-3 align-items-center">
                            <a href="#" class="text-dark text-decoration-none" data-bs-toggle="modal" data-bs-target="#changelogModal"><i class="fa-solid fa-code-branch me-1"></i> v<?= esc($systemVersion) ?></a>
                            <a href="#" class="text-dark text-decoration-none" data-bs-toggle="modal" data-bs-target="#docsModal"><i class="fa-solid fa-book-open me-1"></i> Docs</a>
                            <a href="#" class="text-dark text-decoration-none" data-bs-toggle="popover" data-bs-trigger="focus" data-bs-placement="top" data-bs-html="true" data-bs-content="<div class='text-center p-2'><p class='mb-2 fw-semibold small'>Scan to Download APK</p><img src='https://api.qrserver.com/v1/create-qr-code/?size=120x120&amp;data=<?= urlencode(base_url('android-app')) ?>' class='img-fluid mb-2 rounded border' style='max-width: 120px;'><br><a href='<?= esc($systemGithub) ?>' target='_blank' class='btn btn-dark btn-sm rounded-pill py-1 px-3 mt-1' style='font-size: 11px;'><i class='fa-brands fa-github me-1'></i> GitHub Repo</a></div>" title="Get Android Client"><i class="fa-solid fa-download me-1"></i> App</a>
                        </div>
                    </div>
                </footer>
            </main>
            
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- Bootstrap 5 Bundle JS (Includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- SortableJS for Drag-and-Drop Widgets -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

    <!-- Sidebar Toggle Script -->
    <script>
        document.getElementById("sidebarToggle").addEventListener("click", function(e) {
            e.preventDefault();
            document.getElementById("sidebar").classList.toggle("toggled");
        });
        document.getElementById("sidebarClose").addEventListener("click", function(e) {
            e.preventDefault();
            document.getElementById("sidebar").classList.remove("toggled");
        });

        // Dark Mode Logic
        document.addEventListener('DOMContentLoaded', () => {
            const themeToggleBtn = document.getElementById('themeToggleBtn');
            const themeIcon = document.getElementById('themeIcon');
            const themeText = document.getElementById('themeText');
            
            function updateThemeUI(theme) {
                if (theme === 'dark') {
                    themeIcon.classList.remove('fa-moon');
                    themeIcon.classList.add('fa-sun');
                    themeText.textContent = 'Light Mode';
                } else {
                    themeIcon.classList.remove('fa-sun');
                    themeIcon.classList.add('fa-moon');
                    themeText.textContent = 'Dark Mode';
                }
            }

            // Init UI
            const currentTheme = document.documentElement.getAttribute('data-bs-theme') || 'light';
            updateThemeUI(currentTheme);

            themeToggleBtn.addEventListener('click', (e) => {
                e.preventDefault();
                const currentTheme = document.documentElement.getAttribute('data-bs-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                
                document.documentElement.setAttribute('data-bs-theme', newTheme);
                localStorage.setItem('theme', newTheme);
                updateThemeUI(newTheme);
            });
        });
    </script>

    <!-- Scan Progress Modal -->
    <div class="modal fade" id="scanProgressModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content glass-card border-0 shadow-lg">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">
                        <i class="fa-solid fa-microchip me-2 text-primary"></i>LLM Scan Progress
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span id="modalProgressLabel" class="fw-semibold">Progress</span>
                            <span id="modalProgressCount" class="fw-bold">0 / 0</span>
                        </div>
                        <div class="progress mb-2" style="height: 12px;">
                            <div id="modalProgressBar" class="progress-bar progress-bar-striped progress-bar-animated"
                                 role="progressbar" style="width: 0%"></div>
                        </div>
                        <div class="d-flex justify-content-between small text-muted">
                            <span><i class="fa-solid fa-clock me-1"></i>Started: <span id="modalStartedAt">—</span></span>
                            <span><i class="fa-solid fa-gears me-1"></i>Engine: <span id="modalEngine" class="badge bg-secondary">—</span></span>
                        </div>
                    </div>
                    
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <div class="border rounded p-3 bg-light-subtle h-100">
                                <div class="small fw-bold text-secondary mb-2 border-bottom pb-1">
                                    <i class="fa-solid fa-envelope me-1"></i>SMS STATS
                                </div>
                                <div class="small">
                                    <div class="d-flex justify-content-between mb-1"><span>All SMS (Raw):</span><strong id="smsTotalCount" class="text-dark">0</strong></div>
                                    <div class="d-flex justify-content-between mb-1 text-success"><span>Financial SMS:</span><strong id="smsFinanceCount">0</strong></div>
                                    <div class="d-flex justify-content-between mb-1 text-danger"><span>Bad / Non-Finance:</span><strong id="smsBadCount">0</strong></div>
                                    <div class="d-flex justify-content-between border-top pt-1 mt-1 fw-bold text-primary">
                                        <span>Finance SMS Progress:</span><span id="smsFinanceProgress">0 of 0</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-3 bg-light-subtle h-100">
                                <div class="small fw-bold text-secondary mb-2 border-bottom pb-1">
                                    <i class="fa-solid fa-users me-1"></i>SENDER STATS
                                </div>
                                <div class="small">
                                    <div class="d-flex justify-content-between mb-1"><span>All Senders:</span><strong id="sendersTotalCount" class="text-dark">0</strong></div>
                                    <div class="d-flex justify-content-between mb-1 text-success"><span>Financial Senders:</span><strong id="sendersFinanceCount">0</strong></div>
                                    <div class="d-flex justify-content-between mb-1 text-danger"><span>Bad / Unwanted:</span><strong id="sendersBadCount">0</strong></div>
                                    <div class="d-flex justify-content-between border-top pt-1 mt-1 fw-bold text-primary">
                                        <span>Senders Classified:</span><span id="sendersProgress">0 of 0</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2 text-center mb-3">
                        <div class="col-4">
                            <div class="border rounded p-2">
                                <div class="fw-bold fs-6 text-danger" id="modalErrors"><i class="fa-solid fa-circle-xmark text-danger me-1 small"></i>0</div>
                                <div class="text-muted small" style="font-size: 0.75rem;">Errors</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-2">
                                <div class="fw-bold fs-6 text-info" id="modalSpeed"><i class="fa-solid fa-gauge-high text-info me-1 small"></i>—</div>
                                <div class="text-muted small" style="font-size: 0.75rem;">Speed</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-2">
                                <div class="fw-bold fs-6 text-primary" id="modalETA"><i class="fa-solid fa-hourglass-half text-primary me-1 small"></i>—</div>
                                <div class="text-muted small" style="font-size: 0.75rem;">ETA</div>
                            </div>
                        </div>
                    </div>

                    <div id="modalCurrentSendersContainer" class="border rounded p-2 bg-light-subtle d-none mb-3 text-start">
                        <div class="fw-bold text-secondary mb-1" style="font-size: 0.75rem;">
                            <i class="fa-solid fa-spinner fa-spin me-1 text-primary"></i>Currently Processing Senders
                        </div>
                        <div id="modalCurrentSenders" class="text-secondary small" style="word-break: break-all;">—</div>
                    </div>

                    <div class="mt-3 small text-muted text-start" id="modalStatusText"><i class="fa-solid fa-circle-info me-1"></i>No scan running.</div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" id="modalStopJobBtn" class="btn btn-danger btn-sm rounded-pill px-4 d-none">
                        <i class="fa-solid fa-stop me-1"></i> Stop Job
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Global Toast Container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 2000;">
        <div id="liveToast" class="toast align-items-center border-0 shadow-lg glass-card" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-3">
                    <div id="toastIconContainer" class="rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i id="toastIcon" class="fa-solid"></i>
                    </div>
                    <div>
                        <strong id="toastTitle" class="d-block">Notification</strong>
                        <small id="toastMessage" class="text-secondary"></small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-dark me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <!-- Global JS Helpers -->
    <script>
        /**
         * Global Alert Helper (Replaces alert())
         * @param {string} title
         * @param {string} message
         * @param {string} type - 'success', 'danger', 'warning', 'info'
         */
        function showAlert(title, message, type = 'info') {
            const toastEl = document.getElementById('liveToast');
            const toast = new bootstrap.Toast(toastEl, { delay: 5000 });
            
            const titleEl = document.getElementById('toastTitle');
            const messageEl = document.getElementById('toastMessage');
            const iconEl = document.getElementById('toastIcon');
            const iconContainer = document.getElementById('toastIconContainer');
            
            titleEl.textContent = title;
            messageEl.textContent = message;
            
            // Type Styling
            iconContainer.className = 'rounded-circle d-flex align-items-center justify-content-center';
            iconEl.className = 'fa-solid';
            
            if (type === 'success') {
                iconContainer.classList.add('bg-success-subtle', 'text-success');
                iconEl.classList.add('fa-check-circle');
            } else if (type === 'danger') {
                iconContainer.classList.add('bg-danger-subtle', 'text-danger');
                iconEl.classList.add('fa-triangle-exclamation');
            } else if (type === 'warning') {
                iconContainer.classList.add('bg-warning-subtle', 'text-warning');
                iconEl.classList.add('fa-circle-exclamation');
            } else {
                iconContainer.classList.add('bg-primary-subtle', 'text-primary');
                iconEl.classList.add('fa-circle-info');
            }
            
            toast.show();
        }
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.map(function(el) {
            return new bootstrap.Popover(el);
        });
    });
    </script>

    <!-- Changelog Modal -->
    <div class="modal fade" id="changelogModal" tabindex="-1" aria-labelledby="changelogModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="changelogModalLabel" style="color: var(--primary);"><i class="fa-solid fa-clock-rotate-left me-2"></i> System Changelog</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 fw-semibold">Current Version: v<?= esc($systemVersion) ?></span>
                    </div>
                    <ul class="list-group list-group-flush small">
                        <?php foreach ($systemChangelog as $change): ?>
                            <li class="list-group-item px-0 py-3 border-light d-flex align-items-start">
                                <i class="fa-solid fa-circle-check text-success me-2 mt-1"></i>
                                <div><?= esc($change) ?></div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Documentation Tabbed Modal -->
    <div class="modal fade" id="docsModal" tabindex="-1" aria-labelledby="docsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="docsModalLabel" style="color: var(--primary);"><i class="fa-solid fa-book-open me-2"></i> App Guide & Documentation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Nav Tabs -->
                    <ul class="nav nav-tabs border-light mb-3" id="docsTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-semibold" id="link-tab" data-bs-toggle="tab" data-bs-target="#link-tab-pane" type="button" role="tab" aria-controls="link-tab-pane" aria-selected="true"><i class="fa-solid fa-mobile-screen me-1"></i> App Linking</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-semibold" id="crypto-tab" data-bs-toggle="tab" data-bs-target="#crypto-tab-pane" type="button" role="tab" aria-controls="crypto-tab-pane" aria-selected="false"><i class="fa-solid fa-lock me-1"></i> Security</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-semibold" id="api-tab" data-bs-toggle="tab" data-bs-target="#api-tab-pane" type="button" role="tab" aria-controls="api-tab-pane" aria-selected="false"><i class="fa-solid fa-gears me-1"></i> REST API</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-semibold" id="ml-tab" data-bs-toggle="tab" data-bs-target="#ml-tab-pane" type="button" role="tab" aria-controls="ml-tab-pane" aria-selected="false"><i class="fa-solid fa-brain me-1"></i> ML Engine</button>
                        </li>
                    </ul>
                    
                    <!-- Tab Content -->
                    <div class="tab-content small" id="docsTabContent">
                        <!-- Tab 1: App Linking -->
                        <div class="tab-pane fade show active" id="link-tab-pane" role="tabpanel" aria-labelledby="link-tab" tabindex="0">
                            <h6 class="fw-bold mb-2">Connecting Your Android Device</h6>
                            <ol class="ps-3 mb-3">
                                <li>Download the **APK file** (using the App download link in the footer or by scanning the QR code).</li>
                                <li>Install the application on your Android device and open it.</li>
                                <li>Navigate to **Settings > Security** inside the WebApp dashboard to retrieve your unique authorization token.</li>
                                <li>Enter the token in the Android app to establish a secure handshake. The device automatically generates and stores a secure device fingerprint key to protect access.</li>
                            </ol>
                            <p class="text-muted">Note: Once paired, future uploads are linked exclusively to your device fingerprint signature to prevent token leakage.</p>
                        </div>
                        
                        <!-- Tab 2: Security & Encryption -->
                        <div class="tab-pane fade" id="crypto-tab-pane" role="tabpanel" aria-labelledby="crypto-tab" tabindex="0">
                            <h6 class="fw-bold mb-2">Cryptographic Pipeline (AES-128-CBC)</h6>
                            <p>To secure raw SMS data during upload, the Android app generates a cryptographically secure random 16-byte initialization vector (IV) using `SecureRandom` on every single sync session.</p>
                            <div class="bg-light p-3 rounded-3 mb-3 font-monospace" style="font-size: 11px;">
                                Payload Layout: [16-byte Raw IV] + [AES-128-CBC Encrypted ciphertext]
                            </div>
                            <p>Upon receiving the backup file, the backend CryptoHelper extracts the first 16 bytes of the stream to extract the session-unique IV, decryption is then safely processed, and only parsed transaction records are stored in the MySQL schema.</p>
                        </div>
                        
                        <!-- Tab 3: REST API client -->
                        <div class="tab-pane fade" id="api-tab-pane" role="tabpanel" aria-labelledby="api-tab" tabindex="0">
                            <h6 class="fw-bold mb-2">V1 Client API Specifications</h6>
                            <p>The Android application communicates with the web backend using type-safe Retrofit calls targeting these core resource modules:</p>
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr><th>Endpoint</th><th>Method</th><th>Action</th></tr>
                                </thead>
                                <tbody style="font-size: 11px;">
                                    <tr><td>`/api/v1/auth/login`</td><td>POST</td><td>Pairs device using secure token validation.</td></tr>
                                    <tr><td>`/api/v1/uploads/store`</td><td>POST</td><td>Uploads encrypted payload streams.</td></tr>
                                    <tr><td>`/api/v1/analytics/overview`</td><td>GET</td><td>Fetches sync states and database metrics.</td></tr>
                                    <tr><td>`/api/v1/settings/preferences`</td><td>POST</td><td>Updates notification toggle preferences.</td></tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Tab 4: ML Classifier -->
                        <div class="tab-pane fade" id="ml-tab-pane" role="tabpanel" aria-labelledby="ml-tab" tabindex="0">
                            <h6 class="fw-bold mb-2">Local LLM Classification Pipeline</h6>
                            <p>Once raw transaction data is received and parsed into database schemas, the processing worker queues a classification request.</p>
                            <ul class="ps-3 mb-3">
                                <li>The classifier uses an async pipeline querying the local FastAPI engine model (`qwen2.5-1.5b-instruct` GGUF).</li>
                                <li>SMS contents are mapped to strict category definitions (Mobile Money, Sacco, Bank, Fintech, Insurance, Payments/Govt, etc.).</li>
                                <li>Calculated transaction amounts, counterparties, directions, and health scores are instantly generated and synced.</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>
