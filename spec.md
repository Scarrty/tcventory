# TCventory – Spezifikation & Setup-Guide für Codex-Instanzen

## 1) Zweck dieses Dokuments
Dieses Dokument beschreibt, wie eine **andere Codex-Instanz** das Projekt TCventory reproduzierbar

1. lokal aufsetzt,
2. für Tests vorbereitet,
3. und für Deployment baut.

Ziel ist ein standardisierter, automationsfreundlicher Ablauf ohne implizites Wissen.

---

## 2) Projektziel (Kurzfassung)
TCventory ist eine Web-App zur Inventarverwaltung von TCG-Karten und Sealed-Produkten mit Fokus auf:

- Inventarisierung und Katalogisierung,
- wirtschaftliches Tracking (Einkauf, aktueller Wert, Verkauf inkl. Gebühren/Versand),
- Zustands- und Grading-Management,
- audit-sichere Historie von Bestandsänderungen.

---

## 3) Zielplattform & Architektur

### Plattform
- Web-App (Desktop + responsive Mobile)

### Geplanter Stack

**Backend**
- PHP 8.4+
- Laravel 12
- Composer
- Eloquent ORM

**Frontend**
- Filament Admin Framework
- Livewire 3
- Alpine.js
- Tailwind CSS
- Blade Templates

**Datenbanken**
- PostgreSQL (empfohlen)
- MySQL (unterstützt)

**Infra / Async / Search**
- Redis
- Laravel Horizon
- Meilisearch (optional)

**Build / QA**
- Node.js + npm
- Vite
- Pest + PHPUnit
- PHPStan
- Laravel Pint
- Rector

**Monitoring**
- Sentry

---

## 4) Voraussetzungen für eine frische Codex-Instanz

Die Instanz sollte folgende Tools verfügbar haben:

- `git`
- `php` (>= 8.4)
- `composer`
- `node` + `npm`
- `psql` **oder** `mysql` Client (je nach DB)
- optional: `redis-cli`, `meilisearch`

### Quick-Check

```bash
php -v
composer --version
node -v
npm -v
```

---

## 5) Standard-Setup (lokal / CI-kompatibel)

> Alle Befehle im Repository-Root ausführen.

### 5.1 Repository klonen

```bash
git clone <REPO_URL> tcventory
cd tcventory
```

### 5.2 PHP- und Node-Abhängigkeiten installieren

```bash
composer install
npm install
```

### 5.3 Umgebungsdatei anlegen und App-Key erzeugen

```bash
cp .env.example .env
php artisan key:generate
```

### 5.4 `.env` konfigurieren (mindestens)

Folgende Variablen müssen konsistent gesetzt werden:

- `APP_NAME=TCventory`
- `APP_ENV=local` (für lokale Instanzen)
- `APP_DEBUG=true` (nur lokal)
- `APP_URL=http://localhost:8000`
- `DB_CONNECTION=pgsql` (oder `mysql`)
- `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- `CACHE_STORE=redis` (oder Datei-Cache für Minimal-Setup)
- `QUEUE_CONNECTION=redis`
- `SESSION_DRIVER=database` oder `redis`
- optional: Meilisearch- und Sentry-Variablen

### 5.5 Datenbank vorbereiten

```bash
php artisan migrate
```

Wenn Seed-Daten vorhanden sind:

```bash
php artisan db:seed
```

### 5.6 Lokale Laufzeit starten

In separaten Prozessen:

```bash
php artisan serve --host=0.0.0.0 --port=8000
npm run dev
```

Optional (Queue/Jobs):

```bash
php artisan queue:work
php artisan horizon
```

---

## 6) Test-Setup für Codex-Automation

Vor jedem Testlauf:

```bash
php artisan config:clear
php artisan cache:clear
```

### Standard-Testlauf

```bash
php artisan test
```

### Optionale Qualitätsprüfungen

```bash
vendor/bin/pint --test
vendor/bin/phpstan analyse
```

### Empfohlene Reihenfolge in CI

1. `composer install --no-interaction --prefer-dist`
2. `npm ci`
3. `.env` erstellen + `php artisan key:generate`
4. `php artisan migrate --force`
5. `php artisan test`
6. `vendor/bin/pint --test`
7. `vendor/bin/phpstan analyse`

---

## 7) Deployment-Spezifikation (generisch)

Diese Schritte sind Host-unabhängig (VM, Container, PaaS).

### 7.1 Build-Phase

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

### 7.2 Server-Konfiguration

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_KEY` gesetzt
- Produktions-DB + Redis erreichbar
- Schreibrechte auf:
  - `storage/`
  - `bootstrap/cache/`

### 7.3 Laravel-Optimierung & Migration

```bash
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 7.4 Prozessmanagement

Empfohlen:

- Webserver: Nginx + PHP-FPM
- Queue-Worker via Supervisor/Systemd
- Optional: Horizon als eigener Prozess

### 7.5 Deployment-Checkliste

- Health-Endpoint liefert 200 (`/api/v1/health`)
- DB-Verbindung erfolgreich
- Queue verarbeitet Jobs
- Logs ohne kritische Fehler
- Frontend-Assets aus `npm run build` korrekt ausgeliefert

---

## 8) Minimaler Smoke-Test nach Setup

Nach lokalem Start:

1. Browser: `http://localhost:8000`
2. API-Check: `GET /api/v1/health`
3. Artisan-Test: `php artisan test`

Wenn diese drei Punkte erfolgreich sind, gilt die Instanz als "testbereit".

---

## 9) Hinweise für weitere Codex-Instanzen

- Vor Änderungen immer den aktuellen Branch prüfen.
- Änderungen an Setup/Deployment synchron in `README.md` und `spec.md` halten.
- Für reproduzierbare Builds in CI möglichst feste Versionen von PHP/Node verwenden.
- Bei optionalen Services (Meilisearch, Sentry) stets Fallback dokumentieren, damit Testläufe nicht blockieren.
