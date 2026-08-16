# Backend Development Plan — Car Rental Website (Version 1)

---

> **Authoritative Sources:** This document implements the decisions defined in:
> - `System_Architecture_Plan.md` (v1.4.0)
> - `Database_Design.md` (v1.1.0)
> - `API_Contract.md` (v1.1.0)
>
> This document defines **how** to implement V1. It does not redefine architecture, database design, or API contracts.
> When this document refers to a field, endpoint, or rule, the source document is the authority on its exact definition.

---

## 1. Purpose and Scope

This document defines the concrete implementation plan for the Version 1 Laravel backend.

**What this document covers:**
- All backend files, classes, and responsibilities
- Service layer design and method-level planning
- Eloquent model configuration
- Filament resource implementation
- REST API implementation (Laravel controllers + API Resources)
- Public web controller responsibilities (Blade backend integration)
- Validation strategy
- Media handling via Spatie Media Library
- Settings management
- Caching, queues, error handling, security, and testing

**What this document does NOT cover:**
- Blade template design, CSS, or frontend JavaScript (→ `Frontend_Development_Plan.md`)
- Database schema definitions (→ `Database_Design.md`)
- API endpoint contracts (→ `API_Contract.md`)
- System architecture decisions (→ `System_Architecture_Plan.md`)
- V2 implementation work of any kind

**The V1 constraint:** This is a marketing website. The backend exists to serve content — not to process transactions. Keep every decision proportional to that purpose.

---

## 2. Backend Architecture Context

> Full architecture is defined in `System_Architecture_Plan.md`. This section only states what is directly relevant to implementation choices.

The approved backend stack is:

```
HTTP Layer      Routes → Middleware → FormRequest → Controller
     ↓
Service Layer   Business logic, caching, orchestration
     ↓
Data Layer      Eloquent Models → MySQL
```

**What this means for implementation:**

| Layer | Responsibility | Must NOT contain |
|-------|---------------|-----------------|
| Controller | Receive request, validate, call one service, return view or JSON | Business logic, Eloquent queries |
| Service | Business decisions, caching, event firing, coordination | HTTP concerns, view rendering |
| Model | Relationships, scopes, casts, accessors | Business logic, HTTP logic |

**No Repository Pattern.** There are no `Repository` interfaces, `Contract` folders, or Domain Entity classes. The Service layer is the isolation boundary.

**Two HTTP entry points:**
1. `routes/web.php` — Blade MPA public pages
2. `routes/api.php` — REST API for future external consumers

**One admin entry point:**
- Filament at `/admin` — server-rendered, session-authenticated, accesses services/Eloquent directly

---

## 3. Laravel Project Structure

The project root is `cars_rental/`. The structure below shows only the directories and files that will be created during V1 implementation. This is derived from `System_Architecture_Plan.md` §9.

```
cars_rental/
│
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Public/          # Blade page controllers
│   │   │   │   ├── HomeController.php
│   │   │   │   ├── CarController.php
│   │   │   │   ├── CategoryController.php
│   │   │   │   ├── FaqController.php
│   │   │   │   ├── ContactController.php
│   │   │   │   └── BookingController.php
│   │   │   └── Api/             # REST API controllers
│   │   │       ├── CarController.php
│   │   │       ├── CategoryController.php
│   │   │       ├── FaqController.php
│   │   │       ├── BannerController.php
│   │   │       └── SettingController.php
│   │   ├── Requests/
│   │   │   ├── Api/
│   │   │   │   └── CarListRequest.php
│   │   │   └── Web/
│   │   │       └── ContactFormRequest.php
│   │   ├── Resources/           # API Resources (JSON transformers)
│   │   │   ├── CarListingResource.php
│   │   │   ├── CarDetailResource.php
│   │   │   ├── CategoryResource.php
│   │   │   ├── FaqResource.php
│   │   │   ├── BannerResource.php
│   │   │   └── PublicSettingsResource.php
│   │   └── Middleware/
│   │       ├── ForceHttps.php
│   │       ├── MaintenanceMode.php
│   │       └── ApiRateLimit.php
│   │
│   ├── Services/
│   │   ├── CarService.php
│   │   ├── CategoryService.php
│   │   ├── FaqService.php
│   │   ├── BannerService.php
│   │   ├── SettingService.php
│   │   └── MediaService.php
│   │
│   ├── Models/
│   │   ├── Admin.php
│   │   ├── Car.php
│   │   ├── CarFeature.php
│   │   ├── Category.php
│   │   ├── Faq.php
│   │   ├── Banner.php
│   │   └── Setting.php
│   │
│   ├── Filament/
│   │   ├── Resources/
│   │   │   ├── CarResource.php
│   │   │   ├── CarResource/Pages/
│   │   │   │   ├── ListCars.php
│   │   │   │   ├── CreateCar.php
│   │   │   │   └── EditCar.php
│   │   │   ├── CategoryResource.php
│   │   │   ├── CategoryResource/Pages/
│   │   │   ├── FaqResource.php
│   │   │   ├── FaqResource/Pages/
│   │   │   ├── BannerResource.php
│   │   │   └── BannerResource/Pages/
│   │   ├── Pages/
│   │   │   └── ManageSettings.php
│   │   └── Widgets/
│   │       ├── StatsOverviewWidget.php
│   │       └── LatestCarsWidget.php
│   │
│   ├── Exceptions/
│   │   └── Handler.php
│   │
│   ├── Events/
│   │   ├── CarSaved.php
│   │   └── CarDeleted.php
│   │
│   ├── Listeners/
│   │   └── ClearCarCache.php
│   │
│   ├── Policies/
│   │   ├── CarPolicy.php
│   │   ├── CategoryPolicy.php
│   │   ├── FaqPolicy.php
│   │   ├── BannerPolicy.php
│   │   └── SettingPolicy.php
│   │
│   └── Providers/
│       ├── AppServiceProvider.php
│       ├── EventServiceProvider.php
│       ├── AuthServiceProvider.php
│       └── Filament/
│           └── AdminPanelProvider.php
│
├── database/
│   ├── migrations/              # 8 V1 migrations (see §5)
│   ├── seeders/
│   │   ├── DatabaseSeeder.php
│   │   ├── AdminSeeder.php
│   │   ├── CategorySeeder.php
│   │   └── SettingSeeder.php
│   └── factories/
│       ├── CarFactory.php
│       └── CategoryFactory.php
│
├── routes/
│   ├── web.php                  # Blade MPA public pages
│   └── api.php                  # REST API routes (/api/*)
│
├── resources/
│   └── views/
│       ├── layouts/app.blade.php
│       └── pages/
│           ├── home.blade.php
│           ├── cars/index.blade.php
│           ├── cars/show.blade.php
│           ├── about.blade.php
│           ├── faq.blade.php
│           ├── contact.blade.php
│           └── booking.blade.php
│
└── config/
    ├── filament.php
    └── car-rental.php           # App-specific constants (default per_page, etc.)
```

**Directories explicitly NOT created:**
- `app/Domain/` — no DDD abstractions
- `app/Repositories/` — no repository pattern
- `app/Contracts/` — no interface layer
- Any V2 placeholder directories

---

## 4. Application Modules

V1 consists of five domains. Each domain owns controllers, a service, a model (or set of models), and a Filament resource.

| Domain | Models | Service | Web Controllers | API Controllers | Filament |
|--------|--------|---------|-----------------|-----------------|----------|
| Cars | `Car`, `CarFeature` | `CarService` | `Public\CarController` | `Api\CarController` | `CarResource` |
| Categories | `Category` | `CategoryService` | `Public\CategoryController` (shared with car listing) | `Api\CategoryController` | `CategoryResource` |
| FAQs | `Faq` | `FaqService` | `Public\FaqController` | `Api\FaqController` | `FaqResource` |
| Banners | `Banner` | `BannerService` | (used by `HomeController`) | `Api\BannerController` | `BannerResource` |
| Settings | `Setting` | `SettingService` | (used by all web controllers) | `Api\SettingController` | `ManageSettings` |
| Media | *(Spatie handles)* | `MediaService` | — | — | *(via `SpatieMediaLibraryFileUpload`)* |
| Admin Auth | `Admin` | — | — | — | *(Filament built-in)* |

**Cross-cutting modules:** `MediaService` is used by `CarService`, `BannerService`, and `CategoryService`. `SettingService` is used by all web controllers and the booking page.

---

## 5. Database and Eloquent Implementation

> The complete schema is defined in `Database_Design.md`. This section explains how each model maps to that schema and configures Laravel-specific behaviour.

### 5.1 Migration Execution Order

Migrations must be created in this order to satisfy foreign key dependencies:

1. `create_admins_table`
2. `create_categories_table`
3. `create_cars_table` — declares `category_id` FK with `->onDelete('restrict')`
4. `create_car_features_table` — declares `car_id` FK with `->onDelete('cascade')` and `->unique(['car_id', 'feature'])`
5. `create_media_table` — published from Spatie: `php artisan vendor:publish --tag="medialibrary-migrations"`
6. `create_faqs_table`
7. `create_banners_table`
8. `create_settings_table`

**Critical notes:**
- The `cars` migration must override Laravel's default `constrained()` cascade to `restrict`: `$table->foreignId('category_id')->constrained()->onDelete('restrict')`
- The `car_features` migration must add the composite unique constraint: `$table->unique(['car_id', 'feature'])`
- The `media` table must never be hand-written

