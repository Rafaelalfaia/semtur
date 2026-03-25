@props([
    'eyebrow' => null,
    'title',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'ui-card-hero p-5 lg:p-6']) }}>
    @if($eyebrow)
        <div class="relative z-[1] mb-3 inline-flex rounded-full bg-white/14 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.15em] text-white/85">
            {{ $eyebrow }}
        </div>
    @endif

    <div class="relative z-[1]">
        <h2 class="text-[1.55rem] font-semibold leading-tight tracking-[-0.04em] text-white lg:text-[1.9rem]">
            {{ $title }}
        </h2>

        @if($description)
            <p class="mt-2.5 max-w-2xl text-[13px] leading-6 text-white/80">
                {{ $description }}
            </p>
        @endif
    </div>

    @if (isset($slot) && trim($slot))
        <div class="relative z-[1] mt-5">
            {{ $slot }}
        </div>
    @endif
</div>
