@props(['car', 'index' => 0])

@php
    $fallbacks = ['fleet-bmw-7.png', 'fleet-gmc-yukon.png', 'fleet-staria.png'];
    $mediaUrl = $car->getFirstMediaUrl('car_images');
    $imageUrl = $mediaUrl !== '' ? $mediaUrl : asset('assets/images/'.$fallbacks[$index % count($fallbacks)]);
@endphp

<article {{ $attributes->merge(['class' => 'group overflow-hidden rounded-3xl border border-white/10 bg-panel transition duration-300 hover:-translate-y-1 hover:border-gold/45']) }}>
    <a href="{{ route('cars.show', $car->slug) }}" class="block overflow-hidden">
        <img src="{{ $imageUrl }}" alt="{{ $car->name }}" class="aspect-[4/3] w-full object-cover transition duration-500 group-hover:scale-[1.03]">
    </a>
    <div class="grid gap-5 p-6">
        <div>
            <p class="text-xs font-semibold text-gold">{{ $car->category?->name ?? 'سيارة فاخرة' }}</p>
            <h2 class="mt-2 text-xl font-bold text-white">{{ $car->name }}</h2>
            <p class="mt-2 text-sm text-white/55">{{ $car->brand }} · {{ $car->model }} · {{ $car->year }}</p>
        </div>
        <div class="flex items-center justify-between gap-4 border-t border-white/8 pt-4">
            @if($car->price_daily)
                <p class="text-sm text-white/55"><strong class="text-lg text-gold-light">{{ number_format((float) $car->price_daily) }}</strong> {{ $car->currency }} / يوم</p>
            @else
                <p class="text-sm text-white/55">السعر عند الطلب</p>
            @endif
            <a href="{{ route('cars.show', $car->slug) }}" class="inline-flex items-center gap-2 text-sm font-bold text-gold-light hover:text-white">التفاصيل <x-heroicon-o-arrow-left class="size-4" /></a>
        </div>
    </div>
</article>
