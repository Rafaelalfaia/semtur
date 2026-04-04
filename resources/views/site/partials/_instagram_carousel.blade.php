@php
    $items = collect($instagram ?? [])->values();
    $instagramEyebrow = $eyebrow ?? ui_text('ui.instagram.eyebrow');
    $instagramTitle = $title ?? '@visitaltamira';
    $instagramHref = $href ?? 'https://www.instagram.com/visitaltamira/';
    $instagramEditor = $editor ?? null;
@endphp

@if($items->isNotEmpty())
    <section class="site-section site-home-instagram-section" x-data="{
        canPrev: false,
        canNext: true,
        update() {
            const el = this.$refs.viewport;
            if (!el) return;
            this.canPrev = el.scrollLeft > 12;
            this.canNext = (el.scrollWidth - el.clientWidth - el.scrollLeft) > 12;
        },
        scrollBy(direction) {
            const el = this.$refs.viewport;
            if (!el) return;
            const step = Math.max(el.clientWidth * 0.82, 260);
            el.scrollBy({ left: step * direction, behavior: 'smooth' });
            window.setTimeout(() => this.update(), 220);
        }
    }" x-init="$nextTick(() => update())">
        <div class="site-section-head">
            <div>
                @if($instagramEditor)
                    @include('site.partials._content_editor', [
                        'editorTitle' => $instagramEditor['title'] ?? $instagramTitle,
                        'editorPage' => $instagramEditor['page'] ?? 'site.home',
                        'editorKey' => $instagramEditor['key'] ?? 'instagram_section',
                        'editorLabel' => $instagramEditor['label'] ?? 'Seção Instagram',
                        'editorLocale' => route_locale(),
                        'editorTriggerVariant' => 'inline-compact',
                        'editorTriggerLabel' => 'Editar texto',
                        'editorFields' => ['eyebrow', 'titulo', 'cta_href'],
                        'editableTranslation' => $instagramEditor['translation'] ?? null,
                        'editableStatus' => $instagramEditor['status'] ?? 'publicado',
                        'editableFallback' => [
                            'eyebrow' => $instagramEyebrow,
                            'titulo' => $instagramTitle,
                            'cta_href' => $instagramHref,
                        ],
                    ])
                @endif
                <p class="site-section-head-eyebrow">{{ $instagramEyebrow }}</p>
                <h2 class="site-section-head-title">{{ $instagramTitle }}</h2>
            </div>

            <a href="{{ $instagramHref }}" class="site-section-head-link" target="_blank" rel="noopener noreferrer">
                {{ ui_text('ui.instagram.open') }}
            </a>
        </div>

        <div class="site-instagram-shell">
            <div class="site-instagram-headerline">
                <div class="site-instagram-profile">
                    <span class="site-badge">VisitAltamira</span>
                    <span class="site-instagram-profile-copy">{{ ui_text('ui.instagram.profile_copy') }}</span>
                </div>

                <div class="site-instagram-controls" aria-hidden="true">
                    <button type="button" class="site-instagram-control" @click="scrollBy(-1)" :disabled="!canPrev" :aria-disabled="!canPrev">
                        <span>&larr;</span>
                    </button>
                    <button type="button" class="site-instagram-control" @click="scrollBy(1)" :disabled="!canNext" :aria-disabled="!canNext">
                        <span>&rarr;</span>
                    </button>
                </div>
            </div>

            <div class="site-instagram-track" x-ref="viewport" @scroll.debounce.50ms="update()" x-on:resize.window.debounce.120ms="update()">
                @foreach($items as $post)
                    @php
                        $rawImage = $post['image'] ?? null;
                        $resolvedImage = $rawImage && \Illuminate\Support\Facades\Route::has('proxy.ig')
                            ? route('proxy.ig', ['u' => $rawImage])
                            : $rawImage;
                    @endphp
                    <a
                        href="{{ $post['url'] ?? 'https://www.instagram.com/visitaltamira/' }}"
                        target="_blank"
                        rel="noopener"
                        class="site-instagram-card"
                    >
                        <div class="site-instagram-card-media">
                            @if(!empty($resolvedImage))
                                <img
                                    src="{{ $resolvedImage }}"
                                    alt="{{ ui_text('ui.instagram.post_alt') }}"
                                    class="site-instagram-card-image"
                                    loading="lazy"
                                    decoding="async"
                                    referrerpolicy="no-referrer"
                                >
                            @else
                                <div class="site-instagram-card-placeholder"></div>
                            @endif
                        </div>

                        <div class="site-instagram-card-body">
                            <span class="site-badge">{{ ui_text('ui.instagram.badge') }}</span>
                            @if(!empty($post['caption']))
                                <p class="site-instagram-card-caption">
                                    {{ \Illuminate\Support\Str::limit(trim($post['caption']), 108) }}
                                </p>
                            @else
                                <p class="site-instagram-card-caption">
                                    {{ ui_text('ui.instagram.fallback_caption') }}
                                </p>
                            @endif

                            <span class="site-instagram-card-cta">{{ ui_text('ui.instagram.open') }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif





