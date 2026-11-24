# CLAUDE.md

See [`AGENTS.md`](AGENTS.md) for complete repository architecture, coding invariants, modification checklists, and packaging commands.

## Quick Context Files for Agents
- [`AGENTS.md`](AGENTS.md) — Authoritative instructions and modification checklist
- [`ai-context/environment-profile.json`](ai-context/environment-profile.json) — Target WordPress + Listeo Core environment schema
- [`ai-context/field-mappings.json`](ai-context/field-mappings.json) — Canonical CSV column → Listeo field mappings and validation rules
- [`ai-context/listeo-core-reference.md`](ai-context/listeo-core-reference.md) — Listeo Core CPT, taxonomy, and meta box technical reference

## Build & Release Commands
After editing anything in `plugin/listeo-bulk-add-listings/`, build the uploadable WordPress zip locally (`dist/` is git-ignored; attach the zip to a GitHub Release):
```bash
./scripts/build-zip.sh
gh release create vX.Y.Z dist/listeo-bulk-add-listings.zip --title "vX.Y.Z" --generate-notes
```
