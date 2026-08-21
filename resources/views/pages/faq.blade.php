@extends('layouts.app')

@section('title', 'الأسئلة الشائعة | فخامة مسافر')
@section('content')
    <x-page-hero eyebrow="معلومات الرحلة" title="كل ما تحتاج معرفته" description="إجابات سريعة وواضحة عن الحجز والسيارات والخدمة." />

    <section class="px-5 py-20 lg:px-8">
        <div class="mx-auto grid max-w-4xl gap-4">
            @forelse($faqs as $faq)
                <details class="group rounded-2xl border border-white/10 bg-panel px-5 py-5 open:border-gold/40 sm:px-7">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-5 text-base font-bold text-white sm:text-lg"><span>{{ $faq->question }}</span><x-heroicon-o-plus class="size-5 shrink-0 text-gold transition group-open:rotate-45" /></summary>
                    <p class="mt-5 border-t border-white/8 pt-5 text-sm leading-8 text-white/60 sm:text-base">{{ $faq->answer }}</p>
                </details>
            @empty
                @foreach([
                    ['كيف أبدأ الحجز؟', 'اختر السيارة أو فئة الخدمة ثم أرسل تفاصيل الرحلة من نموذج الحجز. سيتواصل معك الفريق لتأكيد الموعد والسعر.'],
                    ['هل تتوفر الخدمة على مدار الساعة؟', 'نعم، نستقبل طلبات الرحلات طوال اليوم بحسب توفر السيارات والسائقين.'],
                    ['هل يمكن حجز سيارة للتنقل بين المدن؟', 'نعم، نوفر رحلات خاصة بين المدن الرئيسية داخل المملكة مع تنسيق مسبق.'],
                ] as $faq)
                    <details class="group rounded-2xl border border-white/10 bg-panel px-5 py-5 open:border-gold/40 sm:px-7"><summary class="flex cursor-pointer list-none items-center justify-between gap-5 text-lg font-bold text-white"><span>{{ $faq[0] }}</span><x-heroicon-o-plus class="size-5 shrink-0 text-gold group-open:rotate-45" /></summary><p class="mt-5 border-t border-white/8 pt-5 text-sm leading-8 text-white/60">{{ $faq[1] }}</p></details>
                @endforeach
            @endforelse
        </div>
    </section>

    <section class="px-5 pb-20 lg:px-8">
        <div class="gold-surface mx-auto max-w-4xl rounded-[2rem] p-8 text-center text-ink sm:p-10"><h2 class="text-2xl font-extrabold">لم تجد الإجابة التي تبحث عنها؟</h2><p class="mt-3 text-sm font-medium opacity-70">فريقنا جاهز لمساعدتك وترتيب تفاصيل رحلتك.</p><a href="{{ route('contact') }}" class="mt-6 inline-flex items-center gap-2 rounded-xl bg-ink px-6 py-4 font-bold text-white">تواصل معنا <x-heroicon-o-arrow-left class="size-5" /></a></div>
    </section>
@endsection
