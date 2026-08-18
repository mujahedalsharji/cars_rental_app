# Frontend Development Plan — Car Rental Website (Version 1)

---

> **Authoritative Sources:** This document implements the frontend decisions defined by:
> - `System_Architecture_Plan.md` (v1.6.0)
> - `Database_Design.md` (v1.2.0)
> - `API_Contract.md` (v1.2.0)
> - `Backend_Development_Plan.md` (v1.1.0)
>
> This document defines the frontend presentation layer only.
> It does not redefine architecture, database design, API contracts, or backend implementation.
> When data fields or page routes are referenced, the source document governs their exact definition.

---

## 1. Purpose and Scope

This document defines the complete Version 1 frontend implementation plan for the Car Rental Website.

**What this document covers:**
- Blade view architecture and component strategy
- CSS organization and naming conventions
- JavaScript module responsibilities
- RTL and Arabic support
- Per-page data consumption from backend-provided variables
- SEO metadata implementation in Blade
- Responsive design approach
- Image and media rendering
- WhatsApp inquiry flow (client-side)
- Accessibility requirements
- Performance strategy
- Testing strategy
- Implementation sequence (coordinated with `Backend_Development_Plan.md`)

**What this document does NOT cover:**
- Backend controllers, services, or Eloquent (→ `Backend_Development_Plan.md`)
- Database schema (→ `Database_Design.md`)
- API endpoint contracts (→ `API_Contract.md`)
- System architecture (→ `System_Architecture_Plan.md`)
- Filament admin implementation (→ `Backend_Development_Plan.md` §8)
- V2 features of any kind

**V1 constraint:** This is a marketing website. Every frontend decision must serve the goal of presenting the company and its cars professionally — not building a web application.

---

## 2. Frontend Architecture

### 2.1 The Architecture in One Paragraph

Every public page is a server-rendered Blade view. A browser request hits a Laravel web route, a controller calls services, and the controller returns a Blade view with all data pre-populated on the server. The browser receives complete HTML. There is no client-side routing, no SPA shell, and no JavaScript framework. The REST API defined in `API_Contract.md` is not consumed by Blade pages — it exists for future external consumers.

### 2.2 Request-to-HTML Flow

```
Browser request
      ↓
NGINX (serves PHP-FPM)
      ↓
routes/web.php
      ↓
Public Controller (thin)
      ↓
Service Layer (data, caching)
      ↓
Eloquent / MySQL
      ↓
Controller returns view($template, $data)
      ↓
Blade renders complete HTML
      ↓
Browser receives full page
```

The browser receives finished HTML. JavaScript then adds progressive enhancements (navigation toggle, accordion, gallery) — it does not render the page.

### 2.3 What JavaScript Is and Is Not

| JavaScript IS used for | JavaScript IS NOT used for |
|------------------------|---------------------------|
| Mobile navigation toggle | Rendering page content |
| FAQ accordion | Client-side routing |
| Car image gallery / lightbox | Fetching car data from the API |
| Banner/hero carousel | State management |
| WhatsApp message generation and redirect | Replacing Blade templates |
| Client-side convenience form validation | Building a SPA |
| Smooth scroll / minor UI effects | Global data stores |

### 2.4 API Boundary

The REST API (`/api/*`) is not consumed by Blade pages. The backend provides all page data through controller variables.

```
Public Blade Website
        │
        └── Laravel Controllers → Services → Eloquent
                (data served as Blade variables)


REST API (/api/*)
        │
        └── Future: Flutter app, mobile consumer, external integrations
```

No `fetch()`, `axios`, or `XMLHttpRequest` calls are made to `/api/*` from any Blade page during normal page rendering.

---

## 3. Technology Stack

| Layer | Technology | Justification |
|-------|-----------|---------------|
| Templating | Laravel Blade | Approved in `System_Architecture_Plan.md`; native PHP server rendering; SEO-optimal |
| CSS | Tailwind CSS v4 | Already installed; mobile-first utilities, CSS-first theme tokens, and strong RTL support |
| JavaScript | Vanilla JavaScript (ES2020+) | Progressive enhancement only; no framework needed for MPA interactions |
| Asset pipeline | Laravel Vite | Laravel-native; handles CSS/JS bundling, cache-busting, HMR in dev |
| Icons | SVG inline or CSS icon font | To be confirmed by UI/UX designer; SVG preferred for accessibility |
| Fonts | Google Fonts or self-hosted | Arabic-compatible font required (see §11); choice confirmed by designer |

**Technologies explicitly NOT used:**
- React, Vue, Angular, Next.js, Nuxt — no SPA framework
- Bootstrap, Material UI, and other competing CSS frameworks — Tailwind CSS v4 is the single styling system
- Alpine.js, HTMX — not needed for the interaction scope of this project
- Redux, Pinia, Zustand — no state management library
- webpack (standalone) — Vite is the asset pipeline
- npm package added speculatively — every dependency must have a concrete V1 justification

**Third-party JavaScript packages policy:** Before adding any npm package, verify that native browser APIs cannot solve the problem. For any package that is added:
- Document what it does
- Document why native browser APIs are insufficient
- Document its minified bundle size impact
- Confirm it is MPA-compatible (no module bundler assumptions)

---

## 4. Laravel Blade MPA Structure

### 4.1 Views Directory

The complete `resources/views/` structure for V1:

```
resources/views/
│
├── layouts/
│   └── app.blade.php             # Master layout: <html>, <head>, <body>, header, footer
│
├── components/
│   ├── seo-head.blade.php        # <title>, meta, OG, canonical slot
│   ├── car-card.blade.php        # Single car card (used in listing and homepage featured)
│   ├── car-gallery.blade.php     # Image gallery component for car detail page
│   ├── car-specs.blade.php       # Specifications table component
│   ├── feature-list.blade.php    # Car features list component
│   ├── faq-item.blade.php        # Single FAQ accordion item
│   ├── breadcrumbs.blade.php     # Breadcrumb trail component
│   ├── pagination.blade.php      # Pagination links (overrides Laravel default)
│   ├── cta-whatsapp.blade.php    # WhatsApp call-to-action button
│   ├── social-links.blade.php    # Social media icon links
│   └── alert.blade.php           # Success/error/info alert message
│
├── partials/
│   ├── header.blade.php          # Site header with logo and navigation
│   ├── nav.blade.php             # Main navigation links
│   ├── mobile-nav.blade.php      # Mobile drawer navigation
│   └── footer.blade.php          # Site footer
│
├── pages/
│   ├── home.blade.php            # Home page
│   ├── cars/
│   │   ├── index.blade.php       # Car listing page
│   │   └── show.blade.php        # Car detail page
│   ├── about.blade.php           # About page
│   ├── faq.blade.php             # FAQ page
│   ├── contact.blade.php         # Contact page
│   └── booking.blade.php         # Pre-booking WhatsApp inquiry form
│
└── errors/
    ├── 404.blade.php             # Custom 404 page
    ├── 500.blade.php             # Custom 500 page
    └── 503.blade.php             # Maintenance mode page
```

### 4.2 Vite Asset Structure

```
resources/
│
├── css/
│   └── app.css                   # Tailwind import, @theme tokens, base rules, and rare custom CSS
│
├── js/
│   ├── app.js                    # Main JS entry point (imports all modules)
│   └── modules/
│       ├── navigation.js         # Mobile navigation toggle
│       ├── gallery.js            # Car image gallery / lightbox
│       ├── faq.js                # FAQ accordion
│       ├── carousel.js           # Hero banner carousel (if CSS-only is insufficient)
│       ├── whatsapp.js           # WhatsApp message generation and redirect
│       └── filters.js            # Car listing filter interactions (URL param sync)
│
└── images/
    ├── logo.svg                  # Site logo (fallback; real logo managed via settings)
    ├── placeholder-car.svg       # Car image fallback
    └── placeholder-banner.svg    # Banner image fallback
```

### 4.3 Vite Configuration (`vite.config.js`)

The Vite configuration uses Laravel's official `laravel-vite-plugin` and `@tailwindcss/vite`:

```
Input files:
  - resources/css/app.css
  - resources/js/app.js

Public directory: public/build/

Development: Hot Module Replacement (HMR) via `npm run dev`
Production:  `npm run build` — outputs hashed filenames for cache-busting

Blade usage: @vite(['resources/css/app.css', 'resources/js/app.js'])
```

**No per-page bundles:** V1 has one Tailwind CSS entry point and one JavaScript entry point. Tailwind scans Blade and JavaScript sources and emits only the utilities used by the application. Page-specific behavior is initialized by JavaScript modules only when their target element exists.

---

## 5. Routes and Pages

Routes are defined in `Backend_Development_Plan.md` §7.1. This section documents the frontend responsibilities of each route.

| Route | Named Route | Controller Method | View File | Purpose |
|-------|-------------|------------------|-----------|---------|
| `GET /` | `home` | `HomeController@index` | `pages/home` | Company intro, hero, featured cars, CTAs |
| `GET /cars` | `cars.index` | `CarController@index` | `pages/cars/index` | Browse and filter car catalogue |
| `GET /cars/{slug}` | `cars.show` | `CarController@show` | `pages/cars/show` | Full car information page |
| `GET /about` | `about` | `AboutController@index` | `pages/about` | Company story and information |
| `GET /faq` | `faq.index` | `FaqController@index` | `pages/faq` | FAQ accordion page |
| `GET /contact` | `contact` | `ContactController@index` | `pages/contact` | Contact details and form |
| `POST /contact` | `contact.submit` | `ContactController@submit` | (redirects) | Optional contact form submission |
| `GET /booking` | `booking` | `BookingController@show` | `pages/booking` | WhatsApp inquiry form |

