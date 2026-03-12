# TCventory – Spezifikation & Setup-Guide für Codex-Instanzen


## Dokumentationsstatus

- Stand: 2026-03-12
- Diese Datei wurde im Rahmen der projektweiten Dokumentationspflege auf Aktualität geprüft und sprachlich vereinheitlicht.

## 1) Zweck dieses Dokuments
Dieses Dokument beschreibt, wie eine weitere Codex-Instanz TCventory reproduzierbar

1. lokal aufsetzt,
2. testet,
3. und für Deployment vorbereitet.

Zusätzlich enthält es einen kompakten Ist-Stand der Implementierung, damit Setup und Produktrealität konsistent bleiben.

## 1a) Audience
- Primär: Codex-/Automations-Instanzen und Maintainer für reproduzierbare Setup- und QA-Abläufe.
- Sekundär: Entwickler:innen, die schnell einen lauffähigen Stand herstellen wollen.

## 1b) Non-goals
- Keine vollständige Domänen-/Architekturspezifikation.
- Keine langfristige Produkt- oder Roadmap-Strategie.
- Kein Ersatz für `docs/technische-spezifikation.md` (Engineering-Spezifikation).

---

## 2) Projektziel (Kurzfassung)
TCventory ist eine Web-App zur Verwaltung von TCG-Inventar und begleitenden Finanzdaten mit Fokus auf:

- Katalogisierung (Game/Set/Product),
- Bestandsführung (Inventory Items, Lagerorte, Zustandsdaten),
- Finanzflüsse (Einkauf, Verkauf, Bewertung),
- nachvollziehbare Bestandsänderungen (Audit/Ledger als Zielbild).

---

## 3) Zielplattform & Architektur

### Plattform
- Web-App (Backoffice-first, responsive nutzbar)

### Stack

**Backend**
- PHP 8.4+
- Laravel 12
- Sanctum + Policies/RBAC

**Frontend/Admin**
- Filament
- Livewire 3
- Alpine.js
- Tailwind CSS

**Daten & Infrastruktur**
- PostgreSQL (empfohlen)
- Redis (optional, empfohlen für Queue/Cache)
- Horizon (optional, Betriebsausbau)
- Meilisearch (optional)

**Build/QA**
- Node.js + npm + Vite
- PHPUnit/Pest via `php artisan test`
- Laravel Pint
- PHPStan

---

## 4) Implementierungsstatus (Stand: 2026-03-12)

### Bereits umgesetzt
- `/api/v1/health`
- API-CRUD für `games`, `sets`, `products`, `inventory-items` (inkl. Delete)
- Inventaraktionen: `transfer`, `adjust-stock`
- Finance-Endpunkte: `purchases`, `sales`, `valuations` (index/store)
- Aggregierter Report: `GET /api/v1/reports/finance-summary`
- API-Tokens: `POST /api/v1/tokens`, `GET /api/v1/me`

### Teilweise umgesetzt / offen
- Audit-Hash-Chain aktuell für Finance-Write-Flows integriert; Ausweitung auf weitere Domänen offen
- Zusätzliche dedizierte Reports (z. B. Inventory-Value, Profit/Loss)
- Weitergehende Integrationen und Search-Ausbau

---

## 5) Standard-Setup (lokal / CI-kompatibel)

> Alle Befehle im Repository-Root ausführen.

### 5.1 Abhängigkeiten installieren

```bash
composer install
npm install
```

### 5.2 Umgebung initialisieren

```bash
cp .env.example .env
php artisan key:generate
```

### 5.3 Datenbank vorbereiten

```bash
php artisan migrate
```

Optional:

```bash
php artisan db:seed
```

### 5.4 Lokale Laufzeit starten

```bash
php artisan serve --host=0.0.0.0 --port=8000
npm run dev
```

---

## 6) Test-Setup für Codex-Automation

Vor einem Testlauf (empfohlen):

```bash
php artisan config:clear
php artisan cache:clear
```

### Standard-Testlauf

```bash
php artisan test
```

### Qualitätsprüfungen

```bash
vendor/bin/pint --test
vendor/bin/phpstan analyse --memory-limit=1G
```

### Empfohlene CI-Reihenfolge

1. `composer install --no-interaction --prefer-dist`
2. `npm ci`
3. `.env` erstellen + `php artisan key:generate`
4. `php artisan migrate --force`
5. `php artisan test`
6. `vendor/bin/pint --test`
7. `vendor/bin/phpstan analyse --memory-limit=1G`

---

## 7) Deployment-Spezifikation (generisch)

### 7.1 Build-Phase

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

### 7.2 Runtime-Konfiguration

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_KEY` gesetzt
- DB/Redis erreichbar
- Schreibrechte auf `storage/` und `bootstrap/cache/`

### 7.3 Optimierung & Migration

```bash
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 7.4 Minimaler Produktions-Check

- `GET /api/v1/health` liefert 200
- zentrale API-Endpunkte antworten erwartungskonform
- Queue/Worker laufen ohne kritische Fehler

---

## 8) Synchronisationsregel für Doku

Bei Änderungen an Setup, API-Scope oder Delivery-Status sind mindestens diese Dateien gemeinsam zu aktualisieren:

- `README.md`
- `ROADMAP.md`
- `PROGRESS.md`
- `spec.md`
- `docs/technische-spezifikation.md`
