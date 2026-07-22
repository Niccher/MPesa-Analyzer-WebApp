<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Android App — Mpesa Analyzer</title>
    <meta name="description" content="Download the Mpesa Analyzer Android app. Automatically syncs SMS, detects financial messages, and forwards them securely to the ML backend for classification.">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?= base_url('android-app') ?>">
    <link rel="icon" type="image/png" href="<?= base_url('favicon.png') ?>">
    <link rel="apple-touch-icon" href="<?= base_url('favicon.png') ?>">
    <meta property="og:title" content="Android App — Mpesa Analyzer">
    <meta property="og:description" content="Sync your SMS automatically. The companion app detects and forwards financial messages to the ML backend.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= base_url('android-app') ?>">

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
        .nav-link.active { position: relative; }
        .nav-link.active::after { content: ''; position: absolute; bottom: -2px; left: 0; right: 0; height: 2px; background: var(--primary); border-radius: 1px; }
        .btn-primary { background-color: var(--primary); border-color: var(--primary); padding: 0.7rem 1.8rem; font-weight: 600; border-radius: var(--radius); transition: all 0.3s; }
        .btn-primary:hover { background-color: var(--primary-dark); transform: translateY(-2px); box-shadow: 0 8px 24px rgba(93,95,239,0.35); }
        .btn-outline-primary { color: var(--primary); border-color: var(--primary); padding: 0.7rem 1.8rem; font-weight: 600; border-radius: var(--radius); }
        .btn-outline-primary:hover { background-color: var(--primary); color: #fff; transform: translateY(-2px); }
        .page-header { padding: 120px 0 60px; background: linear-gradient(135deg, #fff 0%, var(--secondary) 100%); }
        .glass-card { background: rgba(255,255,255,0.8); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.4); border-radius: var(--radius); padding: 2.5rem; transition: all 0.3s; height: 100%; box-shadow: 0 4px 16px rgba(0,0,0,0.04); }
        .glass-card:hover { transform: translateY(-6px); box-shadow: 0 12px 32px rgba(0,0,0,0.08); }
        .icon-box { width: 56px; height: 56px; background: var(--secondary); color: var(--primary); display: flex; align-items: center; justify-content: center; border-radius: var(--radius); font-size: 1.4rem; margin-bottom: 1.25rem; flex-shrink: 0; }
        .step-num { width: 40px; height: 40px; background: var(--primary); color: #fff; border-radius: var(--radius); display: inline-flex; align-items: center; justify-content: center; font-weight: 700; flex-shrink: 0; }
        .footer { background: var(--dark); color: #fff; padding: 4rem 0 2rem; }
        .footer a { color: rgba(255,255,255,0.7); text-decoration: none; transition: color 0.2s; }
        .footer a:hover { color: #fff; }
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
                    <li class="nav-item"><a class="nav-link active" href="<?= base_url('android-app') ?>">Android App</a></li>
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

    <section class="page-header">
        <div class="container">
            <div class="row align-items-center g-4">
                <div class="col-lg-6">
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2 mb-3 fw-semibold">
                        <i class="fa-brands fa-android me-1"></i> Companion App
                    </span>
                    <h1 class="fw-800 mb-3" style="font-size: 2.75rem; font-weight: 800; letter-spacing: -0.5px;">Android App</h1>
                    <p class="lead text-muted mb-4" style="line-height: 1.7;">
                        The Mpesa Analyzer Android app runs quietly on your phone, reading incoming SMS in real time. It uses on-device heuristics — and server-side ML — to identify financial messages, then encrypts and forwards them to the backend. Non-financial SMS are never transmitted.
                    </p>
                    <a href="#" class="btn btn-primary btn-lg px-4">
                        <i class="fa-brands fa-android me-2"></i>Download APK
                    </a>
                </div>
                <div class="col-lg-6">
                    <div class="glass-card text-center p-5">
                        <i class="fa-solid fa-mobile-screen-button fa-6x text-primary opacity-25 mb-3"></i>
                        <p class="text-muted small mb-0">Mpesa Analyzer Android Companion</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 bg-white">
        <div class="container py-4">
            <div class="text-center mb-5">
                <h2 class="fw-bold h1 mb-3">How the App Works</h2>
                <p class="text-muted mx-auto" style="max-width: 600px;">The Android app handles the critical first step — getting your financial data from your phone to the cloud.</p>
            </div>
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="glass-card text-center">
                        <div class="icon-box mx-auto"><i class="fa-solid fa-eye"></i></div>
                        <h5 class="fw-bold">1. Monitor SMS</h5>
                        <p class="text-muted small mb-0">The app registers an SMS receiver that listens for new messages. It runs in the background with minimal battery impact.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="glass-card text-center">
                        <div class="icon-box mx-auto"><i class="fa-solid fa-filter"></i></div>
                        <h5 class="fw-bold">2. Pre-Filter</h5>
                        <p class="text-muted small mb-0">On-device rules pre-screen incoming SMS. Messages from known financial senders (e.g., M-Pesa, banks) are flagged for upload.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="glass-card text-center">
                        <div class="icon-box mx-auto"><i class="fa-solid fa-lock"></i></div>
                        <h5 class="fw-bold">3. Encrypt & Send</h5>
                        <p class="text-muted small mb-0">Flagged SMS are encrypted using the device token and sent via HTTPS to the backend API at <code>/process/upload</code>.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="glass-card text-center">
                        <div class="icon-box mx-auto"><i class="fa-solid fa-check"></i></div>
                        <h5 class="fw-bold">4. Confirm & Sync</h5>
                        <p class="text-muted small mb-0">The backend acknowledges receipt. The app marks messages as synced to avoid duplicates. Status is visible on the dashboard.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 bg-light">
        <div class="container py-4">
            <div class="row g-4 align-items-center">
                <div class="col-lg-6">
                    <h2 class="fw-bold h1 mb-3">Privacy & Permissions</h2>
                    <p class="text-muted mb-4" style="line-height: 1.7;">We take your privacy seriously. The Android app requests only the permissions it needs, and we never access data beyond what's required for financial analysis.</p>
                    <div class="d-flex gap-3 mb-4">
                        <div class="icon-box" style="width: 48px; height: 48px; font-size: 1.1rem;"><i class="fa-solid fa-envelope"></i></div>
                        <div>
                            <h6 class="fw-bold mb-1">SMS Reading</h6>
                            <p class="text-muted small mb-0">Required to read incoming SMS. Only messages identified as financial are processed; all others are ignored.</p>
                        </div>
                    </div>
                    <div class="d-flex gap-3 mb-4">
                        <div class="icon-box" style="width: 48px; height: 48px; font-size: 1.1rem;"><i class="fa-solid fa-globe"></i></div>
                        <div>
                            <h6 class="fw-bold mb-1">Internet</h6>
                            <p class="text-muted small mb-0">Required to upload encrypted financial SMS to the backend. No data is shared with third parties.</p>
                        </div>
                    </div>
                    <div class="d-flex gap-3">
                        <div class="icon-box" style="width: 48px; height: 48px; font-size: 1.1rem;"><i class="fa-solid fa-battery-three-quarters"></i></div>
                        <div>
                            <h6 class="fw-bold mb-1">Background Operation</h6>
                            <p class="text-muted small mb-0">The app is optimized for low battery consumption. It suspends processing when the device is idle.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="glass-card">
                        <h5 class="fw-bold mb-3"><i class="fa-solid fa-list-check text-primary me-2"></i>App Features</h5>
                        <ul class="list-unstyled">
                            <li class="d-flex gap-2 mb-3"><i class="fa-solid fa-check-circle text-primary mt-1"></i><span><strong>Real-time sync</strong> — SMS are uploaded seconds after arrival</span></li>
                            <li class="d-flex gap-2 mb-3"><i class="fa-solid fa-check-circle text-primary mt-1"></i><span><strong>Offline queue</strong> — messages are stored locally if no network and sent when connectivity resumes</span></li>
                            <li class="d-flex gap-2 mb-3"><i class="fa-solid fa-check-circle text-primary mt-1"></i><span><strong>Duplicate prevention</strong> — each SMS is synced exactly once using a unique message ID</span></li>
                            <li class="d-flex gap-2 mb-3"><i class="fa-solid fa-check-circle text-primary mt-1"></i><span><strong>Device linking</strong> — connect multiple devices to one account via unique tokens</span></li>
                            <li class="d-flex gap-2 mb-3"><i class="fa-solid fa-check-circle text-primary mt-1"></i><span><strong>Bulk history upload</strong> — on first install, previously received SMS can be scanned and uploaded</span></li>
                            <li class="d-flex gap-2"><i class="fa-solid fa-check-circle text-primary mt-1"></i><span><strong>Dark mode</strong> — follows Android system theme</span></li>
                        </ul>
                        <a href="#" class="btn btn-primary w-100 mt-3"><i class="fa-brands fa-android me-2"></i>Download APK</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 bg-white">
        <div class="container py-4">
            <div class="text-center mb-5">
                <h2 class="fw-bold h1 mb-3">Technical Details</h2>
                <p class="text-muted mx-auto" style="max-width: 600px;">For developers and advanced users who want to understand the integration.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="glass-card">
                        <h5 class="fw-bold mb-3"><i class="fa-solid fa-code text-primary me-2"></i>API Endpoint</h5>
                        <p class="text-muted small mb-2">The app sends SMS data as <code>POST</code> requests to:</p>
                        <pre class="bg-light p-3 rounded-3 small mb-0"><code>POST <?= base_url('process/upload') ?></code></pre>
                        <p class="text-muted small mt-3 mb-0">The request body contains the encrypted SMS text, sender, timestamp, and the device token for authentication.</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="glass-card">
                        <h5 class="fw-bold mb-3"><i class="fa-solid fa-shield text-primary me-2"></i>Authentication</h5>
                        <p class="text-muted small mb-2">Each device is authenticated using an Access Token generated from the web dashboard (Info & Auth page).</p>
                        <ul class="text-muted small mb-0">
                            <li class="mb-1">Token is hashed with SHA-256 before storage</li>
                            <li class="mb-1">SMS owner is identified by the token hash</li>
                            <li class="mb-1">Multiple devices can share one account</li>
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
