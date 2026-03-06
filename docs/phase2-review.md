# Phase-2-Review (Roadmap-Alignment Check)


## Dokumentationsstatus

- Stand: 2026-03-06
- Diese Datei wurde im Rahmen der projektweiten Dokumentationspflege auf Aktualität geprüft und sprachlich vereinheitlicht.

## Ziel der Prüfung
Prüfung, ob der aktuelle Repository-Stand mit **ROADMAP Phase 2 – Inventory & Catalog MVP** konsistent ist, ohne die Erfüllung der Vorphasen (Phase 0/1) zu ignorieren.

## Bewertungsbasis
- Planung: `ROADMAP.md`, `PROGRESS.md`
- API/Backend: `routes/api.php`, API-Controller/Requests/Services, Feature- und Unit-Tests
- Admin-UI: Filament-Ressourcen
- Doku-Konsistenz: `PROJECT_OVERVIEW.md`, `API_DOCS.md`

---

## Zusammenfassung
**Gesamtbewertung: Phase 2 ist funktional weit fortgeschritten, aber noch nicht vollständig abgeschlossen.**

- **Stark umgesetzt:** katalog- und inventarbezogene API-Flows (inkl. Transfer/Adjust), Transaktions-Services, RBAC/Policies, zentrale Tests.
- **Offene Punkte:** echtes API-CRUD (fehlende `destroy`-Routen/-Tests), fehlender dedizierter Status-Tracker für Phase 2, teils veraltete Projektdoku (widersprüchliche Ist-Beschreibung).

---

## Roadmap-Abgleich (Phase 0 → 2)

### Phase 0 – Foundations
**Status: Erfüllt (konsistent).**
- Top-Level-Dokumentation, Projektziele und versionierte Health-Route sind vorhanden.

### Phase 1 – Core Platform Setup
**Status: Erfüllt (laut eigener Review + aktuellem Codebestand plausibel).**
- Auth (Breeze/Sanctum), RBAC, Kernmigrationen und Filament-Stammdaten sind umgesetzt.

### Phase 2 – Inventory & Catalog MVP

#### 1) CRUD für Games/Sets/Products
**Status: Teilweise erfüllt.**
- Vorhanden: `index/store/show/update` für alle drei Katalogressourcen.
- Offen: `destroy` fehlt in API-Routing und Controller-Exposition; damit ist API-seitig eher **CRU(D-light)** statt vollständigem CRUD erreicht.

#### 2) Inventarverwaltung inkl. Zustand/Grading
**Status: Erfüllt (API-seitig).**
- `inventory-items` unterstützt Zustand/Grading-Felder und Update-Flows.

#### 3) Lagerortverwaltung und Bestands-Transfer
**Status: Erfüllt (hybrid).**
- Lagerorte via Filament-Ressource verwaltbar.
- Transfer/Bestandskorrektur via dedizierte API-Endpunkte und Service-Layer vorhanden.

#### 4) API-v1-Endpunkte für Katalog + Inventar
**Status: Erfüllt mit kleiner Lücke zur CRUD-Vollständigkeit.**
- Endpunkte für Katalog + Inventar sind vorhanden.
- Für strenges CRUD-Verständnis fehlen Delete-Endpunkte.

#### 5) Tests für zentrale Use Cases (Feature + Unit)
**Status: Erfüllt.**
- Feature-Tests für Katalog-/Inventar-Use-Cases, Auth/RBAC und Inventarbewegungen sind vorhanden.
- Unit-Tests für Inventar-Services/Modelldomäne sind vorhanden.

---

## Konkrete Abweichungen / Risiken

1. **CRUD-Anspruch vs. API-Realität**
   - Risiko: Erwartungsbruch für Integratoren/Client-Apps, wenn Delete operationell erwartet wird.

2. **Doku-Inkonsistenz zum Implementierungsstand**
   - `PROJECT_OVERVIEW.md` beschreibt im Implementierungsstand weiterhin nur Health-Routen, obwohl API-Ressourcen bereits deutlich weiter sind.
   - `PROGRESS.md` endet auf Phase 1 und bildet Phase 2-Fortschritt nicht ab.

3. **Fehlende explizite Phase-2-Abnahme-Definition (DoD)
**
   - Ohne messbare DoD-Kriterien droht „Phase 2 gefühlt fertig“, aber ohne klare Abnahmebasis.

---

## Actionable Instructions (für die nächste Iteration)

### A) Phase-2-DoD finalisieren (hohe Priorität)
1. In `ROADMAP.md` oder neuem `docs/phase2-dod.md` klare Abnahmekriterien ergänzen:
   - Welche Ressourcen müssen echtes CRUD (inkl. Delete) liefern?
   - Ist Lagerortverwaltung nur im Backoffice ausreichend oder auch via API erforderlich?
   - Welche minimalen Filter/Sortierungen sind für MVP verpflichtend?

### B) API-CRUD vervollständigen oder bewusst abgrenzen (hohe Priorität)
1. Entweder `DELETE` für `games`, `sets`, `products`, `inventory-items` implementieren **inkl. Policy-Checks und Tests**,
2. oder Roadmap/`API_DOCS.md` explizit auf „read/write ohne delete im MVP“ korrigieren.

### C) Fortschrittstracking auf Phase 2 erweitern (mittlere Priorität)
1. `PROGRESS.md` um Phase-2-Arbeitspakete ergänzen (analog zur Struktur von Phase 1).
2. Für jedes Arbeitspaket eine messbare Fortschrittsmetrik hinterlegen.

### D) Dokumentation auf Ist-Stand bringen (mittlere Priorität)
1. `PROJECT_OVERVIEW.md` Abschnitt „Aktueller Implementierungsstand“ aktualisieren (nicht mehr nur Health-Endpunkte).
2. `API_DOCS.md` um Transfer/Adjust-Endpunkte als bereits implementiert ergänzen (inkl. Request-/Response-Beispielen und Fehlerschema).

### E) Qualitäts- und Betriebscheck nachziehen (mittlere Priorität)
1. Nach Dependency-Installation vollständigen QA-Lauf ausführen:
   - `vendor/bin/pint --test`
   - `vendor/bin/phpstan analyse`
   - `php artisan test`
2. Ergebnis in Changelog/Progress dokumentieren.

---

## Empfohlene Entscheidungsvorlage (Kurz)
- Wenn Produktziel „vollständiges API-CRUD im MVP“ ist: **Phase 2 noch nicht abschließen** (Delete + Tests zuerst).
- Wenn Produktziel „operatives MVP ohne Delete“ ist: Roadmap/Docs präzisieren und **Phase 2 mit dokumentierter Scope-Entscheidung** abschließen.
