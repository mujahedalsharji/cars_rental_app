@extends('layouts.app')

@php
    $media = $car->getMedia('car_images');
    $fallbackImage = asset('assets/images/fleet-gmc-yukon.png');
    $coverImage = $media->first()?->getUrl() ?? $fallbackImage;
@endphp

@section('title', ($car->meta_title ?: $car->name).' | فخامة مسافر')
@section('meta_description', $car->meta_description ?: 'احجز '.$car->name.' مع سائق محترف من فخامة مسافر.')

@section('content')

    <section class="px-4 py-10 sm:px-6 sm:py-14 lg:px-8 lg:py-20">
        <div class="mx-auto max-w-7xl">

            {{-- Breadcrumb --}}
            <nav aria-label="مسار التنقل" class="mb-6 flex flex-wrap items-center gap-1.5 text-xs text-white/40 sm:text-sm">
                <a href="{{ route('home') }}" class="hover:text-gold-light">الرئيسية</a>
                <span>/</span>
                <a href="{{ route('cars.index') }}" class="hover:text-gold-light">السيارات</a>
                <span>/</span>
                <span class="text-white/70">{{ $car->name }}</span>
            </nav>

            {{-- Main grid --}}
            <div class="grid gap-8 lg:grid-cols-[1.2fr_0.8fr] lg:items-start">

                {{-- Images --}}
                <div>
                    <img src="{{ $coverImage }}" alt="{{ $car->name }}"
                         class="aspect-[4/3] w-full rounded-3xl border border-white/10 object-cover">
                    @if($media->count() > 1)
                        <div class="mt-3 grid grid-cols-3 gap-3">
                            @foreach($media->skip(1)->take(3) as $image)
                                <img src="{{ $image->getUrl() }}" alt="{{ $car->name }}"
                                     class="aspect-[4/3] w-full rounded-2xl border border-white/10 object-cover">
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Details --}}
                <div class="lg:sticky lg:top-32">
                    <p class="text-xs font-bold uppercase tracking-widest text-gold">{{ $car->category?->name ?? 'سيارة فاخرة' }}</p>
                    <h1 class="mt-2 text-3xl font-extrabold text-white sm:text-4xl">{{ $car->name }}</h1>
                    <p class="mt-2 text-sm text-white/50">{{ $car->brand }} · {{ $car->model }} · {{ $car->year }}</p>
                    <p class="mt-5 text-sm leading-8 text-white/60 sm:text-base sm:leading-9">
                        {{ $car->description ?: 'سيارة حديثة مجهزة لتمنحك رحلة هادئة ومريحة مع أحد سائقي فخامة مسافر المحترفين.' }}
                    </p>

                    {{-- Specs grid --}}
                    <div class="mt-6 grid grid-cols-2 gap-3">
                        @foreach([
                            ['icon' => 'heroicon-o-calendar-days', 'label' => 'الموديل', 'value' => $car->year],
                            ['icon' => 'heroicon-o-swatch', 'label' => 'اللون', 'value' => $car->color ?: 'حسب المتاح'],
                            ['icon' => 'heroicon-o-tag', 'label' => 'الفئة', 'value' => $car->category?->name ?: 'فاخرة'],
                            ['icon' => 'heroicon-o-shield-check', 'label' => 'الخدمة', 'value' => 'مع سائق'],
                        ] as $detail)
                            <div class="rounded-2xl border border-white/10 bg-panel p-4">
                                <x-dynamic-component :component="$detail['icon']" class="size-5 text-gold" />
                                <p class="mt-2 text-xs text-white/40">{{ $detail['label'] }}</p>
                                <p class="mt-1 text-sm font-bold text-white">{{ $detail['value'] }}</p>
                            </div>
                        @endforeach
                    </div>

                    {{-- Price --}}
                    @if($car->price_daily)
                        <div class="mt-6 flex items-end justify-between gap-4 border-y border-white/10 py-5">
                            <span class="text-sm text-white/40">يبدأ من</span>
                            <p>
                                <strong class="text-3xl font-extrabold text-gold-light">{{ number_format((float) $car->price_daily) }}</strong>
                                <span class="text-sm text-white/45"> {{ $car->currency }} / يوم</span>
                            </p>
                        </div>
                    @endif

                    {{-- CTA --}}
                    <a href="{{ route('booking', ['car' => $car->slug]) }}"
                       class="gold-surface mt-6 inline-flex min-h-13 w-full items-center justify-center gap-3 rounded-2xl px-6 font-bold text-ink transition hover:brightness-110">
                        اطلب هذه السيارة
                        <x-heroicon-o-arrow-left class="size-5" />
                    </a>
                </div>
            </div>

            {{-- Features & Specs --}}
            @if($car->features->isNotEmpty() || filled($car->specifications))
                <div class="mt-12 grid gap-8 rounded-3xl border border-white/10 bg-panel p-6 sm:grid-cols-2 sm:p-10">
                    <div>
                        <h2 class="text-xl font-bold text-white sm:text-2xl">المزايا</h2>
                        <ul class="mt-5 grid gap-3 text-sm text-white/60">
                            @forelse($car->features as $feature)
                                <li class="flex items-center gap-3">
                                    <x-heroicon-o-check-circle class="size-5 shrink-0 text-gold" />
                                    {{ $feature->feature }}
                                </li>
                            @empty
                                <li>راحة وخصوصية طوال الرحلة</li>
                            @endforelse
                        </ul>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-white sm:text-2xl">المواصفات</h2>
                        <dl class="mt-5 grid gap-3 text-sm">
                            @foreach(($car->specifications ?? []) as $key => $value)
                                <div class="flex items-center justify-between gap-4 border-b border-white/8 pb-3">
                                    <dt class="text-white/45">{{ str($key)->replace('_', ' ')->headline() }}</dt>
                                    <dd class="font-semibold text-white">{{ is_array($value) ? implode('، ', $value) : $value }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                </div>
            @endif

        </div>
    </section>

@endsection
