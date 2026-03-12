<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Financial Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #5D5FEF;
            --bg-color: #B1B8ED;
            --card-light: #6C5CE7;
            --card-dark: #4834D4;
            --text-white: #FFFFFF;
            --radius: 16px;
        }

        body {
            background-color: var(--bg-color);
            margin: 0;
            font-family: 'Outfit', sans-serif;
            color: var(--text-white);
            padding-bottom: 50px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 10px 0;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 16px;
            border-radius: 50px;
            backdrop-filter: blur(5px);
        }

        .user-profile img {
            width: 32px;
            height: 32px;
            border-radius: 50%;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .summary-card {
            background: var(--card-light);
            border-radius: var(--radius);
            padding: 24px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            transition: transform 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .summary-card:hover {
            transform: translateY(-5px);
        }

        .summary-card h2 {
            margin: 0 0 20px 0;
            font-size: 1.4rem;
            text-align: center;
            font-weight: 700;
        }

        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            text-align: center;
        }

        .metric-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }

        .metric-icon {
            width: 48px;
            height: 48px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .metric-value {
            font-size: 1.1rem;
            font-weight: 600;
        }

        .metric-label {
            font-size: 0.75rem;
            opacity: 0.9;
        }

        .logout-btn {
            background: #FF4757;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: opacity 0.3s;
        }

        .logout-btn:hover {
            opacity: 0.8;
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        .summary-card {
            animation: fadeIn 0.5s ease-out forwards;
        }

        .summary-card:nth-child(2) { animation-delay: 0.1s; }
        .summary-card:nth-child(3) { animation-delay: 0.2s; }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div>
                <h1 style="margin:0; font-size: 1.8rem;">Mpesa Analyzer</h1>
                <p style="margin:5px 0 0 0; opacity: 0.8;">Web Dashboard</p>
            </div>
            <div style="display: flex; align-items: center; gap: 15px;">
                <div class="user-profile">
                    <span>Welcome, <?= auth()->user()->username ?? 'User' ?></span>
                </div>
                <a href="<?= url_to('logout') ?>" class="logout-btn">Log Out</a>
            </div>
        </header>

        <main>
            <div class="dashboard-grid">
                <!-- General Summary -->
                <section class="summary-card">
                    <h2>General Summary</h2>
                    <div class="metrics-grid">
                        <div class="metric-item">
                            <div class="metric-icon">💰</div>
                            <span class="metric-value"><?= $total_uploads ?></span>
                            <span class="metric-label">All</span>
                        </div>
                        <div class="metric-item">
                            <div class="metric-icon">💳</div>
                            <span class="metric-value">0</span>
                            <span class="metric-label">Balance</span>
                        </div>
                        <div class="metric-item">
                            <div class="metric-icon">📦</div>
                            <span class="metric-value">0</span>
                            <span class="metric-label">Fuliza</span>
                        </div>
                        <div class="metric-item" style="grid-column: span 3; margin-top: 10px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 15px;">
                            <span class="metric-label">Recent Activity: <?= count($recent_uploads) ?> entries</span>
                        </div>
                    </div>
                </section>

                <!-- Sent Summary -->
                <section class="summary-card" style="background: #341f97;">
                    <h2>Sent Summary</h2>
                    <div class="metrics-grid">
                        <div class="metric-item">
                            <div class="metric-icon">📤</div>
                            <span class="metric-value">0</span>
                            <span class="metric-label">All</span>
                        </div>
                        <div class="metric-item">
                            <div class="metric-icon">📲</div>
                            <span class="metric-value">0</span>
                            <span class="metric-label">Mpesa</span>
                        </div>
                        <div class="metric-item">
                            <div class="metric-icon">🏠</div>
                            <span class="metric-value">0</span>
                            <span class="metric-label">Till</span>
                        </div>
                    </div>
                </section>

                <!-- Received Summary -->
                <section class="summary-card" style="background: #1e272e;">
                    <h2>Received Summary</h2>
                    <div class="metrics-grid">
                        <div class="metric-item">
                            <div class="metric-icon">📥</div>
                            <span class="metric-value">0</span>
                            <span class="metric-label">All</span>
                        </div>
                        <div class="metric-item">
                            <div class="metric-icon">🏧</div>
                            <span class="metric-value">0</span>
                            <span class="metric-label">ATM</span>
                        </div>
                        <div class="metric-item">
                            <div class="metric-icon">🏛️</div>
                            <span class="metric-value">0</span>
                            <span class="metric-label">Bank</span>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>
</body>
</html>
