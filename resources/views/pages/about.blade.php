@extends('layouts.app')

@section('title', 'من نحن | فخامة مسافر')
@section('content')
    <x-page-hero eyebrow="قصتنا" title="فخامة تبدأ قبل الوصول" description="نرتب كل تفصيلة في رحلتك لتكون أكثر هدوءاً وراحة، من اختيار السيارة إلى لحظة الوصول." />

    <section class="px-5 py-20 lg:px-8">
        <div class="mx-auto grid max-w-7xl gap-12 lg:grid-cols-2 lg:items-center">
            <div class="relative">
                <img src="{{ asset('assets/images/hero-clean.png') }}" alt="أسطول فخامة مسافر" class="aspect-[4/3] w-full rounded-[2rem] border border-white/10 object-cover">
                <div class="gold-surface absolute -bottom-6 start-6 rounded-2xl px-6 py-5 text-ink shadow-2xl"><p class="text-xs font-bold opacity-65">وعدنا لك</p><p class="mt-1 text-xl font-extrabold">رحلة آمنة، راقية، ومنضبطة</p></div>
            </div>
            <div class="pt-8 lg:pt-0">
                <p class="text-sm font-bold text-gold">فخامة مسافر</p>
                <h2 class="mt-3 text-3xl font-extrabold leading-tight text-white sm:text-4xl">خدمة نقل خاصة تهتم بالإنسان قبل الطريق</h2>
                <div class="mt-6 grid gap-5 text-base leading-9 text-white/60">
                    <p>{{ $aboutText ?: 'نقدم خدمات النقل البري والسيارات مع سائق للزوار والعائلات ورجال الأعمال في المملكة العربية السعودية. نؤمن أن جودة الرحلة تبدأ بالالتزام، والسيارة النظيفة، والسائق المحترف.' }}</p>
                    <p>نعمل على مدار الساعة لنمنح ضيوفنا تجربة واضحة منذ الطلب الأول، مع متابعة مستمرة واهتمام كامل بالخصوصية والراحة.</p>
                </div>
                <a href="{{ route('booking') }}" class="gold-surface mt-8 inline-flex items-center gap-2 rounded-xl px-6 py-4 font-bold text-ink">احجز رحلتك <x-heroicon-o-arrow-left class="size-5" /></a>
            </div>
        </div>
    </section>

    <section class="border-y border-white/8 bg-white/[0.025] px-5 py-20 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <div class="text-center"><p class="text-sm font-bold text-gold">قيمنا</p><h2 class="mt-3 text-3xl font-extrabold text-white">ما الذي يحركنا كل يوم؟</h2></div>
            <div class="mt-10 grid gap-6 md:grid-cols-3">
                @foreach([
                    ['icon' => 'heroicon-o-shield-check', 'title' => 'الأمان', 'text' => 'اختيار السائقين والمركبات بعناية ليبقى الأمان أساس كل رحلة.'],
                    ['icon' => 'heroicon-o-clock', 'title' => 'الانضباط', 'text' => 'نحترم وقتك وننسق تفاصيل الاستقبال والوصول بدقة.'],
                    ['icon' => 'heroicon-o-heart', 'title' => 'الضيافة', 'text' => 'نقدم تجربة راقية ودافئة تليق بضيوف المملكة.'],
                ] as $value)
                    <article class="rounded-3xl border border-white/10 bg-panel p-7"><div class="inline-flex size-14 items-center justify-center rounded-2xl border border-gold/35 text-gold"><x-dynamic-component :component="$value['icon']" class="size-7" /></div><h3 class="mt-6 text-xl font-bold text-white">{{ $value['title'] }}</h3><p class="mt-3 text-sm leading-8 text-white/55">{{ $value['text'] }}</p></article>
                @endforeach
            </div>
        </div>
    </section>
@endsection
