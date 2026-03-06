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

- PHP `^8.2`
- Composer `^2`
- Node.js + npm (optional, für Frontend-Build)

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
