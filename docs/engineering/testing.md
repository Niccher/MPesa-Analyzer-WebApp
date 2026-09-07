# Testing Guide — Mpesa Analyzer WebApp

Unit and feature tests use PHPUnit configured in `phpunit.xml.dist`.

---

## 1. Running Tests

### Via Docker
```bash
docker compose exec mpesa-analyzer vendor/bin/phpunit
```

### Native Host
```bash
vendor/bin/phpunit
```

---

## 2. Test Structure

Tests reside in `tests/`:
- **Unit Tests**: Test custom helpers (`mpesa_date_helper`), libraries (`ProgressCalculator`), and data models.
- **Feature Tests**: Test HTTP route responses, Shield authentication filters, and API endpoints using CodeIgniter 4 `FeatureTestCase`.
