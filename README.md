# M-Pesa Analyzer WebApp

**Cloud backend and analytics dashboard for the M-Pesa Analyzer ecosystem.** Receives encrypted transaction data from the Android app, stores it in MySQL, triggers LLM-powered classification, and serves a rich web dashboard for visualising spending, setting budgets, generating reports, and managing accounts.

[![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white)]()
[![CodeIgniter 4](https://img.shields.io/badge/CodeIgniter-4.x-EF4223?style=for-the-badge&logo=codeigniter&logoColor=white)]()
[![MySQL 8.4](https://img.shields.io/badge/MySQL-8.4-4479A1?style=for-the-badge&logo=mysql&logoColor=white)]()
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?style=for-the-badge&logo=docker&logoColor=white)]()
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=for-the-badge)](LICENSE)

---

## The Three-Repos Ecosystem

This web app is the **storage and presentation layer** of a three-part stack:

```
┌─────────────────────────────────────────────────────────────────────┐
│                    M-Pesa Analyzer Ecosystem                         │
│                                                                      │
│  ┌──────────────────────┐                                            │
│  │  Android App          │                                            │
│  │  (Mpesa_Analyzer_App) │                                            │
│  │                       │                                            │
│  │  Reads MPESA SMS      │                                            │
│  │  AES-128 encrypts     │                                            │
│  │  Uploads to backend   │                                            │
│  └──────────┬───────────┘                                            │
│             │ POST /process/upload                                    │
│             ▼                                                        │
│  ┌──────────────────────────────────────────────────────────────┐   │
│  │  ┌─────────────────────────────────────┐                      │   │
│  │  │  CI4 Web Backend                    │                      │   │
│  │  │  (This repo — YOU ARE HERE)         │                      │   │
│  │  │                                     │                      │   │
│  │  │  Decrypts AES payload               │     ┌──────────────────┐│
│  │  │  Inserts SMS into tbl_Sms           │     │  Docker LLM      ││
│  │  │  Creates processing job             │────▶│  Service         ││
│  │  │  Serves web dashboard              │     │                  ││
│  │  │  Manages budgets, reports, users   │     │  Classifies      ││
│  │  │  [PHP / CodeIgniter 4 / Shield]    │     │  Extracts        ││
│  │  └──────────────┬──────────────────────┘     │  Writes to DB   ││
│  │                 │                            │  [FastAPI /      ││
│  │                 ▼                            │   llama.cpp /    ││
│  │  ┌──────────────────────────────────────┐    │   Qwen2.5 1.5B]  ││
│  │  │  Shared MySQL 8.4 Database           │    └──────────────────┘│
│  │  │  db_mpesa_analyzer                   │          │             │
│  │  │  tbl_Sms, tbl_Loot, tbl_Devices,    │◄─────────┘             │
│  │  │  tbl_Analyzed_Transactions,          │                         │
│  │  │  tbl_Sender_Profiles, ...           │                         │
│  │  └──────────────────────────────────────┘                         │
│  └───────────────────────────────────────────────────────────────────┘
└──────────────────────────────────────────────────────────────────────┘
```

### How they depend on each other

| Step | Android App | This Web App | Docker LLM |
|------|------------|-------------|------------|
| **1. Capture** | Reads SMS, encrypts with AES-128-CBC | — | — |
| **2. Upload** | Sends encrypted file via `POST /process/upload` | Decrypts payload, parses JSON, inserts SMS into `tbl_Sms`, updates `tbl_Loot_Summary` | — |
| **3. Trigger** | — | Inserts job in `tbl_Processing_Jobs`, calls `POST /process/for-user/{id}` on LLM service | Polls `tbl_Sms` (background, gated by admin auto-jobs toggle) or receives trigger from web app |
| **4. Classify & Extract** | — | — | Classifies senders (known-dict or LLM), extracts amounts/counterparties/directions, writes **one canonical row** per SMS in `tbl_Sms`; `tbl_Analyzed_Transactions` and `tbl_Sms_Classification` are views over it |
| **5. Visualise** | Fetches summaries via `get/my_uploads`, `get/my_summary_calculations` | Dashboard shows classified transactions, budgets, reports, analytics, per-job ML run summaries | — |

---

## Hardcoded Regex vs Machine Learning: Why Both Matter

This ecosystem uses a **hybrid approach**:

**On-device (Android app):** Fast, private regex scanning parses SMS at capture time. This gives instant feedback and works offline.

