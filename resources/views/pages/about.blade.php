@extends('layouts.app')

@section('title', 'من نحن | فخامة مسافر')

@section('content')

    <x-page-hero eyebrow="قصتنا" title="فخامة تبدأ قبل الوصول"
        description="نرتب كل تفصيلة في رحلتك لتكون أكثر هدوءاً وراحة، من اختيار السيارة إلى لحظة الوصول." />

    {{-- Story --}}
    <section class="px-4 py-14 sm:px-6 sm:py-20 lg:px-8">
        <div class="mx-auto grid max-w-7xl items-center gap-10 lg:grid-cols-2">
            {{-- Image --}}
            <div class="relative">
                <img src="{{ asset('assets/images/hero-clean.png') }}" alt="أسطول فخامة مسافر"
                     class="aspect-[4/3] w-full rounded-3xl border border-white/10 object-cover">
                <div class="gold-surface absolute -bottom-5 start-5 rounded-2xl px-5 py-4 text-ink shadow-2xl sm:-bottom-6 sm:start-6">
                    <p class="text-xs font-bold opacity-60">وعدنا لك</p>
                    <p class="mt-1 text-base font-extrabold sm:text-lg">رحلة آمنة، راقية، ومنضبطة</p>
                </div>
            </div>
            {{-- Text --}}
            <div class="pt-8 lg:pt-0">
                <p class="text-xs font-bold uppercase tracking-widest text-gold">فخامة مسافر</p>
                <h2 class="mt-3 text-2xl font-extrabold leading-tight text-white sm:text-3xl">
                    خدمة نقل خاصة تهتم بالإنسان قبل الطريق
                </h2>
                <div class="mt-5 grid gap-4 text-sm leading-8 text-white/55 sm:text-base sm:leading-9">
                    <p>{{ $aboutText ?: 'نقدم خدمات النقل البري والسيارات مع سائق للزوار والعائلات ورجال الأعمال في المملكة العربية السعودية. نؤمن أن جودة الرحلة تبدأ بالالتزام، والسيارة النظيفة، والسائق المحترف.' }}</p>
                    <p>نعمل على مدار الساعة لنمنح ضيوفنا تجربة واضحة منذ الطلب الأول، مع متابعة مستمرة واهتمام كامل بالخصوصية والراحة.</p>
                </div>
                <a href="{{ route('booking') }}"
                   class="gold-surface mt-7 inline-flex items-center gap-2 rounded-xl px-6 py-3.5 text-sm font-bold text-ink transition hover:brightness-110">
                    احجز رحلتك
                    <x-heroicon-o-arrow-left class="size-5" />
                </a>
            </div>
        </div>
    </section>

    {{-- Values --}}
    <section class="border-y border-white/8 bg-white/[0.02] px-4 py-14 sm:px-6 sm:py-20 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <div class="text-center">
                <p class="text-xs font-bold uppercase tracking-widest text-gold">قيمنا</p>
                <h2 class="mt-3 text-2xl font-extrabold text-white sm:text-3xl">ما الذي يحركنا كل يوم؟</h2>
            </div>
            <div class="mt-10 grid gap-5 sm:grid-cols-3">
                @foreach([
                    ['icon' => 'heroicon-o-shield-check', 'title' => 'الأمان', 'text' => 'اختيار السائقين والمركبات بعناية ليبقى الأمان أساس كل رحلة.'],
                    ['icon' => 'heroicon-o-clock', 'title' => 'الانضباط', 'text' => 'نحترم وقتك وننسق تفاصيل الاستقبال والوصول بدقة.'],
                    ['icon' => 'heroicon-o-heart', 'title' => 'الضيافة', 'text' => 'نقدم تجربة راقية ودافئة تليق بضيوف المملكة.'],
                ] as $value)
                    <article class="rounded-3xl border border-white/10 bg-panel p-6 sm:p-7">
                        <div class="inline-flex size-13 items-center justify-center rounded-2xl border border-gold/30 text-gold">
                            <x-dynamic-component :component="$value['icon']" class="size-6" />
                        </div>
                        <h3 class="mt-5 text-lg font-bold text-white">{{ $value['title'] }}</h3>
                        <p class="mt-3 text-sm leading-7 text-white/50">{{ $value['text'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

@endsection
