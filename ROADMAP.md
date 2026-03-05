# ROADMAP

## Zielbild

TCventory entwickelt sich von einer dokumentierten Basis hin zu einer produktionsnahen Inventar- und Finanzplattform für TCG-Bestände mit revisionssicherem Audit-Trail.

## Phase 0 – Foundations (aktuell)

- Technische Spezifikation und Zielarchitektur dokumentiert
- Projektstruktur als modulare Laravel-Organisation definiert
- Basis-Routen für Web/API-Status vorhanden (`/`, `/api/v1/health`)
- Top-Level-Dokumentation konsolidiert

## Phase 1 – Core Platform Setup

- Laravel-Basis-Setup finalisieren (Env, DB, Redis, Queue)
- Authentifizierung + Rollen/Berechtigungen einführen
- Migrationen für Kernentitäten erstellen:
  - Katalog (Games/Sets/Products)
  - Inventar (`inventory_items`, `storage_locations`)
  - Finanzflüsse (Purchases/Sales/Valuations)
  - Audit/Ledger (`inventory_movements`, `audit_events`)
- Grundlegende Filament-Ressourcen für Stammdaten

## Phase 2 – Inventory & Catalog MVP

- CRUD für Games/Sets/Products
- Inventarverwaltung inkl. Zustand/Grading
- Lagerortverwaltung und Bestands-Transfer
- API-v1-Endpunkte für Katalog + Inventar bereitstellen
- Tests für zentrale Use Cases (Feature + Unit)

## Phase 3 – Finance & Valuation

- Einkauf inkl. Nebenkosten und Zuordnung zu Bestand
- Bewertungen (manuell/Markt/API-gestützt) historisieren
- Verkauf inkl. Gebühren, Versand und Netto-Berechnung
- Erste Profit/Loss-Auswertungen
- Idempotente POST-Flows für kritische Finanzoperationen

## Phase 4 – Audit, Reporting & Operations

- Vollständiger append-only Audit-Trail mit Hash-Kette
- Bewegungsledger für jede bestandsrelevante Änderung
- Reporting-Module (Bestandswert, Umschlag, P/L)
- Queue-gestützte Prozesse (Import, Repricing, Aggregation)
- Monitoring/Alerting mit Sentry + Horizon

## Phase 5 – Skalierung & Integrationen

- Optional: Meilisearch für Volltext-/Facettensuche
- Performance-Tuning und Index-Optimierung
- Externe Integrationen (Marktplätze, Preisdatenquellen)
- API-Härtung, Dokumentation und Versionierungsstrategie ausbauen

## Laufende Qualitätsziele

- Sicherheitsstandards (RBAC, Validierung, Secret-Handling)
- Konsistente Transaktionen in kritischen Prozessen
- Testabdeckung für Domainlogik und API-Flows
- Coding-Standards über Pint, statische Analyse via PHPStan
