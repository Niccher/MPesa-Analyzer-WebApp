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
                <h4><i class="fa-solid fa-user-shield"></i> Admin Panel</h4>
                <button class="btn btn-sm btn-close d-lg-none" id="sidebarClose"></button>
            </div>
            
            <ul class="nav flex-column mt-3">
                <?php $currentURL = uri_string(); ?>

                <!-- Admin Section -->
                <li class="nav-item px-3 mt-1 mb-1">
                    <small class="text-uppercase fw-bold text-secondary" style="font-size: 0.65rem; letter-spacing: 1px;">Admin</small>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentURL == 'admin' || $currentURL == 'admin/') ? 'active' : '' ?>" href="<?= base_url('admin') ?>">
                        <i class="fa-solid fa-gauge-high"></i> Overview
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= strpos($currentURL, 'admin/users') !== false ? 'active' : '' ?>" href="<?= base_url('admin/users') ?>">
                        <i class="fa-solid fa-users"></i> Users
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= strpos($currentURL, 'admin/devices') !== false ? 'active' : '' ?>" href="<?= base_url('admin/devices') ?>">
                        <i class="fa-solid fa-mobile-screen-button"></i> Devices
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= strpos($currentURL, 'admin/ml') !== false ? 'active' : '' ?>" href="<?= base_url('admin/ml') ?>">
                        <i class="fa-solid fa-microchip"></i> ML Backend
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= strpos($currentURL, 'admin/crons') !== false ? 'active' : '' ?>" href="<?= base_url('admin/crons') ?>">
                        <i class="fa-solid fa-clock"></i> Cron Jobs
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= strpos($currentURL, 'admin/notifications') !== false ? 'active' : '' ?>" href="<?= base_url('admin/notifications') ?>">
                        <i class="fa-solid fa-envelope"></i> Email Notifications
                    </a>
                </li>

                <!-- System Section -->
                <li class="nav-item px-3 mt-3 mb-1">
                    <small class="text-uppercase fw-bold text-secondary" style="font-size: 0.65rem; letter-spacing: 1px;">System</small>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= strpos($currentURL, 'admin/system') !== false ? 'active' : '' ?>" href="<?= base_url('admin/system') ?>">
                        <i class="fa-solid fa-gears"></i> System Utilities
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= strpos($currentURL, 'admin/audit') !== false ? 'active' : '' ?>" href="<?= base_url('admin/audit') ?>">
                        <i class="fa-solid fa-clipboard-list"></i> Audit Trail
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
                    
                    <h5 class="mb-0 fw-bold text-dark d-none d-sm-block me-3">Admin Panel</h5>

                    <div class="ms-auto d-flex align-items-center">
                        <div class="dropdown">
                            <a class="nav-link dropdown-toggle text-dark fw-semibold d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <?php $username = auth()->user()->username ?? 'User'; ?>
                                <div class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 32px; height: 32px; background-color: var(--primary);">
                                    <?= strtoupper(substr($username, 0, 1)) ?>
                                </div>
                                <?= $username ?>
                            </a>

                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                <li>
                                    <button class="dropdown-item d-flex align-items-center" id="themeToggleBtn">
                                        <i class="fa-solid fa-moon me-2" id="themeIcon"></i> <span id="themeText">Dark Mode</span>
                                    </button>
                                </li>
                                <li><a class="dropdown-item" href="<?= base_url('admin') ?>"><i class="fa-solid fa-gauge-high me-2"></i> Admin Dashboard</a></li>
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
                            <a href="#" class="text-dark text-decoration-none" data-bs-toggle="modal" data-bs-target="#changelogModal"><i class="fa-solid fa-code-branch me-1"></i> v<?= esc($systemVersion) ?> Admin</a>
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

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

    <?= $this->renderSection('scripts') ?>
</body>
</html>
