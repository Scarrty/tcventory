# 📱 App Project: TCventory

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

# 🧱 Tech Stack

## Frontend
- Framework: Filament (Admin), Livewire 3, Blade
- UI Library: Filament Components
- Styling: Tailwind CSS, Alpine.js

## Backend
- Framework: Laravel 12 (PHP 8.4+)
- API: REST unter `/api/v1` (JSON, versioniert)
- Auth: Laravel Sanctum + RBAC (Rollen/Policies)

## Database
- Hauptdatenbank: PostgreSQL (lokal optional SQLite)
- Caching: Redis (optional/empfohlen)

## Infrastruktur
- Hosting: Docker-/Container-basiertes Deployment (Compose)
- CI/CD: Qualitätsgates vorgesehen (Tests, Pint, PHPStan), konkrete CI-Pipeline projektspezifisch
- Container: Docker (App + Nginx)

---

# 🧩 Core Features

## Feature 1
Katalog- und Inventarverwaltung für TCG-Produkte.

**User Story**
Als Backoffice-Nutzer möchte ich Games, Sets, Produkte und Inventory Items zentral verwalten, damit Bestände konsistent und schnell auffindbar sind.

**Funktion**
- CRUD für `games`, `sets`, `products`, `inventory-items`
- Lagerorte und Bestandsführung inkl. Zustands-/Grading-Felder
- Soft Deletes für ausgewählte Bereiche

---

## Feature 2
Transaktionale Inventarbewegungen.

**User Story**
Als Lagerverantwortlicher möchte ich Bestände zwischen Lagerorten transferieren und Korrekturen buchen, damit der Bestand fachlich korrekt bleibt.

**Funktion**
- `POST /inventory-items/{id}/transfer`
- `POST /inventory-items/{id}/adjust-stock`
- Bewegungsprotokollierung über `inventory_movements`

---

## Feature 3
Finance & Bewertung.

**User Story**
Als Betreiber möchte ich Einkäufe, Verkäufe und Bewertungen erfassen, damit ich Profitabilität und Bestandswert nachvollziehen kann.

**Funktion**
- `purchases`, `sales`, `valuations`
- `request_key`-Idempotenz für kritische POST-Flows
- `GET /reports/finance-summary`

---

# 🧪 MVP Scope

Minimal funktionierende Version der App.

### Enthalten
- User Login / Token-basierte API-Auth
- Backoffice-Basis über Filament + API v1
- Hauptfunktion: Katalog + Inventar + Inventarbewegungen
- Basis-Finance-Module (Einkauf/Verkauf/Bewertung)

### Nicht enthalten
- Native Mobile App
- Vollständige Advanced Analytics/Drilldowns
- Vollständig durchgezogene Audit-Hash-Chain über alle kritischen Flows

---

# 🗂 Architektur

## Systemübersicht

Filament/Frontend → API `/api/v1` → Services/Policies → PostgreSQL (+ optional Redis/Queue)

## Module

### Auth
- Login/Registrierung (Breeze)
- Sanctum Tokens
- Rollen & Permissions

### User
- Profilverwaltung
- API-Endpunkt `GET /api/v1/me`

### Core Logic
- Catalog Module
- Inventory Module
- Finance Module
- Audit/Ledger (teilweise vorbereitet)

---

# 🗄 Datenbankstruktur

## Users

| Feld | Typ | Beschreibung |
|-----|-----|-------------|
| id | bigint/uuid (auto) | User ID |
| name | string | Benutzername |
| email | string | Email |
| created_at | timestamp | Erstellung |

## Weitere Kern-Tabellen
- `games`, `sets`, `products`
- `inventory_items`, `storage_locations`, `inventory_movements`
- `purchases`, `purchase_items`, `sales`, `sale_items`, `valuations`
- `audit_events`

---

# 🔌 API Struktur

## Auth

POST /api/v1/tokens  
GET /api/v1/me  

## Catalog

