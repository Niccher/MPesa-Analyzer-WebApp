# Making Changes — Engineering Guide

Instructions for modifying controllers, routes, views, and migrations in the WebApp.

---

## 1. Task Navigator

| If you want to… | Files to modify |
|-----------------|-----------------|
| **Add a new API endpoint for Android** | 1. Define route in `app/Config/Routes.php` under `api/v1` group<br/>2. Add controller action in `app/Controllers/Api/V1/`<br/>3. Update API contract documentation in `docs/api/contract.md` |
| **Add a new Dashboard page** | 1. Add route in `app/Config/Routes.php`<br/>2. Create controller in `app/Controllers/`<br/>3. Create view in `app/Views/` |
| **Add a database table or column** | 1. Create migration: `php spark make:migration AddFieldToTable`<br/>2. Implement `up()` and `down()` in `app/Database/Migrations/`<br/>3. Update `docs/engineering/database.md` |
| **Modify ML Service interaction** | Edit `app/Config/MlBackend.php` and controller dispatchers in `app/Controllers/Admin/Ml.php` |

---

## 2. Definition of Done (DoD)

Before merging any change:

- [ ] Route registered with appropriate filter (`session` or `admin`).
- [ ] Database migration written if tables or columns changed.
- [ ] Views follow standard layout template (`app/Views/Layouts/`).
- [ ] Mobile API changes documented in `docs/api/contract.md`.
- [ ] Changes tested inside Docker environment (`docker compose up`).
