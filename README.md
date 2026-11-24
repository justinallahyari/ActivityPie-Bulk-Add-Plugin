# ActivityPie — Listeo Bulk Add Listings Plugin

A lightweight WordPress plugin built for **Listeo Core** that enables administrators to bulk-create **Pending** listings by uploading a CSV file directly from the WordPress admin dashboard (**Listings → Bulk Add Listings**).

---

## Features

- **Admin Page under Listings:** Accessible at **Listings → Bulk Add Listings** (`edit.php?post_type=listing&page=listeo-bulk-add-listings`).
- **One-Click Example CSV Template:** Dynamically generates a ready-to-use `.csv` template pre-filled with an active listing type, category, and region from your WordPress site.
- **Author Assignment:** Choose any registered WordPress user to be assigned as the author (`post_author`) for all listings in the batch.
- **All-or-Nothing Validation:** Validates CSV structure, column headers, required fields, active listing types, existing `listing_category` and `region` taxonomy terms, email syntax, website URLs, latitude/longitude ranges, and timezones **before** inserting any posts. If any row has an error, the upload is rejected with a row-by-row error report so you can fix and re-upload cleanly.
- **Pending Review Workflow:** Every listing created via bulk upload is saved with `post_status = 'pending'`, ready for admin review before publishing.
- **On-Page Reference Sidebar:** Displays your site's active Listing Types, existing Listing Categories, and existing Regions directly alongside the upload form.

---

## Repository Structure

```text
ActivityPie-Bulk-Add-Plugin/
├── README.md                              # Project overview & quick links
├── AGENTS.md                              # Primary instructions for AI coding agents
├── CLAUDE.md                              # Companion instructions for Claude Code / AI agents
├── plugin/
│   └── listeo-bulk-add-listings/
│       ├── listeo-bulk-add-listings.php   # Main WordPress plugin source
│       └── example-template.csv           # Static sample CSV template
├── dist/
│   └── listeo-bulk-add-listings.zip       # Pre-packaged WordPress plugin zip ready for upload
├── scripts/
│   └── build-zip.sh                       # Script to rebuild dist/listeo-bulk-add-listings.zip
├── docs/
│   ├── INSTALLATION.md                    # How to install, upgrade, and uninstall the plugin
│   ├── USER_GUIDE.md                      # How to prepare CSVs, upload listings, and fix errors
│   └── AI_AGENT_MAINTENANCE.md            # How to update or extend this plugin using an AI agent
└── ai-context/
    ├── environment-profile.json           # Target WordPress + Listeo Core environment schema
    ├── field-mappings.json                # Machine-readable CSV column -> Listeo field mappings
    └── listeo-core-reference.md           # Listeo Core technical reference (CPT, meta, taxonomies)
```

---

## Documentation

| Document | Audience | Purpose |
|---|---|---|
| [`docs/INSTALLATION.md`](docs/INSTALLATION.md) | Site Admins | Install, update, verify, or uninstall the plugin in WordPress |
| [`docs/USER_GUIDE.md`](docs/USER_GUIDE.md) | Content / Ops Team | Download the CSV template, format data, upload listings, and resolve validation errors |
| [`docs/AI_AGENT_MAINTENANCE.md`](docs/AI_AGENT_MAINTENANCE.md) | Client / Developers | How to safely request plugin updates using an AI coding agent |
| [`AGENTS.md`](AGENTS.md) | AI Coding Agents | Architecture, coding rules, packaging commands, and step-by-step modification instructions |

---

## Quick Start

1. Download [`dist/listeo-bulk-add-listings.zip`](dist/listeo-bulk-add-listings.zip).
2. In WordPress Admin, go to **Plugins → Add New → Upload Plugin**, choose the `.zip` file, click **Install Now**, and click **Activate Plugin**.
3. Navigate to **Listings → Bulk Add Listings**.
4. Click **Download Example Template (.csv)**, fill in your listings, select the listing author, upload your CSV, and click **Validate & Bulk Add Pending Listings**.

---

## Requirements

- **WordPress:** 5.8 or higher (tested on 6.9.x)
- **PHP:** 7.4 or higher (tested on PHP 8.3)
- **Required Plugin:** Listeo Core (`listing` post type, `listing_category` and `region` taxonomies)
