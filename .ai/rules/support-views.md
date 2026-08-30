---
paths:
  - 'app/Support/StructuredData.php|resources/views/**'
---

# Support Views

## Keep JSON-LD centralized and factual
Build public JSON-LD through App\Support\StructuredData and render it with the structured-data Blade component. Source organization/contact/social values from settings, use CanonicalUrl for production URLs, and omit address, hours, ratings, or reviews unless verified data exists.
