<?php

namespace App\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class StructuredData
{
    public function __construct(private CanonicalUrl $canonicalUrl) {}

    /**
     * @param  array<string, array<string, mixed>>  $settings
     * @return array<string, mixed>
     */
    public function organizationAndWebsite(array $settings, string $logoUrl): array
    {
        $company = $settings['company'] ?? [];
        $contact = $settings['contact'] ?? [];
        $social = $settings['social'] ?? [];
        $homeUrl = $this->canonicalUrl->fromPath('/');
        $organizationId = $homeUrl.'#organization';
        $companyName = ($company['name'] ?? null) === 'Cars Rental'
            ? 'فخامة مسافر'
            : ($company['name'] ?? 'فخامة مسافر');

        $organization = array_filter([
            '@type' => 'Organization',
            '@id' => $organizationId,
            'name' => $companyName,
            'url' => $homeUrl,
            'logo' => $this->siteAssetUrl($logoUrl),
            'description' => $company['description']
                ?? $company['tagline']
                ?? 'خدمات سيارات خاصة مع سائق في المملكة العربية السعودية.',
            'areaServed' => $this->saudiArabia(),
            'contactPoint' => $this->contactPoint($contact),
            'sameAs' => $this->socialProfiles($social),
        ], fn (mixed $value): bool => filled($value));

        return [
            '@context' => 'https://schema.org',
            '@graph' => [
                $organization,
                [
                    '@type' => 'WebSite',
                    '@id' => $homeUrl.'#website',
                    'url' => $homeUrl,
                    'name' => $companyName,
                    'inLanguage' => 'ar',
                    'publisher' => ['@id' => $organizationId],
                ],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $service
     * @return array<string, mixed>
     */
    public function service(array $service): array
    {
        $serviceUrl = $this->canonicalUrl->fromPath(
            route('services.show', ['service' => $service['slug']], false),
        );

        return [
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            '@id' => $serviceUrl.'#service',
            'url' => $serviceUrl,
            'name' => $service['title'],
            'serviceType' => $service['card_title'],
            'description' => $service['meta_description'],
            'image' => $this->canonicalUrl->fromPath($service['image']),
            'inLanguage' => 'ar',
            'areaServed' => $this->saudiArabia(),
            'provider' => ['@id' => $this->canonicalUrl->fromPath('/').'#organization'],
        ];
    }

    /**
     * @param  array<string, mixed>  $service
     * @return array<string, mixed>
     */
    public function serviceBreadcrumbs(array $service): array
    {
        return $this->breadcrumbs([
            ['name' => 'الرئيسية', 'path' => route('home', absolute: false)],
            ['name' => 'الخدمات', 'path' => route('services', absolute: false)],
            [
                'name' => $service['card_title'],
                'path' => route('services.show', ['service' => $service['slug']], false),
            ],
        ]);
    }

    /**
     * @param  list<array{name: string, path: string}>  $items
     * @return array<string, mixed>
     */
    public function breadcrumbs(array $items): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($items)
                ->values()
                ->map(fn (array $item, int $index): array => [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => $item['name'],
                    'item' => $this->canonicalUrl->fromPath($item['path']),
                ])
                ->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $contact
     * @return array<string, mixed>|null
     */
    private function contactPoint(array $contact): ?array
    {
        $telephone = Arr::first([
            $contact['phone_primary'] ?? null,
            $contact['whatsapp_number'] ?? null,
        ], fn (mixed $value): bool => filled($value));
        $email = $contact['email'] ?? null;

        if (blank($telephone) && blank($email)) {
            return null;
        }

        return array_filter([
            '@type' => 'ContactPoint',
            'contactType' => 'customer service',
            'telephone' => $telephone,
            'email' => $email,
            'availableLanguage' => ['ar'],
        ], fn (mixed $value): bool => filled($value));
    }

    /**
     * @param  array<string, mixed>  $social
     * @return list<string>
     */
    private function socialProfiles(array $social): array
    {
        return collect($social)
            ->filter(fn (mixed $url): bool => is_string($url) && filter_var($url, FILTER_VALIDATE_URL) !== false)
            ->values()
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function saudiArabia(): array
    {
        return [
            '@type' => 'Country',
            'name' => 'المملكة العربية السعودية',
        ];
    }

    private function siteAssetUrl(string $url): string
    {
        if (! Str::startsWith($url, ['http://', 'https://'])) {
            return $this->canonicalUrl->fromPath($url);
        }

        $path = parse_url($url, PHP_URL_PATH);

        return is_string($path) ? $this->canonicalUrl->fromPath($path) : $url;
    }
}
