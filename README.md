# TCventory (Laravel 12)


## Dokumentationsstatus

- Stand: 2026-03-06
- Diese Datei wurde im Rahmen der projektweiten Dokumentationspflege auf Aktualität geprüft und sprachlich vereinheitlicht.

Dieses Repository enthält ein vollständiges Laravel-12-Projektgerüst mit migrierten, projektspezifischen TCventory-Dateien.

## Aktueller Stand

- **Phase 0/1:** abgeschlossen (Plattform, Auth, RBAC, Migrationen, Filament-Basis)
- **Phase 2:** weitgehend umgesetzt (API-CRUD für Katalog/Inventar inkl. Delete, Transfer, Stock-Adjust)
- **Phase 3:** teilweise umgesetzt (Purchases, Sales, Valuations, Finance Summary)
- **Phase 4/5:** vorbereitet bzw. geplant (Audit-Hash-Chain, erweiterte Reports, Integrationen)

Siehe auch: `ROADMAP.md`, `PROGRESS.md`, `docs/current-state-roadmap-review.md`.

## Kernfunktionen (implementiert)

- API v1 unter `/api/v1`
- Health-Endpoint: `GET /api/v1/health`
- Katalog-CRUD: `games`, `sets`, `products`
- Inventar-CRUD: `inventory-items`
- Inventar-Aktionen: `transfer`, `adjust-stock`
- Finance-Module: `purchases`, `sales`, `valuations`
- Finance-Report: `GET /api/v1/reports/finance-summary`
- Token-basierte API-Authentifizierung via Sanctum (`POST /api/v1/tokens`, `GET /api/v1/me`)

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

Empfehlung für Produktion:

- release-spezifischen Tag nutzen (`IMAGE_TAG=vX.Y.Z`)
- nach Deploy Migrationen ausführen
- Release-Artefakte und Rollback-Hinweise nutzen

Details: `docs/deployment/docker-compose-v2.md`.
