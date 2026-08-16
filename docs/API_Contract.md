# API Contract — Car Rental Website (Version 1)

---

> **Authoritative Sources:** This document is derived from and must remain consistent with
> `System_Architecture_Plan.md` (v1.4.0) and `Database_Design.md` (v1.1.0).
> This document defines only the REST API contract.
> It does not repeat architecture, database design, or implementation details from those documents.

---

## 1. Purpose and Scope

This document defines the complete Version 1 REST API contract for the Car Rental Website.

**What this document covers:**
- All V1 public API endpoints
- Request parameters and validation rules
- JSON response structures for every endpoint
- Public resource field definitions
- HTTP status codes and error format
- Pagination, filtering, and sorting behavior
- Security requirements specific to the API
- Future extension strategy

**What this document does not cover:**
- Laravel implementation details (controllers, services, resources)
- Database schema (defined in `Database_Design.md`)
- System architecture (defined in `System_Architecture_Plan.md`)
- Admin dashboard behavior (Filament, defined in `System_Architecture_Plan.md`)
- Redis/caching internals
- Migration or deployment details

---

## 2. API Context

The REST API is one component of the Laravel Modular Monolith. Understanding its position in the system is essential:

| Consumer | How it accesses data | Uses REST API? |
|---|---|---|
| Public website (Blade MPA) | Server-side via Service layer → Eloquent | **No** |
| Admin dashboard (Filament) | Server-side directly via Eloquent | **No** |
| Future Flutter/mobile app | HTTP requests to `/api/*` | **Yes** |
| Future external integrations | HTTP requests to `/api/*` | **Yes** |

The V1 API is intentionally small. It exists to be ready for future consumers. **No V1 in-application code consumes the REST API.** Both the Blade MPA and Filament access the Service layer directly within the same Laravel process.

---

## 3. Base URL and Routing

### 3.1 Base Path

```
/api
```

All V1 endpoints are rooted at `/api`. There is no version prefix in V1.

**Examples:**
```
GET /api/cars
GET /api/cars/{slug}
GET /api/categories
GET /api/faqs
GET /api/banners
GET /api/settings
GET /api/settings/contact
```

### 3.2 Full URL Construction

```
{scheme}://{host}/api/{resource}
```

Example (local development):
```
http://localhost:8000/api/cars
```

Production hostnames are not hardcoded in this contract. The base host is environment-specific.

### 3.3 Routing File

All public API routes are registered in `routes/api.php` without the `auth:sanctum` middleware. Laravel's default `/api` prefix applies.

---

## 4. API Conventions

### 4.1 Format

- All requests and responses use **JSON** (`application/json`)
- Encoding is **UTF-8**
- All responses include `Content-Type: application/json` header

### 4.2 Required Request Header

```
Accept: application/json
```

Including this header ensures Laravel returns JSON error responses (e.g., 404, 422) rather than HTML pages.

### 4.3 HTTP Methods

| Method | Usage |
|---|---|
| `GET` | Retrieve a resource or collection |

The V1 public API is **read-only**. No `POST`, `PUT`, `PATCH`, or `DELETE` endpoints exist in V1.

### 4.4 Naming Conventions

| Element | Convention | Example |
|---|---|---|
| Endpoint paths | Lowercase, hyphen-separated | `/api/cars`, `/api/car-features` |
| JSON keys | `snake_case` | `is_featured`, `cta_url` |
| Query parameters | `snake_case` | `?per_page=24&category=suv` |
| Boolean values | `true` / `false` (JSON boolean) | `"is_featured": true` |
| Dates | ISO 8601 UTC | `"2026-08-01T00:00:00.000000Z"` |
| Null fields | `null` (JSON null, not omitted) | `"color": null` |

### 4.5 Resource Identifiers

- Cars and categories are identified publicly by **slug** (never by numeric ID)
- FAQs and banners are identified by **numeric ID** (not exposed in public URLs; returned in response for client reference)
- Numeric IDs are never used in public URL paths for cars or categories

### 4.6 Slug Format

- Lowercase, hyphen-separated
- URL-safe characters only (no spaces, special characters, or encoding needed for typical slugs)
- Unique across the resource type
- Example: `bmw-5-series`, `luxury-suv`

---

## 5. Authentication and Authorization

