@extends('layouts.app')

@section('title', 'تواصل معنا | فخامة مسافر')

@section('content')

    <x-page-hero eyebrow="نحن بالقرب منك" title="دعنا نرتب رحلتك" image="assets/images/heroes/contact.webp"
        description="أرسل استفسارك وسيعود إليك فريق فخامة مسافر في أقرب وقت." />

    <section class="px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
        <div class="mx-auto grid max-w-7xl gap-8 lg:grid-cols-[0.8fr_1.2fr]">

            {{-- Contact info --}}
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1 lg:content-start">
                @foreach([
                    ['icon' => 'heroicon-o-phone', 'title' => 'الهاتف',
                     'value' => $contactSettings['phone_primary'] ?? 'يُضاف من لوحة التحكم',
                     'href' => filled($contactSettings['phone_primary'] ?? null) ? 'tel:'.$contactSettings['phone_primary'] : null],
                    ['icon' => 'heroicon-o-envelope', 'title' => 'البريد الإلكتروني',
                     'value' => $contactSettings['email'] ?? 'يُضاف من لوحة التحكم',
                     'href' => filled($contactSettings['email'] ?? null) ? 'mailto:'.$contactSettings['email'] : null],
                    ['icon' => 'heroicon-o-map-pin', 'title' => 'الموقع',
                     'value' => $contactSettings['address'] ?? 'المملكة العربية السعودية',
                     'href' => null],
                ] as $item)
                    <div class="rounded-3xl border border-white/10 bg-panel p-5">
                        <div class="flex items-start gap-4">
                            <span class="inline-flex size-11 shrink-0 items-center justify-center rounded-2xl border border-gold/25 text-gold">
                                <x-dynamic-component :component="$item['icon']" class="size-5" />
                            </span>
                            <div>
                                <p class="text-xs font-bold text-gold">{{ $item['title'] }}</p>
                                @if($item['href'])
                                    <a href="{{ $item['href'] }}" class="mt-1.5 block text-sm font-bold text-white hover:text-gold-light" dir="auto">
                                        {{ $item['value'] }}
                                    </a>
                                @else
                                    <p class="mt-1.5 text-sm font-bold text-white">{{ $item['value'] }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Form --}}
            <div class="rounded-3xl border border-gold/15 bg-panel p-6 sm:p-8">
                @if(session('success'))
                    <div role="status" class="mb-5 flex items-center gap-3 rounded-2xl border border-emerald-400/25 bg-emerald-400/10 p-4 text-sm text-emerald-200">
                        <x-heroicon-o-check-circle class="size-5 shrink-0" />
                        {{ session('success') }}
                    </div>
                @endif
                <h2 class="text-xl font-bold text-white sm:text-2xl">أرسل لنا رسالة</h2>
                <p class="mt-1.5 text-sm text-white/45">الحقول المعلّمة (*) مطلوبة.</p>

                <form method="POST" action="{{ route('contact.submit') }}" class="mt-6 grid gap-4 sm:grid-cols-2">
                    @csrf
                    <label class="grid gap-1.5 text-xs text-white/60">
                        الاسم الكامل *
                        <input name="name" value="{{ old('name') }}" required
                               class="min-h-11 rounded-xl border border-white/10 bg-black/20 px-4 text-sm text-white outline-none transition focus:border-gold/60 placeholder:text-white/25">
                        @error('name')<span class="text-red-400">{{ $message }}</span>@enderror
                    </label>
                    <label class="grid gap-1.5 text-xs text-white/60">
                        البريد الإلكتروني *
                        <input type="email" name="email" value="{{ old('email') }}" required
                               class="min-h-11 rounded-xl border border-white/10 bg-black/20 px-4 text-sm text-white outline-none transition focus:border-gold/60 placeholder:text-white/25">
                        @error('email')<span class="text-red-400">{{ $message }}</span>@enderror
                    </label>
                    <label class="grid gap-1.5 text-xs text-white/60 sm:col-span-2">
                        رقم الهاتف
                        <input type="tel" name="phone" value="{{ old('phone') }}" dir="ltr"
                               class="min-h-11 rounded-xl border border-white/10 bg-black/20 px-4 text-sm text-white outline-none transition focus:border-gold/60 placeholder:text-white/25">
                        @error('phone')<span class="text-red-400">{{ $message }}</span>@enderror
                    </label>
                    <label class="grid gap-1.5 text-xs text-white/60 sm:col-span-2">
                        الرسالة *
                        <textarea name="message" rows="5" required
                                  class="rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-sm text-white outline-none transition focus:border-gold/60 placeholder:text-white/25">{{ old('message') }}</textarea>
                        @error('message')<span class="text-red-400">{{ $message }}</span>@enderror
                    </label>
                    <button class="gold-surface min-h-12 rounded-xl px-7 text-sm font-bold text-ink transition hover:brightness-110 sm:col-span-2">
                        إرسال الرسالة
                    </button>
                </form>
            </div>
        </div>
    </section>

@endsection