**Server-side (Docker LLM):** A 1.5B-parameter LLM (Qwen2.5) reclassifies and enriches all SMS with contextual understanding that regex cannot achieve:

| Capability | Regex Scanner | LLM (Qwen2.5 1.5B) |
|------------|--------------|---------------------|
| Speed | Instant, no network | ~1-3s per SMS batch |
| Format changes | Breaks — requires app update | Adapts automatically |
| Unknown senders | Cannot handle | Classifies by content alone |
| Sender name resolution | Regex can't resolve "MPE802" | Identifies as "KCB MPESA" |
| Contextual direction | "Sent" / "Received" keyword match | Understands "You have received KSH500 from" vs "500 bob withdrawn" |
| Partial extraction | All-or-nothing | Returns what it can, nulls the rest |
| Confidence scoring | No | 0.0–1.0 confidence per extraction |
| Maintenance | Code change + app store deployment | No changes needed |
| Cost | Free | ~2 GB RAM, CPU-only, zero API fees |

---

## About the Project

**M-Pesa Analyzer WebApp** is the server-side component of a full-stack financial tracking solution. Built on CodeIgniter 4, it provides:

- A secure **REST API** for the Android app to upload encrypted SMS data
- A full **web dashboard** with transaction search, analytics, budgets, and reports
- **CodeIgniter Shield** authentication (session + access tokens) for both web and mobile
- **Trigger mechanism** for the Docker LLM service to classify and enrich transactions
- A dedicated **admin ML console** to manage models, prompts, configuration, jobs, and allowed senders
- Fully **Dockerized** deployment with MySQL 8.4 and phpMyAdmin

---

## Features

### Mobile Data Ingestion API

| Endpoint | Method | Purpose |
|---|---|---|
| `/process/upload` | POST | Receive encrypted `.txt` loot file from Android, decrypt (AES-128-CBC), parse JSON, insert SMS + classifications |
| `/process/device` | POST | Register or identify an Android device by 15-field hardware fingerprint |
| `/process/get/my_uploads` | POST | List all upload batches for a user/device with per-batch counts and formatted dates |
| `/process/get/my_summary_calculations` | POST | Detailed summary stats for a single upload UUID (sent/received/balance/fuliza/errors breakdown) |
| `/process/get/my_uploads_count` | POST | Total upload count for a user/device |
| `/process/get/my_uploads_category_count` | POST | Aggregated category counts across all uploads |
| `/process/get/my_uploads_graph` | POST | Last 3 uploads' summary data for chart rendering |
| `/process/get/user_info` | POST | User name and email by ID |
| `/process/get/list_all_sms_in_category` | POST | All SMS messages matching a category filter |
| `/process/set/delete_loot_by_uuid` | POST | Delete a specific upload UUID |
| `/process/verify_token` | POST | Validate a raw access token (SHA-256 match) |
| `/process/delete_data` | POST | Delete all user data but preserve the account |
| `/process/delete_account` | POST | Delete account and all associated data |

### Web Dashboard

| Page | Features |
|---|---|
| **Dashboard** | Financial metrics, sent/received summaries, recent transactions, top counterparties, budget alerts, smart financial alerts, spending trends, financial health score |
| **Analytics (Graph)** | 30-day chart data with toggleable views, AI-generated observations, recent transaction list |
| **Transactions** | Paginated list with category filters (finances/money_in/money_out/notifications), CSV export |
| **Search** | Full-text search with date range, category, sender, keyword, and amount range filters |
| **History** | Tabbed view — **Upload History** (per-batch totals, classification, category breakdowns) and **ML Jobs** (every job run with a click-through summary modal showing SMS/sender/category/direction breakdowns, model & backend config) |
| **Reports** | Monthly reports with daily inflow/outflow, category breakdown, top counterparties, trends, recurring payments; print-friendly view |
| **Budget** | Create/edit/delete budgets per category with spend-vs-limit progress tracking (monthly/weekly periods) |
| **Analyse** | Regex-based SMS parsing interface, keyword-to-category rule management with retroactive application |
| **Blocklist** | Blocked / Allowed / Unknown sender tabs; the Allowed tab merges the ML hardcoded default finance senders so every preselected good sender appears even without matching data |
| **Data Management** | Export (CSV/JSON/settings/rules), purge old data, delete upload batches, and **Delete Non-Finance SMS** (type `DELETE` to confirm) |
| **Info** | Account details, active device tokens, generate/revoke access tokens |

### Admin ML Console (`/admin/ml`)

