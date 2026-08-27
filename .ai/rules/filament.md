---
paths:
  - 'app/Models/User.php|app/Filament/Resources/Users/**|app/Providers/Filament/AdminPanelProvider.php'
---

# Filament

## Require explicit administrator access
Only users with is_admin=true may access the admin panel. The configured ADMIN_EMAIL remains a migration fallback for the original account; administrator accounts are managed through the Filament Users resource.
