<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Setup Guide — Mpesa Analyzer</title>
    <meta name="description" content="Complete setup guide for Mpesa Analyzer. Learn how to install the Android app, generate tokens, link devices, and run ML analysis. Step-by-step instructions.">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?= base_url('setup') ?>">
    <link rel="icon" type="image/png" href="<?= base_url('favicon.png') ?>">
    <link rel="apple-touch-icon" href="<?= base_url('favicon.png') ?>">
    <meta property="og:title" content="Setup Guide — Mpesa Analyzer">
    <meta property="og:description" content="Step-by-step guide to set up Mpesa Analyzer: Android app, device linking, ML analysis.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= base_url('setup') ?>">

    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root { --primary: #5D5FEF; --primary-dark: #4A4CD4; --secondary: #B1B8ED; --dark: #1A1A2E; --light: #F8F9FA; --radius: 4px; }
        body { font-family: 'Outfit', sans-serif; background-color: var(--light); color: var(--dark); }
        .navbar { padding: 1.25rem 0; background: transparent; transition: all 0.3s ease; }
        .navbar.scrolled { background: rgba(255,255,255,0.92); backdrop-filter: blur(12px); padding: 0.75rem 0; box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
        .navbar-brand { font-weight: 800; font-size: 1.5rem; letter-spacing: -0.5px; }
        .nav-link { font-weight: 500; color: var(--dark); transition: color 0.2s; }
        .nav-link:hover, .nav-link.active { color: var(--primary) !important; }
        .nav-link.active::after { content: ''; position: absolute; bottom: -2px; left: 0; right: 0; height: 2px; background: var(--primary); border-radius: 1px; }
        .btn-primary { background-color: var(--primary); border-color: var(--primary); padding: 0.7rem 1.8rem; font-weight: 600; border-radius: var(--radius); transition: all 0.3s; }
        .btn-primary:hover { background-color: var(--primary-dark); transform: translateY(-2px); box-shadow: 0 8px 24px rgba(93,95,239,0.35); }
        .btn-outline-primary { color: var(--primary); border-color: var(--primary); padding: 0.7rem 1.8rem; font-weight: 600; border-radius: var(--radius); }
        .btn-outline-primary:hover { background-color: var(--primary); color: #fff; transform: translateY(-2px); }
        .page-header { padding: 120px 0 60px; background: linear-gradient(135deg, #fff 0%, var(--secondary) 100%); }
        .glass-card { background: rgba(255,255,255,0.8); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.4); border-radius: var(--radius); padding: 2.5rem; transition: all 0.3s; box-shadow: 0 4px 16px rgba(0,0,0,0.04); }
        .glass-card:hover { transform: translateY(-6px); box-shadow: 0 12px 32px rgba(0,0,0,0.08); }
        .icon-box { width: 56px; height: 56px; background: var(--secondary); color: var(--primary); display: flex; align-items: center; justify-content: center; border-radius: var(--radius); font-size: 1.4rem; margin-bottom: 1.25rem; flex-shrink: 0; }
        .step-number { width: 44px; height: 44px; background: var(--primary); color: #fff; border-radius: var(--radius); display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.2rem; flex-shrink: 0; }
        .footer { background: var(--dark); color: #fff; padding: 4rem 0 2rem; }
        .footer a { color: rgba(255,255,255,0.7); text-decoration: none; transition: color 0.2s; }
        .footer a:hover { color: #fff; }
        code { background: #eef0ff; color: var(--primary); padding: 2px 6px; border-radius: 3px; font-size: 0.85em; }
        @media (max-width: 768px) { .page-header { padding: 100px 0 40px; } }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand text-primary" href="<?= base_url() ?>">
                <i class="fa-solid fa-wallet me-2"></i>Mpesa Analyzer
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link" href="<?= base_url() ?>">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('android-app') ?>">Android App</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('ml-backend') ?>">ML Backend</a></li>
                    <li class="nav-item"><a class="nav-link active" href="<?= base_url('setup') ?>">Setup</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('faq') ?>">FAQ</a></li>
                </ul>
                <div class="d-flex gap-2">
                    <a href="<?= url_to('login') ?>" class="btn btn-outline-primary btn-sm">Sign In</a>
                    <a href="<?= url_to('register') ?>" class="btn btn-primary btn-sm">Get Started</a>
                </div>
            </div>
        </div>
    </nav>

    <section class="page-header">
        <div class="container">
            <div class="text-center">
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2 mb-3 fw-semibold">
                    <i class="fa-solid fa-book-open me-1"></i> Full System Guide
                </span>
                <h1 class="fw-800 mb-3" style="font-size: 2.75rem; font-weight: 800; letter-spacing: -0.5px;">Setup Guide</h1>
                <p class="lead text-muted mx-auto mb-0" style="max-width: 600px;">Get the entire Mpesa Analyzer system running — from creating your account to viewing your first AI-classified transaction.</p>
            </div>
        </div>
    </section>

    <section class="py-5 bg-white">
        <div class="container py-4">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="glass-card p-4 p-lg-5">
                        <div class="d-flex gap-4 mb-5">
                            <span class="step-number">1</span>
                            <div>
                                <h4 class="fw-bold mb-2">Create Your Account</h4>
                                <p class="text-muted mb-3">The first step is to register on the Mpesa Analyzer web platform. This will be your central hub for managing devices, viewing analytics, and configuring settings.</p>
                                <ol class="text-muted small" style="line-height: 2;">
                                    <li>Navigate to <a href="<?= url_to('register') ?>" class="fw-semibold">the registration page</a></li>
                                    <li>Enter your <strong>email address</strong> — use a valid one for account recovery</li>
                                    <li>Choose a <strong>username</strong> that will identify you in the dashboard</li>
                                    <li>Create a <strong>strong password</strong> (at least 8 characters with mixed case and numbers)</li>
                                    <li>Confirm your password and submit the form</li>
                                    <li>Check your email for a verification link (if email activation is enabled)</li>
                                </ol>
                                <div class="bg-light p-3 rounded-3 small">
                                    <i class="fa-solid fa-lightbulb text-warning me-2"></i>
                                    <strong>Tip:</strong> Use the same email you'll access the dashboard from. You can link multiple devices to a single account.
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-4 mb-5">
                            <span class="step-number">2</span>
                            <div>
                                <h4 class="fw-bold mb-2">Install the Android App</h4>
                                <p class="text-muted mb-3">The Mpesa Analyzer Android companion app handles SMS collection on your phone. It runs in the background and automatically forwards financial SMS to the backend.</p>
                                <ol class="text-muted small" style="line-height: 2;">
                                    <li>Download the latest APK from the <a href="#" class="fw-semibold">downloads page</a></li>
                                    <li>On your Android phone, enable <strong>Install from Unknown Sources</strong> (Settings → Security)</li>
                                    <li>Open the downloaded APK file and tap <strong>Install</strong></li>
                                    <li>Grant the <strong>SMS permission</strong> when prompted — this is required to read M-Pesa messages</li>
                                    <li>Grant <strong>Notification access</strong> if you want real-time sync of new SMS</li>
                                    <li>Open the app and note the device identifier screen</li>
                                </ol>
                                <div class="bg-light p-3 rounded-3 small">
                                    <i class="fa-solid fa-lightbulb text-warning me-2"></i>
                                    <strong>Tip:</strong> The app requires Android 6.0 (API 23) or higher. It has been tested on Samsung, Tecno, Infinix, and Xiaomi devices.
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-4 mb-5">
                            <span class="step-number">3</span>
                            <div>
                                <h4 class="fw-bold mb-2">Generate an Access Token</h4>
                                <p class="text-muted mb-3">The access token securely links your Android app to your web account. It's generated from the dashboard and entered into the phone app.</p>
                                <ol class="text-muted small" style="line-height: 2;">
                                    <li>Log in to the web dashboard at <a href="<?= url_to('login') ?>" class="fw-semibold">/login</a></li>
                                    <li>Navigate to <strong>Info & Auth</strong> in the sidebar menu</li>
                                    <li>In the <strong>Access Tokens</strong> section, click <strong>Generate New Token</strong></li>
                                    <li>Give the token a name (e.g., "My Galaxy S23") to identify the device</li>
                                    <li><strong>Copy the token immediately</strong> — it will only be shown once for security</li>
                                    <li>The token is a long alphanumeric string — paste it somewhere safe temporarily</li>
                                </ol>
                                <div class="bg-danger bg-opacity-10 p-3 rounded-3 small text-danger">
                                    <i class="fa-solid fa-shield me-2"></i>
                                    <strong>Security Note:</strong> Never share your access token. It provides full access to your financial data. If compromised, revoke it immediately from the Info & Auth page.
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-4 mb-5">
                            <span class="step-number">4</span>
                            <div>
                                <h4 class="fw-bold mb-2">Link Your Device</h4>
                                <p class="text-muted mb-3">Connecting your phone to your account is done through the dashboard. This step authorizes the Android app to upload data under your account.</p>
                                <ol class="text-muted small" style="line-height: 2;">
                                    <li>From the dashboard home page, click the <strong>Link Device</strong> button</li>
                                    <li>In the modal that appears, paste the <strong>Device Token</strong> you copied in step 3</li>
                                    <li>Optionally enter a <strong>Device Name</strong> (e.g., "Galaxy S23") for easy identification</li>
                                    <li>Click <strong>Save Link</strong> — the device is now authorized</li>
                                    <li>On your Android app, enter the same token in the settings screen</li>
                                    <li>The app will confirm a successful connection with the backend</li>
                                </ol>
                                <div class="bg-light p-3 rounded-3 small">
                                    <i class="fa-solid fa-lightbulb text-warning me-2"></i>
                                    <strong>Tip:</strong> You can link multiple devices to one account. Each device token is unique. Use the device filter on the dashboard to view data per device.
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-4 mb-5">
                            <span class="step-number">5</span>
                            <div>
                                <h4 class="fw-bold mb-2">Synchronize SMS</h4>
                                <p class="text-muted mb-3">Once linked, the Android app will begin uploading SMS. On first install, it can scan historically received SMS. New messages are synced in real time.</p>
                                <ol class="text-muted small" style="line-height: 2;">
                                    <li>Ensure your phone has an active internet connection (Wi-Fi or mobile data)</li>
                                    <li>Open the Android app — it will show sync status and message count</li>
                                    <li>For first-time setup, tap <strong>Sync History</strong> to upload previously received M-Pesa SMS</li>
                                    <li>The app processes SMS in batches to minimize battery/data impact</li>
                                    <li>On the web dashboard, the <strong>Transactions</strong> page will begin populating</li>
                                    <li>You can monitor sync progress from the dashboard home screen</li>
                                </ol>
                            </div>
                        </div>

                        <div class="d-flex gap-4 mb-5">
                            <span class="step-number">6</span>
                            <div>
                                <h4 class="fw-bold mb-2">Run ML Analysis</h4>
                                <p class="text-muted mb-3">After SMS are uploaded, the ML backend needs to classify them. This step extracts structured transaction data from the raw SMS text.</p>
                                <ol class="text-muted small" style="line-height: 2;">
                                    <li>On the dashboard, click the <strong>Analyze</strong> button (the microchip icon)</li>
                                    <li>A progress badge appears in the top bar showing classification status</li>
                                    <li>Click the badge to open a detailed progress modal with counts</li>
                                    <li>The LLM processes each SMS one by one, extracting transaction details</li>
                                    <li>Processing time depends on volume — typically 1-3 seconds per SMS</li>
                                    <li>Once complete, your analytics, charts, and reports will be populated</li>
                                </ol>
                                <div class="bg-light p-3 rounded-3 small">
                                    <i class="fa-solid fa-lightbulb text-warning me-2"></i>
                                    <strong>Tip:</strong> The <strong>Rescan</strong> button processes only new/unprocessed SMS. The <strong>Full</strong> button clears all existing analysis and re-classifies everything from scratch.
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-4">
                            <span class="step-number">7</span>
                            <div>
                                <h4 class="fw-bold mb-2">Explore Your Dashboard</h4>
                                <p class="text-muted mb-3">With SMS uploaded and classified, the full dashboard becomes active. Here's what you can do:</p>
                                <div class="row g-2">
                                    <div class="col-sm-6">
                                        <div class="bg-light p-3 rounded-3 small">
                                            <strong><i class="fa-solid fa-house text-primary me-1"></i> Home</strong>
                                            <p class="mb-0 text-muted">Financial overview, balance, health score, top counterparties, and smart alerts.</p>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="bg-light p-3 rounded-3 small">
                                            <strong><i class="fa-solid fa-chart-pie text-primary me-1"></i> Analytics</strong>
                                            <p class="mb-0 text-muted">Interactive charts for spending, income, Fuliza usage, and category breakdowns.</p>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="bg-light p-3 rounded-3 small">
                                            <strong><i class="fa-solid fa-magnifying-glass text-primary me-1"></i> Search</strong>
                                            <p class="mb-0 text-muted">Full-text search across all transactions with filters by date, category, amount, and counterparty.</p>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="bg-light p-3 rounded-3 small">
                                            <strong><i class="fa-solid fa-bullseye text-primary me-1"></i> Budget</strong>
                                            <p class="mb-0 text-muted">Set monthly budgets per category and get alerts when approaching or exceeding limits.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 bg-white">
        <div class="container py-2 text-center">
            <div class="bg-primary p-5 rounded-4 shadow-lg text-white" style="border-radius: var(--radius) !important;">
                <h2 class="fw-bold mb-3">Ready to get started?</h2>
                <p class="mb-4 opacity-75 fs-5">Create your account and begin your financial intelligence journey.</p>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="<?= url_to('register') ?>" class="btn btn-light btn-lg px-5 text-primary fw-bold shadow-sm">
                        <i class="fa-solid fa-user-plus me-2"></i>Create Free Account
                    </a>
                    <a href="<?= url_to('login') ?>" class="btn btn-outline-light btn-lg px-5 fw-bold">
                        <i class="fa-solid fa-arrow-right-to-bracket me-2"></i>Sign In
                    </a>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-5">
                    <h4 class="text-white fw-bold mb-3"><i class="fa-solid fa-wallet me-2"></i>Mpesa Analyzer</h4>
                    <p class="opacity-75 small">AI-powered financial intelligence platform.</p>
                </div>
                <div class="col-md-2">
                    <h6 class="text-white fw-bold mb-3">Platform</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="<?= base_url('android-app') ?>">Android App</a></li>
                        <li class="mb-2"><a href="<?= base_url('ml-backend') ?>">ML Backend</a></li>
                        <li class="mb-2"><a href="<?= base_url('setup') ?>">Setup Guide</a></li>
                        <li class="mb-2"><a href="<?= base_url('faq') ?>">FAQ</a></li>
                    </ul>
                </div>
                <div class="col-md-2">
                    <h6 class="text-white fw-bold mb-3">Account</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="<?= url_to('login') ?>">Sign In</a></li>
                        <li class="mb-2"><a href="<?= url_to('register') ?>">Register</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6 class="text-white fw-bold mb-3">Tech Stack</h6>
                    <div class="d-flex flex-wrap gap-1 small opacity-75">
                        <span class="badge bg-light text-dark">CodeIgniter 4</span>
                        <span class="badge bg-light text-dark">FastAPI</span>
                        <span class="badge bg-light text-dark">LLM</span>
                        <span class="badge bg-light text-dark">MySQL</span>
                        <span class="badge bg-light text-dark">Bootstrap 5</span>
                        <span class="badge bg-light text-dark">Docker</span>
                    </div>
                </div>
            </div>
            <hr class="mt-4 mb-4 opacity-25">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start"><p class="mb-0 opacity-75 small">&copy; <?= date('Y') ?> Mpesa Analyzer. All rights reserved.</p></div>
                <div class="col-md-6 text-center text-md-end mt-2 mt-md-0"><p class="mb-0 opacity-75 small">Built with <i class="fa-solid fa-heart text-danger"></i> for financial freedom</p></div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.addEventListener('scroll', function() { document.querySelector('.navbar').classList.toggle('scrolled', window.scrollY > 50); });
    </script>
</body>
</html>
