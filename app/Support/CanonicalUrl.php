<?php

namespace App\Support;

use Illuminate\Http\Request;

class CanonicalUrl
{
    public function fromRequest(Request $request): string
    {
        return $this->fromPath($request->getPathInfo());
    }

    public function fromPath(string $path): string
    {
        $normalizedPath = '/'.ltrim($path, '/');
        $normalizedPath = $normalizedPath === '/' ? '/' : rtrim($normalizedPath, '/');

        return rtrim((string) config('app.url'), '/').$normalizedPath;
    }
}
