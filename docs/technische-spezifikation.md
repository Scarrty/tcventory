# Technische Spezifikation – TCventory


## Dokumentationsstatus

- Stand: 2026-03-06
- Diese Datei wurde im Rahmen der projektweiten Dokumentationspflege auf Aktualität geprüft und sprachlich vereinheitlicht.

## 1. Zielbild und Scope

TCventory ist eine Laravel-12-Anwendung zur Inventar- und Finanzverwaltung von TCG-Produkten mit Backoffice-Fokus.

Zielbild:

- belastbare Katalog- und Inventarführung,
- transaktionssichere Finanzflüsse,
- nachvollziehbare Bestandsänderungen (Audit/Ledger),
- ausbaufähige Integrations- und Reporting-Schnittstellen.

---

## 2. Architekturüberblick

### 2.1 Schichtenmodell

1. **Presentation**: Filament, Blade, Livewire
2. **Application**: Controller + FormRequests + Services
3. **Domain**: Eloquent-Modelle, Policies, Geschäftsregeln
4. **Infrastructure**: PostgreSQL, Redis, Queue/Horizon, Monitoring

### 2.2 Leitprinzipien

- API-Versionierung über `/api/v1`
- RBAC über Sanctum + Rollen/Policies
- transaktionale Umsetzung kritischer Write-Operationen
- testbare Service-Layer-Logik für Inventarprozesse

---

## 3. Datenmodell (Kernbereiche)

- **Katalog**: `games`, `sets`, `products`
- **Inventar**: `storage_locations`, `inventory_items`, `inventory_movements`
- **Finanzen**: `purchases`, `purchase_items`, `sales`, `sale_items`, `valuations`
- **Audit/Auth**: `audit_events`, Token-/Permission-Tabellen

Wesentliche Designentscheidungen:

- Soft Deletes für ausgewählte Katalog-/Inventarobjekte
- referenzielle Integrität über Foreign Keys
- `request_key` für idempotente POST-Flows in kritischen Finance-Endpunkten

---

## 4. API-Spezifikation (Ist-Stand)

### 4.1 Basis

- `GET /api/v1/health`
- `POST /api/v1/tokens`
- `GET /api/v1/me`

### 4.2 Katalog

- `GET/POST /api/v1/games`
- `GET/PATCH/DELETE /api/v1/games/{id}`
- `GET/POST /api/v1/sets`
- `GET/PATCH/DELETE /api/v1/sets/{id}`
- `GET/POST /api/v1/products`
- `GET/PATCH/DELETE /api/v1/products/{id}`

### 4.3 Inventar

- `GET/POST /api/v1/inventory-items`
- `GET/PATCH/DELETE /api/v1/inventory-items/{id}`
- `POST /api/v1/inventory-items/{id}/transfer`
- `POST /api/v1/inventory-items/{id}/adjust-stock`

### 4.4 Finance

- `GET/POST /api/v1/purchases`
- `GET/POST /api/v1/sales`
- `GET/POST /api/v1/valuations`
- `GET /api/v1/reports/finance-summary`

### 4.5 API-Konventionen

- JSON-Antworten mit `data` und optional `meta`
- Pagination über `per_page`
- Statuscodes gemäß REST-Semantik (`200`, `201`, `401`, `403`, `404`, `422`)

---

## 5. Sicherheits- und Qualitätsanforderungen

- Authentifizierung über Sanctum
- Autorisierung über Policies/Rollen
- Validierung via FormRequests
- Quality Gates: `php artisan test`, `vendor/bin/pint --test`, `vendor/bin/phpstan analyse --memory-limit=1G`

---

## 6. Offene technische Ausbaupunkte

1. Durchgängige append-only Audit-Hash-Chain in der Anwendung
2. Erweiterte Finanzreports (periodisiert/kanalbezogen)
3. Vereinheitlichung von Fehler- und Response-Contracts über alle Endpunkte
4. Operations-Reifegrad (Horizon/Sentry-Runbooks) finalisieren
