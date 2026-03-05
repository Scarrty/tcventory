# API_DOCS

## 1. Überblick

TCventory stellt eine versionierte API unter **`/api/v1`** bereit. Die API ist primär für Integrationen vorgesehen; die Hauptbedienung erfolgt über das Filament-Admin-Interface.

Aktueller Minimalstatus:

- `GET /api/v1/health` liefert einen einfachen API-Status.

## 2. Basis-Konventionen

- Basis-Pfad: `/api/v1`
- JSON-Antworten
- empfohlene Antwortstruktur (JSON:API-ähnlich):
  - `data`
  - `meta` (optional)
  - `links` (optional)
- Fehlerstruktur:
  - `code`
  - `message`
  - `details`
  - `trace_id`
- Pagination/Filter:
  - `page`, `per_page`, `sort`, `filter[*]`
- Idempotency-Key für kritische POST-Endpunkte (insb. `sales`, `purchases`)

## 3. Aktuell implementierte Endpunkte

### Health

#### `GET /api/v1/health`

**Beschreibung:** Prüft den API-Basisstatus.

**Beispielantwort:**

```json
{
  "app": "TCventory API",
  "status": "ok"
}
```

## 4. Geplante Endpunkte (laut Spezifikation)

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

## 5. Sicherheits- und Betriebsaspekte

- RBAC über Rollen/Policies (Laravel)
- Input-Validation via FormRequests und Domänenregeln
- Transaktionale Umsetzung kritischer Flows
- Strukturierte Logs + Monitoring (Sentry)

## 6. Versionierung und Erweiterung

- Erste stabile Zielversion: `v1`
- Abwärtsinkompatible Änderungen über neue API-Version
- Erweiterungen bevorzugt additiv (neue Felder/Endpunkte)
