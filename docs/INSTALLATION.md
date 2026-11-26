# Installation & Upgrade Guide

This guide covers installing, updating, verifying, and uninstalling the **Listeo Bulk Add Listings** plugin on WordPress.

---

## Prerequisites

Before installing, confirm that your WordPress site meets the following requirements:

- **WordPress:** 5.8 or later
- **PHP:** 7.4 or later (PHP 8.1+ recommended)
- **Active Plugin:** **Listeo-Core** by Purethemes must be installed and activated (this plugin relies on the `listing` post type and the `listing_category` / `region` taxonomies registered by Listeo Core).
- **User Role:** You must be logged in as an **Administrator** (`manage_options` capability).

---

## Option 1: Install via WordPress Admin Dashboard (Recommended)

1. Download `listeo-bulk-add-listings.zip` from the latest [GitHub Release](https://github.com/justinallahyari/ActivityPie-Bulk-Add-Plugin/releases/latest) (or build it locally with `./scripts/build-zip.sh`, which outputs to `dist/listeo-bulk-add-listings.zip`).
2. Log in to your WordPress Admin Dashboard (`/wp-admin/`).
3. In the left sidebar, click **Plugins → Add New Plugin**.
4. Click the **Upload Plugin** button near the top of the page.
5. Click **Choose File**, select `listeo-bulk-add-listings.zip`, and click **Install Now**.
6. Once installation finishes, click **Activate Plugin**.

---

## Option 2: Install Manually via SFTP / File Manager

1. Download and extract `listeo-bulk-add-listings.zip` from the latest [GitHub Release](https://github.com/justinallahyari/ActivityPie-Bulk-Add-Plugin/releases/latest) (or copy the `plugin/listeo-bulk-add-listings/` folder directly).
2. Upload the `listeo-bulk-add-listings` folder to:
   ```text
   /wp-content/plugins/listeo-bulk-add-listings/
   ```
   Verify that the main plugin file is located at:
   ```text
   /wp-content/plugins/listeo-bulk-add-listings/listeo-bulk-add-listings.php
   ```
3. Log in to WordPress Admin, go to **Plugins → Installed Plugins**, find **Listeo Bulk Add Listings**, and click **Activate**.

---

## Verifying Installation

1. In the WordPress Admin left sidebar, hover over or click **Listings**.
2. Confirm that **Bulk Add Listings** appears in the Listings submenu.
3. Click **Bulk Add Listings** and verify:
   - The **Download Example Template (.csv)** button appears.
   - The **Select Listing Author** dropdown lists your WordPress users.
   - The sidebar displays your **Valid Listing Types**, **Existing Listing Categories**, and **Existing Regions**.

> **Note for Admin Menu Editor users:** If your site uses the **Admin Menu Editor** plugin and **Bulk Add Listings** does not appear immediately under **Listings**, open **Settings → Menu Editor**, locate the **Listings** menu, and ensure **Bulk Add Listings** is visible/enabled for Administrators.

---

## Uninstalling the Plugin

1. Go to **Plugins → Installed Plugins**.
2. Click **Deactivate** under **Listeo Bulk Add Listings**.
3. Click **Delete** to remove the plugin files.

> **Data Safety:** Deactivating or deleting this plugin does **not** delete any listings you previously created with it. All imported listings remain in WordPress under **Listings**.
