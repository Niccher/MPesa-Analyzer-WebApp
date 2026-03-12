<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>M-Pesa Analyzer - Financial Insights</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- FontAwesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary: #5D5FEF;
            --secondary: #B1B8ED;
            --dark: #1A1A2E;
            --light: #F8F9FA;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--light);
            color: var(--dark);
        }

        .navbar {
            padding: 1.5rem 0;
            background: transparent;
            transition: all 0.3s ease;
        }

        .navbar.scrolled {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            padding: 1rem 0;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }

        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
            padding: 0.8rem 2rem;
            font-weight: 600;
            border-radius: 12px;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            background-color: #4a4cd9;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(93, 95, 239, 0.3);
        }

        .btn-outline-primary {
            color: var(--primary);
            border-color: var(--primary);
            padding: 0.8rem 2rem;
            font-weight: 600;
            border-radius: 12px;
        }

        .hero-section {
            padding: 160px 0 100px;
            background: linear-gradient(135deg, #fff 0%, var(--secondary) 100%);
            position: relative;
            overflow: hidden;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 1.5rem;
            background: linear-gradient(to right, var(--primary), #8E91FF);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-img {
            max-width: 100%;
            height: auto;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.1);
        }

        .feature-card {
            background: #fff;
            padding: 2.5rem;
            border-radius: 24px;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            transition: all 0.3s;
            height: 100%;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.08);
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            background: var(--secondary);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 16px;
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .footer {
            background: var(--dark);
            color: #fff;
            padding: 4rem 0 2rem;
        }

        .app-badge {
            height: 50px;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .app-badge:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary fs-3" href="#">
                <i class="fa-solid fa-wallet"></i> Analyzer
            </a>
            <div class="ms-auto d-flex gap-3">
                <a href="<?= url_to('login') ?>" class="btn btn-outline-primary d-none d-sm-block">Login</a>
                <a href="<?= url_to('dashboard') ?>" class="btn btn-primary">Dashboard</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="hero-title">Unlock Your Financial Insights</h1>
                    <p class="lead mb-4 text-muted">Master your M-Pesa transactions with our powerful analyzer. Track spending, visualize trends, and take control of your mobile money.</p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="#" class="btn btn-primary btn-lg">
                            <i class="fa-brands fa-android me-2"></i> Download Android App
                        </a>
                        <a href="<?= url_to('login') ?>" class="btn btn-outline-primary btn-lg">Get Started Online</a>
                    </div>
                </div>
                <div class="col-lg-6 mt-5 mt-lg-0">
                    <div class="position-relative">
                        <!-- We could use an image here if available, using a placeholder for now -->
                        <div class="bg-white p-3 rounded-4 shadow-lg">
                            <div class="bg-light rounded-3 d-flex align-items-center justify-content-center" style="height: 400px;">
                                <i class="fa-solid fa-chart-line fa-5x text-primary opacity-25"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Features Section -->
    <section class="py-5 bg-white">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="fw-bold h1">Everything You Need</h2>
                <p class="text-muted mx-auto" style="max-width: 600px;">Integrated solutions for web and mobile to keep your finances in check.</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fa-solid fa-laptop-code"></i>
                        </div>
                        <h3>Web Dashboard</h3>
                        <p class="text-muted">A comprehensive, centralized dashboard for deep-dive transaction analysis. Visualize your spending patterns with interactive charts, manage your account, and generate detailed reports from anywhere.</p>
                        <ul class="list-unstyled mb-4">
                            <li><i class="fa-solid fa-check text-primary me-2"></i> Advanced Analytics & Graphs</li>
                            <li><i class="fa-solid fa-check text-primary me-2"></i> Transaction History Search</li>
                            <li><i class="fa-solid fa-check text-primary me-2"></i> Secure Account Management</li>
                        </ul>
                        <a href="<?= url_to('login') ?>" class="btn btn-link text-decoration-none fw-bold p-0 text-primary">Explore Web App <i class="fa-solid fa-arrow-right ms-1"></i></a>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fa-solid fa-mobile-screen-button"></i>
                        </div>
                        <h3>Android App</h3>
                        <p class="text-muted">Seamlessly sync your transaction SMS directly from your phone. Our Android app provides real-time tracking, privacy-focused automated data extraction, and offline accessibility for your convenience.</p>
                        <ul class="list-unstyled mb-4">
                            <li><i class="fa-solid fa-check text-primary me-2"></i> Automated SMS Syncing</li>
                            <li><i class="fa-solid fa-check text-primary me-2"></i> Real-time Push Notifications</li>
                            <li><i class="fa-solid fa-check text-primary me-2"></i> Offline Mode Support</li>
                        </ul>
                        <a href="#" class="btn btn-link text-decoration-none fw-bold p-0 text-primary">Download APK <i class="fa-solid fa-arrow-right ms-1"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Download CTA -->
    <section class="py-5 bg-light">
        <div class="container py-5 text-center">
            <div class="bg-primary p-5 rounded-4 shadow-lg text-white">
                <h2 class="fw-bold mb-3">Ready to start?</h2>
                <p class="mb-4 opacity-75">Join thousands of users managing their finances smarter with M-Pesa Analyzer.</p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="#" class="btn btn-light btn-lg px-4 text-primary fw-bold">Download App</a>
                    <a href="<?= url_to('register') ?>" class="btn btn-outline-light btn-lg px-4 fw-bold">Create Account</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4 mb-md-0">
                    <h4 class="text-white fw-bold mb-4"><i class="fa-solid fa-wallet"></i> Analyzer</h4>
                    <p class="opacity-75">The most comprehensive tool for M-Pesa transaction analysis and financial tracking.</p>
                </div>
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5 class="text-white fw-bold mb-4">Quick Links</h5>
                    <ul class="list-unstyled opacity-75">
                        <li class="mb-2"><a href="<?= url_to('login') ?>" class="text-white text-decoration-none">Login</a></li>
                        <li class="mb-2"><a href="<?= url_to('register') ?>" class="text-white text-decoration-none">Register</a></li>
                        <li class="mb-2"><a href="<?= url_to('dashboard') ?>" class="text-white text-decoration-none">Dashboard</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5 class="text-white fw-bold mb-4">Connect</h5>
                    <div class="d-flex gap-3 fs-4 opacity-75">
                        <a href="#" class="text-white"><i class="fa-brands fa-github"></i></a>
                        <a href="#" class="text-white"><i class="fa-brands fa-twitter"></i></a>
                        <a href="#" class="text-white"><i class="fa-brands fa-linkedin"></i></a>
                    </div>
                </div>
            </div>
            <hr class="mt-5 mb-4 opacity-25">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0 opacity-75">&copy; <?= date('Y') ?> M-Pesa Analyzer. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end mt-3 mt-md-0">
                    <p class="mb-0 opacity-75">Made with <i class="fa-solid fa-heart text-danger"></i> for financial freedom.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    </script>
</body>
</html>
