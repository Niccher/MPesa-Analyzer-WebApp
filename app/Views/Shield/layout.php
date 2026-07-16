<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= $this->renderSection('title') ?></title>
    <link rel="shortcut icon" type="image/png" href="/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #5D5FEF;
            --bg-color: #B1B8ED;
            --card-bg: rgba(255, 255, 255, 0.9);
            --text-primary: #1A1A1A;
            --text-secondary: #666666;
            --input-bg: #F3F4F6;
            --radius: 4px;
        }

        * {
            box-sizing: border-box;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background: var(--bg-color);
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .auth-container {
            width: 100%;
            max-width: 400px;
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border-radius: var(--radius);
            padding: 40px 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .auth-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .auth-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
            margin: 0;
        }

        .auth-header p {
            color: var(--text-secondary);
            margin-top: 5px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--text-secondary);
        }

        .form-control {
            width: 100%;
            padding: 14px 18px;
            border-radius: 4px;
            border: 2px solid transparent;
            background: var(--input-bg);
            font-size: 1rem;
            transition: all 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 10px rgba(93, 95, 239, 0.2);
        }

        .btn-primary {
            width: 100%;
            padding: 14px;
            border-radius: 4px;
            border: none;
            background: var(--primary);
            color: white;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.2s, background 0.3s;
            margin-top: 10px;
        }

        .btn-primary:hover {
            background: #4A4CD4;
            transform: translateY(-2px);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .auth-footer {
            text-align: center;
            margin-top: 25px;
            font-size: 0.9rem;
            color: var(--text-secondary);
        }

        .auth-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }

        .alert {
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }

        .alert-error {
            background: #FEE2E2;
            color: #B91C1C;
            border: 1px solid #FECACA;
        }

        .alert-success {
            background: #D1FAE5;
            color: #065F46;
            border: 1px solid #A7F3D0;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <?= $this->renderSection('main') ?>
    </div>
</body>
</html>
