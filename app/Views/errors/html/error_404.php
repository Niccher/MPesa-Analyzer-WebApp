<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Page Not Found - M-Pesa Analyzer</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/favicon.png">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --primary: #5D5FEF;
            --bg-gradient: linear-gradient(135deg, #B1B8ED 0%, #8E96E0 100%);
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--bg-gradient);
            height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #2D3436;
        }

        .error-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 30px;
            padding: 3rem;
            max-width: 500px;
            width: 90%;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .error-code {
            font-size: 8rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, var(--primary) 0%, #4834D4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            opacity: 0.8;
        }

        .error-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #333;
        }

        .error-message {
            font-size: 1.1rem;
            color: #636e72;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .btn-primary {
            background-color: var(--primary);
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 8px 15px rgba(93, 95, 239, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 20px rgba(93, 95, 239, 0.4);
            background-color: #4834D4;
        }

        .illustration {
            font-size: 5rem;
            color: var(--primary);
            margin-bottom: 1rem;
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="illustration">
            <i class="fa-solid fa-compass fa-spin-pulse"></i>
        </div>
        <div class="error-code">404</div>
        <h1 class="error-title">Endpoint Lost</h1>
        <p class="error-message">
            <?php if (ENVIRONMENT !== 'production') : ?>
                <?= nl2br(esc($message)) ?>
            <?php else : ?>
                Sorry, we couldn't find the page you're looking for. It might have been moved or deleted.
            <?php endif ?>
        </p>
        <div class="d-grid gap-2">
            <a href="/" class="btn btn-primary btn-lg">
                <i class="fa-solid fa-house me-2"></i> Return to Safety
            </a>
            <button onclick="window.history.back()" class="btn btn-link text-decoration-none text-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i> Go Back
            </button>
        </div>
    </div>
</body>
</html>
