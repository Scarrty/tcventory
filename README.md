# TCventory (Laravel 12)

## Dokumentationsstatus

- Stand: 2026-03-12
- Diese Datei wurde im Rahmen der projektweiten Dokumentationspflege auf den aktuellen Implementierungsstand synchronisiert.

TCventory ist eine API- und Backoffice-Anwendung für TCG-Katalog, Inventar und Finanzprozesse.

## Aktueller Stand

- **Phase 0/1:** abgeschlossen (Plattform, Auth, RBAC, Migrationen, Filament-Basis)
- **Phase 2:** abgeschlossen (Katalog/Inventar-CRUD inkl. Transfer und Stock-Adjust)
- **Phase 3:** abgeschlossen (Purchases, Sales, Valuations, erweitertes Finance Summary Reporting)
- **Phase 4:** gestartet (Audit-Hash-Chain für Finance-Write-Flows inkl. Verifikationskommando)
- **Phase 5:** geplant (Skalierung/Integrationen)

Siehe: `docs/README.md` (Dokumentationslandkarte), `ROADMAP.md` (Plan), `PROGRESS.md` (Live-Ist).

## Dokumentation (Lesepfade)

1. `README.md` (Quickstart)
2. `PROJECT_OVERVIEW.md` (Architektur & Scope)
3. `API_DOCS.md` (API-Kontrakte)
4. `ROADMAP.md` + `PROGRESS.md` (Soll vs. Ist)

Spezifikationen nach Zielgruppe:

- `spec.md`: Setup-/Reproduzierbarkeitsleitfaden für Codex-Instanzen
- `docs/technische-spezifikation.md`: technische Systemspezifikation für Engineering

## Kernfunktionen (implementiert)

- API v1 unter `/api/v1`
- Health-Endpoint: `GET /api/v1/health`
- Katalog-CRUD: `games`, `sets`, `products`
- Inventar-CRUD + Aktionen: `inventory-items`, `transfer`, `adjust-stock`
- Finance-Module: `purchases`, `sales`, `valuations`
- Finance-Report: `GET /api/v1/reports/finance-summary` (periodisiert, channel-filterbar, KPI-Breakdown)
- Token-basierte API-Authentifizierung via Sanctum (`POST /api/v1/tokens`, `GET /api/v1/me`)
- Audit-Integrität: `php artisan audit:verify-chain`

## Voraussetzungen

- PHP `^8.4`
- Composer `^2`
- Node.js `22 LTS` + npm
- Datenbank: PostgreSQL (empfohlen) oder SQLite für lokale Minimal-Setups
- Optional: Redis

## Lokales Setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

### Datenbank (SQLite-Beispiel)

```bash
touch database/database.sqlite
php artisan migrate
```

### Optionales Seeding

```bash
php artisan db:seed --class=Database\\Seeders\\RolesAndPermissionsSeeder
```

### Starten

```bash
php artisan serve
npm run dev
```

## Tests & Qualität

```bash
php artisan test
vendor/bin/pint --test
vendor/bin/phpstan analyse --memory-limit=1G
```

## Docker & Deployment

Schnellstart mit Docker Compose:

```bash
cp .env.docker.example .env.docker
# APP_KEY in .env.docker setzen
IMAGE_TAG=latest docker compose --env-file .env.docker pull
IMAGE_TAG=latest docker compose --env-file .env.docker up -d
```

Details: `docs/deployment/docker-compose-v2.md`.

Release-Dokumentationsprozess: `docs/releases/README.md`.
