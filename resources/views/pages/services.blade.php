@extends('layouts.app')

@section('title', 'خدماتنا | فخامة مسافر')
@section('meta_description', 'خدمات نقل فاخرة تشمل التوصيل من وإلى المطار، الرحلات السياحية، التنقل بين المدن، وسائق خاص بالساعة في المملكة العربية السعودية.')

@section('content')

    {{-- ═══ CINEMATIC HERO ══════════════════════════════════════════════════ --}}
    <section class="relative isolate min-h-[560px] overflow-hidden pt-28 lg:min-h-[640px]">
        <img src="{{ asset('assets/images/hero-clean.png') }}"
             alt="خدمات فخامة مسافر — فخامة ترافقك في كل طريق"
             class="absolute inset-0 -z-30 size-full object-cover object-center">
        <div class="absolute inset-0 -z-20 bg-gradient-to-b from-ink/70 via-ink/55 to-ink"></div>
        <div class="absolute inset-0 -z-10 bg-gradient-to-r from-ink/40 via-transparent to-ink/40"></div>

        <div class="relative mx-auto flex min-h-[520px] max-w-7xl flex-col items-center justify-center px-5 pb-14 pt-16 text-center lg:px-8" data-reveal>
            <span class="inline-flex items-center gap-2 rounded-full border border-gold/35 bg-black/35 px-4 py-2 text-xs font-bold text-gold-light backdrop-blur-md">
                <x-heroicon-o-sparkles class="size-4" />
                خدمات مخصصة لكل رحلة
            </span>
            <h1 class="mt-7 text-5xl font-extrabold leading-[1.18] text-white drop-shadow-2xl sm:text-6xl lg:text-7xl">
                فخامة ترافقك<br><span class="gold-text">في كل طريق</span>
            </h1>
            <p class="mx-auto mt-6 max-w-2xl text-lg font-medium leading-9 text-white/75 sm:text-xl">
                من المطار إلى قلب المدينة، ومن الرحلات السياحية إلى خدمة رجال الأعمال — نحن حاضرون في كل محطة.
            </p>
            <a href="#services-grid"
               class="gold-surface mt-9 inline-flex items-center gap-2 rounded-2xl px-8 py-4 font-bold text-ink shadow-xl shadow-gold/15 transition hover:-translate-y-0.5 hover:brightness-110">
                استعرض خدماتنا
                <x-heroicon-o-arrow-down class="size-5" />
            </a>
        </div>
    </section>

    {{-- ═══ SERVICES GRID ════════════════════════════════════════════════════ --}}
    @php
        $waNumber = $whatsappNumber ?? '966500000000';
        $waBase   = "https://wa.me/{$waNumber}?text=";

        $services = [
            [
                'id'       => 'airport',
                'icon'     => 'heroicon-o-paper-airplane',
                'eyebrow'  => 'خدمة المطار',
                'title'    => 'الحجز من أو إلى المطار',
                'desc'     => 'استقبال فاخر في المطار مع لافتة خاصة، ومتابعة رحلتك حتى الهبوط. سائق محترف ينتظرك عند البوابة ليوصلك بأمان وراحة.',
                'features' => ['استقبال بلافتة خاصة', 'مراقبة وقت الهبوط', 'أولوية للرحلات المتأخرة', 'متاح 24/7'],
                'msg'      => 'طلب خدمة: الحجز من أو إلى المطار',
                'featured' => true,
                'badge'    => 'الأكثر طلباً',
            ],
            [
                'id'       => 'tourism',
                'icon'     => 'heroicon-o-map',
                'eyebrow'  => 'استكشاف وسياحة',
                'title'    => 'الرحلات السياحية',
                'desc'     => 'جولات سياحية مُصممة بعناية لاستكشاف معالم المملكة العربية السعودية، مع سائق مُرشد يعرف كل وجهة ويحرص على راحتك.',
                'features' => ['تخطيط مسار مخصص', 'سائق مُرشد محلي', 'مرونة في التوقيت', 'مناسب للعائلات'],
                'msg'      => 'طلب خدمة: رحلة سياحية',
                'featured' => false,
                'badge'    => null,
            ],
            [
                'id'       => 'intercity',
                'icon'     => 'heroicon-o-truck',
                'eyebrow'  => 'تنقل بين المدن',
                'title'    => 'رحلات بين المدن',
                'desc'     => 'سفر مريح بين مدن المملكة بسيارات حديثة. رحلة مباشرة دون توقف، وسائق محترف يقودك بأمان على الطرق البينية.',
                'features' => ['رحلة مباشرة غير مجزأة', 'سيارات مريحة للمسافات الطويلة', 'مرونة في نقطة الانطلاق', 'أسعار تنافسية'],
                'msg'      => 'طلب خدمة: رحلة بين المدن',
                'featured' => false,
                'badge'    => null,
            ],
            [
                'id'       => 'hourly',
                'icon'     => 'heroicon-o-clock',
                'eyebrow'  => 'مرونة تامة',
                'title'    => 'سائق خاص بالساعة',
                'desc'     => 'سائق خاص تحت تصرفك لساعات محددة للتسوق، الاجتماعات، والمواعيد المتعددة. احجز وقتك واستمتع بحرية التنقل.',
                'features' => ['تصرفك الكامل في المواعيد', 'مناسب للاجتماعات المتعاقبة', 'لا قيود على الوجهات', 'أدنى حجز ساعتان'],
                'msg'      => 'طلب خدمة: سائق خاص بالساعة',
                'featured' => false,
                'badge'    => null,
            ],
            [
                'id'       => 'events',
                'icon'     => 'heroicon-o-star',
                'eyebrow'  => 'مناسبات خاصة',
                'title'    => 'خدمة المناسبات',
                'desc'     => 'أفراح، مؤتمرات، حفلات تخرج — نجعل وصولك حدثاً في حد ذاته. أسطول مزين وسائقون بزي رسمي لمناسباتك الكبرى.',
                'features' => ['سيارات فاخرة مزينة', 'سائقون بزي رسمي', 'تنسيق مسبق مع المناسبة', 'أسطول متعدد للمجموعات'],
                'msg'      => 'طلب خدمة: خدمة المناسبات',
                'featured' => false,
                'badge'    => 'VIP',
            ],
            [
                'id'       => 'business',
                'icon'     => 'heroicon-o-briefcase',
                'eyebrow'  => 'بيئة الأعمال',
                'title'    => 'خدمة رجال الأعمال',
                'desc'     => 'خدمة نقل احترافية لرجال الأعمال والوفود التجارية. خصوصية تامة، سيارات مجهزة، ودقة في المواعيد تليق بمستوى صفقاتك.',
                'features' => ['خصوصية وسرية تامة', 'واي فاي مجاني على المتن', 'دقة صارمة في المواعيد', 'إيصالات للشركات'],
                'msg'      => 'طلب خدمة: خدمة رجال الأعمال',
                'featured' => false,
                'badge'    => 'Business',
            ],
            [
                'id'       => 'family',
                'icon'     => 'heroicon-o-home',
                'eyebrow'  => 'عائلة وترفيه',
                'title'    => 'رحلات عائلية',
                'desc'     => 'رحلات مريحة تناسب العائلات الكبيرة مع سيارات واسعة وسائق صبور ومُدرّب على التعامل مع كل الأعمار.',
                'features' => ['سيارات واسعة متعددة المقاعد', 'سائقون مدربون', 'خيارات لتركيب مقاعد أطفال', 'مناسب للزيارات والمشاوير'],
                'msg'      => 'طلب خدمة: رحلات عائلية',
                'featured' => false,
                'badge'    => null,
            ],
        ];
    @endphp

    <section id="services-grid" class="px-5 py-20 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <div class="text-center" data-reveal>
                <p class="text-sm font-bold uppercase tracking-widest text-gold">خدماتنا</p>
                <h2 class="mx-auto mt-4 max-w-2xl text-3xl font-extrabold text-white sm:text-4xl">
                    كل رحلة تستحق أفضل تجربة
                </h2>
                <p class="mx-auto mt-4 max-w-xl text-sm leading-7 text-white/55">
                    اختر الخدمة التي تناسبك وتواصل معنا مباشرةً عبر واتساب للحصول على عرض سعر فوري.
                </p>
            </div>

            <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($services as $s)
                    <article id="{{ $s['id'] }}"
                             class="group relative flex flex-col overflow-hidden rounded-3xl border bg-panel transition duration-300 hover:-translate-y-1 {{ $s['featured'] ? 'border-gold/60 shadow-lg shadow-gold/10' : 'border-white/10 hover:border-gold/40' }}">

                        {{-- Gold top accent bar --}}
                        <div class="h-1 w-full {{ $s['featured'] ? 'gold-surface' : 'bg-white/10 group-hover:bg-gold/40' }} transition-colors duration-300"></div>

                        @if($s['badge'])
                            <span class="absolute end-5 top-5 rounded-full border border-gold/50 bg-gold/15 px-3 py-1 text-xs font-bold text-gold-light backdrop-blur-sm">
                                {{ $s['badge'] }}
                            </span>
                        @endif

                        <div class="flex flex-1 flex-col p-7">
                            <div class="inline-flex size-14 items-center justify-center rounded-2xl border border-white/10 bg-white/[0.04] text-gold transition duration-300 group-hover:border-gold/40 group-hover:bg-gold/10">
                                <x-dynamic-component :component="$s['icon']" class="size-7" />
                            </div>

                            <p class="mt-5 text-xs font-bold uppercase tracking-widest text-gold/70">{{ $s['eyebrow'] }}</p>
                            <h3 class="mt-2 text-xl font-extrabold text-white">{{ $s['title'] }}</h3>
                            <p class="mt-3 text-sm leading-7 text-white/55">{{ $s['desc'] }}</p>

                            <ul class="mt-5 grid gap-2">
                                @foreach($s['features'] as $feat)
                                    <li class="flex items-center gap-2.5 text-sm text-white/65">
                                        <x-heroicon-o-check-circle class="size-4 shrink-0 text-gold" />
                                        {{ $feat }}
                                    </li>
                                @endforeach
                            </ul>

                            <a href="{{ $waBase . urlencode($s['msg']) }}"
                               target="_blank" rel="noopener noreferrer"
                               class="mt-7 inline-flex items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-bold transition {{ $s['featured'] ? 'gold-surface text-ink hover:brightness-110' : 'border border-white/10 text-gold-light hover:border-gold hover:bg-gold hover:text-ink' }}">
                                <x-heroicon-o-chat-bubble-left-right class="size-4" />
                                {{ $s['featured'] ? 'احجز عبر واتساب' : 'اطلب عرض سعر' }}
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ═══ HOW IT WORKS ═══════════════════════════════════════════════════ --}}
    <section class="border-y border-white/8 bg-white/[0.02] px-5 py-16 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <div class="text-center" data-reveal>
                <p class="text-sm font-bold uppercase tracking-widest text-gold">كيف تعمل الخدمة؟</p>
                <h2 class="mt-4 text-2xl font-extrabold text-white sm:text-3xl">ثلاث خطوات بسيطة للرحلة الكاملة</h2>
            </div>

            <div class="relative mt-14 grid gap-8 sm:grid-cols-3">
                <div class="absolute inset-x-[16.5%] top-7 hidden h-px bg-gradient-to-r from-transparent via-gold/30 to-transparent sm:block" aria-hidden="true"></div>

                @foreach([
                    ['step' => '01', 'icon' => 'heroicon-o-chat-bubble-left-right', 'title' => 'تواصل معنا', 'text' => 'أرسل تفاصيل رحلتك عبر نموذج الحجز أو اتصل بنا على واتساب مباشرةً.'],
                    ['step' => '02', 'icon' => 'heroicon-o-document-check', 'title' => 'تأكيد الحجز', 'text' => 'يتواصل معك فريقنا خلال دقائق لتأكيد التفاصيل وتحديد السيارة المناسبة.'],
                    ['step' => '03', 'icon' => 'heroicon-o-star', 'title' => 'استمتع بالرحلة', 'text' => 'سائقك المحترف يصل في الموعد المحدد ليمنحك تجربة سفر لا تُنسى.'],
                ] as $step)
                    <div class="relative text-center">
                        <div class="relative mx-auto inline-flex size-14 items-center justify-center rounded-2xl border border-gold/35 bg-gold/10 text-gold">
                            <x-dynamic-component :component="$step['icon']" class="size-7" />
                            <span class="absolute -end-2 -top-2 flex size-5 items-center justify-center rounded-full bg-gold text-[10px] font-extrabold text-ink">{{ $step['step'] }}</span>
                        </div>
                        <h3 class="mt-5 font-bold text-white">{{ $step['title'] }}</h3>
                        <p class="mt-3 text-sm leading-7 text-white/55">{{ $step['text'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ═══ CITIES COVERAGE ════════════════════════════════════════════════ --}}
    <section class="px-5 py-14 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <div class="overflow-hidden rounded-3xl border border-white/10 bg-panel">
                <div class="grid lg:grid-cols-2">
                    <div class="flex flex-col justify-center p-9 lg:p-14">
                        <p class="text-xs font-bold uppercase tracking-widest text-gold">التغطية الجغرافية</p>
                        <h2 class="mt-4 text-2xl font-extrabold text-white sm:text-3xl">نخدمك في كل مكان بالمملكة</h2>
                        <p class="mt-4 text-sm leading-8 text-white/55">
                            نغطي المدن الرئيسية في المملكة العربية السعودية بأسطول فاخر وسائقين محترفين على استعداد دائم لخدمتك.
                        </p>
                        <ul class="mt-7 grid grid-cols-2 gap-3">
                            @foreach(['الرياض', 'جدة', 'مكة المكرمة', 'المدينة المنورة', 'الطائف', 'الدمام', 'أبها', 'تبوك'] as $city)
                                <li class="flex items-center gap-2 text-sm text-white/70">
                                    <x-heroicon-o-map-pin class="size-4 shrink-0 text-gold" />
                                    {{ $city }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="relative min-h-[280px] overflow-hidden lg:min-h-[400px]">
                        <img src="{{ asset('assets/images/hero-clean.png') }}"
                             alt="تغطية فخامة مسافر في مدن المملكة"
                             class="absolute inset-0 size-full object-cover opacity-60">
                        <div class="absolute inset-0 bg-gradient-to-e from-panel via-transparent to-transparent"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══ FINAL CTA ═══════════════════════════════════════════════════════ --}}
    <section class="px-5 pb-20 lg:px-8">
        <div class="gold-surface mx-auto flex max-w-7xl flex-col items-center justify-between gap-6 rounded-[2rem] px-7 py-10 text-center text-ink md:flex-row md:text-start lg:px-12">
            <div>
                <p class="text-sm font-bold opacity-70">هل أنت مستعد؟</p>
                <h2 class="mt-2 text-2xl font-extrabold sm:text-3xl">ابدأ رحلتك الفاخرة الآن</h2>
                <p class="mt-2 max-w-md text-sm opacity-75">تواصل معنا الآن للحصول على عرض سعر فوري ومجاني لرحلتك القادمة.</p>
            </div>
            <div class="flex shrink-0 flex-col gap-3 sm:flex-row">
                <a href="https://wa.me/{{ $waNumber }}"
                   target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-2 rounded-xl bg-ink px-6 py-4 font-bold text-white transition hover:-translate-y-0.5">
                    <x-heroicon-o-chat-bubble-left-right class="size-5" />
                    واتساب الآن
                </a>
                <a href="{{ route('booking') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-ink/30 bg-ink/10 px-6 py-4 font-bold text-ink transition hover:bg-ink/20">
                    <x-heroicon-o-calendar-days class="size-5" />
                    نموذج الحجز
                </a>
            </div>
        </div>
    </section>

@endsection
