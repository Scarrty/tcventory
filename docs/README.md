# Dokumentationslandkarte

## Zweck
Diese Datei ist der zentrale Einstiegspunkt für Projektdokumentation und definiert Zuständigkeiten, Aktualisierungslogik und Lebenszyklus der Dokumente.

## What to read first
1. `README.md` – Quickstart, lokale Inbetriebnahme, operative Einstiegsinfos
2. `PROJECT_OVERVIEW.md` – Architektur, Module, technische Scope-Abgrenzung
3. `API_DOCS.md` – API-v1-Kontrakte, Endpunkte, Konventionen
4. `ROADMAP.md` und `PROGRESS.md` – Plan (Soll) vs. Umsetzungsstand (Ist)

## Dokumente, Rollen und Lebenszyklus

| Datei | Zweck | Audience | Authority | Update-Cadence | Owner | Lifecycle |
|---|---|---|---|---|---|---|
| `README.md` | Projekt-Einstieg, Setup, tägliche Nutzung | Alle Mitwirkenden | Source of Truth (Onboarding) | Bei Setup-/Runbook-Änderungen | Maintainer | Active |
| `PROJECT_OVERVIEW.md` | Architektur- und Modulübersicht | Engineering, Reviewer | Source of Truth (Architekturüberblick) | Bei Architekturänderungen | Tech Lead | Active |
| `API_DOCS.md` | API-Kontrakte und Konventionen | Integrationen, Backend | Source of Truth (API-Doku) | Bei Endpoint-/Contract-Änderungen | Backend Team | Active |
| `ROADMAP.md` | Geplante Phasen, Ziele, Milestones | Produkt/Tech Lead | Source of Truth (Plan) | Bei Scope-/Priorisierungsänderungen | Produkt + Tech Lead | Active |
| `PROGRESS.md` | Live-Status, Fortschritt, aktuelle Arbeitspakete | Projektsteuerung, Team | Source of Truth (Ist-Status) | Laufend / pro Iteration | Delivery Owner | Active |
| `VISION.md` | Problem, Zielgruppen, Produktrichtung | Stakeholder, Produkt | Source of Truth (Vision) | Selten, bei Strategie-Shift | Produktverantwortliche | Active |
| `spec.md` | Reproduzierbares Setup für Codex-Instanzen | Automation, Maintainer | Reference | Bei Setup-/Pipeline-Änderungen | Maintainer | Active |
| `docs/technische-spezifikation.md` | Detailliertere technische Spezifikation | Engineering | Reference | Bei Systemdesign-Änderungen | Tech Lead | Active |
| `docs/projektstruktur.md` | Struktur-Referenz des Repos | Engineering | Reference | Bei Strukturänderungen | Maintainer | Active |
| `docs/phase1-bootstrap.md` | Historische Umsetzungsanleitung Phase 1 | Historische Nachvollziehbarkeit | Historical | Nicht laufend | Archiv | Historical |
| `docs/phase1-review.md` | Historisches Review Phase 1 | Historische Nachvollziehbarkeit | Historical | Nicht laufend | Archiv | Historical |
| `docs/phase2-review.md` | Historisches Review Phase 2 | Historische Nachvollziehbarkeit | Historical | Nicht laufend | Archiv | Historical |
| `docs/reviews/2026-03-06-roadmap-review.md` | Datierter Snapshot-Review | Historische Nachvollziehbarkeit | Historical | Nicht laufend | Archiv | Historical |
| `CHANGELOG.md` | Versionierte Änderungsdokumentation | Alle Mitwirkenden | Source of Truth (Release-Historie) | Pro Release/Änderung | Maintainer | Active |
| `docs/releases/README.md` | Prozess für Release-Dokumente | Release-Verantwortliche | Source of Truth (Release-Doku-Prozess) | Bei Prozessänderungen | Release Owner | Active |
| `docs/releases/*.md` | Release Notes und Checklisten | Delivery, Ops | Reference/Historical pro Release | Pro Release | Release Owner | Historical |
| `docs/deployment/docker-compose-v2.md` | Deployment-Abläufe | Ops/DevOps | Source of Truth (Compose-Deployment) | Bei Deployment-Änderungen | Ops | Active |

## Struktur-Hinweise
- Deployment-Dokumentation: `docs/deployment/`
- Release-Artefakte und Prozess: `docs/releases/`
- Historische Review-Snapshots: `docs/reviews/`

## Regel
- **Nur `PROGRESS.md` beschreibt den aktuellen Live-Status.**
- Review-Dokumente sind Snapshots und dienen als Evidenz, nicht als laufende Statusquelle.
