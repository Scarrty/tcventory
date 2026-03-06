# PROGRESS

## Scope
Umsetzungs- und Qualitäts-Tracking für **ROADMAP Phase 1 – Core Platform Setup**.

## Phase-1-Status (Gesamt)
- **Status:** In Progress
- **Fortschritt:** 15%
- **Stand:** Architekturentscheidungen und Bootstrap-Plan für Auth, RBAC und DB festgelegt.

## Arbeitspakete

| Paket | Ziel | Status | Fortschritt | Ergebnis/Notizen |
|---|---|---|---:|---|
| WP1: Laravel Runtime Setup | Env/DB/Redis/Queue produktionsnah definieren | In Progress | 20% | Zielprofil und Setup-Reihenfolge in `docs/phase1-bootstrap.md` dokumentiert. |
| WP2: AuthN/AuthZ | Auth + Rollen/Berechtigungen einführen | In Progress | 25% | Empfehlung: Breeze + Sanctum + spatie/laravel-permission. |
| WP3: Core-Migrationen | Katalog, Inventar, Finance, Audit/Ledger vorbereiten | Not Started | 0% | Tabellen- und Konventionsvorschlag dokumentiert; Implementierung ausstehend. |
| WP4: Filament-Stammdaten | Erste Admin-Ressourcen für Stammdaten | Not Started | 0% | Scope gesetzt (Games, Sets, Products, Storage Locations). |
| WP5: Qualitäts-Gates | Pint, PHPStan, Tests als Gates | In Progress | 30% | DoD/Checkliste für Quality Gates definiert. |

## Qualitätsziele (laufend)

- [x] Sicherheitsbasis über RBAC-Entscheidung vorbereitet.
- [x] Secret-Handling und `.env`-Trennung als Pflicht im Plan verankert.
- [ ] Transaktionsgrenzen für kritische Flows in Services implementiert.
- [ ] Mindest-Testabdeckung für Domainlogik und API-Flows implementiert.
- [ ] CI-Gates für Pint + PHPStan + Tests aktiviert.

## Risiken & offene Entscheidungen

1. **Mehrwährungsfähigkeit** (ja/nein in Phase 1) beeinflusst Finance-Schema.
2. **Inventargranularität** (Einzelitem vs. Lot) beeinflusst `inventory_items` und Ledger.
3. **Audit-Härtung** (nur App-seitig vs. zusätzliche DB-Constraints/Trigger) für Revisionssicherheit.

## Nächste 3 Schritte

1. Laravel-Pakete für Auth/RBAC installieren und Basis-Konfiguration committen.
2. Initiale Migrationen für Katalog + Inventar erstellen (inkl. FK/Indizes).
3. Erstes Quality-Gate lokal/CI aufsetzen (Pint, PHPStan, Test-Seed-Run).