GET/POST /api/v1/games  
GET/PATCH/DELETE /api/v1/games/{id}  
GET/POST /api/v1/sets  
GET/PATCH/DELETE /api/v1/sets/{id}  
GET/POST /api/v1/products  
GET/PATCH/DELETE /api/v1/products/{id}  

## Inventory

GET/POST /api/v1/inventory-items  
GET/PATCH/DELETE /api/v1/inventory-items/{id}  
POST /api/v1/inventory-items/{id}/transfer  
POST /api/v1/inventory-items/{id}/adjust-stock  

## Finance

GET/POST /api/v1/purchases  
GET/POST /api/v1/sales  
GET/POST /api/v1/valuations  
GET /api/v1/reports/finance-summary  

---

# 🖥 UI / UX

## Seitenstruktur

- Landing Page (`/`)
- Login / Register
- Dashboard
- Profil/Settings
- Filament-Admin-Bereiche für Stammdaten

## Design System

- Farben: Tailwind-Utility-basiert
- Typography: Standard-Tailwind/Filament
- Komponenten: Blade + Filament + Livewire Komponenten

---

# 📋 Roadmap

## Phase 1 – Planung
- Zielarchitektur dokumentiert
- Spezifikation und Projektdokumente konsolidiert

## Phase 2 – MVP
- API-CRUD für Catalog/Inventory umgesetzt
- Transaktionale Inventarbewegungen umgesetzt
- Filament-Basisressourcen vorhanden

## Phase 3 – Testing/Finance-Ausbau
- Feature-/Unit-Tests vorhanden, weitere Contract-Härtung offen
- Finance-Basis implementiert, Reporting-Ausbau geplant

## Phase 4 – Launch/Operations
- Docker-Deployment vorbereitet
- Monitoring/Operations-Routinen in Arbeit

---

# 🧪 Testing

## Unit Tests
- Service- und Model-Tests (u. a. Inventory)

## Integration Tests
- API-Endpunkte für Catalog/Inventory/Finance

## UI Tests
- Basis-Filament-Zugriffs- und Auth-Tests

---

# 📊 Fortschritt

| Task | Status | Verantwortlich |
|-----|------|------|
| Projektstruktur | ✅ | Dev |
| Auth System | ✅ | Dev |
| Catalog & Inventory API | 🔄 | Dev |
| Finance Reporting Ausbau | 🔄 | Dev |
| Audit-Hash-Chain | ⏳ | Dev |

Status:
- ⏳ To Do
- 🔄 In Progress
- ✅ Done

---

# 🚀 Deployment

## Development
- Local Setup via Composer/npm oder Docker Compose

## Staging
- vorgesehen (Container-basiertes Testumfeld)

## Production
- Cloud/Server-Deployment via Docker Images + Compose

---

# 📈 Monitoring

- Application Logs
- Optional Sentry Error Tracking
- Queue Monitoring mit Horizon (vorgesehen)

---

# 📚 Dokumentation

## Developer Docs
Setup, Architektur, Spezifikation, Projektstruktur

## API Docs
Versionierte Endpunkte und Konventionen in `API_DOCS.md`

## User Docs
Backoffice-Nutzung aktuell implizit, dedizierte Guides als Ausbaupunkt

---

# 🧠 Ideen / Backlog

- periodisierte/kanalbezogene P/L Reports
- durchgängige Audit-Hash-Chain
- Marktplatz- und Preisdaten-Integrationen
- Meilisearch-basierte Volltext-/Facettensuche

---

# 🔐 Security

- Auth via Sanctum
- RBAC mit Rollen/Permissions/Policies
- Input Validation via FormRequests
- Transaktionale Sicherung kritischer Write-Flows

---

# 💰 Monetarisierung

- Noch nicht festgelegt (primär Produkt-/Plattformfokus)
- Mögliche Modelle: Freemium, Subscription, Enterprise/Team-Lizenzen

---

# 📅 Changelog

## v0.1
Projektstart, Foundations und Core Platform Setup

## v0.2
Catalog/Inventory CRUD, Transfer/Adjust-Stock, Finance-Basis

## v1.0
Geplanter stabiler Release nach Audit-/Reporting-Konsolidierung
