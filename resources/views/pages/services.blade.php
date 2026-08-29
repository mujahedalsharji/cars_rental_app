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
                'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/2/27/Abraj-ul-bait.jpg/900px-Abraj-ul-bait.jpg',
                'source' => 'https://commons.wikimedia.org/wiki/File:Abraj-ul-bait.jpg',
                'credit' => 'EZ',
                'license' => 'ملكية عامة',
            ],
            [
                'name' => 'جدة',
                'landmark' => 'نافورة الملك فهد',
                'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/7/78/King_Fahd%27s_Fountain.jpg/900px-King_Fahd%27s_Fountain.jpg',
                'source' => 'https://commons.wikimedia.org/wiki/File:King_Fahd%27s_Fountain.jpg',
                'credit' => 'Ammar shaker',
                'license' => 'ملكية عامة',
            ],
            [
                'name' => 'المدينة المنورة',
                'landmark' => 'ساحات المسجد النبوي',
                'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/7/7c/Medina_Haram_Piazza.jpg/900px-Medina_Haram_Piazza.jpg',
                'source' => 'https://commons.wikimedia.org/wiki/File:Medina_Haram_Piazza.jpg',
                'credit' => 'GLady',
                'license' => 'CC0',
            ],
            [
                'name' => 'الرياض',
                'landmark' => 'مركز الملك عبدالله المالي',
                'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/f/fe/%D9%85%D8%B1%D9%83%D8%B2_%D8%A7%D9%84%D9%85%D9%84%D9%83_%D8%B9%D8%A8%D8%AF%D8%A7%D9%84%D9%84%D9%87_%D8%A7%D9%84%D9%85%D8%A7%D9%84%D9%8A.JPG/900px-%D9%85%D8%B1%D9%83%D8%B2_%D8%A7%D9%84%D9%85%D9%84%D9%83_%D8%B9%D8%A8%D8%AF%D8%A7%D9%84%D9%84%D9%87_%D8%A7%D9%84%D9%85%D8%A7%D9%84%D9%8A.JPG',
                'source' => 'https://commons.wikimedia.org/wiki/File:%D9%85%D8%B1%D9%83%D8%B2_%D8%A7%D9%84%D9%85%D9%84%D9%83_%D8%B9%D8%A8%D8%AF%D8%A7%D9%84%D9%84%D9%87_%D8%A7%D9%84%D9%85%D8%A7%D9%84%D9%8A.JPG',
                'credit' => 'Halfalah',
                'license' => 'ملكية عامة',
            ],
            [
                'name' => 'الدمام',
                'landmark' => 'كورنيش الدمام',
                'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/7/73/Corniche_Dammam.jpg/900px-Corniche_Dammam.jpg',
                'source' => 'https://commons.wikimedia.org/wiki/File:Corniche_Dammam.jpg',
                'credit' => 'DonToofee',
                'license' => 'CC BY 2.0',
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
                    <article class="group flex min-h-64 flex-col rounded-2xl border border-white/10 bg-panel p-6 transition hover:border-gold/45 hover:bg-white/[0.045]">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex size-11 items-center justify-center rounded-xl bg-gold/10 text-gold">
                                <x-dynamic-component :component="$service['icon']" class="size-5" />
                            </div>
                            <span class="text-xs font-bold text-gold/75">{{ $service['eyebrow'] }}</span>
                        </div>

                        <h2 class="mt-5 text-xl font-extrabold text-white">{{ $service['card_title'] }}</h2>
                        <p class="mt-3 flex-1 text-sm leading-7 text-white/55">{{ $service['description'] }}</p>

                        @if($service['slug'])
                            <a href="{{ route('services.show', $service['slug']) }}"
                               class="mt-6 inline-flex items-center gap-2 text-sm font-bold text-gold-light">
                                تفاصيل الخدمة
                                <x-heroicon-o-arrow-left class="size-4 transition group-hover:-translate-x-1" />
                            </a>
                        @else
                            <a href="https://wa.me/{{ $waNumber }}?text={{ urlencode($service['whatsapp_message']) }}"
                               target="_blank" rel="noopener noreferrer"
                               class="mt-6 inline-flex items-center gap-2 text-sm font-bold text-gold-light">
                                اسأل عن الخدمة
                                <x-heroicon-o-chat-bubble-left-right class="size-4" />
                            </a>
                        @endif
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
                    <article class="group overflow-hidden rounded-2xl border border-white/10 bg-panel">
                        <div class="relative aspect-[4/3] overflow-hidden bg-ink-soft">
                            <img src="{{ $city['image'] }}"
                                 alt="{{ $city['landmark'] }} في {{ $city['name'] }}"
                                 width="900" height="675" loading="lazy" decoding="async"
                                 class="size-full object-cover opacity-70 grayscale-[25%] transition duration-500 group-hover:scale-105 group-hover:opacity-90 group-hover:grayscale-0">
                            <div class="absolute inset-0 bg-gradient-to-t from-panel via-transparent to-transparent"></div>
                        </div>
                        <div class="px-4 pb-4">
                            <h3 class="text-sm font-bold text-white">{{ $city['name'] }}</h3>
                            <p class="mt-1 text-xs text-white/40">{{ $city['landmark'] }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>

        <details class="mx-auto mt-5 max-w-7xl text-xs text-white/35">
            <summary class="w-fit cursor-pointer transition hover:text-white/55">مصادر الصور وتراخيصها</summary>
            <ul class="mt-3 flex flex-wrap gap-x-4 gap-y-2">
                @foreach($cities as $city)
                    <li>
                        <a href="{{ $city['source'] }}" target="_blank" rel="noopener noreferrer"
                           class="underline decoration-white/20 underline-offset-4 hover:text-gold-light">
                            {{ $city['name'] }} — {{ $city['credit'] }}، {{ $city['license'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </details>
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
