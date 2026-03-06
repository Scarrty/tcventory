# API_DOCS

## 1. Überblick

TCventory stellt eine versionierte API unter **`/api/v1`** bereit. Die API ist primär für Integrationen vorgesehen; die Hauptbedienung erfolgt über das Filament-Admin-Interface.

Aktueller Minimalstatus:

- `GET /api/v1/health` liefert einen einfachen API-Status.
- REST-Endpunkte für `games`, `sets`, `products` und `inventory-items` mit `index/store/show/update/destroy` sind vorhanden.
- Für Bestandsoperationen sind `transfer` und `adjust-stock` als dedizierte Aktionen vorhanden.

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
- Idempotency-Key für kritische POST-Endpunkte (insb. Transfers/Korrekturen via `request_key`)

## 3. Aktuell implementierte Endpunkte

Alle Ressourcen-Endpunkte liefern eine konsistente Antwort mit `data` und bei Listen zusätzlich `meta` (Pagination).

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

### Auth / User (implementiert)

- `POST /api/v1/tokens`
- `GET /api/v1/me`

#### `POST /api/v1/tokens`

**Auth:** `Bearer` Token über Sanctum (eingeloggt).

**Request (JSON):**

```json
{
  "token_name": "ci-integration",
  "abilities": ["inventory:read", "catalog:read"],
  "expires_in_minutes": 120
}
```

- `abilities` ist optional; Standard ist `inventory:read`.
- Erlaubte `abilities`: `inventory:read`, `inventory:write`, `catalog:read`, `catalog:write`, `reports:read`.

### Katalog (implementiert)

- `GET /api/v1/games`
- `POST /api/v1/games`
- `GET /api/v1/games/{id}`
- `PATCH /api/v1/games/{id}`
- `DELETE /api/v1/games/{id}`
- `GET /api/v1/sets`
- `POST /api/v1/sets`
- `GET /api/v1/sets/{id}`
- `PATCH /api/v1/sets/{id}`
- `DELETE /api/v1/sets/{id}`
- `GET /api/v1/products`
- `POST /api/v1/products`
- `GET /api/v1/products/{id}`
- `PATCH /api/v1/products/{id}`
- `DELETE /api/v1/products/{id}`

### Inventar (implementiert)

- `GET /api/v1/inventory-items`
- `POST /api/v1/inventory-items`
- `GET /api/v1/inventory-items/{id}`
- `PATCH /api/v1/inventory-items/{id}`
- `DELETE /api/v1/inventory-items/{id}`
- `POST /api/v1/inventory-items/{id}/transfer`
- `POST /api/v1/inventory-items/{id}/adjust-stock`

#### `POST /api/v1/inventory-items/{id}/transfer`

**Beschreibung:** Verschiebt Bestand ganz oder teilweise auf einen anderen Lagerort.

**Request (JSON):**

```json
{
  "quantity": 3,
  "target_storage_location_id": 5,
  "reason": "Umlagerung in Tresor",
  "request_key": "transfer-2026-03-06-001"
}
```

**Beispielantwort (200):**

```json
{
  "data": {
    "id": 42,
    "product_id": 7,
    "storage_location_id": 2,
    "quantity": 7,
    "condition": "nm",
    "grading_provider": null,
    "grade": null
  }
}
```

**Typische Fehlerfälle:**

- `401 Unauthorized`: Kein gültiges Sanctum-Token übermittelt.
- `403 Forbidden`: Authentifiziert, aber keine `update`-Berechtigung auf das Inventory Item.
- `404 Not Found`: Inventory Item (`{id}`) existiert nicht.
- `422 Unprocessable Entity`: Validierung oder Fachregel verletzt, z. B.:
  - `quantity < 1`
  - `quantity` größer als verfügbarer Bestand
  - `target_storage_location_id` ungültig oder identisch mit aktueller Location

#### `POST /api/v1/inventory-items/{id}/adjust-stock`

**Beschreibung:** Korrigiert den Bestand eines Inventory Items über ein Delta (positiv/negativ).

**Request (JSON):**

```json
{
  "quantity_delta": -2,
  "reason": "Beschädigte Exemplare aussortiert",
  "request_key": "adjust-2026-03-06-003"
}
```

**Beispielantwort (200):**

```json
{
  "data": {
    "id": 42,
    "product_id": 7,
    "storage_location_id": 2,
    "quantity": 5,
    "condition": "nm",
    "grading_provider": null,
    "grade": null
  }
}
```

**Typische Fehlerfälle:**

- `401 Unauthorized`: Kein gültiges Sanctum-Token übermittelt.
- `403 Forbidden`: Authentifiziert, aber keine `update`-Berechtigung auf das Inventory Item.
- `404 Not Found`: Inventory Item (`{id}`) existiert nicht.
- `422 Unprocessable Entity`: Validierung oder Fachregel verletzt, z. B.:
  - `quantity_delta = 0`
  - Anpassung würde zu negativem Bestand führen

## 4. Geplante Endpunkte (laut Spezifikation)

### 4.1 Auth & Session

- `POST /api/v1/auth/login`
- `POST /api/v1/auth/logout`

### 4.2 Katalog (Erweiterungen)

- weitere Query-/Filter-Endpunkte gemäß Produktspezifikation
- Bulk-Import-/Sync-Endpunkte für externe Katalogquellen

### 4.3 Inventar (Erweiterungen)

- `GET /api/v1/inventory-items/{id}/movements`
- erweiterte Filter-/Reporting-Endpunkte für Bestandsanalysen

### 4.4 Einkauf, Bewertung, Verkauf

- `POST /api/v1/purchases`
- `GET /api/v1/purchases/{id}`
- `POST /api/v1/valuations`
- `GET /api/v1/inventory-items/{id}/valuations`
- `POST /api/v1/sales`
- `GET /api/v1/sales/{id}`

### 4.5 Audit & Reporting

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

## 7. Stand

- Stand: 2026-03-06
- Quelle: aktueller Branch-Stand, Commit `379def6`
