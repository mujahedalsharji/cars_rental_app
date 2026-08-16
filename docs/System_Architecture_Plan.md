# System Architecture Plan
## Car Rental Website — Version 1

**Document Version:** 1.5.0
**Date:** August 2026
**Status:** Final — Approved for Implementation
**Classification:** Internal Architecture Document

---

> **Prepared by:** Senior Software Architecture Team
> **Target Implementation Team:** Laravel Full-Stack Developers
> **Review Cycle:** Per major version release

---

## Table of Contents

1. [Project Vision](#1-project-vision)
2. [Business Goals](#2-business-goals)
3. [Functional Scope](#3-functional-scope)
4. [Non-Functional Requirements](#4-non-functional-requirements)
5. [Overall System Architecture](#5-overall-system-architecture)
6. [High-Level Architecture Diagram (ASCII)](#6-high-level-architecture-diagram-ascii)
7. [Layers and Responsibilities](#7-layers-and-responsibilities)
8. [Module Breakdown](#8-module-breakdown)
9. [Folder Structure](#9-folder-structure)
10. [Request Flow](#10-request-flow)
11. [API Flow](#11-api-flow)
12. [Admin Dashboard Architecture](#12-admin-dashboard-architecture)
13. [Public Website Architecture](#13-public-website-architecture)
14. [Future Expansion Strategy](#14-future-expansion-strategy)
15. [Security Architecture](#15-security-architecture)
16. [File Storage Strategy](#16-file-storage-strategy)
17. [Logging Strategy](#17-logging-strategy)
18. [Error Handling Strategy](#18-error-handling-strategy)
19. [Scalability Strategy](#19-scalability-strategy)
20. [Deployment Architecture](#20-deployment-architecture)
21. [Development Phases](#21-development-phases)
22. [Architectural Decisions](#22-architectural-decisions)
23. [Trade-offs](#23-trade-offs)
24. [Future Migration Strategy](#24-future-migration-strategy)
25. [Appendix](#25-appendix)

---

## 1. Project Vision

### 1.1 Executive Summary

The Car Rental Website (Version 1) is a production-grade marketing platform designed to digitize the public face of a car rental business. Its primary function is to present the company's fleet to prospective customers, communicate brand identity, and channel customer inquiries directly through WhatsApp — the preferred communication medium in many regional markets.

This is not a reservation system. Version 1 deliberately avoids online booking, payment processing, or customer account management. Instead, it acts as an intelligent digital brochure: beautifully presented, administratively manageable, and technically structured to evolve into a full-scale Car Rental Management System (CRMS) in Version 2 without requiring a rewrite of the foundational codebase.

### 1.2 The Two-Phase Philosophy

The architecture is governed by a deliberate two-phase design philosophy:

**Phase 1 (Version 1) — Marketing Platform:**
Establish a clean, performant, and fully manageable public website. Use pragmatic Laravel conventions with clear service boundaries so that Phase 2 expansion is additive rather than disruptive.

**Phase 2 (Version 2) — Full CRMS:**
Add booking, payment, customer accounts, fleet management, driver management, branch management, maintenance tracking, notifications, and analytics as independent modules that integrate into the existing architecture without touching Phase 1 code.

This philosophy is borrowed from the concept of "Evolutionary Architecture" — design the system so that it can absorb planned future changes with minimal structural disruption.

### 1.3 Vision Statement

> *"To build a technically superior, maintainable, and scalable digital presence for a car rental business that serves customers effectively today and evolves into a complete management platform tomorrow — without architectural debt."*

---

## 2. Business Goals

### 2.1 Primary Business Goals (Version 1)

| ID  | Goal | Priority | Success Metric |
|-----|------|----------|----------------|
| BG-01 | Showcase the vehicle fleet online to attract new customers | Critical | Fleet fully browsable online |
| BG-02 | Convert website visitors into WhatsApp inquiries | Critical | WhatsApp redirect functional for every car |
| BG-03 | Allow non-technical admin staff to manage all website content | Critical | Admin panel requires zero code changes |
| BG-04 | Establish brand credibility through professional online presence | High | Design & content rating by stakeholders |
| BG-05 | Reduce manual effort in answering repetitive customer questions | High | FAQ section live and manageable |
| BG-06 | Enable admin to update company info, contacts, and social links | High | All contact info editable via admin |

### 2.2 Strategic Business Goals (Version 2 Readiness)

Module boundaries and extension points are documented to allow future implementation without architectural redesign. No V2 database schemas or migrations are created in Version 1.

| ID  | Goal |
|-----|------|
| SBG-01 | Launch online booking system without rewriting the platform |
| SBG-02 | Accept online payments |
| SBG-03 | Manage multiple branches |
| SBG-04 | Track fleet availability and maintenance |
| SBG-05 | Onboard customers with accounts |
| SBG-06 | Manage drivers and assignments |

### 2.3 Technical Business Goals

- Achieve sub-2-second page load time for all public-facing pages.
- Maintain 99.5% uptime for the public website.
- Support a single administrator account in Version 1, expandable to role-based access in Version 2.
- Keep hosting costs minimal by supporting shared or VPS hosting initially.
- Produce a codebase that can be handed to any competent Laravel developer for maintenance.

---

## 3. Functional Scope

### 3.1 Version 1 — In Scope

#### 3.1.1 Public Website

**Home Page**
- Hero banner (image, headline, CTA button)
- Featured cars section (admin-configurable)
- Brief company introduction
- FAQ highlights
- Contact/WhatsApp CTA

**Cars Listing Page**
- Grid/list view of all available cars
- Filter by category
- Search by car name or keyword
- Pagination

**Car Details Page**
- Car name, brand, model, year
- Full specification table (engine, transmission, fuel type, seats, etc.)
- Features list (A/C, GPS, Bluetooth, etc.)
- Image gallery (multiple images with carousel)
- "Book via WhatsApp" inquiry button (links to Pre-Booking Inquiry Form)
- "Call Now" button

> **Note on pricing:** Rental prices are not displayed on the website. Pricing is discussed and agreed through the WhatsApp conversation between the customer and the company.

**Pre-Booking Inquiry Form (الحجز المسبق)**

This is a dedicated page (`/booking`) — a server-rendered Blade page — where the customer submits a pre-booking inquiry. The controller pre-loads the car list and WhatsApp number server-side and passes them to the Blade template. The form collects trip-specific details and redirects to WhatsApp with a structured pre-filled message.

Form fields:
- Car selection (dropdown pre-populated server-side by Blade from the published cars list; pre-selected when the request includes `?car=bmw-5-series`)
- Trip road / destination (text input, e.g., "Riyadh → Jeddah")
- Customer Name (required)
- Customer Phone (required)
- Pickup Date (required)
- Return Date (required)
- Additional Notes (optional)

Flow:
1. Customer navigates to booking page (via CTA on homepage, cars listing, or car detail)
2. `BookingController@show` loads the published car list and the WhatsApp number from `SettingService`, passes them to the Blade template
3. If the request includes `?car=bmw-5-series`, the controller pre-selects that car and passes it to the view
4. Blade renders the complete form server-side with all data embedded in HTML
5. JavaScript validates the form client-side on submit
6. JavaScript generates a structured WhatsApp message string from form values
7. Browser redirects to `https://wa.me/{company_number}?text={encoded_message}`
8. No booking data is stored in the database
9. No API call is made at any point in this flow

**About Us Page**
- Company story, mission, vision
- Team section (optional)
- Why choose us section

**FAQ Page**
- Accordion-style questions and answers
- Category-grouped FAQs (optional)

**Contact Us Page**
- Company phone number(s)
- Email address
- Physical address
- Social media links
- WhatsApp direct contact button

#### 3.1.2 Admin Dashboard

The admin dashboard is built with **Laravel Filament v5**. Filament generates a fully-featured, server-rendered admin panel directly from Eloquent models — no custom HTML, CSS, or JavaScript required. The admin panel lives at `/admin` and is served by the same Laravel application.

**Authentication**
- Filament's built-in admin authentication (email + password)
- Separate `admins` guard — isolated from public API
- Logout and password change via Filament profile panel

**Dashboard Overview** (Filament Widgets)
- Stats overview: total cars, categories, FAQs, banners
- Recently added cars table widget

**Car Management** (Filament Resource: `CarResource`)
- Full CRUD: Create, Edit, Delete (hard delete)
- Table with columns: Name, Category, Status, Featured, Date
- Filters: by category, by published status, by featured
- Toggle published/draft status inline
- Toggle featured flag inline
- Image gallery management (Spatie Media Library integration)
- Multi-image upload with cover image selection

**Category Management** (Filament Resource: `CategoryResource`)
- Full CRUD with form fields: Name, Slug (auto), Description, Icon, Sort Order, Active toggle

**FAQ Management** (Filament Resource: `FaqResource`)
- Full CRUD with reordering via `sort_order` field
- Toggle active/inactive per item

**Hero Banner Management** (Filament Resource: `BannerResource`)
- Full CRUD with image upload
- Sort order and active toggle

**Settings Management** (Filament Custom Page: `ManageSettings`)
- Tabbed form: Company | Contact | Social | SEO | Appearance | System
- Each tab reads from and writes to the `settings` key-value table
- Logo and favicon file upload with preview

**Company & Contact Information**
- Managed through the Settings page tabs
- Fields: company name, tagline, description, phone, email, address, WhatsApp number, social links

### 3.2 Version 2 — Out of Scope for V1

The following features are explicitly NOT implemented in Version 1. No database tables, migrations, or models for these features exist in V1. They will be added as new modules in Version 2:

- Online Booking System
- Payment Gateway Integration
- Customer Accounts and Authentication
- Booking Management Dashboard
- Fleet Availability Calendar
- Driver Management
- Vehicle Maintenance Tracking
- Invoices and Receipts
- Coupon and Discount System
- Push Notifications and Email Notifications
- Branch Management
- Analytics Dashboard
- Reports and Exports

---

## 4. Non-Functional Requirements

### 4.1 Performance

| Requirement | Target | Strategy |
|-------------|--------|----------|
| Public page load time (FCP) | < 2 seconds | Data/query caching (Redis), image optimization, CDN-ready assets |
| API response time (p95) | < 300ms | Query optimization, eager loading, indexing |
| Time to First Byte (TTFB) | < 200ms | Server-side caching, optimized hosting |
| Image delivery | < 500ms per image | Thumbnails generated on upload, lazy loading |
| Admin panel response | < 1 second | Acceptable — admin is internal |

### 4.2 Reliability

- **Uptime Target:** 99.5% monthly (allows ~3.6 hours/month downtime)
- **Database Backup:** Automated daily backups with 30-day retention
- **Error Recovery:** All API errors return structured JSON responses; frontend handles gracefully
- **Hard Deletes:** Records are permanently removed on admin delete. If a car is accidentally deleted, it can be re-added manually by the admin.

### 4.3 Security

- **Authentication:** Only admin login in V1; Filament session-based authentication (no Sanctum tokens in V1)
- **Authorization:** Filament middleware protects all `/admin/*` routes; public REST API routes are read-only, no auth required
- **Input Validation:** All inputs validated at Controller and Form Request layer
- **SQL Injection:** Prevented by Eloquent ORM; raw queries prohibited
- **XSS:** Output escaped at frontend; API returns raw data only
- **File Upload Security:** MIME type validation, size limits, no executable uploads
- **Rate Limiting:** Applied on public contact form and API endpoints
- **HTTPS:** Enforced at web server level; HTTP redirected to HTTPS

### 4.4 Maintainability

- **Code Standards:** PSR-12 coding standards enforced
- **Documentation:** Every service class and model documented with DocBlocks
- **Separation of Concerns:** Business logic in service classes; controllers are thin
- **Testability:** Services are testable in isolation with dependency injection
- **Naming Conventions:** Consistent naming for routes, classes, methods, and database columns

### 4.5 Scalability

- **Horizontal Scaling:** Stateless API design allows adding application servers
- **Database Scaling:** Read replicas supported through Laravel's read/write connection config
- **Cache Layer:** Redis supported for query and response caching
- **Queue Workers:** Laravel Queue configured for async tasks (e.g., thumbnail generation)
- **Storage:** Local storage abstracted behind `Storage` facade; cloud switch requires config change only

### 4.6 Extensibility

- **Module Boundaries:** Each feature domain (cars, categories, FAQ, settings) has its own controller, service, and model
- **API Versioning:** No version prefix in V1 — all public REST API routes under `/api/`. A version prefix is introduced only when breaking changes require backward compatibility for existing external consumers.
- **Event System:** Laravel Events and Listeners available for future integrations

### 4.7 Accessibility

- **WCAG 2.1 Level AA** compliance for public-facing pages
- Semantic HTML structure
- Alt text for all images
- Keyboard-navigable UI
- Sufficient color contrast ratios

### 4.8 Internationalisation (i18n)

- All user-facing strings stored in Laravel language files
- Database content stored in single language (V1)
- Architecture supports multi-language content via JSON columns or translation tables in V2

---

## 5. Overall System Architecture

### 5.1 Architectural Style

The system adopts a **Pragmatic Layered Architecture** combined with a **Modular Monolith** pattern. The layers are:

1. **HTTP Layer** — Routes, Middleware, Controllers, Form Requests, API Resources
2. **Application Layer** — Service classes containing all business logic
3. **Data Layer** — Eloquent Models interacting directly with the database

This is standard Laravel architecture followed by convention, extended with clear service boundaries to support future growth.

**Why not a full Clean Architecture with Repositories and Domain Entities?**
For a Version 1 marketing website, the overhead of Domain Entities, Value Objects, and Repository Interfaces adds significant file count and complexity without delivering proportional benefit at this scale. Laravel Eloquent's expressive ORM provides sufficient abstraction. If the application grows to require multiple data sources or complex domain logic, the Repository Pattern can be introduced incrementally without a full rewrite, because service classes already act as the isolation boundary between HTTP and data concerns.

**Why Modular Monolith over Microservices?**
For a Version 1 marketing website operated by a small team, microservices introduce operational overhead (service discovery, inter-service communication, distributed tracing) that does not return value at this scale. A Modular Monolith gives the code organisation benefits of microservices (clear module boundaries, loose coupling) while maintaining the simplicity of a single deployable unit. When Version 2 requires it, individual modules can be extracted.

### 5.2 Deployment Model

Single-server deployment for Version 1:
- One VPS or shared hosting server
- NGINX as web server and reverse proxy
- PHP-FPM process manager
- MySQL on the same server (or separate DB server)
- Redis for caching and session management
- Laravel Queue worker process managed by Supervisor

### 5.3 Communication Model

- **Public Frontend → Backend:** Laravel Blade renders all public pages server-side — controllers call Service classes directly, no HTTP API round-trip
- **Admin (Filament) → Backend:** Server-rendered Livewire/Blade — Filament talks **directly to Eloquent** (no HTTP API round-trip; same process)
- **Backend → Storage:** Laravel Storage Facade (local disk, S3-compatible future)
- **Backend → WhatsApp:** Client-side URL redirect (no server involvement)
- **Backend → Email:** SMTP via Laravel Mail (optional contact form)

> **Key distinction:** Neither the public website (Blade MPA) nor the admin panel (Filament) are REST API consumers. Both communicate directly with the Service and Eloquent layers within the same Laravel process. The REST API (`/api/*`) exists exclusively for future external consumers — Flutter/mobile apps, third-party integrations.

### 5.4 Data Flow Overview

All data enters the system through:
1. Blade page requests (public reads — server-side rendered by the Service layer)
2. Filament form submissions (admin writes — via Livewire, server-side)
3. File uploads (admin images via Filament + Spatie Media Library)
4. REST API HTTP requests (future external consumers — `/api/*` routes)

All data exits the system through:
1. Server-rendered Blade HTML (public pages — controller passes data to view)
2. WhatsApp URL redirects (client-side only — booking form submit)
3. Filament admin pages (server-rendered Livewire/Blade)
4. HTTP JSON responses (public REST API — for future external consumers)

### 5.5 State Management

- **Admin Session:** Filament uses Laravel's standard session (cookie-based, server-side) — no Sanctum tokens needed for admin
- **Public State:** No server-side session for public users — fully stateless public API
- **Application Cache:** Redis stores API response cache, settings cache, car listing cache
- **Database State:** MySQL is the single source of truth for all persistent data

---

## 6. High-Level Architecture Diagram (ASCII)

```
================================================================
            CAR RENTAL SYSTEM — V1 ARCHITECTURE
================================================================

+------------------------------------------------------------------------+
|                       CLIENT LAYER (Browser)                           |
|                                                                        |
|   +--------------------------------+  +-----------------------------+  |
|   |   PUBLIC WEBSITE (Blade MPA)   |  | ADMIN DASHBOARD (Filament)  |  |
|   |                                |  |                             |  |
|   |   Laravel Blade Templates      |  |  Laravel Filament v5        |  |
|   |   Server-rendered by Laravel   |  |  Livewire / Blade           |  |
|   |                                |  |  Server-rendered UI         |  |
|   |   Pages (routes/web.php):      |  |                             |  |
|   |   / (home)                     |  |  Resources:                 |  |
|   |   /cars                        |  |  - CarResource              |  |
|   |   /cars/{slug}                 |  |  - CategoryResource         |  |
|   |   /about                       |  |  - FaqResource              |  |
|   |   /faq                         |  |  - BannerResource           |  |
|   |   /contact                     |  |  Custom Pages:              |  |
|   |   /booking                     |  |  - ManageSettings           |  |
|   +---------------+----------------+  +----------+------------------+  |
+-------------------|---------------------------------|------------------+
                    |  HTTP (Blade server-rendered)   | Livewire HTTP
                    |  Controller → Service → Eloquent| (full page + AJAX)
+-------------------V-----------------V-----------------------------------+
|                          WEB SERVER LAYER (NGINX)                       |
|                                                                         |
|   NGINX => PHP-FPM => Laravel Application                               |
|   - SSL Termination (Let's Encrypt)                                     |
|   - Static Asset Serving (CSS, JS, images from /public)                 |
|   - Rate Limiting                                                       |
|   - /*     => PHP-FPM (Blade MPA — public pages)                        |
|   - /api/* => PHP-FPM (REST API — future external consumers)            |
|   - /admin/* => PHP-FPM (Filament — Livewire/Blade server-rendered)     |
+------------------------------------+------------------------------------+
                                    |
+-----------------------------------V------------------------------------+
|                      LARAVEL APPLICATION LAYER                         |
|                                                                        |
|  +------------------------------------------------------------------+  |
|  |                        HTTP LAYER                                |  |
|  |   Routes => Middleware => FormRequest => Controller              |  |
|  +-------------------------------+----------------------------------+  |
|                                  |                                     |
|  +-------------------------------V----------------------------------+  |
|  |                  APPLICATION LAYER (Services)                    |  |
|  |   CarService, CategoryService, FaqService, SettingService        |  |
|  |   MediaService, WhatsAppService                                  |  |
|  +-------------------------------+----------------------------------+  |
|                                  |                                     |
|  +-------------------------------V----------------------------------+  |
|  |                     DATA LAYER (Eloquent)                        |  |
|  |   Car, Category, Faq, Banner, Setting, Admin models              |  |
|  |   Relationships, Scopes, Casts                                   |  |
|  +-------------------------------+----------------------------------+  |
+----------------------------------+-------------------------------------+
                                   |
+--------------------+ +-----------+----------+ +----------------------+
|    MySQL Database  | |    Redis Cache        | |   File Storage       |
|                    | |                       | | (Local / S3 Future)  |
|  - admins          | |  - API Responses      | |                      |
|  - categories      | |  - Settings           | |  - Car Images        |
|  - cars            | |  - Sessions           | |  - Banners           |
|  - car_features    | |  - Rate Limits        | |  - Company Logo      |
|  - media (Spatie)  | +-----------------------+ |  - Favicon           |
|  - faqs            |                           +----------------------+
|  - banners         | +-------------------------------------------------+
|  - settings        | |       QUEUE WORKER (Supervisor)                 |
+--------------------+ |  - Image thumbnail generation (async)           |
                       |  - Email sending (contact form)                 |
                       +-------------------------------------------------+

================================================================
```

---

## 7. Layers and Responsibilities

### 7.1 Layer Overview

The application is structured into three practical layers. Each layer has a single responsibility:

```
+-------------------------------------------+
|          HTTP Layer                       |  Routes, Middleware, Controllers, Resources
+-------------------------------------------+
|          Application Layer               |  Service Classes (business logic)
+-------------------------------------------+
|          Data Layer                      |  Eloquent Models (database access)
+-------------------------------------------+
```

### 7.2 HTTP Layer

**Location:** `app/Http/`

**Responsibilities:**
- Receive HTTP requests from clients
- Parse and validate incoming request data (FormRequest classes)
- Authenticate and authorize the request (Middleware, Policies)
- Delegate business operations to Service classes
- Transform output into JSON API responses (API Resources)
- Handle HTTP-specific concerns only (status codes, headers)

**Components:**
- `Routes/api.php` — All API route definitions, grouped and versioned
- `Http/Controllers/` — Thin controllers that call one service method per action
- `Http/Requests/` — Form Request classes with validation rules
- `Http/Resources/` — API Resource transformers (define the JSON output shape)
- `Http/Middleware/` — Request interceptors (auth, rate limiting, CORS, maintenance mode)

**What does NOT belong here:**
- Business logic of any kind
- Database queries
- File processing
- Email sending
- Any decision-making beyond "is this request valid and who is making it?"

**Design Principle:** Controllers are thin. A controller method should do no more than: validate the request, call one service method, and return a resource response.

### 7.3 Application Layer

**Location:** `app/Services/`

**Responsibilities:**
- Contain all business logic and workflow orchestration
- Use Eloquent models to read and write data
- Trigger Laravel events when significant state changes occur
- Delegate file operations to `MediaService`
- Keep controllers unaware of database structure

**Components:**
- `Services/CarService.php` — All car-related business operations
- `Services/CategoryService.php` — Category management
- `Services/FaqService.php` — FAQ management
- `Services/BannerService.php` — Hero banner management
- `Services/SettingService.php` — Website settings and company info
- `Services/MediaService.php` — File upload and image processing
- `Services/WhatsAppService.php` — WhatsApp URL generation helper
- `Services/Admin/AuthService.php` — Admin login/logout logic

**Design Principle:** Services are the only place in the application where business decisions are made. A service method reads from the database, applies business rules, writes results, and fires events. No business logic leaks into controllers or models.

### 7.4 Data Layer

**Location:** `app/Models/`

**Responsibilities:**
- Define Eloquent relationships between tables
- Define query scopes for common filters (e.g., `scopePublished`)
- Define attribute casts (e.g., `specifications` as array, `is_published` as boolean)
- Handle soft deletes

**Components:**
- `Models/Admin.php` — Admin user model
- `Models/Car.php` — Car model with category, features, and Spatie media relationship; implements `HasMedia`
- `Models/CarFeature.php` — Car feature model
- `Models/Category.php` — Category model
- `Models/Faq.php` — FAQ model
- `Models/Banner.php` — Banner model
- `Models/Setting.php` — Settings model

> **Note:** There is no `CarImage.php` or custom `Media.php` model. Spatie Media Library provides its own `Media` Eloquent model. The `Car` model uses Spatie's `HasMedia` trait.

**What belongs in a model:** Relationships, scopes, casts, accessors/mutators. **What does NOT belong:** Business logic, validation, or direct HTTP concerns.

### 7.5 Cross-Cutting Concerns

These are concerns that apply across all layers:

| Concern | Tool | Location |
|---------|------|----------|
| Logging | Laravel Log | Service classes |
| Error Handling | Laravel Exception Handler | `app/Exceptions/Handler.php` |
| Admin Authentication | Filament session-based (Laravel session) | Filament middleware |
| Caching | Redis via Laravel Cache | Service layer |
| Validation | Form Request classes | HTTP layer |
| Events | Laravel Events/Listeners | Service layer |

---

## 8. Module Breakdown

Each module is a self-contained feature domain with its own controller, service, and model.

### 8.1 Cars Module

**Purpose:** Manages the vehicle fleet displayed to public users and managed by admin.

**Public responsibilities:**
- List all published cars with filters (category, search, pagination)
- Retrieve single car with all images, specs, features, and category

**Admin responsibilities:**
- Create a car with full metadata
- Update all car fields
- Delete a car permanently (hard delete)
- Toggle published/draft status
- Set featured flag for homepage display

**Eloquent Relationships:**
```
Car
+-- belongs to --> Category
+-- has many   --> CarFeature
+-- morph many --> Media (Spatie Media Library — collection: 'car_images')
```

**Database Tables:**
- `cars` — Core car data
- `car_features` — Feature tags per car (e.g., "Air Conditioning", "GPS")
- `categories` — Car categories (SUV, Sedan, Luxury, etc.)
- `media` — Spatie Media Library table (schema owned by Spatie's migration; not hand-written)

**Key Design Decisions:**
- Car specifications stored as JSON column in V1 (flexible schema, avoids spec table explosion)
- Car features stored as a separate table for future filterability
- Hard delete on cars: records are permanently removed. Accidental deletes are recovered by re-adding the car
- `slug` column for SEO-friendly URLs
- `is_published` and `is_featured` flags for content control

**V2 Extension Points (additive migrations — no changes to V1 code):**
- `availability_status` column (Available, Rented, Maintenance) — added in V2
- `branch_id` foreign key — added in V2
- `license_plate`, `vin` columns for fleet tracking — added in V2

### 8.2 Categories Module

**Purpose:** Organises cars into browsable groups.

**Responsibilities:**
- CRUD operations on categories
- Provide category list for car filtering

**Eloquent Relationships:**
```
Category
+-- has many --> Car
```

**Database Tables:**
- `categories` — id, name, slug, description, icon, image, is_active, sort_order

### 8.3 FAQ Module

**Purpose:** Provides a customer-facing FAQ section and an admin content management interface.

**Responsibilities:**
- CRUD on FAQ items
- Optional category grouping for FAQs
- Reordering
- 

**Database Tables:**
- `faqs` — id, question, answer, category (nullable string), is_active, sort_order

### 8.4 Banner Module

**Purpose:** Manages the hero slider/banners displayed on the homepage.

**Responsibilities:**
- CRUD on banners
- Image upload per banner
- Active/inactive toggle
- Sort order management

**Database Tables:**
- `banners` — id, title, subtitle, image, cta_text, cta_url, is_active, sort_order

### 8.5 Settings Module

**Purpose:** Centralised management of all website configuration, company information, contact details, and social links.

**Architecture Decision:** All settings are stored in a key-value store pattern using a single `settings` table.

**Justification:** Settings are read far more often than they are written. A key-value table allows unlimited settings to be added without schema migrations. Settings are loaded and accessed through a `SettingService` with Redis caching. New settings added by future modules require no database migration — only a new key.

**Database Tables:**
- `settings` — id, key (unique), value (text), type (string/boolean/json/integer), settings_group, description

**Setting Groups (V1):**
- `company` — name, tagline, description, logo, about_text
- `contact` — phone_primary, phone_secondary, email, address, whatsapp_number
- `social` — facebook_url, instagram_url, twitter_url, youtube_url, tiktok_url, linkedin_url
- `seo` — site_title, meta_description, meta_keywords, google_analytics_id
- `appearance` — favicon, primary_color, secondary_color
- `system` — maintenance_mode, app_locale

### 8.6 Media Module

**Purpose:** Handles all file uploads, storage, and retrieval across the application.

**Why a separate service?**
Media handling requires consistent validation, filename generation, storage, and URL resolution logic. Centralising this in a `MediaService` prevents code duplication.

**Responsibilities:**
- Validate uploaded files (MIME type, size, extension)
- Generate public URLs
- Coordinate deletion of files when records are removed

**Implementation:** `spatie/laravel-medialibrary` package. The `media` database table is created by Spatie's own published migration — it is not hand-written. The `Car` model implements `HasMedia` and uses `InteractsWithMedia`. Image uploads in Filament use `SpatieMediaLibraryFileUpload`.

**Scope in V1:** Spatie Media Library is used for car images only (multiple images per car, with ordering and cover selection). Banners and categories store their single image as a VARCHAR path column — no polymorphic media needed for single-image entities.

### 8.7 Admin Authentication Module

**Purpose:** Manages admin identity and access to the Filament dashboard.

**V1 Scope:** Single admin account, email/password login. Authentication is handled by Filament's built-in panel authentication — no Sanctum tokens involved for admin access.

**Implementation:** The `Admin` model implements Filament's `HasPanel` interface. Filament manages the login page at `/admin/login`, session handling, and logout natively.

**Database Tables:**
- `admins` — id, name, email, password, remember_token, last_login_at, last_login_ip, created_at

**Why a separate `admins` table and not `users`?**
In Version 2, the `users` table will hold customer accounts. Mixing admin and customer identities in one table creates permission complexity. By separating them from the start, the `admins` guard is completely isolated. In V2, a `customers` table and guard are added alongside the existing `admins` table with no changes required to V1 code.

### 8.8 Version 2 Module Boundaries (Future)

The following modules do NOT exist in Version 1 in any form — no database tables, no migrations, no model stubs. They are documented here to confirm that the V1 architecture does not block their future introduction:

| V2 Module | How It Integrates with V1 |
|-----------|--------------------------|
| **Customers** | New `customers` table + new Sanctum guard + new `CustomerAuthController` |
| **Bookings** | New `bookings` table + new `BookingService` + new `/api/v2/bookings` routes |
| **Payments** | New `payments` table + new `PaymentGatewayInterface` + payment controllers |
| **Branches** | New `branches` table + additive `branch_id` column on `cars` via migration |
| **Drivers** | New `drivers` table + new `DriverService` and admin section |
| **Fleet Management** | Additive columns on `cars` (`license_plate`, `vin`, `availability_status`) via migration |
| **Notifications** | New Laravel Notification classes + queue channels (email, SMS, database) |
| **Analytics** | New `BookingReportService` + admin report pages |

V2 modules are added as new code (new tables, new services, new controllers, new routes). V2 should preserve V1 behaviour and minimise breaking changes. Existing V1 models, services, migrations, Filament resources, routes, and views may be extended or adapted when required, while maintaining compatibility with existing V1 functionality.

---

## 9. Folder Structure

The following folder structure enforces the pragmatic three-layer architecture and Laravel conventions.

```
cars_rental/
|
+-- app/
|   |
|   +-- Http/
|   |   +-- Controllers/
|   |   |   +-- Public/                    # Blade page controllers
|   |   |   |   +-- HomeController.php
|   |   |   |   +-- CarController.php
|   |   |   |   +-- CategoryController.php
|   |   |   |   +-- FaqController.php
|   |   |   |   +-- ContactController.php
|   |   |   |   +-- BookingController.php
|   |   |   +-- Api/                       # REST API controllers (future consumers)
|   |   |       +-- CarController.php
|   |   |       +-- CategoryController.php
|   |   |       +-- FaqController.php
|   |   |       +-- BannerController.php
|   |   |       +-- SettingController.php
|   |   +-- Requests/
|   |   |   +-- Car/
|   |   |       +-- CarFilterRequest.php
|   |   +-- Resources/                     # JSON transformers for REST API
|   |   |   +-- CarResource.php
|   |   |   +-- CarCollection.php
|   |   |   +-- CategoryResource.php
|   |   |   +-- FaqResource.php
|   |   |   +-- BannerResource.php
|   |   |   +-- SettingResource.php
|   |   +-- Middleware/
|   |       +-- ForceHttps.php
|   |       +-- MaintenanceMode.php
|   |       +-- ApiRateLimit.php
|   |
|   +-- Services/
|   |   +-- CarService.php
|   |   +-- CategoryService.php
|   |   +-- FaqService.php
|   |   +-- BannerService.php
|   |   +-- SettingService.php
|   |   +-- MediaService.php
|   |   +-- WhatsAppService.php
|   |
|   +-- Models/
|   |   +-- Admin.php
|   |   +-- Car.php                        # implements HasMedia (Spatie)
|   |   +-- CarFeature.php
|   |   +-- Category.php
|   |   +-- Faq.php
|   |   +-- Banner.php
|   |   +-- Setting.php
|   |
|   +-- Exceptions/
|   |   +-- Handler.php
|   |   +-- CarNotFoundException.php
|   |   +-- MediaUploadException.php
|   |
|   +-- Events/
|   |   +-- CarSaved.php
|   |   +-- CarDeleted.php
|   |
|   +-- Listeners/
|   |   +-- ClearCarCache.php
|   |
|   +-- Policies/
|   |   +-- CarPolicy.php
|   |   +-- CategoryPolicy.php
|   |   +-- FaqPolicy.php
|   |   +-- BannerPolicy.php
|   |   +-- SettingPolicy.php
|   |
|   +-- Filament/                           # Filament admin panel
|   |   +-- Resources/
|   |   |   +-- CarResource.php
|   |   |   +-- CarResource/
|   |   |   |   +-- Pages/
|   |   |   |       +-- ListCars.php
|   |   |   |       +-- CreateCar.php
|   |   |   |       +-- EditCar.php
|   |   |   +-- CategoryResource.php
|   |   |   +-- CategoryResource/
|   |   |   |   +-- Pages/
|   |   |   |       +-- ListCategories.php
|   |   |   |       +-- CreateCategory.php
|   |   |   |       +-- EditCategory.php
|   |   |   +-- FaqResource.php
|   |   |   +-- FaqResource/
|   |   |   |   +-- Pages/
|   |   |   |       +-- ListFaqs.php
|   |   |   |       +-- CreateFaq.php
|   |   |   |       +-- EditFaq.php
|   |   |   +-- BannerResource.php
|   |   |   +-- BannerResource/
|   |   |       +-- Pages/
|   |   |           +-- ListBanners.php
|   |   |           +-- CreateBanner.php
|   |   |           +-- EditBanner.php
|   |   +-- Pages/
|   |   |   +-- ManageSettings.php         # Custom Filament page for settings
|   |   +-- Widgets/
|   |       +-- StatsOverviewWidget.php
|   |       +-- LatestCarsWidget.php
|   |
|   +-- Providers/
|       +-- AppServiceProvider.php
|       +-- EventServiceProvider.php
|       +-- AuthServiceProvider.php
|       +-- Filament/
|           +-- AdminPanelProvider.php      # Registers Filament panel
|
+-- database/
|   +-- migrations/
|   |   +-- 2024_01_01_create_admins_table.php
|   |   +-- 2024_01_02_create_categories_table.php
|   |   +-- 2024_01_03_create_cars_table.php
|   |   +-- 2024_01_04_create_car_features_table.php
|   |   +-- 2024_01_05_create_faqs_table.php
|   |   +-- 2024_01_06_create_banners_table.php
|   |   +-- 2024_01_07_create_settings_table.php
|   |   +-- [spatie media migration — published via: php artisan vendor:publish --tag="medialibrary-migrations"]
|   +-- seeders/
|   |   +-- DatabaseSeeder.php
|   |   +-- AdminSeeder.php
|   |   +-- CategorySeeder.php
|   |   +-- SettingSeeder.php
|   +-- factories/
|       +-- CarFactory.php
|       +-- CategoryFactory.php
|
+-- routes/
|   +-- api.php                            # Public REST API routes (/api/*)
|   +-- web.php                            # Blade MPA public page routes
|
+-- resources/
|   +-- views/
|   |   +-- layouts/
|   |   |   +-- app.blade.php              # Main public layout (nav, footer)
|   |   +-- pages/
|   |       +-- home.blade.php
|   |       +-- cars/
|   |       |   +-- index.blade.php        # Car listing
|   |       |   +-- show.blade.php         # Car detail
|   |       +-- about.blade.php
|   |       +-- faq.blade.php
|   |       +-- contact.blade.php
|   |       +-- booking.blade.php          # Pre-Booking Inquiry Form
|   +-- css/
|   |   +-- app.css
|   +-- js/
|       +-- app.js
|
+-- config/
|   +-- app.php
|   +-- cors.php
|   +-- cache.php
|   +-- filesystems.php
|   +-- logging.php
|   +-- filament.php
|   +-- car-rental.php
|
+-- storage/
|   +-- app/
|   |   +-- public/
|   |       +-- cars/
|   |       +-- banners/
|   |       +-- categories/
|   |       +-- company/
|   +-- logs/
|       +-- laravel.log
|
+-- public/
|   +-- index.php
|   +-- storage -> symlink to ../storage/app/public
|
+-- tests/
    +-- Unit/
    |   +-- Services/
    |       +-- CarServiceTest.php
    |       +-- SettingServiceTest.php
    +-- Feature/
        +-- Api/
        |   +-- CarListingTest.php
        |   +-- CarDetailTest.php
        +-- Pages/
        |   +-- HomePageTest.php
        |   +-- CarListingPageTest.php
        +-- Middleware/
            +-- MaintenanceModeTest.php
```

---

## 10. Request Flow

This section traces the complete journey of a request through the system, from browser to database and back.

### 10.1 Public Car Listing Request Flow

**Scenario:** A public visitor navigates to `/cars?category=suv` — a server-rendered Blade page.

```
1. Browser
   |
   |   GET /cars?category=suv&page=1
   V
2. NGINX Web Server
   |  - Validates HTTPS
   |  - /* => forwards to PHP-FPM
   V
3. PHP-FPM => public/index.php
   |
   V
4. Laravel Bootstrap
   |  - Loads .env configuration
   |  - Boots service providers
   |
   V
5. Middleware Pipeline
   |  - TrimStrings
   |  - ConvertEmptyStringsToNull
   |  - MaintenanceMode check
   |
   V
6. Router (routes/web.php)
   |  - Matches: GET /cars
   |  - Resolves to: Public\CarController@index
   |
   V
7. CarController@index
   |  - Reads query params: category, search, page
   |  - Calls: $this->carService->getAllPublished($request)
   |  - Calls: $this->categoryService->getActive()
   |
   V
8. CarService@getAllPublished
   |  - Builds cache key from filter params
   |  - Checks Redis cache: "cars:suv:page:1"
   |  - CACHE HIT  => return cached collection immediately
   |  - CACHE MISS => continue to Eloquent
   |
   V
9. Car Eloquent Model (via CarService)
   |  - Car::with(['category', 'media'])
   |      ->published()
   |      ->inCategory('suv')
   |      ->paginate(12)
   |
   V
10. MySQL Database
    |  - SELECT * FROM cars JOIN categories
    |    WHERE is_published = 1 AND categories.slug = 'suv'
    |    ORDER BY sort_order ASC LIMIT 12 OFFSET 0
    |
    V (back up the stack)
11. CarService
    |  - Stores result in Redis: 1-hour TTL
    |  - Returns Eloquent collection to Controller
    |
    V
12. CarController@index
    |  - Returns: view('pages.cars.index', compact('cars', 'categories'))
    |
    V
13. Blade Template (resources/views/pages/cars/index.blade.php)
    |  - Renders server-side HTML:
    |    - Category filter bar (from $categories)
    |    - Car grid (from $cars)
    |    - Pagination links
    |
    V
14. HTTP Response
    - Server-rendered HTML page (200 OK)
    - Full page delivered in one request
    - No client-side data fetching required
```

**Total Estimated Time:** 50–150ms (with cache hit: 20–50ms)

### 10.2 Admin Car Creation Request Flow

**Scenario:** Admin submits the car creation form in the Filament dashboard.

```
1. Admin navigates to /admin/cars/create
   |  - Filament serves the CreateCar Livewire page
   V
2. PHP-FPM => Laravel => Filament middleware
   |  - Checks session cookie for valid admin session
   |  - No valid session => redirect to /admin/login
   |  - Valid session => serve CreateCar form
   V
3. Filament CarResource form() renders server-side:
   |  - TextInput (Name), TextInput (Slug — auto-generated)
   |  - Select (Category)
   |  - Textarea (Description)
   |  - KeyValueInput (Specifications JSON)
   |  - TagsInput (Features)
   |  - SpatieMediaLibraryFileUpload (Images — multiple, with cover selection)
   |  - Toggle (is_published), Toggle (is_featured), TextInput (sort_order)
   V
4. Admin fills the form and clicks Save
   |  - Livewire AJAX request to the same server
   V
5. Filament form validation (server-side, always enforced)
   |  - FAILS => Livewire returns inline validation errors (no page reload)
   |  - PASSES => continue
   V
6. Filament CarResource writes directly via Eloquent:
   |  - Car::create($validated)
   |  - Spatie Media Library handles image uploads from SpatieMediaLibraryFileUpload
   |    => Stores files to storage/app/public/cars/
   |    => Creates media records in the media table
   V
7. CarSaved event dispatched:
   |  - ClearCarCache listener => clears Redis car cache
   V
8. Filament success:
   |  - Redirect to /admin/cars (list view)
   |  - Filament success toast notification shown

NOTE: No REST API involved at any step.
NOTE: No Sanctum token. Filament uses session-based authentication.
NOTE: Filament accesses Eloquent directly in the same PHP process.
```

### 10.3 Pre-Booking Inquiry Form Flow (الحجز المسبق)

**Scenario:** A visitor on `/cars/{slug}` clicks the inquiry button, then navigates to the pre-booking form at `/booking`.

```
1. Visitor clicks "Book via WhatsApp" on any car page
   |  - URL: /booking?car=bmw-5-series
   V
2. BookingController@show
   |  - Reads ?car=slug from request
   |  - Calls CarService->getAllPublished() for car dropdown
   |  - Calls CarService->findBySlug('bmw-5-series') for pre-selection
   |  - Calls SettingService->get('contact.whatsapp_number')
   |
   V
3. Blade Template (resources/views/pages/booking.blade.php)
   |  - Renders complete form server-side:
   |    - $cars passed to view => dropdown rendered in HTML
   |    - $selectedCar passed to view => option pre-selected in HTML
   |    - $whatsappNumber embedded in Blade via @json($whatsappNumber)
   |  - Additional form fields: Trip Road, Name, Phone, Pickup Date,
   |    Return Date, Notes
   V
4. Visitor reviews and completes the form
   V
5. JavaScript client-side validation on Submit
   V
6. JavaScript generates structured message:
   "طلب حجز مسبق / Pre-Booking Inquiry
    Car:       {form.car}
    Trip Road: {form.tripRoad}
    Name:      {form.name}
    Phone:     {form.phone}
    Pickup:    {form.pickupDate}
    Return:    {form.returnDate}
    Notes:     {form.notes}"
   V
7. Encode with encodeURIComponent()
   V
8. window.location.href = "https://wa.me/{whatsapp_number}?text={encoded}"

NOTE: No server request is made on form submission.
NOTE: No booking data is stored in the database.
NOTE: The WhatsApp number is delivered to the page server-side by Blade.
NOTE: No REST API call is made at any point in this flow.
```

---

## 11. API Flow

### 11.1 API Design Principles

- **RESTful conventions:** Resources as nouns, HTTP verbs as actions
- **Base path:** All public routes under `/api` — no version prefix in V1
- **Consistent response envelope:** Every response follows the same JSON structure
- **Structured error responses:** Errors include error code, message, and field-level details
- **Pagination:** All list endpoints are paginated (default 12 items, max 100)
- **Filtering:** Via query parameters on GET requests
- **Authentication:** Public routes — none. Admin operations are handled by Filament directly (server-side Eloquent), not via REST API

### 11.2 Standard Response Envelope

**Success (Single Resource):**
```json
{
  "success": true,
  "data": { },
  "meta": null
}
```

**Success (Collection):**
```json
{
  "success": true,
  "data": [ { }, { } ],
  "meta": {
    "current_page": 1,
    "per_page": 12,
    "total": 48,
    "last_page": 4
  },
  "links": {
    "first": "/api/cars?page=1",
    "last": "/api/cars?page=4",
    "prev": null,
    "next": "/api/cars?page=2"
  }
}
```

**Error Response:**
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The given data was invalid.",
    "details": {
      "name": ["The name field is required."],
      "price_daily": ["The price must be a positive number."]
    }
  }
}
```

### 11.3 Public API Endpoints (V1)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/cars` | List published cars | None |
| GET | `/api/cars/{slug}` | Get single car details | None |
| GET | `/api/categories` | List active categories | None |
| GET | `/api/faqs` | List active FAQs | None |
| GET | `/api/banners` | List active hero banners | None |
| GET | `/api/settings` | Get all public settings | None |
| GET | `/api/settings/contact` | Get contact information | None |

**Query Parameters for `/api/cars`:**
- `category` — Filter by category slug (e.g., `?category=suv`)
- `search` — Keyword search in name and brand (e.g., `?search=toyota`)
- `page` — Page number (e.g., `?page=2`)
- `per_page` — Items per page, max 100 (e.g., `?per_page=24`)
- `featured` — Filter featured cars only (e.g., `?featured=1`)

### 11.4 Admin API Endpoints (V1)

**There are no admin REST API endpoints in V1.**

All admin operations (creating, editing, deleting cars, categories, FAQs, banners, and settings) are handled entirely by **Laravel Filament** — directly via Eloquent, server-side, without HTTP API calls.

| Concern | How it is handled |
|---|---|
| Admin authentication | Filament session-based login at `/admin/login` |
| Car CRUD | Filament `CarResource` — direct Eloquent access |
| Image uploads | Filament `SpatieMediaLibraryFileUpload` — direct Spatie Media Library |
| Settings management | Filament custom `ManageSettings` page — direct SettingService |
| All other entities | Filament Resources — direct Eloquent access |

> **V2 Extension:** If a mobile admin app or external integration requires admin CRUD via REST, `/api/admin/*` endpoints may be introduced in V2 with Sanctum Bearer token authentication. This is not a V1 requirement.

### 11.5 API Versioning Strategy

The V1 API uses the base path `/api` with no version prefix. There are currently no external consumers requiring backward compatibility, so versioning is deferred.

If breaking changes are introduced in the future, a `/api/v2/` prefix may be adopted at that time, while V1 routes remain functional for any existing consumers.

---

## 12. Admin Dashboard Architecture

### 12.1 Overview

The Admin Dashboard is built with **Laravel Filament v5** — a first-party Laravel admin panel framework built on top of Livewire and Alpine.js. Filament generates a production-grade, fully-responsive admin interface directly from Eloquent models with minimal configuration.

Filament lives entirely within the Laravel codebase (`app/Filament/`). It is served at `/admin` by the same PHP-FPM process. There is no separate frontend build step — Filament ships its own pre-built assets.

**Why Filament over custom HTML/JS admin pages?**
1. **Speed:** A Filament `Resource` replaces hundreds of lines of HTML, JS, and API wiring with a single PHP class.
2. **Built-in UI components:** Tables with sorting, filtering, and search; forms with all input types; modals, notifications, file upload — all included.
3. **No API round-trip:** Filament accesses Eloquent directly (same process) — faster and simpler than HTTP API calls.
4. **Authentication included:** Login page, session management, and logout handled by Filament natively.
5. **Customisable:** Custom pages, widgets, and themes can be added without leaving PHP.
6. **Maintainable:** Any Laravel developer familiar with Filament can maintain and extend the admin.

### 12.2 Authentication Architecture

Filament handles admin authentication natively — no custom auth code required.

**How it works:**
- Login page is at `/admin/login` (generated by Filament)
- Admin submits email and password
- Laravel authenticates against the `admins` table using the `admin` guard
- On success, a standard Laravel session cookie is issued (HttpOnly, server-managed)
- All Filament requests include the session cookie automatically — no token management in JavaScript
- Logout at `/admin/logout` destroys the session

**Security properties:**
- Session cookie is HttpOnly — inaccessible to JavaScript by browser design
- CSRF protection applied to all Filament form submissions by Laravel's middleware
- No token storage in `localStorage` or any client-side store

**Login Flow:**
```
1. Admin navigates to /admin
2. Filament middleware checks for valid session
3. No session => redirect to /admin/login
4. Admin submits email + password
5. Laravel authenticates via admins guard + Hash::check()
6. Session created => redirect to /admin (Filament dashboard)
7. All subsequent Filament requests: session cookie sent automatically
```

### 12.3 Filament Resource Architecture

Each entity in the admin is represented by a **Filament Resource** — a PHP class that defines the table, form, and pages for that entity.

**Resource anatomy (example: `CarResource`):**
```php
CarResource
|
+-- table()       // Defines the list table: columns, filters, actions
|   +-- Columns:  TextColumn (Name), BadgeColumn (Status), ImageColumn (Cover)
|   +-- Filters:  SelectFilter (Category), TernaryFilter (Published)
|   +-- Actions:  EditAction, DeleteAction, TogglePublishedAction
|
+-- form()        // Defines the create/edit form: fields, sections, tabs
|   +-- Section "Basic Info":   TextInput (Name), Select (Category), Textarea (Description)
|   +-- Section "Specs":        KeyValueInput (specifications JSON)
|   +-- Section "Features":     TagsInput (car_features)
|   +-- Section "Images":       SpatieMediaLibraryFileUpload (multiple, with cover)
|   +-- Section "Publication":  Toggle (is_published), Toggle (is_featured), TextInput (sort_order)
|
+-- Pages:
    +-- ListCars    => /admin/cars          (table view)
    +-- CreateCar   => /admin/cars/create   (create form)
    +-- EditCar     => /admin/cars/{id}/edit (edit form)
```

### 12.4 Settings Custom Page

Since the `settings` table uses a key-value pattern (not a standard CRUD model), it is managed through a **custom Filament Page** rather than a Resource.

```php
ManageSettings extends Page
|
+-- Renders a tabbed form (using Filament's Tabs component)
+-- Tabs: Company | Contact | Social | SEO | Appearance | System
+-- On load: reads all keys from settings table via SettingService
+-- On save: writes changed keys back via SettingService (clears Redis cache)
+-- Logo / Favicon tabs include FileUpload with live preview
```

### 12.5 Dashboard Widgets

The Filament dashboard (home page at `/admin`) shows two widgets:

- **`StatsOverviewWidget`** — Displays four stat cards: Total Cars, Published Cars, Categories, FAQs
- **`LatestCarsWidget`** — A compact table showing the last 10 cars added

---

## 13. Public Website Architecture

### 13.1 Overview

The public website is a **Laravel Blade Multi-Page Application (MPA)**. Each public URL is handled by a Laravel web route (`routes/web.php`) that dispatches to a controller. The controller calls the Service layer, retrieves data, and returns a rendered Blade template. There are no standalone HTML files and no client-side API fetching on public pages.

**This approach provides:**
- Full server-side rendering — data is embedded in HTML by the time the browser receives it
- Natural Laravel conventions — controllers, services, and Blade work identically to how Filament works
- SEO-ready HTML out of the box — search engines see complete content without executing JavaScript
- No duplication of data-fetching logic between a frontend and backend

### 13.2 Routing

All public pages are defined in `routes/web.php`:

```php
Route::get('/',           [HomeController::class,    'index'])->name('home');
Route::get('/cars',       [CarController::class,     'index'])->name('cars.index');
Route::get('/cars/{slug}',[CarController::class,     'show'])->name('cars.show');
Route::get('/about',      [AboutController::class,   'index'])->name('about');
Route::get('/faq',        [FaqController::class,     'index'])->name('faq.index');
Route::get('/contact',    [ContactController::class, 'index'])->name('contact');
Route::get('/booking',    [BookingController::class, 'show'])->name('booking');
```

### 13.3 SEO Strategy

**Advantage of Blade MPA:**
Each page is rendered server-side with its own `<title>`, `<meta description>`, `<h1>`, and full content embedded in HTML. Search engines index complete pages immediately — no JavaScript execution required. This is the strongest possible SEO foundation.

**Per-Page SEO:**
- Each Blade layout slot accepts a `@section('title')` and `@section('meta_description')`
- Car detail pages set title and meta description from `$car->meta_title` and `$car->meta_description` (with fallback to name + description)
- Car detail URLs are clean: `/cars/bmw-5-series` — no query parameters in public-facing URLs
- A sitemap route may be added: `GET /sitemap.xml` served by a Laravel controller
- Open Graph tags are set per page from server-side data

### 13.4 Page-Specific Architecture

**Home Page (`/` → `HomeController@index`):**
- Controller fetches: active banners, featured cars, FAQ highlights, company settings
- Blade template renders: hero banner slider, featured cars grid, intro section, FAQ accordion
- All data is embedded server-side — no JavaScript data fetching on load

**Car Listing Page (`/cars` → `CarController@index`):**
- Controller reads query params: `category`, `search`, `page`
- Controller fetches: filtered+paginated car list, active categories (for filter bar)
- Filter state is maintained in URL query parameters (`?category=suv&page=2`)
- Blade template renders: filter bar, car grid, pagination links
- Browser back button and URL sharing work naturally

**Car Detail Page (`/cars/{slug}` → `CarController@show`):**
- Controller fetches: car with full specifications, features, images, and category
- Returns 404 if slug not found or car is unpublished
- Blade template renders: image gallery, specification table, features, WhatsApp CTA button
- Pricing is **not displayed** — pricing is communicated through WhatsApp directly

**Pre-Booking Inquiry Form (`/booking` → `BookingController@show`):**
- Controller fetches: all published cars (for dropdown), WhatsApp number from settings
- If `?car=bmw-5-series` is in the request, the controller pre-selects that car
- Blade template renders the complete form with all data embedded
- On submit: JavaScript generates a structured WhatsApp message and redirects
- No server request on submit. No data stored.

---

## 14. Future Expansion Strategy

### 14.1 Expansion Philosophy

V2 modules are added progressively alongside V1. The guiding principles are:

1. **Prefer additive changes** — new routes go under `/api/v2/` or new route groups where possible
2. **No destructive database changes** — only additive migrations (new columns with `nullable()`, new tables)
3. **Preserve V1 API responses** — existing responses remain stable for any existing consumers
4. **Minimise rewriting V1 classes** — V2 adds new service classes; existing ones may be extended when necessary

V2 should preserve V1 behaviour and minimise breaking changes. Existing V1 models, services, migrations, Filament resources, routes, and views may be extended or adapted when required, while maintaining compatibility with existing V1 functionality.

**Mechanism:** Each V2 module is a new folder of controllers, services, and models that integrates into the existing Laravel service container, route file, and event system.

### 14.2 Booking Module (V2)

**What V1 provides:**
- Car pricing columns (`price_daily`, `price_weekly`, `price_monthly`) are already present for display
- API versioning means `/api/v2/bookings` can be added without touching V1 routes

**V2 Addition Plan:**
- New migration: `create_customers_table`
- New migration: `create_bookings_table` (references `cars.id`)
- New migration: Add `availability_status` column to `cars`
- New module: `app/Services/BookingService.php`
- New controllers: `Api\V2\Public\BookingController`, `Api\V2\Admin\BookingController`
- WhatsApp modal remains as fallback for non-authenticated users

### 14.3 Payment Module (V2)

**V2 Addition Plan:**
- New migration: `create_payments_table` (references `bookings.id`)
- New interface: `PaymentGatewayInterface` (bound in new `PaymentServiceProvider`)
- New implementations: `StripeGateway`, `PayPalGateway`
- New settings group: `payment` (gateway keys stored in existing `settings` table)

### 14.4 Customer Authentication (V2)

**V2 Addition Plan:**
- New migration: `create_customers_table`
- New guard: `customer` in `config/auth.php`
- New `CustomerAuthController`
- New routes under `/api/v2/customer/auth/`
- Separate from `admins` table — no conflicts with V1 authentication

### 14.5 Multi-Branch Support (V2)

**V2 Addition Plan:**
- New migration: `create_branches_table`
- New migration: Add `branch_id` (nullable FK) to `cars` table
- New `BranchService` and admin section
- Car queries in V2 updated to scope by branch

### 14.6 Fleet Management (V2)

**V2 Addition Plan:**
- New migration: Add `license_plate`, `vin`, `availability_status` to `cars` table
- New migration: `create_fleet_status_logs_table`
- New `FleetService` for availability tracking

### 14.7 Notifications (V2)

**V2 Addition Plan:**
- New Laravel Notification classes (`BookingConfirmed`, `PaymentReceived`)
- New notification channels: email, SMS (via API provider), database
- Queue worker already running in V1 — notifications use the existing worker

### 14.8 REST API Versioning Strategy

```
V1: /api/*      => Current endpoints (no prefix — no external consumers yet)
V2: /api/v2/*   => V2 endpoints (booking, payments, customer auth) — when needed
```

V1 endpoints remain active. If a version prefix becomes necessary for backward compatibility, `/api/v2/` is introduced for breaking changes while V1 endpoints are preserved.

---

## 15. Security Architecture

### 15.1 Authentication and Authorization

**Admin Authentication Flow (Filament session-based):**
1. Admin navigates to `/admin`
2. Filament middleware checks for a valid session cookie
3. No valid session → redirect to `/admin/login`
4. Admin submits email + password via the Filament login form
5. Laravel authenticates against the `admins` table using `Hash::check()`
6. On success, a standard Laravel session cookie is issued (HttpOnly, server-managed)
7. All subsequent Filament requests include the session cookie automatically
8. Logout at `/admin/logout` destroys the session server-side

**Why session-based (not Sanctum tokens) for admin?**
- Filament is a server-rendered Livewire application; it does not need token-based auth
- Session cookies are HttpOnly — inaccessible to JavaScript, XSS-resistant by browser design
- No token storage needed in `localStorage` or any client-side store
- CSRF protection applied automatically to all Filament form submissions

**Authorization:**
In V1, there is only one admin. No RBAC is needed. In preparation for V2's multi-admin scenario:
- Laravel Policies are created for every resource (CarPolicy, CategoryPolicy, etc.)
- V1 policies always return `true` (no restrictions beyond authentication)
- When V2 adds roles, only policies are updated — controllers require no changes

### 15.2 Rate Limiting

All inputs are validated at two layers:

**Layer 1 — Form Request (Server-Side, Always Enforced):**
- Required field checks
- Type validation (numeric, string, date, email)
- Max length enforcement
- Enum value checks
- File MIME type and size validation
- Foreign key existence checks

**Layer 2 — Client-Side (UX Only, Never Trusted for Security):**
- Immediate user feedback before form submission
- Server always re-validates — client-side validation is advisory only

**SQL Injection Prevention:**
- Eloquent ORM used exclusively for all queries
- Raw queries explicitly forbidden in code reviews
- If raw queries are ever needed: `DB::select()` with bound parameters only

### 15.4 File Upload Security

Files uploaded through the admin panel pass through these validation gates:

1. **Extension whitelist:** Only `jpg`, `jpeg`, `png`, `webp`, `gif`, `svg` — no PHP, JS, HTML, EXE
2. **MIME type check:** Validate MIME type matches declared extension (prevents extension spoofing)
3. **File size limit:** Maximum 5MB per image file, 50MB per upload batch
4. **Filename sanitization:** Original filename discarded; UUID-based filename generated: `{uuid}.{extension}`
5. **Storage path isolation:** Files stored in `storage/app/public/` — not in `public/` directly
6. **No executable uploads:** Directory configured with no-execute permissions at OS level
7. **NGINX configuration:** Uploaded file directory has PHP execution explicitly denied

### 15.5 Rate Limiting

| Endpoint Group | Rate Limit | Window |
|----------------|------------|--------|
| Public API | 60 requests | per minute per IP |
| Public Contact Form | 5 requests | per minute per IP |
| Admin Login | 10 attempts | per minute per IP |

Rate limits enforced by Laravel's `ThrottleRequests` middleware with Redis as the storage backend.

**Brute Force Protection for Admin Login:**
After 5 failed login attempts from the same IP, that IP is blocked for 15 minutes. Failed attempts tracked in Redis for performance.

### 15.6 CORS Configuration

```
Allowed Origins:  {app_domain} only (not wildcard)
Allowed Methods:  GET, POST, OPTIONS
Allowed Headers:  Content-Type, Accept, X-Requested-With
Exposed Headers:  X-RateLimit-Remaining, X-RateLimit-Limit
Allow Credentials: false (public REST API is read-only and unauthenticated)
Max Age: 86400 seconds (preflight cache)
```

**Why not wildcard (*)?**
Wildcard CORS is a security anti-pattern. The allowed origin is explicitly set to the application domain.

### 15.7 HTTPS Enforcement

- NGINX redirects all HTTP traffic to HTTPS (301 permanent redirect)
- HSTS header: `max-age=31536000; includeSubDomains`
- SSL certificate: Let's Encrypt (auto-renewed via Certbot)
- Minimum TLS version: TLS 1.2

### 15.8 Security Headers

All responses include these HTTP security headers applied at NGINX level:

```
X-Content-Type-Options: nosniff
X-Frame-Options: SAMEORIGIN
X-XSS-Protection: 1; mode=block
Referrer-Policy: strict-origin-when-cross-origin
Content-Security-Policy: default-src 'self'; img-src 'self' data: blob:;
Permissions-Policy: camera=(), microphone=(), geolocation=()
```

---

## 16. File Storage Strategy

### 16.1 Storage Architecture

The system uses Laravel's `Storage` Facade as an abstraction layer over the physical storage backend. Application code is identical regardless of whether files are stored locally or in S3 — only configuration changes.

**V1 Storage Backend:** Local filesystem
**V2 Storage Backend:** Amazon S3 or S3-compatible provider (MinIO, Cloudflare R2)

**Why Local Storage in V1?**
- Zero additional cost
- No external dependency
- Simpler initial deployment
- Storage Facade ensures painless future migration

### 16.2 Directory Structure

```
storage/app/public/
+-- cars/
|   +-- originals/          <= Original uploaded files (full resolution)
|   |   +-- {uuid}.jpg
|   +-- thumbnails/         <= Auto-generated smaller versions
|       +-- {uuid}_sm.jpg   <= 400x300  (card thumbnail)
|       +-- {uuid}_md.jpg   <= 800x600  (listing view)
|       +-- {uuid}_lg.jpg   <= 1920x1080 (detail page)
+-- banners/
|   +-- originals/
|   +-- thumbnails/
|       +-- {uuid}_sm.jpg   <= 640x360  (mobile)
|       +-- {uuid}_lg.jpg   <= 1920x600 (desktop hero)
+-- categories/
|   +-- {uuid}.{ext}        <= Category icons
+-- company/
    +-- logo_{uuid}.{ext}
    +-- favicon_{uuid}.ico
```

### 16.3 Image Processing Strategy

On upload, images are processed to generate thumbnails asynchronously to avoid blocking the upload response.

**Process:**
1. Admin uploads image(s) via the Filament `SpatieMediaLibraryFileUpload` component
2. Spatie Media Library validates and stores the original file immediately
3. A queued job (`ProcessImageThumbnails`) is dispatched to the queue
4. Filament responds immediately; the original file is available; thumbnails are generated shortly after
5. Queue worker processes thumbnails within seconds
6. Public pages use thumbnails; fall back to original if thumbnails are not yet ready

**Why asynchronous?**
- Avoids HTTP timeout on large image batches
- Admin sees the image immediately while thumbnails process in the background
- Queue worker retries on failure automatically

### 16.4 Migration to Cloud Storage

When switching to S3:
1. Update `FILESYSTEM_DISK=s3` in `.env`
2. Add S3 credentials to `.env`
3. Run a one-time migration script to copy existing local files to S3
4. No application code changes required — Storage Facade is transparent

---

## 17. Logging Strategy

### 17.1 Log Types

The system maintains four distinct log channels:

| Channel | Purpose | Location | Retention |
|---------|---------|----------|-----------|
| `daily` | General application logs | `storage/logs/laravel-{date}.log` | 30 days |
| `activity` | Admin activity audit trail | `storage/logs/activity-{date}.log` | 90 days |
| `api` | API request/response logs | `storage/logs/api-{date}.log` | 14 days |
| `error` | Critical errors only | `storage/logs/error-{date}.log` | 90 days |

### 17.2 Log Level Strategy

| Level | When Used | Example |
|-------|-----------|---------|
| `DEBUG` | Development only | Query execution plan |
| `INFO` | Normal operations worth recording | "Car #42 published by admin" |
| `WARNING` | Unexpected but recoverable situations | "Cache miss spike detected" |
| `ERROR` | Failed operations requiring attention | "Image upload failed: disk full" |
| `CRITICAL` | System-threatening events | "Database connection refused" |

**Production Logging:** Only `INFO` and above are logged in production.

### 17.3 Activity Logging

Every admin action is logged for audit purposes via the `LogAdminActivity` listener:

```json
{
  "timestamp": "2026-08-05T19:30:00Z",
  "admin_id": 1,
  "admin_email": "admin@example.com",
  "action": "car.created",
  "entity": "Car",
  "entity_id": 42,
  "changes": {
    "name": "BMW 5 Series",
    "category_id": 3,
    "is_published": false
  },
  "ip_address": "203.0.113.1",
  "user_agent": "Mozilla/5.0..."
}
```

### 17.4 Error Monitoring (Production Recommendation)

Integrate with Sentry (recommended) for real-time error tracking. The `Handler.php` `report()` method is the single integration point. Adding Sentry requires only the SDK and `SENTRY_LARAVEL_DSN` in `.env`.

---

## 18. Error Handling Strategy

### 18.1 Error Handling Philosophy

All errors are caught, logged, and returned in a consistent JSON structure. No raw exception messages or stack traces are ever exposed to clients in production.

### 18.2 Exception Hierarchy

```
Throwable
+-- AppException (base for all custom exceptions)
|   +-- NotFoundException
|   |   +-- CarNotFoundException
|   |   +-- CategoryNotFoundException
|   +-- UnauthorizedException
|   +-- ForbiddenException
|   +-- MediaUploadException
+-- ValidationException (Laravel built-in, handled natively)
+-- \Exception (unexpected — caught and wrapped in generic 500)
```

### 18.3 Exception Handler Mapping

| Exception | HTTP Code | Error Code | Client Message |
|-----------|-----------|------------|----------------|
| `CarNotFoundException` | 404 | `CAR_NOT_FOUND` | "The requested car was not found." |
| `CategoryNotFoundException` | 404 | `CATEGORY_NOT_FOUND` | "The requested category was not found." |
| `ValidationException` | 422 | `VALIDATION_ERROR` | "The given data was invalid." |
| `UnauthorizedException` | 401 | `UNAUTHENTICATED` | "Authentication required." |
| `ForbiddenException` | 403 | `FORBIDDEN` | "You do not have permission." |
| `MediaUploadException` | 422 | `UPLOAD_FAILED` | "File upload failed." |
| `\Throwable` (unexpected) | 500 | `SERVER_ERROR` | "An unexpected error occurred." |

**Critical Rule:** Unexpected exceptions are fully logged internally but return only the generic 500 message to the client. Stack traces are never returned in production API responses.

### 18.4 Database Error Handling

- `QueryException` is caught and wrapped in a generic 500 response
- Database connection failures trigger a `CRITICAL` log and graceful 503 response
- Deadlocks are retried up to 3 times before failing

### 18.5 Public Website Error Handling

Blade-rendered public pages handle errors server-side:

| Scenario | Behaviour |
|----------|-----------|
| Car slug not found or unpublished | Laravel exception handler returns custom `errors/404.blade.php` (HTTP 404) |
| Server error during page render | Laravel exception handler returns `errors/500.blade.php` (HTTP 500); stack trace never shown in production |
| Maintenance mode active | `errors/503.blade.php` served (HTTP 503) via `MaintenanceMode` middleware |
| Contact form validation failure | Controller redirects back with validation errors displayed inline in the form |
| Contact form success | Controller redirects back with a session flash success message |

Public Blade pages do not make client-side API calls, so there are no API error states to handle in JavaScript on the public website.

---

## 19. Scalability Strategy

### 19.1 Current V1 Scale Assumptions

- Traffic: 100–5,000 unique visitors per day
- Concurrent users: up to 200 simultaneous
- Cars in fleet: 10–200 vehicles
- Admin users: 1
- Server: Single VPS (2–4 CPU cores, 2–8 GB RAM)

### 19.2 Caching Strategy

**Three-tier caching approach:**

**Tier 1 — Browser / CDN Cache (asset level):**
- Static assets (compiled CSS, JS bundles, uploaded images): `Cache-Control: public, max-age=31536000` (1 year with cache busting via Vite content-hashed filenames)
- Blade-rendered HTML pages are **not** cached at the HTTP layer — they are dynamically generated per request from Redis-cached service data

**Tier 2 — Application Cache (Redis):**

| Cache Key Pattern | Content | TTL |
|-------------------|---------|-----|
| `cars:list:{filters}:{page}` | Car listing results | 1 hour |
| `cars:detail:{slug}` | Single car with images | 2 hours |
| `categories:active` | Category list | 6 hours |
| `faqs:active` | All FAQ items | 6 hours |
| `banners:active` | Hero banners | 6 hours |
| `settings:all` | All site settings | 24 hours |
| `settings:{group}` | Settings by group | 24 hours |

**Cache Invalidation Strategy:**
- Car cache cleared by `ClearCarCache` listener on `CarSaved` and `CarDeleted` events
- Category cache cleared on category update
- Settings cache cleared on settings save
- Admin "Clear All Cache" utility endpoint available

**Tier 3 — Database Query Optimisation:**
- Eloquent eager loading prevents N+1 query problems (`with()` always required on relations)
- Database indexes on all frequently queried columns
- Full-text index on `cars.name` and `cars.description` for search

### 19.3 Database Optimisation

**Required Indexes:**

```sql
-- Cars table
CREATE INDEX idx_cars_is_published ON cars (is_published);
CREATE INDEX idx_cars_category_id ON cars (category_id);
CREATE INDEX idx_cars_slug ON cars (slug);
CREATE INDEX idx_cars_is_featured ON cars (is_featured);
CREATE FULLTEXT INDEX idx_cars_search ON cars (name, description);

-- Media table (Spatie Media Library)
CREATE INDEX idx_media_model ON media (model_type, model_id);
CREATE INDEX idx_media_collection ON media (collection);

-- FAQs table
CREATE INDEX idx_faqs_is_active ON faqs (is_active);
CREATE INDEX idx_faqs_sort_order ON faqs (sort_order);

-- Settings table
CREATE UNIQUE INDEX idx_settings_key ON settings (`key`);
CREATE INDEX idx_settings_group ON settings (settings_group);
```

### 19.4 Horizontal Scaling (V2 Readiness)

When traffic grows beyond a single server, the following steps are required:

**Step 1 — Sessions in Redis (already configured in V1):**
Laravel session driver set to `redis` from day one. All servers share session state.

**Step 2 — Shared File Storage:**
Move to S3 so all application servers access the same files (no code changes).

**Step 3 — Load Balancer:**
Add NGINX or cloud load balancer in front of multiple application servers.

**Step 4 — Database Read Replicas:**
Configure Laravel's read/write connection split in `config/database.php`.

**Scaling Trigger Points:**

| Daily Users | Recommended Action |
|-------------|-------------------|
| < 5,000 | Current single-server setup is sufficient |
| 5,000 – 20,000 | Redis cluster, CDN for assets, DB query optimisation review |
| 20,000 – 100,000 | Load balancer, second app server, DB read replica |
| 100,000+ | DB sharding, microservice extraction, Kubernetes |

---

## 20. Deployment Architecture

### 20.1 V1 Deployment Environment

```
Internet
    |
    V
+--------------------------------------------------------------------+
|                     VPS / Dedicated Server                         |
|                                                                    |
|  +--------------------------------------------------------------+  |
|  |                       NGINX                                  |  |
|  |   - SSL Termination (Let's Encrypt)                          |  |
|  |   - Static Asset Serving (CSS, JS, uploaded images)          |  |
|  |   - Proxy /* to PHP-FPM (all requests)                      |  |
|  |   - Security Headers, Rate Limiting                          |  |
|  +-----------------------------+--------------------------------+  |
|                                |                                   |
|  +-----------------------------V--------------------------------+  |
|  |                       PHP-FPM                               |  |
|  |   - Executes Laravel application (all requests)             |  |
|  |   - PHP 8.3+                                                |  |
|  +-----------------------------+--------------------------------+  |
|                                |                                   |
|  +-----------+  +--------------V-----------+  +----------------+  |
|  | MySQL 8.0 |  |   Laravel Application    |  |   Redis 7.x    |  |
|  |   tables  |  |                          |  |   Cache/Queue  |  |
|  +-----------+  +--------------------------+  +----------------+  |
|                                                                    |
|  +--------------------------------------------------------------+  |
|  |            Supervisor Process Manager                        |  |
|  |   - Manages Laravel Queue Worker (php artisan queue:work)    |  |
|  |   - Auto-restarts on failure                                 |  |
|  +--------------------------------------------------------------+  |
|                                                                    |
|  +--------------------------------------------------------------+  |
|  |              Cron (Laravel Scheduler)                        |  |
|  |   - "* * * * * php artisan schedule:run"                     |  |
|  |   - Scheduled tasks: cache warmup, log rotation              |  |
|  +--------------------------------------------------------------+  |
+--------------------------------------------------------------------+
```

### 20.2 Recommended Server Specifications (V1)

| Component | Minimum | Recommended |
|-----------|---------|-------------|
| CPU | 2 vCPU | 4 vCPU |
| RAM | 2 GB | 4 GB |
| Storage | 20 GB SSD | 50 GB SSD |
| Bandwidth | 1 TB/month | 5 TB/month |
| OS | Ubuntu 22.04 LTS | Ubuntu 22.04 LTS |
| PHP | 8.2 | 8.3 |
| MySQL | 8.0 | 8.0 |
| Redis | 7.x | 7.x |
| NGINX | 1.24 | 1.26 |

### 20.3 Deployment Process (Zero-Downtime)

```bash
# 1. Pull latest code
git pull origin main

# 2. Install PHP dependencies (production only)
composer install --no-dev --optimize-autoloader

# 3. Run pending database migrations
php artisan migrate --force

# 4. Cache configuration, routes, and views
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Ensure storage symlink exists
php artisan storage:link

# 6. Gracefully restart queue workers
php artisan queue:restart

# 7. Reload PHP-FPM (graceful worker rotation)
sudo systemctl reload php8.3-fpm
```

### 20.4 Required Environment Variables (Production)

```dotenv
APP_NAME="Car Rental"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://example.com
APP_KEY={generated with artisan key:generate}

LOG_CHANNEL=daily
LOG_LEVEL=info

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=car_rental_db
DB_USERNAME=db_user
DB_PASSWORD={strong_password}

CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=127.0.0.1
REDIS_PORT=6379

FILESYSTEM_DISK=local

MAIL_MAILER=smtp
MAIL_HOST=smtp.provider.com
MAIL_PORT=587
MAIL_FROM_ADDRESS=noreply@example.com
```

### 20.5 Backup Strategy

**Database Backups:**
- Automated daily dump via `mysqldump` through Cron
- Stored locally with 30-day retention; weekly backup to remote storage

**File Backups:**
- `storage/app/public/` synced to remote storage weekly; 4 weekly snapshots retained

**Application Backups:**
- Git repository is the source of truth; deployment scripts recreate from Git

---

## 21. Development Phases

### 21.1 Phase 0 — Project Setup (Week 1)

**Deliverables:**
- Laravel project scaffolded with correct directory structure
- Git repository initialised with `.gitignore` and branching strategy
- Packages installed: CORS, Spatie Sluggable, **Filament v5**, Spatie Media Library
- Filament panel configured (`AdminPanelProvider`) with `admins` guard and `/admin` path
- Vite configured with CSS and JS entry points
- Database configured; base migrations created
- Environment files configured (local, staging, production)
- NGINX and PHP-FPM configured on staging server
- Redis configured and tested
- Admin seeder created and tested

**Acceptance Criteria:**
- `php artisan serve` returns 200 OK
- `php artisan migrate` runs without errors
- `php artisan db:seed` creates admin account
- `/admin/login` renders the Filament login page
- Admin can log in and see the Filament dashboard
- Health check endpoint `GET /api/health` returns `{ "status": "ok" }`

### 21.2 Phase 1 — Data Layer (Week 2)

**Deliverables:**
- All V1 database migrations written and tested (8 tables only)
- All Eloquent models created with relationships, scopes, and casts
- All seeders with realistic demo data (10 cars, 5 categories, 20 FAQs)
- All settings seeded with sensible defaults

**Acceptance Criteria:**
- `php artisan migrate:fresh --seed` completes without errors
- Relationships return correct data when queried in tinker
- Hard deletes confirmed: deleted cars are permanently removed from the database

### 21.3 Phase 2 — Filament Admin Panel (Weeks 3–4)

**Deliverables:**
- `CarResource` with full table, form, image upload (Spatie Media Library)
- `CategoryResource` with full table and form
- `FaqResource` with reordering support
- `BannerResource` with image upload
- `ManageSettings` custom page with tabbed form (all setting groups)
- `StatsOverviewWidget` and `LatestCarsWidget` on dashboard
- Cache invalidation wired via Events/Listeners on Filament saves
- Admin authentication confirmed working (Filament session-based)

**Acceptance Criteria:**
- Admin can log in at `/admin/login`
- Admin can perform full CRUD on cars including image upload
- Settings page saves and reflects changes; Redis cache is cleared on save
- Dashboard widgets show correct counts
- All Filament pages are responsive on 1280px and 1920px screens
- Hard delete confirmed: deleted cars are permanently removed

### 21.4 Phase 3 — Public REST API (Week 5)

**Deliverables:**
- Cars listing API with filters and pagination
- Car detail API
- Categories, FAQs, Banners, Settings public APIs
- Redis caching on all public endpoints
- Cache invalidation via Events/Listeners on Filament admin writes
- Rate limiting on all public endpoints

**Acceptance Criteria:**
- Car listing returns correct paginated results
- Filters (category, search) work correctly
- Cache returns response within 50ms (verified with Redis MONITOR)
- Rate limit returns 429 after threshold exceeded
- No admin-only data leaks into public responses

### 21.5 Phase 4 — Public Website Frontend (Weeks 6–7)

**Deliverables:**
- All Blade page views: `home`, `cars/index`, `cars/show`, `about`, `faq`, `contact`, `booking`
- Blade layout (`layouts/app.blade.php`), components, and partials implemented
- View Composer injecting shared settings into layout
- Banner slider on home page
- Car listing with category filters, search, and pagination (full server-side render)
- Car detail page with image gallery (no price display)
- Pre-Booking Inquiry Form (`/booking`) — Blade-rendered form; JavaScript generates WhatsApp message on submit
- FAQ accordion (`<details>/<summary>` with optional JS animation)
- Fully responsive RTL design (375px, 768px, 1280px, 1920px breakpoints)
- Vite production build (`npm run build`) produces hashed assets

**Acceptance Criteria:**
- All pages render complete server-side HTML with no client-side data fetching
- Pre-booking form generates correctly structured WhatsApp message
- Car dropdown pre-selects car when arriving from car detail page (`?car=slug`)
- Car listing filters update via full page navigation (URL query parameters)
- Car detail shows all data: images, specs, features (no price shown)
- Website is fully responsive in RTL Arabic layout on mobile and desktop
- Page load time < 2 seconds on standard broadband
- Lighthouse SEO score ≥ 90

### 21.7 Phase 6 — Testing, Polish, and Launch (Week 10)

**Deliverables:**
- Unit tests for all service classes
- Feature tests for all API endpoints
- Security audit: input validation, authentication, file upload
- Performance audit: database queries, cache effectiveness
- Cross-browser testing (Chrome, Firefox, Safari, Edge)
- Mobile testing (iOS Safari, Android Chrome)
- Real content entered by stakeholder
- SSL certificate installed and verified
- Production deployment and smoke testing
- Admin training session

**Acceptance Criteria:**
- All automated tests pass
- No critical security vulnerabilities found
- All real content entered and reviewed by stakeholder
- Website live at production domain
- Admin can independently manage all content

---

## 22. Architectural Decisions

### 22.1 Decision: Separate Admin and Customer Tables

**Decision:** Create a separate `admins` table instead of using a single `users` table with roles.

**Alternatives Considered:**
- Single `users` table with `role` column
- Single `users` table with role-based pivot table

**Chosen Approach Rationale:**
A unified `users` table with roles introduces permission complexity from day one. By separating tables:
- The `admins` guard is completely isolated from any future `customers` guard
- No accidental privilege escalation through role manipulation
- In V2, a `customers` table is added with no changes to the `admins` table or existing auth code

**Accepted Trade-off:** Two authentication tables instead of one. Minor code duplication in authentication controllers.

### 22.2 Decision: Key-Value Settings Table

**Decision:** Store all site settings in a single `settings` table with `key` and `value` columns.

**Alternatives Considered:**
- Separate tables: `company_info`, `contact_info`, `social_links`, etc.
- JSON configuration file in storage
- Environment variables for all settings

**Chosen Approach Rationale:**
Separate tables create migration friction every time a new setting is needed. A key-value store allows new settings to be added without any database migration. The `SettingService` provides typed access (casting based on the `type` column). Redis caching means there is no performance difference — settings are fetched from Redis on every request, not MySQL.

**Accepted Trade-off:** Loss of SQL type enforcement and foreign key support. Mitigated by application-level type casting and validation in `SettingService`.

### 22.3 Decision: Eloquent Models Used Directly in Services (No Repository Pattern)

**Decision:** Service classes use Eloquent models directly. No Repository interfaces or implementations are created in V1.

**Alternatives Considered:**
- Repository Pattern with interfaces (`CarRepositoryInterface`, `EloquentCarRepository`)
- Data Access Objects (DAO)

**Chosen Approach Rationale:**
The Repository Pattern's primary benefit is abstracting the data source so that services can be unit-tested without a database. In a Laravel application that uses Eloquent exclusively, this abstraction adds significant verbosity for limited payoff in V1. Laravel's testing facilities (in-memory SQLite, database transactions) already make service testing fast and reliable without repositories.

Services remain the isolation boundary: controllers never query the database directly, and business logic never leaks into models. If V2 ever requires multiple data sources (e.g., pulling car data from an external fleet API alongside the database), the Repository Pattern can be introduced incrementally into the service layer without any controller changes.

**Accepted Trade-off:** Services are technically coupled to Eloquent. Mitigated by the service boundary — any future database abstraction is introduced only in the service layer.

### 22.4 Decision: Laravel Blade MPA (Not SPA)

**Decision:** The public website is a Laravel Blade Multi-Page Application. Each URL is handled by a Laravel route dispatching to a controller, which renders a complete Blade template server-side. No standalone HTML files exist; no client-side API fetching occurs on public pages.

**Alternatives Considered:**
- Single Page Application (React, Vue, Next.js) consuming the REST API
- Inertia.js with Vue.js
- Standalone static HTML files + JavaScript API fetching

**Chosen Approach Rationale:**
For a Version 1 marketing website, Laravel Blade MPA is the correct level of complexity:
- **SEO:** Each page delivers complete HTML to the browser. Search engines index full content without executing JavaScript. This is the strongest possible SEO foundation.
- **Simplicity:** Laravel controllers, services, and Blade templates follow standard Laravel conventions. No separate frontend project, no client-side router, no framework lifecycle.
- **No data duplication:** The controller calls services directly in the same PHP process. There is no duplicate data-fetching layer.
- **Progressive enhancement:** JavaScript is used only for UI interactions (navigation toggle, gallery, WhatsApp message generation). Removing JavaScript does not break page content.
- **Maintainability:** Adding a new page means a new route, a new controller method, and a new Blade template. Any Laravel developer can immediately understand and extend the system.

**Accepted Trade-off:** No shared client-side state between page navigations (each navigation triggers a new server request). This is natural and expected behaviour for a marketing website.

### 22.5 Decision: Session-Based Admin Authentication (Not Token-Based)

**Decision:** Admin authentication is handled by Laravel Filament's built-in session-based login. No Sanctum tokens are used for admin access.

**Alternatives Considered:**
- Sanctum Bearer tokens stored in `localStorage` (admin SPA pattern)
- Sanctum SPA cookie mode
- Custom admin JWT

**Chosen Approach Rationale:**
Filament is a server-rendered Livewire application. Session-based authentication is the correct and native authentication model for server-rendered applications:
- Session cookies are HttpOnly — inaccessible to JavaScript, XSS-resistant by browser design
- No token management in client-side JavaScript
- No `localStorage` usage — no token theft surface
- CSRF protection applied automatically to all Filament form submissions
- Zero custom authentication code required — Filament handles login, session, and logout natively

**Accepted Trade-off:** Admin access is browser-session-bound. This is the correct behaviour for a web-based admin panel and requires no mitigation.

### 22.6 Decision: JSON Column for Car Specifications

**Decision:** Store car specifications (engine, transmission, fuel type, etc.) in a JSON column on the `cars` table.

**Alternatives Considered:**
- Separate `car_specifications` table
- Predefined columns for each specification type
- EAV (Entity-Attribute-Value) pattern

**Chosen Approach Rationale:**
Car specifications vary significantly between vehicle types. Predefined columns create a wide table with many nullable values. A specifications table creates N+1 risks and complex join queries. JSON column provides complete flexibility: any spec can be added without schema changes, specs are retrieved in a single query with the car, and MySQL 8.0's JSON functions allow querying specific values if needed.

**Accepted Trade-off:** JSON columns can't have column-level SQL indexes. Filtering cars by specification requires application-level filtering — acceptable in V1 where filtering is by category only.

### 22.7 Decision: WhatsApp Redirect (Client-Side Only)

**Decision:** The WhatsApp booking flow is entirely client-side. No server request is made. No inquiry data is stored.

**Alternatives Considered:**
- Log all inquiries in the database for analytics
- Use WhatsApp Business API for server-side message sending
- Send inquiry to email AND WhatsApp simultaneously

**Chosen Approach Rationale:**
- Zero cost (WhatsApp Business API has per-message costs)
- No WhatsApp Business API approval required
- Works on any device with WhatsApp installed
- Completely private — no customer data is processed by our servers

**Accepted Trade-off:** No server-side analytics on inquiry volume. Acceptable within V1 scope.

### 22.8 Decision: Laravel Filament for Admin Dashboard

**Decision:** Use Laravel Filament v5 for the admin dashboard instead of building custom HTML/JS admin pages.

**Alternatives Considered:**
- Custom MPA admin pages (standalone HTML + Vanilla JS consuming admin REST API)
- Vue.js SPA admin consuming the admin REST API
- Laravel Nova (alternative paid admin panel)

**Chosen Approach Rationale:**
- **Development speed:** A Filament Resource delivers a complete CRUD interface (table + form + pages) in a single PHP file. The equivalent in custom HTML/JS requires: HTML page, form JS, API client calls, validation display, image upload component, and error handling — across multiple files.
- **No API round-trip:** Filament accesses Eloquent directly (same PHP process). Faster, simpler, and removes an entire layer of HTTP API calls for admin operations.
- **Security:** Session-based authentication with HttpOnly cookies is the default. No token management in client-side JavaScript.
- **Maintained & documented:** Filament v5 is actively maintained with excellent documentation and a large community ecosystem.
- **V2 compatible:** Filament is additive. New V2 admin features (booking management, fleet management) are added as new Filament Resources without modifying existing ones.

**Accepted Trade-off:** The admin panel is tightly coupled to Filament's conventions. Deep customisation (custom layouts, complex multi-step workflows) requires Filament-specific knowledge. Mitigated by Filament's comprehensive documentation and growing team adoption.

---

## 23. Trade-offs

### 23.1 Pragmatic Architecture vs. Textbook Clean Architecture

**The Trade-off:**
Using Eloquent models directly in services is not "Clean Architecture" by the strictest definition. A purist implementation would require Repository interfaces and domain entities. Our approach couples services to Eloquent.

**Why We Accept This:**
For a V1 marketing website, the benefit of strict Clean Architecture does not justify its complexity cost. Services already act as the isolation boundary between HTTP and data. If V2 requires true data source independence, repositories can be introduced into the existing service layer without touching controllers. The pragmatic choice now does not burn the bridge.

**Mitigation:** All database access is in service classes. Controllers never call Eloquent directly. This discipline alone prevents the most common technical debt patterns.

### 23.2 Blade MPA vs. SPA Trade-offs

**The Trade-off:**
A Blade MPA performs a full server request on each navigation. There is no shared client-side state or animated transitions between pages. The user experience is less "app-like" than an SPA.

**Why We Accept This:**
A car rental marketing website is browsed, not operated like an application. Users visit home, browse cars, view a detail page, then inquire. Server-rendered page loads at each step are natural and expected. The SEO benefit — every page fully indexed without JavaScript execution — is a significant win that outweighs the UX trade-off.

**Mitigation:** Redis caching at the service layer means repeated page visits within a browsing session return server-rendered HTML from cached data. Page load times remain fast (sub-200ms with cache hit). The Blade approach also eliminates the additional round-trip that an SPA would require to fetch data after the shell loads.

### 23.4 V2 Tables Not Pre-Created

**The Trade-off:**
The original architecture included stub tables for V2 (customers, bookings, payments). This has been removed — no V2 tables exist in V1. This means V2 table additions require new migrations and foreign key additions to existing tables (e.g., adding `branch_id` to `cars`). These additive migrations carry risk if not handled carefully.

**Why We Accept This:**
Pre-creating empty tables that serve no function in V1 adds confusion for developers who see tables with no application code. It also creates maintenance burden and potential migration conflicts. V2 tables should be created when V2 is ready, with careful additive migrations and full backups before each.

**Mitigation:** All V2 additive migrations must use `nullable()` columns to avoid breaking existing records. The migration strategy (additive only, no destructive changes) is documented in Section 24.

### 23.5 Single Admin Account vs. RBAC

**The Trade-off:**
V1 has one admin account. No Role-Based Access Control is implemented. If the business needs a second admin before V2 is started, adding one requires a code change (the policies must be revisited).

**Mitigation:**
- Laravel Policies are created for every resource even in V1 (returning `true`)
- The `admins` table has a `role` column (unused but present for V2)
- Adding a second admin account can be done via database seeder immediately
- Full RBAC is added in V2 by updating policies only — controllers need no changes

---

## 24. Future Migration Strategy

### 24.1 Migration Path to Version 2

Version 2 development follows the **Strangler Fig Pattern**: new functionality is added module by module alongside the existing V1 system. No V1 code is modified.

**Migration Order (Recommended):**

```
Step 1: Customer Accounts Module
    - New migration: customers table
    - New Sanctum guard: customer
    - New CustomerAuthController and routes
    - Existing WhatsApp flow unchanged

Step 2: Booking Module
    - New migration: bookings table (references cars.id)
    - New migration: Add availability_status to cars
    - New BookingService
    - New /api/v2/bookings endpoints
    - WhatsApp modal remains as fallback

Step 3: Payment Module
    - New migration: payments table
    - New PaymentGatewayInterface
    - New payment controllers and routes

Step 4: Branch Module
    - New migration: branches table
    - New migration: Add branch_id (nullable) to cars
    - New BranchService and admin section

Step 5: Fleet Management
    - New migration: Add license_plate, vin, availability_status to cars
    - New migration: fleet_status_logs table
    - New FleetService

Step 6: Analytics and Reports
    - New report service classes
    - New admin report pages
```

### 24.2 API Version Migration

V1 API endpoints remain live during the entire V2 migration period.

```
/api/     => V1 endpoints (stable — maintained throughout)
/api/v2/  => V2 endpoints (new features)
```

After full V2 migration: V1 endpoints are deprecated with a 6-month notice, then removed in a planned maintenance window.

### 24.3 Database Migration Strategy

All V2 database changes are **additive only** during the transition period:
- New columns added with `nullable()` — existing records are unaffected
- New tables added — no modifications to existing tables
- No columns renamed or dropped during active migration
- No indexes dropped — only added

**Breaking changes** are batched and executed in a planned maintenance window with a full backup taken immediately before.

### 24.4 Frontend Migration (Optional V2)

If the V2 public website adopts a different frontend approach (e.g., a mobile app consuming the REST API, or an Inertia.js hybrid):

1. The public REST API (`/api/*`) is already available for external consumers — no backend changes needed for a mobile client
2. If the public website itself is replaced (e.g., with a Next.js SSR frontend), the new frontend targets the same `/api/*` and `/api/v2/*` endpoints — backend unchanged
3. The Blade MPA routes in `routes/web.php` and Blade views are removed or consolidated when V2 rendering replaces them
4. Filament admin remains unchanged — it is not part of any frontend migration

Backend services, models, and the REST API require no changes for a frontend-only migration.

### 24.5 Infrastructure Scaling Migration

| Daily Users | Recommended Infrastructure Action |
|-------------|----------------------------------|
| < 5,000 | Current single-server setup sufficient |
| 5,000 – 20,000 | Redis cluster, CDN for assets, DB index review |
| 20,000 – 100,000 | Load balancer, second app server, MySQL read replica |
| 100,000+ | DB sharding, microservice extraction, Kubernetes |

---

## 25. Appendix

### 25.1 Database Schema Reference

This section documents the **8 V1 database tables**. No V2 tables are pre-created. All future tables are added via new migrations when V2 modules are built.

#### `admins` Table
```sql
CREATE TABLE admins (
    id               BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name             VARCHAR(255) NOT NULL,
    email            VARCHAR(255) UNIQUE NOT NULL,
    password         VARCHAR(255) NOT NULL,
    role             VARCHAR(50) DEFAULT 'super_admin',  -- reserved for V2 RBAC
    remember_token   VARCHAR(100) NULL,
    last_login_at    TIMESTAMP NULL,
    last_login_ip    VARCHAR(45) NULL,
    created_at       TIMESTAMP NULL,
    updated_at       TIMESTAMP NULL
);
```

#### `categories` Table
```sql
CREATE TABLE categories (
    id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(255) NOT NULL,
    slug         VARCHAR(255) UNIQUE NOT NULL,
    description  TEXT NULL,
    icon         VARCHAR(100) NULL,
    image        VARCHAR(500) NULL,
    is_active    TINYINT(1) DEFAULT 1,
    sort_order   INT DEFAULT 0,
    created_at   TIMESTAMP NULL,
    updated_at   TIMESTAMP NULL
);
```

#### `cars` Table
```sql
CREATE TABLE cars (
    id               BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id      BIGINT UNSIGNED NOT NULL,
    name             VARCHAR(255) NOT NULL,
    slug             VARCHAR(255) UNIQUE NOT NULL,
    brand            VARCHAR(100) NOT NULL,
    model            VARCHAR(100) NOT NULL,
    year             YEAR NOT NULL,
    color            VARCHAR(100) NULL,
    description      LONGTEXT NULL,
    specifications   JSON NULL,             -- Engine, transmission, seats, etc.
    price_daily      DECIMAL(10,2) UNSIGNED NULL,    -- internal reference; not displayed publicly
    price_weekly     DECIMAL(10,2) UNSIGNED NULL,    -- internal reference; not displayed publicly
    price_monthly    DECIMAL(10,2) UNSIGNED NULL,    -- internal reference; not displayed publicly
    currency         VARCHAR(3) DEFAULT 'AED',
    is_published     TINYINT(1) DEFAULT 0,
    is_featured      TINYINT(1) DEFAULT 0,
    sort_order       INT DEFAULT 0,
    meta_title       VARCHAR(255) NULL,
    meta_description TEXT NULL,
    created_at       TIMESTAMP NULL,
    updated_at       TIMESTAMP NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);
-- Note: no deleted_at column — cars use hard delete.
-- Note: availability_status, branch_id, license_plate, vin
-- are NOT in this table. They will be added via V2 migrations.
```

#### `car_features` Table
```sql
CREATE TABLE car_features (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    car_id      BIGINT UNSIGNED NOT NULL,
    feature     VARCHAR(255) NOT NULL,
    created_at  TIMESTAMP NULL,
    updated_at  TIMESTAMP NULL,
    FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE,
    INDEX (car_id)
);
```

#### `media` Table (Polymorphic)
```sql
CREATE TABLE media (
    id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    model_type   VARCHAR(255) NOT NULL,         -- e.g., 'App\Models\Car'
    model_id     BIGINT UNSIGNED NOT NULL,
    collection   VARCHAR(100) NOT NULL,         -- e.g., 'car_images', 'banners'
    file_name    VARCHAR(255) NOT NULL,
    file_path    VARCHAR(500) NOT NULL,
    mime_type    VARCHAR(100) NOT NULL,
    size         INT UNSIGNED NOT NULL,         -- bytes
    is_cover     TINYINT(1) DEFAULT 0,
    order_column INT DEFAULT 0,
    created_at   TIMESTAMP NULL,
    updated_at   TIMESTAMP NULL,
    INDEX idx_media_model (model_type, model_id),
    INDEX idx_media_collection (collection)
);
```

#### `faqs` Table
```sql
CREATE TABLE faqs (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    question    TEXT NOT NULL,
    answer      LONGTEXT NOT NULL,
    category    VARCHAR(100) NULL,
    is_active   TINYINT(1) DEFAULT 1,
    sort_order  INT DEFAULT 0,
    created_at  TIMESTAMP NULL,
    updated_at  TIMESTAMP NULL
);
```

#### `banners` Table
```sql
CREATE TABLE banners (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(255) NULL,
    subtitle    VARCHAR(500) NULL,
    image       VARCHAR(500) NULL,
    cta_text    VARCHAR(100) NULL,
    cta_url     VARCHAR(500) NULL,
    is_active   TINYINT(1) DEFAULT 1,
    sort_order  INT DEFAULT 0,
    created_at  TIMESTAMP NULL,
    updated_at  TIMESTAMP NULL
);
```

#### `settings` Table
```sql
CREATE TABLE settings (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key`           VARCHAR(255) UNIQUE NOT NULL,
    value           LONGTEXT NULL,
    type            ENUM('string','integer','boolean','json','text') DEFAULT 'string',
    settings_group  VARCHAR(100) NOT NULL,
    description     VARCHAR(500) NULL,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,
    INDEX (settings_group)
);
```

> **Note on V2 Tables:** Tables for customers, bookings, payments, branches, and drivers do not exist in V1. They are created as new migrations in V2. See Section 14 for the module-by-module addition strategy.

### 25.2 Technology Stack Summary

| Category | Technology | Version | Justification |
|----------|-----------|---------|---------------|
| Backend Framework | Laravel | 12.x | Latest stable, extensive ecosystem, active maintenance |
| Language | PHP | 8.3 | Current stable, strong type system, performance improvements |
| Admin Panel | Laravel Filament | 5.x | Server-rendered admin UI built on Livewire; full CRUD in single PHP class |
| Database | MySQL | 8.0 | Mature, JSON column support, full-text search, wide hosting support |
| Cache & Queue | Redis | 7.x | In-memory speed, queue support, rate limit tracking |
| Web Server | NGINX | 1.26 | High performance, efficient static asset serving, reverse proxy to PHP-FPM |
| Process Manager | PHP-FPM | 8.3 | Standard PHP process manager |
| Media Library | Spatie Media Library | Latest | Polymorphic file management; Filament integration; manages `media` table |
| Image Processing | Intervention Image | 3.x | PHP image manipulation, GD and ImageMagick backends |
| Asset Pipeline | Laravel Vite | — | CSS/JS bundling with HMR in development; content-hashed output for production |
| Public Frontend | Laravel Blade + Vanilla CSS + Vanilla JS | — | Server-rendered MPA; progressive JS enhancement only |
| Task Queue | Laravel Queue + Redis | — | Built-in, Redis backend for reliability |
| Queue Supervisor | Supervisor | Latest | OS-level process management for queue workers |
| SSL | Let's Encrypt (Certbot) | Latest | Free, auto-renewing, industry standard |
| Version Control | Git | Latest | Industry standard |

### 25.3 Recommended Laravel Packages

| Package | Purpose | Required? |
|---------|---------|-----------|
| `filament/filament` | Admin panel framework (Livewire/Blade, CRUD resources, widgets) | Yes |
| `spatie/laravel-media-library` | Polymorphic file/image management; `media` table; Filament integration | Yes |
| `spatie/laravel-sluggable` | Automatic slug generation from name fields | Yes |
| `intervention/image` | Image resizing and thumbnail generation | Recommended |
| `spatie/laravel-query-builder` | Eloquent query filtering from HTTP params (public REST API) | Recommended |
| `spatie/laravel-activitylog` | Admin activity audit logging | Recommended |
| `sentry/sentry-laravel` | Real-time error monitoring | Recommended for Production |

### 25.4 Development Environment Setup

**PHP Extensions Required:**
BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, PDO_MySQL, Tokenizer, XML, GD (or ImageMagick)

**Local Setup Steps:**
```bash
git clone {repository_url}
cd laravel-car-rental
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link
php artisan serve
```

**Redis for Local Development (Docker):**
```bash
docker run -d --name redis -p 6379:6379 redis:7-alpine
```

### 25.5 Glossary

| Term | Definition |
|------|------------|
| **Blade MPA** | A Multi-Page Application built with Laravel Blade. Each URL is a separate server-rendered page. The browser receives complete HTML from Laravel. No client-side routing or API fetching for page content |
| **Blade Template** | A Laravel templating engine file (`.blade.php`) that generates server-side HTML. Data is passed from the controller and embedded in HTML before the browser receives it |
| **Filament** | A Laravel admin panel framework built on Livewire and Alpine.js. Generates full CRUD interfaces (tables, forms, pages) from PHP Resource classes. Used for the admin dashboard in this project |
| **Livewire** | A Laravel full-stack framework that allows writing reactive, dynamic UI in PHP/Blade without custom JavaScript. Used internally by Filament |
| **Filament Resource** | A PHP class in Filament that defines the table, form, and pages for managing a single Eloquent model in the admin panel |
| **REST API** | Representational State Transfer API — an HTTP-based API following resource-oriented conventions. In this project: public, read-only, at `/api/*`, for future external consumers |
| **Progressive Enhancement** | Adding JavaScript behaviour to a fully-functional server-rendered page to improve the user experience. If JavaScript fails, the page content and core functionality remain intact |
| **Eloquent Service Pattern** | A pattern where service classes contain all business logic and use Eloquent models directly for data access, with controllers remaining thin |
| **DTO** | Data Transfer Object — a simple object that carries data between layers without containing business logic |
| **Hard Delete** | Permanently removing a record from the database. Used in this project for all admin deletions. Accidental deletes are recovered by re-creating the record |
| **CORS** | Cross-Origin Resource Sharing — browser security mechanism controlling which origins can access an API |
| **Redis** | An in-memory data structure store used for caching, sessions, and task queues |
| **Spatie Media Library** | A Laravel package for managing file uploads polymorphically. Stores file metadata in the `media` table. The `car_images` collection is a logical grouping within this table — not a separate database table |
| **Polymorphic Relation** | An Eloquent relationship where one model can belong to multiple different model types (e.g., media can belong to Car or Banner) |
| **Eager Loading** | Loading related models in a single query using `with()` to avoid the N+1 problem |
| **N+1 Problem** | A performance antipattern where fetching N related records requires N+1 individual database queries |
| **Modular Monolith** | A single deployable application organised into clearly bounded, loosely coupled modules |
| **Strangler Fig Pattern** | Adding new functionality alongside old code, gradually replacing old features without a big-bang rewrite |
| **RBAC** | Role-Based Access Control — a system where permissions are assigned to roles, and roles to users |
| **HSTS** | HTTP Strict Transport Security — a web security policy forcing HTTPS connections |
| **HttpOnly Cookie** | A browser cookie inaccessible to JavaScript, protecting its contents from XSS attacks |
| **Evolutionary Architecture** | An architecture designed to absorb planned future changes with minimal structural disruption |
| **WhatsApp Redirect** | A client-side URL redirect to `wa.me` that opens WhatsApp with a pre-filled message — no server involvement |
| **Additive Migration** | A database migration that only adds new columns or tables, never modifying or removing existing structure |
| **Vite** | A frontend asset build tool used by Laravel for bundling CSS and JavaScript. Outputs content-hashed files for production cache busting |

### 25.6 Recommended Reading

- Taylor Otwell — *Laravel Documentation* (laravel.com/docs)
- Martin Fowler — *Patterns of Enterprise Application Architecture*
- Martin Fowler — *Refactoring: Improving the Design of Existing Code*
- Sam Newman — *Building Microservices* (for V2 extraction reference)
- Spatie — *Laravel Package Guidelines* (spatie.be/guidelines/laravel-package)

---

*End of Document*

---

**Document Metadata:**

| Field | Value |
|-------|-------|
| Document Name | System_Architecture_Plan.md |
| Version | 1.5.0 |
| Created | August 2026 |
| Last Updated | August 2026 |
| Status | Final — Approved for Implementation |
| Summary | Laravel Modular Monolith; Blade MPA public website; Filament v5 admin; public read-only REST API at `/api/*`; 8 V1 database tables; Spatie Media Library for car images; no admin REST API in V1; no static HTML files |
| Next Review | Upon V2 initiation |
| Repository Path | `/docs/System_Architecture_Plan.md` |
