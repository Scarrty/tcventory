# Technische Spezifikation – TCventory

## 1. Zielbild und Scope

**TCventory** ist eine Webanwendung zur Inventarverwaltung von TCG-Karten und Sealed-Produkten mit Fokus auf:

- strukturierte Inventarisierung und Katalogisierung,
- wirtschaftliches Tracking (Einkauf, aktueller Wert, Verkauf inkl. Versand/Gebühren),
- Qualitäts-/Zustandsmanagement inkl. Grading,
- revisionssicheres (audit-sicheres) Nachvollziehen aller Bestandsänderungen.

Die Lösung wird als **Laravel 12**-Anwendung mit **Filament Admin** (Backoffice-first), **Livewire 3** und responsivem UI umgesetzt.

---

## 2. Systemarchitektur

### 2.1 Architekturprinzip

Die Anwendung folgt einer modularen, schichtenorientierten Architektur:

1. **Presentation Layer**
   - Filament Admin Panels (Ressourcen, Tabellen, Formulare)
   - Livewire-Komponenten für interaktive Workflows
   - Blade + Tailwind CSS + Alpine.js für UI

2. **Application Layer**
   - Use-Case-orientierte Services (z. B. `InventoryService`, `ValuationService`, `SalesService`)
   - Command-/Action-Klassen für transaktionale Geschäftsprozesse
   - Policies für Autorisierung

3. **Domain Layer**
   - Eloquent-Modelle als Aggregat-Wurzel
   - Value Objects (Status, Condition, Money)
   - Domänenregeln für Bestand, Bewertungen, Gebühren, Audit-Events

4. **Infrastructure Layer**
   - PostgreSQL
   - Redis (Cache, Queues)
   - Horizon (Queue Monitoring)
   - Optional: Meilisearch für Volltextsuche
   - Sentry für Error Monitoring

### 2.2 Kontextdiagramm (logisch)

- **Nutzer (Admin, Inventory Manager, Sales Manager, Viewer)** greifen via Browser/Mobile auf TCventory zu.
- TCventory interagiert mit:
  - **PostgreSQL** (persistente Daten)
  - **Redis** (Queue, Cache, Locks)
  - **Queue Worker/Horizon** (asynchrone Jobs)
  - **Meilisearch** (optional)
  - **Sentry** (Logging/Alerting)

### 2.3 Laufzeit-Topologie (Deployment)

- **Web Node(s)**: Nginx + PHP-FPM + Laravel App
- **Worker Node(s)**: Laravel Queue Worker + Horizon
- **DB Node**: PostgreSQL (empfohlen)
- **Cache/Queue Node**: Redis
- **Optional Search Node**: Meilisearch

### 2.4 Non-Functional Requirements

- **Auditierbarkeit**: Jede relevante Bestandsänderung wird als unveränderlicher Event protokolliert.
- **Konsistenz**: Kritische Flows laufen transaktional.
- **Performance**:
  - P95 Read-Requests im Admin-Bereich < 500 ms (bei normaler Last)
  - Bulk-Imports/Exports und Repricing asynchron via Queue
- **Sicherheit**:
  - RBAC via Policies/Roles
  - Schutz vor CSRF/XSS/SQLi durch Framework-Defaults
  - verschlüsselte Secrets, regelmäßige Backups
- **Skalierung**:
  - horizontale Skalierung von Web/Worker möglich
  - DB-Indizes auf Such-/Filterfelder

---

## 3. Datenbankstruktur

> Ziel: normalisierte Struktur, klar trennbar zwischen Stammdaten (Katalog) und Bestand/Transaktion.

### 3.1 Kernentitäten

1. **users**
   - `id`, `name`, `email`, `password`, `is_active`, `last_login_at`, timestamps

2. **roles**, **permissions**, **model_has_roles**, **model_has_permissions**
   - für rollenbasierte Zugriffskontrolle

3. **tcg_games**
   - `id`, `name` (z. B. Pokémon, MTG), `slug`, timestamps

4. **sets**
   - `id`, `tcg_game_id`, `name`, `code`, `release_date`, timestamps

5. **products** (Katalogobjekte, polymorph: Card oder Sealed)
   - `id`, `tcg_game_id`, `set_id`, `product_type` (`card`|`sealed`), `name`, `number`, `rarity`, `language`, `meta_json`, timestamps

6. **inventory_items** (physische Bestandseinheit)
   - `id`, `product_id`, `owner_id`, `storage_location_id`,
   - `condition_code`, `grading_company`, `grade_value`,
   - `acquired_at`, `quantity`, `is_active`, timestamps

7. **storage_locations**
   - `id`, `name`, `type` (Binder/Box/Warehouse), `description`, timestamps

8. **purchases**
   - `id`, `vendor_name`, `purchased_at`, `currency`, `total_gross`, `shipping_cost`, `fees`, `notes`, timestamps

9. **purchase_lines**
   - `id`, `purchase_id`, `inventory_item_id`, `unit_price`, `quantity`, `line_total`, timestamps

