---
paths:
  - 'config/filesystems.php|app/Http/Controllers/PublicMediaController.php|routes/web.php|resources/views/**'
---

# Views

## Serve public uploads through the media route
Generate public-disk URLs from FILESYSTEM_PUBLIC_URL, defaulting to /media. The Namecheap deployment returns 403 for public/storage symlink URLs, so public images and settings assets must be streamed through PublicMediaController.
