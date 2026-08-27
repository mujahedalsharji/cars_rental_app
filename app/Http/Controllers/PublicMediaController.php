<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicMediaController extends Controller
{
    public function __invoke(string $path): StreamedResponse
    {
        abort_if(Str::contains($path, ['..', '\\', "\0"]), 404);

        $disk = Storage::disk('public');
        abort_unless($disk->exists($path), 404);
        abort_unless(Str::startsWith((string) $disk->mimeType($path), 'image/'), 404);

        return $disk->response($path, headers: [
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
}
