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
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= $this->renderSection('title') ?? 'Mpesa Analyzer' ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= base_url('favicon.png') ?>">
    <link rel="apple-touch-icon" href="<?= base_url('favicon.png') ?>">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FontAwesome 6 & FontAwesome 4 fallback -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="<?= base_url('assets/ace/font-awesome/4.5.0/css/font-awesome.min.css') ?>" rel="stylesheet">

    <!-- Ace Admin CSS -->
    <link href="<?= base_url('assets/ace/css/ace.min.css') ?>" rel="stylesheet" class="ace-main-stylesheet" id="main-ace-style" />
    <link href="<?= base_url('assets/ace/css/ace-skins.min.css') ?>" rel="stylesheet" />
    <link href="<?= base_url('assets/ace/css/ace-custom.css') ?>" rel="stylesheet" />

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Ace settings handler -->
    <script src="<?= base_url('assets/ace/js/ace-extra.min.js') ?>"></script>

    <!-- Prevent Light Flash & Handle Theme -->
    <script>
        const savedTheme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        if (savedTheme === 'dark') {
            document.documentElement.setAttribute('data-bs-theme', 'dark');
        }
    </script>

    <style>
        body {
            font-family: 'Outfit', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f2f5f8;
            margin: 0;
            padding: 0;
        }

        .navbar {
            margin-bottom: 0;
            border-radius: 0;
        }

        /* Dark mode overrides for Ace layout elements */
        [data-bs-theme="dark"] body {
            background-color: #0f172a !important;
            color: #f1f5f9;
        }

        [data-bs-theme="dark"] .navbar {
            background-color: #1e293b !important;
            border-color: #334155 !important;
        }

        [data-bs-theme="dark"] .sidebar {
            background-color: #1e293b !important;
            border-color: #334155 !important;
        }

        [data-bs-theme="dark"] .nav-list > li > a {
            background-color: #1e293b !important;
            color: #cbd5e1 !important;
        }

        [data-bs-theme="dark"] .nav-list > li.active > a,
        [data-bs-theme="dark"] .nav-list > li.active > a:hover {
            background-color: #334155 !important;
            color: #38bdf8 !important;
        }

        [data-bs-theme="dark"] .widget-box {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            color: #f1f5f9 !important;
        }

        [data-bs-theme="dark"] .widget-header {
            background: #1e293b !important;
            border-bottom: 1px solid #334155 !important;
            color: #38bdf8 !important;
        }

        [data-bs-theme="dark"] .widget-main {
            background-color: #1e293b !important;
            color: #f1f5f9 !important;
        }

        [data-bs-theme="dark"] .breadcrumbs {
            background-color: #0f172a !important;
            border-color: #334155 !important;
        }

        [data-bs-theme="dark"] .breadcrumb > li,
        [data-bs-theme="dark"] .breadcrumb > li > a {
            color: #94a3b8 !important;
        }

        [data-bs-theme="dark"] .footer .footer-inner .footer-content {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            color: #94a3b8 !important;
        }

        [data-bs-theme="dark"] .table {
            color: #f1f5f9 !important;
            border-color: #334155 !important;
        }

        [data-bs-theme="dark"] .table thead th {
            background-color: #1e293b !important;
            color: #38bdf8 !important;
            border-color: #334155 !important;
        }

        [data-bs-theme="dark"] .form-control,
        [data-bs-theme="dark"] .form-select {
            background-color: #0f172a !important;
            border-color: #334155 !important;
            color: #f1f5f9 !important;
        }

        /* Glass / Card styling fixes */
        .glass-card, .card {
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }

        /* Scan badge & action header alignment */
        .ace-header-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }
    </style>

    <?= $this->renderSection('styles') ?>
</head>

