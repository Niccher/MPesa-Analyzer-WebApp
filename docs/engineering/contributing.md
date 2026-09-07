# Contributing Guidelines — Mpesa Analyzer WebApp

Guidelines for contributing code, views, and migrations to the WebApp repository.

---

## 1. Branching Strategy

- Develop all features on descriptive branches (`feature/budget-notifications`, `fix/upload-stream-handling`).
- Merge into `main` only via pull requests.

---

## 2. Pull Request Checklist

1. Verify migrations apply cleanly from empty database state:
   ```bash
   php spark migrate:refresh
   ```
2. Verify all views render without unhandled null pointer warnings.
3. Validate documentation conforms to `project-docs`:
   ```bash
   python3 /home/niccher/Downloads/readme-docs-skill/readme-docs-skill/scripts/lint-docs.py .
   ```
4. Include summary of updated API endpoints or views in the PR description.