### 5.2 Models

---

#### `Admin`

**Table:** `admins`
**Guard:** `admin` (configured in `config/auth.php`)

**Configuration:**
- Implements `FilamentUser` interface (required by Filament to authorize panel access)
- `canAccessPanel(Panel $panel): bool` returns `true` for all admin records in V1 (no RBAC)
- `$fillable`: `name`, `email`, `password` (password is hashed via `bcrypt` before fill)
- `$hidden`: `password`, `remember_token`
- No soft delete

**Purpose:** The `Admin` model is the sole Filament authenticatable. It is never exposed via the public REST API. No public controller interacts with this model.

---

#### `Category`

**Table:** `categories`

**Configuration:**
- `$fillable`: `name`, `slug`, `description`, `icon`, `image`, `is_active`, `sort_order`
- `$casts`: `is_active` → `boolean`, `sort_order` → `integer`
- Slug generation: implement `Spatie\Sluggable\HasSlug` trait; `getSlugOptions()` generates from `name`, saves to `slug`, does not regenerate on update
- No soft delete

**Relationships:**
```php
public function cars(): HasMany  // hasMany(Car::class)
```

**Scopes:**
```php
public function scopeActive($query)        // WHERE is_active = 1
public function scopeOrdered($query)       // ORDER BY sort_order ASC, id ASC
```

**Deletion guard:** Before allowing Filament to delete a category, a check must confirm `$category->cars()->exists()` is false. If cars exist, a Filament notification error is shown and deletion is blocked.

**Image handling:** The `image` column stores a relative file path string (e.g., `categories/uuid.webp`). The public URL is resolved by `Storage::url($category->image)`. No Spatie Media Library involvement.

---

#### `Car`

**Table:** `cars`

**Configuration:**
- `$fillable`: `category_id`, `name`, `slug`, `brand`, `model`, `year`, `color`, `description`, `specifications`, `price_daily`, `price_weekly`, `price_monthly`, `currency`, `is_published`, `is_featured`, `sort_order`, `meta_title`, `meta_description`
- `$casts`: `specifications` → `array`, `is_published` → `boolean`, `is_featured` → `boolean`, `year` → `integer`, `sort_order` → `integer`, `price_daily` → `decimal:2`, `price_weekly` → `decimal:2`, `price_monthly` → `decimal:2`
- Slug: `HasSlug` from `spatie/laravel-sluggable`; generated from `name`, does not regenerate on update (slug is permanent once set)
- Media: implements `HasMedia` and uses `InteractsWithMedia` from `spatie/laravel-medialibrary`
- No soft delete

**Relationships:**
```php
public function category(): BelongsTo     // belongsTo(Category::class)
public function features(): HasMany       // hasMany(CarFeature::class)
```

**Media Collection:**
```php
public function registerMediaCollections(): void
{
    $this->addMediaCollection('car_images')
         ->useDisk('public');
}
```

**Scopes:**
```php
public function scopePublished($query)     // WHERE is_published = 1
public function scopeFeatured($query)      // WHERE is_featured = 1
public function scopeOrdered($query)       // ORDER BY sort_order ASC, id ASC
public function scopeInCategory($query, string $slug)  // JOIN categories WHERE slug = ?
```

**Events:** `CarSaved` is fired after `created` or `updated`. `CarDeleted` is fired after `deleted`. Both events trigger `ClearCarCache`.

**Pricing fields:** `price_daily`, `price_weekly`, `price_monthly` are in `$fillable` (admin writes them) but are excluded from all public API Resources. They are never passed to any public view or JSON response.

---

#### `CarFeature`

**Table:** `car_features`

**Configuration:**
- `$fillable`: `car_id`, `feature`
- No casts required
- No soft delete

**Relationships:**
```php
public function car(): BelongsTo    // belongsTo(Car::class)
```

**Notes:** The composite unique constraint `(car_id, feature)` is enforced at the database level. The application layer in `CarService` must also normalize feature input (trim, consistent casing) before saving to prevent near-duplicate values.

---

#### `Faq`

**Table:** `faqs`

**Configuration:**
- `$fillable`: `question`, `answer`, `category`, `is_active`, `sort_order`
- `$casts`: `is_active` → `boolean`, `sort_order` → `integer`
- No soft delete

**Scopes:**
```php
public function scopeActive($query)     // WHERE is_active = 1
public function scopeOrdered($query)    // ORDER BY sort_order ASC, id ASC
```

---

#### `Banner`

**Table:** `banners`

**Configuration:**
- `$fillable`: `title`, `subtitle`, `image`, `cta_text`, `cta_url`, `is_active`, `sort_order`
- `$casts`: `is_active` → `boolean`, `sort_order` → `integer`
- No soft delete

**Image handling:** The `image` column stores a relative file path string. Public URL resolved via `Storage::url($banner->image)`. No Spatie Media Library.

**File deletion on model delete:** Handled by a `BannerObserver` registered in `AppServiceProvider`. The observer calls `Storage::delete($banner->image)` in its `deleted()` method before the database row is removed.

**Scopes:**
```php
public function scopeActive($query)     // WHERE is_active = 1
public function scopeOrdered($query)    // ORDER BY sort_order ASC, id ASC
```

---

#### `Setting`

**Table:** `settings`

**Configuration:**
- `$fillable`: `key`, `value`, `type`, `settings_group`, `description`
- `$casts`: none at the model level — casting is done by `SettingService` based on the `type` column value
- No soft delete

**Notes:** This model is intentionally thin. It is a data container. All setting retrieval, grouping, caching, and type-casting logic lives in `SettingService`.

### 5.3 Eloquent Relationships Summary

```
Category ──── has many ──────────────────→ Car (RESTRICT on delete)
                                            │
                                            ├── has many ──→ CarFeature (CASCADE on delete)
                                            │
                                            └── (Spatie) ──→ media rows (collection='car_images')
                                                             (auto-deleted by Spatie on car delete)

Admin       — no relationships
Banner      — standalone
Faq         — standalone
Setting     — standalone
```

### 5.4 Slug Strategy

Both `Car` and `Category` use `spatie/laravel-sluggable`.

| Behaviour | Configuration |
|-----------|--------------|
| Generated from | `name` column |
| Stored in | `slug` column |
| Regenerate on update | **No** — slug is permanent once created |
| Uniqueness | Package appends `-2`, `-3` etc. on collision |
| Public lookup | `Car::where('slug', $slug)->published()->firstOrFail()` |

**Why no regeneration on update:** Changing a published car's slug breaks shared URLs, bookmarks, and any cached links. The admin can manually edit the slug field in Filament only if they explicitly intend to change the URL.

### 5.5 Seeders

| Seeder | Content |
|--------|---------|
| `AdminSeeder` | Creates one admin account using credentials from `.env` (`ADMIN_EMAIL`, `ADMIN_PASSWORD`) |
| `SettingSeeder` | Inserts all 25 V1 setting keys with `null` or sensible default values |
| `CategorySeeder` | Inserts 5–7 starter categories (SUV, Sedan, Luxury, Economy, Sports) with slugs |

Additional seeders for demo data during development only — not seeded in production:
- `CarSeeder` — 10 sample cars with features and placeholder images
- `FaqSeeder` — 10–15 sample FAQ items

---

## 6. Service Layer

Services contain all business logic. Controllers call one service method per action and return the result. Services may call other services where justified.

### 6.1 `CarService`

**Location:** `app/Services/CarService.php`
**Dependencies:** `Car` model, `CategoryService` (for category validation), `MediaService` (for image URL resolution)

**Responsibility:** All read operations for published cars. Coordinates cache lookup/fill for public queries. Fires events on write operations (which are handled by Filament directly, so the service handles post-save event firing via model observers).

**Public methods:**

| Method | Used By | Description |
|--------|---------|-------------|
| `getAllPublished(array $filters): LengthAwarePaginator` | `Public\CarController`, `Api\CarController` | Returns paginated published cars applying `category`, `search`, `featured` filters. Checks Redis cache before querying. |
| `findBySlug(string $slug): Car` | `Public\CarController`, `Api\CarController`, `BookingController` | Finds one published car by slug. Returns 404 if not found or unpublished. Checks Redis cache. |
| `getFeatured(int $limit = 8): Collection` | `HomeController` | Returns featured published cars up to the specified limit. Cached separately. |
| `clearCache(): void` | `ClearCarCache` listener | Clears all car-related Redis cache keys. Called by `CarSaved` and `CarDeleted` events. |

**Must NOT contain:**
- HTTP redirect logic
- View rendering
- Direct Filament form handling (Filament talks to Eloquent through its own lifecycle)

**Cache keys used:**
- `cars:list:{category}:{search}:{featured}:{page}:{per_page}` — paginated list results
- `cars:slug:{slug}` — single car detail
- `cars:featured:{limit}` — featured cars for homepage

### 6.2 `CategoryService`

**Location:** `app/Services/CategoryService.php`
**Dependencies:** `Category` model

**Public methods:**

