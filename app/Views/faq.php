<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>FAQ — Mpesa Analyzer</title>
    <meta name="description" content="Frequently asked questions about Mpesa Analyzer. Covers Android app setup, ML classification, privacy, device linking, and troubleshooting.">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?= base_url('faq') ?>">
    <link rel="icon" type="image/png" href="<?= base_url('favicon.png') ?>">
    <link rel="apple-touch-icon" href="<?= base_url('favicon.png') ?>">
    <meta property="og:title" content="FAQ — Mpesa Analyzer">
    <meta property="og:description" content="Frequently asked questions about setup, privacy, ML classification, and troubleshooting.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= base_url('faq') ?>">

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
        .glass-card { background: rgba(255,255,255,0.8); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.4); border-radius: var(--radius); padding: 2rem; transition: all 0.3s; box-shadow: 0 4px 16px rgba(0,0,0,0.04); }
        .glass-card:hover { transform: translateY(-3px); box-shadow: 0 12px 32px rgba(0,0,0,0.08); }
        .faq-question { cursor: pointer; user-select: none; }
        .faq-question:hover { color: var(--primary); }
        .faq-question .icon { transition: transform 0.3s; }
        .faq-question[aria-expanded="true"] .icon { transform: rotate(45deg); }
        .faq-answer { font-size: 0.95rem; line-height: 1.7; }
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
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('setup') ?>">Setup</a></li>
                    <li class="nav-item"><a class="nav-link active" href="<?= base_url('faq') ?>">FAQ</a></li>
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
                    <i class="fa-solid fa-circle-question me-1"></i> Got Questions?
                </span>
                <h1 class="fw-800 mb-3" style="font-size: 2.75rem; font-weight: 800; letter-spacing: -0.5px;">Frequently Asked Questions</h1>
                <p class="lead text-muted mx-auto mb-0" style="max-width: 550px;">Everything you need to know about Mpesa Analyzer, from setup to troubleshooting.</p>
            </div>
        </div>
    </section>

    <section class="py-5 bg-white">
        <div class="container py-4">
            <div class="row justify-content-center">
                <div class="col-lg-8">

                    <h4 class="fw-bold mb-4 text-primary"><i class="fa-solid fa-rocket me-2"></i>Getting Started</h4>

                    <div class="glass-card p-4 mb-3">
                        <div class="faq-question d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#faq1" aria-expanded="false">
                            <h5 class="fw-bold mb-0">What is Mpesa Analyzer?</h5>
                            <span class="icon fs-4 text-primary"><i class="fa-solid fa-circle-plus"></i></span>
                        </div>
                        <div class="collapse" id="faq1">
                            <div class="faq-answer text-muted pt-3 border-top mt-3">
                                Mpesa Analyzer is an AI-powered financial intelligence platform that automatically syncs SMS from your Android phone, uses a local LLM (Large Language Model) to detect and classify financial transactions, and visualizes your spending through an interactive web dashboard. It consists of three components: an Android companion app, a FastAPI ML backend, and a CodeIgniter 4 web dashboard.
                            </div>
                        </div>
                    </div>

                    <div class="glass-card p-4 mb-3">
                        <div class="faq-question d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#faq2" aria-expanded="false">
                            <h5 class="fw-bold mb-0">Do I need an Android phone?</h5>
                            <span class="icon fs-4 text-primary"><i class="fa-solid fa-circle-plus"></i></span>
                        </div>
                        <div class="collapse" id="faq2">
                            <div class="faq-answer text-muted pt-3 border-top mt-3">
                                Yes, currently the SMS collection requires the Android companion app because it needs direct access to incoming SMS. iOS restrictions prevent reading SMS system-wide. However, the web dashboard works on any device with a browser — you can view your analytics on iPhone, iPad, or desktop after the data has been synced.
                            </div>
                        </div>
                    </div>

                    <div class="glass-card p-4 mb-4">
                        <div class="faq-question d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#faq3" aria-expanded="false">
                            <h5 class="fw-bold mb-0">Is it free to use?</h5>
                            <span class="icon fs-4 text-primary"><i class="fa-solid fa-circle-plus"></i></span>
                        </div>
                        <div class="collapse" id="faq3">
                            <div class="faq-answer text-muted pt-3 border-top mt-3">
                                The platform is available for use within your own Docker deployment. There are no usage limits or subscription fees. You run the entire stack (web app, ML backend, database) on your own infrastructure using the provided Docker Compose configuration.
                            </div>
                        </div>
                    </div>

                    <h4 class="fw-bold mb-4 mt-5 text-primary"><i class="fa-solid fa-mobile-screen me-2"></i>Android App</h4>

                    <div class="glass-card p-4 mb-3">
                        <div class="faq-question d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#faq4" aria-expanded="false">
                            <h5 class="fw-bold mb-0">What permissions does the app need?</h5>
                            <span class="icon fs-4 text-primary"><i class="fa-solid fa-circle-plus"></i></span>
                        </div>
                        <div class="collapse" id="faq4">
                            <div class="faq-answer text-muted pt-3 border-top mt-3">
                                The app requires two permissions: <strong>SMS reading</strong> (to access M-Pesa messages) and <strong>Internet access</strong> (to upload encrypted data to the backend). Optionally, <strong>Notification access</strong> can be enabled for real-time sync of new SMS. Once uploaded, the ML flags each sender as finance or non-finance, and you control what is kept or deleted.
                            </div>
                        </div>
                    </div>

                    <div class="glass-card p-4 mb-3">
                        <div class="faq-question d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#faq5" aria-expanded="false">
                            <h5 class="fw-bold mb-0">Does the app drain my battery?</h5>
                            <span class="icon fs-4 text-primary"><i class="fa-solid fa-circle-plus"></i></span>
                        </div>
                        <div class="collapse" id="faq5">
                            <div class="faq-answer text-muted pt-3 border-top mt-3">
                                The app is designed to be battery-efficient. It uses Android's background execution limits and suspends processing when the device is idle. SMS are uploaded in small batches. Typical battery impact is less than 2% per day based on user reports.
                            </div>
                        </div>
                    </div>

                    <div class="glass-card p-4 mb-4">
                        <div class="faq-question d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#faq6" aria-expanded="false">
                            <h5 class="fw-bold mb-0">Can I use multiple phones with one account?</h5>
                            <span class="icon fs-4 text-primary"><i class="fa-solid fa-circle-plus"></i></span>
                        </div>
                        <div class="collapse" id="faq6">
                            <div class="faq-answer text-muted pt-3 border-top mt-3">
                                Yes. Generate a unique Access Token for each device from the Info & Auth page, then link each device separately. The dashboard includes a device filter to view data per phone. This is useful if you use multiple SIM cards or want to separate personal and business finances.
                            </div>
                        </div>
                    </div>

                    <h4 class="fw-bold mb-4 mt-5 text-primary"><i class="fa-solid fa-microchip me-2"></i>ML Classification</h4>

                    <div class="glass-card p-4 mb-3">
                        <div class="faq-question d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#faq7" aria-expanded="false">
                            <h5 class="fw-bold mb-0">How accurate is the ML classification?</h5>
                            <span class="icon fs-4 text-primary"><i class="fa-solid fa-circle-plus"></i></span>
                        </div>
                        <div class="collapse" id="faq7">
                            <div class="faq-answer text-muted pt-3 border-top mt-3">
                                The local LLM achieves high accuracy on common transaction types (send, receive, airtime, Fuliza, M-Shwari, KCB M-Pesa). Known finance senders are recognised instantly from a curated list; unknown senders are classified by the LLM. For edge cases or unusual SMS formats, accuracy may vary.
                            </div>
                        </div>
                    </div>

                    <div class="glass-card p-4 mb-3">
                        <div class="faq-question d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#faq8" aria-expanded="false">
                            <h5 class="fw-bold mb-0">What happens to non-financial SMS?</h5>
                            <span class="icon fs-4 text-primary"><i class="fa-solid fa-circle-plus"></i></span>
                        </div>
                        <div class="collapse" id="faq8">
                            <div class="faq-answer text-muted pt-3 border-top mt-3">
                                Every SMS you sync is kept so no transaction is ever missed. The ML engine flags each sender as finance-related or not: <strong>good SMS</strong> (finance senders) feed your analytics, while <strong>bad SMS</strong> (non-finance senders) are stored separately. You can review them on the dashboard and permanently delete all non-finance SMS from <strong>Data Management</strong> (type DELETE to confirm) while keeping your finance data intact.
                            </div>
                        </div>
                    </div>

                    <div class="glass-card p-4 mb-4">
                        <div class="faq-question d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#faq9" aria-expanded="false">
                            <h5 class="fw-bold mb-0">Why use an LLM instead of regex patterns?</h5>
                            <span class="icon fs-4 text-primary"><i class="fa-solid fa-circle-plus"></i></span>
                        </div>
                        <div class="collapse" id="faq9">
                            <div class="faq-answer text-muted pt-3 border-top mt-3">
                                Regex-based parsers are brittle — they break when Safaricom changes SMS templates, and they require manual updates for each new transaction type. An LLM understands the <em>semantic meaning</em> of text, so it adapts to format changes automatically and can recognize new transaction types without rule updates. This makes the system more maintainable and future-proof.
                            </div>
                        </div>
                    </div>

                    <h4 class="fw-bold mb-4 mt-5 text-primary"><i class="fa-solid fa-lock me-2"></i>Privacy & Security</h4>

                    <div class="glass-card p-4 mb-3">
                        <div class="faq-question d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#faq10" aria-expanded="false">
                            <h5 class="fw-bold mb-0">Is my data secure?</h5>
                            <span class="icon fs-4 text-primary"><i class="fa-solid fa-circle-plus"></i></span>
                        </div>
                        <div class="collapse" id="faq10">
                            <div class="faq-answer text-muted pt-3 border-top mt-3">
                                Yes. SMS data is encrypted in transit (HTTPS) between the Android app and the backend. Access tokens are hashed with SHA-256 before storage. The platform is self-hosted on your infrastructure — data never leaves your Docker environment. You have full control over database backups and retention.
                            </div>
                        </div>
                    </div>

                    <div class="glass-card p-4 mb-3">
                        <div class="faq-question d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#faq11" aria-expanded="false">
                            <h5 class="fw-bold mb-0">Can I delete my data?</h5>
                            <span class="icon fs-4 text-primary"><i class="fa-solid fa-circle-plus"></i></span>
                        </div>
                        <div class="collapse" id="faq11">
                            <div class="faq-answer text-muted pt-3 border-top mt-3">
                                Yes. From <strong>Data Management</strong> in the dashboard you can delete non-finance SMS, purge data older than a chosen period, or remove specific upload batches. To delete your account and all associated data, use the Info &amp; Auth page. You can also revoke individual device tokens to stop data collection from specific phones without deleting your entire account.
                            </div>
                        </div>
                    </div>

                    <div class="glass-card p-4 mb-4">
                        <div class="faq-question d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#faq12" aria-expanded="false">
                            <h5 class="fw-bold mb-0">What if I lose my phone?</h5>
                            <span class="icon fs-4 text-primary"><i class="fa-solid fa-circle-plus"></i></span>
                        </div>
                        <div class="collapse" id="faq12">
                            <div class="faq-answer text-muted pt-3 border-top mt-3">
                                Log in to the dashboard, go to <strong>Info & Auth</strong>, and revoke the device token for the lost phone. This immediately invalidates the token and stops any future uploads from that device. Your existing data remains safe in the database.
                            </div>
                        </div>
                    </div>

                    <h4 class="fw-bold mb-4 mt-5 text-primary"><i class="fa-solid fa-wrench me-2"></i>Troubleshooting</h4>

                    <div class="glass-card p-4 mb-3">
                        <div class="faq-question d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#faq13" aria-expanded="false">
                            <h5 class="fw-bold mb-0">My Android app shows "Connection failed"</h5>
                            <span class="icon fs-4 text-primary"><i class="fa-solid fa-circle-plus"></i></span>
                        </div>
                        <div class="collapse" id="faq13">
                            <div class="faq-answer text-muted pt-3 border-top mt-3">
                                Check that: (1) your phone has internet access, (2) the server URL in the app settings is correct (should be the public IP or domain of your Mpesa Analyzer server), (3) the backend server is running and accessible on port :9002, (4) your device token is correctly entered and not expired.
                            </div>
                        </div>
                    </div>

                    <div class="glass-card p-4 mb-3">
                        <div class="faq-question d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#faq14" aria-expanded="false">
                            <h5 class="fw-bold mb-0">The ML analysis is stuck or very slow</h5>
                            <span class="icon fs-4 text-primary"><i class="fa-solid fa-circle-plus"></i></span>
                        </div>
                        <div class="collapse" id="faq14">
                            <div class="faq-answer text-muted pt-3 border-top mt-3">
                                ML processing speed depends on your server's CPU/GPU and the volume of SMS. Processing is batched, so large volumes are handled efficiently. If processing appears stopped, an admin can check the <strong>Auto Jobs</strong> toggle in the ML console (when disabled, no ML jobs run) and confirm the FastAPI service on port :9050 is healthy. You can also click <strong>Full</strong> rescan to restart classification from scratch.
                            </div>
                        </div>
                    </div>

                    <div class="glass-card p-4 mb-4">
                        <div class="faq-question d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#faq15" aria-expanded="false">
                            <h5 class="fw-bold mb-0">I forgot my password</h5>
                            <span class="icon fs-4 text-primary"><i class="fa-solid fa-circle-plus"></i></span>
                        </div>
                        <div class="collapse" id="faq15">
                            <div class="faq-answer text-muted pt-3 border-top mt-3">
                                On the login page, click <strong>Forgot Password</strong> (or navigate to <a href="<?= url_to('magic-link') ?>">/magic-link</a>). Enter your email address, and a magic login link will be sent to your inbox. Click the link to log in securely. If you don't receive the email, check your spam folder.
                            </div>
                        </div>
                    </div>

                    <div class="glass-card p-4">
                        <div class="faq-question d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#faq16" aria-expanded="false">
                            <h5 class="fw-bold mb-0">How do I update the Android app?</h5>
                            <span class="icon fs-4 text-primary"><i class="fa-solid fa-circle-plus"></i></span>
                        </div>
                        <div class="collapse" id="faq16">
                            <div class="faq-answer text-muted pt-3 border-top mt-3">
                                Download the latest APK and install it over the existing version. Your settings and synced data will be preserved. It's recommended to check for updates periodically as new features and improvements are released.
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
                <h2 class="fw-bold mb-3">Still have questions?</h2>
                <p class="mb-4 opacity-75 fs-5">Create an account and explore the dashboard, or refer to the setup guide for step-by-step instructions.</p>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="<?= url_to('register') ?>" class="btn btn-light btn-lg px-5 text-primary fw-bold shadow-sm">
                        <i class="fa-solid fa-user-plus me-2"></i>Get Started
                    </a>
                    <a href="<?= base_url('setup') ?>" class="btn btn-outline-light btn-lg px-5 fw-bold">
                        <i class="fa-solid fa-book me-2"></i>Setup Guide
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
