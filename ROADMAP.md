# ROADMAP


## Dokumentationsstatus

- Stand: 2026-03-06
- Diese Datei wurde im Rahmen der projektweiten Dokumentationspflege auf Aktualität geprüft und sprachlich vereinheitlicht.

## Dokumentgrenzen

- **Dieses Dokument** beschreibt die _geplante_ Entwicklung (Vorwärtsblick, Phasen, Ziele, offene Lücken).
- Produktvision und Markt-/Nutzerperspektive stehen in `VISION.md`.
- Technische Architektur und Systemzuschnitt stehen in `PROJECT_OVERVIEW.md`.
- Der aktuelle Umsetzungsstand steht in `PROGRESS.md`.

## Zielbild

TCventory entwickelt sich von einer dokumentierten Basis zu einer produktionsnahen Inventar- und Finanzplattform für TCG-Bestände mit nachvollziehbaren Inventarbewegungen, belastbaren Finanzkennzahlen und auditfähigen Prozessen.

## Phase 0 – Foundations (abgeschlossen)

- Technische Spezifikation und Zielarchitektur dokumentiert
- Top-Level-Dokumentation konsolidiert
- Basis-Routen für Web/API-Status vorhanden (`/`, `/api/v1/health`)

## Phase 1 – Core Platform Setup (abgeschlossen)

- Laravel-Setup inkl. Docker-Basis und CI-Qualitätsgates eingerichtet
- Authentifizierung (Breeze + Sanctum) umgesetzt
- RBAC mit Rollen/Permissions und Policies eingeführt
- Kernmigrationen für Katalog, Inventar, Finance, Audit/Ledger vorhanden
- Filament-Basisressourcen für Stammdaten umgesetzt

## Phase 2 – Inventory & Catalog MVP (nahe Abschluss)

- API-CRUD für `games`, `sets`, `products`, `inventory-items` inkl. Delete umgesetzt
- Inventarverwaltung inkl. Zustand/Grading im API-Flow vorhanden
- Lagerortverwaltung via Filament und Bestandsoperationen per API verfügbar
- Transfer (`/transfer`) und Korrektur (`/adjust-stock`) transaktional umgesetzt
- Feature- und Unit-Tests für zentrale Catalog/Inventory-Use-Cases vorhanden

## Phase 3 – Finance & Valuation (teilweise umgesetzt)

- Einkauf, Verkauf und Bewertungen als API-Ressourcen implementiert
- Idempotente POST-Flows über `request_key` für Purchases/Sales umgesetzt
- Aggregierter Finanzreport (`/api/v1/reports/finance-summary`) verfügbar
- Offene Lücken:
  - vertiefte P/L-Analysen (periodisiert, kanalbezogen, realisiert/unrealisiert)
  - weitergehende Reporting- und Kostenallokationslogik

## Phase 4 – Audit, Reporting & Operations (in Vorbereitung)

- Datenmodell für Audit/Ledger ist angelegt
- Noch offen für Abschluss:
  - systemweit erzwungene append-only Audit-Hash-Chain
  - vollständige Audit-Abdeckung für alle kritischen Write-Flows
  - dokumentierte Operations-Reife mit Horizon/Sentry-Runbooks

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

## Technische Upgrade-Initiative – PHP 8.4 & LTS-Stack (in Arbeit)

- Runtime-Anforderung auf PHP 8.4 ausgerichtet
- LTS-Harmonisierung des Stacks (Backend, Frontend-Build, Infrastruktur)
- Detaillierter Ablauf inkl. Risiko- und Rollback-Plan in `docs/php84-lts-upgrade-plan.md`
