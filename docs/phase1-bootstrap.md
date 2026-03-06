# Phase 1 Bootstrap-Setup (Empfehlung)


## Dokumentationsstatus

- Stand: 2026-03-06
- Diese Datei wurde im Rahmen der projektweiten Dokumentationspflege auf Aktualität geprüft und sprachlich vereinheitlicht.

Dieses Dokument konkretisiert die Entscheidungen für:
1. **Auth-Stack:** Laravel Breeze + Sanctum
2. **RBAC:** spatie/laravel-permission
3. **Datenbank:** PostgreSQL 15/16

---

## 1) Auth-Stack: Breeze + Sanctum

### Ziel
Schnelles, wartbares Fundament für Web-Login und API-Token ohne OAuth-Overhead.

### Umsetzung

```bash
composer require laravel/breeze --dev
php artisan breeze:install blade
composer require laravel/sanctum
php artisan migrate
```

### Leitlinien
- **Web-Auth:** Session-basiert (Breeze).
- **API-Auth:** Token-basiert (Sanctum Personal Access Tokens).
- **Sicherheitsdefaults:**
  - Rate-Limits für Auth-Routen
  - sichere Cookie-/Session-Settings pro Environment
  - keine Secrets im Repo; nur `.env`/Secret-Store

### Minimal-DoD
- Login/Logout/Reset funktionsfähig
- API-Token-Erstellung für berechtigte Nutzer möglich
- Basis-Feature-Tests für Auth-Flows vorhanden

---

## 2) RBAC: spatie/laravel-permission

### Ziel
Rollen + granulare Berechtigungen als belastbare Sicherheitsbasis.

### Umsetzung

```bash
composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
```

User-Modell ergänzen:
- Trait `HasRoles` hinzufügen

### Rollenmodell (Start)
- `admin`
- `operator`
- `accounting`
- `viewer`

### Permission-Gruppen
- `catalog.*`
- `inventory.*`
- `finance.*`
- `audit.*`
- `admin.*`

### Leitlinien
- Zugriff über **Policies + Permissions**, nicht nur Rollenchecks in Controllern.
- Seed für Rollen/Permissions versionieren.
- Filament-Ressourcen strikt über Policies/Permissions schützen.

### Minimal-DoD
- Rollen/Permissions seeded
- Zugriffstests für zentrale Rollen vorhanden
- Admin-Bereich gegen unberechtigten Zugriff abgesichert

---

## 3) PostgreSQL 15/16 als Primärdatenbank

### Ziel
Hohe Datenintegrität und stabile Transaktionen für Inventar-, Finance- und Audit-Workloads.

### Warum PostgreSQL
- robuste Constraints/Transaktionen
- gute Performance bei komplexeren Auswertungen
- langfristig vorteilhaft für Ledger-/Audit-Szenarien

### Konventionen (Phase 1)
- Primärschlüssel: `bigint` (`id`)
- Zeitstempel: `timestampsTz`
- Geldbeträge: `numeric(14,2)` (oder Value Object + minor units in späterer Härtung)
- Pflicht-FKs mit sinnvollen `on delete`-Regeln
- Indizes für häufige Filter-/Join-Spalten

### Tabellempfehlung (Start)
- Katalog: `games`, `sets`, `products`
- Inventar: `storage_locations`, `inventory_items`
- Finance: `purchases`, `purchase_items`, `sales`, `sale_items`, `valuations`
- Audit/Ledger: `inventory_movements`, `audit_events`

### Minimal-DoD
- Migrationen laufen lokal clean (fresh + migrate)
- FK/Index-Strategie dokumentiert
- Initiale Seeds für Rollen + Stammdaten möglich

---

## Qualitäts-Gates für Phase 1

Empfohlene Pipeline/Local Gates:

```bash
vendor/bin/pint --test
vendor/bin/phpstan analyse
php artisan test
```

Zusätzlich:
- transaktionale Services für kritische Schreibpfade
- nachvollziehbare Audit-Events für sicherheitsrelevante Änderungen
- klare DoD je Arbeitspaket in `PROGRESS.md`
