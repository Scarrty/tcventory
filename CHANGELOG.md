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

### Changed

- `README.md` von einer minimalen Kurzbeschreibung zu einer vollständigen Projekt-Einstiegsdokumentation erweitert.

### Notes

- Der fachliche Zielumfang basiert weiterhin auf:
  - `docs/technische-spezifikation.md`
  - `docs/projektstruktur.md`
  - `spec.md`
