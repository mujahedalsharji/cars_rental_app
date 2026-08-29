<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Support\ServiceCatalog;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(ServiceCatalog $serviceCatalog): Response
    {
        $staticUrls = [
            route('home'),
            route('cars.index'),
            route('services'),
            route('about'),
            route('faq.index'),
            route('contact'),
            route('booking'),
        ];

        $staticUrls = [
            ...$staticUrls,
            ...collect($serviceCatalog->slugs())
                ->map(fn (string $service): string => route('services.show', $service))
                ->all(),
        ];

        $cars = Car::query()
            ->published()
            ->select(['id', 'slug', 'updated_at'])
            ->orderBy('id')
            ->get();

        return response()
            ->view('seo.sitemap', compact('staticUrls', 'cars'))
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
