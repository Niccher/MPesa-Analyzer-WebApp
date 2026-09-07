# Configuration Guide — Mpesa Analyzer WebApp

Configuration is managed via `.env` in the repository root.

---

## Environment Variables

| Variable | Service | Required | Default | Purpose |
|----------|---------|:--------:|---------|---------|
| `CI_ENVIRONMENT` | WebApp | No | `development` | CodeIgniter environment mode (`development` or `production`) |
| `app.baseURL` | WebApp | Yes | `http://localhost:9002/` | Base URL used for generating links and redirects |
| `database.default.hostname` | MySQL | Yes | `mysql` | Hostname of MySQL container on the Docker network |
| `database.default.database` | MySQL | Yes | `db_mpesa_analyzer` | Database schema name |
| `database.default.username` | MySQL | Yes | `root` | Database username |
| `database.default.password` | MySQL | Yes | `root_password` | Database password |
| `database.default.port` | MySQL | Yes | `3306` | MySQL port inside Docker network |
| `ML_BACKEND_URL` | WebApp | No | `http://ml-mpesa-analyzer:9050` | Internal network URL for the ML microservice |
| `MPESA_CRYPT_KEY` | WebApp | Yes | `a:r2yt>N3_\Py,f=` | AES-128 key matching the Android app BuildConfig |
| `MPESA_CRYPT_IV` | WebApp | Yes | `[M[@_w[F4a>yQsJW` | Fallback IV matching the Android app BuildConfig |
