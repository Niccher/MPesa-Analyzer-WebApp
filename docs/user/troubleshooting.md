# Troubleshooting Guide (Operator) — Mpesa Analyzer WebApp

Common runtime problems and solutions for operators running the WebApp container.

---

## 1. WebApp Returns 500 or Blank Screen

**Symptom**: `http://localhost:9002` displays a 500 server error or blank screen.

**Remedy**:
1. Check container logs:
   ```bash
   docker compose logs mpesa-analyzer
   ```
2. Verify directory permissions on `writable/`:
   ```bash
   chmod -R 777 writable/
   ```
3. Check `writable/logs/` for the latest CodeIgniter error trace.

---

## 2. Port Conflict on 9002, 9000, or 9306

**Symptom**: `bind: address already in use` during `docker compose up`.

**Remedy**:
Edit host port mappings in `docker-compose.yml`:
- Change `9002:80` to `9003:80` (WebApp)
- Change `9000:80` to `9001:80` (phpMyAdmin)
- Change `9306:3306` to `9307:3306` (MySQL)

---

## 3. Database Migration Blocked on Startup

**Symptom**: Container repeatedly outputs `Waiting for MySQL to accept connections...`.

**Remedy**:
1. Inspect MySQL container health:
   ```bash
   docker compose ps mysql
   docker compose logs mysql
   ```
2. Confirm the `mysql-data` volume is not corrupted. If resetting dev data is acceptable:
   ```bash
   docker compose down -v
   docker compose up --build -d
   ```
