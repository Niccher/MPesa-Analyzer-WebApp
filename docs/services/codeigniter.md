# CodeIgniter 4 Service Handbook — Mpesa Analyzer WebApp

This handbook covers MVC architecture, Shield authentication, route definitions, and custom Spark CLI commands.

---

## 1. Directory Structure

```
app/
├── Config/                  # App, Database, Filters, Routes, Shield, MlBackend
├── Controllers/
│   ├── Api/V1/              # Android API controllers (Uploads, Auth, Analytics, Settings)
│   ├── Admin/               # Admin consoles (Ml, Overview, Users, Crons, Devices)
│   ├── DashboardController  # Main user financial dashboard
│   ├── Graph                # Financial analytics & charts
│   ├── Transactions         # Paginated transaction listing & CSV export
│   └── Budget               # Spending limits & progress tracking
├── Commands/                # Spark CLI background commands
├── Database/                # Migrations & Seeds
├── Filters/                 # Shield session, token, CSRF, admin filters
├── Helpers/                 # Date & encryption helper functions
├── Models/                  # ModUploads, ModUser, ModBudget, ModInsights
└── Views/                   # Dashboard layouts, admin views, reports
```

---

## 2. Authentication Architecture (CodeIgniter Shield)

The application utilizes **CodeIgniter Shield** supporting two distinct operational contexts:

1. **Web Dashboard Users**:
   - Authenticated via standard session cookies.
   - Guarded by the `'session'` filter in `app/Config/Routes.php`.
   - Supports Magic Link passwordless login (`/magic-link`).
2. **Mobile Android Clients**:
   - Authenticated via SHA-256 access tokens (`auth_identities` table).
   - Guarded by token validation in `App\Controllers\Api\V1\AuthController`.

---

## 3. Custom Spark CLI Commands

Custom background tasks reside in `app/Commands/`:

| Command | Class | Description |
|---------|-------|-------------|
| `php spark llm:process` | `LlmProcess` | CLI trigger to dispatch pending SMS to the ML service. |
| `php spark cron:run` | `CronRun` | Executes scheduled cron definitions configured in the admin UI. |
| `php spark data:retention` | `DataRetention` | Purges expired logs and non-essential raw cache data. |
| `php spark uploads:cleanup` | `UploadsCleanup` | Cleans temporary binary upload streams from `writable/uploads/`. |
| `php spark digest:weekly` | `WeeklyDigest` | Generates weekly financial digest notifications for active users. |