### 5.1 V1 Public Endpoints

**All V1 public API endpoints require no authentication.**

There are no Bearer tokens, API keys, JWT, OAuth, or Sanctum tokens on any V1 public endpoint.

**Reason:** V1 endpoints are read-only. All data returned is intended for public consumption. Requiring authentication for read-only public data adds friction for future mobile consumers with no security benefit.

### 5.2 Admin Endpoints

There are **no admin REST API endpoints in V1.**

All admin operations are handled by Laravel Filament directly, without REST API calls. See `System_Architecture_Plan.md` §12 for the Filament authentication architecture.

### 5.3 V2 Authentication Scope

Customer authentication (registration, login, tokens) is a V2 feature. No customer authentication infrastructure exists in V1. See §13 for future extension notes.

---

## 6. Response Format

Every API response follows a consistent JSON envelope.

### 6.1 Single Resource (Success)

```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "BMW 5 Series",
    "slug": "bmw-5-series"
  }
}
```

### 6.2 Collection (Success)

```json
{
  "success": true,
  "data": [
    { "id": 1, "name": "BMW 5 Series", "slug": "bmw-5-series" },
    { "id": 2, "name": "Toyota Camry",  "slug": "toyota-camry"  }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 12,
    "total": 48,
    "last_page": 4,
    "from": 1,
    "to": 12
  },
  "links": {
    "first": "/api/cars?page=1",
    "last":  "/api/cars?page=4",
    "prev":  null,
    "next":  "/api/cars?page=2"
  }
}
```

### 6.3 Empty Collection

```json
{
  "success": true,
  "data": [],
  "meta": {
    "current_page": 1,
    "per_page": 12,
    "total": 0,
    "last_page": 1,
    "from": null,
    "to": null
  },
  "links": {
    "first": "/api/cars?page=1",
    "last":  "/api/cars?page=1",
    "prev":  null,
    "next":  null
  }
}
```

### 6.4 Error Response

```json
{
  "success": false,
  "error": {
    "code": "NOT_FOUND",
    "message": "The requested car was not found."
  }
}
```

### 6.5 Validation Error Response

```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The given data was invalid.",
    "details": {
      "per_page": ["The per page may not be greater than 100."],
      "category": ["The selected category is invalid."]
    }
  }
}
```

### 6.6 Rules

- `success` is always a boolean — never omitted
- `data` is always present on success — never omitted
- `meta` and `links` are present on collection responses only
- `error` is present on error responses only
- Stack traces, exception class names, and internal file paths are **never** included in production responses
- All `null`-able fields are returned as `null`, not omitted

---

## 7. HTTP Status Codes

| Code | Name | When Used |
|---|---|---|
| `200 OK` | Success | Successful GET request |
| `404 Not Found` | Not Found | Slug/ID does not exist, or resource is not published/active |
| `422 Unprocessable Entity` | Validation Error | Invalid query parameter values |
| `429 Too Many Requests` | Rate Limited | Client exceeded the rate limit |
| `500 Internal Server Error` | Server Error | Unexpected application error |

**Notes:**
- A car that exists but is unpublished (`is_published = 0`) returns `404`, not `403`. The existence of unpublished content is not disclosed.
- An active category with zero published cars returns `200` with an empty `data` array on its cars listing — not `404`.

---

## 8. Public Endpoints

---

### 8.1 List Cars

**`GET /api/cars`**

Returns a paginated list of published cars.

#### Authentication
None.

#### Query Parameters

| Parameter | Type | Required | Default | Constraints | Description |
|---|---|---|---|---|---|
| `page` | integer | No | `1` | `>= 1` | Page number |
| `per_page` | integer | No | `12` | `1–100` | Items per page |
| `category` | string | No | — | Valid category slug | Filter by category slug |
| `search` | string | No | — | Max 100 chars | Keyword search on car name and brand |
| `featured` | boolean | No | — | `1` or `true` | Return only featured cars |

**Parameter Details:**

`category` — Must be the slug of an active category (e.g., `?category=luxury`). If the slug does not match an active category, a `422` is returned.

`search` — Performs a `LIKE` search against `cars.name` and `cars.brand`. Minimum 1 character. Searches are case-insensitive. Leading wildcards are used (`%keyword%`) — sufficient for V1 data volumes.

