@extends('layouts.app')

@section('title', 'احجز رحلتك | فخامة مسافر')
@section('content')
    <x-page-hero eyebrow="طلب رحلة" title="بضع تفاصيل تفصلك عن رحلتك" description="املأ بيانات الرحلة وسيفتح طلب منظم عبر واتساب لتأكيد التوفر والسعر." />

    <section class="px-5 py-20 lg:px-8">
        <div class="mx-auto grid max-w-6xl gap-10 lg:grid-cols-[0.72fr_1.28fr] lg:items-start">
            <aside class="overflow-hidden rounded-[2rem] border border-white/10 bg-panel lg:sticky lg:top-36">
                <img src="{{ $selectedCar?->getFirstMediaUrl('car_images') ?: asset('assets/images/fleet-gmc-yukon.png') }}" alt="{{ $selectedCar?->name ?? 'سيارة فاخرة من فخامة مسافر' }}" class="aspect-[4/3] w-full object-cover">
                <div class="p-6"><p class="text-xs font-bold text-gold">{{ $selectedCar ? 'السيارة المختارة' : 'رحلتك معنا' }}</p><h2 class="mt-2 text-2xl font-bold text-white">{{ $selectedCar?->name ?? 'اختر سيارتك ووجهتك' }}</h2><ul class="mt-5 grid gap-3 text-sm text-white/55"><li class="flex items-center gap-3"><x-heroicon-o-shield-check class="size-5 text-gold" />سائق محترف وخدمة موثوقة</li><li class="flex items-center gap-3"><x-heroicon-o-clock class="size-5 text-gold" />تنسيق ومتابعة على مدار الساعة</li><li class="flex items-center gap-3"><x-heroicon-o-sparkles class="size-5 text-gold" />سيارات حديثة ونظيفة</li></ul></div>
            </aside>

            <form method="GET" action="{{ route('contact') }}" data-booking-form data-whatsapp-number="{{ $whatsappNumber }}" class="grid gap-5 rounded-[2rem] border border-gold/20 bg-panel p-6 sm:grid-cols-2 sm:p-9">
                <div class="sm:col-span-2"><h2 class="text-2xl font-bold text-white">تفاصيل الطلب</h2><p class="mt-2 text-sm text-white/50">لن يتم إرسال أي شيء قبل ضغطك على زر الإرسال في واتساب.</p></div>
                <label class="grid gap-2 text-sm text-white/65">الاسم الكامل *<input name="name" required class="min-h-12 rounded-xl border border-white/10 bg-black/20 px-4 text-white outline-none focus:border-gold/60"></label>
                <label class="grid gap-2 text-sm text-white/65">رقم الهاتف *<input name="phone" type="tel" required class="min-h-12 rounded-xl border border-white/10 bg-black/20 px-4 text-white outline-none focus:border-gold/60" dir="ltr"></label>
                <label class="grid gap-2 text-sm text-white/65 sm:col-span-2">السيارة أو الفئة
                    <select name="car_name" class="min-h-12 rounded-xl border border-white/10 bg-ink px-4 text-white outline-none focus:border-gold/60">
                        <option value="">اختر السيارة</option>
                        @if($selectedCar)<option value="{{ $selectedCar->name }}" selected>{{ $selectedCar->name }}</option>@endif
                        @foreach($cars as $car)@if(!$selectedCar || $selectedCar->isNot($car))<option value="{{ $car->name }}">{{ $car->name }}</option>@endif @endforeach
                        @if($cars->isEmpty())<option value="سيدان فاخرة" @selected(request('car_name') === 'سيدان فاخرة')>سيدان فاخرة</option><option value="SUV فاخرة" @selected(request('car_name') === 'SUV فاخرة')>SUV فاخرة</option><option value="عائلية فاخرة" @selected(request('car_name') === 'عائلية فاخرة')>عائلية فاخرة</option>@endif
                    </select>
                </label>
                <label class="grid gap-2 text-sm text-white/65">من *<input name="pickup" value="{{ request('pickup') }}" required placeholder="موقع الانطلاق" class="min-h-12 rounded-xl border border-white/10 bg-black/20 px-4 text-white outline-none placeholder:text-white/25 focus:border-gold/60"></label>
                <label class="grid gap-2 text-sm text-white/65">إلى *<input name="destination" value="{{ request('destination') }}" required placeholder="الوجهة" class="min-h-12 rounded-xl border border-white/10 bg-black/20 px-4 text-white outline-none placeholder:text-white/25 focus:border-gold/60"></label>
                <label class="grid gap-2 text-sm text-white/65">التاريخ *<input name="date" type="date" value="{{ request('date') }}" min="{{ now()->toDateString() }}" required class="min-h-12 rounded-xl border border-white/10 bg-black/20 px-4 text-white outline-none scheme-dark focus:border-gold/60"></label>
                <label class="grid gap-2 text-sm text-white/65">الوقت *<input name="time" type="time" required class="min-h-12 rounded-xl border border-white/10 bg-black/20 px-4 text-white outline-none scheme-dark focus:border-gold/60"></label>
                <label class="grid gap-2 text-sm text-white/65 sm:col-span-2">عدد الركاب<select name="passengers" class="min-h-12 rounded-xl border border-white/10 bg-ink px-4 text-white outline-none focus:border-gold/60">@foreach(range(1, 12) as $count)<option value="{{ $count }}" @selected((int) request('passengers', 1) === $count)>{{ $count }}</option>@endforeach</select></label>
                <button class="gold-surface min-h-14 rounded-xl px-7 font-bold text-ink sm:col-span-2">إرسال الطلب عبر واتساب</button>
                @unless($whatsappNumber)<p class="text-center text-xs text-amber-200/75 sm:col-span-2">أضف رقم واتساب من لوحة التحكم لتفعيل إرسال الطلب مباشرة. سيتم توجيهك حالياً إلى صفحة التواصل.</p>@endunless
            </form>
        </div>
    </section>
@endsection
