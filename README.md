# TCventory

TCventory ist eine geplante Webanwendung zur Inventarverwaltung von Trading-Card-Game-(TCG)-Karten und Sealed-Produkten. Das Projekt ist als Laravel-12-Anwendung mit Filament-Admin, Livewire und responsivem UI konzipiert.

## Projektbeschreibung

Ziel von TCventory ist die zentrale Verwaltung von:

- Inventarisierung und Katalogisierung von Karten/Produkten
- wirtschaftlichem Tracking (Einkauf, aktueller Wert, Verkauf inkl. Versand und Gebühren)
- Zustands- und Grading-Informationen pro Bestandseinheit
- audit-sicherer Historie aller relevanten Bestandsänderungen

Detaillierte fachliche und technische Spezifikation:

- `docs/technische-spezifikation.md`
- `docs/projektstruktur.md`
- `PROJECT_OVERVIEW.md`
- `API_DOCS.md`
- `ROADMAP.md`
- `docs/phase1-bootstrap.md`
- `PROGRESS.md`

## Installation

### Voraussetzungen

- PHP 8.4+
- Composer
- Node.js + npm
- PostgreSQL (empfohlen) oder MySQL
- Redis (für Queue/Cache)
- Optional: Meilisearch

### Projekt lokal aufsetzen

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Danach Datenbankzugang, Redis und weitere Umgebungsvariablen in `.env` eintragen.

### Datenbank und lokale Entwicklung starten

```bash
php artisan migrate
php artisan serve
npm run dev
```

Optional Queue-Worker/Horizon starten:

```bash
php artisan queue:work
php artisan horizon
```

## Setup

Empfohlene Basiskonfiguration:

1. Authentifizierung und Rollenmodell (Admin, Inventory Manager, Sales Manager, Viewer) vorbereiten
2. Stammdaten initial anlegen:
   - TCG Games
   - Sets
   - Produkte (Card/Sealed)
3. Lagerorte (`storage_locations`) anlegen
4. Inventar erfassen und Bewegungs-/Audit-Logging aktiv nutzen

## Architektur (Kurzüberblick)

Das System folgt einer modularen Schichtenarchitektur:

- **Presentation Layer**: Filament, Livewire, Blade, Tailwind, Alpine
- **Application Layer**: Services/Actions/Policies für Use Cases
- **Domain Layer**: Eloquent-Modelle, Value Objects, Domänenregeln
- **Infrastructure Layer**: DB, Redis, Queue/Horizon, optional Meilisearch, Sentry

Weitere Details in `PROJECT_OVERVIEW.md`.

## API (Kurzüberblick)

Versionierte API unter `/api/v1` für Integrationen.

Aktuell implementiert:

- `GET /api/v1/health`

Geplante Endpunkte für Auth, Katalog, Inventar, Einkauf/Bewertung/Verkauf, Audit und Reporting sind in `API_DOCS.md` dokumentiert.

## Entwicklung

### Empfohlene Tools

- Testing: Pest, PHPUnit
- Qualität: PHPStan, Laravel Pint, Rector
- Monitoring: Sentry

### Projektstruktur

Die Zielstruktur ist in `docs/projektstruktur.md` dokumentiert.

### Nützliche Start-Kommandos

```bash
php artisan test
vendor/bin/pint
vendor/bin/phpstan analyse
```

## Status

Das Repository enthält derzeit eine initiale Basis mit Health-/Status-Routen und Dokumentation als technische Grundlage.