`featured` — Accepts `1` or `true`. Returns only cars where `is_featured = 1`. Can be combined with `category` and `search`.

`per_page` — Values above `100` return a `422` error.

#### Success Response — `200 OK`

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "BMW 5 Series",
      "slug": "bmw-5-series",
      "brand": "BMW",
      "model": "5 Series",
      "year": 2024,
      "color": "Pearl White",
      "is_featured": true,
      "category": {
        "id": 2,
        "name": "Luxury",
        "slug": "luxury"
      },
      "cover_image": {
        "url": "https://example.com/storage/cars/a1b2c3d4.webp",
        "order": 1
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 12,
    "total": 36,
    "last_page": 3,
    "from": 1,
    "to": 12
  },
  "links": {
    "first": "/api/cars?page=1",
    "last":  "/api/cars?page=3",
    "prev":  null,
    "next":  "/api/cars?page=2"
  }
}
```

**Note:** The listing response returns only the **cover image** per car (not the full gallery). This avoids loading large image arrays for every car in the list. The full gallery is returned by `GET /api/cars/{slug}`.

#### Error Responses

| Status | Code | Condition |
|---|---|---|
| `422` | `VALIDATION_ERROR` | Invalid query parameter values |
| `429` | `RATE_LIMIT_EXCEEDED` | Too many requests |

#### Business Rules

- Only cars with `is_published = 1` are returned
- Results are ordered by `cars.sort_order ASC`, then `cars.id ASC`
- Cars belonging to inactive categories (`is_active = 0`) are excluded when filtering by category
- Filtering by an inactive category slug returns a `422` error (invalid category)
- Price fields (`price_daily`, `price_weekly`, `price_monthly`) are never returned
- `description` and `specifications` are not returned in the list response — only in the detail response

---

### 8.2 Get Car Details

**`GET /api/cars/{slug}`**

Returns the full details of a single published car.

#### Authentication
None.

#### Path Parameters

| Parameter | Type | Required | Description |
|---|---|---|---|
| `slug` | string | Yes | The URL-safe car identifier (e.g., `bmw-5-series`) |

**Slug rules:**
- Lowercase, hyphen-separated
- Must exactly match `cars.slug` in the database
- Case-sensitive at the URL level (all slugs are stored lowercase)
- No URL encoding needed for typical slugs

#### Success Response — `200 OK`

```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "BMW 5 Series",
    "slug": "bmw-5-series",
    "brand": "BMW",
    "model": "5 Series",
    "year": 2024,
    "color": "Pearl White",
    "description": "The BMW 5 Series is the benchmark for the business sedan segment...",
    "specifications": {
      "engine": "2.0L TwinPower Turbo",
      "transmission": "8-Speed Automatic",
      "fuel_type": "Petrol",
      "seats": 5,
      "doors": 4,
      "drive_type": "RWD",
      "horsepower": "248 hp",
      "0_to_100_kph": "6.1s"
    },
    "is_featured": true,
    "meta_title": "Rent BMW 5 Series — Premium Sedan",
    "meta_description": "Rent the BMW 5 Series. Luxury, performance, and comfort for your journey.",
    "category": {
      "id": 2,
      "name": "Luxury",
      "slug": "luxury"
    },
    "features": [
      "Air Conditioning",
      "GPS Navigation",
      "Bluetooth",
      "Sunroof",
      "Leather Seats"
    ],
    "images": [
      {
        "url": "https://example.com/storage/cars/a1b2c3d4.webp",
        "is_cover": true,
        "order": 1
      },
      {
        "url": "https://example.com/storage/cars/e5f6g7h8.webp",
        "is_cover": false,
        "order": 2
      },
      {
        "url": "https://example.com/storage/cars/i9j0k1l2.webp",
        "is_cover": false,
        "order": 3
      }
    ],
    "created_at": "2026-08-01T00:00:00.000000Z"
  }
}
```

#### Error Responses

| Status | Code | Condition |
|---|---|---|
| `404` | `NOT_FOUND` | Slug does not exist, or the car is unpublished |
| `429` | `RATE_LIMIT_EXCEEDED` | Too many requests |

#### Business Rules

- A car with `is_published = 0` returns `404` — its existence is not disclosed
- `specifications` is a JSON object; its keys are flexible and vary by car type — consumers should handle missing keys gracefully
- `features` is an array of plain strings, ordered by database insertion order (`id ASC`)
- `images` is an array ordered by `order_column ASC`; the cover image is always first (it has `is_cover: true`)
- If a car has no images, `images` is an empty array `[]`
- Price fields are never returned
- `meta_title` and `meta_description` may be `null` — the client should fall back to the car name and description

---

### 8.3 List Categories

**`GET /api/categories`**

Returns all active categories, ordered by display sequence.

#### Authentication
None.

#### Query Parameters
None.

#### Success Response — `200 OK`

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "SUV",
      "slug": "suv",
      "description": "Spacious and powerful sport utility vehicles",
      "icon": "fa-truck-suv",
      "image_url": "https://example.com/storage/categories/suv-cover.webp"
    },
    {
      "id": 2,
      "name": "Luxury",
      "slug": "luxury",
      "description": "Premium luxury sedans and coupes",
      "icon": "fa-star",
      "image_url": null
    }
  ]
}
```

