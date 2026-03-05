# PROJECT_OVERVIEW

## 1. Ziel und Scope

**TCventory** ist eine Webanwendung zur Verwaltung von TCG-Karten und Sealed-Produkten.

Kernziele:

- strukturierte Inventarisierung und Katalogisierung
- wirtschaftliches Tracking (Einkauf, Wertentwicklung, Verkauf, Gebühren)
- Zustands- und Grading-Verwaltung auf Einzelobjekt-Ebene
- revisionssichere Historisierung von Bestandsänderungen

## 2. Plattform und Tech Stack

### Backend

- PHP 8.4+
- Laravel 12
- Composer
- Eloquent ORM

### Frontend

- Filament Admin Framework
- Livewire 3
- Alpine.js
- Tailwind CSS
- Blade

### Datenhaltung und Infrastruktur

- PostgreSQL (empfohlen), MySQL (unterstützt)
- Redis (Cache/Queue/Locks)
- Laravel Horizon (Queue Monitoring)
- Meilisearch (optional)
- Sentry (Monitoring)

### Build- und Entwickler-Tooling

- Node.js + Vite
- Pest + PHPUnit
- PHPStan + Laravel Pint + Rector

## 3. Architektur

Die Anwendung folgt einer modularen, schichtenorientierten Architektur.

### 3.1 Presentation Layer

- Filament Panels für Backoffice-Flows
- Livewire-Komponenten für interaktive Prozesse
- Blade/Tailwind/Alpine für UI

### 3.2 Application Layer

- Use-Case-Services (z. B. Inventory, Valuation, Sales)
- Action-/Command-Klassen für transaktionale Prozesse
- Policies für Autorisierung

### 3.3 Domain Layer

- Eloquent-Modelle als Domänen-Aggregate
- Value Objects für Zustände/Preiswerte
- Domänenregeln für Bestand, Bewertung, Gebühren und Audit-Events

### 3.4 Infrastructure Layer

- relationale DB für persistente Daten
- Redis + Queue Worker + Horizon für asynchrone Aufgaben
- optional Suchindex über Meilisearch
- Observability über Sentry und Logging

## 4. Kernmodule

1. **Catalog Module**
   - Games, Sets, Produkte
   - Importmöglichkeiten (CSV/API)
2. **Inventory Module**
   - Bestand, Lagerorte, Zustände, Grading
   - Transfers/Bestandsanpassungen
3. **Finance Module**
   - Einkauf/Verkauf, Gebühren, Marge
   - Bewertungsverlauf
4. **Audit Module**
   - unveränderliche Ereignishistorie
   - Nachvollziehbarkeit von Bestandsänderungen
5. **Reporting Module**
   - Bestandswert, Umschlag, P/L
6. **Search Module (optional)**
   - Volltext und facettierte Filter

## 5. Datenmodell (Überblick)

Zentrale Entitäten:

- `users`, Rollen/Berechtigungen
- `tcg_games`, `sets`, `products`
- `inventory_items`, `storage_locations`
- `purchases`, `purchase_lines`
- `sales`, `sale_lines`
- `valuations`
- `inventory_movements`
- `audit_events`

Audit-Prinzip:

1. fachliche Änderung
2. Ledger-Bewegung (`inventory_movements`)
3. append-only Audit-Event mit Hash-Verkettung

## 6. Nicht-funktionale Anforderungen

- **Auditierbarkeit** durch unveränderliche Ereignisse
- **Konsistenz** via transaktionale Kernprozesse
- **Performance** (u. a. asynchrone Bulk-/Repricing-Prozesse)
- **Sicherheit** (RBAC, Framework-Schutzmechanismen)
- **Skalierbarkeit** (horizontale Web-/Worker-Skalierung)

## 7. Aktueller Implementierungsstand

Aktuell sind im Projekt grundlegende Health-/Status-Routen vorhanden:

- `GET /` (Web-Status)
- `GET /api/v1/health` (API-Status)

Die fachliche Architektur und der Zielumfang sind in der technischen Spezifikation ausformuliert und in den neuen Top-Level-Dokumenten strukturiert zusammengefasst.
