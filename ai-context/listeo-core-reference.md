# Listeo Core Technical Reference (for AI Agents)

This reference summarizes the relevant **Listeo Core** internals so AI agents can modify or extend `listeo-bulk-add-listings` without needing access to the full Listeo Core plugin source.

---

## 1. Custom Post Type & Statuses

- **Post Type:** `listing` (registered in `Listeo_Core_Post_Types::register_post_types()`).
- **Supports:** `title`, `author`, `editor`, `custom-fields`, `publicize`, `thumbnail`, `comments`.
- **Standard & Custom Statuses:**
  - `publish`, `pending`, `draft`, `trash` (WordPress core)
  - `preview`, `expired`, `pending_payment`, `rejected` (Listeo custom statuses)
- **Admin Menu Parent Slug:** `edit.php?post_type=listing`
  - Listeo Core's `reorganize_listing_taxonomies()` reorganizes taxonomy submenu items under `edit.php?post_type=listing` but preserves custom plugin submenu pages registered via `add_submenu_page( 'edit.php?post_type=listing', ... )`.

---

## 2. Taxonomies Registered for `listing`

| Taxonomy Slug | Hierarchical | Notes |
|---|---|---|
| `listing_category` | Yes | Global categories shared across all listing types |
| `region` | Yes | Geographic regions |
| `listing_feature` | Yes | Features/amenities; categories can link features via term meta `listeo_taxonomy_multicheck` |
| `service_category` | Yes | Optional per-type taxonomy for `service` |
| `rental_category` | Yes | Optional per-type taxonomy for `rental` |
| `event_category` | Yes | Optional per-type taxonomy for `event` |
| `classifieds_category` | Yes | Optional per-type taxonomy for `classifieds` |
| `{custom_type_slug}_category` | Yes | Dynamic taxonomy registered for custom listing types with `register_taxonomy = 1` (truncated to WP's 32-char taxonomy limit) |

---

## 3. Listing Types API

Listeo Core supports dynamic custom listing types via `listeo_core_custom_listing_types()`:

```php
if ( function_exists( 'listeo_core_custom_listing_types' ) ) {
    $manager = listeo_core_custom_listing_types();
    $types   = $manager->get_listing_types( true ); // true = active types only
    // Each item is an object with ->slug, ->name, ->plural_name, ->is_active, ->is_default, ->register_taxonomy
}
```

Fallback default slugs when custom listing types manager is unavailable: `service`, `rental`, `event`, `classifieds`.

---

## 4. Key Listing Post Meta Fields

### Core & Search Meta
| Meta Key | Purpose |
|---|---|
| `_listing_type` | Listing type slug (`service`, `multi-day-rental`, etc.). Determines booking & field tabs. |
| `listing_title` | Duplicate of `post_title`; queried directly by Listeo's keyword search SQL (`IN ('listeo_subtitle','listing_title','listing_description','keywords')`). |
| `listing_description` | Duplicate of `post_content`; queried directly by Listeo's keyword search SQL. |
| `keywords` | Comma-separated search keywords. |
| `_featured` | `'0'` or `'on'`. Default `'0'` set on `save_post_listing`. |
| `_verified` | `'on'` or empty. |
| `listeo-avg-rating` | Numeric string, default `'0'` set on `wp_insert_post` for sorting. |
| `_listing_views_count` | Numeric string, default `'0'` set on `wp_insert_post` for sorting. |
| `_listing_expires` | Unix timestamp for expiration (set on transition to `publish`). |

### Location Meta (`locations_tab`)
| Meta Key | Purpose |
|---|---|
| `_address` | Google Maps / geolocation address. |
| `_friendly_address` | Human-readable display address. If `listeo_disable_address` option is enabled, Listeo displays `_friendly_address` only. Saving address to both ensures consistent display & search. |
| `_geolocation_lat` | Latitude (`-90` to `90`). |
| `_geolocation_long` | Longitude (`-180` to `180`). |
| `_place_id` | Google Maps Place ID (optional, used for Google Reviews). |

### Contact & Social Meta (`contact_tab`)
| Meta Key | Purpose |
|---|---|
| `_phone` | Phone number |
| `_email` | Contact email |
| `_email_contact_widget` | Checkbox (`'on'` or empty) to enable sidebar contact widget |
| `_website` | Website URL |
| `_facebook` | Facebook URL |
| `_twitter` | X / Twitter URL |
| `_youtube` | YouTube URL |
| `_instagram` | Instagram URL / handle |
| `_tiktok` | TikTok URL |
| `_whatsapp` | WhatsApp contact |

### Media, Timezone & Pricing Meta
| Meta Key | Purpose |
|---|---|
| `_video` | Video oEmbed URL (`video_tab`) |
| `_listing_logo` | Listing logo URL/attachment |
| `_gallery` | CMB2 `file_list` array (`attachment_id => url`) |
| `_listing_timezone` | IANA timezone string (e.g. `Asia/Taipei`, `America/New_York`) or WP `UTC±offset` (`wp_timezone_choice()`) |
| `_price_min`, `_price_max` | Display price range (`prices_tab`) |
| `_normal_price`, `_weekday_price`, `_reservation_price` | Booking pricing fields |
| `_booking_status`, `_slots_status`, `_opening_hours_status`, `_menu_status`, `_faq_status` | Section on/off checkboxes (`'on'` when enabled) |

### Client Custom Fields (Listeo Forms & Fields Editor)
| Meta Key | Tab | Notes |
|---|---|---|
| `_service_cancellation_refund_policy` | `service_tab` | Currently mapped from CSV column `Policy` |
| Additional fields in `contact_tab` | `contact_tab` | Bank Name, Bank Account Number, Company Number, Language Support (confirm exact meta keys in Listeo Editor before mapping) |