| Method | Used By | Description |
|--------|---------|-------------|
| `getActive(): Collection` | `Public\CarController`, `Api\CategoryController`, `HomeController` | Returns all active categories ordered by `sort_order`. Cached. |
| `findBySlug(string $slug): ?Category` | `CarService` (for filter validation), `Api\CarController` | Returns an active category by slug; returns `null` if not found or inactive. |

**Cache key:** `categories:active`

**Deletion guard logic:** When a Filament delete action is triggered on a `Category`, a Filament `Action::before()` hook must call `$category->cars()->exists()` and show a Filament notification error if true, blocking the deletion.

### 6.3 `FaqService`

**Location:** `app/Services/FaqService.php`
**Dependencies:** `Faq` model

**Public methods:**

| Method | Used By | Description |
|--------|---------|-------------|
| `getActive(): Collection` | `Public\FaqController`, `Api\FaqController` | Returns all active FAQs ordered by `sort_order`. Cached. |

**Cache key:** `faqs:active`
**Cache invalidation:** On any Filament FAQ save or delete, the cache key is cleared. Handled by a `FaqObserver` or by hooking into Filament lifecycle methods.

### 6.4 `BannerService`

**Location:** `app/Services/BannerService.php`
**Dependencies:** `Banner` model, `MediaService` (for file upload)

**Public methods:**

| Method | Used By | Description |
|--------|---------|-------------|
| `getActive(): Collection` | `HomeController`, `Api\BannerController` | Returns all active banners ordered by `sort_order`. Cached. |

**Cache key:** `banners:active`
**File deletion:** Handled by `BannerObserver::deleted()` which calls `Storage::delete($banner->image)`.

### 6.5 `SettingService`

**Location:** `app/Services/SettingService.php`
**Dependencies:** `Setting` model, `Cache` facade

**Responsibility:** The sole interface for all setting reads and writes. Loads settings from Redis cache on first call. Returns typed values based on the `type` column.

**Public methods:**

| Method | Used By | Description |
|--------|---------|-------------|
| `get(string $key, mixed $default = null): mixed` | Web controllers, API `SettingController` | Returns a single setting value, type-cast. Loads all settings into cache on first call. |
| `all(): array` | `SettingController`, `ManageSettings` page | Returns all settings grouped by `settings_group` as a nested array. |
| `getGroup(string $group): array` | `Api\SettingController` (for contact endpoint) | Returns all settings for one group. |
| `set(string $key, mixed $value): void` | `ManageSettings` Filament page | Updates one setting value. Clears the settings cache. |
| `setBulk(array $keyValues): void` | `ManageSettings` Filament page (on form save) | Updates multiple settings at once. Clears cache once. |
| `clearCache(): void` | Called after any write | Deletes the `settings:all` Redis key. |

**Type casting logic:**

| `type` column value | Cast applied |
|--------------------|-------------|
| `string` | Return as `string` |
| `text` | Return as `string` |
| `integer` | Cast to `(int)` |
| `boolean` | Cast to `(bool)` (1 = true, 0 = false) |
| `json` | `json_decode($value, true)` |

**File path resolution:** For keys `company.logo` and `appearance.favicon`, the `SettingService` does NOT resolve URLs — URL resolution is done by the API Resource class using `Storage::url()`.

**Cache key:** `settings:all` — a single flattened collection of all 25 rows, loaded once and cached. Group-level reads are derived from this in-memory after retrieval.

### 6.6 `MediaService`

**Location:** `app/Services/MediaService.php`
**Dependencies:** `Illuminate\Http\UploadedFile`, Spatie `HasMedia` interface

**Responsibility:** Handles file uploads and deletion for entities that do NOT use Spatie Media Library (banners, categories). For cars, Spatie handles media directly through Filament's `SpatieMediaLibraryFileUpload` component — `MediaService` is not involved in that flow.

**Public methods:**

| Method | Used By | Description |
|--------|---------|-------------|
| `storeImage(UploadedFile $file, string $folder): string` | `BannerResource` (Filament), `CategoryResource` (Filament) | Validates, stores the image in `storage/app/public/{folder}/`, returns the relative path. |
| `deleteImage(?string $path): void` | `BannerObserver`, `CategoryObserver` | Calls `Storage::delete($path)` if path is not null. |
| `resolveUrl(?string $path): ?string` | Banner/Category API Resources | Returns `Storage::url($path)` or `null`. |

**Validation performed by `storeImage`:**
- MIME type must be in: `image/jpeg`, `image/png`, `image/webp`, `image/gif`
- Max file size: 5 MB
- File extension whitelist: `jpg`, `jpeg`, `png`, `webp`, `gif`
- Filename is replaced with a UUID-based name: `Str::uuid() . '.' . $extension`

**What `MediaService` must NOT do:**
- Touch Spatie's `media` table or the car images collection
- Handle car images (Spatie handles this natively)

---

## 7. Web Layer / Blade Backend Integration

> The public website is a Blade MPA. Controllers receive HTTP requests, call services, and return Blade views with data. Full Blade/frontend design is in `Frontend_Development_Plan.md`.

### 7.1 Route Definitions (`routes/web.php`)

```php
Route::get('/',            [HomeController::class,    'index'])->name('home');
Route::get('/cars',        [CarController::class,     'index'])->name('cars.index');
Route::get('/cars/{slug}', [CarController::class,     'show'])->name('cars.show');
Route::get('/about',       [AboutController::class,   'index'])->name('about');
Route::get('/faq',         [FaqController::class,     'index'])->name('faq.index');
Route::get('/contact',     [ContactController::class, 'index'])->name('contact');
Route::post('/contact',    [ContactController::class, 'submit'])->name('contact.submit');
Route::get('/booking',     [BookingController::class, 'show'])->name('booking');
```

The `MaintenanceMode` middleware is applied globally. All web routes are implicitly CSRF-protected by Laravel.

### 7.2 Controller Responsibilities

---

#### `Public\HomeController`

**Method:** `index()`

**Service calls:**
- `BannerService::getActive()` → `$banners`
- `CarService::getFeatured(8)` → `$featuredCars`
- `FaqService::getActive()` → `$faqs` (first 5, for homepage preview)
- `SettingService::get('company.*')` → `$settings`

**Returns:** `view('pages.home', compact('banners', 'featuredCars', 'faqs', 'settings'))`

---

#### `Public\CarController`

**Method:** `index(Request $request)`

**Service calls:**
- `CategoryService::getActive()` → `$categories`
- `CarService::getAllPublished($request->only(['category', 'search', 'page', 'per_page']))` → `$cars`

**Returns:** `view('pages.cars.index', compact('cars', 'categories'))`

**Method:** `show(string $slug)`

**Service calls:**
- `CarService::findBySlug($slug)` → `$car` (throws `ModelNotFoundException` if not found/unpublished)
- `SettingService::get('contact.whatsapp_number')` → `$whatsappNumber`

**Returns:** `view('pages.cars.show', compact('car', 'whatsappNumber'))`

---

#### `Public\FaqController`

**Method:** `index()`

**Service calls:**
- `FaqService::getActive()` → `$faqs`

**Returns:** `view('pages.faq', compact('faqs'))`

---

#### `Public\ContactController`

**Method:** `index()`
**Returns:** `view('pages.contact')` with settings for phone, email, address, WhatsApp from `SettingService`

**Method:** `submit(ContactFormRequest $request)`
**Action:** Optionally sends email via `Mail` (if SMTP configured). Returns back with success message. No database write.

---

#### `Public\BookingController`

**Method:** `show(Request $request)`

**Service calls:**
- `CarService::getAllPublished(['per_page' => 100])` → `$cars` (full list for dropdown, no pagination needed)
- `SettingService::get('contact.whatsapp_number')` → `$whatsappNumber`
- If `$request->has('car')`: `CarService::findBySlug($request->car)` → `$selectedCar`

**Returns:** `view('pages.booking', compact('cars', 'whatsappNumber', 'selectedCar'))`

**What this controller must NOT do:**
- Store any booking data
- Send a WhatsApp message from the server
- Create any database record
- Call any booking-related service (none exists)

---

### 7.3 Shared Layout Data

Settings (company name, logo, navigation links, social links) are needed on every public page layout. Two approaches are valid:

**Option A (Recommended):** Register a View Composer in `AppServiceProvider` that injects `SettingService::getGroup('company')`, `getGroup('contact')`, and `getGroup('social')` into the shared `layouts/app.blade.php`.

**Option B:** Each controller passes settings explicitly.

Option A is preferred because it avoids repetition across every controller and the settings are cached — the overhead is negligible.

---

## 8. Filament Admin Implementation

> The admin panel is Filament v5. All admin operations go through Filament Resources. No custom admin HTML, JavaScript, or REST API endpoints exist.

### 8.1 Panel Configuration (`AdminPanelProvider`)

**Location:** `app/Providers/Filament/AdminPanelProvider.php`

| Configuration | Value |
|--------------|-------|
| Panel ID | `admin` |
| Path | `/admin` |
| Auth model | `App\Models\Admin` |
| Auth guard | `admin` |
| Login route | `/admin/login` (Filament built-in) |
| Logout action | Filament built-in |
| Colors | Match company `primary_color` from settings |
| Dark mode | Optional — configure as project preference |
| Registered resources | `CarResource`, `CategoryResource`, `FaqResource`, `BannerResource` |
| Registered pages | `ManageSettings` |
| Registered widgets | `StatsOverviewWidget`, `LatestCarsWidget` |