**URL conventions:**
- Slug-based car URLs: `/cars/bmw-5-series` — human-readable and SEO-friendly
- No numeric IDs in public URLs
- All URLs are lowercase, hyphen-separated
- Query parameters used only for filtering: `/cars?category=suv&page=2`

---

## 6. Blade Layouts, Components, and Partials

### 6.1 Master Layout (`layouts/app.blade.php`)

The master layout wraps every public page. It:
- Sets `<html lang="ar" dir="rtl">` (Arabic/RTL, see §11)
- Includes `<head>` with the `@component('components.seo-head')` slot
- Loads Vite assets: `@vite(['resources/css/app.css', 'resources/js/app.js'])`
- Includes `partials/header`
- Renders `@yield('content')` for page body
- Includes `partials/footer`
- Contains a `@stack('scripts')` for any page-specific inline JS

**Slots/sections defined by the layout:**
- `@section('title')` — page title (used by `seo-head` component)
- `@section('meta_description')` — meta description
- `@section('og_image')` — optional OG image URL override
- `@section('canonical')` — canonical URL override
- `@yield('content')` — main page body
- `@stack('scripts')` — page-specific JavaScript (used sparingly)
- `@stack('structured_data')` — page-specific JSON-LD blocks

### 6.2 Components

Components are created only where a UI element is genuinely repeated across pages or where isolating it improves maintainability. Blade anonymous components (in `resources/views/components/`) are used.

---

#### `components/seo-head.blade.php`

Used inside `<head>`. Accepts:
- `$title` — page-specific title; defaults to `settings.seo.site_title`
- `$description` — meta description; defaults to `settings.seo.meta_description`
- `$canonical` — canonical URL; defaults to `request()->url()`
- `$ogImage` — Open Graph image URL; optional
- `$ogType` — Open Graph type; defaults to `website`

Renders: `<title>`, `<meta name="description">`, `<link rel="canonical">`, Open Graph tags, `<meta name="robots">`, language/locale attributes.

---

#### `components/car-card.blade.php`

Used on: Home (featured cars), Cars listing page.

**Accepts:** `$car` — a `Car` model instance

**Renders:**
- Cover image (`$car->getFirstMedia('car_images')->getUrl()` or `$car->getFirstMedia()`) with `loading="lazy"` and fallback
- Car name as heading
- Category badge
- Brand and year
- Top 3 features (if present)
- "View Details" link → `route('cars.show', $car->slug)`
- WhatsApp inquiry shortcut → `route('booking', ['car' => $car->slug])`

**Must NOT display:** pricing fields (`price_daily`, `price_weekly`, `price_monthly`), internal status fields, database ID in links

---

#### `components/car-gallery.blade.php`

Used on: Car detail page.

**Accepts:** `$images` — collection/array of media items (from `$car->getMedia('car_images')`)

**Renders:**
- Main large image (cover image first — `is_cover: true`)
- Thumbnail strip for additional images
- Clicking a thumbnail updates the main image (JavaScript in `gallery.js`)
- Optional lightbox on main image click (JavaScript in `gallery.js`)
- If no images: renders `placeholder-car.svg` fallback

**Image attributes:**
- `loading="eager"` on cover (above-the-fold)
- `loading="lazy"` on all other gallery images
- `width` and `height` attributes for layout stability (prevent CLS)
- Descriptive `alt` text: `{{ $car->name }} - صورة {{ $loop->iteration }}`

---

#### `components/car-specs.blade.php`

Used on: Car detail page.

**Accepts:** `$specifications` — associative array from `$car->specifications` (JSON cast)

**Renders:**
- Definition list `<dl>` or `<table>` of specification key-value pairs
- Only renders if `$specifications` is not null and not empty
- Keys are displayed as-is (admin-entered); no hardcoded spec names
- Handles gracefully if individual keys are missing

---

#### `components/feature-list.blade.php`

Used on: Car detail page, optionally on car card.

**Accepts:** `$features` — collection of `CarFeature` models (plucked to strings by controller)

**Renders:**
- Unordered list `<ul>` of feature labels
- Each feature displayed with an icon or bullet
- Empty state: renders nothing (component is optional)

---

#### `components/faq-item.blade.php`

Used on: FAQ page, Home page (preview section).

**Accepts:** `$faq` — a `Faq` model instance

**Renders:**
- `<details>` / `<summary>` HTML elements (native browser accordion — no JavaScript required for basic functionality)
- Question in `<summary>`, answer in the `<details>` body
- JavaScript in `faq.js` adds animation and aria states for enhancement

**Why `<details>/<summary>`:** Native HTML accordion with zero JavaScript. Keyboard-accessible and screen-reader-compatible by default. `faq.js` only enhances the open/close animation.

---

#### `components/breadcrumbs.blade.php`

Used on: Car detail page, FAQ page.

**Accepts:** `$crumbs` — array of `['label' => string, 'url' => string|null]`

**Renders:**
- `<nav aria-label="مسار التنقل">` wrapper
- Ordered list `<ol>` with `aria-current="page"` on the last item
- JSON-LD `BreadcrumbList` structured data via `@push('structured_data')`

---

#### `components/pagination.blade.php`

Used on: Car listing page.

**Accepts:** `$paginator` — Laravel `LengthAwarePaginator` instance

**Renders:**
- Previous / Next links
- Page number links
- Current page indicator
- Accessible `<nav aria-label="ترقيم الصفحات">` wrapper
- All links are `<a>` elements (not buttons) — crawlable by search engines

This overrides Laravel's default pagination view for custom styling.

---

#### `components/cta-whatsapp.blade.php`

Used on: Car detail page, Home page, Contact page.

**Accepts:** `$whatsappNumber`, `$message` (optional pre-filled message), `$label` (optional button label)

**Renders:**
- An `<a>` tag linking to `https://wa.me/{$whatsappNumber}` (or with message if provided)
- Target `_blank` with `rel="noopener noreferrer"`
- WhatsApp icon
- Accessible label

---

#### `components/social-links.blade.php`

Used on: Footer, Contact page.

**Accepts:** `$social` — group array from `SettingService` (injected via View Composer into layout)

**Renders:**
- Icon links for each non-null social URL
- Each link: `<a href="..." target="_blank" rel="noopener noreferrer" aria-label="...">` with SVG icon
- Only renders links where the URL setting is not null

---

#### `components/alert.blade.php`

Used on: Contact page (after form submission), any page with flash messages.

**Accepts:** `$type` (`success|error|info`), `$message`

**Renders:** Styled alert box with ARIA `role="alert"`

### 6.3 Partials

---

#### `partials/header.blade.php`

Renders:
- `<header>` semantic element
- Logo (from `settings.company.logo` or fallback SVG) — links to `route('home')`
- Includes `partials/nav`
- Mobile hamburger button (toggles navigation via `navigation.js`)
- WhatsApp quick-contact icon in header

---

#### `partials/nav.blade.php`

Renders:
- `<nav>` semantic element with `aria-label="القائمة الرئيسية"`
- Navigation links with `aria-current="page"` on the active page
- Navigation items: الرئيسية (Home), السيارات (Cars), عن الشركة (About), الأسئلة الشائعة (FAQ), تواصل معنا (Contact), احجز الآن (Booking/CTA)
- Active state determined by comparing current route name with link route

---

#### `partials/mobile-nav.blade.php`

Renders:
- Off-canvas or dropdown navigation for mobile screens
- Same links as `partials/nav`
- Managed by `navigation.js` (toggle open/close)
- `aria-expanded` attribute toggled by JavaScript
- Trap focus inside when open (keyboard accessibility)

---

#### `partials/footer.blade.php`

Renders:
- `<footer>` semantic element
- Company name and tagline (from settings via View Composer)
- Quick navigation links
- Contact information (phone, WhatsApp) from settings
- `components/social-links`
- Copyright line
- Link to `/sitemap.xml`

### 6.4 Component Naming Convention

- Blade component files: `kebab-case.blade.php`
- Components expose an optional `class` attribute so callers can merge Tailwind utilities cleanly
- Use `data-page="cars"` on `<body>` only when JavaScript needs page identification
- Use semantic `data-state`, `aria-expanded`, `aria-current`, and native element state before inventing custom state classes

---

## 7. Data Flow and Backend Integration

> The backend controller responsibilities are defined in `Backend_Development_Plan.md` §7. This section documents what each Blade view receives and how it uses those variables.

### 7.1 Blade Views Must NOT:
- Call Eloquent models directly
- Call services directly
- Contain business logic
- Contain database queries
- Access `$_GET`, `$_POST` directly (use Blade variables from controller)

### 7.2 Per-Page Data Contract

---

#### Home Page (`pages/home.blade.php`)

**Variables received:**

| Variable | Type | Source | Usage in View |
|----------|------|--------|---------------|
| `$banners` | Collection of `Banner` | `BannerService::getActive()` | Hero slider; rendered via carousel or static layout |
| `$featuredCars` | Collection of `Car` | `CarService::getFeatured(8)` | Featured cars grid, each using `components/car-card` |
| `$faqs` | Collection of `Faq` | First 5 of `FaqService::getActive()` | FAQ preview section using `components/faq-item` |
| `$settings` | Grouped settings array | View Composer (layout) | Company name, tagline, logo in header and content |