<body class="no-skin">

    <!-- Top Ace Navbar -->
    <div id="navbar" class="navbar navbar-default ace-save-state">
        <div class="navbar-container ace-save-state" id="navbar-container">
            <button type="button" class="navbar-toggle menu-toggler pull-left" id="menu-toggler" data-target="#sidebar">
                <span class="sr-only">Toggle sidebar</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>

            <div class="navbar-header pull-left">
                <a href="<?= url_to('DashboardController::index') ?>" class="navbar-brand">
                    <small>
                        <i class="fa fa-wallet"></i>
                        Mpesa Analyzer
                    </small>
                </a>
            </div>

            <!-- Navbar Quick Action Buttons & Status -->
            <div class="navbar-buttons navbar-header pull-right" role="navigation">
                <ul class="nav ace-nav align-items-center">
                    
                    <!-- Rescan Buttons -->
                    <li class="light-blue me-2 d-none d-sm-inline-block">
                        <div class="btn-group btn-group-sm py-2" role="group">
                            <button class="btn btn-xs btn-primary fw-semibold" id="rescanBtn" title="Process only new/unprocessed SMS">
                                <i class="fa fa-refresh"></i> Rescan
                            </button>
                            <button class="btn btn-xs btn-warning fw-semibold" id="rescanAllBtn" title="Reprocess ALL SMS from scratch">
                                <i class="fa fa-retweet"></i> Full Reset
                            </button>
                        </div>
                    </li>

                    <!-- Persistent Scan Status Badge -->
                    <li class="grey me-2 d-none d-sm-inline-block">
                        <button id="scanStatusBadge" class="btn btn-xs btn-default d-none" style="margin-top: 8px;" title="Click for scan progress details" data-bs-toggle="modal" data-bs-target="#scanProgressModal">
                            <span id="scanStatusIcon" class="spinner-border spinner-border-sm text-warning" role="status"></span>
                            <span id="scanStatusText">Scanning...</span>
                        </button>
                    </li>

                    <!-- Theme Light/Dark Mode Switcher -->
                    <li class="light-blue">
                        <a href="#" id="themeToggleBtn" title="Toggle Light/Dark Theme">
                            <i class="ace-icon fa fa-moon-o" id="themeIcon"></i>
                            <span id="themeText" class="d-none d-md-inline"> Theme</span>
                        </a>
                    </li>

                    <!-- User Profile Dropdown -->
                    <li class="light-blue dropdown-modal">
                        <a data-bs-toggle="dropdown" href="#" class="dropdown-toggle">
                            <?php $username = auth()->user()->username ?? 'User'; ?>
                            <span class="user-info">
                                <small>Welcome,</small>
                                <?= esc($username) ?>
                            </span>
                            <i class="ace-icon fa fa-caret-down"></i>
                        </a>

                        <ul class="user-menu dropdown-menu-right dropdown-menu dropdown-yellow dropdown-caret dropdown-close">
                            <li>
                                <a href="<?= url_to('Info::index') ?>">
                                    <i class="ace-icon fa fa-user"></i>
                                    Profile
                                </a>
                            </li>
                            <li>
                                <a href="<?= base_url('dashboard/settings/profile') ?>">
                                    <i class="ace-icon fa fa-cog"></i>
                                    Settings
                                </a>
                            </li>
                            <li class="divider"></li>
                            <li>
                                <a href="<?= url_to('logout') ?>" class="text-danger">
                                    <i class="ace-icon fa fa-power-off text-danger"></i>
                                    Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div><!-- /.navbar-container -->
    </div>

    <!-- Main Container -->
    <div class="main-container ace-save-state" id="main-container">
        <script type="text/javascript">
            try{ace.settings.loadState('main-container')}catch(e){}
        </script>

        <!-- Sidebar Navigation -->
        <div id="sidebar" class="sidebar responsive ace-save-state">
            <script type="text/javascript">
                try{ace.settings.loadState('sidebar')}catch(e){}
            </script>

            <div class="sidebar-shortcuts" id="sidebar-shortcuts">
                <div class="sidebar-shortcuts-large" id="sidebar-shortcuts-large">
                    <a href="<?= url_to('DashboardController::index') ?>" class="btn btn-success" title="Dashboard">
                        <i class="ace-icon fa fa-signal"></i>
                    </a>
                    <a href="<?= url_to('Graph::index') ?>" class="btn btn-info" title="Analytics">
                        <i class="ace-icon fa fa-bar-chart"></i>
                    </a>
                    <a href="<?= url_to('Transactions::index') ?>" class="btn btn-warning" title="Transactions">
                        <i class="ace-icon fa fa-list-alt"></i>
                    </a>
                    <a href="<?= base_url('dashboard/settings/profile') ?>" class="btn btn-danger" title="Control Center">
                        <i class="ace-icon fa fa-cogs"></i>
                    </a>
                </div>

                <div class="sidebar-shortcuts-mini" id="sidebar-shortcuts-mini">
                    <span class="btn btn-success"></span>
                    <span class="btn btn-info"></span>
                    <span class="btn btn-warning"></span>
                    <span class="btn btn-danger"></span>
                </div>
            </div><!-- /.sidebar-shortcuts -->

            <ul class="nav nav-list">
                <?php $currentURL = uri_string(); ?>

                <li class="<?= ($currentURL == 'dashboard' || $currentURL == '') ? 'active' : '' ?>">
                    <a href="<?= url_to('DashboardController::index') ?>">
                        <i class="menu-icon fa fa-tachometer"></i>
                        <span class="menu-text"> Home Dashboard </span>
                    </a>
                    <b class="arrow"></b>
                </li>

                <li class="<?= ($currentURL == 'dashboard/graph') ? 'active' : '' ?>">
                    <a href="<?= url_to('Graph::index') ?>">
                        <i class="menu-icon fa fa-pie-chart"></i>
                        <span class="menu-text"> Analytics </span>
                    </a>
                    <b class="arrow"></b>
                </li>

                <li class="<?= strpos($currentURL, 'dashboard/transactions') !== false ? 'active' : '' ?>">
                    <a href="<?= url_to('Transactions::index') ?>">
                        <i class="menu-icon fa fa-list"></i>
                        <span class="menu-text"> Transactions </span>
                    </a>
                    <b class="arrow"></b>
                </li>

                <li class="<?= strpos($currentURL, 'dashboard/reports') !== false ? 'active' : '' ?>">
                    <a href="<?= base_url('dashboard/reports') ?>">
                        <i class="menu-icon fa fa-file-text-o"></i>
                        <span class="menu-text"> Reports & Insights </span>
                    </a>
                    <b class="arrow"></b>
                </li>

                <li class="<?= strpos($currentURL, 'dashboard/budget') !== false ? 'active' : '' ?>">
                    <a href="<?= base_url('dashboard/budget') ?>">
                        <i class="menu-icon fa fa-calculator"></i>
                        <span class="menu-text"> Budget Tracker </span>
                    </a>
                    <b class="arrow"></b>
                </li>

                <li class="<?= (strpos($currentURL, 'dashboard/settings') !== false || strpos($currentURL, 'dashboard/devices') !== false || $currentURL == 'dashboard/info') ? 'active' : '' ?>">
                    <a href="<?= base_url('dashboard/settings/profile') ?>">
                        <i class="menu-icon fa fa-sliders"></i>
                        <span class="menu-text"> Control Center </span>
                    </a>
                    <b class="arrow"></b>
                </li>

                <li class="<?= strpos($currentURL, 'dashboard/history') !== false ? 'active' : '' ?>">
                    <a href="<?= url_to('HistoryController::index') ?>">
                        <i class="menu-icon fa fa-history"></i>
                        <span class="menu-text"> History </span>
                    </a>
                    <b class="arrow"></b>
                </li>

                <li class="<?= strpos($currentURL, 'dashboard/blocklist') !== false ? 'active' : '' ?>">
                    <a href="<?= base_url('dashboard/blocklist') ?>">
                        <i class="menu-icon fa fa-ban"></i>
                        <span class="menu-text"> Blocklist </span>
                    </a>
                    <b class="arrow"></b>
                </li>
            </ul><!-- /.nav-list -->

            <div class="sidebar-toggle sidebar-collapse" id="sidebar-collapse">
                <i id="sidebar-toggle-icon" class="ace-icon fa fa-angle-double-left ace-save-state" data-icon1="ace-icon fa fa-angle-double-left" data-icon2="ace-icon fa fa-angle-double-right"></i>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="main-content">
            <div class="main-content-inner">
                
                <!-- Breadcrumbs Bar -->
                <div class="breadcrumbs ace-save-state" id="breadcrumbs">
                    <ul class="breadcrumb">
                        <li>
                            <i class="ace-icon fa fa-home home-icon"></i>
                            <a href="<?= url_to('DashboardController::index') ?>">Home</a>
                        </li>
                        <li class="active"><?= ucfirst(explode('/', uri_string())[0] ?? 'Dashboard') ?></li>
                    </ul>

                    <div class="nav-search" id="nav-search">
                        <form class="form-search" action="<?= url_to('Transactions::index') ?>" method="get">
                            <span class="input-icon">
                                <input type="text" name="search" placeholder="Search transactions..." class="nav-search-input" id="nav-search-input" autocomplete="off" />
                                <i class="ace-icon fa fa-search nav-search-icon"></i>
                            </span>
                        </form>
                    </div>
                </div>

                <!-- Page Content Section -->
                <div class="page-content">
                    
                    <!-- Ace Skin Settings Box -->
                    <div class="ace-settings-container" id="ace-settings-container">
                        <div class="btn btn-app btn-xs btn-warning ace-settings-btn" id="ace-settings-btn">
                            <i class="ace-icon fa fa-cog bigger-130"></i>
                        </div>

                        <div class="ace-settings-box clearfix" id="ace-settings-box">
                            <div class="pull-left width-50">
                                <div class="ace-settings-item">
                                    <div class="pull-left">
                                        <select id="skin-colorpicker" class="hide">
                                            <option data-skin="no-skin" value="#438EB9">#438EB9</option>
                                            <option data-skin="skin-1" value="#222A2D">#222A2D</option>
                                            <option data-skin="skin-2" value="#C6487E">#C6487E</option>
                                            <option data-skin="skin-3" value="#D0D0D0">#D0D0D0</option>
                                        </select>
                                    </div>
                                    <span>&nbsp; Choose Skin</span>
                                </div>

                                <div class="ace-settings-item">
                                    <input type="checkbox" class="ace ace-checkbox-2 ace-save-state" id="ace-settings-navbar" autocomplete="off" />
                                    <label class="lbl" for="ace-settings-navbar"> Fixed Navbar</label>
                                </div>

                                <div class="ace-settings-item">
                                    <input type="checkbox" class="ace ace-checkbox-2 ace-save-state" id="ace-settings-sidebar" autocomplete="off" />
                                    <label class="lbl" for="ace-settings-sidebar"> Fixed Sidebar</label>
                                </div>

                                <div class="ace-settings-item">
                                    <input type="checkbox" class="ace ace-checkbox-2 ace-save-state" id="ace-settings-breadcrumbs" autocomplete="off" />
                                    <label class="lbl" for="ace-settings-breadcrumbs"> Fixed Breadcrumbs</label>
                                </div>
                            </div>
                        </div>
                    </div><!-- /.ace-settings-container -->

                    <div class="page-header-container mb-3">
                        <?= $this->renderSection('page_header') ?>
                    </div>

                    <div class="row">
                        <div class="col-xs-12">
                            <?= $this->renderSection('content') ?>
                        </div>
                    </div>
                </div><!-- /.page-content -->
            </div>
        </div><!-- /.main-content -->

        <!-- Footer -->
        <div class="footer">
            <div class="footer-inner">
                <div class="footer-content">
                    <span class="bigger-120">
                        <span class="blue fw-bold">Mpesa Analyzer</span> &copy; <?= date('Y') ?>
                    </span>
                    &nbsp; &nbsp;
                    <span class="action-buttons">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#changelogModal" class="badge bg-primary text-white text-decoration-none">
                            <i class="fa fa-code-fork me-1"></i> v<?= esc($systemVersion) ?>
                        </a>
                        <a href="#" data-bs-toggle="modal" data-bs-target="#docsModal" class="badge bg-info text-white text-decoration-none">
                            <i class="fa fa-book me-1"></i> Docs
                        </a>
                        <a href="<?= esc($systemGithub) ?>" target="_blank" class="badge bg-dark text-white text-decoration-none">
                            <i class="fa fa-github me-1"></i> GitHub
                        </a>
                    </span>
                </div>
            </div>
        </div>

        <a href="#" id="btn-scroll-up" class="btn-scroll-up btn btn-sm btn-inverse">
            <i class="ace-icon fa fa-angle-double-up icon-only bigger-110"></i>
        </a>
    </div><!-- /.main-container -->

    <!-- jQuery -->
    <script src="<?= base_url('assets/ace/js/jquery-2.1.4.min.js') ?>"></script>
    
    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Ace Admin Scripts -->
    <script src="<?= base_url('assets/ace/js/ace-elements.min.js') ?>"></script>
    <script src="<?= base_url('assets/ace/js/ace.min.js') ?>"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <?php $baseUrl = base_url(); ?>

    <!-- Scan Status & AJAX Logic -->
    <script>
    let pollInterval = null;
    const scanBadge = document.getElementById('scanStatusBadge');
    const scanIcon = document.getElementById('scanStatusIcon');
    const scanText = document.getElementById('scanStatusText');

    function startPolling() {
        stopPolling();
        if (scanBadge) scanBadge.classList.remove('d-none');
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
        if (!scanBadge) return;
        scanBadge.classList.remove('d-none');
        if (state === 'scanning') {
            scanIcon.className = 'spinner-border spinner-border-sm text-warning';
            scanText.textContent = label || 'Scanning...';
            scanBadge.className = 'btn btn-xs btn-warning';
        } else if (state === 'idle') {
            scanIcon.className = 'fa fa-circle text-secondary';
            scanText.textContent = label || 'No scan running';
            scanBadge.className = 'btn btn-xs btn-default';
        } else if (state === 'complete') {
            scanIcon.className = 'fa fa-check-circle text-success';
            scanText.textContent = label || 'Scan complete';
            scanBadge.className = 'btn btn-xs btn-success';
        } else if (state === 'failed') {
            scanIcon.className = 'fa fa-exclamation-triangle text-danger';
            scanText.textContent = label || 'Scan failed';
            scanBadge.className = 'btn btn-xs btn-danger';
        }
    }

    function populateModal(data) {
        const total = data.total || 0;
        const processed = data.processed || 0;
        const errors = data.errors || 0;
        const pct = total > 0 ? Math.min(100, Math.round((processed / total) * 100)) : 0;

        const bar = document.getElementById('modalProgressBar');
        if (bar) {
            bar.style.width = pct + '%';
            document.getElementById('modalProgressCount').textContent = processed + ' / ' + total;
            bar.classList.remove('bg-danger', 'bg-warning', 'bg-info', 'bg-success');
            if (pct < 30) bar.classList.add('bg-danger');
            else if (pct < 70) bar.classList.add('bg-warning');
            else if (pct < 100) bar.classList.add('bg-info');
            else bar.classList.add('bg-success');
        }

        if (document.getElementById('smsTotalCount')) {
            document.getElementById('smsTotalCount').textContent = total;
            document.getElementById('smsFinanceCount').textContent = data.finance_sms || 0;
            document.getElementById('smsBadCount').textContent = data.skipped || 0;
            document.getElementById('smsFinanceProgress').textContent = (data.completed || 0) + ' of ' + (data.finance_sms || 0);

            document.getElementById('sendersTotalCount').textContent = data.total_senders || 0;
            document.getElementById('sendersFinanceCount').textContent = data.finance_senders || 0;
            document.getElementById('sendersBadCount').textContent = data.bad_senders || 0;
            document.getElementById('sendersProgress').textContent = (data.processed_senders || 0) + ' of ' + (data.total_senders || 0);

            document.getElementById('modalErrors').innerHTML = '<i class="fa fa-times-circle text-danger me-1 small"></i>' + errors;
        }

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
                let endMs = new Date().getTime();
                if (isTerminal && j.completed_at) {
                    const compDateStr = j.completed_at.trim().replace(' ', 'T');
                    endMs = new Date(compDateStr.indexOf('Z') === -1 && compDateStr.indexOf('+') === -1 ? compDateStr + 'Z' : compDateStr).getTime();
                }
                const elapsedSec = Math.max(1, Math.round((endMs - startMs) / 1000));
                const completed = data.completed || 0;
                if (processed > 0) {
                    const overallSpeed = (processed / elapsedSec).toFixed(1);
                    if (completed > 0) {
                        const extractionSpeed = completed / elapsedSec;
                        speedText = overallSpeed + ' SMS/s';
                        if (isTerminal) etaText = (j.status === 'completed' || j.status === 'done') ? 'Done' : '—';
                        else if ((data.finance_sms || 0) > completed) {
                            const remainingFinance = (data.finance_sms || 0) - completed;
                            const etaSec = Math.round(remainingFinance / extractionSpeed);
                            etaText = etaSec < 60 ? etaSec + 's' : Math.floor(etaSec / 60) + 'm ' + (etaSec % 60) + 's';
                        } else etaText = 'Done';
                    } else {
                        speedText = overallSpeed + ' SMS/s';
                        if (isTerminal) etaText = (j.status === 'completed' || j.status === 'done') ? 'Done' : '—';
                        else if (total > processed) {
                            const remaining = total - processed;
                            const etaSec = Math.round(remaining / (processed / elapsedSec));
                            etaText = etaSec < 60 ? etaSec + 's' : Math.floor(etaSec / 60) + 'm ' + (etaSec % 60) + 's';
                        } else etaText = 'Done';
                    }
                }
            }

            if (j.metadata) {
                try {
                    const meta = typeof j.metadata === 'string' ? JSON.parse(j.metadata) : j.metadata;
                    if (meta.llm_engine) {
                        engineText = meta.llm_engine;
                        if (meta.model) engineText += ' (' + meta.model + ')';
                        if (meta.model_provider) engineText = meta.model_provider + ' / ' + engineText;
                    }
                } catch (e) {}
            }

            if (document.getElementById('modalStatusText')) {
                let st = 'Status: <strong>' + j.status + '</strong>';
                if (processed) st += ' | ' + processed + ' processed';
                if (errors > 0) st += ' | ' + errors + ' errors';
                document.getElementById('modalStatusText').innerHTML = '<i class="fa fa-info-circle me-1"></i>' + st;
            }
        }

        if (document.getElementById('modalStartedAt')) {
            document.getElementById('modalStartedAt').textContent = startedText;
            document.getElementById('modalEngine').textContent = engineText;
            document.getElementById('modalSpeed').innerHTML = '<i class="fa fa-tachometer text-info me-1 small"></i>' + speedText;
            document.getElementById('modalETA').innerHTML = '<i class="fa fa-hourglass-half text-primary me-1 small"></i>' + etaText;
        }

        const stopBtn = document.getElementById('modalStopJobBtn');
        if (stopBtn && data.job && ['queued', 'processing', 'starting'].includes(data.job.status)) {
            stopBtn.classList.remove('d-none');
            stopBtn.setAttribute('data-job-id', data.job.id);
        } else if (stopBtn) {
            stopBtn.classList.add('d-none');
        }
    }

    function pollProgress() {
        fetch('<?= $baseUrl ?>dashboard/rescan/progress')
            .then(r => r.json())
            .then(data => {
                const total = data.total || 0;
                const processed = data.processed || 0;
                const terminalStatuses = ['done', 'error', 'failed', 'cancelled', 'disabled', 'completed'];
                const isTerminal = data.job && terminalStatuses.includes(data.job.status);

                if (isTerminal) {
                    stopPolling();
                    if (data.job.status === 'done' || data.job.status === 'completed') {
                        setBadgeState('complete', processed + ' processed');
                        showAlert('Scan Complete', processed + ' messages processed.', 'success');
                    } else {
                        setBadgeState('failed', 'Scan stopped/failed');
                    }
                    populateModal(data);
                } else if (total > 0 && processed >= total) {
                    stopPolling();
                    setBadgeState('complete', processed + ' processed');
                    showAlert('Scan Complete', processed + ' messages processed.', 'success');
                    populateModal(data);
                } else if (data.running) {
                    const pct = total > 0 ? Math.min(100, Math.round((processed / total) * 100)) : 0;
                    setBadgeState('scanning', pct + '% - ' + processed + '/' + total);
                    populateModal(data);
                } else {
                    setBadgeState('idle', 'No scan running');
                    populateModal(data);
                }
            })
            .catch(() => {});
    }

    document.addEventListener('DOMContentLoaded', function() {
        fetch('<?= $baseUrl ?>dashboard/rescan/progress')
            .then(r => r.json())
            .then(data => {
                const processed = data.processed || 0;
                const terminalStatuses = ['done', 'error', 'failed', 'cancelled', 'disabled', 'completed'];
                const isTerminal = data.job && terminalStatuses.includes(data.job.status);

                if (data.running && !isTerminal) {
                    startPolling();
                } else if (data.job && (data.job.status === 'completed' || data.job.status === 'done')) {
                    setBadgeState('complete', processed + ' processed');
                } else if (isTerminal) {
                    setBadgeState('failed', 'Scan stopped/failed');
                }
            })
            .catch(() => {});
    });

    document.getElementById('rescanBtn')?.addEventListener('click', function(e) {
        e.preventDefault();
        const btn = this;
        Swal.fire({
            title: 'Start Rescan?',
            text: 'This will analyze all new/unprocessed SMS messages using the LLM.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#438EB9',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, start!'
        }).then((result) => {
            if (result.isConfirmed) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Scanning...';
                fetch('<?= $baseUrl ?>dashboard/rescan', { method: 'POST' })
                    .then(r => r.json())
                    .then(data => {
                        if (data.status === 'started') {
                            showAlert('Rescan Started', data.message || 'LLM analysis is running.', 'info');
                            startPolling();
                        } else {
                            showAlert('Notice', data.message || 'No unprocessed SMS found.', 'warning');
                            setBadgeState('idle', 'No unprocessed SMS');
                        }
                    })
                    .catch(err => showAlert('Error', 'Failed to start rescan: ' + err.message, 'danger'))
                    .finally(() => {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa fa-refresh"></i> Rescan';
                    });
            }
        });
    });

    document.getElementById('rescanAllBtn')?.addEventListener('click', function(e) {
        e.preventDefault();
        const btn = this;
        Swal.fire({
            title: 'Reset & Reprocess All?',
            text: 'WARNING: This will re-analyze all SMS messages from scratch.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, reset everything!'
        }).then((result) => {
            if (result.isConfirmed) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Resetting...';
                fetch('<?= $baseUrl ?>dashboard/rescan/all', { method: 'POST' })
                    .then(r => r.json())
                    .then(data => {
                        showAlert(data.status === 'started' ? 'Reprocess Started' : 'Notice', data.message || 'Processing triggered.', data.status === 'started' ? 'info' : 'warning');
                        if (data.status === 'started') startPolling();
                    })
                    .catch(err => showAlert('Error', 'Failed: ' + err.message, 'danger'))
                    .finally(() => {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa fa-retweet"></i> Full Reset';
                    });
            }
        });
    });

    // Theme Toggle Logic (Light / Dark)
    document.addEventListener('DOMContentLoaded', () => {
        const themeToggleBtn = document.getElementById('themeToggleBtn');
        const themeIcon = document.getElementById('themeIcon');
        const themeText = document.getElementById('themeText');
        
        function updateThemeUI(theme) {
            if (theme === 'dark') {
                if (themeIcon) {
                    themeIcon.className = 'ace-icon fa fa-sun-o text-warning';
                }
                if (themeText) themeText.textContent = ' Light';
            } else {
                if (themeIcon) {
                    themeIcon.className = 'ace-icon fa fa-moon-o';
                }
                if (themeText) themeText.textContent = ' Dark';
            }
        }

        const currentTheme = document.documentElement.getAttribute('data-bs-theme') || 'light';
        updateThemeUI(currentTheme);

        if (themeToggleBtn) {
            themeToggleBtn.addEventListener('click', (e) => {
                e.preventDefault();
                const cur = document.documentElement.getAttribute('data-bs-theme');
                const next = cur === 'dark' ? 'light' : 'dark';
                document.documentElement.setAttribute('data-bs-theme', next);
                localStorage.setItem('theme', next);
                updateThemeUI(next);
            });
        }
    });
    </script>

    <!-- Scan Progress Modal -->
    <div class="modal fade" id="scanProgressModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content glass-card border-0 shadow-lg">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">
                        <i class="fa fa-microchip me-2 text-primary"></i>LLM Scan Progress
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
                            <div id="modalProgressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                        </div>
                        <div class="d-flex justify-content-between small text-muted">
                            <span><i class="fa fa-clock-o me-1"></i>Started: <span id="modalStartedAt">—</span></span>
                            <span><i class="fa fa-cogs me-1"></i>Engine: <span id="modalEngine" class="badge bg-secondary">—</span></span>
                        </div>
                    </div>
                    
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <div class="border rounded p-3 bg-light-subtle h-100">
                                <div class="small fw-bold text-secondary mb-2 border-bottom pb-1">
                                    <i class="fa fa-envelope me-1"></i>SMS STATS
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
                                    <i class="fa fa-users me-1"></i>SENDER STATS
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
                                <div class="fw-bold fs-6 text-danger" id="modalErrors">0</div>
                                <div class="text-muted small" style="font-size: 0.75rem;">Errors</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-2">
                                <div class="fw-bold fs-6 text-info" id="modalSpeed">—</div>
                                <div class="text-muted small" style="font-size: 0.75rem;">Speed</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-2">
                                <div class="fw-bold fs-6 text-primary" id="modalETA">—</div>
                                <div class="text-muted small" style="font-size: 0.75rem;">ETA</div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 small text-muted text-start" id="modalStatusText"><i class="fa fa-info-circle me-1"></i>No scan running.</div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" id="modalStopJobBtn" class="btn btn-danger btn-sm rounded-pill px-4 d-none">
                        <i class="fa fa-stop me-1"></i> Stop Job
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
                        <i id="toastIcon" class="fa"></i>
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

    <script>
        function showAlert(title, message, type = 'info') {
            const toastEl = document.getElementById('liveToast');
            if (!toastEl) return;
            const toast = new bootstrap.Toast(toastEl, { delay: 5000 });
            
            document.getElementById('toastTitle').textContent = title;
            document.getElementById('toastMessage').textContent = message;
            const iconEl = document.getElementById('toastIcon');
            const iconContainer = document.getElementById('toastIconContainer');
            
            iconContainer.className = 'rounded-circle d-flex align-items-center justify-content-center';
            iconEl.className = 'fa';
            
            if (type === 'success') {
                iconContainer.classList.add('bg-success-subtle', 'text-success');
                iconEl.classList.add('fa-check-circle');
            } else if (type === 'danger') {
                iconContainer.classList.add('bg-danger-subtle', 'text-danger');
                iconEl.classList.add('fa-exclamation-triangle');
            } else if (type === 'warning') {
                iconContainer.classList.add('bg-warning-subtle', 'text-warning');
                iconEl.classList.add('fa-exclamation-circle');
            } else {
                iconContainer.classList.add('bg-primary-subtle', 'text-primary');
                iconEl.classList.add('fa-info-circle');
            }
            
            toast.show();
        }
    </script>

    <!-- Changelog Modal -->
    <div class="modal fade" id="changelogModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-primary"><i class="fa fa-history me-2"></i> System Changelog</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <span class="badge bg-primary text-white rounded-pill px-3 py-2 fw-semibold">Version: v<?= esc($systemVersion) ?></span>
                    </div>
                    <ul class="list-group list-group-flush small">
                        <?php foreach ($systemChangelog as $change): ?>
                            <li class="list-group-item px-0 py-2 border-light d-flex align-items-start">
                                <i class="fa fa-check text-success me-2 mt-1"></i>
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

    <!-- Documentation Modal -->
    <div class="modal fade" id="docsModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-primary"><i class="fa fa-book me-2"></i> App Guide & Documentation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs border-light mb-3" id="docsTab" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active fw-semibold" id="link-tab" data-bs-toggle="tab" data-bs-target="#link-tab-pane" type="button"><i class="fa fa-mobile me-1"></i> App Linking</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link fw-semibold" id="crypto-tab" data-bs-toggle="tab" data-bs-target="#crypto-tab-pane" type="button"><i class="fa fa-lock me-1"></i> Security</button>
                        </li>
                    </ul>
                    <div class="tab-content small" id="docsTabContent">
                        <div class="tab-pane fade show active" id="link-tab-pane">
                            <h6 class="fw-bold mb-2">Connecting Your Android Device</h6>
                            <ol class="ps-3 mb-3">
                                <li>Download the Android APK application.</li>
                                <li>Retrieve your unique token from Control Center > Security.</li>
                                <li>Pair the app using the device token.</li>
                            </ol>
                        </div>
                        <div class="tab-pane fade" id="crypto-tab-pane">
                            <h6 class="fw-bold mb-2">Cryptographic Pipeline (AES-128-CBC)</h6>
                            <p>Encrypted payload data uploaded from the mobile application is securely decrypted server-side using session-unique IV vectors.</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <?= $this->renderSection('scripts') ?>
</body>
</html>
