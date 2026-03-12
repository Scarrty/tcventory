# PROJECT_OVERVIEW

## Dokumentationsstatus

- Stand: 2026-03-12
- Architekturüberblick auf den aktuellen Repository-Stand synchronisiert.

## 0. Dokumentgrenzen

- **Dieses Dokument** beschreibt Architektur, technische Module und nicht-funktionale Anforderungen.
- Produktvision: `VISION.md`
- Lieferplanung: `ROADMAP.md`
- Live-Fortschritt: `PROGRESS.md`

## 1. Ziel und Scope

**TCventory** ist eine Webanwendung zur Verwaltung von TCG-Karten und Sealed-Produkten.

Kernziele:

- strukturierte Inventarisierung und Katalogisierung
- wirtschaftliches Tracking (Einkauf, Wertentwicklung, Verkauf, Gebühren)
- Zustands- und Grading-Verwaltung auf Einzelobjekt-Ebene
- revisionsnahe Historisierung kritischer Finanz- und Bestandsänderungen

## 2. Plattform und Tech Stack

### Backend

- PHP 8.4+
- Laravel 12
- Eloquent ORM

### Frontend

- Filament Admin Framework
- Livewire 3
- Alpine.js
- Tailwind CSS
- Blade

### Datenhaltung und Infrastruktur

- PostgreSQL (empfohlen)
- SQLite (lokal/minimal)
- Redis (optional)
- Docker Compose Setups für Betrieb/Entwicklung

### Build- und Entwickler-Tooling

- Node.js + Vite
- PHPUnit via `php artisan test`
- PHPStan + Laravel Pint

## 3. Architektur

Die Anwendung folgt einer schichtenorientierten Laravel-Struktur mit Services für transaktionale Use-Cases.

### 3.1 Presentation Layer

- Filament-Panel für Backoffice-Flows
- Blade/Livewire/Alpine/Tailwind für UI
- API-Endpunkte unter `routes/api.php`

### 3.2 Application Layer

- Controller + FormRequests für API-Kontrakte
- Domänennahe Services für Lösch-, Transfer-, Adjust- und Audit-Flows
- Policies/RBAC für Autorisierung

### 3.3 Domain Layer

- Eloquent-Modelle als Domänenentitäten
- Geschäftsregeln für Bestand, Transaktionen und Reporting
- Idempotenz (`request_key`) für kritische Finance- und Inventory-Write-Flows

### 3.4 Infrastructure Layer

- Relationale Datenbank inkl. Audit-/Ledger-Tabellen
- Artisan-Kommandos für Betriebs-/Integritätschecks (z. B. Audit-Chain-Verifikation)
- Optionaler Ausbau für Queue/Monitoring

## 4. Kernmodule

1. **Catalog Module** (`games`, `sets`, `products`)
2. **Inventory Module** (`storage_locations`, `inventory_items`, Transfers/Korrekturen)
3. **Finance Module** (`purchases`, `sales`, `valuations`, finance summary report)
4. **Audit Module** (verkettete Audit-Events für kritische Finance-Write-Flows)
5. **Reporting Module** (KPIs + channel/period Filter im Finance Summary)

## 5. Datenmodell (Überblick)

Zentrale Entitäten:

- `users` + Rollen/Berechtigungen
- `games`, `sets`, `products`
- `storage_locations`, `inventory_items`, `inventory_movements`
- `purchases`, `purchase_items`
- `sales`, `sale_items`
- `valuations`
- `audit_events`

Audit-Prinzip (Phase-4-Einstieg):

1. fachliche Write-Operation
2. transaktionaler Abschluss
3. append-only Audit-Event mit `previous_hash` → `event_hash`

## 6. Nicht-funktionale Anforderungen

- **Auditierbarkeit** via verketteter Audit-Events
- **Konsistenz** über transaktionale Kernprozesse
- **Sicherheit** via RBAC, Validierung und Framework-Schutz
- **Wartbarkeit** über tests + statische Analyse + konsistente API-Kontrakte

## 7. Aktueller Implementierungsstand

Unter `/api/v1` sind produktiv umgesetzt:

- Health/Access: `GET /health`, `POST /tokens`, `GET /me`
- Catalog API: `games`, `sets`, `products` (index/store/show/update/destroy)
- Inventory API: `inventory-items` (index/store/show/update/destroy)
- Inventory Actions: `POST /inventory-items/{inventory_item}/transfer`, `POST /inventory-items/{inventory_item}/adjust-stock`
- Finance API: `purchases`, `sales`, `valuations` (index/store)
- Reporting API: `GET /reports/finance-summary` (period/channel/grouping)

Zusätzlich vorhanden:

- Audit-Hash-Chain für Finance-Write-Flows
- Integritätsprüfung per `php artisan audit:verify-chain`
