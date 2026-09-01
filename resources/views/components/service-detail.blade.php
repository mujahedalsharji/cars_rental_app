<div>
    <!-- Very little is needed to make a happy life. - Marcus Aurelius -->
</div>
@props(['service', 'whatsappNumber'])

@php
    $waNumber = preg_replace('/\D+/', '', $whatsappNumber ?? '967777575308');
    $whatsappUrl = 'https://wa.me/'.$waNumber.'?text='.urlencode($service['whatsapp_message']);
@endphp

<div>
    <section class="border-b border-white/8 bg-ink-soft px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
        <div class="mx-auto max-w-6xl">
            <nav aria-label="مسار التنقل" class="flex items-center gap-2 text-xs text-white/45">
                <a href="{{ route('home') }}" class="hover:text-gold-light">الرئيسية</a>
                <x-heroicon-o-chevron-left class="size-3.5" />
                <a href="{{ route('services') }}" class="hover:text-gold-light">الخدمات</a>
                <x-heroicon-o-chevron-left class="size-3.5" />
                <span class="text-white/70">{{ $service['card_title'] }}</span>
            </nav>

            <div class="mt-8 grid gap-8 lg:grid-cols-[1fr_auto] lg:items-end">
                <div>
                    <span class="inline-flex items-center gap-2 rounded-full border border-gold/25 bg-gold/10 px-3 py-1.5 text-xs font-bold text-gold-light">
                        <x-dynamic-component :component="$service['icon']" class="size-4" />
                        {{ $service['eyebrow'] }}
                    </span>
                    <h1 class="mt-5 max-w-3xl text-3xl font-extrabold leading-tight text-white sm:text-4xl lg:text-5xl">
                        {{ $service['title'] }}
                    </h1>
                    <p class="mt-5 max-w-3xl text-sm leading-8 text-white/60 sm:text-base">{{ $service['intro'] }}</p>
                </div>

                <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer"
                   data-whatsapp-number="{{ $waNumber }}"
                   data-whatsapp-message="{{ $service['whatsapp_message'] }}"
                   class="gold-surface inline-flex items-center justify-center gap-2 rounded-xl px-6 py-3.5 text-sm font-bold text-ink">
                    احجز عبر واتساب
                    <x-heroicon-o-chat-bubble-left-right class="size-5" />
                </a>
            </div>
        </div>
    </section>

    <section class="px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
        <div class="mx-auto max-w-6xl">
            <div class="grid gap-4 md:grid-cols-3">
                @foreach($service['benefits'] as $benefit)
                    <article class="rounded-2xl border border-white/10 bg-panel p-6">
                        <x-heroicon-o-check-circle class="size-6 text-gold" />
                        <h2 class="mt-4 font-extrabold text-white">{{ $benefit['title'] }}</h2>
                        <p class="mt-2 text-sm leading-7 text-white/50">{{ $benefit['text'] }}</p>
                    </article>
                @endforeach
            </div>

            <div class="mt-10 grid gap-6 lg:grid-cols-2">
                <section class="rounded-2xl border border-white/10 p-6 sm:p-8">
                    <p class="text-xs font-bold tracking-[0.16em] text-gold">مناسبة لـ</p>
                    <h2 class="mt-3 text-xl font-extrabold text-white">متى تختار هذه الخدمة؟</h2>
                    <ul class="mt-6 grid gap-3 sm:grid-cols-2">
                        @foreach($service['ideal_for'] as $useCase)
                            <li class="flex items-center gap-2.5 text-sm text-white/65">
                                <span class="size-1.5 shrink-0 rounded-full bg-gold"></span>
                                {{ $useCase }}
                            </li>
                        @endforeach
                    </ul>
                </section>

                <section class="rounded-2xl border border-white/10 bg-white/[0.025] p-6 sm:p-8">
                    <p class="text-xs font-bold tracking-[0.16em] text-gold">نطاق الخدمة</p>
                    <h2 class="mt-3 text-xl font-extrabold text-white">المدن والمسارات</h2>
                    <p class="mt-4 text-sm leading-8 text-white/55">{{ $service['coverage'] }}</p>
                </section>
            </div>
        </div>
    </section>

    <section class="border-y border-white/8 bg-white/[0.02] px-4 py-12 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-4xl">
            <div class="text-center">
                <p class="text-xs font-bold tracking-[0.16em] text-gold">الأسئلة الشائعة</p>
                <h2 class="mt-3 text-2xl font-extrabold text-white">قبل أن تحجز</h2>
            </div>

            <div class="mt-7 grid gap-3">
                @foreach($service['faqs'] as $faq)
                    <details class="group rounded-2xl border border-white/10 bg-panel p-5">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-bold text-white">
                            {{ $faq['question'] }}
                            <x-heroicon-o-plus class="size-5 shrink-0 text-gold group-open:rotate-45" />
                        </summary>
                        <p class="mt-3 border-t border-white/8 pt-3 text-sm leading-7 text-white/55">{{ $faq['answer'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <section class="px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
        <div class="gold-surface mx-auto flex max-w-6xl flex-col justify-between gap-5 rounded-2xl px-6 py-8 text-ink sm:flex-row sm:items-center lg:px-9">
            <div>
                <h2 class="text-xl font-extrabold sm:text-2xl">أرسل تفاصيل رحلتك</h2>
                <p class="mt-2 text-sm opacity-75">المدينة والموعد وعدد الركاب والوجهة تكفي لبدء الحجز.</p>
            </div>
            <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer"
               data-whatsapp-number="{{ $waNumber }}"
               data-whatsapp-message="{{ $service['whatsapp_message'] }}"
               class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-ink px-6 py-3.5 text-sm font-bold text-white">
                ابدأ عبر واتساب
                <x-heroicon-o-arrow-left class="size-4" />
            </a>
        </div>
    </section>
</div>
