# PROGRESS

## Scope
Umsetzungs- und Qualitäts-Tracking für **ROADMAP Phase 2 – Inventory & Catalog MVP**.

## Phase 2 – Inventory & Catalog MVP (Gesamt)
- **Status:** In Arbeit
- **Fortschritt:** 40%
- **Stichtag:** 2026-03-31
- **Entscheidungs- und Auditbasis:** `docs/phase2-review.md`

## Arbeitspakete (Phase 2)

| Paket | Ziel | Status | Fortschritt | Messbare Kriterien | Ergebnis/Notizen |
|---|---|---|---:|---|---|
| WP2.1: CRUD für Games/Sets/Products | Vollständige Verwaltung der Katalogstammdaten | In Arbeit | 60% | - `POST/GET/PATCH/DELETE`-Endpunkte für Games, Sets und Products vorhanden.<br>- Validierungsregeln für Pflichtfelder und Relationen aktiv.<br>- Soft-Delete/Hard-Delete-Verhalten dokumentiert und getestet. | Create/Read/Update sind weitgehend umgesetzt; DELETE-Absicherung und Doku-Finish offen. |
| WP2.2: Inventar inkl. Zustand/Grading | Inventarpositionen mit Qualität/Condition belastbar führen | In Arbeit | 50% | - Inventar-Model enthält Zustand/Grading-Felder mit erlaubten Wertebereichen.<br>- Erfassung und Änderung von Grading über API/Admin möglich.<br>- Historisierung relevanter Zustandsänderungen nachweisbar. | Grundmodell und Felder vorhanden; durchgängige Workflow-Tests für Grading-Änderungen offen. |
| WP2.3: Lagerortverwaltung & Bestands-Transfer | Transfers zwischen Lagerorten konsistent und nachvollziehbar | In Arbeit | 35% | - Lagerorte per CRUD verwaltbar.<br>- Transfer-Endpoint bucht Quelle/Ziel atomar.<br>- Negativbestände werden technisch verhindert und validiert. | Lagerortbasis steht; atomare Transferlogik + Grenzfall-Tests müssen abgeschlossen werden. |
| WP2.4: API-v1 für Katalog + Inventar | Stabile API-v1 als Integrationsbasis bereitstellen | In Arbeit | 30% | - Alle Phase-2-Ressourcen unter `/api/v1` verfügbar.<br>- Fehlerformat und HTTP-Statuscodes konsistent.<br>- AuthN/AuthZ auf allen mutierenden Endpunkten verifiziert. | Erste Endpunkte vorhanden; Konsistenz von Response-Contracts und Policy-Abdeckung noch unvollständig. |
| WP2.5: Tests (Feature + Unit) | Kern-Use-Cases automatisiert absichern | In Arbeit | 25% | - Feature-Tests für CRUD vollständig (inkl. DELETE) vorhanden.<br>- Feature-Tests für Transfer- und Grading-Flows vorhanden.<br>- Unit-Tests für Domainregeln/Services mit kritischen Validierungen vorhanden. | Testbasis existiert, deckt aber noch nicht alle Phase-2-Akzeptanzkriterien vollständig ab. |

## Review-Status (Phase 2)

Die laufende Bewertung, offene Punkte und Freigabeentscheidungen werden in `docs/phase2-review.md` geführt.

## Nächste Schritte (nur offene Phase-2-Lücken)

1. DELETE-Pfade für Katalog-CRUD vollständig absichern (Policies, Validierung, Feature-Tests).
2. Transfer-Workflow fachlich abschließen: atomare Buchung, Negativbestands-Schutz und Fehlerfälle testen.
3. API-v1-Contracts vereinheitlichen (Response-Format, Statuscodes, Fehlermeldungen) und regressionssicher testen.
4. Testlücken schließen: vollständige Feature-Suite für CRUD/Grading/Transfer sowie ergänzende Unit-Tests für Domainregeln.
