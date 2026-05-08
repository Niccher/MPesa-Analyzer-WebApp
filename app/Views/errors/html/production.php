<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="robots" content="noindex">
    <title>Whoops! - M-Pesa Analyzer</title>
    <link rel="icon" type="image/png" href="/assets/img/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --primary: #5149E4; --bg-gradient: linear-gradient(135deg, #B1B8ED 0%, #8E96E0 100%); }
        body { font-family: 'Outfit', sans-serif; background: var(--bg-gradient); height: 100vh; margin: 0; display: flex; align-items: center; justify-content: center; }
        .error-card { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(15px); border-radius: 30px; padding: 3rem; max-width: 500px; width: 90%; text-align: center; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1); }
        .error-title { font-size: 2.5rem; font-weight: 700; margin-bottom: 1rem; color: var(--primary); }
        .error-message { color: #636e72; font-size: 1.2rem; margin-bottom: 2rem; }
        .btn-primary { background-color: var(--primary); border: none; padding: 12px 30px; border-radius: 50px; font-weight: 600; }
        .illustration { font-size: 4rem; color: #ffab00; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="illustration"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <h1 class="error-title">Whoops!</h1>
        <p class="error-message">We hit a snag. Please try again later or contact support if the issue persists.</p>
        <div class="d-grid"><a href="/" class="btn btn-primary btn-lg">Return to Dashboard</a></div>
    </div>
</body>
</html>