### 8.2 `CarResource`

**Location:** `app/Filament/Resources/CarResource.php`
**Model:** `Car`

#### Table (List Page)

| Column | Type | Sortable | Searchable |
|--------|------|----------|------------|
| Car Name | `TextColumn` | Yes | Yes |
| Brand | `TextColumn` | Yes | Yes |
| Category | `TextColumn` (via relationship) | No | No |
| Status | `ToggleColumn` (`is_published`) | No | No |
| Featured | `ToggleColumn` (`is_featured`) | No | No |
| Sort Order | `TextColumn` | Yes | No |
| Created At | `DateColumn` | Yes | No |

**Filters:**
- `SelectFilter` — Category (list of all categories)
- `TernaryFilter` — Published / Draft / All
- `TernaryFilter` — Featured / Not Featured / All

**Actions:**
- `EditAction` — standard
- `DeleteAction` — hard delete (triggers `CarDeleted` event → `ClearCarCache`)

**Reorder:** Enable `reorderRecordsUsing()` on `sort_order` for drag-and-drop ordering in the list.

#### Form (Create/Edit Pages)

**Section: Basic Info**
- `TextInput` — Name (required, max 255)
- `TextInput` — Slug (auto-generated from name; editable; unique validation)
- `Select` — Category (required; `options` from `Category::active()->ordered()->pluck('name', 'id')`)
- `Textarea` or `RichEditor` — Description (nullable)

**Section: Specifications**
- `KeyValue` component — mapped to `specifications` JSON column
- Keys are flexible (admin defines them per car)
- Hint text: "Examples: Engine, Transmission, Seats, Fuel Type, Drive Type"

**Section: Features**
- `Repeater` (or `TagsInput`) — mapped to `car_features` table via relationship
- Repeater with one `TextInput` per feature row
- Filament manages the `has-many` relationship save/delete automatically when using `Repeater`

**Section: Images**
- `SpatieMediaLibraryFileUpload` — collection `'car_images'`
- Multiple: `->multiple()`
- Reorderable: `->reorderable()`
- Image preview: `->image()`
- Max files: 20 (reasonable limit)
- File types accepted: `image/jpeg`, `image/png`, `image/webp`

**Cover Image Strategy:** When images are reordered in the Filament component, the first image in order is treated as the cover. The `MediaService` approach: after save, query the `car_images` collection and set `custom_properties['is_cover'] = true` on the first item, `false` on all others. This can be done in a Filament `afterSave()` hook on the Edit/Create page.

**Section: Pricing (internal — not public)**
- `TextInput` (numeric) — Price Daily (nullable, decimal)
- `TextInput` (numeric) — Price Weekly (nullable, decimal)
- `TextInput` (numeric) — Price Monthly (nullable, decimal)
- `Select` — Currency (default AED)
- Add a `Placeholder` or `Section` header warning: "Prices are for internal reference only. They are not displayed on the public website."

**Section: Publication**
- `Toggle` — Published (`is_published`)
- `Toggle` — Featured (`is_featured`)
- `TextInput` (numeric) — Sort Order (default 0)
- `TextInput` — Meta Title (nullable)
- `Textarea` — Meta Description (nullable)

#### Slug Auto-Generation

`spatie/laravel-sluggable` handles slug creation on model `creating` event. In the Filament form, the slug field should be pre-filled via an `afterStateUpdated()` hook on the Name field:
```php
TextInput::make('slug')
    ->unique(ignoreRecord: true)
    ->required()
```
The slug is shown and editable so the admin can review it before saving.

### 8.3 `CategoryResource`

**Location:** `app/Filament/Resources/CategoryResource.php`
**Model:** `Category`

#### Table

| Column | Type | Sortable |
|--------|------|----------|
| Name | `TextColumn` | Yes |
| Slug | `TextColumn` | No |
| Active | `ToggleColumn` | No |
| Sort Order | `TextColumn` | Yes |

**Actions:** `EditAction`, `DeleteAction` — delete is blocked if category has cars (checked before delete via Filament `Action::before()` hook).

#### Form

