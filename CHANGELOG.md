# CHANGELOG

Alle relevanten Änderungen am Projekt werden in dieser Datei dokumentiert.

Das Format orientiert sich an *Keep a Changelog* und verwendet semantische Versionierung als Zielbild.

## [Unreleased]

### Added

- Neue, konsolidierte Projektdokumentation auf Root-Ebene:
  - `README.md`
  - `PROJECT_OVERVIEW.md`
  - `API_DOCS.md`
  - `CHANGELOG.md`
  - `ROADMAP.md`
- Strukturierte Zusammenfassung der bestehenden technischen Spezifikation inklusive:
  - Projektbeschreibung
  - Installation/Setup
  - Architekturüberblick
  - API-Überblick
  - Entwicklungsleitplanken
- Phase-1-Core-Migrationen für Katalog (`games`, `sets`, `products`), Inventar (`storage_locations`, `inventory_items`), Finance (`purchases`, `purchase_items`, `sales`, `sale_items`, `valuations`) und Audit/Ledger (`inventory_movements`, `audit_events`).
- RBAC-Basis über `RolesAndPermissionsSeeder` inkl. Rollen (`admin`, `operator`, `accounting`, `viewer`) und initialen Permissions.
- `DatabaseSeeder`-Verkettung für reproduzierbares Rollen-/Permission-Seeding und `User`-Modell mit `HasRoles`-Trait vorbereitet.
- Review-Artefakt `docs/phase1-review.md` mit Soll-/Ist-Abgleich zu Phase 1 und priorisierten Maßnahmen.

### Changed

- PHP-Runtime auf `^8.4` angehoben und Composer-Platform-Pin auf `8.4.0` aktualisiert.
- Node-LTS-Version für den Build-Stack über `.nvmrc` auf `22` festgelegt und in `package.json` als Engine dokumentiert.
- Composer-Lockfile auf den neuen PHP-Target-Stand neu aufgelöst (inkl. kompatibler Paket-Updates).
- `README.md` von einer minimalen Kurzbeschreibung zu einer vollständigen Projekt-Einstiegsdokumentation erweitert.
- Phase-1-Statusdokumente (`ROADMAP.md`, `PROGRESS.md`) mit einem expliziten Architektur-Review synchronisiert.

### Notes

- Der fachliche Zielumfang basiert weiterhin auf:
  - `docs/technische-spezifikation.md`
  - `docs/projektstruktur.md`
  - `spec.md`
