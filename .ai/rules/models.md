---
paths:
  - 'app/Models/*.php'
---

# Models

## Use native Laravel slug generation
Generate Car and Category slugs with Illuminate\Support\Str::slug() in model creating events. Preserve explicit slugs, suffix collisions with -2/-3, keep unique database indexes, and do not regenerate slugs during normal updates. Do not add a third-party slug package.
