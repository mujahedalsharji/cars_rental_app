<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Support\CanonicalUrl;
use App\Support\ServiceCatalog;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(ServiceCatalog $serviceCatalog, CanonicalUrl $canonicalUrl): Response
    {
        $staticPaths = [
            route('home', absolute: false),
            route('cars.index', absolute: false),
            route('services', absolute: false),
            route('about', absolute: false),
            route('faq.index', absolute: false),
            route('contact', absolute: false),
            route('booking', absolute: false),
        ];

        $staticPaths = [
            ...$staticPaths,
            ...collect($serviceCatalog->slugs())
                ->map(fn (string $service): string => route('services.show', $service, false))
                ->all(),
        ];

        $staticUrls = collect($staticPaths)
            ->map(fn (string $path): string => $canonicalUrl->fromPath($path))
            ->all();

        $cars = Car::query()
            ->published()
            ->select(['id', 'slug', 'updated_at'])
            ->orderBy('id')
            ->get();

        return response()
            ->view('seo.sitemap', compact('staticUrls', 'cars', 'canonicalUrl'))
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
