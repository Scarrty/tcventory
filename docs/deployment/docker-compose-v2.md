# Docker Compose v2 – Deployment & Betrieb


## Dokumentationsstatus

- Stand: 2026-03-06
- Diese Datei wurde im Rahmen der projektweiten Dokumentationspflege auf Aktualität geprüft und sprachlich vereinheitlicht.

Diese Variante stellt TCventory mit folgenden Services bereit:

- `web` (Nginx)
- `app` (PHP-FPM / Laravel)
- `db` (PostgreSQL 16)
- `redis` (Cache/Queue/Session)
- `queue` (Laravel Queue Worker)
- `scheduler` (Laravel Scheduler)

## CI/CD-Integration (Release)

Bei jedem GitHub Release (`published`) läuft die CD-Pipeline:

1. Build + Push der Images nach GHCR
   - `ghcr.io/<owner>/tcventory-app:<release-tag>` und `:latest`
   - `ghcr.io/<owner>/tcventory-web:<release-tag>` und `:latest`
2. Generierung einer release-spezifischen `docker-compose.release.yml`
   - Die Datei enthält den veröffentlichten Release-Tag statt `latest`.
3. Upload der `docker-compose.release.yml` als Release-Asset.

Damit kann produktiv entweder mit `latest` oder strikt mit einem bestimmten Release-Tag deployt werden.

## 1) Konfiguration

```bash
cp .env.docker.example .env.docker
```

Pflichtwerte:

- `APP_KEY`
- `DB_PASSWORD`
- `REDIS_PASSWORD` (empfohlen)

App-Key erzeugen:

```bash
docker compose --env-file .env.docker run --rm app php artisan key:generate --show
```

## 2) Deployment (empfohlen mit Release-Tag)

### Variante A: Release-Asset verwenden (immutable)

1. `docker-compose.release.yml` aus dem GitHub Release herunterladen.
2. Starten:

```bash
docker compose --env-file .env.docker -f docker-compose.release.yml pull
docker compose --env-file .env.docker -f docker-compose.release.yml up -d
```

### Variante B: Repository-Compose + ENV-Tag

```bash
# Beispiel: v1.2.3
IMAGE_TAG=v1.2.3 docker compose --env-file .env.docker pull
IMAGE_TAG=v1.2.3 docker compose --env-file .env.docker up -d
```

## 3) Erstes Datenbank-Migrations-Setup

```bash
docker compose --env-file .env.docker run --rm -e RUN_MIGRATIONS=true app php artisan migrate --force
```

Optional:

```bash
docker compose --env-file .env.docker run --rm app php artisan db:seed --class=Database\\Seeders\\RolesAndPermissionsSeeder
```

## 4) Betrieb

- App erreichbar unter `http://localhost:${APP_PORT}`
- Logs:

```bash
docker compose --env-file .env.docker logs -f web app queue scheduler db redis
```

## 5) Update-Strategie

- **Patch/Minor Update:** `IMAGE_TAG` auf neuen Release-Tag setzen, dann `pull && up -d`.
- **Rollback:** auf alten, bekannten `IMAGE_TAG` zurücksetzen und erneut `pull && up -d`.
- Nach jedem Versionswechsel Migrationen ausführen:

```bash
docker compose --env-file .env.docker run --rm app php artisan migrate --force
```

## Hinweise

- TLS wird im Stack **nicht** terminiert. Für HTTPS wird ein externer Reverse Proxy empfohlen (z. B. Traefik, Caddy oder Nginx Proxy Manager).
- Volumes:
  - `db_data` für PostgreSQL
  - `redis_data` für Redis AOF
  - `app_storage` für Laravel `storage/`
