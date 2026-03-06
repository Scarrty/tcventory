# Release Checklist – 2026-03-06


## Dokumentationsstatus

- Stand: 2026-03-06
- Diese Datei wurde im Rahmen der projektweiten Dokumentationspflege auf Aktualität geprüft und sprachlich vereinheitlicht.

## Scope

Durchführung der angeforderten Punkte:

1. Tests bestehen
2. Environment Variablen prüfen
3. Datenbank Migration
4. Build erstellen
5. Deployment

## Ergebnisse

### 1) Tests bestehen

- `php artisan test` wurde erfolgreich ausgeführt.
- Ergebnis: **36 passed**.

### 2) Environment Variablen prüfen

- `.env.example` wurde als lokale Basis nach `.env` kopiert.
- `php artisan key:generate` wurde erfolgreich ausgeführt.
- Deployment-relevante Variablen sind in `.env.docker.example` dokumentiert (u. a. `APP_KEY`, `DB_PASSWORD`, optional `REDIS_PASSWORD`).

### 3) Datenbank Migration

- Migration erfolgreich gegen SQLite validiert:
  - `DB_CONNECTION=sqlite DB_DATABASE=database/database.sqlite php artisan migrate --force`
- Alle vorhandenen Migrationen liefen erfolgreich durch.

### 4) Build erstellen

- Frontend-Build erfolgreich erstellt:
  - `npm run build`
- Artefakte wurden nach `public/build/` geschrieben (`manifest.json`, CSS/JS Bundles).

### 5) Deployment

- Deployment konnte in dieser Laufzeitumgebung **nicht** vollständig ausgeführt werden, da `docker`/`docker compose` nicht installiert ist.
- Verifizierbarer Hinweis:
  - `bash: command not found: docker`

## Zusätzliche Qualitätschecks

- `vendor/bin/pint --test` erfolgreich.
- `vendor/bin/phpstan analyse --memory-limit=1G` erfolgreich.

## Ergebnis

- Release-Checklist ist bis auf den tatsächlichen Docker-Deployment-Run abgearbeitet.
- Codebasis ist test- und build-grün; statische Analyse ist grün.
