# Database Design — Car Rental Website (Version 1)

---

> **Authoritative Source:** This document is derived from and must remain consistent with
> `System_Architecture_Plan.md` (v1.6.0). All architectural decisions referenced here
> are defined in that document. This document defines only the database implementation
> required to realize Version 1 of the system.

---

## 1. Purpose and Scope

This document defines the complete Version 1 database design for the Car Rental Website.

**What this document covers:**
- The exact set of V1 tables and their purpose
- Column definitions, data types, and constraints
- Relationships between entities
- Indexing strategy
- Deletion behavior per entity
- Considerations specific to Laravel Filament, Eloquent ORM, and the public REST API
- A brief summary of how V2 features can be added without modifying V1 structure

**What this document does not cover:**
- Application layer architecture (covered in `System_Architecture_Plan.md`)
- API endpoint contracts (to be defined in `API_Contract.md`)
- Laravel migration code (to be written during implementation)
- Frontend architecture
- V2 tables, stubs, or placeholders

---

## 2. Database Design Principles

The following principles apply specifically to this database design:

1. **Laravel conventions first.** Column names, primary key types (`BIGINT UNSIGNED`), timestamp pair (`created_at`, `updated_at`), and foreign key naming follow Laravel/Eloquent defaults wherever possible to minimize friction with the ORM.

2. **Only what V1 actually needs.** No table exists unless it is required by a documented V1 functional requirement. Each table's existence must be justified by a user-facing feature or an admin content management operation.

3. **Simple over normalized.** Where a JSON column or a nullable string column on an existing table solves a problem cleanly, it is preferred over adding a new table. For example, FAQ grouping uses a `category` string column on the `faqs` table rather than a separate `faq_categories` table.

4. **MySQL 8.0 compatibility.** All column types and index strategies are compatible with MySQL 8.0. JSON columns are used where appropriate; MySQL 8.0's native JSON support is leveraged.

5. **No soft deletes by default.** Hard delete is used throughout V1, as defined in `System_Architecture_Plan.md` §4.2. The only exception is if Filament's default behavior introduces one — in which case that is documented explicitly per table.

6. **Referential integrity enforced at the database level.** Foreign keys are declared with explicit `ON DELETE` behavior. The application layer never bypasses this.

7. **Index only what is queried.** Indexes are added to columns that appear in `WHERE`, `ORDER BY`, or `JOIN` clauses in predictable V1 queries. Premature indexing on speculative future queries is avoided.

---

## 3. Version 1 Database Scope

### Implemented in V1

| # | Table | Purpose |
|---|-------|---------|
| 1 | `users` | Existing Laravel authentication table used by the V1 Filament operator |
| 2 | `categories` | Car classification (SUV, Sedan, Luxury, etc.) |
| 3 | `cars` | Core car entity — the primary content of the website |
| 4 | `car_features` | Feature tags per car (A/C, GPS, Bluetooth, etc.) |
| 5 | `media` | Spatie Media Library storage for car gallery images |
| 6 | `faqs` | Frequently asked questions content |
| 7 | `banners` | Hero slider images and content for the homepage |
| 8 | `settings` | Key-value store for all site configuration and contact info |

**Total: 8 core application tables.** Laravel infrastructure tables such as `sessions`, `cache`, and queue tables also exist but are outside the domain schema count.

### Explicitly Excluded from V1

The following entities are **not implemented** in V1. No tables, migrations, model stubs, or placeholder columns exist for them:

| Excluded Entity | Reason |
|-----------------|--------|
| `customers` | No customer account system in V1 |
| `bookings` | All booking is via WhatsApp redirect — no server-side storage |
| `payments` | No payment processing in V1 |
| `drivers` | Fleet management is a V2 feature |
| `branches` | Single-location business in V1 |
| `fleet_status_logs` | No fleet tracking in V1 |
| `invoices` | No transactions in V1 |
| `coupons` | No discount system in V1 |
| `notifications` | No notification system in V1 |

> **Booking data is never stored.** The Pre-Booking Inquiry Form (`booking.html`) collects customer input client-side only, generates a WhatsApp message string, and redirects the browser to `wa.me`. No server request is made on form submission. No inquiry data touches the database.

---

## 4. Entity Overview

### 4.1 `users`
**Purpose:** Stores the single trusted V1 operator who authenticates into the Filament admin panel.
**Why it exists:** This is Laravel's existing authentication table and model. Filament uses the default `web` guard, while `User::canAccessPanel()` explicitly restricts production panel access.
**Required:** Yes — Filament cannot function without it.

### 4.2 `categories`
**Purpose:** Groups cars into browsable types (SUV, Sedan, Luxury, Economy, etc.).
**Why it exists:** The public car listing page supports filtering by category. Filament must provide CRUD for categories. A separate table gives categories their own slug, description, and display order.
**Required:** Yes.

### 4.3 `cars`
**Purpose:** The central content entity. Stores all displayable information about each rental car.
**Why it exists:** This is the primary product being marketed. Every public page either displays or links to cars.
**Required:** Yes.

### 4.4 `car_features`
**Purpose:** Stores discrete feature tags per car (e.g., "Air Conditioning", "GPS", "Bluetooth", "Sunroof").
**Why it exists:** Features are displayed as a list on the car detail page and managed through Filament. A separate table (rather than a JSON column) allows future filterability without a schema change.
**Required:** Yes. See §5.5 for the design decision rationale.

### 4.5 `media`
**Purpose:** Polymorphic storage record for car gallery images (multiple images per car, with ordering and cover selection).
**Why it exists:** Cars require multiple images with ordering and a designated cover. Spatie Media Library provides this out of the box and integrates directly with Filament's `SpatieMediaLibraryFileUpload` component. Single-image entities (`banners`, `categories`) store their image as a plain VARCHAR path column — no polymorphic media needed for them.
**Required:** Yes — the `media` table schema is created by Spatie Media Library's own migration; it is not hand-written.