**SEO values set by this page:**
- Title: `settings.seo.site_title` (default)
- Description: `settings.seo.meta_description` (default)
- OG type: `website`

---

#### Car Listing (`pages/cars/index.blade.php`)

**Variables received:**

| Variable | Type | Source | Usage in View |
|----------|------|--------|---------------|
| `$cars` | `LengthAwarePaginator` of `Car` | `CarService::getAllPublished()` | Car grid using `components/car-card`; pagination using `components/pagination` |
| `$categories` | Collection of `Category` | `CategoryService::getActive()` | Category filter bar (links with `?category=slug`) |

**URL state:** The active category filter is reflected in the URL (`?category=suv`). The view reads `request()->get('category')` to apply the `is-active` class to the correct filter link. The active search term is echoed in the search input via `request()->get('search')`.

**SEO values:**
- Title: `السيارات المتاحة — {settings.seo.site_title}`
- Description: Derived from settings default
- Canonical: `/cars` (without pagination query params — canonical points to page 1)

---

#### Car Detail (`pages/cars/show.blade.php`)

**Variables received:**

| Variable | Type | Source | Usage in View |
|----------|------|--------|---------------|
| `$car` | `Car` model | `CarService::findBySlug()` | All car information |
| `$whatsappNumber` | `string\|null` | `SettingService::get('contact.whatsapp_number')` | WhatsApp CTA button |

**Data accessed from `$car`:**
- `$car->name`, `$car->brand`, `$car->model`, `$car->year`, `$car->color`
- `$car->description` — rich text from admin; use `{!! !!}` (admin-sourced, trusted)
- `$car->specifications` — JSON cast to array; rendered via `components/car-specs`
- `$car->features` — relationship; rendered via `components/feature-list`
- `$car->getMedia('car_images')` — collection; rendered via `components/car-gallery`
- `$car->category->name`, `$car->category->slug` — for breadcrumbs and badge
- `$car->meta_title`, `$car->meta_description` — SEO override fields

**Must NOT access:** `$car->price_daily`, `$car->price_weekly`, `$car->price_monthly`, `$car->currency`, `$car->is_published`, `$car->sort_order`

**SEO values:**
- Title: `$car->meta_title ?? ($car->name . ' — ' . settings.seo.site_title)`
- Description: `$car->meta_description ?? Str::limit($car->description, 160)`
- OG image: first car image URL
- Canonical: `route('cars.show', $car->slug)`

---

#### FAQ Page (`pages/faq.blade.php`)

**Variables received:**

| Variable | Type | Source | Usage in View |
|----------|------|--------|---------------|
| `$faqs` | Collection of `Faq` | `FaqService::getActive()` | FAQ list; optionally grouped by `$faq->category` string; each using `components/faq-item` |

**Grouping strategy:** If multiple FAQs share the same `category` string value, display them under a group heading. If `category` is null, display under a general section. This grouping happens in the Blade template using `$faqs->groupBy('category')` — no service-layer change needed.

**SEO values:**
- Title: `الأسئلة الشائعة — {settings.seo.site_title}`
- JSON-LD: `FAQPage` structured data (see §14)

---

#### About Page (`pages/about.blade.php`)

**Variables received:** Settings from View Composer (company name, description, logo, about text).

**Renders:** Company story using `settings.company.about_text` and `settings.company.description`. Contact CTA. Social links.

---

#### Contact Page (`pages/contact.blade.php`)

**Variables received:** Settings from View Composer (phone, email, address, WhatsApp number, social links).

**Renders:**
- Contact information (phone links `tel:`, WhatsApp link `wa.me/`, email `mailto:`)
- Optional contact form (Name, Email, Phone, Message)
- Social media links
- Map embed (optional — only if approved by design; use an `<iframe>` for a Google Maps embed)

**Contact form:** POST to `route('contact.submit')` with CSRF. Server validates via `ContactFormRequest`. On success, session flash message displayed via `components/alert`.

---

#### Booking Page (`pages/booking.blade.php`)

**Variables received:**

| Variable | Type | Source | Usage in View |
|----------|------|--------|---------------|
| `$cars` | Collection of `Car` | `CarService::getAllPublished()` | Car dropdown `<select>` |
| `$whatsappNumber` | `string\|null` | `SettingService::get('contact.whatsapp_number')` | WhatsApp redirect target |
| `$selectedCar` | `?Car` | `CarService::findBySlug($request->car)` | Pre-selects car in dropdown |

**Renders:**
- Car selector `<select>` — pre-populated with `$cars`, option pre-selected from `$selectedCar->id`
- Form fields: Trip Route/Destination, Customer Name, Phone, Pickup Date, Return Date, Notes
- All rendered as standard HTML form elements
- WhatsApp CTA button (submit triggers JS, not form POST)

**Critical:** This form does NOT submit to any Laravel endpoint. The submit event is intercepted by `whatsapp.js`, which builds and encodes the message and redirects to `wa.me`. See §13 for the complete flow.

### 7.3 View Composer for Shared Layout Data

As defined in `Backend_Development_Plan.md` §7.3, a View Composer registered in `AppServiceProvider` injects the following into `layouts/app.blade.php` automatically:

| Variable | Content |
|----------|---------|
| `$companySettings` | `company` group from `SettingService` |
| `$contactSettings` | `contact` group from `SettingService` |
| `$socialSettings` | `social` group from `SettingService` |
| `$seoSettings` | `seo` group from `SettingService` |
| `$appearanceSettings` | `appearance` group from `SettingService` |

These are available in all Blade views through the layout without each controller passing them explicitly.

---

## 8. Tailwind CSS Architecture

### 8.1 Utility and Component Strategy

- Blade markup uses Tailwind v4 utility classes directly.
- Repeated UI is extracted into Blade components rather than duplicated or replaced with a parallel BEM stylesheet.
- Use `gap-*` for sibling spacing and mobile-first responsive variants such as `md:` and `lg:`.
- JavaScript state uses semantic `data-*` and ARIA attributes. Tailwind variants style those states where practical.
- Custom CSS is reserved for behavior that utilities cannot express clearly, such as complex gallery or carousel transitions.
- Do not introduce Bootstrap, BEM component layers, or a second utility system.

### 8.2 CSS-First Theme Tokens

Design tokens are defined in `resources/css/app.css` using Tailwind v4's `@theme` directive. The generated CSS variables keep branding consistent across utilities and any small custom rules.

**Token categories:**
```css
@import 'tailwindcss';

@theme {
  /* Colors — populated by UI/UX designer */
  --color-brand:          ;
  --color-brand-dark:     ;
  --color-accent:         ;
  --color-surface:        ;
  --color-muted:          ;

  /* Typography */
  --font-sans:            ;  /* Arabic-compatible family */

  /* Spacing */
  --breakpoint-sm: 40rem;
  --breakpoint-md: 48rem;
  --breakpoint-lg: 64rem;
  --breakpoint-xl: 80rem;
  --container-7xl: 75rem;
}
```

**Values are set by the UI/UX designer.** The frontend developer implements the token system; the designer fills in the values.

### 8.3 `app.css` Organization

Keep the CSS entry point small and explicit:

```css
@import 'tailwindcss';

@source '../views/**/*.blade.php';
@source '../js/**/*.js';

@theme {
  /* Project design tokens */
}

@layer base {
  /* Arabic typography and document-wide defaults only */
}

@layer components {
  /* Rare complex rules that are clearer than repeated utilities */
}
```

### 8.4 RTL with Tailwind

Set `<html lang="ar" dir="rtl">` and prefer logical utilities such as `ms-*`, `me-*`, `ps-*`, `pe-*`, `start-*`, `end-*`, `text-start`, and `text-end`. Use `rtl:` or `ltr:` variants only when a component genuinely needs direction-specific behavior. Do not create a separate RTL stylesheet.

---

## 9. JavaScript Architecture

### 9.1 Philosophy

JavaScript is loaded once (`app.js`) and each module initializes itself if its trigger element exists on the current page. There is no global state, no component tree, and no framework.

```javascript
// app.js pattern
import { initNavigation } from './modules/navigation.js';
import { initGallery }    from './modules/gallery.js';
import { initFaq }        from './modules/faq.js';
import { initCarousel }   from './modules/carousel.js';
import { initWhatsapp }   from './modules/whatsapp.js';
import { initFilters }    from './modules/filters.js';

document.addEventListener('DOMContentLoaded', () => {
    initNavigation();
    initGallery();
    initFaq();
    initCarousel();
    initWhatsapp();
    initFilters();
});
```

Each `init*` function checks for its target element before running. No errors if the element is absent.

### 9.2 Module Responsibilities

---

#### `navigation.js`

**Purpose:** Mobile navigation toggle

**What it does:**
- Selects the hamburger button and the mobile nav panel
- Toggles `is-open` class on the nav panel
- Toggles `aria-expanded` on the hamburger button
- Traps keyboard focus inside the nav when open
- Closes nav on `Escape` key
- Closes nav on outside click

**What it does NOT do:** Render navigation links (those are in Blade)

---

#### `gallery.js`

**Purpose:** Car image gallery interaction

