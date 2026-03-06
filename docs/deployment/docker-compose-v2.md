# Docker Compose v2 – Production-ready light

Diese Variante stellt TCventory mit folgenden Services bereit:

- `web` (Nginx)
- `app` (PHP-FPM / Laravel)
- `db` (PostgreSQL 16)
- `redis` (Cache/Queue/Session)
- `queue` (Laravel Queue Worker)
- `scheduler` (Laravel Scheduler)

## 1) Konfiguration

```bash
cp .env.docker.example .env.docker
```

- Setze mindestens `APP_KEY`, `DB_PASSWORD` und optional `REDIS_PASSWORD`.
- Erzeuge den Key lokal:

```bash
docker compose run --rm app php artisan key:generate --show
```

## 2) Build + Start

```bash
docker compose --env-file .env.docker up -d --build
```

## 3) Erstes Datenbank-Migrations-Setup

```bash
docker compose --env-file .env.docker run --rm -e RUN_MIGRATIONS=true app php artisan migrate --force
```

(Optional)

```bash
docker compose --env-file .env.docker run --rm app php artisan db:seed --class=Database\\Seeders\\RolesAndPermissionsSeeder
```

## 4) Betrieb

- App erreichbar unter `http://localhost:${APP_PORT}`
- Logs:

```bash
docker compose --env-file .env.docker logs -f web app queue scheduler db redis
```

## 5) Updates

```bash
git pull
docker compose --env-file .env.docker up -d --build
docker compose --env-file .env.docker run --rm app php artisan migrate --force
```

## Hinweise

- Diese Konfiguration terminiert TLS **nicht** im Stack. Für HTTPS wird ein externer Reverse Proxy empfohlen (z. B. Traefik, Caddy oder Nginx Proxy Manager).
- Volumes:
  - `db_data` für PostgreSQL
  - `redis_data` für Redis AOF
  - `app_storage` für Laravel `storage/`