### 4.6 `faqs`
**Purpose:** Stores FAQ items displayed on the public FAQ page.
**Why it exists:** The FAQ page is a documented V1 public page. Filament must provide CRUD for FAQ items.
**Required:** Yes.

### 4.7 `banners`
**Purpose:** Stores hero slider content (image, title, subtitle, call-to-action) for the homepage.
**Why it exists:** The homepage has a hero banner/slider section managed through Filament.
**Required:** Yes.

### 4.8 `settings`
**Purpose:** A key-value store for all site configuration: company name, contact information, social media links, SEO settings, and appearance.
**Why it exists:** Company information, contact details, and social links are all managed through the Filament settings page. A key-value table allows settings to be added without schema migrations.
**Required:** Yes.

---

## 5. Design Decisions

### 5.1 Car Specifications — JSON Column

Car specifications (engine size, transmission type, fuel type, number of seats, doors, drive type, etc.) vary significantly between vehicle types. A luxury SUV has specifications a compact sedan does not have, and vice versa.

**Decision:** Store specifications as a `JSON` column on the `cars` table.

**Alternatives considered:**
- `car_specifications` table (EAV or typed rows) — adds N+1 risk and complex queries for little gain in V1
- Predefined columns per spec — creates a wide table full of `NULL` values with poor scalability

**Why JSON:** MySQL 8.0 provides native JSON type with efficient storage and indexed path extraction if needed. All specifications are retrieved with the car in a single query. The admin manages specs via Filament's `KeyValue` form component. No filtering by specification is required in V1, so the lack of per-column indexes is not a performance concern.

### 5.2 Car Features — Separate Table (not JSON)

**Decision:** Store car features in a separate `car_features` table with one row per feature per car.

**Alternatives considered:**
- JSON column (e.g., `["AC", "GPS", "Bluetooth"]`) — simple but blocks SQL-level filtering
- Comma-delimited text column — not maintainable; no relational integrity

**Why separate table:** Feature values like "Air Conditioning" and "GPS" are candidates for future filtering on the car listing page ("show only cars with GPS"). A separate table makes this additive in V2 with no schema change. Filament's `Repeater` or `TagsInput` component handles this table natively.

**Cascade behavior:** When a car is hard-deleted, all its `car_features` rows are deleted via `ON DELETE CASCADE`.

### 5.3 Settings — Key-Value Table (not individual columns)

**Decision:** All site configuration lives in a single `settings` table with `key`, `value`, `type`, `settings_group`, and `description` columns.

**Why:** The range of settings (company name, contact, social, SEO, appearance, system flags) would require a wide, sparse table if normalized. The key-value pattern allows new settings to be added by new V2 modules with only a database seeder — no schema migration required.

**Trade-off:** No column-level SQL constraints on individual setting values. Mitigated by the `SettingService` enforcing type casting based on the `type` column at the application layer.

### 5.4 Pricing — Internal Reference Only

Per `System_Architecture_Plan.md` §3.1.1: rental prices are **not displayed on the public website**. Pricing is communicated through the WhatsApp conversation.

**Decision:** `price_daily`, `price_weekly`, and `price_monthly` columns are kept on the `cars` table as nullable internal reference fields. They are visible only in the Filament admin and not exposed through the public API response.

This preserves the ability to show pricing in V2 without a schema change. All three columns are `NULL`-able and `UNSIGNED DECIMAL(10,2)`.

### 5.5 Media Strategy — Spatie Media Library for Car Images Only

**Decision:** Use `spatie/laravel-media-library` (and its `media` table) for car images only. All other image fields (`banners.image`, `categories.image`) use a plain `VARCHAR(500)` path column.

**Why Spatie for cars:** Cars have multiple images requiring ordering and a designated cover image. Spatie's package handles all of this natively and its `SpatieMediaLibraryFileUpload` Filament component makes the admin integration seamless. File deletion on model delete is automatic via model events.

**Why VARCHAR for banners and categories:** Each banner has exactly one image. Each category has at most one image. Using the polymorphic `media` table for single-file entities adds a join with no benefit. A VARCHAR path column is simpler, faster, and easier to reason about.

**Logo and favicon:** Stored as file path strings in the `settings` table (`company.logo`, `appearance.favicon`). Not in `media`.

**Important:** The `media` table schema is owned by Spatie Media Library's migration. Do not create or redefine it manually. Run `php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="migrations"` during setup.

### 5.6 No `car_images` Table

The architecture document §8.1 notes "car_images (legacy, alongside polymorphic media)". **The `car_images` table is NOT created in V1.** Spatie Media Library's `media` table with `collection = 'car_images'` covers this requirement entirely.

### 5.7 No Separate Contact/Company Tables

Company information, contact details, and social media links are all stored as setting keys within the `settings` table. They do not need their own tables because:
- They are singleton data (one value per key)
- They are managed through a single Filament settings page
- A key-value table handles this pattern idiomatically

---

## 6. Table Specifications

---

### 6.1 `users`

**Purpose:** Laravel's existing authentication table, used for the single V1 Filament operator.

| Column | Data Type | Nullable | Default | Key | Description |
|--------|-----------|----------|---------|-----|-------------|
| `id` | `BIGINT UNSIGNED` | NO | auto | PK | Auto-increment primary key |
| `name` | `VARCHAR(255)` | NO | — | — | Operator display name |
| `email` | `VARCHAR(255)` | NO | — | UNIQUE | Login email address |
| `email_verified_at` | `TIMESTAMP` | YES | NULL | — | Standard Laravel verification timestamp |
| `password` | `VARCHAR(255)` | NO | — | — | Bcrypt-hashed password |
| `remember_token` | `VARCHAR(100)` | YES | NULL | — | Laravel "remember me" token |
| `created_at` | `TIMESTAMP` | YES | NULL | — | Laravel standard timestamp |
| `updated_at` | `TIMESTAMP` | YES | NULL | — | Laravel standard timestamp |

