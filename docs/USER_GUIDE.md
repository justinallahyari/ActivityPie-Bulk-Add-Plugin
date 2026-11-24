# User Guide — Bulk Add Listings

This guide explains how to prepare a CSV spreadsheet, upload listings in bulk, resolve any validation errors, and review/publish the created pending listings in WordPress.

---

## Step 1: Open the Bulk Add Listings Page

1. Log in to WordPress Admin as an Administrator.
2. In the left navigation menu, go to **Listings → Bulk Add Listings**.
3. Take note of the **Reference Sidebar** on the right side of the page, which shows the exact live values currently configured on your site:
   - **Valid Listing Types** (e.g., `service`, `multi-day-rental`)
   - **Existing Listing Categories** (e.g., `Adventure`, `Art`, `Culture`, `Education`, `Sport`)
   - **Existing Regions** (e.g., `New Taipei`, `Taipei`, `Taichung`, etc.)

---

## Step 2: Download the Example CSV Template

1. Click **Download Example Template (.csv)**.
2. Open the downloaded file (`listeo-bulk-listings-template.csv`) in **Google Sheets**, **Microsoft Excel**, or **Apple Numbers**.
3. Row 1 contains the required column headers. Row 2 contains a sample listing pre-populated with a valid listing type, category, and region from your site.
4. Replace Row 2 with your first real listing and add one row per listing you want to import.

> **Spreadsheet Tip:** When saving or exporting from Excel, choose **CSV UTF-8 (Comma delimited) (.csv)** so special characters, accents, and non-English characters (such as Traditional Chinese) are preserved properly.

---

## Step 3: Fill Out the CSV Columns

All 18 column headers must remain present in Row 1 (headers are matched case-insensitively, and column order does not matter as long as every required header exists).

| Column Header | Required? | Listeo Field / Taxonomy | Formatting & Validation Rules |
|---|---|---|---|
| `Listing name` | **Required** | Post Title (`post_title` & `listing_title`) | Must not be blank. Also used to generate the URL slug. |
| `Listing type` | **Required** | `_listing_type` & `listing_type` | Must match an active Listeo listing type by **slug or display name** (e.g., `service`, `Time-Slot Booking`, `multi-day-rental`, `Multi-Day Rental`). |
| `category` | **Required** | `listing_category` taxonomy | Must match one or more **existing** categories by name, slug, or term ID. Separate multiple categories with commas (e.g., `"Adventure, Sport"`). |
| `Keywords` | Optional | `keywords` | Comma-separated search keywords (e.g., `"kayaking, outdoor, guided tour"`). |
| `Description` | Optional | Post Content (`post_content` & `listing_description`) | Full description text. Basic safe HTML formatting is allowed. |
| `Video embed` | Optional | `_video` | URL to an oEmbed-supported video (e.g., YouTube or Vimeo link). |
| `Phone` | Optional | `_phone` | Contact phone number (any readable format). |
| `Website` | Optional | `_website` | Valid website URL. If `https://` is omitted (e.g., `example.com`), `https://` is added automatically. |
| `Email` | Optional | `_email` | Valid email address syntax (e.g., `hello@example.com`). |
| `FaceBook` | Optional | `_facebook` | Facebook page URL or handle. |
| `Youtube` | Optional | `_youtube` | YouTube channel or video URL. |
| `instagram` | Optional | `_instagram` | Instagram profile URL or handle. |
| `Address` | Optional | `_address` & `_friendly_address` | Street / display address (saved to both Google Maps Address and Friendly Address). |
| `Region` | Optional | `region` taxonomy | If provided, must match one or more **existing** regions by name, slug, or term ID. Separate multiple regions with commas. |
| `Longitude` | Optional | `_geolocation_long` | Numeric value between `-180` and `180` (e.g., `121.5654`). |
| `latitude` | Optional | `_geolocation_lat` | Numeric value between `-90` and `90` (e.g., `25.0330`). |
| `Listing Timezone` | Optional | `_listing_timezone` | Valid IANA timezone (e.g., `Asia/Taipei`, `America/New_York`) or WordPress UTC offset (e.g., `UTC+8`, `UTC-5`). |
| `Policy` | Optional | `_service_cancellation_refund_policy` | Service cancellation and refund policy text. |

### Notes on Commas & Multi-Line Text in CSV
- If a cell contains a comma (for example, multiple categories, keywords, an address, or a description), your spreadsheet editor automatically wraps the cell in double quotes (`"..."`). Do not edit raw CSV files manually without quoting cells that contain commas or line breaks.
- Completely empty trailing rows at the bottom of the CSV are automatically ignored.

---

## Step 4: Select Author & Upload

1. Under **2. Upload CSV Template & Assign Author**, choose the WordPress user from **Select Listing Author**. Every listing in this upload will be owned by that user.
2. Click **Choose File** under **CSV Template File** and select your `.csv` file.
3. Click **Validate & Bulk Add Pending Listings**.

---

## Step 5: Review Validation Results

The plugin uses **all-or-nothing validation**:
- **If any errors are detected**, **zero listings are created**. A red error report table appears listing the exact **CSV Row**, **Listing Name**, **Column**, and **Error Details** for every issue found so you can fix all errors at once and re-upload.
- **If all rows pass validation**, every row is created as a **Pending** listing, and a green success summary displays each created listing with a direct **Edit Listing** link.

### Common Validation Errors & How to Fix Them

| Error Message | Cause | Fix |
|---|---|---|
| `Missing required CSV column header(s)` | A column was renamed or deleted from Row 1. | Restore the exact column header name from the template. |
| `Unrecognized CSV column header(s)` | An extra unsupported column was added to Row 1. | Remove the extra column (or ask your developer/AI agent to add support for it). |
| `Expected X columns... but found Y columns` | Unescaped commas or mismatched quotes in a raw CSV row. | Open and re-export the file as CSV UTF-8 from Google Sheets or Excel. |
| `Invalid Listing type "..."` | The value does not match an active listing type. | Use a slug or name listed under **Valid Listing Types** in the sidebar. |
| `Category does not exist in Listing Categories` | Typo in category name, or category hasn't been created yet. | Match an existing category in the sidebar, or create it first under **Listings → Categories**. |
| `Region does not exist in Regions` | Typo in region name, or region hasn't been created yet. | Match an existing region in the sidebar, or create it first under **Listings → Regions**. |
| `Invalid Longitude / latitude` | Non-numeric text or out-of-range coordinate (e.g., lat/long swapped). | Verify Latitude is between `-90` and `90` and Longitude is between `-180` and `180`. |
| `Invalid Listing Timezone` | Unrecognized timezone abbreviation (e.g., `PST` or `CST`). | Use an IANA identifier like `Asia/Taipei` or `America/Los_Angeles`, or `UTC+8` / `UTC-8`. |

---

## Step 6: Review & Publish Pending Listings

1. Go to **Listings → All Listings** and click the **Pending** filter tab.
2. Open any imported listing to add images/gallery photos, configure booking slots or pricing, verify map coordinates, and double-check custom fields.
3. Click **Publish** (or use **Bulk Actions → Approve Listings**) when ready to make listings live.
