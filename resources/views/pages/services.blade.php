@extends('layouts.app')

@section('title', 'خدمات السيارات مع سائق في السعودية | فخامة مسافر')
@section('meta_description', 'خدمات سيارة مع سائق، توصيل مطار جدة إلى مكة، رحلات مكة والمدينة، وسائق خاص بالساعة في مدن المملكة العربية السعودية.')

@section('content')
    @php
        $waNumber = preg_replace('/\D+/', '', $whatsappNumber ?? '967777575308');
        $cities = [
            [
                'name' => 'مكة المكرمة',
                'landmark' => 'أبراج البيت',
            ],
            [
                'name' => 'جدة',
                'landmark' => 'نافورة الملك فهد',
            ],
            [
                'name' => 'المدينة المنورة',
                'landmark' => 'ساحات المسجد النبوي',
            ],
            [
                'name' => 'الرياض',
                'landmark' => 'مركز الملك عبدالله المالي',
            ],
            [
                'name' => 'الدمام',
                'landmark' => 'كورنيش الدمام',
            ],
        ];
    @endphp

    <x-page-hero
        eyebrow="خدمات فخامة مسافر"
        title="تنقل خاص يناسب رحلتك"
        description="اختر الخدمة المناسبة، اطلع على التفاصيل، ثم أرسل موعدك ومسارك عبر واتساب." />

    <section class="px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                @foreach($services as $service)
                    <article class="group relative isolate flex min-h-80 overflow-hidden rounded-2xl border border-white/15 bg-panel p-6 shadow-xl shadow-black/15 transition duration-500 hover:-translate-y-1 hover:border-gold/60 hover:shadow-gold/10">
                        <img src="{{ asset($service['image']) }}"
                             alt="" width="1536" height="1024" loading="lazy" decoding="async"
                             class="absolute inset-0 -z-20 size-full object-cover transition duration-700 group-hover:scale-105">
                        <div class="absolute inset-0 -z-10 bg-black/40"></div>
                        <div class="absolute inset-0 -z-10 bg-gradient-to-t from-black via-black/75 to-black/20"></div>

                        <div class="flex w-full flex-col">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex size-11 items-center justify-center rounded-xl bg-gold/15 text-gold ring-1 ring-gold/20 backdrop-blur-sm">
                                    <x-dynamic-component :component="$service['icon']" class="size-5" />
                                </div>
                                <span class="rounded-full bg-black/30 px-3 py-1.5 text-xs font-bold text-gold-light backdrop-blur-sm">{{ $service['eyebrow'] }}</span>
                            </div>

                            <div class="mt-auto pt-12">
                                <h2 class="text-2xl font-extrabold text-white drop-shadow-sm">{{ $service['card_title'] }}</h2>
                                <p class="mt-3 max-w-sm text-sm leading-7 text-white/75">{{ $service['description'] }}</p>

                                @if($service['slug'])
                                    <a href="{{ route('services.show', $service['slug']) }}"
                                       class="mt-5 inline-flex items-center gap-2 rounded-xl border border-white/20 bg-black/25 px-4 py-2.5 text-sm font-bold text-gold-light backdrop-blur-sm transition hover:border-gold/50 hover:bg-black/45">
                                        تفاصيل الخدمة
                                        <x-heroicon-o-arrow-left class="size-4 transition group-hover:-translate-x-1" />
                                    </a>
                                @else
                                    <a href="https://wa.me/{{ $waNumber }}?text={{ urlencode($service['whatsapp_message']) }}"
                                       target="_blank" rel="noopener noreferrer"
                                       class="mt-5 inline-flex items-center gap-2 rounded-xl border border-white/20 bg-black/25 px-4 py-2.5 text-sm font-bold text-gold-light backdrop-blur-sm transition hover:border-gold/50 hover:bg-black/45">
                                        اسأل عن الخدمة
                                        <x-heroicon-o-chat-bubble-left-right class="size-4" />
                                    </a>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="border-y border-white/8 bg-white/[0.02] px-4 py-12 sm:px-6 lg:px-8">
        <div class="mx-auto grid max-w-7xl gap-8 lg:grid-cols-[0.8fr_1.2fr] lg:items-center">
            <div>
                <p class="text-xs font-bold tracking-[0.18em] text-gold">نطاق الخدمة</p>
                <h2 class="mt-3 text-2xl font-extrabold text-white sm:text-3xl">المدن الرئيسية</h2>
                <p class="mt-3 max-w-md text-sm leading-7 text-white/55">
                    نخدم معظم مدن المملكة، مع تركيز أكبر على هذه الوجهات. المدن الأخرى متاحة حسب موعد الرحلة وتوفر السيارة.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                @foreach($cities as $city)
                    <article class="rounded-2xl border border-white/10 bg-panel px-4 py-5 transition hover:border-gold/35">
                        <h3 class="text-sm font-bold text-white">{{ $city['name'] }}</h3>
                        <p class="mt-1 text-xs text-white/40">{{ $city['landmark'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
        <div class="mx-auto grid max-w-7xl gap-4 sm:grid-cols-3">
            @foreach([
                ['number' => '01', 'title' => 'أرسل التفاصيل', 'text' => 'المدينة والموعد وعدد الركاب والوجهة.'],
                ['number' => '02', 'title' => 'اختر السيارة', 'text' => 'نقترح السيارة المناسبة ونؤكد تفاصيل الحجز.'],
                ['number' => '03', 'title' => 'ابدأ الرحلة', 'text' => 'يصلك السائق في المكان والموعد المتفق عليهما.'],
            ] as $step)
                <div class="rounded-2xl border border-white/10 p-5">
                    <span class="text-xs font-extrabold text-gold">{{ $step['number'] }}</span>
                    <h2 class="mt-3 font-bold text-white">{{ $step['title'] }}</h2>
                    <p class="mt-2 text-sm leading-6 text-white/50">{{ $step['text'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section class="px-4 pb-16 sm:px-6 lg:px-8">
        <div class="gold-surface mx-auto flex max-w-7xl flex-col justify-between gap-5 rounded-2xl px-6 py-8 text-ink sm:flex-row sm:items-center lg:px-9">
            <div>
                <h2 class="text-xl font-extrabold sm:text-2xl">لست متأكداً من الخدمة المناسبة؟</h2>
                <p class="mt-2 text-sm opacity-75">أرسل تفاصيل رحلتك وسنساعدك في اختيار السيارة والترتيب المناسب.</p>
            </div>
            <a href="https://wa.me/{{ $waNumber }}?text={{ urlencode('أرغب في الاستفسار عن خدمات فخامة مسافر') }}"
               target="_blank" rel="noopener noreferrer"
               class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-ink px-6 py-3.5 text-sm font-bold text-white">
                تواصل عبر واتساب
                <x-heroicon-o-chat-bubble-left-right class="size-5" />
            </a>
        </div>
    </section>
@endsection