**Constraints:**
- `UNIQUE` on `email`

**Indexes:**
- Primary key on `id`
- Unique index on `email` (authentication lookup)

**Notes:**
- Filament uses this table through Laravel's existing `web` guard and `users` provider
- The existing `User` model implements Filament's `FilamentUser` contract
- `canAccessPanel()` checks the admin panel ID and explicitly configured operator email in production
- No role column or permissions package is introduced in V1
- No soft delete

---

### 6.2 `categories`

**Purpose:** Car categories for grouping and filtering.

| Column | Data Type | Nullable | Default | Key | Description |
|--------|-----------|----------|---------|-----|-------------|
| `id` | `BIGINT UNSIGNED` | NO | auto | PK | Auto-increment primary key |
| `name` | `VARCHAR(255)` | NO | — | — | Display name (e.g., "SUV", "Luxury") |
| `slug` | `VARCHAR(255)` | NO | — | UNIQUE | URL-safe identifier; auto-generated |
| `description` | `TEXT` | YES | NULL | — | Optional short description |
| `icon` | `VARCHAR(100)` | YES | NULL | — | Icon class name or identifier (e.g., `fa-car`) |
| `image` | `VARCHAR(500)` | YES | NULL | — | Path to category image in storage |
| `is_active` | `TINYINT(1)` | NO | `1` | IDX | Controls visibility in public API |
| `sort_order` | `INT` | NO | `0` | IDX | Controls display sequence |
| `created_at` | `TIMESTAMP` | YES | NULL | — | Laravel standard timestamp |
| `updated_at` | `TIMESTAMP` | YES | NULL | — | Laravel standard timestamp |

**Constraints:**
- `UNIQUE` on `slug`

**Indexes:**
- Primary key on `id`
- Unique index on `slug` (URL resolution, API filtering)
- Index on `is_active` (public API filter: only active categories)
- Index on `sort_order` (ordered display)

**Deletion behavior:**
- A category **cannot** be deleted if it has associated cars (`RESTRICT` on the FK from `cars.category_id`).
- The admin must reassign or delete the cars first.
- No soft delete.

---

### 6.3 `cars`

**Purpose:** The primary content entity. Every car displayed on the public website is a row in this table.

| Column | Data Type | Nullable | Default | Key | Description |
|--------|-----------|----------|---------|-----|-------------|
| `id` | `BIGINT UNSIGNED` | NO | auto | PK | Auto-increment primary key |
| `category_id` | `BIGINT UNSIGNED` | NO | — | FK, IDX | Foreign key to `categories.id` |
| `name` | `VARCHAR(255)` | NO | — | — | Full display name (e.g., "BMW 5 Series") |
| `slug` | `VARCHAR(255)` | NO | — | UNIQUE | SEO-friendly URL identifier |
| `brand` | `VARCHAR(100)` | NO | — | IDX | Manufacturer name (e.g., "BMW") |
| `model` | `VARCHAR(100)` | NO | — | — | Model designation (e.g., "5 Series") |
| `year` | `YEAR` | NO | — | — | Model year (e.g., 2024) |
| `color` | `VARCHAR(100)` | YES | NULL | — | Exterior color description |
| `description` | `LONGTEXT` | YES | NULL | — | Full marketing description (rich text) |
| `specifications` | `JSON` | YES | NULL | — | Structured specs: engine, transmission, seats, fuel, etc. |
| `price_daily` | `DECIMAL(10,2) UNSIGNED` | YES | NULL | — | Internal reference price; not displayed publicly |
| `price_weekly` | `DECIMAL(10,2) UNSIGNED` | YES | NULL | — | Internal reference price; not displayed publicly |
| `price_monthly` | `DECIMAL(10,2) UNSIGNED` | YES | NULL | — | Internal reference price; not displayed publicly |
| `currency` | `VARCHAR(3)` | NO | `AED` | — | ISO 4217 currency code |
| `is_published` | `TINYINT(1)` | NO | `0` | IDX | `1` = visible to public; `0` = draft |
| `is_featured` | `TINYINT(1)` | NO | `0` | IDX | `1` = shown in "Featured Cars" section |
| `sort_order` | `INT` | NO | `0` | IDX | Manual display ordering within listing |
| `meta_title` | `VARCHAR(255)` | YES | NULL | — | SEO page title override |
| `meta_description` | `TEXT` | YES | NULL | — | SEO meta description override |
| `created_at` | `TIMESTAMP` | YES | NULL | — | Laravel standard timestamp |
| `updated_at` | `TIMESTAMP` | YES | NULL | — | Laravel standard timestamp |

**Constraints:**
- `UNIQUE` on `slug`
- `FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT ON UPDATE CASCADE`

**Indexes:**

| Index | Columns | Purpose |
|-------|---------|---------|
| PRIMARY | `id` | PK lookup |
| `cars_slug_unique` | `slug` | URL-based detail page lookup |
| `cars_category_id_index` | `category_id` | Category filter queries |
| `cars_is_published_index` | `is_published` | Public API: only published cars |
| `cars_is_featured_index` | `is_featured` | Homepage featured cars query |
| `cars_sort_order_index` | `sort_order` | Ordered listing queries |
| `cars_brand_index` | `brand` | Filament admin filter by brand |

**Deletion behavior:**
- **Hard delete.** When a car is deleted, its `car_features` rows cascade-delete. Its `media` rows must be cleaned up by the `MediaService` before or alongside the delete (Spatie Media Library handles this automatically via model events).
- No `deleted_at` column. No soft delete.

