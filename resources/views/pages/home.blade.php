@extends('layouts.app')

@section('title', 'فخامة مسافر | رحلة فاخرة تليق بضيوف المملكة')
@section('meta_description', 'سيارات حديثة مع سائقين محترفين لخدمة المطارات والتنقل بين مدن المملكة على مدار الساعة.')

@section('content')
    <section class="relative isolate min-h-[820px] overflow-hidden pt-28 lg:min-h-[920px]">
        <img src="{{ asset('assets/images/hero-clean.png') }}" alt="أسطول فخامة مسافر أمام معالم مكة المكرمة والمدينة المنورة" class="absolute inset-0 -z-30 size-full object-cover object-center">
        <div class="hero-vignette absolute inset-0 -z-20"></div>
        <div class="absolute inset-0 -z-10 bg-gradient-to-r from-ink/35 via-transparent to-ink/25"></div>

        <div class="mx-auto flex min-h-[690px] max-w-7xl flex-col items-center justify-between px-5 pb-28 pt-16 text-center sm:pb-8 lg:px-8" data-reveal>
            <div class="max-w-4xl">
                <p class="mx-auto inline-flex items-center gap-3 rounded-full border border-gold/35 bg-black/30 px-4 py-2 text-xs font-bold text-gold-light backdrop-blur-md sm:text-sm">
                    <x-heroicon-o-sparkles class="size-4" />
                    خدمة سيارات خاصة على مدار الساعة
                </p>
                <h1 class="mt-7 text-5xl font-extrabold leading-[1.18] text-white drop-shadow-2xl sm:text-6xl lg:text-7xl">
                    رحلة فاخرة<br><span class="gold-text">تليق بضيوف المملكة</span>
                </h1>
                <p class="mx-auto mt-6 max-w-2xl text-lg font-medium leading-9 text-white/80 sm:text-xl">خدمة سائق خاص بأسطول فاخر للمطارات، والتنقل بين المدن، والمناسبات الخاصة.</p>
            </div>
            <div class="flex flex-col justify-center gap-4 sm:flex-row">
                <a href="{{ route('cars.index') }}" class="gold-surface inline-flex min-h-14 items-center justify-center gap-3 rounded-2xl px-8 font-bold text-ink shadow-xl shadow-black/25 transition hover:-translate-y-0.5 hover:brightness-110">
                    استعرض السيارات
                    <x-heroicon-o-arrow-left class="size-5" />
                </a>
                <a href="{{ route('booking') }}" class="hidden min-h-14 items-center justify-center gap-3 rounded-2xl border border-gold/60 bg-black/30 px-8 font-bold text-white backdrop-blur-md transition hover:bg-gold hover:text-ink sm:inline-flex">
                    احجز رحلتك
                    <x-heroicon-o-calendar-days class="size-5" />
                </a>
            </div>
        </div>
    </section>

    <section class="relative z-10 -mt-24 px-5 lg:px-8">
        <div class="panel-glow mx-auto max-w-7xl rounded-3xl border border-gold/35 bg-panel/95 p-5 backdrop-blur-xl sm:p-7">
            <div class="flex items-center gap-3">
                <span class="h-px w-7 bg-gold"></span>
                <h2 class="font-bold text-white">احجز رحلتك الآن</h2>
            </div>
            <form id="hero-quote-form"
                  data-home-quote-form
                  data-whatsapp-number="{{ $whatsappNumber }}"
                  class="mt-5 grid gap-3 md:grid-cols-2 lg:grid-cols-[1fr_1fr_0.8fr_0.8fr_auto]">
                <label class="grid gap-2 rounded-2xl border border-white/10 bg-black/20 p-4 text-xs text-white/50">
                    <span class="inline-flex items-center gap-2"><x-heroicon-o-map-pin class="size-4 text-gold" /> من</span>
                    <input id="hqf-pickup" name="pickup" type="text" placeholder="موقع الانطلاق" class="min-w-0 bg-transparent text-sm text-white outline-none placeholder:text-white/35">
                </label>
                <label class="grid gap-2 rounded-2xl border border-white/10 bg-black/20 p-4 text-xs text-white/50">
                    <span class="inline-flex items-center gap-2"><x-heroicon-o-map-pin class="size-4 text-gold" /> إلى</span>
                    <input id="hqf-destination" name="destination" type="text" placeholder="اختر الوجهة" class="min-w-0 bg-transparent text-sm text-white outline-none placeholder:text-white/35">
                </label>
                <label class="grid gap-2 rounded-2xl border border-white/10 bg-black/20 p-4 text-xs text-white/50">
                    <span class="inline-flex items-center gap-2"><x-heroicon-o-calendar-days class="size-4 text-gold" /> التاريخ</span>
                    <input id="hqf-date" name="date" type="date" min="{{ now()->toDateString() }}" class="min-w-0 bg-transparent text-sm text-white outline-none scheme-dark">
                </label>
                <label class="grid gap-2 rounded-2xl border border-white/10 bg-black/20 p-4 text-xs text-white/50">
                    <span class="inline-flex items-center gap-2"><x-heroicon-o-tag class="size-4 text-gold" /> نوع الخدمة</span>
                    <select id="hqf-service" name="service" class="min-w-0 bg-panel text-sm text-white outline-none">
                        <option value="الحجز من أو إلى المطار">✈️ الحجز من أو إلى المطار</option>
                        <option value="رحلات سياحية">🗺️ رحلات سياحية</option>
                        <option value="رحلات بين المدن">🛣️ رحلات بين المدن</option>
                        <option value="سائق بالساعة">🕐 سائق بالساعة</option>
                        <option value="خدمة المناسبات">🎉 خدمة المناسبات</option>
                        <option value="خدمة رجال الأعمال">💼 خدمة رجال الأعمال</option>
                        <option value="رحلات عائلية">👨‍👩‍👧 رحلات عائلية</option>
                    </select>
                </label>
                <button type="submit" class="gold-surface min-h-16 rounded-2xl px-7 font-bold text-ink transition hover:brightness-110">طلب عرض</button>
            </form>

        </div>
    </section>

    <section class="overflow-hidden px-5 pb-4 pt-16 lg:px-8" aria-labelledby="popular-services-title">
        <div class="mx-auto max-w-7xl">
            <div class="flex flex-col items-start justify-between gap-5 sm:flex-row sm:items-end">
                <div>
                    <p class="text-sm font-bold text-gold">الخدمات الأكثر طلباً</p>
                    <h2 id="popular-services-title" class="mt-3 text-3xl font-extrabold text-white sm:text-4xl">اختر الخدمة المناسبة لرحلتك</h2>
                    <p class="mt-4 max-w-2xl text-sm leading-7 text-white/55">من استقبال المطار إلى الرحلات بين المدن، نرتب تنقلك كما يناسب وقتك ووجهتك.</p>
                </div>
                <a href="{{ route('services') }}" class="hidden items-center gap-2 text-sm font-bold text-gold-light transition hover:text-white sm:inline-flex">
                    جميع الخدمات
                    <x-heroicon-o-arrow-left class="size-4" />
                </a>
            </div>

            <div class="mt-10" data-service-carousel data-autoplay-ms="10000" aria-roledescription="carousel" aria-label="الخدمات الأكثر طلباً">
                <div data-service-carousel-viewport
                     class="relative h-[440px] touch-pan-y overflow-hidden sm:h-[460px]"
                     aria-live="off">
                    @foreach($popularServices as $service)
                        <article data-service-carousel-card
                                 data-carousel-position="{{ $loop->first ? '0' : ($loop->index === 1 ? '1' : ($loop->last ? '-1' : 'far')) }}"
                                 aria-roledescription="slide"
                                 aria-label="{{ $loop->iteration }} من {{ $loop->count }}"
                                 aria-hidden="{{ $loop->first ? 'false' : 'true' }}"
                                 @if(! $loop->first) inert @endif
                                 class="service-carousel-card group absolute inset-y-0 left-1/2 w-[82%] max-w-[620px] overflow-hidden rounded-[1.75rem] border border-white/15 bg-panel sm:w-[72%] lg:w-[58%]">
                            <img src="{{ asset($service['image']) }}"
                                 alt=""
                                 width="1536"
                                 height="1024"
                                 loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                                 decoding="async"
                                 class="absolute inset-0 size-full object-cover transition duration-700 group-hover:scale-105">

                            <div class="absolute inset-x-0 top-0 flex items-start justify-between gap-4 p-5 sm:p-6">
                                <span class="inline-flex items-center rounded-full border border-gold/30 bg-black/55 px-4 py-2 text-xs font-bold text-gold-light backdrop-blur-md">
                                    {{ $service['eyebrow'] }}
                                </span>
                                <span class="inline-flex size-12 items-center justify-center rounded-2xl border border-gold/25 bg-gold/15 text-gold backdrop-blur-md">
                                    <x-dynamic-component :component="$service['icon']" class="size-6" />
                                </span>
                            </div>

                            <div class="absolute inset-x-0 bottom-0 flex flex-col items-start p-6 sm:p-7">
                                <h3 class="text-2xl font-extrabold leading-tight text-white drop-shadow-lg sm:text-3xl">{{ $service['card_title'] }}</h3>
                                <p class="mt-3 line-clamp-3 text-sm font-medium leading-7 text-white/85 drop-shadow-md">{{ $service['description'] }}</p>
                                <a href="{{ $service['slug'] ? route('services.show', $service['slug']) : route('services') }}"
                                   class="mt-5 inline-flex min-h-12 items-center gap-3 rounded-xl border border-gold/40 bg-black/50 px-5 py-3 text-sm font-bold text-gold-light backdrop-blur-md transition hover:border-gold hover:bg-gold hover:text-ink">
                                    تفاصيل الخدمة
                                    <x-heroicon-o-arrow-left class="size-4" />
                                </a>
                            </div>
                        </article>
                    @endforeach

                    <div class="pointer-events-none absolute inset-y-0 left-1/2 z-40 w-[82%] max-w-[620px] -translate-x-1/2 sm:w-[72%] lg:w-[58%]">
                        <button type="button" data-service-carousel-next aria-label="الخدمة التالية"
                                class="pointer-events-auto absolute start-3 top-1/2 inline-flex size-11 -translate-y-1/2 items-center justify-center rounded-full border border-gold/35 bg-black/65 text-white shadow-xl backdrop-blur-md transition hover:border-gold hover:bg-gold hover:text-ink sm:start-4">
                            <x-heroicon-o-chevron-right class="size-5" />
                        </button>
                        <button type="button" data-service-carousel-previous aria-label="الخدمة السابقة"
                                class="pointer-events-auto absolute end-3 top-1/2 inline-flex size-11 -translate-y-1/2 items-center justify-center rounded-full border border-gold/35 bg-black/65 text-white shadow-xl backdrop-blur-md transition hover:border-gold hover:bg-gold hover:text-ink sm:end-4">
                            <x-heroicon-o-chevron-left class="size-5" />
                        </button>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-center gap-2" aria-label="اختيار الخدمة">
                    @foreach($popularServices as $service)
                        <button type="button"
                                data-service-carousel-indicator="{{ $loop->index }}"
                                aria-label="عرض خدمة {{ $service['card_title'] }}"
                                @if($loop->first) aria-current="true" @endif
                                class="h-2.5 rounded-full transition-all {{ $loop->first ? 'w-8 bg-gold' : 'w-2.5 bg-white/25 hover:bg-white/50' }}">
                        </button>
                    @endforeach
                </div>

                <a href="{{ route('services') }}" class="mt-6 inline-flex items-center gap-2 text-sm font-bold text-gold-light transition hover:text-white sm:hidden">
                    استعرض جميع الخدمات
                    <x-heroicon-o-arrow-left class="size-4" />
                </a>
            </div>
        </div>
    </section>

    <section class="overflow-hidden px-5 py-20 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <div class="flex flex-col items-start justify-between gap-5 sm:flex-row sm:items-end">
                <div>
                    <p class="text-sm font-bold text-gold">أسطولنا المختار</p>
                    <h2 class="mt-3 text-3xl font-extrabold text-white sm:text-4xl">أسطول فاخر يليق بتوقعاتك</h2>
                    <p class="mt-4 text-sm leading-7 text-white/55">سيارات مميزة للعائلات، ورجال الأعمال، والرحلات الخاصة.</p>
                </div>
                <a href="{{ route('cars.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-gold/50 px-5 py-3 text-sm font-bold text-gold-light hover:bg-gold hover:text-ink">عرض جميع السيارات <x-heroicon-o-arrow-left class="size-4" /></a>
            </div>

            @if($featuredCars->isNotEmpty())
                <div class="mt-10 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($featuredCars->take(3) as $car)<x-car-card :car="$car" :index="$loop->index" />@endforeach
                </div>
            @else
                <div class="mt-10 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach([
                        ['image' => 'fleet-bmw-7.png', 'name' => 'BMW الفئة السابعة', 'type' => 'سيدان فاخرة', 'capacity' => '3 ركاب', 'luggage' => '3 حقائب'],
                        ['image' => 'fleet-gmc-yukon.png', 'name' => 'GMC يوكن', 'type' => 'SUV فاخرة', 'capacity' => '6 ركاب', 'luggage' => '4 حقائب'],
                        ['image' => 'fleet-staria.png', 'name' => 'هيونداي ستاريا', 'type' => 'عائلية فاخرة', 'capacity' => '7 ركاب', 'luggage' => '5 حقائب'],
                    ] as $fallbackCar)
                        <article class="group overflow-hidden rounded-3xl border border-white/10 bg-panel transition hover:-translate-y-1 hover:border-gold/45">
                            <img src="{{ asset('assets/images/'.$fallbackCar['image']) }}" alt="{{ $fallbackCar['name'] }}" class="aspect-[4/3] w-full object-cover transition duration-500 group-hover:scale-[1.03]">
                            <div class="p-6">
                                <p class="text-xs font-semibold text-gold">{{ $fallbackCar['type'] }}</p>
                                <h3 class="mt-2 text-xl font-bold text-white">{{ $fallbackCar['name'] }}</h3>
                                <div class="mt-5 flex gap-5 border-t border-white/8 pt-4 text-sm text-white/55">
                                    <span class="inline-flex items-center gap-2"><x-heroicon-o-user-group class="size-4 text-gold" />{{ $fallbackCar['capacity'] }}</span>
                                    <span class="inline-flex items-center gap-2"><x-heroicon-o-briefcase class="size-4 text-gold" />{{ $fallbackCar['luggage'] }}</span>
                                </div>
                                <a href="{{ route('booking') }}" class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-xl border border-white/10 px-4 py-3 text-sm font-bold text-gold-light hover:border-gold hover:bg-gold hover:text-ink">اطلب هذه الفئة <x-heroicon-o-arrow-left class="size-4" /></a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <section class="px-5 pb-24 lg:px-8">
        <div class="mx-auto max-w-7xl rounded-[2rem] border border-gold/20 bg-panel p-7 sm:p-10 lg:p-14">
            <div class="text-center">
                <p class="text-sm font-bold text-gold">رحلة بلا مساومة</p>
                <h2 class="mt-3 text-3xl font-extrabold text-white sm:text-4xl">لماذا تختار فخامة مسافر؟</h2>
            </div>
            <div class="mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-5">
                @foreach([
                    ['icon' => 'heroicon-o-shield-check', 'title' => 'أمان وثقة', 'text' => 'سائقون محترفون ومركبات موثوقة بالكامل'],
                    ['icon' => 'heroicon-o-clock', 'title' => 'خدمة 24 ساعة', 'text' => 'متاحون لخدمتكم في أي وقت ومن أي مكان'],
                    ['icon' => 'heroicon-o-sparkles', 'title' => 'أسطول فاخر', 'text' => 'سيارات حديثة تناسب مختلف احتياجاتك'],
                    ['icon' => 'heroicon-o-map-pin', 'title' => 'تغطية واسعة', 'text' => 'نخدم المدن والوجهات الرئيسية في المملكة'],
                    ['icon' => 'heroicon-o-chat-bubble-left-right', 'title' => 'دعم مميز', 'text' => 'استجابة سريعة قبل الرحلة وأثناءها'],
                ] as $feature)
                    <div class="text-center">
                        <div class="mx-auto inline-flex size-14 items-center justify-center rounded-2xl border border-gold/35 text-gold"><x-dynamic-component :component="$feature['icon']" class="size-7" /></div>
                        <h3 class="mt-5 font-bold text-gold-light">{{ $feature['title'] }}</h3>
                        <p class="mt-3 text-sm leading-7 text-white/55">{{ $feature['text'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="border-y border-white/8 bg-white/[0.025] px-5 py-20 lg:px-8">
        <div class="mx-auto grid max-w-7xl gap-12 lg:grid-cols-[0.8fr_1.2fr] lg:items-start">
            <div>
                <p class="text-sm font-bold text-gold">قبل أن تبدأ رحلتك</p>
                <h2 class="mt-3 text-3xl font-extrabold text-white sm:text-4xl">أسئلة يطرحها عملاؤنا</h2>
                <p class="mt-5 max-w-md text-sm leading-8 text-white/55">إجابات واضحة تساعدك على اختيار السيارة والخدمة المناسبة بثقة.</p>
                <a href="{{ route('faq.index') }}" class="mt-7 inline-flex items-center gap-2 text-sm font-bold text-gold-light hover:text-white">كل الأسئلة الشائعة <x-heroicon-o-arrow-left class="size-4" /></a>
            </div>
            <div class="grid gap-3">
                @forelse($faqs as $faq)
                    <details class="group rounded-2xl border border-white/10 bg-panel px-5 py-4 open:border-gold/35">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-bold text-white"><span>{{ $faq->question }}</span><x-heroicon-o-plus class="size-5 shrink-0 text-gold group-open:rotate-45" /></summary>
                        <p class="mt-4 border-t border-white/8 pt-4 text-sm leading-8 text-white/60">{{ $faq->answer }}</p>
                    </details>
                @empty
                    <details open class="rounded-2xl border border-gold/35 bg-panel px-5 py-4"><summary class="font-bold text-white">كيف أبدأ الحجز؟</summary><p class="mt-4 border-t border-white/8 pt-4 text-sm leading-8 text-white/60">اختر السيارة أو فئة الرحلة، ثم أرسل التفاصيل عبر نموذج الحجز ليتواصل معك فريقنا.</p></details>
                @endforelse
            </div>
        </div>
    </section>

    <section class="px-5 py-20 lg:px-8">
        <div class="gold-surface mx-auto flex max-w-7xl flex-col items-center justify-between gap-6 rounded-[2rem] px-7 py-10 text-center text-ink md:flex-row md:text-start lg:px-12">
            <div><p class="text-sm font-bold opacity-70">جاهز لرحلتك القادمة؟</p><h2 class="mt-2 text-2xl font-extrabold sm:text-3xl">دع الفخامة ترافقك إلى وجهتك</h2></div>
            <a href="{{ route('booking') }}" class="inline-flex shrink-0 items-center gap-2 rounded-xl bg-ink px-6 py-4 font-bold text-white transition hover:-translate-y-0.5">ابدأ الحجز الآن <x-heroicon-o-arrow-left class="size-5" /></a>
        </div>
    </section>
@endsection
