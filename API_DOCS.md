# API_DOCS

## Dokumentationsstatus

- Stand: 2026-03-12
- Auf den aktuellen API-v1-Stand synchronisiert.

## 1. Überblick

TCventory stellt eine versionierte API unter **`/api/v1`** bereit. Die API ist für Integrationen optimiert; zentrale Backoffice-Workflows laufen zusätzlich über Filament.

## 2. Basis-Konventionen

- Basis-Pfad: `/api/v1`
- JSON-Antworten
- Authentifizierung: Sanctum Bearer Token
- Ressourcenantworten: primär unter `data` (bei Listen inkl. Pagination-Metadaten)
- Idempotenz für kritische POST-Flows via `request_key`

## 3. Implementierte Endpunkte

### Health & Access

- `GET /api/v1/health`
- `POST /api/v1/tokens`
- `GET /api/v1/me`

### Katalog

- `GET|POST /api/v1/games`
- `GET|PATCH|DELETE /api/v1/games/{id}`
- `GET|POST /api/v1/sets`
- `GET|PATCH|DELETE /api/v1/sets/{id}`
- `GET|POST /api/v1/products`
- `GET|PATCH|DELETE /api/v1/products/{id}`

### Inventar

- `GET|POST /api/v1/inventory-items`
- `GET|PATCH|DELETE /api/v1/inventory-items/{id}`
- `POST /api/v1/inventory-items/{id}/transfer`
- `POST /api/v1/inventory-items/{id}/adjust-stock`

### Finanzen & Reporting

- `GET|POST /api/v1/purchases`
- `GET|POST /api/v1/sales`
- `GET|POST /api/v1/valuations`
- `GET /api/v1/reports/finance-summary`

#### `GET /api/v1/reports/finance-summary`

Query-Parameter:

- `period`: `all | day | week | month | custom`
- `from_date`, `to_date` (erforderlich bei `period=custom`)
- `channel` (optional)
- `group_by`: `none | channel`

Wesentliche KPIs (unter `data.kpis`):

- `purchase_total`
- `sale_gross_total`
- `sale_net_total`
- `realized_profit_loss`
- `unrealized_profit_loss`
- `latest_inventory_valuation`
- `fee_burden_total`
- `tax_burden_total`

Bei `group_by=channel` wird ein Channel-Breakdown unter `data.breakdown.by_channel` geliefert.

## 4. Typische Fehlercodes

- `401 Unauthorized`: fehlendes/ungültiges Token
- `403 Forbidden`: fehlende Berechtigung (Policy/RBAC)
- `404 Not Found`: Ressource nicht vorhanden
- `422 Unprocessable Entity`: Validierungs-/Fachregelverletzung

## 5. Geplante API-Erweiterungen (nächste Ausbaustufe)

- `GET /api/v1/audit-events`
- `GET /api/v1/reports/inventory-value`
- `GET /api/v1/reports/profit-loss`
- zusätzliche Detailansichten für Finance (`/purchases/{id}`, `/sales/{id}`)

## 6. Sicherheits- und Betriebsaspekte

- RBAC über Rollen/Policies
- FormRequest-Validierung + Domänenregeln
- Transaktionale Umsetzung kritischer Flows
- Audit-Hash-Chain-Validierung per `php artisan audit:verify-chain`
