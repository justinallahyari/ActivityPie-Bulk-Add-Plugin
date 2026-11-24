# Updating This Plugin Using an AI Agent

This repository is structured so that you (or a developer) can safely update, customize, and repackage the **Listeo Bulk Add Listings** plugin using an AI coding agent such as **Antigravity**, **Claude Code**, **Cursor**, **OpenAI Codex**, or **GitHub Copilot**.

---

## Why This Repo Is AI-Agent Ready

In addition to the plugin source code, this repository includes dedicated context files that give an AI agent immediate, accurate knowledge of your WordPress + Listeo Core setup:

- [`AGENTS.md`](../AGENTS.md) — Rules, architecture overview, step-by-step modification checklists, and build commands automatically read by most modern AI coding agents.
- [`CLAUDE.md`](../CLAUDE.md) — Companion entry point for Claude Code.
- [`ai-context/environment-profile.json`](../ai-context/environment-profile.json) — Machine-readable snapshot of your WordPress version, PHP version, active Listeo listing types, taxonomies, regions, and custom fields.
- [`ai-context/field-mappings.json`](../ai-context/field-mappings.json) — Machine-readable mapping of every CSV column to its exact WordPress/Listeo meta key, taxonomy, and validation rule.
- [`ai-context/listeo-core-reference.md`](../ai-context/listeo-core-reference.md) — Technical reference of Listeo Core's `listing` post type, meta keys, CMB2 tabs, and admin hooks.
- [`scripts/build-zip.sh`](../scripts/build-zip.sh) — One-command packaging script that builds `dist/listeo-bulk-add-listings.zip` locally (`dist/` is git-ignored; zips are uploaded to GitHub Releases).

---

## Standard Workflow for Updating with an AI Agent

1. **Clone or open this repository** in your AI coding tool (or point your AI agent at the local folder).
2. **State your change clearly** (use one of the prompt templates below as a starting point).
3. **Let the AI agent make the edits**, which should always include:
   - Updating `plugin/listeo-bulk-add-listings/listeo-bulk-add-listings.php`
   - Bumping the plugin `Version:` header (e.g., `1.0.0` → `1.1.0`)
   - Updating `plugin/listeo-bulk-add-listings/example-template.csv` (if CSV columns changed)
   - Updating `ai-context/field-mappings.json` and `docs/USER_GUIDE.md` so docs stay in sync
   - Running `./scripts/build-zip.sh` to build `dist/listeo-bulk-add-listings.zip` locally
4. **Review the changes** (`git diff`) and verify PHP syntax if PHP is installed locally (`php -l plugin/listeo-bulk-add-listings/listeo-bulk-add-listings.php`).
5. **Commit, push, and publish a GitHub Release** (e.g., `gh release create v1.1.0 dist/listeo-bulk-add-listings.zip --title "v1.1.0" --generate-notes`) so the zip is versioned cleanly without bloating git history.
6. **Upload the release zip** (`listeo-bulk-add-listings.zip`) in WordPress under **Plugins → Add New → Upload Plugin → Replace current with uploaded**.
7. **Test with a small 1-row CSV** before importing a large batch.

---

## Copy-Paste Prompt Templates

### 1. Add a New Custom Field Column to the CSV
```text
Read AGENTS.md and ai-context/field-mappings.json.
Add a new optional CSV column named "Language Support" that maps to the Listeo custom field meta key "_language_support" (verify the exact meta key if I provide one, or ask me first).
Update the plugin code, example-template.csv, ai-context/field-mappings.json, docs/USER_GUIDE.md, bump the plugin version to 1.1.0, and run scripts/build-zip.sh to rebuild dist/listeo-bulk-add-listings.zip.
```

### 2. Add Support for Listing Features (`listing_feature` taxonomy)
```text
Read AGENTS.md and ai-context/listeo-core-reference.md.
Add an optional "Features" column to the CSV that accepts comma-separated terms from the "listing_feature" taxonomy (e.g. "Free Parking, Free Wi-Fi, Pet Friendly").
Validate that each specified feature exists in "listing_feature" just like Category and Region, show existing features in the admin sidebar, update example-template.csv and docs, bump the version, and rebuild the zip.
```

### 3. Change Validation Behavior (e.g., Make a Field Required or Optional)
```text
Read AGENTS.md and plugin/listeo-bulk-add-listings/listeo-bulk-add-listings.php.
Make the "Region" column required (reject rows where Region is empty) and update the Column Reference Table, ai-context/field-mappings.json, and docs/USER_GUIDE.md. Bump the plugin patch version and rebuild dist/listeo-bulk-add-listings.zip.
```

### 4. Add a New Listing Field for `multi-day-rental` Listings
```text
Read AGENTS.md and ai-context/environment-profile.json.
Add support for minimum/maximum price columns ("Price Min" -> "_price_min", "Price Max" -> "_price_max") with numeric >= 0 validation. Update all template, docs, and AI context files, bump the version, and rebuild the zip.
```

### 5. Update `ai-context/environment-profile.json` After Site Changes
If you add new categories, regions, listing types, or custom fields in Listeo Editor on WordPress:
```text
Update ai-context/environment-profile.json with these new Listeo custom fields / listing types: [paste details here]. Do not change plugin behavior unless asked.
```

---

## Best Practices & Safety Checklist

- **Always keep `post_status = 'pending'`** unless you explicitly intend to auto-publish listings.
- **Never commit credentials, database exports, or API keys** into this public repository.
- **Test in WordPress after every update:**
  1. Download the example template from **Listings → Bulk Add Listings** and confirm headers match.
  2. Upload an intentionally invalid CSV (e.g., fake category) and verify the upload is rejected with a clear error message and zero posts created.
  3. Upload a valid 1-row CSV and verify the resulting Pending listing in **Listings → All Listings** has every field populated correctly in the editor.
