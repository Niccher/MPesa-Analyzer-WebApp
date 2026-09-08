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
    <title><?= $this->renderSection('title') ?? 'Mpesa Analyzer - SuperAdmin' ?></title>

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

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Ace settings handler -->
    <script src="<?= base_url('assets/ace/js/ace-extra.min.js') ?>"></script>

    <!-- Prevent Light Flash -->
    <script>
        const savedTheme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        if (savedTheme === 'dark') {
            document.documentElement.setAttribute('data-bs-theme', 'dark');
        }
    </script>

    <style>
        body {
            font-family: 'Outfit', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #f2f5f8;
            margin: 0;
            padding: 0;
        }

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

        [data-bs-theme="dark"] .nav-list > li.active > a {
            background-color: #334155 !important;
            color: #38bdf8 !important;
        }

        [data-bs-theme="dark"] .widget-box,
        [data-bs-theme="dark"] .widget-main {
            background-color: #1e293b !important;
            color: #f1f5f9 !important;
            border-color: #334155 !important;
        }

        [data-bs-theme="dark"] .breadcrumbs {
            background-color: #0f172a !important;
            border-color: #334155 !important;
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
                <a href="<?= base_url('admin') ?>" class="navbar-brand">
                    <small>
                        <i class="fa fa-user-secret"></i>
                        Admin Panel - Mpesa Analyzer
                    </small>
                </a>
            </div>

            <!-- Navbar Right Items -->
            <div class="navbar-buttons navbar-header pull-right" role="navigation">
                <ul class="nav ace-nav align-items-center">
                    
                    <li class="light-blue me-2">
                        <a href="<?= url_to('DashboardController::index') ?>" title="Switch to Main Dashboard">
                            <i class="ace-icon fa fa-tachometer"></i>
                            <span class="d-none d-md-inline"> User Dashboard</span>
                        </a>
                    </li>

                    <li class="light-blue me-2">
                        <a href="#" id="themeToggleBtn" title="Toggle Light/Dark Theme">
                            <i class="ace-icon fa fa-moon-o" id="themeIcon"></i>
                            <span id="themeText" class="d-none d-md-inline"> Theme</span>
                        </a>
                    </li>

                    <li class="light-blue dropdown-modal">
                        <a data-bs-toggle="dropdown" href="#" class="dropdown-toggle">
                            <?php $username = auth()->user()->username ?? 'SuperAdmin'; ?>
                            <span class="user-info">
                                <small>Admin,</small>
                                <?= esc($username) ?>
                            </span>
                            <i class="ace-icon fa fa-caret-down"></i>
                        </a>

                        <ul class="user-menu dropdown-menu-right dropdown-menu dropdown-yellow dropdown-caret dropdown-close">
                            <li>
                                <a href="<?= base_url('admin') ?>">
                                    <i class="ace-icon fa fa-tachometer"></i>
                                    Overview
                                </a>
                            </li>
                            <li>
                                <a href="<?= base_url('admin/system') ?>">
                                    <i class="ace-icon fa fa-cogs"></i>
                                    System
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
        </div>
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
                    <a href="<?= base_url('admin') ?>" class="btn btn-success" title="Overview">
                        <i class="ace-icon fa fa-tachometer"></i>
                    </a>
                    <a href="<?= base_url('admin/users') ?>" class="btn btn-info" title="Users">
                        <i class="ace-icon fa fa-users"></i>
                    </a>
                    <a href="<?= base_url('admin/ml') ?>" class="btn btn-warning" title="ML Backend">
                        <i class="ace-icon fa fa-cogs"></i>
                    </a>
                    <a href="<?= base_url('admin/system') ?>" class="btn btn-danger" title="System Utilities">
                        <i class="ace-icon fa fa-wrench"></i>
                    </a>
                </div>
            </div>

            <ul class="nav nav-list">
                <?php $currentURL = uri_string(); ?>

                <li class="nav-header">Admin Administration</li>

                <li class="<?= ($currentURL == 'admin' || $currentURL == 'admin/') ? 'active' : '' ?>">
                    <a href="<?= base_url('admin') ?>">
                        <i class="menu-icon fa fa-tachometer"></i>
                        <span class="menu-text"> Overview </span>
                    </a>
                    <b class="arrow"></b>
                </li>

                <li class="<?= strpos($currentURL, 'admin/users') !== false ? 'active' : '' ?>">
                    <a href="<?= base_url('admin/users') ?>">
                        <i class="menu-icon fa fa-users"></i>
                        <span class="menu-text"> Users Management </span>
                    </a>
                    <b class="arrow"></b>
                </li>

                <li class="<?= strpos($currentURL, 'admin/devices') !== false ? 'active' : '' ?>">
                    <a href="<?= base_url('admin/devices') ?>">
                        <i class="menu-icon fa fa-mobile"></i>
                        <span class="menu-text"> Connected Devices </span>
                    </a>
                    <b class="arrow"></b>
                </li>

                <li class="<?= strpos($currentURL, 'admin/ml') !== false ? 'active' : '' ?>">
                    <a href="<?= base_url('admin/ml') ?>">
                        <i class="menu-icon fa fa-cogs"></i>
                        <span class="menu-text"> ML Classifier Backend </span>
                    </a>
                    <b class="arrow"></b>
                </li>

                <li class="<?= strpos($currentURL, 'admin/crons') !== false ? 'active' : '' ?>">
                    <a href="<?= base_url('admin/crons') ?>">
                        <i class="menu-icon fa fa-clock-o"></i>
                        <span class="menu-text"> Cron Schedules </span>
                    </a>
                    <b class="arrow"></b>
                </li>

                <li class="<?= strpos($currentURL, 'admin/notifications') !== false ? 'active' : '' ?>">
                    <a href="<?= base_url('admin/notifications') ?>">
                        <i class="menu-icon fa fa-envelope-o"></i>
                        <span class="menu-text"> Notifications & Email </span>
                    </a>
                    <b class="arrow"></b>
                </li>

                <li class="nav-header">System Operations</li>

                <li class="<?= strpos($currentURL, 'admin/system') !== false ? 'active' : '' ?>">
                    <a href="<?= base_url('admin/system') ?>">
                        <i class="menu-icon fa fa-wrench"></i>
                        <span class="menu-text"> System Utilities </span>
                    </a>
                    <b class="arrow"></b>
                </li>

                <li class="<?= strpos($currentURL, 'admin/audit') !== false ? 'active' : '' ?>">
                    <a href="<?= base_url('admin/audit') ?>">
                        <i class="menu-icon fa fa-list-alt"></i>
                        <span class="menu-text"> Audit Trail </span>
                    </a>
                    <b class="arrow"></b>
                </li>
            </ul>

            <div class="sidebar-toggle sidebar-collapse" id="sidebar-collapse">
                <i id="sidebar-toggle-icon" class="ace-icon fa fa-angle-double-left ace-save-state" data-icon1="ace-icon fa fa-angle-double-left" data-icon2="ace-icon fa fa-angle-double-right"></i>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="main-content">
            <div class="main-content-inner">
                
                <div class="breadcrumbs ace-save-state" id="breadcrumbs">
                    <ul class="breadcrumb">
                        <li>
                            <i class="ace-icon fa fa-home home-icon"></i>
                            <a href="<?= base_url('admin') ?>">Admin Home</a>
                        </li>
                        <li class="active"><?= ucfirst(explode('/', uri_string())[1] ?? 'Overview') ?></li>
                    </ul>
                </div>

                <div class="page-content">
                    <div class="page-header-container mb-3">
                        <?= $this->renderSection('page_header') ?>
                    </div>

                    <div class="row">
                        <div class="col-xs-12">
                            <?= $this->renderSection('content') ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-inner">
                <div class="footer-content">
                    <span class="bigger-120">
                        <span class="blue fw-bold">Mpesa Analyzer Admin</span> &copy; <?= date('Y') ?>
                    </span>
                    &nbsp; &nbsp;
                    <span class="action-buttons">
                        <span class="badge bg-primary text-white">v<?= esc($systemVersion) ?> SuperAdmin</span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="<?= base_url('assets/ace/js/jquery-2.1.4.min.js') ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url('assets/ace/js/ace-elements.min.js') ?>"></script>
    <script src="<?= base_url('assets/ace/js/ace.min.js') ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const themeToggleBtn = document.getElementById('themeToggleBtn');
        const themeIcon = document.getElementById('themeIcon');
        const themeText = document.getElementById('themeText');
        
        function updateThemeUI(theme) {
            if (theme === 'dark') {
                if (themeIcon) themeIcon.className = 'ace-icon fa fa-sun-o text-warning';
                if (themeText) themeText.textContent = ' Light';
            } else {
                if (themeIcon) themeIcon.className = 'ace-icon fa fa-moon-o';
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

    <?= $this->renderSection('scripts') ?>
</body>
</html>
