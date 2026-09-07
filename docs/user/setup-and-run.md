# Setup and Run Guide — Mpesa Analyzer WebApp

This guide walks through starting and operating the **Mpesa Analyzer WebApp** container stack, which includes the web application, shared MySQL 8.4 database, and phpMyAdmin.

---

## 1. Prerequisites

- **Docker Engine** 24.0+ and **Docker Compose** v2
- Host ports available: `9002` (WebApp), `9306` (MySQL), `9000` (phpMyAdmin)

---

## 2. Step-by-Step Run Instructions

From the repository root:

### Step 1: Copy Environment Configuration
```bash
cp .env.example .env
```

### Step 2: Start the Multi-Service Stack
```bash
docker compose up --build -d
```
The container entrypoint (`entrypoint.sh`) will automatically:
1. Wait for the MySQL container to accept TCP connections.
2. Run database migrations via `php spark migrate --all`.
3. Start the background cron daemon.
4. Launch Apache serving on port `9002`.

### Step 3: Verify Service Health
```bash
curl -f http://localhost:9002/health
```

### Step 4: Access Interfaces
- **Web Application Dashboard**: http://localhost:9002
- **phpMyAdmin Database Tool**: http://localhost:9000
  - Host: `mysql`
  - User: `root`
  - Password: `root_password`

### Step 5: Stopping the Stack
```bash
docker compose down
```
To preserve persistent database data stored in the `mysql-data` volume, do not pass `-v`.
