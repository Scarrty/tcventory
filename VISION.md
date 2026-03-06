# 📱 App Project: TCventory

## Dokumentgrenzen

- **Dieses Dokument** fokussiert auf Produktabsicht: Problem, Zielgruppen, Differenzierung, Outcome-Ziele.
- Technische Architektur steht in `PROJECT_OVERVIEW.md`.
- Lieferplanung steht in `ROADMAP.md`.
- Live-Status steht in `PROGRESS.md`.

## 1. Vision
**Kurzbeschreibung**  
TCventory ist eine Laravel-basierte Webanwendung zur Verwaltung von TCG-Karten und Sealed-Produkten. Die App kombiniert Katalog- und Inventarverwaltung mit Finanzfunktionen wie Einkauf, Verkauf, Bewertung und einem Finance-Summary-Reporting. Fokus ist ein belastbares Backoffice mit API-Zugriff, RBAC und nachvollziehbaren Bestandsbewegungen.

**Problem**  
Sammler, Händler und Teams verwalten TCG-Bestände oft verteilt über Tabellen, Marktplätze und Einzellösungen. Dadurch entstehen Medienbrüche, unklare Bestände, fehlende Historie bei Lagerbewegungen und unzureichende finanzielle Transparenz (Marge, Gebühren, Bewertung).

**Zielgruppe**
- TCG-Händler (Singles und Sealed)
- Sammler mit größerem Bestand
- Kleine bis mittlere Teams im Reselling/Backoffice
- Integrationsnutzer (API-first Workflows)

**USP (Unique Selling Point)**
TCventory verbindet operatives Inventar (inkl. Transfer und Bestandskorrekturen), Finance-Transaktionen und rollenbasierte API-Nutzung in einer konsistenten Plattform. Gegenüber einfachen Inventory-Tools bietet es zusätzlich transaktionale Flows, vorbereitete Audit-/Ledger-Strukturen und eine klare Ausbau-Roadmap für revisionsnahe Prozesse.

---

# 🎯 Ziele

## Kurzfristige Ziele (0–3 Monate)
- Inventory & Catalog MVP final konsolidieren (Phase 2)
- API-Contracts (Fehlerformat/Statuscodes/Pagination) harmonisieren
- Qualitätsgates stabil grün (Tests, Pint, PHPStan)

## Mittelfristige Ziele (3–12 Monate)
- Finance-Reporting vertiefen (periodisiert, kanalbezogen, realisiert/unrealisiert)
- Audit-Abdeckung für kritische Write-Flows erweitern
- Operations-Reife mit Horizon/Sentry-Runbooks abschließen

## Langfristige Ziele
- Skalierung von Web-/Worker-Workloads
- Integrationen (z. B. Marktplätze, Preisdatenquellen)
- Erweiterte Such- und Analysefunktionen (optional Meilisearch)

---

# 🧭 Abgrenzung zu technischen Dokumenten

Diese Vision enthält bewusst **keine** detaillierten Architektur-, Datenmodell- oder Endpoint-Definitionen.

- Architektur und Module: `PROJECT_OVERVIEW.md`
- API-Endpunkte und Konventionen: `API_DOCS.md`
- Phasenplanung: `ROADMAP.md`
- Aktueller Lieferstand: `PROGRESS.md`
- Engineering-Spezifikation: `docs/technische-spezifikation.md`

## Erfolgskriterien auf Visionsebene

- Inventar- und Finanztransparenz für TCG-Bestände in einem konsistenten System.
- Nachvollziehbare Bestandsveränderungen ohne Medienbrüche.
- Klare Skalierungsperspektive für Reporting und Integrationen.
