@props(['eyebrow', 'title', 'description' => null])

<section class="relative isolate overflow-hidden border-b border-gold/15 bg-ink-soft">
    <img src="{{ asset('assets/images/hero-clean.png') }}" alt=""
         class="absolute inset-0 -z-20 size-full object-cover opacity-15 grayscale" aria-hidden="true">
    <div class="absolute inset-0 -z-10 bg-gradient-to-b from-ink/40 via-ink/75 to-ink"></div>

    <div class="mx-auto max-w-7xl px-4 py-14 text-center sm:px-6 sm:py-20 lg:px-8" data-reveal>
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-gold">{{ $eyebrow }}</p>
        <h1 class="mx-auto mt-4 max-w-3xl text-3xl font-extrabold leading-tight text-white sm:text-4xl lg:text-5xl">
            {{ $title }}
        </h1>
        @if($description)
            <p class="mx-auto mt-4 max-w-xl text-sm leading-7 text-white/60 sm:text-base sm:leading-8">
                {{ $description }}
            </p>
        @endif
    </div>
</section>
