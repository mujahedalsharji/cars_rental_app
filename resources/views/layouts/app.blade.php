<!DOCTYPE html>
<html lang="ar" dir="rtl" class="scroll-smooth">
<head>
    @php
        $company = $layoutSettings['company'] ?? [];
        $contact = $layoutSettings['contact'] ?? [];
        $social = $layoutSettings['social'] ?? [];
        $companyName = ($company['name'] ?? null) === 'Cars Rental' ? 'فخامة مسافر' : ($company['name'] ?? 'فخامة مسافر');
        $whatsappNumber = $contact['whatsapp_number'] ?? null;
        $whatsappUrl = $whatsappNumber ? 'https://wa.me/'.preg_replace('/\D+/', '', $whatsappNumber) : route('contact');
    @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="@yield('meta_description', 'فخامة مسافر للنقل البري وخدمات السيارات مع سائق في المملكة العربية السعودية.')">
    <meta name="theme-color" content="#080b0d">
    <title>@yield('title', $companyName)</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen overflow-x-hidden bg-ink font-sans text-cream antialiased">
    <a href="#main-content" class="fixed start-4 top-4 z-[100] -translate-y-24 rounded-lg bg-gold px-4 py-3 font-bold text-ink transition focus:translate-y-0">انتقل إلى المحتوى</a>

    <header class="{{ request()->routeIs('home') ? 'absolute' : 'sticky bg-ink/95 shadow-lg shadow-black/20' }} inset-x-0 top-0 z-50 border-b border-white/8 backdrop-blur-xl">
        <div class="mx-auto flex h-24 max-w-7xl items-center justify-between gap-5 px-5 lg:h-28 lg:px-8">
            <a href="{{ route('home') }}" class="relative z-10 shrink-0" aria-label="{{ $companyName }} - الرئيسية">
                <img src="{{ asset('assets/images/logo-clean.png') }}" alt="{{ $companyName }}" class="h-20 w-28 object-contain sm:w-36 lg:h-24 lg:w-40">
            </a>

            <nav aria-label="التنقل الرئيسي" class="hidden items-center gap-8 text-sm font-semibold lg:flex">
                <a href="{{ route('home') }}" class="border-b-2 pb-2 transition {{ request()->routeIs('home') ? 'border-gold text-gold-light' : 'border-transparent text-white/80 hover:text-gold-light' }}">الرئيسية</a>
                <a href="{{ route('cars.index') }}" class="border-b-2 pb-2 transition {{ request()->routeIs('cars.*') ? 'border-gold text-gold-light' : 'border-transparent text-white/80 hover:text-gold-light' }}">السيارات</a>
                <a href="{{ route('about') }}" class="border-b-2 pb-2 transition {{ request()->routeIs('about') ? 'border-gold text-gold-light' : 'border-transparent text-white/80 hover:text-gold-light' }}">من نحن</a>
                <a href="{{ route('faq.index') }}" class="border-b-2 pb-2 transition {{ request()->routeIs('faq.*') ? 'border-gold text-gold-light' : 'border-transparent text-white/80 hover:text-gold-light' }}">الأسئلة الشائعة</a>
                <a href="{{ route('contact') }}" class="border-b-2 pb-2 transition {{ request()->routeIs('contact*') ? 'border-gold text-gold-light' : 'border-transparent text-white/80 hover:text-gold-light' }}">تواصل معنا</a>
            </nav>

            <div class="hidden lg:block">
                <a href="{{ $whatsappUrl }}" @if($whatsappNumber) target="_blank" rel="noopener noreferrer" @endif class="gold-surface inline-flex items-center gap-2 rounded-2xl px-5 py-3 text-sm font-bold text-ink shadow-lg shadow-gold/10 transition hover:-translate-y-0.5 hover:brightness-110">
                    <x-heroicon-o-chat-bubble-left-right class="size-5" />
                    احجز عبر واتساب
                </a>
            </div>

            <button type="button" data-mobile-menu-button aria-expanded="false" aria-controls="mobile-menu" class="inline-flex size-11 items-center justify-center rounded-xl border border-white/15 bg-black/30 text-white lg:hidden">
                <span class="sr-only">فتح القائمة</span>
                <x-heroicon-o-bars-3 class="size-6" />
            </button>
        </div>

        <nav id="mobile-menu" data-mobile-menu aria-label="تنقل الجوال" class="hidden border-t border-white/10 bg-ink/98 px-5 pb-6 pt-4 lg:hidden">
            <div class="grid gap-1 text-base font-semibold">
                <a href="{{ route('home') }}" class="rounded-xl px-4 py-3 hover:bg-white/5 hover:text-gold-light">الرئيسية</a>
                <a href="{{ route('cars.index') }}" class="rounded-xl px-4 py-3 hover:bg-white/5 hover:text-gold-light">السيارات</a>
                <a href="{{ route('about') }}" class="rounded-xl px-4 py-3 hover:bg-white/5 hover:text-gold-light">من نحن</a>
                <a href="{{ route('faq.index') }}" class="rounded-xl px-4 py-3 hover:bg-white/5 hover:text-gold-light">الأسئلة الشائعة</a>
                <a href="{{ route('contact') }}" class="rounded-xl px-4 py-3 hover:bg-white/5 hover:text-gold-light">تواصل معنا</a>
                <a href="{{ $whatsappUrl }}" @if($whatsappNumber) target="_blank" rel="noopener noreferrer" @endif class="gold-surface mt-3 inline-flex items-center justify-center gap-2 rounded-xl px-4 py-3 font-bold text-ink">
                    <x-heroicon-o-chat-bubble-left-right class="size-5" />
                    احجز عبر واتساب
                </a>
            </div>
        </nav>
    </header>

    <main id="main-content">@yield('content')</main>

    <footer class="border-t border-gold/20 bg-black">
        <div class="mx-auto grid max-w-7xl gap-10 px-5 py-14 md:grid-cols-[1.3fr_0.7fr_0.8fr] lg:px-8">
            <div class="max-w-md">
                <img src="{{ asset('assets/images/logo-clean.png') }}" alt="{{ $companyName }}" class="h-28 w-48 object-contain">
                <p class="text-sm leading-8 text-white/60">رحلات خاصة بسيارات حديثة وسائقين محترفين، لتصل إلى وجهتك براحة وأمان يليقان بك.</p>
            </div>
            <div>
                <h2 class="font-bold text-gold-light">روابط سريعة</h2>
                <div class="mt-5 grid gap-3 text-sm text-white/60">
                    <a href="{{ route('cars.index') }}" class="hover:text-gold-light">أسطول السيارات</a>
                    <a href="{{ route('about') }}" class="hover:text-gold-light">عن فخامة مسافر</a>
                    <a href="{{ route('faq.index') }}" class="hover:text-gold-light">الأسئلة الشائعة</a>
                    <a href="{{ route('contact') }}" class="hover:text-gold-light">تواصل معنا</a>
                </div>
            </div>
            <div>
                <h2 class="font-bold text-gold-light">تواصل معنا</h2>
                <div class="mt-5 grid gap-3 text-sm text-white/60">
                    @if($contact['phone_primary'] ?? null)<a dir="ltr" href="tel:{{ $contact['phone_primary'] }}" class="w-fit hover:text-gold-light">{{ $contact['phone_primary'] }}</a>@endif
                    @if($contact['email'] ?? null)<a dir="ltr" href="mailto:{{ $contact['email'] }}" class="w-fit hover:text-gold-light">{{ $contact['email'] }}</a>@endif
                    <span>{{ $contact['address'] ?? 'المملكة العربية السعودية' }}</span>
                </div>
            </div>
        </div>
        <div class="border-t border-white/8 px-5 py-5 text-center text-xs text-white/45">© {{ now()->year }} {{ $companyName }}. جميع الحقوق محفوظة.</div>
    </footer>

    <a href="{{ $whatsappUrl }}" @if($whatsappNumber) target="_blank" rel="noopener noreferrer" @endif aria-label="تواصل عبر واتساب" class="fixed bottom-5 end-5 z-40 inline-flex size-14 items-center justify-center rounded-full bg-[#25D366] text-white shadow-2xl shadow-black/40 transition hover:scale-105">
        <x-heroicon-o-chat-bubble-left-right class="size-7" />
    </a>
</body>
</html>
