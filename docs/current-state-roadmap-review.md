# Current State Review – Roadmap, Fortschritt & Quality Control

## Kontext der Prüfung

Diese Review bewertet den aktuellen Stand des Repositories gegen:

- `ROADMAP.md` (Phasen 0–5 + Qualitätsziele)
- `PROGRESS.md` (laufendes Fortschrittstracking)
- den aktuell implementierten API-/Service-/Test-Stand
- den aktuellen QA-Status aus lokalen Qualitätschecks

Stand der Prüfung: **2026-03-06** (inkl. erneuter lokaler QA-Ausführung).

---

## Executive Summary

- **Phase 0 und Phase 1 sind konsistent umgesetzt** (Basisarchitektur, Auth/RBAC, Migrationen, Filament-Ressourcen).
- **Phase 2 ist technisch deutlich weiter als das formale Fortschrittstracking**: CRUD + Transfer/Adjust sind implementiert, inklusive Delete-Pfaden.
- **Phase 3 ist teilweise bereits vorgezogen** (Purchases/Sales/Valuations + Finance Summary + Request-Key-Idempotenz für Purchases/Sales), wird aber im Fortschritt nicht explizit geführt.
- **Phase 4/5 sind nur vorbereitet, nicht voll umgesetzt**: Audit-Tabellen existieren, aber keine durchgängige append-only Hash-Chain-Implementierung im Anwendungscode.
- **QC-Risiko aktuell hoch**: wichtige Quality Gates laufen lokal nicht grün (Pint-Style-Issues, PHPStan-Memory-Limit, Test-Fails inkl. fehlender Runtime-Artefakte wie `.env` und Vite-Manifest).

---

## Roadmap-Abgleich nach Phase

## Phase 0 – Foundations
**Einschätzung: Erfüllt.**

Erfüllt durch dokumentierte Basis, Versionierung und Health-Endpunkt.

## Phase 1 – Core Platform Setup
**Einschätzung: Erfüllt.**

- Authentifizierung, Sanctum, Rollen-/Policy-Logik und Kernmigrationen sind vorhanden.
- Grundlegende Filament-Ressourcen für Stammdaten sind vorhanden.

## Phase 2 – Inventory & Catalog MVP
**Einschätzung: In der Umsetzung weit fortgeschritten (näher an „done“ als PROGRESS signalisiert).**

- API-CRUD für `games`, `sets`, `products`, `inventory-items` inkl. `destroy` vorhanden.
- Transfer und Bestandskorrektur sind über dedizierte Service-Klassen transaktional umgesetzt.
- Feature- und Unit-Tests für zentrale Catalog/Inventory-Flows existieren.

**Gap zur formalen Steuerung:** `PROGRESS.md` (40%) wirkt gegenüber dem tatsächlichen Implementierungsstand konservativ/inkonsistent.

## Phase 3 – Finance & Valuation
**Einschätzung: Teilweise umgesetzt (früher als im formalen Plan sichtbar).**

- Finance-Endpunkte (`purchases`, `sales`, `valuations`) und aggregierter Report (`reports/finance-summary`) sind implementiert.
- Request-Key-Idempotenz ist bereits in kritischen POST-Flows für Einkauf/Verkauf umgesetzt.

**Offene fachliche Lücken:** tiefergehende P/L-Analysen, belastbare Kostenallokation und umfassende Reporting-Tiefe sind noch nicht als vollständige Phase-3-Abnahme nachweisbar.

## Phase 4 – Audit, Reporting & Operations
**Einschätzung: Vorbereitungen vorhanden, operative Reife fehlt.**

- Tabellenstruktur (`audit_events`, Hash-Felder) ist angelegt.
- Eine echte, systemweit erzwungene append-only Audit-Pipeline mit Hash-Verkettung ist im Code jedoch nicht sichtbar.
- Queue-Basis ist vorhanden, aber Horizon/Sentry-gestützte Operations-Reife ist nicht vollständig dokumentiert/implementiert.

## Phase 5 – Skalierung & Integrationen
**Einschätzung: Noch nicht gestartet (im Sinne produktiver Umsetzung).**

- Spezifikation und Zielbild vorhanden.
- Keine belastbare Implementierung für Search/Integrationen/erweiterte API-Härtung erkennbar.

---

## QC-/Release-Readiness (aktueller Stand)

### Ergebnis lokaler Gates

