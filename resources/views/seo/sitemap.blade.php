{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @foreach ($staticUrls as $url)
        <url>
            <loc>{{ $url }}</loc>
        </url>
    @endforeach
    @foreach ($cars as $car)
        <url>
            <loc>{{ $canonicalUrl->fromPath(route('cars.show', ['slug' => $car->slug], false)) }}</loc>
            <lastmod>{{ $car->updated_at->toAtomString() }}</lastmod>
        </url>
    @endforeach
</urlset>
