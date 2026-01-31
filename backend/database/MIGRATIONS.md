# Database Migrations

This project uses a tiny SQL migration runner to keep production and development schemas in sync.

## How it works

- SQL migrations live in `backend/database/migrations/`.
- Files are applied in **lexical order**.
- Applied migrations are recorded in `schema_migrations`.

## Run migrations

```bash
php backend/database/migrate.php
```

## Adding a migration

1. Create a new file in `backend/database/migrations/`:
   - Example: `20260201_120000_add_provider_fields.sql`
2. Put only **forward** SQL changes inside.
3. Run the migration runner.

## Production notes (cPanel)

- Prefer setting DB credentials via environment variables (or a private `.env` file not tracked by git).
- Never commit `backend/config/.env`.

## Verify deployment

To confirm the server is running the latest deployed code, call:

`GET /backend/api/v1/meta/version`

It returns the API version, server time, and (when available) the current git commit hash.
