# Projektstruktur – TCventory

## Dokumentationsstatus

- Stand: 2026-03-12
- Strukturübersicht auf den realen Repository-Zustand aktualisiert.

Diese Struktur beschreibt die aktuell versionierte Projektorganisation (vereinfachte, aber reale Top-Level-Sicht).

```text
app/
  Filament/
  Http/
    Controllers/
    Requests/
  Models/
  Policies/
  Providers/
  Services/

bootstrap/
config/
database/
  factories/
  migrations/
  seeders/
docker/
  app/
  nginx/
docs/
  deployment/
  releases/
  reviews/
public/
resources/
  css/
  js/
  views/
routes/
storage/
tests/
  Concerns/
  Feature/
  Unit/

.github/
  workflows/
```

## Hinweis

Historische oder Zielbild-Strukturen (z. B. dedizierte `Domain/`- oder `Actions/`-Layer) sind nicht automatisch Bestandteil des aktuellen Dateibaums und werden erst bei konkreter Implementierung ergänzt.
