<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Bad Request - M-Pesa Analyzer</title>
    
    <link rel="icon" type="image/png" href="/favicon.png">
    
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --primary: #5149E4;
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
        }
        .error-code {
            font-size: 8rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, var(--primary) 0%, #4834D4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .error-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        .error-message {
            font-size: 1.1rem;
            color: #636e72;
            margin-bottom: 2rem;
        }
        .btn-primary {
            background-color: var(--primary);
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
        }
        .illustration {
            font-size: 5rem;
            color: #fab1a0;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="illustration">
            <i class="fa-solid fa-circle-exclamation"></i>
        </div>
        <div class="error-code">400</div>
        <h1 class="error-title">Bad Request</h1>
        <p class="error-message">
            Something went wrong with the request. Please check your parameters and try again.
        </p>
        <div class="d-grid gap-2">
            <a href="/" class="btn btn-primary btn-lg">Return to Dashboard</a>
        </div>
    </div>
</body>
</html>
