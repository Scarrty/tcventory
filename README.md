# TCventory (Laravel 12)

Dieses Repository enthält ein vollständiges Laravel-12-Projektgerüst mit migrierten, projektspezifischen TCventory-Dateien.

## Enthaltene Struktur (Auszug)

- `artisan`
- `composer.json`
- `app/Providers/`
- `bootstrap/`
- `config/`
- `database/migrations/`
- `routes/web.php`
- `routes/api.php`

## Voraussetzungen

- PHP `^8.4`
- Composer `^2`
- Node.js `22 LTS` + npm (optional, für Frontend-Build; Version ist in `.nvmrc` gepinnt)

## Reproduzierbares Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
```

### Datenbank vorbereiten (SQLite-Beispiel)

```bash
touch database/database.sqlite
php artisan migrate
```

### Optional: Rollen/Rechte seeden

```bash
php artisan db:seed --class=Database\\Seeders\\RolesAndPermissionsSeeder
```

### Entwicklungsserver starten

```bash
php artisan serve
```

### Tests ausführen

```bash
php artisan test
```

## CI/CD

Der Workflow [`.github/workflows/ci.yml`](.github/workflows/ci.yml) läuft bei Pull Requests und bei Pushes auf `main`.

Folgende Quality Gates sind definiert:

- `vendor/bin/pint --test`
- `vendor/bin/phpstan analyse`
- `php artisan test`

Für die Testausführung sind in CI Services für PostgreSQL (Datenbank) und Redis konfiguriert.

Zusätzlich läuft bei jedem veröffentlichten GitHub Release der CD-Workflow [`.github/workflows/release.yml`](.github/workflows/release.yml):

- Build + Push von `app` und `web` Docker-Images in GHCR
- Tagging mit `<release-tag>` und `latest`
- Erzeugung einer release-spezifischen `docker-compose.release.yml` als Release-Asset

## Docker & Deployment

Schnellstart mit Docker Compose:

```bash
cp .env.docker.example .env.docker
# APP_KEY in .env.docker setzen
IMAGE_TAG=latest docker compose --env-file .env.docker pull
IMAGE_TAG=latest docker compose --env-file .env.docker up -d
```

Empfehlung für Produktion:

- Nutze die `docker-compose.release.yml` aus dem jeweiligen Release.
- Verwende einen fixen Release-Tag (`IMAGE_TAG=vX.Y.Z`) statt dauerhaft `latest`.
- Führe nach jedem Deploy Migrationen aus.

Weitere Details inkl. Rollback-Strategie siehe:

- [`docs/deployment/docker-compose-v2.md`](docs/deployment/docker-compose-v2.md)

## Migrierte projektspezifische Dateien

- `database/migrations/2026_01_01_000001_create_catalog_tables.php`
- `database/migrations/2026_01_01_000002_create_inventory_tables.php`
- `database/migrations/2026_01_01_000003_create_finance_tables.php`
- `database/migrations/2026_01_01_000004_create_audit_and_ledger_tables.php`
- `database/seeders/RolesAndPermissionsSeeder.php`
- `app/Models/User.php`
- `routes/web.php`
- `routes/api.php`

## Hinweise zu Autoload/Namespaces

- PSR-4 bleibt auf Laravel-Standard:
  - `App\\` → `app/`
  - `Database\\Factories\\` → `database/factories/`
  - `Database\\Seeders\\` → `database/seeders/`
- Für das User-Modell mit Rollen/Rechten ist `spatie/laravel-permission` als Composer-Abhängigkeit enthalten.
