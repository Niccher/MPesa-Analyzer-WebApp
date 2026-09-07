# M-Pesa Analyzer WebApp — Engineering Documentation

Welcome to the engineering documentation for **M-Pesa Analyzer WebApp**, the CodeIgniter 4 presentation layer, database manager, and mobile API gateway of the M-Pesa Analyzer ecosystem.

System-wide architecture and cross-repo sequence flows are anchored in the [ML Mpesa Analyzer Architecture Hub](https://github.com/Niccher/ML-Mpesa-Analyser/tree/main/docs/architecture).

---

## Documentation Index

| I want to… | Go here |
|------------|---------|
| **Run the webapp as an operator** | [../README.md](../README.md) |
| **Work on CodeIgniter 4 controllers, models & views** | [services/codeigniter.md](services/codeigniter.md) |
| **Review the mobile & client API contract (`/api/v1`)** | [api/contract.md](api/contract.md) |
| **Inspect database tables, ERD, views & migrations** | [engineering/database.md](engineering/database.md) |
| **Set up a native PHP & Composer environment** | [engineering/local-development.md](engineering/local-development.md) |
| **Inspect AES-128 dynamic IV decryption & Shield auth** | [engineering/security.md](engineering/security.md) |
| **Add new routes, controllers, or database migrations** | [engineering/making-changes.md](engineering/making-changes.md) |
| **Run PHPUnit test suites** | [engineering/testing.md](engineering/testing.md) |
| **Troubleshoot Apache, session, or migration locks** | [engineering/troubleshooting.md](engineering/troubleshooting.md) |
| **Operational Runbook: Full database AI rescan** | [runbooks/full-rescan.md](runbooks/full-rescan.md) |
| **Follow contribution & pull request guidelines** | [engineering/contributing.md](engineering/contributing.md) |

---

## Ecosystem Sibling Repositories

- **ML Intelligence Microservice**: [Niccher/ML-Mpesa-Analyser](https://github.com/Niccher/ML-Mpesa-Analyser) (Python FastAPI, llama.cpp, Qwen2.5)
- **Android Mobile Client**: [Niccher/MPesa-Analyzer-App](https://github.com/Niccher/MPesa-Analyzer-App) (Kotlin, Retrofit, Jetpack Compose)
