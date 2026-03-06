# PROGRESS

## Scope
Umsetzungs- und Qualitäts-Tracking für **ROADMAP Phase 1 – Core Platform Setup**.

## Phase-1-Status (Gesamt)
- **Status:** In Review
- **Fortschritt:** 58%
- **Stand:** Schema-Migrationen und RBAC-Bausteine sind vorhanden; echte Runtime-Validierung, Auth-Stack-Installation und Filament-Basis fehlen noch.

## Arbeitspakete

| Paket | Ziel | Status | Fortschritt | Ergebnis/Notizen |
|---|---|---|---:|---|
| WP1: Laravel Runtime Setup | Env/DB/Redis/Queue produktionsnah definieren | In Progress | 25% | Zielprofil und Setup-Reihenfolge in `docs/phase1-bootstrap.md` dokumentiert; Runtime aktuell nicht im Repo verifizierbar (`composer.json`/Artisan fehlen). |
| WP2: AuthN/AuthZ | Auth + Rollen/Berechtigungen einführen | In Progress | 65% | Rollen-/Permission-Seeder und `User`-RBAC-Integration umgesetzt; Breeze/Sanctum-Installation und Auth-Flow-Tests offen. |
| WP3: Core-Migrationen | Katalog, Inventar, Finance, Audit/Ledger vorbereiten | In Progress | 90% | Migrationen für Katalog, Inventar, Finance, Audit/Ledger mit FK/Indizes umgesetzt; Ausführung gegen echte DB-Laufzeit noch nachzuweisen. |
| WP4: Filament-Stammdaten | Erste Admin-Ressourcen für Stammdaten | Not Started | 0% | Scope gesetzt (Games, Sets, Products, Storage Locations), noch keine Ressourcen im Repository. |
| WP5: Qualitäts-Gates | Pint, PHPStan, Tests als Gates | In Progress | 35% | Isolierte Syntax-Prüfung möglich; keine nachweisbare CI-Pipeline und keine Test-Suite im aktuellen Repository-Stand. |

## Qualitätsziele (laufend)

- [x] Sicherheitsbasis über RBAC-Entscheidung vorbereitet.
- [x] Rollen-/Permission-Seeding als wiederholbarer Setup-Schritt implementiert.
- [x] Secret-Handling und `.env`-Trennung als Pflicht im Plan verankert.
- [ ] Transaktionsgrenzen für kritische Flows in Services implementiert.
- [ ] Mindest-Testabdeckung für Domainlogik und API-Flows implementiert.
- [ ] CI-Gates für Pint + PHPStan + Tests aktiviert.

## Review-Ergebnis (Phase 1)

Detailprüfung in `docs/phase1-review.md`:

1. **Stark umgesetzt:** Kernschema und Datenmodell-Basis (Katalog/Inventar/Finance/Audit) inklusive FK-/Index-Strategie.
2. **Teilweise umgesetzt:** RBAC-Basis über Spatie-Seeding und `HasRoles` im `User`-Modell.
3. **Nicht umgesetzt:** Auth-Stack-Installation (Breeze/Sanctum), Filament-Ressourcen, ausführbare QA-Gates innerhalb eines vollständigen Laravel-Runtime-Setups.

## Risiken & offene Entscheidungen

1. **Mehrwährungsfähigkeit** (ja/nein in Phase 1) beeinflusst Finance-Schema.
2. **Inventargranularität** (Einzelitem vs. Lot) beeinflusst `inventory_items` und Ledger.
3. **Audit-Härtung** (nur App-seitig vs. zusätzliche DB-Constraints/Trigger) für Revisionssicherheit.
4. **Runtime-Lücke im Repository** (fehlende Laravel-Basisdateien) blockiert belastbare Integrations-/Migrationsnachweise.

## Nächste 3 Schritte

1. Vollständiges Laravel-Scaffold inkl. `composer.json` und Artisan-Runtime in den Branch übernehmen und Build reproduzierbar machen.
2. Pakete (Breeze, Sanctum, spatie/permission) installieren/konfigurieren und Auth-Feature-Tests hinzufügen.
3. Erste Filament-Ressourcen (Games, Sets, Products, Storage Locations) plus CI-Gates für Pint/PHPStan/Tests aktivieren.