**What it does:**
- On thumbnail click: updates the main image `src` and `alt` to the clicked image's data
- Maintains `is-active` state on the selected thumbnail
- Optional lightbox: opens a full-screen overlay on main image click; closes on `Escape` or overlay click
- Keyboard navigation: arrow keys cycle through images in lightbox mode

**What it does NOT do:** Fetch images from the API (images are rendered in Blade from `$car->getMedia('car_images')`)

---

#### `faq.js`

**Purpose:** FAQ accordion enhancement

**What it does:**
- Enhances native `<details>/<summary>` elements with animated open/close
- Adds `aria-expanded` state to summary elements
- Optional: closes all other items when one is opened (accordion behaviour)

**Why JavaScript here when `<details>` is native:** The native `<details>` element works without JavaScript. `faq.js` only adds CSS-class-based animation. If JavaScript fails, the native accordion still works.

---

#### `carousel.js`

**Purpose:** Hero banner carousel / slider

**What it does:**
- Auto-advances slides on a timer
- Previous/Next button navigation
- Dots/indicator navigation
- Pauses auto-advance on user interaction or hover
- `prefers-reduced-motion` check: if the user prefers reduced motion, auto-advance is disabled and transitions are instant

**Note:** If the designer's hero section uses CSS-only animation (e.g., CSS `@keyframes`), `carousel.js` may not be needed. Evaluate against the final design before creating this module.

---

#### `whatsapp.js`

**Purpose:** WhatsApp booking message generation

**What it does:**
- Attaches a submit handler to the booking form
- Prevents the default form submission (`event.preventDefault()`)
- Reads form field values
- Validates required fields client-side (see §13)
- Constructs the message string in Arabic
- Encodes the message with `encodeURIComponent()`
- Constructs the `wa.me/{number}?text={encoded}` URL
- Redirects with `window.location.href = url`

**Data it uses:** `$whatsappNumber` embedded in the page by Blade (as a `data-*` attribute on the form or as a Blade-printed JavaScript constant)

**What it does NOT do:** POST to any server, store any data, call any API

---

#### `filters.js`

**Purpose:** Car listing filter interaction

**What it does:**
- On category filter click: updates the URL query parameter (`?category=slug`) and navigates (using `window.location.href` — a full page reload)
- On search input: debounced submission of the search form
- Highlights the currently active filter based on URL params

**Why full-page reload for filters:** The car listing is server-rendered. Updating filters means getting a new server-rendered page. This is correct MPA behaviour — no AJAX filtering. The URL correctly reflects the filter state, supporting back-button navigation and link sharing.

### 9.3 No External JavaScript Libraries

The following are assessed as unnecessary for V1:

| Potential Package | Verdict | Reason |
|-------------------|---------|--------|
| Swiper.js | Not needed | CSS-only carousel or a small custom `carousel.js` is sufficient |
| Fancybox / GLightbox | Evaluate | Only if native `<dialog>` + custom JS is insufficient for the gallery lightbox |
| AOS (scroll animations) | Not needed | CSS `@keyframes` + `IntersectionObserver` is sufficient |
| Flatpickr (date picker) | Evaluate | For booking date inputs; native `<input type="date">` is acceptable for V1 |
| Alpine.js | Not needed | Interaction scope is small; vanilla JS modules are sufficient |

If `GLightbox` is added for the gallery: document its bundle size (~8 KB gzipped) and confirm MPA compatibility. This is the only likely third-party candidate.

---

## 10. Responsive Design

### 10.1 Approach

**Mobile-first.** Base styles target mobile screens. Media queries add complexity for larger screens. This is the correct approach for a website where the majority of users in the target market are mobile users.

### 10.2 Breakpoints

Exact breakpoint values are confirmed by the UI/UX designer's design system. Working breakpoints for implementation:

| Breakpoint | Approximate Width | Target Devices |
|-----------|------------------|----------------|
| `sm` | ≥ 640px | Large phones |
| `md` | ≥ 768px | Tablets |
| `lg` | ≥ 1024px | Laptops, small desktops |
| `xl` | ≥ 1280px | Large desktops |

Use Tailwind's mobile-first `sm:`, `md:`, `lg:`, and `xl:` variants. Override breakpoint theme variables in `app.css` only if the approved design requires non-default values.

### 10.3 Layout Behaviour Per Page

| Component | Mobile | Tablet | Desktop |
|-----------|--------|--------|---------|
| Navigation | Hamburger menu / off-canvas | Hamburger or inline | Inline horizontal nav |
| Car grid | 1 column | 2 columns | 3–4 columns |
| Car card | Full width | Standard card | Standard card |
| Car detail gallery | Stacked (main + scrollable thumbnails) | Side-by-side | Side-by-side |
| Car specs table | Full-width, stacked rows | Table layout | Table layout |
| FAQ accordion | Full width | Full width | Constrained width |
| Booking form | Stacked inputs | 1–2 column grid | 2-column grid |
| Hero banner | Image + text stacked or overlaid | Full banner | Full banner |

### 10.4 Touch Considerations

- All interactive elements minimum touch target: 44×44px (iOS HIG guideline)
- Sufficient spacing between tappable elements
- No hover-only states for essential functionality
- Date inputs use native `<input type="date">` for mobile keyboard

---

## 11. RTL and Arabic Support

### 11.1 HTML Document

Every page rendered by the master layout includes:
```html
<html lang="ar" dir="rtl">
```

This is set in `layouts/app.blade.php` and applies to all public pages.

### 11.2 Tailwind Logical Utilities

As defined in §8.4, use Tailwind's logical utilities throughout. Prefer `ms-4` over `ml-4`, `me-4` over `mr-4`, `ps-4` over `pl-4`, and `text-start` over `text-left`. Use `rtl:` and `ltr:` variants only for genuinely directional details such as arrow orientation. No separate `rtl.css` file is needed.

### 11.3 Typography

Arabic text requirements:
- Use an Arabic-compatible font (e.g., Tajawal, Cairo, Noto Sans Arabic, or Almarai — confirmed by designer)
- Load via Google Fonts or self-hosted (self-hosted preferred for GDPR and performance)
- Font weight options: ensure the font includes weights 400, 500, and 700 at minimum
- Arabic text line-height: set to `1.7` or higher — Arabic text has ascenders and descenders that need more vertical space than Latin text
- Letter-spacing: do NOT set `letter-spacing` on Arabic text — it breaks ligatures

### 11.4 Number Formatting

In V1, numeric values (car year, sort order, page numbers) can be displayed as Western Arabic numerals (0-9). Eastern Arabic numerals (٠١٢٣٤٥٦٧٨٩) are not required unless explicitly specified by the designer or client.

### 11.5 Directional Elements

| Element | RTL Handling |
|---------|-------------|
| Navigation arrow icons | Mirror horizontally for RTL — use CSS `transform: scaleX(-1)` or `unicode-bidi` icons |
| Pagination prev/next | Previous is right arrow (→), Next is left arrow (←) in RTL |
| Breadcrumb separator | `/` or `›` separator should point RTL — use logical direction |
| Form input icons | Icons inside inputs appear on the `inline-start` side in RTL |
| Carousel prev/next | Swap direction buttons |
| WhatsApp icon | Neutral — no flip needed |

### 11.6 Forms in RTL

- `<input type="text">` and `<textarea>` for Arabic text content: no special attribute needed; the browser handles RTL text direction inside inputs when `dir="rtl"` is on `<html>`
- `<input type="date">`, `<input type="tel">`, `<input type="email">`: these may render LTR internally in some browsers; set `dir="ltr"` on those specific inputs while the label remains RTL

### 11.7 Content Language

All user-visible labels, headings, and static content are in Arabic. Only technical strings (URLs, email addresses, phone numbers) may appear in LTR format within RTL context. Use inline `dir="ltr"` on those elements:
```html
<a href="tel:+971501234567" dir="ltr">+971 50 123 4567</a>
```

---

## 12. Image and Media Handling

### 12.1 Car Images (Spatie Media Library)

The backend (Spatie Media Library) provides fully-resolved public URLs. Blade views consume these URLs directly. No image transformation happens in the frontend.

**Car listing — cover image:**
```blade
{{-- $car->getFirstMedia('car_images') returns the cover media item --}}
@if($car->getFirstMedia('car_images'))
    <img
        src="{{ $car->getFirstMedia('car_images')->getUrl() }}"
        alt="{{ $car->name }}"
        width="400"
        height="300"
        loading="lazy"
        class="aspect-[4/3] h-auto w-full object-cover"
    >
@else
    <img
        src="{{ asset('images/placeholder-car.svg') }}"
        alt="{{ $car->name }}"
        width="400"
        height="300"
        loading="lazy"
        class="aspect-[4/3] h-auto w-full bg-slate-100 object-cover"
    >
@endif
```

**Car detail — full gallery:**
The `components/car-gallery` component receives `$car->getMedia('car_images')` sorted by `order_column`. Cover image (first) gets `loading="eager"`. All others get `loading="lazy"`.

### 12.2 `width` and `height` Attributes

Always set `width` and `height` on `<img>` elements to prevent Cumulative Layout Shift (CLS). Use the intended display dimensions, not the file dimensions. Tailwind controls the visual sizing:

```html
<img class="aspect-[4/3] h-auto w-full object-cover" width="800" height="600" alt="...">
```

### 12.3 Responsive Images

For car images where multiple sizes are available from Spatie (if image conversions are configured in V2), use `srcset`:

