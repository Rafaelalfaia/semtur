@php
    $items = collect($experienciasEntrada ?? [])->values();
@endphp

@if($items->isNotEmpty())
    <section class="site-section site-home-entry-section">
        <x-section-head title="Experiências para começar em Altamira" />

        <div class="site-home-entry-grid">
            @foreach($items as $item)
                <a
                    href="{{ $item['href'] ?? '#' }}"
                    class="site-home-entry-card site-home-entry-card--{{ $item['key'] ?? 'entry' }}"
                    aria-label="{{ $item['title'] }}"
                    title="{{ $item['title'] }}"
                >
                    <div class="site-home-entry-media">
                        <img
                            src="{{ $item['image'] }}"
                            alt="{{ $item['title'] }}"
                            loading="lazy"
                            decoding="async"
                            class="site-home-entry-image"
                        >
                    </div>
                </a>
            @endforeach
        </div>
    </section>
@endif
