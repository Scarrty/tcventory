# Entwicklerdokumentation: API-Authentifizierungsmodul


## Dokumentationsstatus

- Stand: 2026-03-06
- Diese Datei wurde im Rahmen der projektweiten Dokumentationspflege auf Aktualität geprüft und sprachlich vereinheitlicht.

## Beschreibung

Das API-Authentifizierungsmodul stellt in `TCventory` die tokenbasierte Authentifizierung für externe Clients bereit.
Es basiert auf Laravel Sanctum und bietet zwei zentrale Endpunkte:

- `POST /api/v1/tokens` zur Erzeugung persönlicher Access Tokens für den aktuell authentifizierten Benutzer.
- `GET /api/v1/me` zur Abfrage der Identität des aktuell angemeldeten Benutzers inklusive Rollen.

Das Modul sorgt außerdem für:

- Validierung und Normalisierung von Token-Scopes („abilities“).
- Optionale Ablaufzeit für Tokens.
- Zugriffsschutz auf Endpunkte via `auth:sanctum` und rollenbasierter Middleware.

## Funktionen

### 1) Token-Erstellung (`POST /api/v1/tokens`)

**Zweck:**
Erzeugt ein neues Personal Access Token für den authentifizierten Benutzer.

**Request-Felder:**

- `token_name` (Pflicht, String, max. 255)
- `abilities` (optional, Array, 1..20 Einträge)
- `expires_in_minutes` (optional, Integer, 1..43200)

**Erlaubte abilities:**

- `inventory:read`
- `inventory:write`
- `catalog:read`
- `catalog:write`
- `reports:read`

**Verhalten:**

- Wenn `abilities` fehlt, wird standardmäßig `['inventory:read']` gesetzt.
- Übergebene `abilities` werden getrimmt, in Kleinbuchstaben umgewandelt und dedupliziert.
- Bei `expires_in_minutes` wird ein Ablaufzeitpunkt berechnet und als ISO-8601 zurückgegeben.

**Response (200 OK):**

- `token` (Plaintext-Token)
- `token_type` (`Bearer`)
- `abilities` (effektive Scopes)
- `expires_at` (ISO-8601 oder `null`)

---

### 2) Aktuellen Benutzer lesen (`GET /api/v1/me`)

**Zweck:**
Liefert Benutzerbasisdaten und Rollen des aktuellen API-Users.

**Sicherheit:**

- `auth:sanctum` erforderlich
- Rolle `user` oder `admin` erforderlich (`role:user|admin`)

**Response (200 OK):**

- `id`
- `name`
- `email`
- `roles` (Liste der Rollennamen)

---

### 3) Health-Check (`GET /api/v1/health`)

Ein einfacher, ungeschützter Endpunkt zum Service-Check:

- Rückgabe: `{ "app": "TCventory API", "status": "ok" }`

## Beispiele

### Beispiel A: Token erstellen

```bash
curl -X POST 'http://localhost:8000/api/v1/tokens' \
  -H 'Authorization: Bearer <SESSION_OR_API_TOKEN>' \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -d '{
    "token_name": "integration-client",
    "abilities": ["inventory:read", "catalog:read"],
    "expires_in_minutes": 1440
  }'
```

Beispielantwort:

```json
{
  "token": "1|abcdefghijklmnopqrstuvwxyz",
  "token_type": "Bearer",
  "abilities": ["inventory:read", "catalog:read"],
  "expires_at": "2026-03-07T10:00:00+00:00"
}
```

### Beispiel B: Standard-Ability nutzen

```bash
curl -X POST 'http://localhost:8000/api/v1/tokens' \
  -H 'Authorization: Bearer <SESSION_OR_API_TOKEN>' \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -d '{
    "token_name": "readonly-client"
  }'
```

Ergebnis: `abilities` enthält automatisch `inventory:read`.

### Beispiel C: Me-Endpunkt mit Bearer-Token

```bash
curl -X GET 'http://localhost:8000/api/v1/me' \
  -H 'Authorization: Bearer <PERSONAL_ACCESS_TOKEN>' \
  -H 'Accept: application/json'
```

Beispielantwort:

```json
{
  "id": 1,
  "name": "Max Mustermann",
  "email": "max@example.com",
  "roles": ["user"]
}
```

### Beispiel D: Fehlerfall bei ungültiger Ability

```bash
curl -X POST 'http://localhost:8000/api/v1/tokens' \
  -H 'Authorization: Bearer <SESSION_OR_API_TOKEN>' \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -d '{
    "token_name": "invalid-scope-client",
    "abilities": ["admin:all"]
  }'
```

Erwartung: HTTP `422 Unprocessable Entity` mit Validierungsfehlern für `abilities`.
