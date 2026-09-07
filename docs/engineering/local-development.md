# Local Development Guide — Mpesa Analyzer WebApp

Instructions for developing directly on a host machine without Docker.

---

## 1. Requirements

- **PHP 8.3+** with extensions: `php-mysql`, `php-intl`, `php-mbstring`, `php-curl`, `php-xml`
- **Composer 2.x**
- Running **MySQL 8.4** instance

---

## 2. Setup Steps

### Step 1: Install Dependencies
```bash
composer install
```

### Step 2: Environment Configuration
```bash
cp .env.example .env
```
Edit `.env` and set:
```ini
CI_ENVIRONMENT = development
app.baseURL = 'http://localhost:8080/'
database.default.hostname = 127.0.0.1
database.default.port = 3306
database.default.database = db_mpesa_analyzer
database.default.username = root
database.default.password = root_password
```

### Step 3: Run Database Migrations
```bash
php spark migrate --all
```

### Step 4: Launch Built-in Development Server
```bash
php spark serve --host 0.0.0.0 --port 8080
```
Open http://localhost:8080 in your browser.
