@extends('layouts.app')

@section('title', 'أسطول السيارات | فخامة مسافر')
@section('meta_description', 'تصفح أسطول فخامة مسافر من السيارات الفاخرة واختر المركبة المناسبة لرحلتك.')

@section('content')

    <x-page-hero eyebrow="أسطولنا" title="سيارة تليق بكل وجهة"
        description="اختر من سياراتنا الحديثة، ودع فريقنا يرتب لك رحلة مريحة وآمنة." />

    <section class="px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
        <div class="mx-auto max-w-7xl">

            {{-- Filter --}}
            <form method="GET" action="{{ route('cars.index') }}"
                  class="grid grid-cols-1 gap-3 rounded-3xl border border-white/10 bg-panel p-4 sm:grid-cols-[1fr_auto] sm:p-5 md:grid-cols-[1fr_0.5fr_auto]">
                <label class="grid gap-1.5 text-xs text-white/60">
                    ابحث عن سيارة
                    <span class="flex items-center gap-2 rounded-xl border border-white/10 bg-black/20 px-3 focus-within:border-gold/60">
                        <x-heroicon-o-magnifying-glass class="size-4 text-gold" />
                        <input type="search" name="search" value="{{ request('search') }}"
                               placeholder="الاسم أو العلامة التجارية"
                               class="min-h-11 w-full bg-transparent text-sm text-white outline-none placeholder:text-white/30">
                    </span>
                </label>
                <label class="grid gap-1.5 text-xs text-white/60">
                    الفئة
                    <select name="category"
                            class="min-h-11 rounded-xl border border-white/10 bg-ink px-3 text-sm text-white outline-none focus:border-gold/60">
                        <option value="">جميع الفئات</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </label>
                <button class="gold-surface min-h-11 self-end rounded-xl px-6 text-sm font-bold text-ink transition hover:brightness-110">
                    بحث
                </button>
            </form>

            {{-- Results --}}
            @if($cars->isNotEmpty())
                <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($cars as $car)
                        <x-car-card :car="$car" :index="$loop->index" />
                    @endforeach
                </div>
                <div class="mt-8">{{ $cars->withQueryString()->links() }}</div>
            @else
                <div class="mt-8 rounded-3xl border border-gold/15 bg-panel p-8 text-center sm:p-12">
                    @if(request()->hasAny(['search', 'category']))
                        <x-heroicon-o-magnifying-glass class="mx-auto size-12 text-gold" />
                        <h2 class="mt-5 text-xl font-bold text-white sm:text-2xl">لم نجد سيارات مطابقة</h2>
                        <p class="mt-3 text-sm text-white/50">جرّب إزالة عوامل التصفية أو تواصل معنا.</p>
                        <a href="{{ route('cars.index') }}"
                           class="mt-6 inline-flex rounded-xl border border-gold/50 px-5 py-2.5 text-sm font-bold text-gold-light">
                            مسح التصفية
                        </a>
                    @else
                        <h2 class="text-xl font-bold text-white sm:text-2xl">الفئات الأكثر طلباً</h2>
                        <p class="mt-3 text-sm text-white/50">ستظهر سيارات لوحة التحكم هنا تلقائياً.</p>
                        <div class="mt-8 grid gap-5 sm:grid-cols-3">
                            @foreach([
                                ['image' => 'fleet-bmw-7.png', 'name' => 'سيدان فاخرة'],
                                ['image' => 'fleet-gmc-yukon.png', 'name' => 'SUV فاخرة'],
                                ['image' => 'fleet-staria.png', 'name' => 'عائلية فاخرة'],
                            ] as $category)
                                <a href="{{ route('booking', ['car_name' => $category['name']]) }}"
                                   class="group overflow-hidden rounded-2xl border border-white/10 bg-black/20 text-start transition hover:border-gold/50">
                                    <img src="{{ asset('assets/images/'.$category['image']) }}" alt="{{ $category['name'] }}"
                                         class="aspect-[4/3] w-full object-cover transition duration-500 group-hover:scale-[1.03]">
                                    <span class="flex items-center justify-between gap-3 p-4 text-sm font-bold text-white">
                                        {{ $category['name'] }}
                                        <x-heroicon-o-arrow-left class="size-4 text-gold" />
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </section>

@endsection