10. **valuations**
    - `id`, `inventory_item_id`, `source` (manual/market/api), `value_amount`, `currency`, `valued_at`, `meta_json`, timestamps

11. **sales**
    - `id`, `channel` (ebay/cardmarket/local), `sold_at`, `currency`, `gross_amount`, `shipping_income`, `fees_total`, `net_amount`, `buyer_ref`, timestamps

12. **sale_lines**
    - `id`, `sale_id`, `inventory_item_id`, `unit_sale_price`, `quantity`, `line_total`, timestamps

13. **inventory_movements** (Ledger)
    - `id`, `inventory_item_id`, `movement_type` (`in`,`out`,`adjustment`,`transfer`),
    - `qty_delta`, `reference_type`, `reference_id`, `occurred_at`, `performed_by`, `reason`, timestamps

14. **audit_events**
    - `id`, `entity_type`, `entity_id`, `event_type`, `payload_json`, `hash`, `prev_hash`, `created_by`, `created_at`

15. **attachments** (optional)
    - `id`, `entity_type`, `entity_id`, `path`, `mime_type`, `size`, timestamps

16. **jobs/failed_jobs/cache/telescope(optional)**
    - Laravel-Standardtabellen

### 3.2 Wichtige Relationen

- `tcg_games 1:n sets`
- `sets 1:n products`
- `products 1:n inventory_items`
- `inventory_items 1:n valuations`
- `purchases 1:n purchase_lines`
- `sales 1:n sale_lines`
- `inventory_items 1:n inventory_movements`
- `inventory_items 1:n audit_events` (über entity-type/id)

### 3.3 Indizes & Constraints

- Eindeutige Schlüssel:
  - `tcg_games.slug` unique
  - `sets (tcg_game_id, code)` unique
  - `products (set_id, number, language, product_type)` unique (wo sinnvoll)
- Indizes:
  - `inventory_items (product_id, condition_code, is_active)`
  - `valuations (inventory_item_id, valued_at desc)`
  - `inventory_movements (inventory_item_id, occurred_at desc)`
  - `audit_events (entity_type, entity_id, created_at desc)`
- FK-Constraints mit `ON DELETE RESTRICT` für revisionsrelevante Daten

### 3.4 Audit-Sicherheit

- Jeder write-relevante Use-Case erzeugt:
  1. Domainänderung (z. B. Bestand -1)
  2. Ledger-Eintrag (`inventory_movements`)
  3. Audit-Event mit Hash-Kette (`hash`, `prev_hash`)
- Änderungen an Audit-Events sind applikationsseitig gesperrt (append-only).

---

## 4. API-Endpunkte

> Die primäre UI ist Filament, trotzdem wird eine versionierte API (`/api/v1`) für Integrationen vorgesehen.

### 4.1 Auth & Session

- `POST /api/v1/auth/login`
- `POST /api/v1/auth/logout`
- `GET /api/v1/auth/me`

### 4.2 Katalog

- `GET /api/v1/games`
- `POST /api/v1/games`
- `GET /api/v1/sets`
- `POST /api/v1/sets`
- `GET /api/v1/products`
- `POST /api/v1/products`
- `GET /api/v1/products/{id}`
- `PATCH /api/v1/products/{id}`

### 4.3 Inventar

- `GET /api/v1/inventory-items`
  - Filter: `game`, `set`, `condition`, `graded`, `location`, `is_active`
- `POST /api/v1/inventory-items`
- `GET /api/v1/inventory-items/{id}`
- `PATCH /api/v1/inventory-items/{id}`
- `POST /api/v1/inventory-items/{id}/transfer`
- `POST /api/v1/inventory-items/{id}/adjust-stock`

### 4.4 Einkauf, Bewertung, Verkauf

- `POST /api/v1/purchases`
- `GET /api/v1/purchases/{id}`
- `POST /api/v1/valuations`
- `GET /api/v1/inventory-items/{id}/valuations`
- `POST /api/v1/sales`
- `GET /api/v1/sales/{id}`

### 4.5 Audit & Reporting

- `GET /api/v1/inventory-items/{id}/movements`
- `GET /api/v1/audit-events`
- `GET /api/v1/reports/inventory-value`
- `GET /api/v1/reports/profit-loss`

### 4.6 API-Konventionen

- JSON:API-ähnliche Struktur (`data`, `meta`, `links` optional)
- Fehlerformat:
  - `code`, `message`, `details`, `trace_id`
- Idempotency-Key für kritische POST-Operationen (`sales`, `purchases`)
- Pagination: `page`, `per_page`, `sort`, `filter[*]`

---

## 5. Hauptkomponenten

### 5.1 Module

1. **Catalog Module**
   - Verwaltung von Games, Sets, Produkten
   - Importfunktionen (CSV/API)

2. **Inventory Module**
   - physischer Bestand, Lagerorte, Zustände, Grading
   - Transfer und Bestandsanpassung

