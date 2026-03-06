# Release-Dokumentationsprozess

## Ziel
Diese Datei standardisiert, welche Release-Dokumente bei jedem Release erstellt und gepflegt werden.

## Pflicht-Artefakte pro Release
1. **Changelog-Eintrag** in `CHANGELOG.md`
2. **Release Notes** unter `docs/releases/`
3. **Release-Checklist** unter `docs/releases/`

## Dateinamenskonventionen
- Release Notes: `YYYY-MM-DD-vX.Y.Z.md`
- Release Checklist: `YYYY-MM-DD-release-checklist.md`

## Update-Reihenfolge (verbindlich)
1. Release-Checklist ausfüllen/abschließen.
2. Release Notes erstellen/aktualisieren.
3. Changelog-Eintrag finalisieren.

## Minimale Vorlage – Release Notes
```md
# Release Notes – vX.Y.Z (YYYY-MM-DD)

## Highlights
- ...

## Validierungsstatus
- Tests: ...
- Static Analysis: ...
- Style: ...
- Build: ...
- Deployment: ...

## Referenzen
- Changelog: `CHANGELOG.md`
- Checklist: `docs/releases/YYYY-MM-DD-release-checklist.md`
```

## Minimale Vorlage – Release Checklist
```md
# Release Checklist – YYYY-MM-DD

## Scope
1. Tests
2. Env Variablen
3. Migrationen
4. Build
5. Deployment

## Ergebnisse
- Tests: ...
- Env: ...
- Migrationen: ...
- Build: ...
- Deployment: ...
```

## Verlinkungspflicht
- `README.md` verweist auf die Deployment- und Dokumentationslandkarte.
- `CHANGELOG.md` verweist auf Release-Artefakte unter `docs/releases/`.
