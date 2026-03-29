@php
    $items = collect($experienciasEntrada ?? [])->values();
@endphp

@if($items->isNotEmpty())
    <section class="site-section site-home-entry-section">
        <x-section-head :title="__('ui.home.entry_title')" />

        <div class="site-home-entry-grid">
            @foreach($items as $item)
                @php $imageSources = site_image_sources($item['image'] ?? null, 'card'); @endphp
                <a
                    href="{{ $item['href'] ?? '#' }}"
                    class="site-home-entry-card site-home-entry-card--{{ $item['key'] ?? 'entry' }}"
                    aria-label="{{ $item['title'] }}"
                    title="{{ $item['title'] }}"
                >
                    <div class="site-home-entry-media">
                        <x-picture
                            :jpg="$imageSources['jpg'] ?? ($item['image'] ?? null)"
                            :webp="$imageSources['webp'] ?? null"
                            :alt="$item['title']"
                            class="site-home-entry-image"
                            sizes="(max-width: 768px) 86vw, 33vw"
                            :width="$imageSources['width'] ?? null"
                            :height="$imageSources['height'] ?? null"
                        />
                    </div>
                </a>
            @endforeach
        </div>
    </section>
@endif
