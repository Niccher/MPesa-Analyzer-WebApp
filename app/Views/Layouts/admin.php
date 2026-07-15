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
    
    <!-- DataTables Bootstrap 5 CSS -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary: #5D5FEF;
            --bg-color: #B1B8ED;
            --sidebar-width: 260px;
            --card-bg: rgba(255, 255, 255, 0.9);
            --card-border: rgba(255, 255, 255, 0.4);
            --text-main: #2d3436;
            --text-muted: #636e72;
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
            border-radius: 8px;
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
            
            <?php
                $smsView = session()->get('sms_view') ?? 'mpesa';
                $baseUrl = base_url();
            ?>

            <!-- Top Navigation -->
            <nav class="navbar navbar-expand-lg navbar-light py-3">
                <div class="container-fluid px-0">
                    <button class="btn btn-outline-primary d-lg-none me-2" id="sidebarToggle">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    
                    <h5 class="mb-0 fw-bold text-dark d-none d-sm-block me-3">Dashboard</h5>

                    <!-- View Toggle -->
                    <div class="btn-group btn-group-sm me-2" role="group" id="viewToggle">
                        <a href="#" data-view="mpesa"
                           class="btn btn-sm px-3 fw-semibold d-flex align-items-center gap-1
                                  <?= $smsView === 'mpesa' ? 'active' : 'btn-outline-secondary' ?>">
                            <i class="fa-solid fa-phone"></i> All MPESA
                        </a>
                        <a href="#" data-view="finance"
                           class="btn btn-sm px-3 fw-semibold d-flex align-items-center gap-1
                                  <?= $smsView === 'finance' ? 'active' : 'btn-outline-secondary' ?>">
                            <i class="fa-solid fa-building-columns"></i> All Transactions
                        </a>
                        <button class="btn btn-sm btn-outline-secondary border-start-0"
                                type="button" data-bs-toggle="popover"
                                data-bs-trigger="focus" data-bs-placement="bottom"
                                title="Data Scope"
                                data-bs-content="All MPESA: shows messages from MPESA number only. All Transactions: shows messages from ALL classified financial senders (banks, SACCOS, fintechs) once processed by the LLM analyzer.">
                            <i class="fa-regular fa-circle-question"></i>
                        </button>
                    </div>

                    <!-- Rescan Button -->
                    <button class="btn btn-sm btn-outline-warning fw-semibold d-flex align-items-center gap-1 me-3"
                            id="rescanBtn" title="Re-analyze all uploaded SMS via LLM">
                        <i class="fa-solid fa-arrows-rotate"></i> Rescan
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
                            document.getElementById('viewToggle')?.addEventListener('click', function(e) {
                                const btn = e.target.closest('[data-view]');
                                if (!btn) return;
                                e.preventDefault();
                                const view = btn.dataset.view;
                                fetch('<?= $baseUrl ?>set-view/' + view, { method: 'POST' })
                                    .then(() => location.reload());
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
                                            showAlert('Rescan Started', data.message || 'LLM analysis is running in the background. Check back shortly for updated results.', 'info');
                                        } else {
                                            showAlert('Notice', data.message || 'No unprocessed SMS found.', 'warning');
                                        }
                                    })
                                    .catch(err => showAlert('Error', 'Failed to start rescan: ' + err.message, 'danger'))
                                    .finally(() => {
                                        btn.disabled = false;
                                        btn.innerHTML = '<i class="fa-solid fa-arrows-rotate"></i> Rescan';
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
                            &copy; <?= date('Y') ?> <span class="fw-bold text-primary">M-Pesa Analyzer</span>. 
                            <span class="d-none d-sm-inline">All rights reserved.</span>
                        </div>
                        <div class="d-flex gap-3">
                            <span><i class="fa-solid fa-code-branch me-1"></i> v2.1.0</span>
                            <a href="#" class="text-dark text-decoration-none"><i class="fa-solid fa-book-open me-1"></i> Docs</a>
                            <a href="#" class="text-dark text-decoration-none"><i class="fa-solid fa-download me-1"></i> App</a>
                        </div>
                    </div>
                </footer>
            </main>
            
        </div>
    </div>

    <!-- jQuery (Required for DataTables) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- Bootstrap 5 Bundle JS (Includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables JS & Bootstrap 5 Integration -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

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