#### Error Responses

| Status | Code | Condition |
|---|---|---|
| `429` | `RATE_LIMIT_EXCEEDED` | Too many requests |

#### Business Rules

- Only categories with `is_active = 1` are returned
- Results are ordered by `categories.sort_order ASC`, then `categories.id ASC`
- This endpoint is not paginated — the number of categories is small and bounded
- `description`, `icon`, and `image_url` may be `null` — the category name is always present
- `image_url` is a fully resolved public URL, not a raw storage path

---

### 8.4 List FAQs

**`GET /api/faqs`**

Returns all active FAQs, ordered by display sequence.

#### Authentication
None.

#### Query Parameters
None.

#### Success Response — `200 OK`

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "category": "Rental Process",
      "question": "What documents are required to rent a car?",
      "answer": "You will need a valid driver's license, passport or national ID, and a credit card in your name."
    },
    {
      "id": 2,
      "category": "Rental Process",
      "question": "What is the minimum rental period?",
      "answer": "Our minimum rental period is one day."
    },
    {
      "id": 3,
      "category": null,
      "question": "Do you offer delivery?",
      "answer": "Yes, we offer vehicle delivery within the city. Contact us via WhatsApp for details."
    }
  ]
}
```

#### Error Responses

| Status | Code | Condition |
|---|---|---|
| `429` | `RATE_LIMIT_EXCEEDED` | Too many requests |

#### Business Rules

- Only FAQs with `is_active = 1` are returned
- Results are ordered by `faqs.sort_order ASC`, then `faqs.id ASC`
- This endpoint is not paginated — FAQ counts are small and bounded
- `category` is a plain string label (e.g., "Rental Process"), not a foreign key; it may be `null`
- The client may use `category` to group FAQs visually (e.g., accordion sections by category)
- Admin metadata (`is_active`, `sort_order`) is not returned

---

### 8.5 List Banners

**`GET /api/banners`**

Returns all active hero banners for the homepage slider, ordered by display sequence.

#### Authentication
None.

#### Query Parameters
None.

#### Success Response — `200 OK`

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Drive in Style",
      "subtitle": "Premium car rentals for every occasion",
      "image_url": "https://example.com/storage/banners/hero-1.webp",
      "cta": {
        "text": "Browse Cars",
        "url": "/cars"
      }
    },
    {
      "id": 2,
      "title": null,
      "subtitle": null,
      "image_url": "https://example.com/storage/banners/hero-2.webp",
      "cta": null
    }
  ]
}
```

#### Error Responses

| Status | Code | Condition |
|---|---|---|
| `429` | `RATE_LIMIT_EXCEEDED` | Too many requests |

#### Business Rules

- Only banners with `is_active = 1` are returned
- Results are ordered by `banners.sort_order ASC`, then `banners.id ASC`
- This endpoint is not paginated — banner counts are small and bounded
- `title`, `subtitle`, and `cta` may all be `null` — a banner may be image-only
- `cta` is `null` when both `cta_text` and `cta_url` are `null`; otherwise it is an object with `text` and `url`
- `image_url` is a fully resolved public URL; it may be `null` if no image has been uploaded yet

---

