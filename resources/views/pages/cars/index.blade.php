@extends('layouts.app')

@section('title', 'أسطول السيارات | فخامة مسافر')
@section('meta_description', 'تصفح أسطول فخامة مسافر من السيارات الفاخرة والعائلية واختر المركبة المناسبة لرحلتك.')

@section('content')
    <x-page-hero eyebrow="أسطولنا" title="سيارة تليق بكل وجهة" description="اختر من سياراتنا الحديثة، ودع فريقنا يرتب لك رحلة مريحة وآمنة من البداية إلى الوصول." />

    <section class="px-5 py-16 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <form method="GET" action="{{ route('cars.index') }}" class="grid gap-4 rounded-3xl border border-white/10 bg-panel p-5 md:grid-cols-[1fr_0.55fr_auto] md:items-end">
                <label class="grid gap-2 text-sm text-white/65">
                    ابحث عن سيارة
                    <span class="flex items-center gap-3 rounded-xl border border-white/10 bg-black/20 px-4 focus-within:border-gold/60">
                        <x-heroicon-o-magnifying-glass class="size-5 text-gold" />
                        <input type="search" name="search" value="{{ request('search') }}" placeholder="الاسم أو العلامة التجارية" class="min-h-12 w-full bg-transparent text-white outline-none placeholder:text-white/30">
                    </span>
                </label>
                <label class="grid gap-2 text-sm text-white/65">
                    الفئة
                    <select name="category" class="min-h-12 rounded-xl border border-white/10 bg-ink px-4 text-white outline-none focus:border-gold/60">
                        <option value="">جميع الفئات</option>
                        @foreach($categories as $category)<option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>{{ $category->name }}</option>@endforeach
                    </select>
                </label>
                <button class="gold-surface min-h-12 rounded-xl px-7 font-bold text-ink">عرض النتائج</button>
            </form>

            @if($cars->isNotEmpty())
                <div class="mt-10 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($cars as $car)<x-car-card :car="$car" :index="$loop->index" />@endforeach
                </div>
                <div class="mt-10">{{ $cars->withQueryString()->links() }}</div>
            @else
                <div class="mt-10 rounded-3xl border border-gold/20 bg-panel p-7 text-center sm:p-10">
                    @if(request()->hasAny(['search', 'category']))
                        <x-heroicon-o-magnifying-glass class="mx-auto size-12 text-gold" />
                        <h2 class="mt-5 text-2xl font-bold text-white">لم نجد سيارات مطابقة</h2>
                        <p class="mt-3 text-sm text-white/55">جرّب إزالة عوامل التصفية أو تواصل معنا لنقترح عليك الخيار الأنسب.</p>
                        <a href="{{ route('cars.index') }}" class="mt-6 inline-flex rounded-xl border border-gold/50 px-5 py-3 font-bold text-gold-light">مسح التصفية</a>
                    @else
                        <h2 class="text-2xl font-bold text-white">الفئات الأكثر طلباً</h2>
                        <p class="mt-3 text-sm text-white/55">ستظهر سيارات لوحة التحكم هنا تلقائياً. يمكنك الآن طلب إحدى الفئات المتاحة.</p>
                        <div class="mt-8 grid gap-6 md:grid-cols-3">
                            @foreach([
                                ['image' => 'fleet-bmw-7.png', 'name' => 'سيدان فاخرة'],
                                ['image' => 'fleet-gmc-yukon.png', 'name' => 'SUV فاخرة'],
                                ['image' => 'fleet-staria.png', 'name' => 'عائلية فاخرة'],
                            ] as $category)
                                <a href="{{ route('booking', ['car_name' => $category['name']]) }}" class="group overflow-hidden rounded-2xl border border-white/10 bg-black/20 text-start hover:border-gold/50">
                                    <img src="{{ asset('assets/images/'.$category['image']) }}" alt="{{ $category['name'] }}" class="aspect-[4/3] w-full object-cover transition group-hover:scale-[1.03]">
                                    <span class="flex items-center justify-between gap-3 p-5 font-bold text-white">{{ $category['name'] }} <x-heroicon-o-arrow-left class="size-4 text-gold" /></span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </section>
@endsection