- `vendor/bin/pint --test` → **FAIL** (5 Style-Abweichungen, ausschließlich `class_attributes_separation` in Testdateien)
- `vendor/bin/phpstan analyse` → **FAIL/WARN** (Abbruch durch konfiguriertes PHP-Memory-Limit 128M im Parallel-Worker)
- `php artisan test` → **FAIL/WARN** (Suite startet, endet jedoch mit 7 Fails + 69 Warnings; zentrale Ursachen: fehlende `.env` und fehlendes `public/build/manifest.json` in View-basierten Feature-Tests)

### Bewertung

Die Projektfunktionalität ist in zentralen Domänen sichtbar fortgeschritten, aber die **Delivery-Qualität ist derzeit nicht release-ready**, weil die Standard-Quality-Gates nicht stabil grün laufen. Positiv: Die Gates sind ausführbar und liefern reproduzierbare, priorisierbare Fehlerbilder.

---

## Priorisierte Actionable TODOs

### P0 – Unblock Quality Gates (sofort)

1. **Test-/CI-Umgebung deterministisch machen**
   - `.env.testing` verbindlich bereitstellen und im Testbootstrap erzwingen.
   - Frontend-Artefakte für Feature-Tests handhaben (Vite manifest mocken oder `npm run build` im CI-Testjob sicherstellen).
   - Ziel: `php artisan test` läuft lokal und in CI reproduzierbar grün.

2. **Pint-Verstöße beheben und per CI blocken**
   - Aktuelle Style-Issues in betroffenen Testdateien korrigieren.
   - Ziel: `vendor/bin/pint --test` muss gate-fähig grün laufen.

3. **PHPStan stabilisieren**
   - Memory-Limit im QA-Script/CI erhöhen (z. B. `--memory-limit=512M` oder höher).
   - Ziel: vollständiger statischer Analysebericht statt Frühabbruch.

### P1 – Plan/Ist-Synchronisierung

4. **`PROGRESS.md` an Realstand anpassen**
   - Phase 2 realistisch nachziehen (CRUD inkl. Delete + Inventory-Flows bereits umgesetzt).
   - Sichtbar machen, welche Phase-3-Bausteine bereits implementiert sind.

5. **Roadmap-Entscheidung zu Phasenüberlappung dokumentieren**
   - Klar festhalten, ob „Phase-3 vorgezogen während Phase-2-Abschluss“ strategisch gewollt ist.
   - Abnahmekriterien (DoD) je Phase mit messbaren Kriterien ergänzen.

### P1 – Audit- und Revisionssicherheit (Roadmap-Kernversprechen)

6. **Audit-Chain produktiv machen**
   - Event-Erfassung zentralisieren (Domain Events/Observer/Service-Hooks).
   - `event_hash` und `previous_hash` durchgängig berechnen und validieren.
   - Append-only-Verhalten technisch erzwingen + Integritätstests ergänzen.

7. **Ledger/Audit-Abdeckung für alle kritischen Flows prüfen**
   - Catalog-Delete, Transfer, Adjust, Purchase/Sale/Valuation systematisch auf Audit-Ereignisse mappen.

### P2 – API-Konsistenz & Betriebsreife

8. **Response-/Fehler-Contracts vereinheitlichen**
   - Einheitliches Fehlerformat über alle Endpunkte (inkl. Validation/Policy/Domain-Fehler) sicherstellen.

9. **Finance-Reporting vertiefen**
   - Zusätzliche Kennzahlen (periodisiert, nach Channel, realisiert vs. unrealisiert) und deren Tests ergänzen.

10. **Operations-Stack vervollständigen**
    - Entscheidung und Umsetzung zu Horizon/Sentry verbindlich dokumentieren.
    - Alarme, Health-Checks und Runbooks für Queue-/Worker-Prozesse ergänzen.

---

## Empfohlener 2-Wochen-Umsetzungsplan

### Woche 1
- P0.1–P0.3 vollständig schließen (grüne Gates herstellen).
- PROGRESS/ROADMAP-Dokumente synchronisieren (P1.4–P1.5).

### Woche 2
- Audit-Chain MVP implementieren (P1.6–P1.7).
- API-Contract-Harmonisierung und erweiterte Finance-Reports starten (P2.8–P2.9).

---

## Abnahmekriterien für die nächste Review

- Alle drei Gates grün: Pint, PHPStan, Test-Suite.
- Fortschrittsdokumente widerspruchsfrei zu implementiertem Stand.
- Audit-Hash-Chain für mindestens zwei kritische Domänenflüsse produktiv nachweisbar.
- Mindestens ein zusätzlicher Finance-Report mit Regressionstests.
