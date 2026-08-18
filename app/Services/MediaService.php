<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaService
{
    /** @var list<string> */
    private const ALLOWED_MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    /** @var list<string> */
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    private const MAX_SIZE_BYTES = 5_242_880; // 5 MB

    /**
     * Validate and store an image to the public disk under the given folder.
     * Returns the relative path (e.g. "banners/uuid.webp").
     *
     * @throws \InvalidArgumentException
     */
    public function storeImage(UploadedFile $file, string $folder): string
    {
        if (! in_array($file->getMimeType(), self::ALLOWED_MIME_TYPES, true)) {
            throw new \InvalidArgumentException("Invalid image MIME type: {$file->getMimeType()}");
        }

        if (! in_array(strtolower((string) $file->getClientOriginalExtension()), self::ALLOWED_EXTENSIONS, true)) {
            throw new \InvalidArgumentException('Invalid image extension.');
        }

        if ($file->getSize() > self::MAX_SIZE_BYTES) {
            throw new \InvalidArgumentException('Image must not exceed 5 MB.');
        }

        $extension = strtolower((string) $file->getClientOriginalExtension());
        $filename = Str::uuid().'.'.$extension;

        $file->storeAs($folder, $filename, 'public');

        return "{$folder}/{$filename}";
    }

    /**
     * Delete an image file from the public disk.
     * Silently ignores null paths.
     */
    public function deleteImage(?string $path): void
    {
        if ($path !== null) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * Resolve the full public URL for a stored file path.
     */
    public function resolveUrl(?string $path): ?string
    {
        if ($path === null) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }
}
