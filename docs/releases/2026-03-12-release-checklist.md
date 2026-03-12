# Release Checklist – 2026-03-12

## Scope

1. Changelog finalisieren
2. Release Notes erstellen
3. Release-Tag erstellen
4. GitHub-Release publizieren

## Ergebnisse

### 1) Changelog finalisieren

- `CHANGELOG.md` wurde von `Unreleased` auf `0.1.1` (Datum 2026-03-12) geschnitten.

### 2) Release Notes erstellen

- `docs/releases/2026-03-12-v0.1.1.md` wurde erstellt.

### 3) Release-Tag erstellen

- Lokales Annotated Tag `v0.1.1` wurde erstellt.

### 4) GitHub-Release publizieren

- In dieser Laufzeitumgebung nicht möglich:
  - `gh` CLI nicht installiert.
  - Kein `git remote` konfiguriert.
- Follow-up in einer verbundenen Umgebung:
  - `git push origin v0.1.1`
  - `gh release create v0.1.1 --title "v0.1.1" --notes-file docs/releases/2026-03-12-v0.1.1.md`

## Ergebnis

- Release-Artefakte und lokales Tag sind vorbereitet.
- GitHub-Publikation ist als klarer Folge-Schritt dokumentiert.
