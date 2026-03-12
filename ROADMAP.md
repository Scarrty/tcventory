# ROADMAP

## Dokumentationsstatus

- Stand: 2026-03-12
- Diese Datei wurde auf den aktuellen Planungsstand aktualisiert.

## Dokumentgrenzen

- **Dieses Dokument** beschreibt die _geplante_ Entwicklung (Vorwärtsblick).
- Produktvision: `VISION.md`
- Technische Architektur: `PROJECT_OVERVIEW.md`
- Live-Ist-Stand: `PROGRESS.md`

## Zielbild

TCventory entwickelt sich zur produktionsnahen Inventar- und Finanzplattform für TCG-Bestände mit belastbaren KPI-Reports und revisionsnaher Auditierbarkeit.

## Phase 0 – Foundations (abgeschlossen)

- Dokumentations- und Projektgrundlagen aufgebaut
- Basis-Routen für Web/API-Status vorhanden (`/`, `/api/v1/health`)

## Phase 1 – Core Platform Setup (abgeschlossen)

- Laravel-Setup inkl. Docker-Basis und CI-Workflows eingerichtet
- Authentifizierung (Breeze + Sanctum) umgesetzt
- RBAC mit Rollen/Permissions und Policies eingeführt
- Kernmigrationen für Katalog, Inventar, Finance, Audit/Ledger vorhanden
- Filament-Basisressourcen umgesetzt

## Phase 2 – Inventory & Catalog MVP (abgeschlossen)

- API-CRUD für `games`, `sets`, `products`, `inventory-items` inkl. Delete
- Inventarbewegungen via `transfer` und `adjust-stock` transaktional umgesetzt
- Zentrale Catalog/Inventory-Use-Cases durch Feature-/Unit-Tests abgedeckt

## Phase 3 – Finance & Valuation (abgeschlossen)

- `purchases`, `sales`, `valuations` als API-Ressourcen produktiv
- Idempotente POST-Flows via `request_key` für kritische Finance-Schreibpfade
- `GET /api/v1/reports/finance-summary` mit Perioden-/Channel-Filtern und KPI-Breakdown

## Phase 4 – Audit, Reporting & Operations (gestartet)

- Audit-Hash-Chain für Finance-Write-Flows implementiert
- Integritätsprüfung via `php artisan audit:verify-chain` vorhanden
- Nächste Schritte:
  - Audit-Abdeckung auf weitere kritische Write-Flows ausweiten
  - Operations-Reife (Runbooks/Horizon/Sentry) vervollständigen
  - zusätzliche Reports (`inventory-value`, `profit-loss`) produktivisieren

## Phase 5 – Skalierung & Integrationen (geplant)

- Optional: Meilisearch für Volltext-/Facettensuche
- Performance-Tuning und Index-Optimierung
- Externe Integrationen (Marktplätze, Preisdatenquellen)
- API-Härtung und Versionierungsstrategie ausbauen

## Laufende Qualitätsziele

- Sicherheitsstandards (RBAC, Validierung, Secret-Handling)
- Konsistente Transaktionen in kritischen Prozessen
- Stabile Quality Gates: Pint, PHPStan, Test-Suite
- Konsistente API-Contracts (Statuscodes, Fehlerformate, Pagination)