| Page | Features |
|---|---|
| **Status** | Backend health, current configuration with icons, available models with GGUF metadata |
| **Models** | Upload `.gguf`/`.bin` models (streamed), activate, delete, and inspect metadata (params, quantization, context, architecture) via expandable rows |
| **Config** | Edit LLM model, max tokens, temperature, context size, prompt batch, GPU layers, SMS batch, retries, poll interval — each field shows its fallback default |
| **Test Prompt** | Send sample messages to the live LLM and inspect classification + extraction output |
| **Prompts** | Versioned prompt management — create/edit (new version), activate, delete; per-key "current vs hardcoded default" comparison |
| **Jobs** | **Start/Stop Auto Jobs** toggle (blocks the ML poller when off), aggregate stats, per-job metadata drill-down |
| **Senders** | View senders and override their finance flag |
| **Allowed** | Manage the global DB allowlist (add/remove/reset); falls back to hardcoded defaults when empty |

### LLM / AI Integration

| Feature | Detail |
|---|---|
| **LLM microservice** | FastAPI container at `http://ml-mpesa-analyzer:9050` |
| **Canonical SMS record** | Classification + parsed fields live on one `tbl_Sms` row per SMS; `tbl_Sms_Classification` and `tbl_Analyzed_Transactions` are views derived from it |
| **Rescan** | Triggers AI re-analysis of all transactions for a user (`/dashboard/rescan`) |
| **Full rescan** | Clears processing flags and LLM-derived columns, then re-runs LLM (`/dashboard/rescan/all`) |
| **Progress tracking** | Real-time job progress via `/dashboard/rescan/progress` (total/processed/classified counts) |
| **Job metadata** | Each ML job records rich JSON metadata (sender/SMS breakdowns, model, LLM tuning, duration, errors), surfaced to the user in the ML Jobs tab |
| **Auto-jobs control** | Admin can stop/start the background ML poller; when off, no ML jobs run |
| **Classification rules** | Custom keyword-to-category mappings via Analyse page; retroactively applied |

### Authentication

Two parallel auth systems:

| System | Users | Auth Method |
|---|---|---|
| **CodeIgniter Shield** | Modern web + mobile API | Session (web), SHA-256 AccessTokens (API), Magic Link (passwordless email) |
| **Legacy Auths** | Older mobile clients | `tbl_users` table with `password_hash()`, session-based |

**RBAC Groups**: superadmin, admin, developer, user, beta (each with granular permissions)

### Security

| Feature | Detail |
|---|---|
| **AES-128-CBC decryption** | Hardcoded key/IV (matching Android) — `openssl_decrypt()` on received loot files |
| **CSRF protection** | Enabled globally; token auto-refresh |
| **Session driver** | File-based (writable/session/), 30-day cookie expiry |
| **Password rules** | Min 8 chars, composition + dictionary checks |
| **CSP headers** | Configured Content-Security-Policy |
| **Honeypot** | Available but disabled |

---

## Tech Stack

| Layer | Technology |
|---|---|
| **Framework** | CodeIgniter 4 |
| **Language** | PHP 8.3 |
| **Database** | MySQL 8.4 (primary) / SQLite3 (testing) |
| **Auth Library** | CodeIgniter Shield |
| **DB Manager** | phpMyAdmin |
| **Containerisation** | Docker & Docker Compose |
| **Dependency Manager** | Composer |
| **LLM Microservice** | Python FastAPI + llama.cpp + Qwen2.5 1.5B (external container) |

---

## Quick Start (Full Stack)

```bash
# 1. Clone all three repos
git clone https://github.com/YourOrg/Mpesa_Analyzer_WebApp.git
git clone https://github.com/YourOrg/Mpesa_Analyser_Docker.git
git clone https://github.com/YourOrg/Mpesa_Analyzer_App.git

# 2. Start Web + Database
cd "Mpesa Analyzer WebApp"
cp .env.example .env
docker compose up --build -d

# 3. Start LLM Service
cd "Mpesa Analyser Docker"
cp .env.example .env
# Download the GGUF model
wget -P models/ https://huggingface.co/Qwen/Qwen2.5-1.5B-Instruct-GGUF/resolve/main/qwen2.5-1.5b-instruct-q4_k_m.gguf
docker compose up --build -d
```

**Services:**
| Service | URL |
|---|---|
| Web Application | `http://localhost:9002` |
| phpMyAdmin | `http://localhost:9000` |
| LLM Service Health | `http://localhost:9050/health` |