```html
<img
    src="{{ $media->getUrl() }}"
    srcset="{{ $media->getUrl('thumb') }} 400w,
            {{ $media->getUrl() }} 800w"
    sizes="(max-width: 768px) 100vw, 400px"
    alt="{{ $car->name }}"
>
```

In V1, if Spatie conversions are not configured, use a single URL. The `srcset` attribute can be added later without Blade template changes — only the data passed to the component changes.

### 12.4 Banner Images

Banners use the `banners.image` VARCHAR path. Public URL resolved by the controller or View Composer:
```blade
<img
    src="{{ $banner->image ? Storage::url($banner->image) : asset('images/placeholder-banner.svg') }}"
    alt="{{ $banner->title ?? 'لافتة ترويجية' }}"
    loading="eager"    {{-- Above-the-fold --}}
    width="1200"
    height="500"
>
```

### 12.5 Category Images

Category images use `categories.image` VARCHAR path, resolved with `Storage::url()`. Used in the category filter bar if the designer includes them.

### 12.6 Logo and Favicon

Logo from `$companySettings['logo']` (View Composer). Resolve URL with `Storage::url()`. Always provide an SVG fallback in `resources/images/logo.svg` for when the setting is null.

Favicon rendered in `<head>` via the `seo-head` component:
```blade
<link rel="icon" href="{{ $appearanceSettings['favicon'] ? Storage::url($appearanceSettings['favicon']) : asset('images/favicon.ico') }}">
```

### 12.7 Alt Text Policy

- Car images: `{{ $car->name }}` with optional position suffix
- Banner images: banner `title` if available, otherwise a generic Arabic description
- Category images: `{{ $category->name }}`
- Logo: company name from settings
- Social icons: the network name in Arabic (e.g., `إنستغرام`)
- Purely decorative images: `alt=""`

---

## 13. WhatsApp and Contact Flow

### 13.1 WhatsApp as the Primary Contact Channel

WhatsApp is the main customer inquiry mechanism. The booking form does not submit to Laravel. All form data stays in the browser and is encoded into a WhatsApp message URL.

### 13.2 WhatsApp Number Delivery

The WhatsApp number is delivered from the server by `BookingController::show()` into `$whatsappNumber`. Blade embeds it in the page as a `data-*` attribute on the booking form:

```html
<form
    id="booking-form"
    data-whatsapp="{{ $whatsappNumber }}"
    novalidate
>
```

`whatsapp.js` reads `document.getElementById('booking-form').dataset.whatsapp`. It is never hardcoded in JavaScript.

**If `$whatsappNumber` is null:** The submit button is disabled and a message is shown: "خدمة الحجز غير متاحة حالياً، تواصل معنا عبر الهاتف." The booking form is rendered but the WhatsApp action is blocked gracefully.

### 13.3 Booking Form Fields

| Field | Input Type | Required | Notes |
|-------|-----------|----------|-------|
| Car selection | `<select>` | Yes | Pre-populated from `$cars`; pre-selected from `$selectedCar` |
| Trip Route/Destination | `<input type="text">` | Yes | Where they want to go |
| Customer Name | `<input type="text">` | Yes | |
| Phone Number | `<input type="tel">` | Yes | `dir="ltr"` for phone number |
| Pickup Date | `<input type="date">` | Yes | Min: today |
| Return Date | `<input type="date">` | Yes | Min: pickup date |
| Additional Notes | `<textarea>` | No | |

### 13.4 Client-Side Validation (`whatsapp.js`)

Before generating the WhatsApp URL, `whatsapp.js` validates:
- All required fields are non-empty
- Phone number is not empty and has a reasonable length
- Pickup date is not in the past
- Return date is not before pickup date

If validation fails:
- Invalid fields receive `is-invalid` class
- Error messages appear via `aria-describedby`-linked `<span>` elements
- The WhatsApp redirect does not happen

**Important:** This validation is a user convenience only. No server-side booking validation exists because no booking data reaches the server. Do not treat this as a security control.

### 13.5 Message Generation and Redirect

```
whatsapp.js:
1. event.preventDefault()
2. Validate fields → show errors if invalid
3. Get $whatsappNumber from form data attribute
4. Build message string (Arabic):
   "طلب استفسار / حجز مسبق
    السيارة: {car name}
    المسار: {trip route}
    الاسم: {name}
    الهاتف: {phone}
    تاريخ الاستلام: {pickup date}
    تاريخ الإرجاع: {return date}
    ملاحظات: {notes}"
5. const encoded = encodeURIComponent(message)
6. const url = `https://wa.me/${whatsappNumber}?text=${encoded}`
7. window.open(url, '_blank', 'noopener,noreferrer')
   OR window.location.href = url
