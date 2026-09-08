# M-Pesa Analyzer WebApp

Web dashboard, MySQL database manager, and mobile API gateway for the M-Pesa Analyzer ecosystem. Ingests encrypted SMS payloads from Android, stores them in MySQL, triggers ML classification, and provides financial visualizations and budgeting tools.

Stack: PHP 8.3, CodeIgniter 4, Shield, MySQL 8.4, Docker Compose

**If you only need to run the application, this page is enough.**  
Software engineers: [docs/README.md](docs/README.md).

---

## What “Running” Looks Like

| Component | URL / Port | Expected Response / Check |
|-----------|------------|---------------------------|
| **Web Dashboard** | http://localhost:9002 | Login / Dashboard interface |
| **Health Endpoint** | http://localhost:9002/health | HTTP 200 OK |
| **phpMyAdmin** | http://localhost:9000 | MySQL administration UI |
| **MySQL Database** | localhost:9306 | Port open for external connections |

---

## Prerequisites

### Option A — Docker (Recommended)
- Git
- Docker Engine 24+ & Docker Compose v2

### Option B — Without Docker
- PHP 8.3+ with `php-intl`, `php-mbstring`, `php-mysql`, `php-curl`
- Composer 2.x
- Running MySQL 8.4 server
- Native run details: [docs/engineering/local-development.md](docs/engineering/local-development.md)

---

## Setup and Run

1. Clone the repository:
   ```bash
   cd "Mpesa Analyzer WebApp"
   ```
2. Copy environment configuration:
   ```bash
   cp .env.example .env
   ```
3. Start the application stack:
   ```bash
   docker compose up --build -d
   ```
   *Migrations and cron daemons run automatically on container boot.*
4. Check health:
   ```bash
   curl http://localhost:9002/health
   ```
5. Open http://localhost:9002 in your browser.
6. Stop the stack:
   ```bash
   docker compose down
   ```

---

## Configuration Users May Change

| Variable | Default | Purpose |
|----------|---------|---------|
| `app.baseURL` | `http://localhost:9002/` | Public web application URL |
| `database.default.hostname` | `mysql` | MySQL container hostname |
| `database.default.database` | `db_mpesa_analyzer` | Database schema name |
| `database.default.username` | `root` | Database username |
| `ML_BACKEND_URL` | `http://ml-mpesa-analyzer:9050` | Microservice URL for LLM processing |

Full parameter reference: [docs/user/configuration.md](docs/user/configuration.md).

---

## Deployment (Railway & Cloud)

The application ecosystem consists of 3 microservice containers:
1. **MySQL 8.4 Database Container** (Shared relational database)
2. **PHP 8.3 WebApp & Mobile API Gateway** (`Niccher/MPesa-Analyzer-WebApp`)
3. **Python 3.12 ML Microservice** (`Niccher/ML-Mpesa-Analyser`)

### Quick Setup Steps on Railway
1. Create a new project on Railway and attach a **MySQL** database plugin.
2. Deploy the **WebApp** repository connected to the MySQL service.
3. Deploy the **ML Microservice** repository connected to the same MySQL service.
4. Copy-paste the environment variables below into Railway's **Variables -> Bulk Raw Editor**.

### WebApp Service Environment Variables

**JSON Bulk Import Format:**
```json
{
  "database.default.hostname": "${{MySQL.MYSQLHOST}}",
  "database.default.database": "${{MySQL.MYSQLDATABASE}}",
  "database.default.username": "${{MySQL.MYSQLUSER}}",
  "database.default.password": "${{MySQL.MYSQLPASSWORD}}",
  "database.default.port": "${{MySQL.MYSQLPORT}}",
  "database.default.DBDriver": "MySQLi",
  "ML_BACKEND_URL": "http://ml-mpesa-analyzer:9050",
  "CI_ENVIRONMENT": "production",
  "MPESA_CRYPT_KEY": "<YOUR_AES_128_CBC_KEY>",
  "MPESA_CRYPT_IV": "<YOUR_AES_128_CBC_IV>",
  "SUPERADMIN_EMAIL": "<YOUR_SUPERADMIN_EMAIL>",
  "SUPERADMIN_PASSWORD": "<YOUR_SUPERADMIN_PASSWORD>"
}
```

**Raw `.env` Format:**
```env
database.default.hostname=${{MySQL.MYSQLHOST}}
database.default.database=${{MySQL.MYSQLDATABASE}}
database.default.username=${{MySQL.MYSQLUSER}}
database.default.password=${{MySQL.MYSQLPASSWORD}}
database.default.port=${{MySQL.MYSQLPORT}}
database.default.DBDriver=MySQLi
ML_BACKEND_URL=http://ml-mpesa-analyzer:9050
CI_ENVIRONMENT=production
MPESA_CRYPT_KEY=<YOUR_AES_128_CBC_KEY>
MPESA_CRYPT_IV=<YOUR_AES_128_CBC_IV>
SUPERADMIN_EMAIL=<YOUR_SUPERADMIN_EMAIL>
SUPERADMIN_PASSWORD=<YOUR_SUPERADMIN_PASSWORD>
```

### Post-Deployment Health Probes
- **WebApp Health**: `GET https://<your-webapp-domain>.up.railway.app/health`
- **ML Connectivity**: Log in to WebApp Admin $\rightarrow$ **ML Config** and click **Test Endpoint**.

---

## Something Went Wrong?

- **Port in use on 9002 or 9000**: Change port bindings in `docker-compose.yml`.
- **Database connection error**: Verify MySQL health using `docker compose ps mysql`.
- **500 Server Error**: Ensure `writable/` has appropriate write permissions.
- Operational troubleshooting: [docs/user/troubleshooting.md](docs/user/troubleshooting.md).

---

## Software Engineers

- **Service Architecture**: [docs/services/codeigniter.md](docs/services/codeigniter.md)
- **Mobile API Contract**: [docs/api/contract.md](docs/api/contract.md)
- **Database Schema & Migrations**: [docs/engineering/database.md](docs/engineering/database.md)
- **Security & Encryption**: [docs/engineering/security.md](docs/engineering/security.md)
- **Making Changes & Testing**: [docs/engineering/making-changes.md](docs/engineering/making-changes.md)
- **System Architecture Anchor**: [ML Mpesa Analyzer Architecture](https://github.com/Niccher/ML-Mpesa-Analyser/tree/main/docs/architecture)