**Notes on pricing:**
- Prices are nullable intentionally. The admin may leave prices blank and communicate them via WhatsApp only.
- Prices are stored for internal admin reference but are **excluded from public API responses**.
- V2 can add `price_per_km`, `deposit_amount`, or a pricing rule system via additive migrations.

**V2 extension columns (not created in V1):**
- `availability_status` — enum: Available, Rented, In Maintenance
- `branch_id` — FK to future `branches` table
- `license_plate` — string
- `vin` — string

---

### 6.4 `car_features`

**Purpose:** Feature tags associated with a car, displayed as a list on the car detail page.

| Column | Data Type | Nullable | Default | Key | Description |
|--------|-----------|----------|---------|-----|-------------|
| `id` | `BIGINT UNSIGNED` | NO | auto | PK | Auto-increment primary key |
| `car_id` | `BIGINT UNSIGNED` | NO | — | FK, IDX | Foreign key to `cars.id` |
| `feature` | `VARCHAR(255)` | NO | — | — | Feature label (e.g., "Air Conditioning", "GPS") |
| `created_at` | `TIMESTAMP` | YES | NULL | — | Laravel standard timestamp |
| `updated_at` | `TIMESTAMP` | YES | NULL | — | Laravel standard timestamp |

**Constraints:**
- `FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE ON UPDATE CASCADE`
- `UNIQUE (car_id, feature)` — prevents duplicate feature labels on the same car. MySQL's utf8mb4 default collation makes this check case-insensitive, so "AC" and "ac" are treated as the same value. The application layer must also normalize input (trim whitespace, consistent capitalization) before inserting.

**Indexes:**
- Primary key on `id`
- Index on `car_id` (fetching all features for a car in a single query; also covered by the composite unique key)

**Deletion behavior:**
- Cascade delete when the parent `car` is deleted. No independent lifecycle.
- No soft delete.

**Notes:**
- A simple `ORDER BY id ASC` is sufficient for feature display order. No `sort_order` column is needed in V1.

---

### 6.5 `media`

**Purpose:** Polymorphic storage for car gallery images (multiple images per car, with display ordering and cover image selection).

> **Schema ownership:** The `media` table is created and owned by **Spatie Media Library's migration**. Do not define this table manually. The exact column set is determined by the package version installed. Run the Spatie migration during project setup.

**Key columns provided by Spatie (informational — do not redefine):**

| Column | Role |
|--------|------|
| `id` | Primary key |
| `model_type` | Polymorphic model class (e.g., `App\Models\Car`) |
| `model_id` | Polymorphic model ID |
| `uuid` | Unique media identifier |
| `collection_name` | Logical grouping (e.g., `car_images`) |
| `name` | Human-readable name |
| `file_name` | Stored filename |
| `mime_type` | Validated MIME type |
| `disk` | Storage disk (e.g., `public`) |
| `size` | File size in bytes |
| `order_column` | Display order within collection |
| `custom_properties` | JSON — used to store `is_cover` flag |
| `created_at`, `updated_at` | Standard timestamps |