```

**Prefer `window.open`** to avoid breaking mobile browsers where the back button may not return to the booking form after a redirect. Some mobile browsers handle `wa.me` better with `window.open`.

### 13.6 Fallback Contact

The booking page and contact page always show the WhatsApp number as a plain link and the phone number as a `tel:` link. If JavaScript fails or the browser blocks the popup, the customer can still contact the company.

### 13.7 Quick WhatsApp CTAs

On car detail pages, a "استفسر عبر واتساب" (Inquire via WhatsApp) button links directly to:
```
https://wa.me/{$whatsappNumber}?text=أود الاستفسار عن سيارة: {$car->name}
```

This is a plain `<a>` tag, not a JavaScript action. No form is needed.

---

## 14. SEO Implementation

### 14.1 Why Blade MPA is SEO-Optimal

Server-rendered Blade delivers complete HTML to crawlers. No JavaScript execution is required for indexing. All content, titles, and meta tags are present in the initial HTTP response.

### 14.2 Per-Page Title and Meta Structure

The `components/seo-head.blade.php` component renders `<head>` metadata. Each page section defines:

```blade
{{-- In pages/cars/show.blade.php --}}
@section('title', ($car->meta_title ?? $car->name . ' — تأجير سيارات'))
@section('meta_description', ($car->meta_description ?? Str::limit(strip_tags($car->description), 160)))
@section('og_image', $car->getFirstMedia('car_images')?->getUrl())
@section('canonical', route('cars.show', $car->slug))
```

| Page | Title Pattern | Description Pattern |
|------|--------------|---------------------|
| Home | `{seo.site_title}` | `{seo.meta_description}` |
| Car Listing | `السيارات المتاحة — {site_title}` | Default meta description |
| Car Detail | `{car.meta_title ?? car.name} — {site_title}` | `{car.meta_description ?? car.description(160 chars)}` |
| FAQ | `الأسئلة الشائعة — {site_title}` | Default |
| About | `عن الشركة — {site_title}` | `{company.description(160 chars)}` |
| Contact | `تواصل معنا — {site_title}` | Default |
| Booking | `احجز سيارتك عبر واتساب — {site_title}` | Default |

### 14.3 Canonical URLs

Every page declares a canonical URL. Car detail pages use the slug-based route. Car listing with filters (`?category=suv`) use the filtered URL as canonical (this is correct — filtered pages are distinct content).

### 14.4 Open Graph Tags

Rendered in `seo-head.blade.php`:
```html
<meta property="og:title"       content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:url"         content="{{ $canonical }}">
<meta property="og:type"        content="{{ $ogType }}">  {{-- website or article --}}
<meta property="og:locale"      content="ar_AE">
<meta property="og:image"       content="{{ $ogImage }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
```

### 14.5 Twitter/X Card

```html
<meta name="twitter:card"        content="summary_large_image">
<meta name="twitter:title"       content="{{ $title }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="twitter:image"       content="{{ $ogImage }}">
```

### 14.6 Semantic HTML

| Element | Usage |
|---------|-------|
| `<header>` | Site header |
| `<nav>` | Primary navigation, breadcrumbs, pagination |
| `<main>` | Main page content |
| `<section>` | Logical page sections (featured cars, FAQ section, etc.) |
| `<article>` | Individual car cards in listing |
| `<footer>` | Site footer |
| `<h1>` | One per page — page primary heading |
| `<h2>–<h6>` | Hierarchical subheadings — never skip levels |

**Car listing:** Each `<article>` wraps one car card. The page `<h1>` is "السيارات المتاحة للإيجار".

**Car detail:** `<h1>` is `$car->name`. Specifications and features use `<h2>` subheadings.

### 14.7 Structured Data (JSON-LD)

Structured data is rendered via `@push('structured_data')` from page-specific views into `@stack('structured_data')` in the layout's `<head>`.

---

**`Organization` + `LocalBusiness` (all pages via layout):**
```json
{
  "@context": "https://schema.org",
  "@type": ["Organization", "LocalBusiness"],
  "name": "{company.name}",
  "description": "{company.description}",
  "url": "{APP_URL}",
  "logo": "{company.logo_url}",
  "telephone": "{contact.phone_primary}",
  "email": "{contact.email}",
  "address": { "@type": "PostalAddress", "streetAddress": "{contact.address}" }
}
```

---

**`BreadcrumbList` (Car detail, FAQ pages — via `components/breadcrumbs`):**
```json
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    { "@type": "ListItem", "position": 1, "name": "الرئيسية", "item": "/" },
    { "@type": "ListItem", "position": 2, "name": "السيارات", "item": "/cars" },
    { "@type": "ListItem", "position": 3, "name": "{car.name}", "item": "/cars/{slug}" }
  ]
}
```

---

**`FAQPage` (FAQ page only):**
```json
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "{faq.question}",
      "acceptedAnswer": { "@type": "Answer", "text": "{faq.answer}" }
    }
  ]
}
```

---

**`Product` or `Vehicle` schema on Car Detail:** Evaluate against actual Schema.org vehicle types. `Car` (https://schema.org/Car) or a `Product` schema may be appropriate. Only add if it accurately represents the page content. Do not add inaccurate structured data.

### 14.8 Technical SEO

**Sitemap:** Laravel controller at `GET /sitemap.xml` returns XML. The controller uses `CarService::getAllPublished()`, static page routes, and `CategoryService::getActive()` to build the sitemap. Registered in `routes/web.php`.

**robots.txt:** Served statically from `public/robots.txt`:
```
User-agent: *
Allow: /
Disallow: /admin
Disallow: /api
Sitemap: {APP_URL}/sitemap.xml
```

**404 Handling:** Car detail page — when `CarService::findBySlug()` throws `ModelNotFoundException`, Laravel's exception handler returns `errors/404.blade.php` with HTTP 404. Never returns 200 with an error message.

**Crawlable navigation:** All navigation links are `<a href="...">` elements with real URLs. No JavaScript-only navigation.

---

## 15. Accessibility

### 15.1 Semantic HTML First

ARIA is used only when semantic HTML cannot express the needed accessibility information. Semantic HTML elements come first.

| Correct | Incorrect |
|---------|----------|
| `<button>` for actions | `<div role="button">` |
| `<nav>` for navigation | `<div aria-role="navigation">` |
| `<h1>–<h6>` hierarchy | ARIA heading roles on `<div>` |
| `<details>/<summary>` accordion | `<div>` with ARIA accordion role |

### 15.2 Keyboard Navigation

- All interactive elements reachable with `Tab`
- Logical tab order follows reading order (left-to-right in LTR, right-to-left in RTL)
- Mobile navigation: focus trapped inside when open; `Escape` closes it
- Gallery lightbox: focus trapped inside when open; `Escape` closes it; arrow keys navigate images
- FAQ accordion: `Enter`/`Space` open/close items (handled by `<details>/<summary>` natively)
- Pagination: all page links keyboard-accessible as `<a>` elements

### 15.3 Visible Focus States

Never use `outline: none` without providing an equivalent custom focus indicator. All focusable elements must have visible focus states with sufficient contrast (3:1 minimum against adjacent colors, per WCAG 2.1 AA).

```css
:focus-visible {
    outline: 2px solid var(--color-primary);
    outline-offset: 2px;
}
```

Use `:focus-visible` (not `:focus`) to show focus rings only for keyboard users, not mouse clicks.

### 15.4 ARIA Usage

| ARIA attribute | Where used | Purpose |
|----------------|-----------|---------|
| `aria-label` | Nav `<nav>`, icon-only buttons, social links | Describes purpose when text label is absent |
| `aria-current="page"` | Active navigation link | Announces current page to screen readers |
| `aria-expanded` | Mobile nav toggle, FAQ summary | State of collapsible elements |
| `aria-describedby` | Form inputs with error messages | Links inputs to error message elements |
| `role="alert"` | Form success/error flash messages | Announces dynamic status messages |
| `aria-hidden="true"` | Decorative icons | Hides purely decorative content from screen readers |

### 15.5 Forms

- Every `<input>`, `<select>`, `<textarea>` has an associated `<label>` (using `for` + `id` or wrapping)
- Required fields marked with `required` attribute and visual indicator
- Error messages rendered in DOM (not just as CSS changes) and linked via `aria-describedby`
- `autocomplete` attributes on name, phone, email fields for user convenience

### 15.6 Reduced Motion

```css
@media (prefers-reduced-motion: reduce) {
    *, *::before, *::after {
        animation-duration: 0.01ms !important;
        transition-duration: 0.01ms !important;
    }
}
```

`carousel.js` checks `window.matchMedia('(prefers-reduced-motion: reduce)').matches` and disables auto-advance if true.

### 15.7 Color Contrast

All text and interactive elements meet WCAG 2.1 AA:
- Normal text (below 18pt): 4.5:1 contrast ratio
- Large text (18pt+ or 14pt+ bold): 3:1
- Interactive elements and focus indicators: 3:1 against adjacent colors

Color is never the only means of conveying information.

---

## 16. Performance

### 16.1 Minimal JavaScript

The single JS bundle is small by design. No framework is loaded. Estimated production JS bundle size: under 15 KB gzipped. This is achievable because:
- No framework runtime
- No component library
- Small focused modules
- No polyfills for modern browsers

### 16.2 CSS Optimization

- Vite production build minifies and autoprefixes CSS
- Tailwind scans project sources and emits only used utilities
- `@theme` variables reduce duplication and keep brand values consistent
- Target CSS bundle size: under 30 KB gzipped

### 16.3 Image Performance

- `loading="lazy"` on all below-the-fold images
- `loading="eager"` only on hero image and cover images above the fold
- `width` and `height` on all `<img>` elements — prevents CLS
- `aspect-ratio` CSS prevents layout shifts during image load
- WebP format encouraged for uploads (Filament accepts webp; Spatie can convert on upload in V2)
- Fallback images (placeholder SVG) are vector — zero additional HTTP requests

### 16.4 Font Loading

```html
{{-- Preconnect for Google Fonts (if used) --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

{{-- Preload critical font files --}}
<link rel="preload" href="/fonts/arabic-font.woff2" as="font" type="font/woff2" crossorigin>
```

Font loading strategy: `font-display: swap` — text is shown in a fallback font immediately; the web font swaps in when loaded. This prevents invisible text during font load (FOIT).

**Self-hosted fonts preferred** over Google Fonts CDN for privacy (GDPR) and performance (eliminates DNS lookup + TLS handshake for an external domain).

### 16.5 Vite Production Build

`npm run build` produces:
- Minified CSS (one file, content-hashed)
- Minified JS (one file, content-hashed)
- Cache-busting filenames automatically

`@vite()` in Blade resolves to the correct hashed filename.

### 16.6 Server-Side Performance

Page performance benefits from backend caching (Redis, defined in `Backend_Development_Plan.md` §14). The frontend receives complete HTML from cached service calls. No additional JavaScript loading time for data.

### 16.7 Lighthouse Targets (V1)

| Metric | Target |
|--------|--------|
| Performance | ≥ 85 (mobile) |
| Accessibility | ≥ 90 |
| Best Practices | ≥ 90 |
| SEO | ≥ 90 |
| Core Web Vitals LCP | ≤ 2.5s |
| Core Web Vitals CLS | ≤ 0.1 |
| Core Web Vitals FID/INP | ≤ 200ms |

These are targets, not blockers for launch. Measure after implementation and optimize specific bottlenecks.

---

## 17. Error and Empty States

### 17.1 Car Listing — No Cars

When `$cars->isEmpty()` is true:
- Render an informative message: "لا تتوفر سيارات في هذه الفئة حالياً."
- Show a "عرض جميع السيارات" link to clear the filter
- Do NOT show a broken grid or Laravel error

When `$cars->isEmpty()` with no filters applied (the entire catalogue is empty):
- Show: "سيتم إضافة السيارات قريباً. تواصل معنا للاستفسار."
- Show WhatsApp CTA

### 17.2 Car Detail — Car Not Found or Unpublished

The controller returns HTTP 404 for unknown or unpublished slugs. Laravel renders `errors/404.blade.php`:
- Extends `layouts/app.blade.php` for consistent header/footer
- Friendly Arabic message: "الصفحة غير موجودة"
- Links to home page and car listing

### 17.3 Missing Car Images

If a car has no images, `components/car-gallery` renders the `placeholder-car.svg` image. The card component also shows the SVG placeholder. No broken image icons.

### 17.4 Missing Optional Content

| Missing Content | Behavior |
|-----------------|---------|
| `$car->color` is null | Omit color line from the detail view |
| `$car->description` is null | Omit description section |
| `$car->specifications` is null or empty | Omit specs section |
| `$car->features` is empty | Omit features section |
| `$car->meta_title` is null | Fall back to `$car->name` + site title |
| `$banner->title` is null | Image-only banner (no text overlay) |
| `$banner->cta_text` is null | No CTA button on that banner |
| `$category->image` is null | No category image in filter bar |
| `$settings['company']['logo']` is null | Use fallback SVG logo |
| `$whatsappNumber` is null | Disable WhatsApp button; show phone fallback |

### 17.5 500 / Maintenance

`errors/500.blade.php` — generic server error message in Arabic. Never exposes Laravel stack traces (enforced by `APP_DEBUG=false`).

`errors/503.blade.php` — maintenance mode page. This is separate from the main layout (cannot depend on database data); a simple standalone HTML page with a friendly Arabic message.

---

## 18. Security Considerations

### 18.1 Frontend is Not a Security Boundary

All security enforcement is in the backend. Client-side validation and display rules are convenience features only.

### 18.2 Never Expose Private Data in Views

Blade templates must never output:
- Pricing fields: `$car->price_daily`, `$car->price_weekly`, `$car->price_monthly`
- Admin metadata: `$car->is_published`, `$car->sort_order`
- Database credentials or configuration
- Admin panel URLs (other than `/admin` which is known)
- Internal storage file paths (use `Storage::url()` to generate public URLs)

### 18.3 XSS in Blade

- All user-facing output uses `{{ }}` (escaped by default in Blade)
- `{!! !!}` (unescaped) is used only for `$car->description` — admin-entered content that may contain HTML from a rich text editor. This is acceptable because the admin is a trusted user. Document this decision explicitly.
- Never use `{!! !!}` for user-submitted data (e.g., contact form flash messages)

### 18.4 CSRF on Forms

Contact form includes `@csrf`. The booking form does NOT need `@csrf` because it never submits to the server — `whatsapp.js` intercepts the submit event before it reaches the server.

### 18.5 External Links

All external links (`target="_blank"`) include `rel="noopener noreferrer"` to prevent window opener attacks and referrer leakage.

### 18.6 WhatsApp Number

The WhatsApp number is rendered into the page via Blade. If the `contact.whatsapp_number` setting contains unexpected characters, the URL construction in `whatsapp.js` must sanitize the number (remove spaces, `+`, dashes — keep digits only) before constructing the `wa.me/` URL.

---

## 19. Browser Compatibility

### 19.1 Supported Browsers

| Browser | Version |
|---------|---------|
| Chrome | Last 2 major versions |
| Edge | Last 2 major versions |
| Firefox | Last 2 major versions |
| Safari | Last 2 major versions (critical for iOS) |
| Safari iOS | iOS 15+ |
| Chrome Android | Last 2 major versions |
| Samsung Internet | Last 2 major versions |

**No IE11 support.** No legacy browser polyfills.

### 19.2 Features Used and Compatibility

| Feature | Support |
|---------|---------|
| CSS Custom Properties | Universal (all targets) |
| CSS Logical Properties | Universal (all targets since 2023) |
| CSS `aspect-ratio` | Universal |
| `loading="lazy"` | Universal |
| `<details>/<summary>` | Universal |
| `window.matchMedia` | Universal |
| `encodeURIComponent` | Universal |
| `import` (ES modules via Vite) | Vite compiles to compatible syntax |
| `IntersectionObserver` | Universal |
| `<input type="date">` | Universal (renders native date picker) |

### 19.3 Progressive Enhancement

JavaScript enhancements degrade gracefully:
- Navigation: the mobile menu falls back to a static visible list if JS fails
- FAQ: `<details>` works without JS (no animation, but functional)
- Gallery: if JS fails, all images are visible as a simple list
- Booking: WhatsApp CTA button (`<a href="wa.me/...">`) works without JS; only the form-generated message requires JS

---

## 20. Testing Strategy

### 20.1 Blade / View Testing (Laravel Tests)

Test that each page renders correctly with its expected data.

| Test | Assertion |
|------|-----------|
| `HomePageViewTest` | `GET /` returns 200; view contains company name from settings; featured cars rendered |
| `CarListingViewTest` | `GET /cars` returns 200; car cards rendered; category filter links present |
| `CarDetailViewTest` | `GET /cars/{slug}` returns 200 for published car; car name in `<h1>`; WhatsApp button present |
| `CarDetailNotFoundTest` | `GET /cars/non-existent` returns 404 |
| `CarDetailUnpublishedTest` | `GET /cars/{unpublished-slug}` returns 404 |
| `FaqPageViewTest` | `GET /faq` returns 200; FAQ items rendered |
| `BookingPageViewTest` | `GET /booking` returns 200; car dropdown populated; with `?car=slug` pre-selects car |
| `BookingPageNoWhatsappTest` | When `whatsapp_number` setting is null, booking page renders warning |
| `ContactPageViewTest` | `GET /contact` returns 200 |
| `EmptyCarListingTest` | When no published cars: empty-state message rendered, not a PHP error |
| `MissingCarImageTest` | Car with no media renders placeholder SVG, not a PHP error |
| `NullOptionalFieldsTest` | Car with null `description`, `specifications`, `color` renders without error |

### 20.2 Browser / Interaction Testing

Manual or automated (Playwright/Cypress — optional for V1) tests:

| Scenario | Steps |
|----------|-------|
| Mobile navigation | Open mobile view, tap hamburger, verify nav opens, tap close, verify nav closes |
| FAQ accordion | Click FAQ question, verify answer expands, click again, verify collapses |
| Car gallery | Open car detail, click thumbnail, verify main image updates |
| Category filter | Click a category on `/cars`, verify URL updates and filtered cars shown |
| WhatsApp booking | Complete booking form, click submit, verify `wa.me/` URL opened |
| WhatsApp validation | Submit empty form, verify error messages appear |
| Booking pre-fill | Navigate to `/booking?car=bmw-5-series`, verify car pre-selected in dropdown |
| Contact form | Submit contact form, verify success flash message displayed |

### 20.3 Responsive Testing

| Size | Test |
|------|------|
| 375px (iPhone SE) | Navigation, car grid (1 column), gallery, booking form |
| 768px (iPad) | Navigation, car grid (2 columns), specs layout |
| 1280px (Desktop) | Full layout, 3–4 column grid |
| RTL at all sizes | Verify logical properties render correctly; no layout breaks |

Test tools: Chrome DevTools device emulation, physical iPhone (Safari), physical Android (Chrome).

### 20.4 Accessibility Testing

| Tool | What to test |
|------|-------------|
| axe DevTools / Accessibility Insights | Automated ARIA, contrast, label errors |
| Keyboard navigation (Tab only) | Visit all pages, operate all interactions with keyboard only |
| NVDA or VoiceOver | Navigate home, cars listing, car detail, FAQ with screen reader |
| Colour contrast checker | All text and interactive element contrast ratios |

### 20.5 SEO Testing

| Check | Tool |
|-------|------|
| `<title>` per page | Chrome DevTools, view-source |
| Meta descriptions | `<head>` inspection |
| Canonical URLs | `<link rel="canonical">` inspection |
| OG tags | Facebook Sharing Debugger, LinkedIn Post Inspector |
| Structured data validity | Google Rich Results Test |
| Sitemap | `GET /sitemap.xml` — inspect XML for all public car and category URLs |
| robots.txt | `GET /robots.txt` — verify `/admin` and `/api` disallowed |
| Crawlable links | All navigation links are `<a href>` elements visible in source |

### 20.6 Performance Validation

| Tool | Target |
|------|--------|
| Lighthouse (mobile) | Performance ≥ 85, SEO ≥ 90, Accessibility ≥ 90 |
| Chrome DevTools Network | JS bundle ≤ 15 KB gzipped; CSS bundle ≤ 30 KB gzipped |
| WebPageTest | LCP ≤ 2.5s on mobile 4G |
| Image sizes | No car image served above 800 KB |

---

## 21. Implementation Sequence

This sequence is coordinated with `Backend_Development_Plan.md` §21 phases. Frontend work begins in parallel with or after backend Phase 2 (models established) so that real data exists.

### Phase 1 — Frontend Foundation (Parallel with Backend Phase 1–2)

**Goal:** Tailwind CSS v4 and Vite configured, theme tokens established, master layout working.

Tasks:
1. Confirm `vite.config.js` uses Laravel Vite and `@tailwindcss/vite`
2. Keep `resources/css/app.css` as the single Tailwind CSS entry point
3. Define placeholder brand, font, breakpoint, radius, and shadow tokens with `@theme`
4. Create `resources/js/app.js` with module import structure
5. Create `layouts/app.blade.php` with `<html lang="ar" dir="rtl">`, Vite assets, `@yield`/`@stack` slots
6. Implement `partials/header.blade.php` and `partials/footer.blade.php` with static content
7. Implement `components/seo-head.blade.php` with all meta tag slots
8. Verify `npm run dev` HMR works; `npm run build` produces output

**Dependency:** None — can begin immediately.

### Phase 2 — Global UI (Parallel with Backend Phase 3–4)

**Goal:** Complete site header, navigation, footer, and shared components working.

Tasks:
1. Implement `partials/nav.blade.php` and `partials/mobile-nav.blade.php`
2. Implement `navigation.js` (mobile toggle, keyboard trap, Escape close)
3. Style header, navigation, and footer with Tailwind utilities and the shared `@theme` tokens
4. Implement `components/cta-whatsapp.blade.php`
5. Implement `components/social-links.blade.php`
6. Implement `components/alert.blade.php`
7. Implement View Composer injection of settings into layout (requires SettingService ready)
8. Verify RTL layout is correct for header and navigation

**Dependency:** Backend Phase 1 (settings seeded); SettingService working.

### Phase 3 — Home and Car Pages (After Backend Phase 4–5)

**Goal:** Home, Car Listing, and Car Detail pages fully implemented.

Tasks:
1. Implement `components/car-card.blade.php` with Tailwind utilities
2. Implement `pages/home.blade.php` — hero banners, featured cars, FAQ preview, CTAs
3. Implement `components/car-gallery.blade.php` and `gallery.js`
4. Implement `components/car-specs.blade.php` and `components/feature-list.blade.php`
5. Implement `pages/cars/index.blade.php` — filter bar, car grid, pagination
6. Implement `components/pagination.blade.php` and `components/breadcrumbs.blade.php`
7. Implement `filters.js` for category filter URL updates
8. Implement `pages/cars/show.blade.php` — full car detail page
9. Implement hero banner carousel if required by design (`carousel.js`)

**Dependency:** Backend Phase 4 (Filament resources) — real cars exist in database; Phase 5 (media) — car images upload.

### Phase 4 — Content Pages

**Goal:** FAQ, About, Contact, Booking pages implemented.

Tasks:
1. Implement `components/faq-item.blade.php` using `<details>/<summary>`
2. Implement `faq.js` for accordion animation enhancement
3. Implement `pages/faq.blade.php` with FAQ grouping by category
4. Implement `pages/about.blade.php`
5. Implement `pages/contact.blade.php` with contact form
6. Implement `pages/booking.blade.php` with car dropdown pre-population
7. Implement `whatsapp.js` — complete message generation and redirect
8. Test WhatsApp flow end-to-end with a real phone

**Dependency:** Backend Phase 3 (all services); WhatsApp number set in settings.

### Phase 5 — Responsive and RTL Polish

**Goal:** All pages work correctly at all screen sizes in RTL.

Tasks:
1. Apply responsive styles to all pages (mobile-first media queries)
2. Verify all logical CSS properties render correctly in RTL
3. Fix any LTR-specific styles discovered during testing
4. Verify mobile navigation at all breakpoints
5. Test all forms on mobile
6. Verify car grid at all breakpoints
7. Verify car gallery on mobile

**Dependency:** Phase 3–4 complete.

### Phase 6 — SEO and Accessibility

**Goal:** All SEO metadata correct; accessibility audit passes.

Tasks:
1. Complete all `@section('title')`, `@section('meta_description')`, `@section('canonical')` for every page
2. Add OG and Twitter Card tags
3. Implement JSON-LD structured data (Organization, BreadcrumbList, FAQPage)
4. Create sitemap controller and `GET /sitemap.xml` route
5. Create `public/robots.txt`
6. Run axe accessibility audit; fix all critical and major issues
7. Test keyboard navigation on all pages
8. Verify all images have `alt` text
9. Verify form labels and error states

**Dependency:** Phase 3–4 complete.

### Phase 7 — Performance and Production

**Goal:** Production-ready build; Lighthouse targets met.

Tasks:
1. Run `npm run build` and verify asset hashes
2. Run Lighthouse on Home, Car Listing, and Car Detail pages
3. Optimize any images above 800 KB (instruct admin or convert via Spatie in V2)
4. Verify `loading="lazy"` and `loading="eager"` are correctly applied
5. Add font preloading in `<head>`
6. Verify `prefers-reduced-motion` behaviour in carousel
7. Remove any development-only JavaScript (`console.log`)
8. Final cross-browser testing on Chrome, Firefox, Safari, iOS Safari, Android Chrome

**Dependency:** Phase 5–6 complete.

---

## 22. Backend/Frontend Responsibility Boundary

This boundary defines who owns what. Neither side reaches into the other's domain.

| Responsibility | Owner | Must NOT be done by |
|---------------|-------|---------------------|
| Database queries | Backend | Blade views |
| Business logic (published/featured filtering) | Backend Services | Blade views or JavaScript |
| Settings retrieval and caching | `SettingService` | JavaScript, Blade direct DB |
| Car data retrieval | `CarService` | Blade, JavaScript |
| Media URL generation | Spatie / `Storage::url()` in controller/resource | Frontend JS |
| Authentication (admin) | Filament | Frontend |
| Form POST validation | Backend `FormRequest` | Only JavaScript (JS = convenience) |
| API endpoint definitions | `API_Contract.md` | Frontend plan |
| WhatsApp message generation | `whatsapp.js` | Backend (no server-side submission) |
| Page routing | `routes/web.php` | JavaScript |
| HTML structure | Blade templates | JavaScript DOM manipulation |
| CSS / visual styling | Tailwind utilities, `@theme`, and minimal custom CSS | Backend controllers |
| Accessibility attributes | Blade + CSS + JS | Backend |
| SEO meta tag values | Blade templates (data from backend) | JavaScript (not SSR-friendly) |
| Image upload and storage | Spatie / `MediaService` | Frontend |
| WhatsApp number source of truth | `settings` table / `SettingService` | Hardcoded in JS |
| User-visible contact info | Settings (backend) | Hardcoded in Blade/JS |

---

## 23. Future Extension Considerations

> This section explains V2 extension points only. No V2 code is created.

| V2 Feature | Frontend Extension |
|-----------|-------------------|
| Customer accounts | New Blade pages: login, register, profile; new route group `/account/*` |
| Online booking with availability | New booking form with date picker and availability API call; new `/booking/confirm` route |
| Customer dashboard | New Blade MPA section: `/account/bookings`, `/account/profile` |
| Real-time availability | `IntersectionObserver` + `fetch()` to `/api/v2/cars/{slug}/availability`; only this feature justifies API consumption from Blade |
| Payment UI | New multi-step Blade form or a redirect to a hosted payment page |
| Car reviews | New `<section>` on car detail page; reviews loaded from backend |
| Multi-language | Add `lang` toggle in header; Laravel localization (`__()` function in Blade); separate Arabic/English CSS if needed |
| Push notifications | Service Worker — first JS architecture change that approaches PWA territory |

**V1 architecture handles V2 gracefully:**
- The Blade MPA structure (one route per page) is additive — new pages are new routes + new views
- CSS custom properties make theme changes (for a booking UI) straightforward
- JavaScript modules are isolated — new modules can be added to `app.js` without touching existing modules
- The View Composer pattern means all new Blade pages automatically get settings data

---

## 24. Final Frontend Checklist

Before declaring V1 frontend complete, verify all items:

### Architecture
- [ ] All pages are server-rendered Blade
- [ ] No SPA framework in the codebase
- [ ] No client-side routing
- [ ] No `fetch()` or `axios()` calls to `/api/*` from Blade pages for page rendering
- [ ] All public routing is in `routes/web.php`

### RTL / Arabic
- [ ] `<html lang="ar" dir="rtl">` on every page
- [ ] All CSS uses logical properties (no hard-coded `left`/`right`)
- [ ] Arabic font loaded and applied
- [ ] RTL layout verified at mobile, tablet, and desktop
- [ ] Arabic content rendered correctly in all components

### Pages
- [ ] Home page — banners, featured cars, FAQ preview, CTAs
- [ ] Car listing — category filter, search, pagination, car cards
- [ ] Car detail — gallery, specs, features, breadcrumbs, WhatsApp CTA
- [ ] About — company content from settings
- [ ] FAQ — accordion with all active FAQs, grouped by category
- [ ] Contact — phone, email, WhatsApp, social links, optional form
- [ ] Booking — car dropdown, all fields, WhatsApp message generation

### Data Integrity
- [ ] Pricing fields (`price_daily/weekly/monthly`) never displayed publicly
- [ ] `is_published`, `sort_order` never displayed publicly
- [ ] Car images sourced only from `getMedia('car_images')` URLs
- [ ] Company settings sourced from SettingService via View Composer
- [ ] WhatsApp number sourced from settings (never hardcoded)

### WhatsApp Flow
- [ ] Booking form does not POST to any server
- [ ] `whatsapp.js` reads number from `data-*` attribute
- [ ] Message constructed in Arabic
- [ ] `encodeURIComponent()` applied to message
- [ ] `wa.me/{number}?text={encoded}` URL correct
- [ ] Null WhatsApp number disables button gracefully
- [ ] Fallback phone contact shown when WhatsApp is unavailable

### SEO
- [ ] Unique `<title>` on every page
- [ ] Unique `<meta name="description">` on every page
- [ ] `<link rel="canonical">` on every page
- [ ] OG tags on every page
- [ ] `<html lang="ar">` set
- [ ] JSON-LD: `Organization` in site layout
- [ ] JSON-LD: `BreadcrumbList` on car detail and FAQ pages
- [ ] JSON-LD: `FAQPage` on FAQ page
- [ ] `/sitemap.xml` returns valid XML with all public pages
- [ ] `/robots.txt` disallows `/admin` and `/api`
- [ ] Semantic heading hierarchy (`<h1>` once per page)
- [ ] All navigation links are `<a href>` (not JavaScript-only)

### Accessibility
- [ ] All interactive elements keyboard-accessible
- [ ] Visible `:focus-visible` styles on all focusable elements
- [ ] All images have `alt` text (or `alt=""` for decorative)
- [ ] All form inputs have associated `<label>` elements
- [ ] Mobile navigation focus-trapped when open
- [ ] `prefers-reduced-motion` respected in carousel
- [ ] axe audit: zero critical issues

### Performance
- [ ] `loading="lazy"` on all below-the-fold images
- [ ] `loading="eager"` on hero/cover images
- [ ] `width` and `height` on all `<img>` elements
- [ ] No car image above 800 KB served
- [ ] JS bundle ≤ 15 KB gzipped (verify with `npm run build`)
- [ ] CSS bundle ≤ 30 KB gzipped
- [ ] Lighthouse mobile performance ≥ 85

### Security
- [ ] No pricing data in any Blade template output
- [ ] No admin data in any Blade template output
- [ ] `{{ }}` used for all user-facing output (not `{!! !!}` except admin-sourced rich text)
- [ ] All external links have `rel="noopener noreferrer"`
- [ ] CSRF `@csrf` on contact form

---

**Document Metadata:**

| Field | Value |
|-------|-------|
| Document Name | Frontend_Development_Plan.md |
| Version | 1.1.0 |
| Created | August 2026 |
| Sources | System_Architecture_Plan.md v1.6.0, Database_Design.md v1.2.0, API_Contract.md v1.2.0, Backend_Development_Plan.md v1.1.0 |
| Status | Ready for Implementation |
| Phase Count | 7 phases, coordinated with backend 9-phase plan |
| Next Document | — |