- `TextInput` — Name (required)
- `TextInput` — Slug (auto-generated, editable, `->unique(ignoreRecord: true)`)
- `Textarea` — Description (nullable)
- `TextInput` — Icon (nullable; label: "CSS class or icon name, e.g. fa-car")
- `FileUpload` — Image (nullable; stores to `categories/`; uses `MediaService::storeImage()` via a `afterStateUpdated()` save hook or Filament's built-in `FileUpload` with disk `public`)
- `Toggle` — Active (`is_active`)
- `TextInput` (numeric) — Sort Order

### 8.4 `FaqResource`

**Location:** `app/Filament/Resources/FaqResource.php`
**Model:** `Faq`

#### Table

| Column | Type | Sortable |
|--------|------|----------|
| Question (truncated) | `TextColumn` | No |
| Category | `TextColumn` | No |
| Active | `ToggleColumn` | No |
| Sort Order | `TextColumn` | Yes |

**Filter:** `SelectFilter` — Category (predefined group labels)

**Reorder:** Enable `reorderRecordsUsing('sort_order')`.

#### Form

- `Textarea` — Question (required)
- `RichEditor` or `Textarea` — Answer (required; LONGTEXT)
- `TextInput` — Category (nullable; hint: "Optional grouping label, e.g. 'Rental Process'")
- `Toggle` — Active
- `TextInput` (numeric) — Sort Order

### 8.5 `BannerResource`

**Location:** `app/Filament/Resources/BannerResource.php`
**Model:** `Banner`

#### Table

| Column | Type |
|--------|------|
| Title (nullable) | `TextColumn` |
| Image (thumbnail) | `ImageColumn` |
| Active | `ToggleColumn` |
| Sort Order | `TextColumn` |

**Reorder:** Enable `reorderRecordsUsing('sort_order')`.

#### Form

- `FileUpload` — Image (nullable; `->disk('public')->directory('banners')`; store relative path in `banners.image`)
- `TextInput` — Title (nullable, max 255)
- `TextInput` — Subtitle (nullable, max 500)
- `TextInput` — CTA Text (nullable, max 100)
- `TextInput` — CTA URL (nullable, max 500; URL validation)
- `Toggle` — Active
- `TextInput` (numeric) — Sort Order

**File deletion:** Handled by `BannerObserver::deleted()` automatically — no form-level code needed.

### 8.6 `ManageSettings` (Custom Page)

**Location:** `app/Filament/Pages/ManageSettings.php`
**Extends:** `Filament\Pages\Page` with a custom form

**Behaviour:**
- On load: calls `SettingService::all()` and maps the returned grouped array to form state
- Form is tabbed (one tab per settings group)
- On save: calls `SettingService::setBulk()` with all changed key-value pairs; clears cache

**Tab structure:**

| Tab | Fields |
|-----|--------|
| **Company** | Name (text), Tagline (text), Description (textarea), Logo (file upload → stores path to `settings`), About Text (textarea) |
| **Contact** | Phone Primary, Phone Secondary, Email, Address (textarea), WhatsApp Number |
| **Social** | Facebook URL, Instagram URL, Twitter URL, YouTube URL, TikTok URL, LinkedIn URL |
| **SEO** | Site Title, Meta Description, Meta Keywords, Google Analytics ID |
| **Appearance** | Favicon (file upload → stores path to `settings`), Primary Color (color picker), Secondary Color (color picker) |
| **System** | Maintenance Mode (toggle), App Locale (select: `en`, `ar`) |

**Logo and Favicon uploads:** Store the file path in `settings.value` for the respective key using `SettingService::set('company.logo', $path)`. Use Filament's `FileUpload` component with a custom `afterStateUpdated()` callback that calls `SettingService::set()`.

### 8.7 Dashboard Widgets

**`StatsOverviewWidget`:**
- Card 1: Total Cars (`Car::count()`)
- Card 2: Published Cars (`Car::published()->count()`)
- Card 3: Categories (`Category::count()`)
- Card 4: FAQs (`Faq::count()`)

**`LatestCarsWidget`:**
- Compact table of the last 10 cars added, ordered by `created_at DESC`
- Columns: Name, Category, Published toggle

---

## 9. REST API Implementation

> All 7 V1 endpoints are defined in `API_Contract.md`. This section explains their Laravel implementation only. Do not add endpoints, parameters, or behaviours not defined in that document.

### 9.1 Route Definitions (`routes/api.php`)

```php
Route::middleware(['throttle:api'])->group(function () {
    Route::get('/cars',              [CarController::class,      'index']);
    Route::get('/cars/{slug}',       [CarController::class,      'show']);
    Route::get('/categories',        [CategoryController::class, 'index']);
    Route::get('/faqs',              [FaqController::class,      'index']);
    Route::get('/banners',           [BannerController::class,   'index']);
    Route::get('/settings',          [SettingController::class,  'index']);
    Route::get('/settings/contact',  [SettingController::class,  'contact']);
});
```

**Notes:**
- No `/api/v1/` prefix — base path is `/api` (Laravel's default `api.php` prefix)
- No authentication middleware on any V1 route
- Rate limiting is `throttle:api` (60 req/min/IP, defined in `RouteServiceProvider`)

### 9.2 API Controllers

Each API controller is thin: validate → call service → return API Resource.

---

#### `Api\CarController`

**`index(CarListRequest $request)`**
- Delegates to `CarService::getAllPublished($request->validated())`
- Returns `CarListingResource::collection($cars)` in envelope format

**`show(string $slug)`**
- Delegates to `CarService::findBySlug($slug)` — returns 404 if not found
- Returns `CarDetailResource::make($car)` in envelope format

---

#### `Api\CategoryController`

**`index()`**
- Delegates to `CategoryService::getActive()`
- Returns `CategoryResource::collection($categories)` (no pagination)

---

#### `Api\FaqController`

**`index()`**
- Delegates to `FaqService::getActive()`
- Returns `FaqResource::collection($faqs)` (no pagination)

---

#### `Api\BannerController`

**`index()`**
- Delegates to `BannerService::getActive()`
- Returns `BannerResource::collection($banners)` (no pagination)

---

#### `Api\SettingController`

**`index()`**
- Delegates to `SettingService::all()`
- Returns `PublicSettingsResource::make($settings)` — excludes `system` group and `seo.google_analytics_id`

**`contact()`**
- Delegates to `SettingService::getGroup('contact')`
- Returns JSON `{ success: true, data: { ... contact fields ... } }`

### 9.3 API Resources

API Resources transform Eloquent models into the JSON structures defined by `API_Contract.md`. They are the **only** place where model fields are selected for public output.

---

#### `CarListingResource`

**Used by:** `GET /api/cars`

**Includes:** `id`, `name`, `slug`, `brand`, `model`, `year`, `color`, `is_featured`, `category` (embedded), `cover_image`

**Excludes:** `description`, `specifications`, `features`, `images` (full gallery), `meta_title`, `meta_description`, `price_*`, `currency`, `is_published`, `sort_order`, `updated_at`

**`cover_image` resolution:**
```php
'cover_image' => $this->whenNotNull(
    $this->getFirstMedia('car_images'),
    fn($media) => ['url' => $media->getUrl(), 'order' => $media->order_column]
),
```

---

#### `CarDetailResource`

**Used by:** `GET /api/cars/{slug}`

**Includes:** all fields from listing, plus `description`, `specifications`, `features` (array of strings), `images` (full array), `meta_title`, `meta_description`, `created_at`

**`features` resolution:** `$this->features->pluck('feature')->toArray()`

**`images` resolution:**
```php
'images' => $this->getMedia('car_images')->map(fn($media) => [
    'url'      => $media->getUrl(),
    'is_cover' => (bool) ($media->custom_properties['is_cover'] ?? false),
    'order'    => $media->order_column,
])->sortBy('order')->values()
```

**Excludes:** `price_daily`, `price_weekly`, `price_monthly`, `currency`, `is_published`, `sort_order`, `category_id`, `updated_at`

---

#### `CategoryResource`

**Includes:** `id`, `name`, `slug`, `description`, `icon`, `image_url`

**`image_url` resolution:** `$this->image ? Storage::url($this->image) : null`

**Excludes:** `is_active`, `sort_order`, `created_at`, `updated_at`

---

#### `FaqResource`

**Includes:** `id`, `category`, `question`, `answer`

**Excludes:** `is_active`, `sort_order`, `created_at`, `updated_at`

---

#### `BannerResource`

**Includes:** `id`, `title`, `subtitle`, `image_url`, `cta`

**`image_url`:** `$this->image ? Storage::url($this->image) : null`

**`cta`:** `($this->cta_text || $this->cta_url) ? ['text' => $this->cta_text, 'url' => $this->cta_url] : null`

**Excludes:** `is_active`, `sort_order`, `created_at`, `updated_at`

---

#### `PublicSettingsResource`

**Returns:** Nested grouped object (not a flat key-value list).

**Includes groups:** `company`, `contact`, `social`, `seo`, `appearance`

**Excludes:**
- `system` group entirely
- `seo.google_analytics_id`

**File path resolution for `logo_url` and `favicon_url`:**
```php
'logo_url'    => ($val = $settings['company']['logo'] ?? null) ? Storage::url($val) : null,
'favicon_url' => ($val = $settings['appearance']['favicon'] ?? null) ? Storage::url($val) : null,
```

### 9.4 Response Envelope

All API responses must be wrapped in the envelope format defined in `API_Contract.md` §6. Implement a base `ApiResponse` trait or helper method used by all API controllers:

```php
protected function success(mixed $data, int $status = 200): JsonResponse
{
    return response()->json(['success' => true, 'data' => $data], $status);
}

protected function error(string $code, string $message, int $status, array $details = []): JsonResponse
{
    $payload = ['success' => false, 'error' => ['code' => $code, 'message' => $message]];
    if ($details) $payload['error']['details'] = $details;
    return response()->json($payload, $status);
}
```

For collections with pagination, API Resources using `->collection()` with `->response()->getData()` extract the `data`, `meta`, and `links` keys automatically from Laravel's paginator. The envelope must then wrap these with `'success' => true`.

### 9.5 Eager Loading

All API queries must eager load relationships to avoid N+1 problems:

| Endpoint | Eager loads |
|----------|-------------|
| `GET /api/cars` | `category`, cover media via `getFirstMedia()` (Spatie handles this with a scoped query) |
| `GET /api/cars/{slug}` | `category`, `features`, `media` (full collection) |
| `GET /api/categories` | (none — no relationships) |
| `GET /api/faqs` | (none) |
| `GET /api/banners` | (none) |
| `GET /api/settings` | (none) |

---

## 10. Validation and Form Requests

### 10.1 `Api\CarListRequest`

**Used by:** `GET /api/cars`

**Rules:**

| Parameter | Rule | API Contract Requirement |
|-----------|------|--------------------------|
| `page` | `integer\|min:1` | ≥ 1 |
| `per_page` | `integer\|min:1\|max:100` | 1–100; >100 returns 422 |
| `category` | `string\|max:255\|exists:categories,slug,is_active,1` | Must be active category slug |
| `search` | `string\|max:100` | Max 100 chars |
| `featured` | `boolean` | `1` or `true` |

**422 error format:** The `failedValidation()` method must be overridden to return the envelope error format from `API_Contract.md` §6.5, not Laravel's default.

### 10.2 `Web\ContactFormRequest`

| Field | Rule |
|-------|------|
| `name` | `required\|string\|max:255` |
| `email` | `required\|email\|max:255` |
| `phone` | `nullable\|string\|max:50` |
| `message` | `required\|string\|max:5000` |

### 10.3 Filament Validation

Filament uses its own form component validation. Validation rules are defined inline in the Resource form components:

| Field | Key Rules |
|-------|-----------|
| Car Name | `required`, `max:255` |
| Car Slug | `required`, `unique:cars,slug` (with `ignoreRecord: true` on edit) |
| Car Category | `required`, `exists:categories,id` |
| Car Brand | `required`, `max:100` |
| Car Model | `required`, `max:100` |
| Car Year | `required`, `integer`, `min:1990`, `max:current_year+1` |
| Car Color | `nullable`, `max:100` |
| Car Description | `nullable` |
| Car Specifications | `nullable`, JSON (validated by Filament `KeyValue` component) |
| Car Features | Inline validation on `Repeater` child: `required`, `max:255` per feature |
| Car Images | Handled by `SpatieMediaLibraryFileUpload`; max file size 5 MB; MIME: jpg, png, webp |
| Car Prices | `nullable`, `numeric`, `min:0` |
| Car Sort Order | `nullable`, `integer`, `min:0` |
| Category Name | `required`, `max:255` |
| Category Slug | `required`, `unique:categories,slug` (ignore on edit) |
| Category Image | `nullable`, image MIME, max 5 MB |
| FAQ Question | `required` |
| FAQ Answer | `required` |
| Banner Image | `nullable`, image MIME, max 5 MB |
| Banner CTA URL | `nullable`, `url` |
| Settings values | Varies per field type; URL fields validated as `url\|nullable` |

---

## 11. Authentication and Authorization

### 11.1 Admin Model

The `Admin` model implements Filament's `FilamentUser` interface. The `canAccessPanel()` method returns `true` unconditionally in V1 (single admin, no RBAC).

### 11.2 Auth Guard Configuration (`config/auth.php`)

```php
'guards' => [
    'admin' => [
        'driver'   => 'session',
        'provider' => 'admins',
    ],
],
'providers' => [
    'admins' => [
        'driver' => 'eloquent',
        'model'  => App\Models\Admin::class,
    ],
],
```

The `admin` guard is completely isolated from the default `web` guard. Public users have no session guard in V1.

### 11.3 Filament Authentication Flow

1. Admin navigates to `/admin`
2. Filament's `PanelMiddleware` checks for valid `admin` guard session
3. No session → redirect to `/admin/login`
4. Admin submits email + password
5. Laravel authenticates via `Auth::guard('admin')->attempt(['email' => $email, 'password' => $password])`
6. On success: standard Laravel session cookie issued (HttpOnly, CSRF-protected)
7. All Filament requests use this session — no tokens, no Authorization headers
8. Logout: `Auth::guard('admin')->logout()` + session invalidation

**No Sanctum tokens are used for admin authentication in V1.**

### 11.4 Policies

Laravel Policies are registered for all V1 resources. In V1, all policies allow any authenticated admin to perform any action (simple gate check: `auth()->guard('admin')->check()`). Policy stubs exist to make V2 role-based access additive.

| Policy | Covers |
|--------|--------|
| `CarPolicy` | `create`, `update`, `delete`, `view`, `viewAny` |
| `CategoryPolicy` | same |
| `FaqPolicy` | same |
| `BannerPolicy` | same |
| `SettingPolicy` | `update` |

**Authorization boundary:** The public web routes and API routes have no authorization gates. They are open read-only routes.

### 11.5 CSRF Protection

Web routes (`routes/web.php`) use the `web` middleware group which includes `VerifyCsrfToken`. All Blade forms (contact, potentially booking) include `@csrf`. API routes (`routes/api.php`) are in the `api` middleware group which excludes CSRF (stateless).

---

## 12. Media and File Management

### 12.1 Storage Disk Configuration (`config/filesystems.php`)

The `public` disk is used for all uploaded files in V1:

```php
'public' => [
    'driver'     => 'local',
    'root'       => storage_path('app/public'),
    'url'        => env('APP_URL') . '/storage',
    'visibility' => 'public',
],
```

Public URL: `Storage::url($path)` resolves to `{APP_URL}/storage/{path}`.

Run `php artisan storage:link` to create the symlink from `public/storage` to `storage/app/public`.

**Cloud migration readiness:** Switching to S3 in V2 requires only changing `FILESYSTEM_DISK=s3` and adding S3 credentials to `.env`. No application code changes are needed because all file operations use the `Storage` facade.

### 12.2 Car Images via Spatie Media Library

**Registration on `Car` model:**
```php
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Car extends Model implements HasMedia
{
    use InteractsWithMedia;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('car_images')
             ->useDisk('public');
    }
}
```

**Collection name:** `car_images` (as defined in `Database_Design.md` §6.5)

**Multiple images:** Handled by Spatie natively — no limit configured in code, but Filament's `SpatieMediaLibraryFileUpload` component is configured with a reasonable maximum (20 files).

**Ordering:** Spatie stores `order_column` per media item. When images are reordered in the Filament component, Spatie updates `order_column` automatically.

**Cover image:** Stored in `custom_properties['is_cover']`. After each save, a hook sets `is_cover = true` on the first media item (lowest `order_column`) and `false` on all others.

**Cover image retrieval:**
```php
// In CarService or CarDetailResource:
$car->getFirstMedia('car_images');            // Spatie returns the media with order_column = 1
$media->getUrl();                             // Returns public URL
$media->custom_properties['is_cover'] ?? false;
```

**Deletion on car delete:** Spatie's `MediaObserver` (registered automatically by the package) calls `deleteAll()` on the car's media collection when the car is deleted. Physical files are removed from disk. No manual cleanup needed.

**Deleting a single image:** Filament's `SpatieMediaLibraryFileUpload` handles individual image removal through the UI. Spatie deletes the file and the `media` row.

### 12.3 Banner and Category Images

These use `MediaService::storeImage()` and store only a relative path string in the `banners.image` and `categories.image` columns respectively.

**Upload flow:**
1. Admin uploads file in Filament `FileUpload` component
2. Filament's `FileUpload` (with `->disk('public')->directory('banners')`) stores the file and returns the path
3. The path is saved to `banners.image` on model save

**Deletion flow:**
- `BannerObserver::deleted()` calls `Storage::delete($banner->image)` if path is set
- `CategoryObserver::deleted()` calls `Storage::delete($category->image)` if path is set

These observers are registered in `AppServiceProvider`.

### 12.4 Logo and Favicon

Stored as file paths in the `settings` table:
- `company.logo` → e.g., `company/logo-uuid.webp`
- `appearance.favicon` → e.g., `company/favicon.ico`

Uploaded through the `ManageSettings` Filament page via `FileUpload` component. On save, `SettingService::set('company.logo', $path)` stores the path.

When displayed publicly, `Storage::url($path)` resolves the URL. This is done in `PublicSettingsResource`.

---

## 13. Settings Management

### 13.1 Key-Value Storage Design

The `settings` table stores 25 fixed keys seeded at installation. Keys use dot notation: `group.subkey`. The `type` column tells the application how to cast the value when reading.

Full key list defined in `Database_Design.md` §6.8. Do not add keys that are not in that list.

### 13.2 `SettingService` Caching Behaviour

On first call to any `SettingService` read method:
1. Check Redis for key `settings:all`
2. If found: deserialize and return from cache
3. If not found: load all rows from `settings` table, group by `settings_group`, type-cast each `value`, store in Redis with TTL of 24 hours

On any write via `set()` or `setBulk()`:
1. Update the `settings` table (upsert by `key`)
2. Call `clearCache()` to delete `settings:all` from Redis
3. Next read will reload from the database

**Why cache all at once:** The settings table has exactly 25 rows. Loading all at once and caching is more efficient than individual key lookups. Every public page reads multiple settings; fetching them individually would mean 25 database queries per page without caching.

### 13.3 `ManageSettings` Page Behaviour

1. Page loads: `SettingService::all()` returns grouped array → form state populated per tab
2. Admin edits values in tabs
3. On Save: `SettingService::setBulk(['key' => 'value', ...])` for all changed settings
4. Logo/favicon uploads: `FileUpload` component writes path to form state; the save hook calls `SettingService::set('company.logo', $path)`

---

## 14. Caching

> Caching strategy is defined in `System_Architecture_Plan.md` §5.5. This section defines the implementation details.

**Cache driver:** Redis (configured via `REDIS_HOST`, `REDIS_PORT`, `REDIS_PASSWORD` in `.env`)

**Cache facade:** Use Laravel's `Cache::remember()`, `Cache::get()`, `Cache::put()`, `Cache::forget()`

### 14.1 Cached Data in V1

| Cache Key Pattern | Source | TTL | Invalidated By |
|-------------------|--------|-----|----------------|
| `cars:list:{hash}` | `CarService::getAllPublished()` | 1 hour | `ClearCarCache` listener |
| `cars:slug:{slug}` | `CarService::findBySlug()` | 1 hour | `ClearCarCache` listener |
| `cars:featured:{limit}` | `CarService::getFeatured()` | 1 hour | `ClearCarCache` listener |
| `categories:active` | `CategoryService::getActive()` | 6 hours | After any category save/delete |
| `faqs:active` | `FaqService::getActive()` | 6 hours | After any FAQ save/delete |
| `banners:active` | `BannerService::getActive()` | 6 hours | After any banner save/delete |
| `settings:all` | `SettingService` | 24 hours | After any settings save |

**Cache key for list queries:** Hash the filter array using `md5(json_encode($filters))` to generate a unique cache key per filter combination.

### 14.2 What is NOT Cached

- Admin panel data — Filament reads from DB directly; caching would complicate fresh-data requirements
- Single-request computed values — not worth caching
- V2 data that doesn't exist yet

### 14.3 Cache Invalidation Events

`CarSaved` and `CarDeleted` events are fired by Eloquent model observers (`CarObserver`). The `ClearCarCache` listener handles both events and calls `CarService::clearCache()` which deletes all `cars:*` keys using Redis pattern deletion.

---

## 15. Queues and Background Jobs

> Queue usage in V1 is minimal. The only async task is optional image processing.

**Queue connection:** `QUEUE_CONNECTION=redis` in production (sync acceptable in local development).

**Queue worker:** A single Supervisor-managed `php artisan queue:work` process.

### 15.1 V1 Jobs

| Job | When Fired | Why Async |
|-----|-----------|-----------|
| (Optional) Image thumbnail/conversion | After car image upload via Spatie | Image conversion can take 1–2 seconds per image; async prevents admin UI from blocking |

**Implementation note:** Spatie Media Library supports async conversions via queued jobs. If image resizing is needed (e.g., generating WebP thumbnails from uploaded JPEGs), configure `ShouldQueue` on Spatie's conversion registration. Otherwise, conversions run synchronously.

### 15.2 What is NOT queued in V1

- Email on contact form (small volume — sync send is acceptable)
- Cache clearing (synchronous is fine — it is a Redis DELETE)
- V2 booking notifications (do not create)

### 15.3 Failure Handling

Failed jobs are stored in the `failed_jobs` table (default Laravel configuration). `php artisan queue:retry all` retries failed jobs. Maximum attempts: 3 (configured in `php artisan queue:work --tries=3`).

---

## 16. Error Handling

### 16.1 API Error Responses

Override `app/Exceptions/Handler.php` to intercept API route exceptions and return the envelope format defined in `API_Contract.md` §6.4 and §11.

**Key overrides in `Handler.php`:**

| Exception | HTTP Status | Error Code |
|-----------|-------------|------------|
| `ModelNotFoundException` | 404 | `NOT_FOUND` |
| `ValidationException` | 422 | `VALIDATION_ERROR` |
| `ThrottleRequestsException` | 429 | `RATE_LIMIT_EXCEEDED` |
| `AuthenticationException` (on API routes) | 401 | `UNAUTHENTICATED` |
| All other `Throwable` | 500 | `SERVER_ERROR` |

The `renderForApi` check: use `$request->expectsJson()` or `$request->is('api/*')` to determine if the exception should be rendered as the envelope JSON or as a standard web error page.

**Production requirement:** `APP_DEBUG=false` must be set. Stack traces must never appear in API error responses.

### 16.2 Web Error Responses

Standard Laravel web error pages for:
- `404` — Car not found, page not found
- `500` — Unexpected server error
- `503` — Maintenance mode

Custom error pages are placed in `resources/views/errors/404.blade.php`, `500.blade.php`, etc.

### 16.3 Filament Errors

Filament handles its own errors within the admin panel with Livewire's error handling and built-in notifications. No custom override needed beyond the global `Handler.php`.

### 16.4 Category Delete Restriction

When an admin tries to delete a category that has associated cars, the delete action must be blocked before the `destroy()` call:

```php
DeleteAction::make()
    ->before(function (Category $record, Action $action) {
        if ($record->cars()->exists()) {
            Notification::make()
                ->danger()
                ->title('Cannot delete category')
                ->body('This category has cars assigned to it. Reassign or delete the cars first.')
                ->send();
            $action->cancel();
        }
    })
```

---

## 17. Logging and Monitoring

### 17.1 Log Driver Configuration

`LOG_CHANNEL=daily` in production (Laravel's `DailyLogger` rotates log files, keeping 14 days).

### 17.2 What is Logged

| Event | Log Level | Where |
|-------|-----------|-------|
| Admin login failure | `warning` | `Handler.php` or Auth event listener |
| `500` exceptions | `error` | Laravel default via `Handler.php` |
| Failed queue jobs | `error` | Laravel queue worker |
| File deletion failure (`Storage::delete` returns false) | `warning` | `BannerObserver`, `CategoryObserver` |
| Spatie media deletion failure | `error` | Logged by Spatie internally |
| Cache miss on first boot | `info` (debug only, not in production) | `SettingService` |

### 17.3 What is NOT Logged

- Normal CRUD operations (save, create, update) — excessive noise for a small marketing site
- Successful public API requests — handled by NGINX access log
- Every cache hit/miss — Redis itself can be monitored separately

### 17.4 Log Format

Use Laravel's default structured log format. JSON logging can be configured if log aggregation (e.g., CloudWatch, Papertrail) is used in production.

---

## 18. Security Implementation

> Full security architecture is defined in `System_Architecture_Plan.md` §15. This section explains how each requirement is implemented in Laravel.

| Security Requirement | Laravel Implementation |
|---------------------|----------------------|
| Admin authentication | Filament session-based auth; `admin` guard; `Hash::check()` + `bcrypt` |
| CSRF protection | `VerifyCsrfToken` middleware on all web routes; all Blade forms use `@csrf` |
| Mass assignment | `$fillable` defined on every model; `$guarded = ['*']` alternative not used |
| SQL injection | All queries use Eloquent/Query Builder with parameterized bindings; raw SQL prohibited |
| XSS in Blade | Blade `{{ }}` escapes by default; `{!! !!}` only used for trusted admin-sourced rich text |
| XSS in API | API Resources return raw data; no HTML rendered from API responses |
| File upload security | MIME type validation + size limits in `MediaService` and Filament `FileUpload`; UUID filenames; files stored outside `public/` root |
| Rate limiting on API | `throttle:api` middleware = 60 req/min/IP using Redis counters |
| HTTPS enforcement | `ForceHttps` middleware redirects all HTTP requests to HTTPS in production |
| Admin route protection | Filament's `PanelMiddleware` checks `admin` guard session on every request |
| Pricing field protection | `price_*` and `currency` excluded from all API Resources; never in any public JSON response |
| `system` settings protection | `PublicSettingsResource` explicitly excludes `system` group and `seo.google_analytics_id` |
| No admin API surface | No admin REST endpoints exist; no `/api/admin/*` routes registered |
| Error message safety | `APP_DEBUG=false` in production; `Handler.php` returns generic message for 500 errors |

---

## 19. Testing Strategy

### 19.1 Testing Framework

PHPUnit (Laravel default). Pest PHP is optional — either works. Tests are located in:
- `tests/Unit/` — unit tests (services in isolation)
- `tests/Feature/` — feature tests (HTTP requests, DB interactions)

### 19.2 Unit Tests

Unit tests cover service methods with meaningful business logic. Use mock models or in-memory arrays where possible.

| Test Class | Tests |
|-----------|-------|
| `CarServiceTest` | Published filter applied correctly; unpublished cars excluded; search LIKE matches name and brand; featured filter applied; cache hit returns cached data; cache cleared by `clearCache()` |
| `CategoryServiceTest` | Inactive categories excluded from `getActive()`; `findBySlug()` returns null for inactive slugs |
| `SettingServiceTest` | Type casting: boolean `'1'` → `true`, integer `'42'` → `42`, JSON string → array; `getGroup()` returns correct subset; `clearCache()` deletes Redis key |
| `MediaServiceTest` | `storeImage()` generates UUID filename; `resolveUrl()` returns `null` for null path |

### 19.3 Feature Tests

| Test Class | Tests |
|-----------|-------|
| **API** | |
| `CarListingApiTest` | `GET /api/cars` returns 200 with envelope; pagination works; category filter works; invalid category returns 422; `per_page > 100` returns 422; prices never in response |
| `CarDetailApiTest` | `GET /api/cars/{slug}` returns 200 with full car; unpublished slug returns 404; non-existent slug returns 404; prices never in response |
| `CategoryApiTest` | `GET /api/categories` returns only active categories; ordered by `sort_order` |
| `FaqApiTest` | `GET /api/faqs` returns only active FAQs; ordered by `sort_order` |
| `BannerApiTest` | `GET /api/banners` returns only active banners |
| `SettingsApiTest` | `GET /api/settings` excludes `system` group; excludes `google_analytics_id`; logo/favicon returned as URLs |
| `RateLimitApiTest` | 61st request returns 429 |
| **Web Routes** | |
| `HomePageTest` | `GET /` returns 200; banners and featured cars passed to view |
| `CarListingPageTest` | `GET /cars` returns 200; supports category filter; search filter |
| `CarDetailPageTest` | `GET /cars/{slug}` returns 200 for published car; returns 404 for unpublished |
| `BookingPageTest` | `GET /booking` returns 200 with car list and whatsapp number in view |
| `FaqPageTest` | `GET /faq` returns only active FAQs |
| **Authentication** | |
| `AdminAuthTest` | `/admin/login` accepts valid credentials; rejects invalid; session created; logout destroys session |
| **Filament** | |
| `CarResourceTest` | Authenticated admin can see car list; can create car; can edit car; delete triggers cache clear |
| `CategoryDeleteRestrictionTest` | Category with cars cannot be deleted; returns Filament error notification |

### 19.4 Database Tests

| Test | Assertion |
|------|-----------|
| `categories → cars` FK RESTRICT | Deleting a category with cars fails at DB level |
| `cars → car_features` FK CASCADE | Deleting a car removes all its features |
| `car_features` unique constraint | Inserting duplicate `(car_id, feature)` throws DB exception |
| `cars.slug` unique | Inserting duplicate slug throws DB exception |
| `settings.key` unique | Inserting duplicate key throws DB exception |

### 19.5 Media Tests

| Test | Assertion |
|------|-----------|
| `CarMediaUploadTest` | Uploading an image to car creates a `media` row in `car_images` collection |
| `CarMediaOrderTest` | Reordering images updates `order_column` |
| `CarMediaCoverTest` | First image has `is_cover = true`; others have `is_cover = false` |
| `CarMediaDeleteTest` | Deleting a car deletes all associated `media` rows and files |
| `BannerImageDeleteTest` | Deleting a banner deletes the associated image file from storage |

---

## 20. Deployment Preparation

### 20.1 Environment Variables

| Variable | Purpose |
|----------|---------|
| `APP_ENV=production` | Enables production mode |
| `APP_DEBUG=false` | Hides stack traces |
| `APP_URL` | Base URL for `Storage::url()` resolution |
| `DB_*` | MySQL connection credentials |
| `REDIS_*` | Redis connection credentials |
| `QUEUE_CONNECTION=redis` | Enable Redis queue |
| `FILESYSTEM_DISK=public` | Default storage disk |
| `ADMIN_EMAIL`, `ADMIN_PASSWORD` | Seeder reads these for admin account |
| `MAIL_*` | Optional SMTP credentials for contact form |

### 20.2 Production Checklist

- [ ] `php artisan config:cache`
- [ ] `php artisan route:cache`
- [ ] `php artisan view:cache`
- [ ] `php artisan storage:link`
- [ ] `php artisan migrate --force`
- [ ] `php artisan db:seed --class=SettingSeeder`
- [ ] `php artisan db:seed --class=AdminSeeder`
- [ ] `php artisan db:seed --class=CategorySeeder`
- [ ] Supervisor configured for queue worker
- [ ] NGINX configured with `ForceHttps`, `/api/*` → PHP-FPM, `/admin/*` → PHP-FPM, `/*` → PHP-FPM (Blade)
- [ ] `APP_DEBUG=false` verified
- [ ] Redis connection tested
- [ ] `storage/app/public` symlink verified (`public/storage` exists)

---

## 21. Implementation Sequence

The following 9-phase sequence respects dependencies and delivers a working system incrementally.

### Phase 1 — Foundation (Days 1–2)

**Goal:** Runnable Laravel project with working database, auth, and Filament panel.

Tasks:
1. Configure `.env` (DB, Redis, APP_URL, Filament)
2. Publish Spatie Media Library migration: `php artisan vendor:publish --tag="medialibrary-migrations"`
3. Write and run all 8 migrations in order (§5.1)
4. Write and run seeders: `AdminSeeder`, `SettingSeeder`, `CategorySeeder`
5. Configure `config/auth.php` with `admin` guard
6. Configure `AdminPanelProvider` (Filament panel ID, path, model, guard)
7. Verify `/admin/login` works and admin can log in

**Dependency:** Everything else depends on this phase.

### Phase 2 — Eloquent Models (Days 3–4)

**Goal:** All models configured with correct casts, relationships, scopes, and sluggable.

Tasks:
1. Install `spatie/laravel-sluggable` if not already present
2. Create `Admin`, `Category`, `Car`, `CarFeature`, `Faq`, `Banner`, `Setting` models
3. Configure all `$fillable`, `$casts`, relationships, and scopes per §5.2
4. Register `HasSlug` on `Car` and `Category`
5. Register `HasMedia` and `InteractsWithMedia` on `Car` with `car_images` collection
6. Register `BannerObserver`, `CategoryObserver` in `AppServiceProvider`
7. Write model unit tests

### Phase 3 — Service Layer (Days 5–6)

**Goal:** All 6 services implemented and tested.

Tasks:
1. `SettingService` — type casting, grouping, cache (critical dependency for all other pages)
2. `CategoryService` — `getActive()`, `findBySlug()`
3. `FaqService` — `getActive()`
4. `BannerService` — `getActive()`
5. `MediaService` — `storeImage()`, `deleteImage()`, `resolveUrl()`
6. `CarService` — `getAllPublished()`, `findBySlug()`, `getFeatured()`, `clearCache()`
7. Register `CarObserver` → `CarSaved`, `CarDeleted` events → `ClearCarCache` listener
8. Write service unit tests

### Phase 4 — Filament Resources (Days 7–10)

**Goal:** All admin CRUD operations working through Filament.

Tasks:
1. `CategoryResource` (simpler — no media, no relationships)
2. `FaqResource`
3. `BannerResource` (with image upload)
4. `CarResource` (most complex — images, features, specs, pricing)
5. `ManageSettings` custom page
6. `StatsOverviewWidget`, `LatestCarsWidget`
7. Test all resources through the admin panel UI

**Why Filament before public web:** The admin creates the content that the public site displays. It is easier to seed real content through Filament than to write API tests against an empty database.

### Phase 5 — Media Handling Verification (Day 11)

**Goal:** Car image upload, ordering, cover selection, and deletion all work correctly end-to-end.

Tasks:
1. Upload multiple images through `CarResource` and verify `media` table rows created
2. Reorder images and verify `order_column` updated
3. Verify cover image `custom_properties['is_cover'] = true` on first image
4. Delete a car and verify all media rows and files removed
5. Write media feature tests

### Phase 6 — REST API (Days 12–14)

**Goal:** All 7 API endpoints return correct responses matching `API_Contract.md`.

Tasks:
1. Create all 5 API controllers
2. Create all 5 API Resource classes
3. Register routes in `routes/api.php` with `throttle:api`
4. Implement `CarListRequest` validation
5. Implement `Handler.php` API error format
6. Write all API feature tests, including:
   - Prices never in response
   - `system` settings group never in response
   - `404` for unpublished cars
   - `422` for invalid filters

### Phase 7 — Public Web Backend Integration (Days 15–17)

**Goal:** All Blade pages served with correct data from service layer.

Tasks:
1. Create all 6 public web controllers
2. Register web routes in `routes/web.php`
3. Register View Composer for shared layout data (settings, company name, social links)
4. Create `ContactFormRequest`
5. Write web feature tests for all routes

### Phase 8 — Testing & Security Hardening (Days 18–20)

**Goal:** Full test coverage. All security requirements verified.

Tasks:
1. Complete all remaining unit and feature tests
2. Verify `APP_DEBUG=false` hides traces from API responses
3. Verify prices are excluded from all API responses
4. Verify `system` settings excluded from API
5. Verify rate limiting works on API routes
6. Verify CSRF protection on web routes
7. Verify category delete restriction
8. Run `php artisan route:list` to confirm no unintended routes exist
9. Run static analysis (PHPStan level 5 minimum) if time permits

### Phase 9 — Deployment Preparation (Days 21–22)

**Goal:** Production-ready deployment.

Tasks:
1. Configure NGINX
2. Configure Supervisor for queue worker
3. Set up production `.env`
4. Run production checklist from §20.2
5. Verify `storage:link` works
6. Test admin login in production
7. Test one API endpoint from external client

---

## 22. Future Extension Considerations

> This section explains V2 extension points only. No V2 code is created.

| V2 Feature | Extension Mechanism |
|-----------|---------------------|
| Customer accounts | New `customers` table + new `customer` guard in `config/auth.php` + new `CustomerAuthController` + Sanctum tokens for customers only |
| Server-side bookings | New `bookings` table (FK to `cars.id`, `customers.id`) + new `BookingService` + new Filament `BookingResource` + new API endpoints under `/api/v2/` |
| Payments | New `payments` table + `PaymentGatewayInterface` + gateway implementations |
| Branches | New `branches` table + additive nullable `branch_id` column on `cars` + filter support in `CarService` |
| Fleet management | Additive columns on `cars`: `availability_status`, `license_plate`, `vin` |
| API versioning | Introduce `/api/v2/` prefix group in `routes/api.php` when breaking changes are needed; V1 routes remain active |
| Image conversions | Add `registerMediaConversions()` to `Car` model with WebP conversions; queue the jobs |
| RBAC | Add `role` column to `admins` via additive migration; update Policies to check roles |
| Notifications | New Laravel `Notification` classes + existing queue worker handles async delivery |
| Multi-language | Add JSON columns or translation tables; existing `system.app_locale` setting already seeded |

**V1 code that is safe to extend without modification:**
- `SettingService` — adding new setting keys requires only a new seeder, no code change
- `Car` model `registerMediaCollections()` — adding a new collection (e.g., `car_videos`) does not change the existing `car_images` collection
- `CarService::getAllPublished()` — adding V2 filters is additive

---

## 23. Final Backend Checklist

Before declaring V1 backend complete, verify all items:

### Architecture
- [ ] No Repository classes exist
- [ ] No Domain Entity classes exist
- [ ] Controllers are thin — no business logic, no Eloquent queries directly
- [ ] Services contain all business logic
- [ ] Models contain only relationships, scopes, casts, accessors

### Database
- [ ] Exactly 8 tables: `admins`, `categories`, `cars`, `car_features`, `media`, `faqs`, `banners`, `settings`
- [ ] `car_images` table does NOT exist
- [ ] No V2 placeholder tables or columns
- [ ] `cars.category_id` FK uses `ON DELETE RESTRICT`
- [ ] `car_features.car_id` FK uses `ON DELETE CASCADE`
- [ ] Composite unique constraint on `(car_id, feature)` exists
- [ ] `media` table created from Spatie migration, not hand-written

### API
- [ ] Exactly 7 API endpoints (all GET, no auth)
- [ ] Base path is `/api` not `/api/v1`
- [ ] No admin REST endpoints
- [ ] No booking, payment, or customer auth endpoints
- [ ] `price_daily`, `price_weekly`, `price_monthly`, `currency` never in any API response
- [ ] `is_published` never in any API response
- [ ] `system` settings group never in any API response
- [ ] `seo.google_analytics_id` never in any API response
- [ ] Unpublished car returns 404 (not 403)
- [ ] Rate limiting applied to all `/api/*` routes

### Filament Admin
- [ ] Admin uses session authentication (not Sanctum tokens)
- [ ] Car image upload uses `SpatieMediaLibraryFileUpload` in `car_images` collection
- [ ] Category delete is blocked if cars exist
- [ ] `ManageSettings` page covers all 6 approved groups
- [ ] No custom admin HTML pages, no separate admin JavaScript

### Media
- [ ] Only car images use Spatie Media Library
- [ ] Banners and categories use plain `VARCHAR` path column
- [ ] Logo and favicon stored as paths in `settings` table
- [ ] Banner image deleted from storage on banner delete
- [ ] Car media auto-deleted by Spatie on car delete

### Security
- [ ] `APP_DEBUG=false` in production
- [ ] Prices excluded from API — verified by tests
- [ ] No admin API routes — verified by `php artisan route:list`
- [ ] CSRF protection on all web routes
- [ ] File uploads: MIME validation, UUID filenames, max 5 MB

### Testing
- [ ] Unit tests for all service classes
- [ ] Feature tests for all 7 API endpoints
- [ ] Feature tests for all 6 web routes
- [ ] Database tests for FK behavior and unique constraints
- [ ] Media tests for upload, ordering, cover, deletion

---

**Document Metadata:**

| Field | Value |
|-------|-------|
| Document Name | Backend_Development_Plan.md |
| Version | 1.0.0 |
| Created | August 2026 |
| Sources | System_Architecture_Plan.md v1.4.0, Database_Design.md v1.1.0, API_Contract.md v1.1.0 |
| Status | Ready for Implementation |
| Phase Count | 9 phases, ~22 working days |
| Next Document | Frontend_Development_Plan.md |
