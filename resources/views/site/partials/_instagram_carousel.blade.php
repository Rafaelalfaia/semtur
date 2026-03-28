@php $items = collect($instagram ?? [])->values(); @endphp

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
        <x-section-head
            :eyebrow="__('ui.instagram.eyebrow')"
            title="@visitaltamira"
            href="https://www.instagram.com/visitaltamira/"
        />

        <div class="site-instagram-shell">
            <div class="site-instagram-headerline">
                <div class="site-instagram-profile">
                    <span class="site-badge">VisitAltamira</span>
                    <span class="site-instagram-profile-copy">{{ __('ui.instagram.profile_copy') }}</span>
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
                                    alt="{{ __('ui.instagram.post_alt') }}"
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
                            <span class="site-badge">{{ __('ui.instagram.badge') }}</span>
                            @if(!empty($post['caption']))
                                <p class="site-instagram-card-caption">
                                    {{ \Illuminate\Support\Str::limit(trim($post['caption']), 108) }}
                                </p>
                            @else
                                <p class="site-instagram-card-caption">
                                    {{ __('ui.instagram.fallback_caption') }}
                                </p>
                            @endif

                            <span class="site-instagram-card-cta">{{ __('ui.instagram.open') }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif






