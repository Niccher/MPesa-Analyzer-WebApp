<?php
$versionData = [];
if (file_exists(APPPATH . 'Config/version.json')) {
    $versionData = json_decode(file_get_contents(APPPATH . 'Config/version.json'), true);
}
$systemVersion = $versionData['version'] ?? '3.2.0';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Mpesa Analyzer — AI-Powered SMS Financial Intelligence Platform</title>
    <meta name="description" content="Mpesa Analyzer automatically syncs all your SMS, uses ML to detect finance-related messages, classifies M-Pesa transactions, and visualizes your spending. Privacy-first financial intelligence.">
    <meta name="keywords" content="Mpesa analyzer, M-Pesa transaction analyzer, SMS financial intelligence, ML SMS classification, spend tracker Kenya, mobile money analysis">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?= base_url() ?>">
    <link rel="icon" type="image/png" href="<?= base_url('favicon.png') ?>">
    <link rel="apple-touch-icon" href="<?= base_url('favicon.png') ?>">
    <meta property="og:title" content="Mpesa Analyzer — AI-Powered SMS Financial Intelligence">
    <meta property="og:description" content="Sync all SMS, ML detects financial messages, classifies transactions, visualizes spending.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= base_url() ?>">

    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --primary: #5D5FEF;
            --primary-dark: #4A4CD4;
            --secondary: #B1B8ED;
            --dark: #1A1A2E;
            --light: #F8F9FA;
            --radius: 4px;
        }

        body { font-family: 'Outfit', sans-serif; background-color: var(--light); color: var(--dark); }

        .navbar { padding: 1.25rem 0; background: transparent; transition: all 0.3s ease; }
        .navbar.scrolled { background: rgba(255,255,255,0.92); backdrop-filter: blur(12px); padding: 0.75rem 0; box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
        .navbar-brand { font-weight: 800; font-size: 1.5rem; letter-spacing: -0.5px; }

        .nav-link { font-weight: 500; color: var(--dark); transition: color 0.2s; position: relative; }
        .nav-link:hover { color: var(--primary); }
        .nav-link.active { color: var(--primary) !important; }
        .nav-link.active::after { content: ''; position: absolute; bottom: -2px; left: 0; right: 0; height: 2px; background: var(--primary); border-radius: 1px; }

        .btn-primary { background-color: var(--primary); border-color: var(--primary); padding: 0.7rem 1.8rem; font-weight: 600; border-radius: var(--radius); transition: all 0.3s; }
        .btn-primary:hover { background-color: var(--primary-dark); transform: translateY(-2px); box-shadow: 0 8px 24px rgba(93,95,239,0.35); }
        .btn-outline-primary { color: var(--primary); border-color: var(--primary); padding: 0.7rem 1.8rem; font-weight: 600; border-radius: var(--radius); }
        .btn-outline-primary:hover { background-color: var(--primary); color: #fff; transform: translateY(-2px); }

        .hero-section { padding: 140px 0 100px; background: linear-gradient(135deg, #fff 0%, var(--secondary) 100%); position: relative; overflow: hidden; }
        .hero-section::before { content: ''; position: absolute; top: -50%; right: -20%; width: 600px; height: 600px; background: radial-gradient(circle, rgba(93,95,239,0.06) 0%, transparent 70%); border-radius: 50%; }
        .hero-title { font-size: 3.25rem; font-weight: 800; line-height: 1.15; margin-bottom: 1.5rem; letter-spacing: -1px; background: linear-gradient(135deg, var(--primary), #8E91FF); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .hero-img-placeholder { background: rgba(255,255,255,0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.5); border-radius: var(--radius); display: flex; align-items: center; justify-content: center; height: 420px; }

        .section-title { font-weight: 700; letter-spacing: -0.3px; }
        .glass-card { background: rgba(255,255,255,0.8); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.4); border-radius: var(--radius); padding: 2.5rem; transition: all 0.3s; height: 100%; box-shadow: 0 4px 16px rgba(0,0,0,0.04); }
        .glass-card:hover { transform: translateY(-6px); box-shadow: 0 12px 32px rgba(0,0,0,0.08); }
        .icon-box { width: 56px; height: 56px; background: var(--secondary); color: var(--primary); display: flex; align-items: center; justify-content: center; border-radius: var(--radius); font-size: 1.4rem; margin-bottom: 1.25rem; }

        .footer { background: var(--dark); color: #fff; padding: 4rem 0 2rem; }
        .footer a { color: rgba(255,255,255,0.7); text-decoration: none; transition: color 0.2s; }
        .footer a:hover { color: #fff; }

        @media (max-width: 768px) { .hero-title { font-size: 2.25rem; } .hero-section { padding: 100px 0 60px; } }
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
                    <li class="nav-item"><a class="nav-link active" href="<?= base_url() ?>">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('android-app') ?>">Android App</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('ml-backend') ?>">ML Backend</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('setup') ?>">Setup</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('faq') ?>">FAQ</a></li>
                </ul>
                <div class="d-flex gap-2">
                    <a href="<?= url_to('login') ?>" class="btn btn-outline-primary btn-sm">Sign In</a>
                    <a href="<?= url_to('register') ?>" class="btn btn-primary btn-sm">Get Started</a>
                </div>
            </div>
        </div>
    </nav>

    <header class="hero-section">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2 mb-3 fw-semibold">
                        <i class="fa-solid fa-microchip me-1"></i> ML-Powered SMS Intelligence
                    </span>
                    <h1 class="hero-title">Intelligence From Every SMS You Receive</h1>
                    <p class="lead mb-4 text-muted" style="font-size: 1.15rem; line-height: 1.7;">
                        Mpesa Analyzer syncs <strong>all your phone SMS</strong> — not just M-Pesa messages. Our ML engine automatically <strong>detects which senders are finance-related</strong>, classifies transactions (sent, received, airtime, Fuliza, M-Shwari, utilities), and visualizes your complete financial picture. Every message is kept so no transaction is ever missed — finance messages feed your analytics, and the rest can be reviewed or deleted anytime. This approach means you never miss a transaction, even if Safaricom changes their SMS format.
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="<?= url_to('register') ?>" class="btn btn-primary btn-lg px-4">
                            <i class="fa-solid fa-rocket me-2"></i>Start Free
                        </a>
                        <a href="<?= base_url('setup') ?>" class="btn btn-outline-primary btn-lg px-4">
                            <i class="fa-solid fa-book me-2"></i>Setup Guide
                        </a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hero-img-placeholder">
                        <div class="text-center">
                            <i class="fa-solid fa-chart-line fa-6x text-primary opacity-25 mb-3"></i>
                            <p class="text-muted small mb-0">Interactive Analytics Dashboard</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <section class="py-5 bg-white">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="section-title h1 mb-3">Why Analyze All SMS?</h2>
                <p class="text-muted mx-auto" style="max-width: 650px;">Most financial trackers rely on manual entry or parsing specific SMS formats. Mpesa Analyzer takes a smarter approach.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="glass-card">
                        <div class="icon-box"><i class="fa-solid fa-brain"></i></div>
                        <h5 class="fw-bold">ML-Enhanced Detection</h5>
                        <p class="text-muted small mb-0">Instead of brittle regex patterns that break when Safaricom updates SMS templates, our ML model learns the <em>semantic meaning</em> of messages. It reliably identifies financial content even when the format changes.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="glass-card">
                        <div class="icon-box"><i class="fa-solid fa-shield-halved"></i></div>
                        <h5 class="fw-bold">Privacy By Design</h5>
                        <p class="text-muted small mb-0">Your data stays in your own Docker deployment. Finance-related SMS are processed and stored; non-financial messages are flagged separately so you can review or delete them anytime from Data Management — keeping only what you want.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="glass-card">
                        <div class="icon-box"><i class="fa-solid fa-gauge-high"></i></div>
                        <h5 class="fw-bold">Future-Proof Coverage</h5>
                        <p class="text-muted small mb-0">As mobile money evolves and new financial services emerge, the ML model adapts. No need to manually update parsing rules. When a new transaction type appears, the LLM recognizes it based on context.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 bg-light">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="section-title h1 mb-3">Three Components, One Platform</h2>
                <p class="text-muted mx-auto" style="max-width: 600px;">Seamlessly connected to give you real-time financial intelligence.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <a href="<?= base_url('android-app') ?>" class="text-decoration-none">
                        <div class="glass-card">
                            <div class="icon-box"><i class="fa-solid fa-mobile-screen-button"></i></div>
                            <h4 class="fw-bold text-dark mb-2">Android App</h4>
                            <p class="text-muted small mb-0">Installs on your phone, reads SMS, encrypts, and forwards financial messages to the backend. Battery-efficient and privacy-conscious.</p>
                            <span class="text-primary fw-semibold small mt-3 d-inline-block">Learn more <i class="fa-solid fa-arrow-right ms-1"></i></span>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="<?= base_url('ml-backend') ?>" class="text-decoration-none">
                        <div class="glass-card">
                            <div class="icon-box"><i class="fa-solid fa-microchip"></i></div>
                            <h4 class="fw-bold text-dark mb-2">ML Backend</h4>
                            <p class="text-muted small mb-0">FastAPI service running a local LLM. Classifies SMS senders, extracts counterparty, amount, date, and tracks every ML job with full metadata.</p>
                            <span class="text-primary fw-semibold small mt-3 d-inline-block">Learn more <i class="fa-solid fa-arrow-right ms-1"></i></span>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="<?= url_to('register') ?>" class="text-decoration-none">
                        <div class="glass-card">
                            <div class="icon-box"><i class="fa-solid fa-display"></i></div>
                            <h4 class="fw-bold text-dark mb-2">Web Dashboard</h4>
                            <p class="text-muted small mb-0">Interactive charts, transaction search, budget tracking, and printable reports. Access your financial data from any browser.</p>
                            <span class="text-primary fw-semibold small mt-3 d-inline-block">Get started <i class="fa-solid fa-arrow-right ms-1"></i></span>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 bg-white">
        <div class="container py-5 text-center">
            <div class="bg-primary p-5 rounded-4 shadow-lg text-white" style="border-radius: var(--radius) !important;">
                <h2 class="fw-bold mb-3">Ready to take control of your finances?</h2>
                <p class="mb-4 opacity-75 fs-5">Join users who manage their M-Pesa finances smarter with AI-powered analysis.</p>
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
                    <p class="opacity-75 small">AI-powered financial intelligence platform. Android app, ML classification, and interactive web dashboard.</p>
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
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0 opacity-75 small">&copy; <?= date('Y') ?> Mpesa Analyzer v<?= esc($systemVersion) ?>. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end mt-2 mt-md-0">
                    <p class="mb-0 opacity-75 small">Built with <i class="fa-solid fa-heart text-danger"></i> for financial freedom</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.addEventListener('scroll', function() { document.querySelector('.navbar').classList.toggle('scrolled', window.scrollY > 50); });
        document.querySelectorAll('.nav-link').forEach(function(link) { link.addEventListener('click', function() { document.querySelectorAll('.nav-link').forEach(function(l) { l.classList.remove('active'); }); this.classList.add('active'); }); });
    </script>
</body>
</html>
