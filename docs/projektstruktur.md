# Projektstruktur – TCventory


## Dokumentationsstatus

- Stand: 2026-03-06
- Diese Datei wurde im Rahmen der projektweiten Dokumentationspflege auf Aktualität geprüft und sprachlich vereinheitlicht.

Diese Struktur bildet die in der technischen Spezifikation beschriebene modulare Laravel-Organisation ab.

```text
app/
  Actions/
    Inventory/
    Sales/
    Purchases/
  Domain/
    Catalog/
    Inventory/
    Finance/
    Audit/
    Shared/
  Filament/
    Resources/
    Pages/
    Widgets/
  Http/
    Controllers/
      Api/
        V1/
    Requests/
    Resources/
  Jobs/
  Listeners/
  Observers/
  Policies/
  Providers/
  Services/
  Support/

bootstrap/
config/
database/
  factories/
  migrations/
  seeders/

resources/
  views/
  css/
  js/

tests/
  Feature/
    Api/
    Filament/
  Unit/
    Domain/
    Services/

routes/
  web.php
  api.php

storage/
```

Hinweis: Leere Verzeichnisse enthalten bewusst `.gitkeep`, damit die Zielstruktur versioniert bleibt.
