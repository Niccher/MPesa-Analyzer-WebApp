<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= $this->renderSection('title') ?? 'Mpesa Analyzer' ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #5D5FEF;
            --bg-color: #B1B8ED;
            --sidebar-bg: #FFFFFF;
            --sidebar-width: 260px;
            --topbar-height: 70px;
            --text-dark: #1A1A1A;
            --text-light: #666666;
            --hover-bg: #F3F4F6;
            --radius: 16px;
            --card-light: #6C5CE7;
            --card-dark: #4834D4;
        }

        * {
            box-sizing: border-box;
            font-family: 'Outfit', sans-serif;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-dark);
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* --- Sidebar --- */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 15px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
        }

        .sidebar-header {
            height: var(--topbar-height);
            display: flex;
            align-items: center;
            padding: 0 24px;
            border-bottom: 1px solid #f0f0f0;
        }

        .sidebar-header h2 {
            font-size: 1.4rem;
            color: var(--primary);
            font-weight: 700;
        }

        .sidebar-menu {
            flex: 1;
            padding: 20px 15px;
            overflow-y: auto;
        }

        .menu-item {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            margin-bottom: 8px;
            border-radius: 12px;
            color: var(--text-light);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
        }

        .menu-item:hover, .menu-item.active {
            background: var(--hover-bg);
            color: var(--primary);
        }

        .menu-item.active {
            background: var(--primary);
            color: #fff;
            box-shadow: 0 4px 10px rgba(93, 95, 239, 0.3);
        }

        .menu-item i {
            margin-right: 15px;
            font-size: 1.2rem;
            width: 24px;
            text-align: center;
        }

        .sidebar-footer {
            padding: 20px;
            border-top: 1px solid #f0f0f0;
        }

        .sidebar-footer .menu-item.logout {
            color: #FF4757;
        }

        .sidebar-footer .menu-item.logout:hover {
            background: #FEE2E2;
        }

        /* --- Main Content --- */
        .main-wrapper {
            flex: 1;
            margin-left: var(--sidebar-width);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        /* --- Topbar --- */
        .topbar {
            height: var(--topbar-height);
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            border-bottom: 1px solid rgba(255,255,255,0.2);
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--text-dark);
            cursor: pointer;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #fff;
            padding: 6px 16px;
            border-radius: 50px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            font-weight: 600;
        }

        .user-profile .avatar {
            width: 32px;
            height: 32px;
            background: var(--primary);
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
        }

        /* --- Content Area --- */
        .content-area {
            padding: 30px;
            flex: 1;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-wrapper {
                margin-left: 0;
            }
            .menu-toggle {
                display: block;
            }
        }
    </style>
    <?= $this->renderSection('styles') ?>
</head>
<body>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2><i class="fa-solid fa-wallet"></i> Mpesa Analyzer</h2>
        </div>
        <div class="sidebar-menu">
            <?php $currentURL = uri_string(); ?>
            <a href="<?= url_to('Dash::index') ?>" class="menu-item <?= ($currentURL == 'dashboard' || $currentURL == '') ? 'active' : '' ?>">
                <i class="fa-solid fa-house"></i> Home
            </a>
            <a href="<?= url_to('Graph::index') ?>" class="menu-item <?= ($currentURL == 'dashboard/graph') ? 'active' : '' ?>">
                <i class="fa-solid fa-chart-pie"></i> Graph Analytics
            </a>
            <a href="<?= url_to('Transactions::index') ?>" class="menu-item <?= ($currentURL == 'dashboard/transactions') ? 'active' : '' ?>">
                <i class="fa-solid fa-magnifying-glass"></i> Transactions
            </a>
            <a href="<?= url_to('History::index') ?>" class="menu-item <?= ($currentURL == 'dashboard/history') ? 'active' : '' ?>">
                <i class="fa-solid fa-clock-rotate-left"></i> History
            </a>
            <a href="<?= url_to('Info::index') ?>" class="menu-item <?= ($currentURL == 'dashboard/info') ? 'active' : '' ?>">
                <i class="fa-solid fa-circle-info"></i> Info
            </a>
        </div>
        <div class="sidebar-footer">
            <a href="<?= url_to('logout') ?>" class="menu-item logout">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
            </a>
        </div>
    </aside>

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <!-- Topbar -->
        <header class="topbar">
            <button class="menu-toggle" id="menuToggle">
                <i class="fa-solid fa-bars"></i>
            </button>
            <div class="topbar-title">
                <h3 style="font-weight: 600; color: #333; margin:0;">Dashboard</h3>
            </div>
            <div class="topbar-right">
                <div class="user-profile">
                    <?php $username = auth()->user()->username ?? 'User'; ?>
                    <div class="avatar"><?= strtoupper(substr($username, 0, 1)) ?></div>
                    <span><?= $username ?></span>
                </div>
            </div>
        </header>

        <!-- Content Area -->
        <main class="content-area">
            <?= $this->renderSection('content') ?>
        </main>
    </div>

    <!-- Scripts -->
    <script>
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');

        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('show');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 992) {
                if (!sidebar.contains(e.target) && !menuToggle.contains(e.target) && sidebar.classList.contains('show')) {
                    sidebar.classList.remove('show');
                }
            }
        });
    </script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>