**Indexes (created by Spatie's migration):**
- Composite index on `(model_type, model_id)` — fetch all media for a car
- Index on `collection_name`

**Cover image:** Stored in `custom_properties` as `{ "is_cover": true }`. Spatie's `getFirstMedia()` and custom collection configuration handle retrieval.

**Deletion behavior:**
- When a `Car` model is deleted, Spatie automatically deletes all associated `media` rows and their physical files via its registered model event observer.
- Deleting a single image from a car's gallery: handled by Spatie's `deleteMedia()` method — removes the row and the file.
- No soft delete.

**V1 collection used:**

| Collection | Owner | Description |
|---|---|---|
| `car_images` | `Car` model | Gallery images for a car (multiple, ordered, with cover) |

> `banners.image` and `categories.image` are plain VARCHAR columns — they do not use this table.

---

### 6.6 `faqs`

**Purpose:** Frequently asked questions displayed on the public FAQ page.

| Column | Data Type | Nullable | Default | Key | Description |
|--------|-----------|----------|---------|-----|-------------|
| `id` | `BIGINT UNSIGNED` | NO | auto | PK | Auto-increment primary key |
| `question` | `TEXT` | NO | — | — | The question text |
| `answer` | `LONGTEXT` | NO | — | — | The answer text (may contain rich formatting) |
| `category` | `VARCHAR(100)` | YES | NULL | IDX | Optional grouping label (e.g., "Pricing", "Rental Process") |
| `is_active` | `TINYINT(1)` | NO | `1` | IDX | `1` = visible on public site |
| `sort_order` | `INT` | NO | `0` | IDX | Controls display sequence |
| `created_at` | `TIMESTAMP` | YES | NULL | — | Laravel standard timestamp |
| `updated_at` | `TIMESTAMP` | YES | NULL | — | Laravel standard timestamp |

**Constraints:** None beyond the primary key.

**Indexes:**
- Primary key on `id`
- Index on `is_active` (public API filter)
- Index on `sort_order` (ordered display)
- Index on `category` (Filament filter; optional API grouping)

**Deletion behavior:**
- Hard delete. FAQs have no dependents.
- No soft delete.

**Notes:**
- `category` is a plain string column, not a foreign key. This avoids creating a `faq_categories` table for what is a simple grouping label. In V1, a handful of categories is manageable as a consistent set of strings enforced by the admin.
- The public API returns FAQs ordered by `sort_order ASC, id ASC`.

---

### 6.7 `banners`

**Purpose:** Hero slider content displayed on the public homepage.

| Column | Data Type | Nullable | Default | Key | Description |
|--------|-----------|----------|---------|-----|-------------|
| `id` | `BIGINT UNSIGNED` | NO | auto | PK | Auto-increment primary key |
| `title` | `VARCHAR(255)` | YES | NULL | — | Headline text on the banner |
| `subtitle` | `VARCHAR(500)` | YES | NULL | — | Supporting text below headline |
| `image` | `VARCHAR(500)` | YES | NULL | — | Path to banner image in storage |
| `cta_text` | `VARCHAR(100)` | YES | NULL | — | Call-to-action button label (e.g., "Browse Cars") |
| `cta_url` | `VARCHAR(500)` | YES | NULL | — | URL the CTA button links to |
| `is_active` | `TINYINT(1)` | NO | `1` | IDX | `1` = shown in homepage slider |
| `sort_order` | `INT` | NO | `0` | IDX | Controls slide order |
| `created_at` | `TIMESTAMP` | YES | NULL | — | Laravel standard timestamp |
| `updated_at` | `TIMESTAMP` | YES | NULL | — | Laravel standard timestamp |

**Constraints:** None beyond the primary key.

**Indexes:**
- Primary key on `id`
- Index on `is_active` (public API filter)
- Index on `sort_order` (ordered display)

**Deletion behavior:**
- Hard delete. When a banner is deleted, the corresponding image file must be removed from storage. Because banners do not use Spatie Media Library, this deletion must be handled explicitly in a `BannerObserver` or the `BannerService` using `Storage::delete($banner->image)`.
- No soft delete.

**Notes:**
- `image` stores a relative file path (e.g., `banners/uuid.webp`). The actual file is stored in `storage/app/public/banners/` via the Storage Facade.
- All text fields on the banner are nullable — a banner may be image-only with no text overlay.
- Single image per banner — no ordering, no cover concept. VARCHAR column is sufficient.

---

### 6.8 `settings`

**Purpose:** Key-value store for all site-wide configuration: company information, contact details, social media links, SEO metadata, and system settings.

| Column | Data Type | Nullable | Default | Key | Description |
|--------|-----------|----------|---------|-----|-------------|
| `id` | `BIGINT UNSIGNED` | NO | auto | PK | Auto-increment primary key |
| `key` | `VARCHAR(255)` | NO | — | UNIQUE | Dot-notation key (e.g., `contact.whatsapp_number`) |
| `value` | `LONGTEXT` | YES | NULL | — | The setting value; type-cast based on `type` column |
| `type` | `ENUM('string','integer','boolean','json','text')` | NO | `string` | — | Tells the application how to cast `value` |
| `settings_group` | `VARCHAR(100)` | NO | — | IDX | Logical grouping for Filament tabs |
| `description` | `VARCHAR(500)` | YES | NULL | — | Human-readable label for the Filament form field |
| `created_at` | `TIMESTAMP` | YES | NULL | — | Laravel standard timestamp |
| `updated_at` | `TIMESTAMP` | YES | NULL | — | Laravel standard timestamp |

**Constraints:**
- `UNIQUE` on `key`

**Indexes:**
- Primary key on `id`
- Unique index on `key` (lookup by key name)
- Index on `settings_group` (load all settings for a Filament tab)

**Deletion behavior:**
- Settings rows are managed only through the application seeder and Filament settings page. Individual setting rows are never deleted in V1 — only their `value` is updated.

**V1 Setting Keys (seeded at install):**

| Group | Key | Type | Description |
|-------|-----|------|-------------|
| `company` | `company.name` | string | Company display name |
| `company` | `company.tagline` | string | Short marketing tagline |
| `company` | `company.description` | text | About us text |
| `company` | `company.logo` | string | File path to company logo |
| `company` | `company.about_text` | text | Longer about us content |
| `contact` | `contact.phone_primary` | string | Primary phone number |
| `contact` | `contact.phone_secondary` | string | Secondary phone number |
| `contact` | `contact.email` | string | Contact email address |
| `contact` | `contact.address` | text | Physical address |
| `contact` | `contact.whatsapp_number` | string | WhatsApp number (used by booking form) |
| `social` | `social.facebook_url` | string | Facebook page URL |
| `social` | `social.instagram_url` | string | Instagram profile URL |
| `social` | `social.twitter_url` | string | Twitter/X profile URL |
| `social` | `social.youtube_url` | string | YouTube channel URL |
| `social` | `social.tiktok_url` | string | TikTok profile URL |
| `social` | `social.linkedin_url` | string | LinkedIn page URL |
| `seo` | `seo.site_title` | string | Default browser tab title |
| `seo` | `seo.meta_description` | string | Default meta description |
| `seo` | `seo.meta_keywords` | string | Default meta keywords |
| `seo` | `seo.google_analytics_id` | string | GA tracking ID (optional) |
| `appearance` | `appearance.favicon` | string | File path to favicon |
| `appearance` | `appearance.primary_color` | string | Brand hex color |
| `appearance` | `appearance.secondary_color` | string | Secondary hex color |
| `system` | `system.maintenance_mode` | boolean | `1` = site shows maintenance page |
| `system` | `system.app_locale` | string | Default language (e.g., `ar`, `en`) |

**Notes:**
- The `contact.whatsapp_number` setting is the only value critical to the client-side booking form. If this key is null or empty, the WhatsApp redirect on `booking.html` will not function.

---

## 7. Relationships

```
users           (no relationships to other V1 domain tables)

categories 1──────────────────────────── ∞ cars
                                           |
                                  1────── ∞ car_features
                                           |
                               (Spatie) ───── media
                                           (collection = 'car_images')

banners         (standalone — image stored as VARCHAR path column)
categories      (standalone — image stored as VARCHAR path column)
faqs            (standalone — no relationships)
settings        (standalone — no relationships)
```

### Relationship Detail

| Relationship | Type | FK Column | Behavior |
|---|---|---|---|
| `Category` → `Car` | One-to-Many | `cars.category_id` | RESTRICT on delete (must move cars first) |
| `Car` → `CarFeature` | One-to-Many | `car_features.car_id` | CASCADE on delete |
| `Car` → `Media` | One-to-Many (Spatie polymorphic) | `media.model_type + model_id` | Spatie auto-deletes on model delete |

---

## 8. Indexing Strategy

### Public Website Query Indexes

These indexes support the high-frequency read queries executed by the public REST API:

| Table | Index Columns | Query Pattern |
|-------|---------------|---------------|
| `cars` | `(is_published)` | `WHERE is_published = 1` — all public car queries |
| `cars` | `(is_published, is_featured)` | `WHERE is_published = 1 AND is_featured = 1` — homepage featured cars |
| `cars` | `(category_id, is_published)` | `WHERE category_id = ? AND is_published = 1` — category filter |
| `cars` | `(slug)` UNIQUE | `WHERE slug = ?` — car detail page lookup |
| `cars` | `(sort_order)` | `ORDER BY sort_order ASC` — listing order |
| `cars` | `(name)` | `WHERE name LIKE ?` — keyword search (LIKE; see §14) |
| `categories` | `(is_active)` | `WHERE is_active = 1` — category filter bar |
| `categories` | `(slug)` UNIQUE | `WHERE slug = ?` — API filter by category slug |
| `faqs` | `(is_active, sort_order)` | `WHERE is_active = 1 ORDER BY sort_order` |
| `banners` | `(is_active, sort_order)` | `WHERE is_active = 1 ORDER BY sort_order` |
| `media` | `(model_type, model_id)` | Fetch all images for a car (Spatie index) |
| `settings` | `(key)` UNIQUE | `WHERE key = ?` — individual setting lookup |
| `settings` | `(settings_group)` | `WHERE settings_group = ?` — bulk load by group |

### Filament Admin Query Indexes

These indexes support Filament's table filtering, sorting, and searching:

| Table | Index Columns | Admin Use |
|-------|---------------|-----------|
| `cars` | `(brand)` | Filament filter by brand |
| `cars` | `(is_published)` | Filament status badge + filter |
| `cars` | `(is_featured)` | Filament featured filter |
| `categories` | `(is_active)` | Filament active filter |
| `faqs` | `(category)` | Filament FAQ category filter |
| `car_features` | `(car_id)` | Eager load features on car edit form |

### Composite Index Note

The single-column indexes on `cars` (`is_published`, `is_featured`, `category_id`) are sufficient for V1 query volumes. MySQL's index merge optimizer will combine these for compound conditions. If query analysis reveals performance issues at higher car counts, a composite index on `(is_published, category_id, sort_order)` should be added at that time.

---

## 9. Data Integrity Rules

### Foreign Key Behavior Summary

| FK Relationship | ON DELETE | ON UPDATE |
|---|---|---|
| `cars.category_id → categories.id` | RESTRICT | CASCADE |
| `car_features.car_id → cars.id` | CASCADE | CASCADE |
| `media` → (polymorphic, no DB FK) | App-managed | App-managed |

### Required Fields

| Table | Required (NOT NULL) Columns |
|---|---|
| `users` | `name`, `email`, `password` |
| `categories` | `name`, `slug` |
| `cars` | `category_id`, `name`, `slug`, `brand`, `model`, `year` |
| `car_features` | `car_id`, `feature` |
| `media` | `model_type`, `model_id`, `collection`, `file_name`, `file_path`, `mime_type`, `size` |
| `faqs` | `question`, `answer` |
| `settings` | `key`, `type`, `settings_group` |

### Uniqueness Constraints

| Table | Unique Column(s) | Constraint Purpose |
|---|---|---|
| `users` | `email` | No duplicate authentication accounts |
| `categories` | `slug` | Unique URL path |
| `cars` | `slug` | Unique URL path |
| `car_features` | `(car_id, feature)` | No duplicate feature labels per car |
| `settings` | `key` | No duplicate setting keys |

### Slug Generation Rules

- Slugs for `categories` and `cars` are generated from the `name` column with Laravel's native `Str::slug()` helper during the model `creating` event.
- Duplicate slugs are suffixed with a counter: `bmw-5-series`, `bmw-5-series-2`, etc.
- Slugs must not be changed after a car is published — changing the slug breaks existing bookmarked or shared URLs.

### Publication/Visibility Rules

- A `car` with `is_published = 0` is never returned by the public API.
- A `category` with `is_active = 0` is never returned by the public API, and its cars do not appear in the category filter list.
- A `faq` with `is_active = 0` is excluded from the public FAQ page.
- A `banner` with `is_active = 0` is excluded from the homepage slider.

### Cover Image Rule

- Only one `media` row per `(model_type, model_id, collection)` group may have `is_cover = 1`.
- When the admin sets a new cover image, the application sets all other rows in that group to `is_cover = 0` before setting the new one to `1`.
- Enforced at the application layer in `MediaService`.

### WhatsApp Number Requirement

- `contact.whatsapp_number` in the `settings` table must be populated before the pre-booking inquiry form on the public website can function.
- The `SettingService` returns a warning if this key is null.

---

## 10. Deletion Strategy

| Entity | Strategy | Behavior |
|--------|----------|----------|
| `users` | Manual only | The V1 Filament operator is not deleted through the application UI. |
| `categories` | Hard delete | RESTRICTED if cars exist. Admin must reassign or delete cars first. |
| `cars` | Hard delete | Cascade-deletes `car_features`. Application layer deletes associated `media` records and files before database delete. |
| `car_features` | Cascade delete | Auto-deleted when parent `car` is deleted. Can also be individually deleted by Filament's Repeater component. |
| `media` | Hard delete | Application layer deletes the physical file from storage, then deletes the `media` row. |
| `faqs` | Hard delete | No dependents. Immediate permanent deletion. |
| `banners` | Hard delete | Application layer deletes the associated image file, then deletes the `banners` row. |
| `settings` | No deletion | Setting rows persist for the life of the application. Only their `value` is updated. |

**Rationale for no soft deletes:**

As defined in `System_Architecture_Plan.md` §4.2:

> "Hard Deletes: Records are permanently removed on admin delete. If a car is accidentally deleted, it can be re-added manually by the admin."

Soft deletes introduce a `deleted_at` column and change every query to require `WHERE deleted_at IS NULL`. For a V1 marketing website with a small number of cars managed by one admin, this complexity is not justified. Recovery from accidental deletion is handled by re-entering the data.

---

## 11. Laravel Migration Plan

The existing Laravel `users` migration and published Spatie `media` migration are retained. The six missing domain migrations must be created in dependency order:

| # | Migration Name | Creates Table | Depends On |
|---|----------------|---------------|------------|
| Existing | `create_users_table` | `users` | (none) |
| 1 | `create_categories_table` | `categories` | (none) |
| 2 | `create_cars_table` | `cars` | `categories` |
| 3 | `create_car_features_table` | `car_features` | `cars` |
| Existing | `create_media_table` | `media` | (none — polymorphic) |
| 4 | `create_faqs_table` | `faqs` | (none) |
| 5 | `create_banners_table` | `banners` | (none) |
| 6 | `create_settings_table` | `settings` | (none) |

**Migration notes:**

- The `cars` migration must declare `RESTRICT` on `category_id` explicitly.
- The `car_features` migration uses `->onDelete('cascade')` and a composite unique constraint on `car_id` and `feature`.
- The `media` migration remains the package-published Spatie migration and must not be rewritten.
- The `settings` migration is followed by `SettingSeeder`, which inserts all 25 default keys.

**Seeder requirements:**

| Seeder | Purpose |
|--------|---------|
| `UserSeeder` | Creates the authorized Filament operator in the existing `users` table |
| `SettingSeeder` | Inserts all 25 V1 setting keys with default values |
| `CategorySeeder` | Inserts 5–7 starter categories (SUV, Sedan, Luxury, Economy, Sports) |
| `CarSeeder` | Inserts 10 demo cars with features and placeholder images |
| `FaqSeeder` | Inserts 10–15 demo FAQs |
| `BannerSeeder` | Inserts 3 demo hero banners |

---

## 12. Filament Considerations

### `CarResource`

| Filament Concern | Database Requirement |
|---|---|
| Table column: Car Name | `cars.name` VARCHAR — sortable, searchable |
| Table column: Category badge | `cars.category_id` FK — eager-loaded via `with('category')` |
| Table filter: Category | `cars.category_id` with index |
| Table filter: Published status | `cars.is_published` with index |
| Table filter: Featured | `cars.is_featured` with index |
| Form field: Category select | `cars.category_id` FK; populated by `Category::all()` |
| Form field: Specifications | `cars.specifications` JSON — displayed via Filament `KeyValue` component |
| Form field: Features | `car_features` table — displayed via Filament `Repeater` or `TagsInput` |
| Form field: Images | `media` table — via `SpatieMediaLibraryFileUpload` |
| Inline toggle: Published | `cars.is_published` TINYINT — toggled via `ToggleColumn` |
| Inline toggle: Featured | `cars.is_featured` TINYINT — toggled via `ToggleColumn` |
| Reorder: Sort order | `cars.sort_order` INT — via Filament `ReorderAction` |
| Slug auto-generation | `cars.slug` via Laravel's `Str::slug()` helper in the model `creating` event |

### `CategoryResource`

| Filament Concern | Database Requirement |
|---|---|
| Form field: Slug | `categories.slug` — auto-generated; displayed as editable field |
| Form field: Icon | `categories.icon` VARCHAR — text input (CSS class or icon name) |
| Form field: Image | `categories.image` VARCHAR — simple file upload storing path string |
| Toggle: Active | `categories.is_active` TINYINT — toggled via `ToggleColumn` |
| Reorder: Sort order | `categories.sort_order` INT |
| Delete restriction | Application must check for associated cars before allowing delete |

### `FaqResource`

| Filament Concern | Database Requirement |
|---|---|
| Grouping filter | `faqs.category` VARCHAR — Filament `SelectFilter` with predefined values |
| Reorder | `faqs.sort_order` INT |
| Toggle: Active | `faqs.is_active` TINYINT |
| Long text fields | `faqs.answer` LONGTEXT — displayed via `RichEditor` or `Textarea` |

### `BannerResource`

| Filament Concern | Database Requirement |
|---|---|
| Image upload | `banners.image` VARCHAR — stores path; file managed by `MediaService` |
| Reorder | `banners.sort_order` INT |
| Toggle: Active | `banners.is_active` TINYINT |

### `ManageSettings` (Custom Page)

| Filament Concern | Database Requirement |
|---|---|
| Tab loading | `settings_group` index — load all keys for one tab in a single query |
| Key-value saving | `settings.key` UNIQUE — bulk upsert on save |
| Logo/favicon upload | File path stored in `settings.value` for `company.logo` and `appearance.favicon` |
| WhatsApp number field | `settings.key = 'contact.whatsapp_number'` — critical for booking form |

---

## 13. API Considerations

> Full API endpoint contracts and response shapes are defined in `API_Contract.md`. This section documents only the database-level constraints that the API layer must respect.

### Pricing Exclusion

The `price_daily`, `price_weekly`, and `price_monthly` columns on `cars` are **internal admin reference fields**. They must be excluded from all public API responses. The public `CarResource` (Laravel API Resource) must not serialize these columns.

### Slug as Public Identifier

`cars.slug` is the public URL and API identifier for car detail pages. The public API accepts `GET /api/cars/{slug}` — not `{id}`. The `slug` unique index on `cars` makes this lookup efficient. Car IDs must never be exposed in public URLs or API responses.

---

## 14. Performance Considerations

### Expected Data Volume in V1

| Table | Expected Row Count |
|---|---|
| `users` | 1 |
| `categories` | 5–15 |
| `cars` | 20–200 |
| `car_features` | 100–1,000 (5–10 per car) |
| `media` | 100–1,000 (3–10 images per car) |
| `faqs` | 10–50 |
| `banners` | 3–10 |
| `settings` | 25 (fixed) |

At this scale, MySQL's query optimizer handles all queries efficiently without special tuning. The primary performance strategy is Redis caching at the API layer, not complex database optimization. Cache strategy and TTLs are documented in `System_Architecture_Plan.md` §19.2.

### Keyword Search — LIKE (not FULLTEXT)

The public car listing supports a `?search=` query parameter. In V1, search is implemented as:

```sql
WHERE is_published = 1
  AND (name LIKE '%keyword%' OR brand LIKE '%keyword%')
```

At 20–200 cars, the entire `cars` table fits in MySQL's buffer pool. A LIKE scan on this dataset completes in microseconds — no FULLTEXT index is needed or justified. A regular index on `cars.name` marginally helps prefix matching (`name LIKE 'keyword%'`) but not mid-string matching; it is included for Filament search anyway.

**V2 upgrade path:** If the car count grows to thousands, FULLTEXT can be added via `ALTER TABLE cars ADD FULLTEXT INDEX ...` with no application code changes other than switching from LIKE to `MATCH() AGAINST()`.

---

## 15. Future Extension Considerations

The V1 schema is deliberately minimal. The following V2 modules can be added without modifying any V1 table structure:

| V2 Module | Database Extension Required | V1 Impact |
|---|---|---|
| **Customers** | New `customers` table + new Sanctum guard | No changes to any V1 table |
| **Bookings** | New `bookings` table (FK to `cars.id` and `customers.id`) | `cars` table untouched |
| **Payments** | New `payments` table (FK to `bookings.id`) | No V1 changes |
| **Branches** | New `branches` table + additive nullable `branch_id` column on `cars` | `cars` gains one nullable FK column |
| **Fleet Management** | Additive nullable columns on `cars`: `availability_status`, `license_plate`, `vin` | `cars` gains up to 3 columns |
| **Drivers** | New `drivers` table | No V1 changes |
| **FAQ Categories** | New `faq_categories` table + replace `faqs.category` VARCHAR with FK | Minor schema change on `faqs` |
| **Notifications** | New Laravel Notification classes + queue channels | No new tables needed in most cases |

**V1 columns available for V2 use without schema change:**
- `cars.price_daily/weekly/monthly` — internal reference now; may be exposed publicly in V2

**Additive migration rules for V2 (as defined in `System_Architecture_Plan.md` §24.3):**
- New V2 columns use `nullable()` with no default — existing V1 rows are unaffected
- No V1 columns are renamed or removed during active migration
- Full database backup taken before any V2 migration

---

## 16. Final Database Summary

### Tables Implemented

| Table | Rows in Production | Primary Role |
|---|---|---|
| `users` | 1 | Filament authentication through Laravel's default guard |
| `categories` | 5–15 | Car classification and filtering |
| `cars` | 20–200 | Core content entity |
| `car_features` | 100–1,000 | Car feature tags (per-car) |
| `media` | 100–1,000 | Polymorphic file/image store |
| `faqs` | 10–50 | FAQ page content |
| `banners` | 3–10 | Homepage hero slider |
| `settings` | 25 (fixed) | All site configuration and contact info |

### Key Relationships

- `categories` → `cars` (one-to-many, RESTRICT on delete)
- `cars` → `car_features` (one-to-many, CASCADE on delete)
- `cars` → `media` (Spatie polymorphic, auto-deleted by Spatie on car delete)

### Important Constraints

- `cars.slug` UNIQUE — required for public URL routing
- `categories.slug` UNIQUE — required for category filtering API
- `car_features` UNIQUE `(car_id, feature)` — prevents duplicate feature labels
- `settings.key` UNIQUE — required for key-value integrity
- `users.email` UNIQUE — required for authentication
- `cars.category_id` FK RESTRICT — prevents orphaned cars on category delete

### Key Architectural Decisions

| Decision | Rationale |
|---|---|
| 8 core application tables | Sufficient for all V1 functional requirements; Laravel infrastructure tables are counted separately |
| Spatie Media Library for car images | Multi-image + ordering + cover selection; Filament integration is native |
| VARCHAR image columns for banners/categories | Single image per entity; polymorphic table adds complexity with no benefit |
| JSON column for car specs | Flexible schema for heterogeneous vehicle specifications |
| Separate `car_features` table with UNIQUE constraint | Enables future filtering; prevents duplicate data |
| Single `settings` table (key-value) | Handles all company, contact, and SEO settings without individual tables |
| Hard delete throughout | Matches approved policy in `System_Architecture_Plan.md` §4.2 |
| Existing `users` table for Filament | Avoids an unnecessary V1 auth model and guard; panel access remains explicit in `canAccessPanel()` |
| No role column in V1 | Staff RBAC and customer identity design are deferred until their requirements exist |
| LIKE search instead of FULLTEXT | Sufficient for 20–200 cars; FULLTEXT is a V2 upgrade if needed |
| Prices nullable, not public | Pricing by WhatsApp; prices stored internally only |
| No booking storage | Pre-booking form is client-side only; no inquiry data hits the database |

---

**Document Metadata:**

| Field | Value |
|-------|-------|
| Document Name | Database_Design.md |
| Version | 1.2.0 |
| Created | August 2026 |
| Last Updated | August 2026 |
| Source | System_Architecture_Plan.md v1.6.0 |
| Status | Ready for Implementation |
| Changes from v1.0.0 | Spatie Media Library adopted; existing users table retained for Filament; banner/category images use VARCHAR; UNIQUE(car_id,feature) added; FULLTEXT replaced with LIKE; Section 13 stripped to pricing and slug notes only |
| Tables Defined | 8 (V1 only) |
| Next Document | API_Contract.md |
