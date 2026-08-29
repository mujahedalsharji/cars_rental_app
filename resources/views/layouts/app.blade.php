<!DOCTYPE html>
<html lang="ar" dir="rtl" class="scroll-smooth">
<head>
    @php
        $company = $layoutSettings['company'] ?? [];
        $contact = $layoutSettings['contact'] ?? [];
        $appearance = $layoutSettings['appearance'] ?? [];
        $companyName = ($company['name'] ?? null) === 'Cars Rental' ? 'فخامة مسافر' : ($company['name'] ?? 'فخامة مسافر');
        $whatsappNumber = $contact['whatsapp_number'] ?? null;
        $whatsappUrl = $whatsappNumber ? 'https://wa.me/'.preg_replace('/\D+/', '', $whatsappNumber) : route('contact');
        $logoUrl = filled($company['logo'] ?? null) ? \Illuminate\Support\Facades\Storage::disk('public')->url($company['logo']) : asset('assets/images/logo-clean.png');
        $faviconUrl = filled($appearance['favicon'] ?? null) ? \Illuminate\Support\Facades\Storage::disk('public')->url($appearance['favicon']) : asset('favicon.ico');
        $canonicalUrl = app(\App\Support\CanonicalUrl::class)->fromRequest(request());
    @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="@yield('meta_description', 'فخامة مسافر للنقل البري وخدمات السيارات مع سائق في المملكة العربية السعودية.')">
    <meta name="theme-color" content="#080b0d">
    <title>@yield('title', $companyName)</title>
    <link rel="canonical" href="@yield('canonical', $canonicalUrl)">
    <link rel="icon" href="{{ $faviconUrl }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen overflow-x-hidden bg-ink font-sans text-cream antialiased">

    {{-- Skip to content --}}
    <a href="#main-content" class="fixed start-4 top-4 z-[100] -translate-y-24 rounded-lg bg-gold px-4 py-3 font-bold text-ink transition focus:translate-y-0">
        انتقل إلى المحتوى
    </a>

    {{-- ─── HEADER ─────────────────────────────────────────────────────────── --}}
    <header class="{{ request()->routeIs('home') ? 'absolute' : 'sticky bg-ink/95 shadow-lg shadow-black/20 backdrop-blur-xl' }} inset-x-0 top-0 z-50 border-b border-white/8">
        <div class="mx-auto flex h-20 max-w-7xl items-center justify-between gap-4 px-4 sm:h-24 sm:px-6 lg:h-28 lg:px-8">

            {{-- Logo --}}
            <a href="{{ route('home') }}" class="relative z-10 shrink-0" aria-label="{{ $companyName }} - الرئيسية">
                <img src="{{ $logoUrl }}" alt="{{ $companyName }}" class="h-14 w-20 object-contain sm:h-16 sm:w-28 lg:h-20 lg:w-36">
            </a>

            {{-- Desktop Nav --}}
            <nav aria-label="التنقل الرئيسي" class="hidden items-center gap-6 text-sm font-semibold lg:flex xl:gap-8">
                <a href="{{ route('home') }}" class="border-b-2 pb-1.5 transition {{ request()->routeIs('home') ? 'border-gold text-gold-light' : 'border-transparent text-white/80 hover:text-gold-light' }}">الرئيسية</a>
                <a href="{{ route('cars.index') }}" class="border-b-2 pb-1.5 transition {{ request()->routeIs('cars.*') ? 'border-gold text-gold-light' : 'border-transparent text-white/80 hover:text-gold-light' }}">السيارات</a>
                <a href="{{ route('services') }}" class="border-b-2 pb-1.5 transition {{ request()->routeIs('services', 'services.*') ? 'border-gold text-gold-light' : 'border-transparent text-white/80 hover:text-gold-light' }}">خدماتنا</a>
                <a href="{{ route('about') }}" class="border-b-2 pb-1.5 transition {{ request()->routeIs('about') ? 'border-gold text-gold-light' : 'border-transparent text-white/80 hover:text-gold-light' }}">من نحن</a>
                <a href="{{ route('faq.index') }}" class="border-b-2 pb-1.5 transition {{ request()->routeIs('faq.*') ? 'border-gold text-gold-light' : 'border-transparent text-white/80 hover:text-gold-light' }}">الأسئلة الشائعة</a>
                <a href="{{ route('contact') }}" class="border-b-2 pb-1.5 transition {{ request()->routeIs('contact*') ? 'border-gold text-gold-light' : 'border-transparent text-white/80 hover:text-gold-light' }}">تواصل معنا</a>
            </nav>

            {{-- Desktop CTA --}}
            <div class="hidden lg:block">
                <a href="{{ $whatsappUrl }}" @if($whatsappNumber) target="_blank" rel="noopener noreferrer" @endif
                   class="gold-surface inline-flex items-center gap-2 rounded-2xl px-5 py-2.5 text-sm font-bold text-ink shadow-lg shadow-gold/10 transition hover:-translate-y-0.5 hover:brightness-110">
                    <x-heroicon-o-chat-bubble-left-right class="size-5" />
                    احجز عبر واتساب
                </a>
            </div>

            {{-- Mobile hamburger --}}
            <button type="button" data-mobile-menu-button aria-expanded="false" aria-controls="mobile-menu"
                    class="inline-flex size-10 items-center justify-center rounded-xl border border-white/15 bg-black/30 text-white lg:hidden">
                <span class="sr-only">فتح القائمة</span>
                <x-heroicon-o-bars-3 class="size-5" />
            </button>
        </div>

        {{-- Mobile menu --}}
        <nav id="mobile-menu" data-mobile-menu aria-label="تنقل الجوال"
             class="hidden border-t border-white/10 bg-ink/98 px-4 pb-5 pt-3 lg:hidden">
            <div class="grid gap-1 text-sm font-semibold">
                <a href="{{ route('home') }}" class="rounded-xl px-4 py-3 transition hover:bg-white/5 hover:text-gold-light {{ request()->routeIs('home') ? 'text-gold-light' : '' }}">الرئيسية</a>
                <a href="{{ route('cars.index') }}" class="rounded-xl px-4 py-3 transition hover:bg-white/5 hover:text-gold-light {{ request()->routeIs('cars.*') ? 'text-gold-light' : '' }}">السيارات</a>
                <a href="{{ route('services') }}" class="rounded-xl px-4 py-3 transition hover:bg-white/5 hover:text-gold-light {{ request()->routeIs('services', 'services.*') ? 'text-gold-light' : '' }}">خدماتنا</a>
                <a href="{{ route('about') }}" class="rounded-xl px-4 py-3 transition hover:bg-white/5 hover:text-gold-light {{ request()->routeIs('about') ? 'text-gold-light' : '' }}">من نحن</a>
                <a href="{{ route('faq.index') }}" class="rounded-xl px-4 py-3 transition hover:bg-white/5 hover:text-gold-light {{ request()->routeIs('faq.*') ? 'text-gold-light' : '' }}">الأسئلة الشائعة</a>
                <a href="{{ route('contact') }}" class="rounded-xl px-4 py-3 transition hover:bg-white/5 hover:text-gold-light {{ request()->routeIs('contact*') ? 'text-gold-light' : '' }}">تواصل معنا</a>
                <a href="{{ $whatsappUrl }}" @if($whatsappNumber) target="_blank" rel="noopener noreferrer" @endif
                   class="gold-surface mt-2 flex items-center justify-center gap-2 rounded-xl px-4 py-3 font-bold text-ink">
                    <x-heroicon-o-chat-bubble-left-right class="size-5" />
                    احجز عبر واتساب
                </a>
            </div>
        </nav>
    </header>

    {{-- ─── MAIN ────────────────────────────────────────────────────────────── --}}
    <main id="main-content">@yield('content')</main>

    {{-- ─── FOOTER ──────────────────────────────────────────────────────────── --}}
    <footer class="border-t border-gold/20 bg-black">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="grid gap-10 sm:grid-cols-2 md:grid-cols-[1.4fr_0.8fr_0.8fr]">
                {{-- Brand --}}
                <div>
                    <img src="{{ $logoUrl }}" alt="{{ $companyName }}" class="h-20 w-32 object-contain">
                    <p class="mt-4 max-w-xs text-sm leading-7 text-white/55">
                        رحلات خاصة بسيارات حديثة وسائقين محترفين، لتصل إلى وجهتك براحة وأمان.
                    </p>
                </div>
                {{-- Quick links --}}
                <div>
                    <h2 class="text-sm font-bold text-gold-light">روابط سريعة</h2>
                    <div class="mt-4 grid gap-3 text-sm text-white/55">
                        <a href="{{ route('cars.index') }}" class="hover:text-gold-light">أسطول السيارات</a>
                        <a href="{{ route('services') }}" class="hover:text-gold-light">خدماتنا</a>
                        <a href="{{ route('about') }}" class="hover:text-gold-light">عن فخامة مسافر</a>
                        <a href="{{ route('faq.index') }}" class="hover:text-gold-light">الأسئلة الشائعة</a>
                        <a href="{{ route('contact') }}" class="hover:text-gold-light">تواصل معنا</a>
                    </div>
                </div>
                {{-- Contact --}}
                <div>
                    <h2 class="text-sm font-bold text-gold-light">تواصل معنا</h2>
                    <div class="mt-4 grid gap-3 text-sm text-white/55">
                        @if($contact['phone_primary'] ?? null)
                            <a dir="ltr" href="tel:{{ $contact['phone_primary'] }}" class="w-fit hover:text-gold-light">{{ $contact['phone_primary'] }}</a>
                        @endif
                        @if($contact['email'] ?? null)
                            <a dir="ltr" href="mailto:{{ $contact['email'] }}" class="w-fit hover:text-gold-light">{{ $contact['email'] }}</a>
                        @endif
                        <span>{{ $contact['address'] ?? 'المملكة العربية السعودية' }}</span>
                    </div>
                </div>
            </div>
            <div class="mt-10 border-t border-white/8 pt-6 text-center text-xs text-white/35">
                © {{ now()->year }} {{ $companyName }}. جميع الحقوق محفوظة.
            </div>
        </div>
    </footer>

    {{-- WhatsApp FAB --}}
    <a href="{{ $whatsappUrl }}" @if($whatsappNumber) target="_blank" rel="noopener noreferrer" @endif
       aria-label="تواصل عبر واتساب"
       class="fixed bottom-5 end-5 z-40 inline-flex size-13 items-center justify-center rounded-full bg-[#25D366] text-white shadow-2xl shadow-black/40 transition hover:scale-110 active:scale-95">
        <x-heroicon-o-chat-bubble-left-right class="size-6" />
    </a>

</body>
</html>
