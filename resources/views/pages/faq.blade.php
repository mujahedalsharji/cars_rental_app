@extends('layouts.app')

@section('title', 'الأسئلة الشائعة | فخامة مسافر')

@section('content')

    <x-page-hero eyebrow="معلومات الرحلة" title="كل ما تحتاج معرفته"
        description="إجابات سريعة وواضحة عن الحجز والسيارات والخدمة." />

    <section class="px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
        <div class="mx-auto max-w-3xl">
            <div class="grid gap-3">
                @forelse($faqs as $faq)
                    <details class="group rounded-2xl border border-white/10 bg-panel px-5 py-4 transition open:border-gold/35 sm:px-6 sm:py-5">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-sm font-bold text-white sm:text-base">
                            <span>{{ $faq->question }}</span>
                            <x-heroicon-o-plus class="size-5 shrink-0 text-gold transition duration-300 group-open:rotate-45" />
                        </summary>
                        <p class="mt-4 border-t border-white/8 pt-4 text-sm leading-7 text-white/55 sm:text-base sm:leading-8">
                            {{ $faq->answer }}
                        </p>
                    </details>
                @empty
                    @foreach([
                        ['كيف أبدأ الحجز؟', 'اختر السيارة أو فئة الخدمة ثم أرسل تفاصيل الرحلة من نموذج الحجز. سيتواصل معك الفريق لتأكيد الموعد والسعر.'],
                        ['هل تتوفر الخدمة على مدار الساعة؟', 'نعم، نستقبل طلبات الرحلات طوال اليوم بحسب توفر السيارات والسائقين.'],
                        ['هل يمكن حجز سيارة للتنقل بين المدن؟', 'نعم، نوفر رحلات خاصة بين المدن الرئيسية داخل المملكة مع تنسيق مسبق.'],
                    ] as $faq)
                        <details class="group rounded-2xl border border-white/10 bg-panel px-5 py-4 open:border-gold/35 sm:px-6 sm:py-5">
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-sm font-bold text-white sm:text-base">
                                <span>{{ $faq[0] }}</span>
                                <x-heroicon-o-plus class="size-5 shrink-0 text-gold transition duration-300 group-open:rotate-45" />
                            </summary>
                            <p class="mt-4 border-t border-white/8 pt-4 text-sm leading-7 text-white/55">{{ $faq[1] }}</p>
                        </details>
                    @endforeach
                @endforelse
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="px-4 pb-14 sm:px-6 sm:pb-20 lg:px-8">
        <div class="gold-surface mx-auto max-w-3xl rounded-3xl p-8 text-center text-ink sm:p-10">
            <h2 class="text-xl font-extrabold sm:text-2xl">لم تجد الإجابة التي تبحث عنها؟</h2>
            <p class="mt-3 text-sm font-medium opacity-65">فريقنا جاهز لمساعدتك وترتيب تفاصيل رحلتك.</p>
            <a href="{{ route('contact') }}"
               class="mt-6 inline-flex items-center gap-2 rounded-xl bg-ink px-6 py-3.5 text-sm font-bold text-white transition hover:bg-ink/80">
                تواصل معنا
                <x-heroicon-o-arrow-left class="size-4" />
            </a>
        </div>
    </section>

@endsection
