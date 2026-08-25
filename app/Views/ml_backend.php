<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>ML Backend — Mpesa Analyzer</title>
    <meta name="description" content="The Mpesa Analyzer ML backend uses a local LLM to classify SMS senders and extract transactions. FastAPI service with model, prompt and job management.">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?= base_url('ml-backend') ?>">
    <link rel="icon" type="image/png" href="<?= base_url('favicon.png') ?>">
    <link rel="apple-touch-icon" href="<?= base_url('favicon.png') ?>">
    <meta property="og:title" content="ML Backend — Mpesa Analyzer">
    <meta property="og:description" content="Local LLM classifies M-Pesa SMS senders and extracts transactions. FastAPI service at :9050.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= base_url('ml-backend') ?>">

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
        .glass-card { background: rgba(255,255,255,0.8); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.4); border-radius: var(--radius); padding: 2.5rem; transition: all 0.3s; height: 100%; box-shadow: 0 4px 16px rgba(0,0,0,0.04); }
        .glass-card:hover { transform: translateY(-6px); box-shadow: 0 12px 32px rgba(0,0,0,0.08); }
        .icon-box { width: 56px; height: 56px; background: var(--secondary); color: var(--primary); display: flex; align-items: center; justify-content: center; border-radius: var(--radius); font-size: 1.4rem; margin-bottom: 1.25rem; flex-shrink: 0; }
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
                    <li class="nav-item"><a class="nav-link active" href="<?= base_url('ml-backend') ?>">ML Backend</a></li>
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

    <section class="page-header">
        <div class="container">
            <div class="row align-items-center g-4">
                <div class="col-lg-6">
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2 mb-3 fw-semibold">
                        <i class="fa-solid fa-microchip me-1"></i> AI Engine
                    </span>
                    <h1 class="fw-800 mb-3" style="font-size: 2.75rem; font-weight: 800; letter-spacing: -0.5px;">ML Backend</h1>
                    <p class="lead text-muted mb-4" style="line-height: 1.7;">
                        The intelligence layer of Mpesa Analyzer. A FastAPI microservice running a local Large Language Model (Qwen2.5 1.5B via llama.cpp) that reads raw SMS text, determines if a sender is finance-related, and extracts structured transaction data — all without rigid parsing rules.
                    </p>
                    <div class="d-flex gap-2 flex-wrap">
                        <span class="badge bg-light text-dark border px-3 py-2"><i class="fa-brands fa-python me-1"></i>FastAPI</span>
                        <span class="badge bg-light text-dark border px-3 py-2"><i class="fa-solid fa-brain me-1"></i>Local LLM</span>
                        <span class="badge bg-light text-dark border px-3 py-2"><i class="fa-solid fa-database me-1"></i>MySQL</span>
                        <span class="badge bg-light text-dark border px-3 py-2"><i class="fa-brands fa-docker me-1"></i>Docker</span>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="glass-card text-center p-5">
                        <i class="fa-solid fa-microchip fa-6x text-primary opacity-25 mb-3"></i>
                        <p class="text-muted small mb-0">FastAPI LLM Inference Service on <code>:9050</code></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 bg-white">
        <div class="container py-4">
            <div class="text-center mb-5">
                <h2 class="fw-bold h1 mb-3">Why an LLM Instead of Regex?</h2>
                <p class="text-muted mx-auto" style="max-width: 650px;">Traditional parsers struggle when SMS formats change. An LLM understands meaning, not just patterns.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="glass-card">
                        <div class="d-flex gap-3 mb-3">
                            <div class="icon-box" style="width: 48px; height: 48px; font-size: 1.1rem;"><i class="fa-solid fa-wrench"></i></div>
                            <div>
                                <h5 class="fw-bold">Regex Approach (Traditional)</h5>
                                <ul class="text-muted small mb-0 mt-2">
                                    <li class="mb-1">Requires manual patterns for each SMS template</li>
                                    <li class="mb-1">Breaks when Safaricom updates their format</li>
                                    <li class="mb-1">Cannot detect new transaction types</li>
                                    <li class="mb-1">Hard to maintain across different senders</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="glass-card">
                        <div class="d-flex gap-3 mb-3">
                            <div class="icon-box" style="width: 48px; height: 48px; font-size: 1.1rem;"><i class="fa-solid fa-brain"></i></div>
                            <div>
                                <h5 class="fw-bold">LLM Approach (Mpesa Analyzer)</h5>
                                <ul class="text-muted small mb-0 mt-2">
                                    <li class="mb-1">Understands semantic meaning, not just text patterns</li>
                                    <li class="mb-1">Adapts to format changes automatically</li>
                                    <li class="mb-1">Detects new and emerging transaction types</li>
                                    <li class="mb-1">Single model handles all financial SMS sources</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 bg-light">
        <div class="container py-4">
            <div class="text-center mb-5">
                <h2 class="fw-bold h1 mb-3">Classification Pipeline</h2>
                <p class="text-muted mx-auto" style="max-width: 600px;">How a raw SMS becomes a structured transaction in your dashboard.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-3 col-6">
                    <div class="glass-card text-center">
                        <div class="icon-box mx-auto"><i class="fa-solid fa-inbox"></i></div>
                        <h6 class="fw-bold">1. Raw SMS</h6>
                        <p class="text-muted small mb-0">"Ksh 500.00 sent to John Doe on 15/1/2026 at 10:30..."</p>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="glass-card text-center">
                        <div class="icon-box mx-auto"><i class="fa-solid fa-filter-circle-xmark"></i></div>
                        <h6 class="fw-bold">2. Sender Classification</h6>
                        <p class="text-muted small mb-0">Known finance senders (M-Pesa, banks, SACCOs…) are recognised instantly; unknown senders are classified by the LLM.</p>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="glass-card text-center">
                        <div class="icon-box mx-auto"><i class="fa-solid fa-cubes"></i></div>
                        <h6 class="fw-bold">3. Extraction</h6>
                        <p class="text-muted small mb-0">Amount, counterparty, category, timestamp, new balance, transaction code extracted from finance SMS.</p>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="glass-card text-center">
                        <div class="icon-box mx-auto"><i class="fa-solid fa-chart-simple"></i></div>
                        <h6 class="fw-bold">4. Canonical Storage</h6>
                        <p class="text-muted small mb-0">One record per SMS in tbl_Sms carries both classification and parsed fields. Dashboards query it instantly.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 bg-white">
        <div class="container py-4">
            <div class="row g-4">
                <div class="col-lg-6">
                    <h2 class="fw-bold h1 mb-4">What the ML Extracts</h2>
                    <div class="glass-card">
                        <div class="d-flex gap-3 mb-3">
                            <div class="icon-box" style="width: 48px; height: 48px; font-size: 1.1rem;"><i class="fa-solid fa-tag"></i></div>
                            <div><h6 class="fw-bold">Transaction Category</h6><p class="text-muted small mb-0">Send money, receive, airtime, data bundles, Fuliza, M-Shwari, KCB M-Pesa, utilities, bill payments, till number, paybill, withdrawal, deposit, savings, and more.</p></div>
                        </div>
                        <div class="d-flex gap-3 mb-3">
                            <div class="icon-box" style="width: 48px; height: 48px; font-size: 1.1rem;"><i class="fa-solid fa-user"></i></div>
                            <div><h6 class="fw-bold">Counterparty</h6><p class="text-muted small mb-0">The person, business, or organization on the other side of the transaction. Extracted from the SMS context.</p></div>
                        </div>
                        <div class="d-flex gap-3 mb-3">
                            <div class="icon-box" style="width: 48px; height: 48px; font-size: 1.1rem;"><i class="fa-solid fa-coins"></i></div>
                            <div><h6 class="fw-bold">Amount & Balance</h6><p class="text-muted small mb-0">Transaction amount, whether it's a debit or credit, and the resulting account balance after the transaction.</p></div>
                        </div>
                        <div class="d-flex gap-3">
                            <div class="icon-box" style="width: 48px; height: 48px; font-size: 1.1rem;"><i class="fa-solid fa-calendar"></i></div>
                            <div><h6 class="fw-bold">Date & Time</h6><p class="text-muted small mb-0">Transaction timestamp extracted and normalized for consistent reporting and chronological analysis.</p></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <h2 class="fw-bold h1 mb-4">Smart Alerts & Insights</h2>
                    <div class="glass-card">
                        <p class="text-muted small mb-3">Beyond classification, the ML backend analyzes patterns and generates proactive alerts:</p>
                        <div class="d-flex gap-3 mb-3">
                            <div class="icon-box" style="width: 48px; height: 48px; font-size: 1.1rem; background: #fee2e2; color: #dc2626;"><i class="fa-solid fa-triangle-exclamation"></i></div>
                            <div><h6 class="fw-bold">Low Balance Warning</h6><p class="text-muted small mb-0">Notifies you when your M-Pesa balance drops below a configurable threshold.</p></div>
                        </div>
                        <div class="d-flex gap-3 mb-3">
                            <div class="icon-box" style="width: 48px; height: 48px; font-size: 1.1rem; background: #fef3c7; color: #d97706;"><i class="fa-solid fa-shield-halved"></i></div>
                            <div><h6 class="fw-bold">Unusual Activity</h6><p class="text-muted small mb-0">Flags transactions that deviate from your typical spending patterns, amount ranges, or counterparties.</p></div>
                        </div>
                        <div class="d-flex gap-3 mb-3">
                            <div class="icon-box" style="width: 48px; height: 48px; font-size: 1.1rem; background: #dbeafe; color: #2563eb;"><i class="fa-solid fa-percent"></i></div>
                            <div><h6 class="fw-bold">Fuliza Utilization</h6><p class="text-muted small mb-0">Tracks Fuliza overdraft usage and remaining limit, alerting you when utilization is high.</p></div>
                        </div>
                        <div class="d-flex gap-3">
                            <div class="icon-box" style="width: 48px; height: 48px; font-size: 1.1rem; background: #d1fae5; color: #059669;"><i class="fa-solid fa-heart-pulse"></i></div>
                            <div><h6 class="fw-bold">Financial Health Score</h6><p class="text-muted small mb-0">A composite score based on 60 days of transaction history, considering income stability, spending patterns, and Fuliza dependency.</p></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 bg-light">
        <div class="container py-4">
            <div class="text-center mb-5">
                <h2 class="fw-bold h1 mb-3">Managed ML Service</h2>
                <p class="text-muted mx-auto" style="max-width: 650px;">Admins can operate the backend end-to-end from the web console — no SSH or manual config required.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="glass-card">
                        <h5 class="fw-bold mb-3"><i class="fa-solid fa-play text-primary me-2"></i>Automatic Processing</h5>
                        <ul class="text-muted small mb-0">
                            <li class="mb-1">A background poller automatically processes new SMS on a configurable interval</li>
                            <li class="mb-1">Admins can <strong>Start / Stop Auto Jobs</strong> — when stopped, no ML jobs run until re-enabled</li>
                            <li class="mb-1">Jobs can also be triggered on demand per user (Rescan / Analyze)</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="glass-card">
                        <h5 class="fw-bold mb-3"><i class="fa-solid fa-box-open text-primary me-2"></i>Model Management</h5>
                        <ul class="text-muted small mb-0">
                            <li class="mb-1">Upload, activate and delete GGUF models from the web console</li>
                            <li class="mb-1">Model metadata (parameters, quantization, context length, architecture) is read automatically from the file</li>
                            <li class="mb-1">Runtime tuning: context size, prompt batch, GPU layers, temperature — applied on restart</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="glass-card">
                        <h5 class="fw-bold mb-3"><i class="fa-solid fa-file-lines text-primary me-2"></i>Prompt Management</h5>
                        <ul class="text-muted small mb-0">
                            <li class="mb-1">Classification and extraction prompts are editable and versioned</li>
                            <li class="mb-1">Saving a prompt creates a new version and makes it active</li>
                            <li class="mb-1">The hardcoded prompt stays the fallback whenever no DB version exists</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="glass-card">
                        <h5 class="fw-bold mb-3"><i class="fa-solid fa-list-check text-primary me-2"></i>Job Metadata & Audit</h5>
                        <ul class="text-muted small mb-0">
                            <li class="mb-1">Every ML job records rich metadata: all / good / bad / skipped SMS, sender breakdowns, model &amp; LLM tuning, duration, errors</li>
                            <li class="mb-1">Users see their job runs in the ML Jobs tab of the History page with a per-run summary modal</li>
                            <li class="mb-1">Admins monitor all jobs with aggregate stats from the ML Jobs console</li>
                        </ul>
                    </div>
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
