@extends('layouts.app')

@php
    $media = $car->getMedia('car_images');
    $fallbackImage = asset('assets/images/fleet-gmc-yukon.png');
    $coverImage = $media->first()?->getUrl() ?? $fallbackImage;
@endphp

@section('title', ($car->meta_title ?: $car->name).' | فخامة مسافر')
@section('meta_description', $car->meta_description ?: 'احجز '.$car->name.' مع سائق محترف من فخامة مسافر.')

@section('content')
    <section class="px-5 py-12 lg:px-8 lg:py-20">
        <div class="mx-auto max-w-7xl">
            <nav aria-label="مسار التنقل" class="mb-8 flex flex-wrap items-center gap-2 text-sm text-white/45">
                <a href="{{ route('home') }}" class="hover:text-gold-light">الرئيسية</a><span>/</span>
                <a href="{{ route('cars.index') }}" class="hover:text-gold-light">السيارات</a><span>/</span>
                <span class="text-white/75">{{ $car->name }}</span>
            </nav>

            <div class="grid gap-10 lg:grid-cols-[1.15fr_0.85fr] lg:items-start">
                <div>
                    <img src="{{ $coverImage }}" alt="{{ $car->name }}" class="aspect-[4/3] w-full rounded-[2rem] border border-white/10 object-cover">
                    @if($media->count() > 1)
                        <div class="mt-4 grid grid-cols-3 gap-4">
                            @foreach($media->skip(1)->take(3) as $image)<img src="{{ $image->getUrl() }}" alt="{{ $car->name }} - صورة {{ $loop->iteration + 1 }}" class="aspect-[4/3] w-full rounded-2xl border border-white/10 object-cover">@endforeach
                        </div>
                    @endif
                </div>

                <div class="lg:sticky lg:top-36">
                    <p class="text-sm font-bold text-gold">{{ $car->category?->name ?? 'سيارة فاخرة' }}</p>
                    <h1 class="mt-3 text-4xl font-extrabold text-white sm:text-5xl">{{ $car->name }}</h1>
                    <p class="mt-4 text-base text-white/55">{{ $car->brand }} · {{ $car->model }} · {{ $car->year }}</p>
                    <p class="mt-7 text-base leading-9 text-white/65">{{ $car->description ?: 'سيارة حديثة مجهزة لتمنحك رحلة هادئة ومريحة مع أحد سائقي فخامة مسافر المحترفين.' }}</p>

                    <div class="mt-8 grid grid-cols-2 gap-3">
                        @foreach([
                            ['icon' => 'heroicon-o-calendar-days', 'label' => 'الموديل', 'value' => $car->year],
                            ['icon' => 'heroicon-o-swatch', 'label' => 'اللون', 'value' => $car->color ?: 'حسب المتاح'],
                            ['icon' => 'heroicon-o-tag', 'label' => 'الفئة', 'value' => $car->category?->name ?: 'فاخرة'],
                            ['icon' => 'heroicon-o-shield-check', 'label' => 'الخدمة', 'value' => 'مع سائق'],
                        ] as $detail)
                            <div class="rounded-2xl border border-white/10 bg-panel p-4"><x-dynamic-component :component="$detail['icon']" class="size-5 text-gold" /><p class="mt-3 text-xs text-white/40">{{ $detail['label'] }}</p><p class="mt-1 font-bold text-white">{{ $detail['value'] }}</p></div>
                        @endforeach
                    </div>

                    @if($car->price_daily)
                        <div class="mt-8 flex items-end justify-between gap-5 border-y border-white/10 py-6"><span class="text-sm text-white/45">يبدأ من</span><p><strong class="text-3xl text-gold-light">{{ number_format((float) $car->price_daily) }}</strong> <span class="text-sm text-white/50">{{ $car->currency }} / يوم</span></p></div>
                    @endif

                    <a href="{{ route('booking', ['car' => $car->slug]) }}" class="gold-surface mt-8 inline-flex min-h-14 w-full items-center justify-center gap-3 rounded-2xl px-6 font-bold text-ink transition hover:brightness-110">اطلب هذه السيارة <x-heroicon-o-arrow-left class="size-5" /></a>
                </div>
            </div>

            @if($car->features->isNotEmpty() || filled($car->specifications))
                <div class="mt-16 grid gap-8 rounded-[2rem] border border-white/10 bg-panel p-7 md:grid-cols-2 sm:p-10">
                    <div><h2 class="text-2xl font-bold text-white">المزايا</h2><ul class="mt-6 grid gap-4 text-sm text-white/65">@forelse($car->features as $feature)<li class="flex items-center gap-3"><x-heroicon-o-check-circle class="size-5 shrink-0 text-gold" />{{ $feature->feature }}</li>@empty<li>راحة وخصوصية طوال الرحلة</li>@endforelse</ul></div>
                    <div><h2 class="text-2xl font-bold text-white">المواصفات</h2><dl class="mt-6 grid gap-4 text-sm">@foreach(($car->specifications ?? []) as $key => $value)<div class="flex items-center justify-between gap-4 border-b border-white/8 pb-3"><dt class="text-white/45">{{ str($key)->replace('_', ' ')->headline() }}</dt><dd class="font-semibold text-white">{{ is_array($value) ? implode('، ', $value) : $value }}</dd></div>@endforeach</dl></div>
                </div>
            @endif
        </div>
    </section>
@endsection
