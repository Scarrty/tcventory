# PROGRESS

## Scope
Umsetzungs- und Qualitäts-Tracking für **ROADMAP Phase 1 – Core Platform Setup**.

## Phase-1-Status (Gesamt)
- **Status:** Abgeschlossen
- **Fortschritt:** 100%
- **Stand:** Runtime, AuthN/AuthZ, Core-Migrationen, Filament-Stammdaten und CI-Qualitätsgates sind umgesetzt und verifiziert.

## Arbeitspakete

| Paket | Ziel | Status | Fortschritt | Ergebnis/Notizen |
|---|---|---|---:|---|
| WP1: Laravel Runtime Setup | Env/DB/Redis/Queue produktionsnah definieren | Done | 100% | Vollständiges Laravel-Setup inkl. reproduzierbarer Build-/Run-Basis vorhanden. |
| WP2: AuthN/AuthZ | Auth + Rollen/Berechtigungen einführen | Done | 100% | Breeze-Auth, Sanctum-Token-Flow, Spatie-RBAC mit Seeder und Zugriffstests umgesetzt. |
| WP3: Core-Migrationen | Katalog, Inventar, Finance, Audit/Ledger vorbereiten | Done | 100% | Migrationen für alle Phase-1-Domänen vorhanden und testbar. |
| WP4: Filament-Stammdaten | Erste Admin-Ressourcen für Stammdaten | Done | 100% | Ressourcen für Games, Sets, Products, Storage Locations inkl. Policies vorhanden. |
| WP5: Qualitäts-Gates | Pint, PHPStan, Tests als Gates | Done | 100% | CI-Workflow aktiv; lokale Gate-Reproduktion erfolgreich, inklusive behobener Pint-Fehler. |

## Qualitätsziele

- [x] Sicherheitsbasis über RBAC umgesetzt.
- [x] Rollen-/Permission-Seeding als wiederholbarer Setup-Schritt implementiert.
- [x] Secret-Handling und `.env`-Trennung verankert.
- [x] CI-Gates für Pint + PHPStan + Tests aktiviert.
- [ ] Transaktionsgrenzen für kritische Flows in Services erweitern (Phase 2/3).
- [ ] Mindest-Testabdeckung für Domänenservices und Finanzflüsse weiter ausbauen (Phase 2/3).

## Review-Ergebnis (Phase 1)

Detailprüfung in `docs/phase1-review.md`:

1. **Erfolgreich umgesetzt:** Runtime-Setup, Auth-Stack (Breeze/Sanctum), RBAC, Core-Schema und Filament-Stammdaten.
2. **CI-Fehler behoben:** Pint-Style-Verstöße in Migrationen/Seeder formatiert.
3. **Aktueller Fokus:** Umsetzung der produktnahen Use-Cases aus Phase 2+.

## Nächste 3 Schritte

1. Phase 2 starten: End-to-End-CRUD + Workflows für Katalog/Inventar mit Service-Layer.
2. Domain-Tests für Inventarbewegungen, Validierungen und Policies ausbauen.
3. Finanz- und Audit-Use-Cases mit transaktionaler Service-Orchestrierung vorbereiten.
