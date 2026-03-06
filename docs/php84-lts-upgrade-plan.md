# Upgrade-Plan: PHP 8.4 + LTS-Stack

## Ziel

Das Projekt wird von der aktuell in `composer.json` hinterlegten Plattform `php 8.2.0` auf **PHP 8.4** angehoben und gleichzeitig auf einen konsistenten **LTS/Stable-Stack** über alle zentralen Laufzeit- und Build-Abhängigkeiten gebracht.

## Scope

## Umsetzungsstand (aktuell)

- ✅ `composer.json` auf `php: ^8.4` und `config.platform.php: 8.4.0` umgestellt.
- ✅ `composer.lock` mit PHP-8.4-Zielstand neu generiert und kompatible Paketupdates übernommen.
- ✅ Node-LTS-Pinning über `.nvmrc` (`22`) und `package.json#engines` ergänzt.
- ✅ Baseline-Qualitätsgates lokal gegen den neuen Stack ausgeführt (`composer test`, `php artisan test`, `phpstan`, `pint --test`, `npm run build`).


- Backend-Runtime: PHP, Laravel, zentrale Laravel-Pakete
- Admin/UI-Stack: Filament
- Frontend-Build-Stack: Node.js LTS, Vite, Tailwind, PostCSS
- Infrastruktur-Services: Datenbank, Redis, Queue-Worker, CI-Images

## 1) Bestandsaufnahme & Zielstände definieren

1. Abhängigkeiten in `composer.json` und `package.json` inventarisieren.
2. Für jede Kernkomponente Zielversion definieren (bevorzugt LTS oder langfristig unterstützte Major-Version).
3. Upgrade-Risiken je Komponente klassifizieren: niedrig/mittel/hoch.

### Zielmatrix (vorgeschlagen)

| Bereich | Ist | Ziel | Begründung |
|---|---|---|---|
| PHP | 8.2 (Platform-Pin) | 8.4 | Sicherheits- und Performance-Verbesserungen, Zukunftssicherheit |
| Laravel | 12.x | 12.x (aktueller Patch) | Framework bleibt auf aktueller Major, nur sichere Minor/Patch-Fortführung |
| Filament | 3.x | 3.x (aktueller Patch) | Kompatibel halten zur Laravel-12-Basis |
| Node.js | nicht festgelegt | Aktuelle Node-LTS (z. B. 22 LTS) | Stabiler Build-/Tooling-Unterbau |
| Vite/Tailwind/PostCSS | bereits modern | auf letzte kompatible Minor/Patch-Stände | Security-/Bugfix-Wartung |
| DB/Redis | projektabhängig | LTS-Stände in Runtime/Compose/CI festschreiben | reproduzierbare Deployments |

## 2) Technische Vorbereitung

- Branch `chore/php84-lts-upgrade` erstellen.
- CI-Matrix temporär auf **PHP 8.2 + 8.4** erweitern (Parallelvalidierung).
- Falls vorhanden: Docker/Compose/Runtime-Images auf LTS-Basis pinnen.
- Reproduzierbares Baseline-Testing ausführen:
  - `composer test`
  - `php artisan test`
  - `./vendor/bin/phpstan analyse`
  - `./vendor/bin/pint --test`
  - `npm run build`

## 3) PHP-8.4-Migration in kontrollierten Schritten

1. `composer.json` aktualisieren:
   - `require.php` von `^8.2` auf `^8.4`
   - `config.platform.php` von `8.2.0` auf `8.4.0`
2. `composer update` ausführen und Lockfile aktualisieren.
3. Deprecations/BC-Themen beheben:
   - Spracheigenschaften/Signaturen
   - Drittanbieterpakete auf 8.4-kompatible Stände anheben
4. Test- und Qualitätsgates komplett grün herstellen.

## 4) LTS-Harmonisierung der eingesetzten Software

- Node.js-Version in Tooling/CI klar pinnen (z. B. `.nvmrc`, CI-Setup).
- Build-Dependencies auf kompatible stabile Stände bringen und Lockfile erneuern.
- Infrastruktur-Basisversionen dokumentieren:
  - MySQL/MariaDB/PostgreSQL Zielversion
  - Redis Zielversion
  - Queue-Worker-/Supervisor-Basisimage
- Security-Scans ergänzen (Composer Audit + npm Audit in CI als Bericht/Gate nach Teamentscheidung).

## 5) Rollout-Strategie

### Staging

- Staging auf PHP 8.4 deployen.
- Smoke-Tests für:
  - Login/Registrierung
  - Filament-CRUD (Games/Sets/Products/StorageLocations)
  - API-Token-Flows
  - Queue-Verarbeitung

### Produktion

- Wartungsfenster planen.
- Zero-/Low-Downtime-Deployment mit schneller Rollback-Option.
- Runtime-Monitoring für Error-Rate, Queue-Lag und Response-Time aktiv beobachten.

## 6) Rollback-Plan

- Letztes stabiles 8.2-Artefakt taggen.
- Rollback-Trigger definieren (z. B. erhöhte 5xx-Rate, Queue-Stau, Auth-Fehler).
- Rollback-Prozedur dokumentieren:
  1. App auf vorheriges Image zurücksetzen.
  2. Queue Worker neu starten.
  3. Post-Rollback Smoke-Tests ausführen.

## 7) Definition of Done

- CI auf PHP 8.4 vollständig grün.
- Keine offenen kritischen Deprecation-/Kompatibilitätswarnungen.
- Laufzeit- und Build-Stack auf dokumentierte LTS-/Stable-Versionen festgelegt.
- Betriebsdokumentation (Runbook/Deployment) aktualisiert.
