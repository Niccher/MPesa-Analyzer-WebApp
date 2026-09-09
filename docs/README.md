# M-Pesa Analyzer WebApp — Engineering Documentation

Welcome to the engineering documentation for **M-Pesa Analyzer WebApp**, the CodeIgniter 4 presentation layer, database manager, and mobile API gateway of the M-Pesa Analyzer ecosystem.

System-wide architecture and cross-repo sequence flows are anchored in the [ML Mpesa Analyzer Architecture Hub](https://github.com/Niccher/ML-Mpesa-Analyser/tree/main/docs/architecture).

---

## Documentation Index

| Topic | Document | Target Audience |
|-------|----------|-----------------|
| **Operator Guide** | [../README.md](../README.md) | Operators running the web application |
| **User Setup** | [user/setup-and-run.md](user/setup-and-run.md) | End-user deployment walkthrough |
| **Configuration** | [user/configuration.md](user/configuration.md) | Full environment variable reference |
| **Troubleshooting** | [user/troubleshooting.md](user/troubleshooting.md) | Common runtime issues & remedies |
| **CI4 Service Architecture** | [services/codeigniter.md](services/codeigniter.md) | Controllers, models, telemetry & commands |
| **Mobile API Contract** | [api/contract.md](api/contract.md) | REST API endpoints (`/api/v1`) & payloads |
| **Database & Migrations** | [engineering/database.md](engineering/database.md) | Schema design, tables, and migrations |
| **Local Development** | [engineering/local-development.md](engineering/local-development.md) | Setting up native PHP 8.3 & Composer |
| **Security & Encryption** | [engineering/security.md](engineering/security.md) | AES-128 dynamic IV, token hashing & Shield |
| **Making Changes** | [engineering/making-changes.md](engineering/making-changes.md) | Development workflows and code standards |
| **Testing Guide** | [engineering/testing.md](engineering/testing.md) | PHPUnit execution and test suite |
| **Release Matrix** | [engineering/release.md](engineering/release.md) | Compatibility across ecosystem components |
| **Contributing** | [engineering/contributing.md](engineering/contributing.md) | Pull request guidelines and git workflow |
| **Dev Troubleshooting** | [engineering/troubleshooting.md](engineering/troubleshooting.md) | Deep debugging and Apache/PHP logs |
| **Runbook: AI Rescan** | [runbooks/full-rescan.md](runbooks/full-rescan.md) | Historical loot dataset reprocessing |
| **Runbook: Backups** | [runbooks/backup-and-restore.md](runbooks/backup-and-restore.md) | Database snapshotting and restoration |
| **Runbook: Restarts** | [runbooks/restart.md](runbooks/restart.md) | Container cycling and cache purging |

---

## Ecosystem Sibling Repositories

- **ML Intelligence Microservice**: [Niccher/ML-Mpesa-Analyser](https://github.com/Niccher/ML-Mpesa-Analyser) (Python FastAPI, llama.cpp, Qwen2.5)
- **Android Mobile Client**: [Niccher/MPesa-Analyzer-App](https://github.com/Niccher/MPesa-Analyzer-App) (Kotlin, Retrofit, Jetpack Compose)\n