**Default DB credentials:**
- Host: `mysql`
- Database: `db_mpesa_analyzer`
- Username: `root`
- Password: `root_password`

---

## Local Development (without Docker)

```bash
composer install
cp .env.example .env
# Configure database.* in .env
php spark migrate --all
php spark serve
```

---

## Project Structure

```
.
├── app/
│   ├── Config/          # Routes, Database, Auth, Filters, Logger, Encryption, MlBackend, etc.
│   ├── Controllers/     # Home, Dash, Graph, Search, Transactions, History, Reports, Budget,
│   │                    # Analyse, Upload, UserAuth, Auths, Settings, Blocklist, Debug, Api,
│   │                    # Admin/* (Ml, Overview, Users, Logs, Crons, ...)
│   ├── Commands/        # Spark CLI commands (LlmProcess, CronRun, DataRetention, UploadsCleanup)
│   ├── Database/        # Migrations (created by php spark migrate)
│   ├── Helpers/         # Custom helpers (mpesa_date_helper)
│   ├── Libraries/       # Audit, Notifier, CronRunner, CronLogger, ModNotes, ...
│   ├── Models/          # ModUploads, ModUser, ModBudget, ModCryption, ModInsights, ...
│   └── Views/           # landing, Dash/* (+ _ml_jobs partial), Blocklist/*, Settings/*,
│                        # Admin/Ml/* (models, config, prompts, jobs, senders, allowed),
│                        # Reports/*, Budget/*, Layouts/*
├── public/              # Document root (index.php, assets)
├── writable/            # Cache, logs, sessions, uploads
├── docker-compose.yml   # 3-service orchestration (web + mysql + phpmyadmin)
├── Dockerfile           # php:8.3-apache build
└── entrypoint.sh        # Wait for MySQL → run migrations → start Apache
```

---

## Database Tables

| Table | Purpose |
|---|---|
| `tbl_Loot` | Uploaded file records (per-batch) |
| `tbl_Sms` | All parsed individual SMS messages — **canonical record** holding both classification and parsed financial fields |
| `tbl_Loot_Summary` | Per-upload summary stats |
| `tbl_Devices` | Registered Android device fingerprints |
| `tbl_Analyzed_Transactions` | **VIEW** over `tbl_Sms` (transactional SMS) — read-only, derived |
| `tbl_Sms_Classification` | **VIEW** over `tbl_Sms` — read-only, derived |
| `tbl_Sms_Processing` | LLM processing status per SMS (idempotency / retries) |
| `tbl_Processing_Jobs` | Job-level processing tracking + `metadata` JSON column (sender/SMS/model breakdowns per run) |
| `tbl_Sender_Profiles` | Counterparty sender profiles (built by LLM) |
| `tbl_Allowed_Senders` | Global DB allowlist — always treated as finance; falls back to hardcoded defaults when empty |
| `tbl_Blocked_Senders` | Per-user blocked senders |
| `tbl_ML_Controls` | Admin toggles (e.g. `auto_jobs_enabled`) |
| `tbl_LLM_Prompts` | Versioned prompt overrides (create/edit = new version, activate, delete) |
| `tbl_Category_Rules` | Keyword-to-category mapping rules |
| `tbl_User_Devices` | User-device linking |
| `tbl_Budgets` | Spending budgets (monthly/weekly) |
| `tbl_users` | Legacy user accounts |
| `users`, `auth_*` | Shield-managed auth tables (identities, logins, tokens, permissions) |

---

## Integration Summary

| Repository | Role | Key Tech | Depends On |
|-----------|------|----------|------------|
| [Mpesa_Analyzer_App](https://github.com/YourOrg/Mpesa_Analyzer_App) | Data capture & upload | Kotlin, Retrofit, AES-128 | This web app's API |
| **This repo** | Storage, dashboard, API | PHP 8.3, CI4, Shield, MySQL | MySQL database |
| [Mpesa Analyser Docker](https://github.com/YourOrg/Mpesa_Analyser_Docker) | LLM-powered classification | Python, FastAPI, llama.cpp, Qwen2.5 | MySQL database |

---

## Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/my-feature`
3. Commit changes: `git commit -m 'Add my feature'`
4. Push: `git push origin feature/my-feature`
5. Open a Pull Request

Please test inside the Docker environment before submitting.

---

## License

MIT License

---

## Support

- **Email**: [info@chegecache.co.ke](mailto:info@chegecache.co.ke)
- **Website**: [chegecache.co.ke](https://chegecache.co.ke)
