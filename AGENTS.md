# AGENTS.md

Aplikasi pencatatan infak madrasah: Laravel 12 JSON API + Vue 3 SPA (Vite/Tailwind), deploy manual zip ke cPanel.

## Commands

```bash
composer dev     # serve + queue:listen + pail + vite (concurrently)
composer test    # config:clear + artisan test  (alias: php artisan test)
npm run dev      # vite dev server
npm run build    # vite build -> public/build
composer setup   # fresh install: composer + .env + key + migrate + npm + build
```

No linter/typechecker script exists for JS. PHP has `./vendor/bin/pint` (Laravel preset).

## Verify before finishing

1. `php artisan test` — currently only 2 example tests, must stay green. Uses in-memory sqlite (`phpunit.xml`), no services needed.
2. `npm run build` — must succeed (~8s).
3. **Do not run `pint` to "clean up"**: ~23 files currently fail `pint --test`, and CI never runs it. Auto-fixing would create a huge unrelated diff. Only format files you touched, and only if asked.

## Routing — read this before adding endpoints

- Two parallel route files, both live: `routes/api.php` (all real endpoints, `auth:sanctum`) and `routes/web.php` (a duplicated subset using session `auth` + login/logout/user). New endpoints go in `routes/api.php`.
- `bootstrap/app.php` enables `statefulApi()`, so `auth:sanctum` falls back to the session guard — no Bearer tokens in the SPA, just cookies + `GET /sanctum/csrf-cookie`.
- `routes/web.php` ends with catch-all `GET /{any}` → SPA. It must stay last.
- Because of closure routes, **never suggest `php artisan route:cache`** — CI deliberately skips it (it would strand the site in maintenance mode).
- `GET /api/setup-kelas` and `GET /api/delete-dummy-bambim` are unauthenticated dev/seed helpers in `routes/api.php`.

## Auth & roles

- Two roles stored inline on `users.role`: `admin` (everything) and `guru` (only classes where `kelas.guru_id = user.id`).
- There is **no role middleware, gate, or policy**. Authorization is inline in controllers: `$request->user()->role === 'guru'` filters queries. Follow the same pattern (or introduce a middleware only if asked).
- Vue Router guard is client-side only (`localStorage.user`) — always enforce permissions server-side too.

## Domain conventions

- **Academic year, not calendar year**: `infaks.tahun` stores the academic year (July–June). Always use `AcademicYear::current()` (`app/AcademicYear.php`), never `now()->year`.
- **WhatsApp is outbox-only**: never call Fonnte (`app/Services/WhatsAppService.php`) inline from a controller. Queue rows into `wa_outbox` (`WaOutbox`), then the browser drains them via `POST /wa/process` (`resources/js/src/utils/waOutbox.js`), honoring the server's `retry_after` (`FONNTE_DELAY`, default 60s — prevents number bans). Blast dedup uses `group_key` (e.g. `tunggakan-YYYY-MM`). `WhatsAppService::queue()` never throws — outbox write failures are logged, never block a payment save.
- **WA message texts are hardcoded in `InfakController`** — no lang files/DB: thank-you in `store()` and `syncBySiswa()`, arrears blast in `blastReminder()`. Edit there (keep the `*bold*` markers — Fonnte markdown).
- **WA stays dormant when `FONNTE_TOKEN` is empty**: `queue()` does not write outbox rows at all, and `process()` returns early leaving existing rows `pending` — fill the token + `php artisan config:cache` to (re)activate. Transient send failures (network/5xx/429) keep rows `pending` for the next drain; only permanent 4xx errors mark them `failed`. `FONNTE_DELAY` is optional — `config/services.php` defaults it to 60.
- Money/expense mutations should write a `LogAktivitas` row (see `PengeluaranController`, `InfakController`).
- Role-scoped queries belong in `DashboardController::getKelas`-style helpers; guru sees only own classes.

## Frontend (`resources/js/src/`)

- `axios.defaults.baseURL` in `resources/js/app.js` is **hardcoded to `https://assajjad.web.id/api`** — local UI talks to production. Change it (or move to `import.meta.env.VITE_*`) when testing against a local backend.
- Views live in `views/admin/` and `views/guru/`; routes are declared centrally in `src/router/index.js`. `stores/` and `services/` are empty — Pinia is installed but unused; don't assume a store exists.
- Tailwind is **v3 via `postcss.config.js` + `tailwind.config.js`** (custom Material-3-ish palette, `darkMode: "class"`). `@tailwindcss/vite` v4 sits unused in `package.json` — don't wire it in or "upgrade" without being asked.
- PWA pieces: `public/manifest.json`, `public/sw.js`. VAPID public key is hardcoded in `src/utils/pushHelper.js` (private key lives in `.env`).

## Deploy / environment

- **The usual flow is MANUAL** (the user does this): build `deploy.zip` → upload to cPanel File Manager → Extract → run the artisan commands below in cPanel Terminal. The GitHub Actions workflow (`.github/workflows/deploy.yml`) was removed in Oct 2026 (its SCP step kept timing out; the user is moving to Jenkins) — no CI runs on push until a `Jenkinsfile` is added.
- Rebuild the zip **after any code change** (the one at the repo root is a stale snapshot):
  ```bash
  npm run build
  zip -qr deploy.zip . -x "*.git*" -x "node_modules/*" -x "tests/*" -x ".env" -x "*.zip" \
    -x "cookie*.txt" -x "response.json" -x "*.log" -x "composer.phar" -x "composer-2.2.phar" \
    -x "database/*.sqlite" -x ".phpunit.result.cache" -x ".phpunit.cache/*" -x ".github/*" \
    -x "public/hot" -x "storage/logs/*" -x "storage/framework/sessions/*" -x "storage/framework/cache/data/*"
  ```
  It must contain `vendor/` and `public/build/`; it must NOT contain `.env` (the server keeps its own).
- **After extract, these are mandatory** — skipping them means stale views/config or missing tables (e.g. `wa_outbox` → every payment 500s):
  ```bash
  php artisan migrate --force
  php artisan optimize:clear && php artisan config:cache && php artisan view:cache
  ```
- Migrations only run when someone executes them — make them forward-safe/idempotent; data-fix migrations already exist in `database/migrations/`.
- PHP compat: `composer.json` pins `platform.php = 8.2.0` and the server runs PHP 8.2. Local CLI here is PHP 8.5 — avoid 8.5-only syntax; expect `PDO::MYSQL_ATTR_SSL_CA` deprecation noise.
- Local `.env` is production-like (`APP_ENV=production`, `APP_DEBUG=true`, mysql DB `u1zh8984_assajjad@localhost`) — it is NOT a dev config, and it never reaches the server (zip excludes it). The real prod `.env` exists only on cPanel. `php artisan test` is safe: `phpunit.xml` forces sqlite `:memory:`.
- Prod DB is MySQL (`assajjad.web.id`), dev default is sqlite (`database/database.sqlite`).
- `.gitignore` covers `*.zip`, `*.txt`, `response.json`. The old archive/hotfix zips at the repo root were deleted (Oct 2026); only `deploy.zip` remains — never commit any of them.
- Seeders in `database/seeders/` contain plaintext demo credentials (also used in prod).
