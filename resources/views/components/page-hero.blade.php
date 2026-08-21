@props(['eyebrow', 'title', 'description' => null])

<section class="relative isolate overflow-hidden border-b border-gold/15 bg-ink-soft py-20 sm:py-24">
    <img src="{{ asset('assets/images/hero-clean.png') }}" alt="" class="absolute inset-0 -z-20 size-full object-cover opacity-20 grayscale">
    <div class="absolute inset-0 -z-10 bg-gradient-to-b from-ink/35 via-ink/80 to-ink"></div>
    <div class="mx-auto max-w-7xl px-5 text-center lg:px-8" data-reveal>
        <p class="text-sm font-bold tracking-[0.18em] text-gold">{{ $eyebrow }}</p>
        <h1 class="mx-auto mt-4 max-w-4xl text-4xl font-extrabold leading-tight text-white sm:text-5xl lg:text-6xl">{{ $title }}</h1>
        @if($description)<p class="mx-auto mt-5 max-w-2xl text-base leading-8 text-white/65 sm:text-lg">{{ $description }}</p>@endif
    </div>
</section>