### 8.6 Get All Public Settings

**`GET /api/settings`**

Returns all public-facing site settings, grouped by category.

#### Authentication
None.

#### Query Parameters
None.

#### Success Response — `200 OK`

```json
{
  "success": true,
  "data": {
    "company": {
      "name": "Elite Car Rentals",
      "tagline": "Drive in Luxury",
      "description": "We offer premium car rentals across the region.",
      "logo_url": "https://example.com/storage/logo.webp",
      "about_text": "Founded in 2020, Elite Car Rentals..."
    },
    "contact": {
      "phone_primary": "+971501234567",
      "phone_secondary": "+971509876543",
      "email": "info@elitecarrentals.com",
      "address": "Dubai Marina, Dubai, UAE",
      "whatsapp_number": "971501234567"
    },
    "social": {
      "facebook_url": "https://facebook.com/elitecarrentals",
      "instagram_url": "https://instagram.com/elitecarrentals",
      "twitter_url": null,
      "youtube_url": null,
      "tiktok_url": "https://tiktok.com/@elitecarrentals",
      "linkedin_url": null
    },
    "seo": {
      "site_title": "Elite Car Rentals — Premium Cars in Dubai",
      "meta_description": "Rent luxury and economy cars in Dubai with Elite Car Rentals.",
      "meta_keywords": "car rental dubai, luxury car rental, rent a car"
    },
    "appearance": {
      "favicon_url": "https://example.com/storage/favicon.ico",
      "primary_color": "#1A2B4C",
      "secondary_color": "#C9A84C"
    }
  }
}
```

**Excluded setting groups:**
- `system` (`maintenance_mode`, `app_locale`) — internal operational settings, never returned
- `seo.google_analytics_id` — excluded from public response (internal tracking configuration)

#### Error Responses

| Status | Code | Condition |
|---|---|---|
| `429` | `RATE_LIMIT_EXCEEDED` | Too many requests |

#### Business Rules

