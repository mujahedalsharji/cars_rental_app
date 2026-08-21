@extends('layouts.app')

@section('title', 'تواصل معنا | فخامة مسافر')
@section('content')
    <x-page-hero eyebrow="نحن بالقرب منك" title="دعنا نرتب رحلتك" description="أرسل استفسارك وسيعود إليك فريق فخامة مسافر في أقرب وقت." />

    <section class="px-5 py-20 lg:px-8">
        <div class="mx-auto grid max-w-7xl gap-10 lg:grid-cols-[0.75fr_1.25fr]">
            <div class="grid content-start gap-5">
                @foreach([
                    ['icon' => 'heroicon-o-phone', 'title' => 'الهاتف', 'value' => $contactSettings['phone_primary'] ?? 'يُضاف من لوحة التحكم', 'href' => filled($contactSettings['phone_primary'] ?? null) ? 'tel:'.$contactSettings['phone_primary'] : null],
                    ['icon' => 'heroicon-o-envelope', 'title' => 'البريد الإلكتروني', 'value' => $contactSettings['email'] ?? 'يُضاف من لوحة التحكم', 'href' => filled($contactSettings['email'] ?? null) ? 'mailto:'.$contactSettings['email'] : null],
                    ['icon' => 'heroicon-o-map-pin', 'title' => 'الموقع', 'value' => $contactSettings['address'] ?? 'المملكة العربية السعودية', 'href' => null],
                ] as $item)
                    <div class="rounded-3xl border border-white/10 bg-panel p-6"><div class="flex items-start gap-4"><span class="inline-flex size-12 shrink-0 items-center justify-center rounded-2xl border border-gold/30 text-gold"><x-dynamic-component :component="$item['icon']" class="size-6" /></span><div><p class="text-xs font-bold text-gold">{{ $item['title'] }}</p>@if($item['href'])<a href="{{ $item['href'] }}" class="mt-2 block font-bold text-white hover:text-gold-light" dir="auto">{{ $item['value'] }}</a>@else<p class="mt-2 font-bold text-white">{{ $item['value'] }}</p>@endif</div></div></div>
                @endforeach
            </div>

            <div class="rounded-[2rem] border border-gold/20 bg-panel p-6 sm:p-9">
                @if(session('success'))
                    <div role="status" class="mb-6 flex items-center gap-3 rounded-2xl border border-emerald-400/25 bg-emerald-400/10 p-4 text-sm text-emerald-200"><x-heroicon-o-check-circle class="size-5 shrink-0" />{{ session('success') }}</div>
                @endif
                <h2 class="text-2xl font-bold text-white">أرسل لنا رسالة</h2>
                <p class="mt-2 text-sm text-white/50">الحقول المعلّمة مطلوبة لإرسال استفسارك.</p>
                <form method="POST" action="{{ route('contact.submit') }}" class="mt-7 grid gap-5 sm:grid-cols-2">
                    @csrf
                    <label class="grid gap-2 text-sm text-white/65">الاسم الكامل *<input name="name" value="{{ old('name') }}" required class="min-h-12 rounded-xl border border-white/10 bg-black/20 px-4 text-white outline-none focus:border-gold/60">@error('name')<span class="text-xs text-red-300">{{ $message }}</span>@enderror</label>
                    <label class="grid gap-2 text-sm text-white/65">البريد الإلكتروني *<input type="email" name="email" value="{{ old('email') }}" required class="min-h-12 rounded-xl border border-white/10 bg-black/20 px-4 text-white outline-none focus:border-gold/60">@error('email')<span class="text-xs text-red-300">{{ $message }}</span>@enderror</label>
                    <label class="grid gap-2 text-sm text-white/65 sm:col-span-2">رقم الهاتف<input type="tel" name="phone" value="{{ old('phone') }}" class="min-h-12 rounded-xl border border-white/10 bg-black/20 px-4 text-white outline-none focus:border-gold/60" dir="ltr">@error('phone')<span class="text-xs text-red-300">{{ $message }}</span>@enderror</label>
                    <label class="grid gap-2 text-sm text-white/65 sm:col-span-2">الرسالة *<textarea name="message" rows="6" required class="rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white outline-none focus:border-gold/60">{{ old('message') }}</textarea>@error('message')<span class="text-xs text-red-300">{{ $message }}</span>@enderror</label>
                    <button class="gold-surface min-h-13 rounded-xl px-7 font-bold text-ink sm:col-span-2">إرسال الرسالة</button>
                </form>
            </div>
        </div>
    </section>
@endsection
