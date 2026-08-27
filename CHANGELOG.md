# Changelog — Mpesa Analyzer WebApp (CodeIgniter 4)

All notable changes to this project will be documented in this file.
Format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).
Versioning follows [Semantic Versioning](https://semver.org/).

---

## [3.3.0] — 2026-08-26

### Added
- **Per-user category rules engine** — `AnalysisCallbackController.php`
  implements exact/contains matching rules with hit telemetry and isolated
  retroactive backfill per user. Replaces the old monolithic `Analyse.php`.
- **Structured REST API v1 layer**:
  - `Api/V1/BaseApiController.php` — shared JWT auth, versioning, JSON response.
  - `Api/V1/AnalyticsController.php` — per-user analytics endpoints.
  - `Api/V1/AuthController.php` — token issue, refresh, revoke.
  - `Api/V1/NotesController.php` — transaction notes CRUD.
  - `Filters/ForceJsonResponseFilter.php` — enforces `application/json` on
    all `/api/*` routes.
- **`app/Config/version.json`** — single source of truth for app version
  (`3.3.0`), changelog bullets, GitHub URL, and APK download link;
  consumed by the Android AppInfo screen via `/api/v1/system/version`.
- **Dynamic versioning endpoint** `GET /api/v1/system/version` — serves the
  changelog and version metadata to Android clients for update-banner logic.
- `loot_uploaded` email templates (HTML + plain-text) for SMS upload
  notifications.
- Admin system nav section (`Views/Admin/System/_nav.php`).

### Changed
- **Controllers renamed** to singular PSR-4 class names:
  - `Dash.php` → `DashboardController.php`
  - `Auths.php` → `AuthController.php`
  - `History.php` → `HistoryController.php`
  - `Reports.php` → `ReportsController.php`
  - `Upload.php` → `UploadController.php`
  - `UserAuth.php` → `UserAuthController.php`
- **Models renamed** to singular PSR-4 convention:
  - `ModBudget` → `BudgetModel`, `ModDevices` → `DeviceModel`,
    `ModGoals` → `GoalModel`, `ModInsights` → `InsightModel`,
    `ModNotes` → `NoteModel`, `ModRecurring` → `RecurringPaymentModel`,
    `ModTags` → `TagModel`, `ModUploads` → `UploadModel`,
    `ModUser` → `UserModel`, `ModUserSettings` → `UserSettingModel`.
- **`ModCryption` → `CryptoHelper`** — secure dynamic IV generation added;
  IV is now generated per-encryption and transmitted alongside the ciphertext.
- Upload payload directory standardized: `txt_loot/` → `uploads/payloads/`.
- `app/Config/Routes.php` fully rewritten for RESTful resource layout.
- Admin notifications controller simplified; email delivery delegated to
  `Notifier` library.
- Layouts (`admin.php`, `superadmin.php`) enhanced with improved navigation
  structure, sidebar version display, and breadcrumb consistency.
- Setup guide (`Views/setup.php`) corrected to accurately describe token
  generation and device linking flow.
- Dashboard and transaction views updated to reference renamed controllers.
- `UploadModel` extended with new payload-directory and dedup fields.

### Removed
- `Controllers/Analyse.php` — superseded by `AnalysisCallbackController.php`.
- `Controllers/Testar.php` — development/testing controller removed.
- `Controllers/Admin/Notifications.php` — trimmed; logic moved to Notifier.

### Fixed
- `Commands/LlmProcess.php` retry logic corrected.
- `Commands/DataRetention.php` and `Commands/UploadsCleanup.php` minor fixes.
- Blocklist status view enhanced with stats accordion and improved UX.
- Admin Logs, Backup, DbInfo, and Maintenance views improved for clarity.
- Analyze button now presents a confirmation modal explaining the rules engine
  before triggering classification.

---

## [3.2.0] — 2026-08-11

### Added
- Blocklist status dashboard with stats accordion and self-healing job logs.
- ML allowed-senders management and job statistics panel.
- Retention cron job for automated data housekeeping.
- Admin canonical SMS analysis UI and job-status views.
- Per-user SMS stats endpoint.

---

## [3.1.0] — 2026-08-05

### Added
- Device tracking UI (user & admin pages with audit, tokens, and activity).
- Upload pipeline: `user_id` stamping, SMS ownership resolution, audit calls.
- Device fingerprint fields, `user_id` columns, and dedup keys in DB.

---

## [3.0.0] — 2026-07-20

### Added
- HTML email overhaul with rich transactional templates.
- Robust user-data deletion flow (GDPR-aligned).
- Admin system tools (backup, DB info, maintenance mode).
- Blocklist, ML allowed-senders, and job stats features.
- Complete migration to one-table-per-migration pattern.