- The raw `settings` database rows (key/value/type/group format) are never returned directly
- The response is a transformed, grouped object — not a flat key-value list
- `null` values are returned for unpopulated settings (e.g., social media URLs the admin hasn't set)
- File paths (logo, favicon) are returned as fully resolved public URLs (`logo_url`, `favicon_url`)
- The `system` group is never included in any public response
- Secrets, passwords, and infrastructure configuration are never stored in the `settings` table

---

### 8.7 Get Contact Settings

**`GET /api/settings/contact`**

Returns only the contact group settings. Provided as a convenience endpoint for future mobile/external consumers that need only contact information without loading the full settings payload.

#### Authentication
None.

#### Query Parameters
None.

#### Success Response — `200 OK`

```json
{
  "success": true,
  "data": {
    "phone_primary": "+971501234567",
    "phone_secondary": "+971509876543",
    "email": "info@elitecarrentals.com",
    "address": "Dubai Marina, Dubai, UAE",
    "whatsapp_number": "971501234567"
  }
}
```

#### Error Responses

| Status | Code | Condition |
|---|---|---|
| `429` | `RATE_LIMIT_EXCEEDED` | Too many requests |

#### Business Rules

- Returns only the `contact` group; identical to `data.contact` from `GET /api/settings`
- `whatsapp_number` is returned without the `+` prefix — it is used directly in the `wa.me/{number}` URL format
- If `whatsapp_number` is not configured, it is returned as `null`; the consumer must handle this gracefully

---

## 9. Resource Schemas

These schemas define the canonical JSON shape of each public resource.

### 9.1 Car (Listing Item)

Used in `GET /api/cars` responses. Contains only fields needed to render a car card.

```
{
  id:           integer  — database ID (for client reference; not used in public URLs)
  name:         string   — full display name
  slug:         string   — URL identifier; use this for detail page links
  brand:        string   — manufacturer name
  model:        string   — model designation
  year:         integer  — model year (4 digits)
  color:        string?  — exterior color; null if not specified
  is_featured:  boolean  — true if shown in featured section
  category:     object   — embedded category (id, name, slug)
  cover_image:  object?  — single cover image object; null if no images uploaded
}
```

**Not included in listing:** `description`, `specifications`, `features`, full `images` array, `meta_title`, `meta_description`, `created_at`.

### 9.2 Car (Detail)

Used in `GET /api/cars/{slug}` response. Contains the full public car data.

```
{
  id:               integer   — database ID
  name:             string    — full display name
  slug:             string    — URL identifier
  brand:            string    — manufacturer name
  model:            string    — model designation
  year:             integer   — model year
  color:            string?   — exterior color
  description:      string?   — full marketing description (may contain HTML if admin uses rich text)
  specifications:   object?   — JSON object with flexible keys; keys vary by car type
  is_featured:      boolean
  meta_title:       string?   — SEO page title; null if not set
  meta_description: string?   — SEO meta description; null if not set
  created_at:       string    — ISO 8601 timestamp
  category:         object    — embedded category (id, name, slug)
  features:         string[]  — array of feature label strings; [] if none
  images:           object[]  — array of image objects; [] if no images
}
```

**Image object:**
```
{
  url:      string   — fully resolved public URL
  is_cover: boolean  — true for the designated cover/thumbnail image
  order:    integer  — display order (1-based, ascending)
}
```

**Never included in any car response:**
- `price_daily`, `price_weekly`, `price_monthly`, `currency`
- `category_id` (raw FK)
- `is_published` (presence in response implies published)
- `sort_order`
- `updated_at`

### 9.3 Category

```
{
  id:          integer   — database ID
  name:        string    — display name
  slug:        string    — URL identifier for category filter
  description: string?   — short description; null if not set
  icon:        string?   — icon class or identifier (e.g., "fa-car"); null if not set
  image_url:   string?   — fully resolved public URL; null if no image uploaded
}
```

**Never included:** `is_active`, `sort_order`, `created_at`, `updated_at`.

### 9.4 FAQ

```
{
  id:       integer  — database ID
  category: string?  — grouping label (e.g., "Rental Process"); null if ungrouped
  question: string   — the question text
  answer:   string   — the answer text (may contain line breaks)
}
```

**Never included:** `is_active`, `sort_order`, `created_at`, `updated_at`.

### 9.5 Banner

```
{
  id:        integer  — database ID
  title:     string?  — headline text; null if not set
  subtitle:  string?  — supporting text; null if not set
  image_url: string?  — fully resolved public URL; null if no image uploaded
  cta:       object?  — call-to-action; null if not configured
}
```

**CTA object:**
```
{
  text: string  — button label (e.g., "Browse Cars")
  url:  string  — destination URL (may be relative or absolute)
}
```

**Never included:** `is_active`, `sort_order`, `created_at`, `updated_at`.

### 9.6 Public Settings

The settings response is a structured object, not an array. Grouped by category:

```
{
  company: {
    name:        string?
    tagline:     string?
    description: string?
    logo_url:    string?   — fully resolved URL; null if not uploaded
    about_text:  string?
  },
  contact: {
    phone_primary:    string?
    phone_secondary:  string?
    email:            string?
    address:          string?
    whatsapp_number:  string?  — without "+" prefix, for wa.me URL construction
  },
  social: {
    facebook_url:   string?
    instagram_url:  string?
    twitter_url:    string?
    youtube_url:    string?
    tiktok_url:     string?
    linkedin_url:   string?
  },
  seo: {
    site_title:       string?
    meta_description: string?
    meta_keywords:    string?
  },
  appearance: {
    favicon_url:       string?  — fully resolved URL
    primary_color:     string?  — hex code (e.g., "#1A2B4C")
    secondary_color:   string?  — hex code
  }
}
```

**Excluded groups:** `system` (never returned).
**Excluded keys:** `seo.google_analytics_id` (internal tracking configuration).

---

## 10. Filtering, Sorting, and Pagination

### 10.1 Filtering

Filtering is supported only on `GET /api/cars`.

| Filter | Parameter | Type | Behavior |
|---|---|---|---|
| By category | `?category=suv` | string (slug) | Returns cars in that category only |
| By keyword | `?search=toyota` | string | LIKE match on `name` and `brand` |
| Featured only | `?featured=1` | boolean | Returns featured cars only |

Filters are combinable:
```
GET /api/cars?category=suv&featured=1
GET /api/cars?search=bmw&page=2&per_page=6
```

**No speculative filters are supported in V1:** There are no filters for price, fuel type, transmission, seats, availability, or location. These require V2 fleet and inventory features.

### 10.2 Sorting

Sorting is not configurable by API consumers in V1.

Default sort order for all collection endpoints:
```
ORDER BY sort_order ASC, id ASC
```

This matches the admin-configured display order. Custom sort parameters (e.g., `?sort=year&direction=desc`) are not supported in V1.

### 10.3 Pagination

Pagination applies only to `GET /api/cars`. All other endpoints return complete unpagenated lists.

| Parameter | Default | Maximum | Notes |
|---|---|---|---|
| `page` | `1` | — | Must be `>= 1` |
| `per_page` | `12` | `100` | Returns `422` if above 100 |

**Why 12 default:** Designed for a 3-column or 4-column car grid layout. Divisible by 3, 4, and 6.

**Pagination type:** Offset-based (standard Laravel paginator). Cursor pagination is not used in V1.

---

## 11. Error Handling

### 11.1 Error Response Format

All errors follow this structure:

```json
{
  "success": false,
  "error": {
    "code": "ERROR_CODE",
    "message": "Human-readable description.",
    "details": {}
  }
}
```

`details` is only present on validation errors (HTTP 422). It is omitted on all other error types.

### 11.2 Error Codes

| HTTP Status | Error Code | Description |
|---|---|---|
| `404` | `NOT_FOUND` | Resource does not exist or is not published/active |
| `422` | `VALIDATION_ERROR` | One or more query parameters failed validation |
| `429` | `RATE_LIMIT_EXCEEDED` | Too many requests from this IP |
| `500` | `SERVER_ERROR` | Unexpected server-side error |

### 11.3 Error Handling Rules

- Production environments must never return Laravel's default HTML error pages to API consumers (enforced by requiring `Accept: application/json` header)
- Stack traces, file paths, and exception class names are never included in production error responses
- The `message` field is safe to display to end users for `404` and `429`; for `500`, a generic message is used regardless of the actual exception
- `422` `details` keys correspond to the query parameter names that failed validation

### 11.4 Rate Limiting

- **Limit:** 60 requests per minute per IP address
- **Scope:** All `/api/*` routes
- **Response on limit exceeded:** `429` with `Retry-After` header indicating seconds until the limit resets
- **Implementation:** Laravel's built-in `throttle` middleware

---

## 12. Security Considerations

### 12.1 HTTPS

All production API traffic must be served over HTTPS. HTTP requests should be redirected to HTTPS at the web server (NGINX) level.

### 12.2 Sensitive Field Exclusion

The following fields are **never** returned by any public API endpoint:

| Field | Source Table | Reason |
|---|---|---|
| `price_daily`, `price_weekly`, `price_monthly` | `cars` | Internal reference only; pricing by WhatsApp |
| `currency` | `cars` | No public pricing |
| `is_published` | `cars` | Existence of drafts not disclosed |
| `sort_order` | Multiple | Admin operational field |
| `is_active` | Multiple | Admin operational field |
| `is_featured` (on list only — returned in detail) | `cars` | Returned in detail, not relevant to exclude |
| `system.*` settings | `settings` | Internal operational flags |
| `seo.google_analytics_id` | `settings` | Internal tracking configuration |
| Any `password`, `remember_token`, `last_login_*` | `admins` | Admin table never exposed |

### 12.3 Input Validation

All query parameters are validated before use. Invalid values return `422`. Query strings are never passed directly to SQL — all database queries go through Eloquent's parameterized query builder.

### 12.4 CORS

Cross-Origin Resource Sharing headers must be configured for the `/api/*` routes to allow future mobile and external web consumers. In V1, permitted origins should be configured via `config/cors.php` using Laravel's built-in CORS support (`fruitcake/laravel-cors`).

### 12.5 Production Error Masking

`APP_DEBUG=false` must be set in production. This prevents stack traces from leaking into API error responses. All `500` responses return a generic message regardless of the actual exception.

### 12.6 No Admin API Surface

The absence of admin REST endpoints eliminates the largest attack surface. Filament's admin panel is protected by session-based authentication at the HTTP level and is not accessible via the REST API.

---

## 13. Future API Extensions

The following areas are intentionally excluded from V1 but are architecturally prepared for V2:

| V2 Feature | API Scope |
|---|---|
| **Customer Authentication** | `POST /api/auth/register`, `POST /api/auth/login`, `POST /api/auth/logout`, `GET /api/auth/me` |
| **Booking API** | `POST /api/bookings`, `GET /api/bookings/{id}` — once server-side booking storage is added |
| **Payment API** | `POST /api/payments`, payment gateway webhook endpoints |
| **Fleet / Availability** | `GET /api/cars/{slug}/availability` — requires `availability_status` column on `cars` |
| **Driver API** | `GET /api/drivers` — requires V2 `drivers` table |
| **Admin REST API** | `/api/admin/*` endpoints — if a mobile admin app or external integration requires admin CRUD |
| **API Versioning** | Introduce `/api/v2/` prefix if breaking changes are needed while maintaining V1 compatibility |

**V2 database prerequisites (additive migrations — no V1 changes):**
- `customers` table — for customer authentication
- `bookings` table — for server-side booking storage
- `availability_status` column on `cars` — for fleet availability
- `drivers` table — for driver management

---

## 14. Consistency Notes

This section documents the original inconsistencies found in the source documents during the drafting of this contract, and confirms their resolution. All three source documents are now aligned.

### 14.1 API Base Path

**Original issue:** `System_Architecture_Plan.md` (v1.3.0 and earlier) stated all routes under `/api/v1/`.

**Resolution:** The architecture document was corrected to `/api` (no version prefix in V1) in v1.4.0. This contract uses `/api` throughout.

### 14.2 Admin API Endpoints

**Original issue:** `System_Architecture_Plan.md` (v1.3.0 and earlier) §11.4 defined a full set of `/api/v1/admin/*` endpoints.

**Resolution:** The architecture document was corrected in v1.4.0 — §11.4 now states "There are no admin REST API endpoints in V1. All admin operations are handled by Filament." This contract defines no admin endpoints.

### 14.3 Public Website and API Consumption

**Original issue:** `System_Architecture_Plan.md` (v1.3.0 and earlier) described the public website as standalone HTML pages fetching from the API via JavaScript. The pre-booking form (`booking.html`) was documented as calling `GET /api/cars` and `GET /api/settings/contact`.

**Resolution:** The architecture document was corrected in v1.4.0:
- The public website is a **Laravel Blade MPA** — controllers call the Service layer directly; no API fetching
- The pre-booking form is a **Blade page** (`/booking`) — `BookingController` passes the car list and WhatsApp number server-side to the template; no API call is made
- **No V1 in-application code consumes the REST API**

### 14.4 Filament Version

**Original issue:** `System_Architecture_Plan.md` referenced Filament v3.

**Resolution:** Corrected to Filament v5 in v1.4.0. The API contract is unaffected by the Filament version.

---

## 15. Final Endpoint Summary

| Method | Endpoint | Auth | Purpose | Paginated | V1 Status |
|---|---|---|---|---|---|
| `GET` | `/api/cars` | None | List published cars with filtering | Yes | ✅ Defined |
| `GET` | `/api/cars/{slug}` | None | Get full car details by slug | No | ✅ Defined |
| `GET` | `/api/categories` | None | List active categories | No | ✅ Defined |
| `GET` | `/api/faqs` | None | List active FAQs | No | ✅ Defined |
| `GET` | `/api/banners` | None | List active hero banners | No | ✅ Defined |
| `GET` | `/api/settings` | None | Get all public settings grouped | No | ✅ Defined |
| `GET` | `/api/settings/contact` | None | Get contact settings only | No | ✅ Defined |

**Total V1 endpoints: 7**

**Explicitly excluded from V1:**
- Any `POST`, `PUT`, `PATCH`, `DELETE` endpoint
- Any `/api/admin/*` endpoint
- Any `/api/auth/*` endpoint
- Any `/api/bookings/*` endpoint
- Any `/api/payments/*` endpoint

---

**Document Metadata:**

| Field | Value |
|-------|-------|
| Document Name | API_Contract.md |
| Version | 1.1.0 |
| Created | August 2026 |
| Sources | System_Architecture_Plan.md v1.4.0, Database_Design.md v1.1.0 |
| Status | Ready for Implementation |
| Endpoints Defined | 7 (V1 public, read-only) |
| Next Document | — |
