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
                    <a class="nav-link <?= ($currentURL == 'dashboard' || $currentURL == '') ? 'active' : '' ?>" href="<?= url_to('Dash::index') ?>">
                        <i class="fa-solid fa-house"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentURL == 'dashboard/graph') ? 'active' : '' ?>" href="<?= url_to('Graph::index') ?>">
                        <i class="fa-solid fa-chart-pie"></i> Analytics
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentURL == 'dashboard/search') ? 'active' : '' ?>" href="<?= base_url('dashboard/search') ?>">
                        <i class="fa-solid fa-magnifying-glass"></i> Search & Filtering
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= strpos($currentURL, 'dashboard/transactions') !== false ? 'active' : '' ?>" href="<?= url_to('Transactions::index') ?>">
                        <i class="fa-solid fa-list-check"></i> Transactions
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
                    <a class="nav-link <?= ($currentURL == 'dashboard/history') ? 'active' : '' ?>" href="<?= url_to('History::index') ?>">
                        <i class="fa-solid fa-clock-rotate-left"></i> History
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentURL == 'dashboard/info') ? 'active' : '' ?>" href="<?= url_to('Info::index') ?>">
                        <i class="fa-solid fa-circle-info"></i> Info & Auth
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
                        <button class="btn btn-outline-warning fw-semibold d-flex align-items-center gap-1"
                                id="rescanBtn" title="Process only new/unprocessed SMS">
                            <i class="fa-solid fa-arrows-rotate"></i> Rescan
                        </button>
                        <button class="btn btn-outline-danger fw-semibold d-flex align-items-center gap-1"
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

                                document.getElementById('modalProgressBar').style.width = pct + '%';
                                document.getElementById('modalProgressCount').textContent = processed + ' / ' + total;
                                document.getElementById('modalTotal').textContent = total;
                                document.getElementById('modalProcessed').textContent = processed;
                                document.getElementById('modalErrors').textContent = errors;

                                if (data.job) {
                                    const j = data.job;
                                    let st = 'Status: <strong>' + j.status + '</strong>';
                                    if (j.messages_processed) st += ' | ' + j.messages_processed + ' processed';
                                    if (j.errors > 0) st += ' | ' + j.errors + ' errors';
                                    if (j.duration_seconds) st += ' | ' + j.duration_seconds + 's elapsed';
                                    document.getElementById('modalStatusText').innerHTML = st;
                                } else {
                                    document.getElementById('modalStatusText').textContent = 'Total SMS: ' + total + ', Completed: ' + processed + ', Errors: ' + errors;
                                }

                                if (total > 0 && !data.running && (data.job && (data.job.status === 'completed' || data.job.status === 'failed'))) {
                                    document.getElementById('modalProgressBar').classList.remove('progress-bar-animated');
                                    if (data.job.status === 'completed') {
                                        document.getElementById('modalProgressBar').classList.add('bg-success');
                                    } else {
                                        document.getElementById('modalProgressBar').classList.add('bg-danger');
                                    }
                                } else if (total > 0 && processed >= total) {
                                    document.getElementById('modalProgressBar').classList.remove('progress-bar-animated');
                                    document.getElementById('modalProgressBar').classList.add('bg-success');
                                } else {
                                    document.getElementById('modalProgressBar').classList.remove('bg-success', 'bg-danger');
                                }
                            }

                            function pollProgress() {
                                fetch('<?= $baseUrl ?>dashboard/rescan/progress')
                                    .then(r => r.json())
                                    .then(data => {
                                        const total = data.total || 0;
                                        const processed = data.processed || 0;
                                        const errors = data.errors || 0;

                                        if (data.job && (data.job.status === 'completed' || data.job.status === 'failed')) {
                                            stopPolling();
                                            if (data.job.status === 'completed') {
                                                setBadgeState('complete', data.job.messages_processed + ' processed');
                                                showAlert('Scan Complete', data.job.messages_processed + ' messages processed' + (errors ? ', ' + errors + ' errors' : '') + '.', 'success');
                                            } else {
                                                setBadgeState('failed', 'Scan failed');
                                                showAlert('Scan Failed', 'LLM analysis encountered errors.', 'danger');
                                            }
                                            populateModal(data);
                                            setTimeout(() => window.location.reload(), 3000);
                                        } else if (!data.running && processed >= total && total > 0) {
                                            stopPolling();
                                            setBadgeState('complete', processed + ' processed');
                                            showAlert('Scan Complete', processed + ' messages processed.', 'success');
                                            populateModal(data);
                                            setTimeout(() => window.location.reload(), 3000);
                                        } else if (data.running || (total > 0 && processed < total)) {
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
                                        if (data.running || (total > 0 && processed < total && data.job && data.job.status !== 'completed' && data.job.status !== 'failed')) {
                                            startPolling();
                                        } else if (data.job && data.job.status === 'completed') {
                                            setBadgeState('complete', data.job.messages_processed + ' processed');
                                        } else if (data.job && data.job.status === 'failed') {
                                            setBadgeState('failed', 'Scan failed');
                                        }
                                    })
                                    .catch(() => {});
                            });

                            document.getElementById('rescanBtn')?.addEventListener('click', function(e) {
                                e.preventDefault();
                                if (!confirm('This will re-analyze all unprocessed SMS using the LLM. It may take several minutes depending on the volume. Continue?')) return;
                                const btn = this;
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
                            });

                            document.getElementById('rescanAllBtn')?.addEventListener('click', function(e) {
                                e.preventDefault();
                                if (!confirm('WARNING: This will DELETE all existing LLM analysis and re-process every single SMS from scratch. This may take many minutes. Continue?')) return;
                                const btn = this;
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
                            });
                            </script>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                <li>
                                    <button class="dropdown-item d-flex align-items-center" id="themeToggleBtn">
                                        <i class="fa-solid fa-moon me-2" id="themeIcon"></i> <span id="themeText">Dark Mode</span>
                                    </button>
                                </li>
                                <li><a class="dropdown-item" href="<?= url_to('Info::index') ?>"><i class="fa-solid fa-user me-2"></i> Profile</a></li>
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
                        <div class="d-flex gap-3">
                            <span><i class="fa-solid fa-code-branch me-1"></i> v2.1.0</span>
                            <a href="<?= base_url('setup') ?>" class="text-dark text-decoration-none"><i class="fa-solid fa-book-open me-1"></i> Docs</a>
                            <a href="<?= base_url('android-app') ?>" class="text-dark text-decoration-none"><i class="fa-solid fa-download me-1"></i> App</a>
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
        <div class="modal-dialog modal-dialog-centered">
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
                            <span id="modalProgressLabel">Progress</span>
                            <span id="modalProgressCount">0 / 0</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div id="modalProgressBar" class="progress-bar progress-bar-striped progress-bar-animated"
                                 role="progressbar" style="width: 0%"></div>
                        </div>
                    </div>
                    <div class="row g-2 text-center">
                        <div class="col-4">
                            <div class="border rounded p-2">
                                <div class="fw-bold fs-5" id="modalTotal">0</div>
                                <div class="text-muted small">Total</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-2">
                                <div class="fw-bold fs-5 text-success" id="modalProcessed">0</div>
                                <div class="text-muted small">Done</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-2">
                                <div class="fw-bold fs-5 text-danger" id="modalErrors">0</div>
                                <div class="text-muted small">Errors</div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 small text-muted" id="modalStatusText">No scan running.</div>
                </div>
                <div class="modal-footer border-0 pt-0">
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

    <?= $this->renderSection('scripts') ?>
</body>
</html>
