# PROGRESS

## Scope
Umsetzungs- und Qualitäts-Tracking für **ROADMAP Phase 1 – Core Platform Setup**.

## Phase-1-Status (Gesamt)
- **Status:** In Progress
- **Fortschritt:** 55%
- **Stand:** Initiale Core-Migrationen und RBAC-Seeder implementiert; Runtime/Auth-Paketinstallation ausstehend.

## Arbeitspakete

| Paket | Ziel | Status | Fortschritt | Ergebnis/Notizen |
|---|---|---|---:|---|
| WP1: Laravel Runtime Setup | Env/DB/Redis/Queue produktionsnah definieren | In Progress | 20% | Zielprofil und Setup-Reihenfolge in `docs/phase1-bootstrap.md` dokumentiert. |
| WP2: AuthN/AuthZ | Auth + Rollen/Berechtigungen einführen | In Progress | 60% | Rollen-/Permission-Seeder und `User`-RBAC-Integration vorbereitet; Paketinstallation/Flow-Tests ausstehend. |
| WP3: Core-Migrationen | Katalog, Inventar, Finance, Audit/Ledger vorbereiten | In Progress | 85% | Initiale Migrationen für Katalog, Inventar, Finance und Audit/Ledger mit FK/Indizes umgesetzt. |
| WP4: Filament-Stammdaten | Erste Admin-Ressourcen für Stammdaten | Not Started | 0% | Scope gesetzt (Games, Sets, Products, Storage Locations). |
| WP5: Qualitäts-Gates | Pint, PHPStan, Tests als Gates | In Progress | 40% | Syntax-Checks für neue PHP-Dateien durchgeführt; CI-Gates weiterhin offen. |

## Qualitätsziele (laufend)

- [x] Sicherheitsbasis über RBAC-Entscheidung vorbereitet.
- [x] Rollen-/Permission-Seeding als wiederholbarer Setup-Schritt implementiert.
- [x] Secret-Handling und `.env`-Trennung als Pflicht im Plan verankert.
- [ ] Transaktionsgrenzen für kritische Flows in Services implementiert.
- [ ] Mindest-Testabdeckung für Domainlogik und API-Flows implementiert.
- [ ] CI-Gates für Pint + PHPStan + Tests aktiviert.

## Risiken & offene Entscheidungen

1. **Mehrwährungsfähigkeit** (ja/nein in Phase 1) beeinflusst Finance-Schema.
2. **Inventargranularität** (Einzelitem vs. Lot) beeinflusst `inventory_items` und Ledger.
3. **Audit-Härtung** (nur App-seitig vs. zusätzliche DB-Constraints/Trigger) für Revisionssicherheit.

## Nächste 3 Schritte

1. Laravel-Pakete (Breeze, Sanctum, spatie/permission) installieren und Konfiguration publizieren.
2. Migrationen gegen echte Laravel-Runtime ausführen und ggf. Constraints feinjustieren.
3. CI-Workflow für Pint/PHPStan/Tests ergänzen und aktivieren.