3. **Finance Module**
   - Einkauf, Gebühren, Verkauf, Netto-Margen
   - Bewertungsverlauf

4. **Audit Module**
   - unveränderliche Event-Historie
   - Differenzen, wer-wann-was

5. **Reporting Module**
   - Bestandswert, Lagerumschlag, Gewinn/Verlust

6. **Search Module (optional Meilisearch)**
   - Volltext- und Facettenfilterung

### 5.2 Querschnittskomponenten

- **AuthN/AuthZ** (Laravel Auth + Policies + Roles)
- **Queue/Jobs** (Imports, Repricing, Aggregationen)
- **Caching** (häufige Listen/Filter)
- **Observability** (Sentry, strukturiertes Logging)
- **Validation** (FormRequest + Domain Rules)

---

## 6. Projektordnerstruktur (Vorschlag)

```text
app/
  Actions/
    Inventory/
    Sales/
    Purchases/
  Domain/
    Catalog/
    Inventory/
    Finance/
    Audit/
    Shared/
  Filament/
    Resources/
    Pages/
    Widgets/
  Http/
    Controllers/
      Api/V1/
    Requests/
    Resources/
  Jobs/
  Listeners/
  Observers/
  Policies/
  Providers/
  Services/
  Support/

bootstrap/
config/
database/
  factories/
  migrations/
  seeders/

resources/
  views/
  css/
  js/

tests/
  Feature/
    Api/
    Filament/
  Unit/
    Domain/
    Services/

routes/
  web.php
  api.php

storage/
```

**Namenskonvention:** Feature-first in `Domain/*`, transport-layer-spezifische Klassen in `Http/*`.

---

## 7. Entwicklungsphasen (Roadmap)

### Phase 0 – Setup & Foundations (1–2 Wochen)

- Laravel-Grundprojekt, CI, Linting (Pint, PHPStan), Test-Setup (Pest/PHPUnit)
- Docker-/Dev-Setup, DB-Migration-Baseline
- Auth, Rollenmodell, Basispanel in Filament

**Deliverables:** laufendes Grundsystem, CI grün, Login + Rollen.

### Phase 1 – Katalog & Inventar-Basis (2–4 Wochen)

- Entitäten: Games, Sets, Produkte, Inventory Items
- CRUD in Filament inkl. Filter/Suche
- Standortverwaltung, Zustands-/Grading-Felder
- Basis-Ledger (`inventory_movements`)

**Deliverables:** vollständige Inventarisierung und Katalogisierung.

### Phase 2 – Einkauf/Verkauf/Finanzen (2–3 Wochen)

- Purchases + Purchase Lines
- Sales + Sale Lines inkl. Gebühren/Versand
- Margen-/Profitberechnung
- API V1 für zentrale Flows

**Deliverables:** Ende-zu-Ende wirtschaftliche Nachverfolgung.

### Phase 3 – Audit-Sicherheit & Reporting (2 Wochen)

- Audit-Event-Store mit Hash-Kette
- Reporting-Dashboards (Bestandswert, P/L)
- Exporte (CSV)

**Deliverables:** revisionsfähige Historie + Management-Reports.

### Phase 4 – Skalierung & Integrationen (2 Wochen)

- Queue-Optimierungen, Horizon Dashboards
- Optional Meilisearch
- Monitoring/Alerting (Sentry)
- Performance-Tuning und Index-Review

**Deliverables:** produktionsreife Performance- und Betriebsfähigkeit.

### Phase 5 – Hardening & Go-Live (1–2 Wochen)

- Security Review, Backup/Restore-Tests
- Lasttests, UAT, Bugfixing
- Deployment Runbook + Onboarding-Doku

**Deliverables:** Go-Live-fähiges Release.

---

## 8. Qualitäts- und Teststrategie

- **Unit-Tests** für Domainlogik (Bestandsänderung, Margen, Audit-Hashing)
- **Feature-Tests** für API-Endpoints und Policies
- **UI/Panel-Tests** für kritische Filament-Flows
- **Mutation-/Regression-Fokus** auf finanz- und bestandskritischen Rechenwegen
- Statische Analyse mit PHPStan, Formatierung mit Pint, optionale automatische Refactorings via Rector

---

## 9. Risiken & Entscheidungen

- **Marktdatenquellen** für Bewertung sind variabel → Adapter-Pattern vorsehen
- **Audit-Anforderungen** können regulatorisch wachsen → append-only + Hash-Kette früh etablieren
- **Mehrwährung** kann Komplexität erhöhen → einheitliches Money-Handling + FX-Strategie definieren

---

## 10. Zusammenfassung

Die Spezifikation definiert eine robuste, modulare Laravel-Architektur mit starkem Audit-Fokus. Kern ist die Trennung von Katalog, physischem Bestand, Finanztransaktionen und revisionssicherer Ereignishistorie. Das Phasenmodell ermöglicht eine iterative Umsetzung bis zur produktionsreifen Plattform.
