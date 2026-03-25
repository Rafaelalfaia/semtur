@props([
    'title',
    'subtitle' => null,
    'href' => null,
    'items' => collect(),
    'empty' => 'Sem itens publicados nesta secao.',
    'eyebrow' => null,
    'emptyTitle' => 'Nada por aqui ainda',
    'gridClass' => null,
    'cardVariant' => null,
    'layout' => 'grid',
])

<section {{ $attributes->class('site-section site-home-category-section') }}>
    <x-section-head :eyebrow="$eyebrow" :title="$title" :subtitle="$subtitle" :href="$href" />

    @if(collect($items)->isEmpty())
        <div class="site-empty-state">
            <p class="site-empty-state-title">{{ $emptyTitle }}</p>
            <p class="site-empty-state-copy">{{ $empty }}</p>
        </div>
    @else
        @if($layout === 'carousel')
            <div class="site-home-carousel-shell" x-data="{
                canPrev: false,
                canNext: true,
                update() {
                    const el = this.$refs.viewport;
                    if (!el) return;
                    this.canPrev = el.scrollLeft > 12;
                    this.canNext = (el.scrollWidth - el.clientWidth - el.scrollLeft) > 12;
                },
                move(direction) {
                    const el = this.$refs.viewport;
                    if (!el) return;
                    const step = Math.max(el.clientWidth * 0.78, 260);
                    el.scrollBy({ left: step * direction, behavior: 'smooth' });
                    window.setTimeout(() => this.update(), 220);
                }
            }" x-init="$nextTick(() => update())">
                <div class="site-home-carousel-controls" aria-hidden="true">
                    <button type="button" class="site-home-carousel-control" @click="move(-1)" :disabled="!canPrev" :aria-disabled="!canPrev">&larr;</button>
                    <button type="button" class="site-home-carousel-control" @click="move(1)" :disabled="!canNext" :aria-disabled="!canNext">&rarr;</button>
                </div>

                <div class="site-home-carousel-track" x-ref="viewport" @scroll.debounce.50ms="update()" x-on:resize.window.debounce.120ms="update()">
                    @foreach($items as $item)
                        <div class="site-home-carousel-slide">
                            <x-card-list
                                :title="$item['title']"
                                :subtitle="$item['subtitle'] ?? null"
                                :summary="$item['summary'] ?? null"
                                :image="$item['image'] ?? null"
                                :href="$item['href'] ?? '#'"
                                :badge="$item['badge'] ?? null"
                                :meta="$item['meta'] ?? null"
                                :cta="$item['cta'] ?? 'Ver mais'"
                                :variant="$cardVariant"
                            />
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div @class(['site-card-list-grid', 'site-home-category-grid', $gridClass])>
                @foreach($items as $item)
                    <x-card-list
                        :title="$item['title']"
                        :subtitle="$item['subtitle'] ?? null"
                        :summary="$item['summary'] ?? null"
                        :image="$item['image'] ?? null"
                        :href="$item['href'] ?? '#'"
                        :badge="$item['badge'] ?? null"
                        :meta="$item['meta'] ?? null"
                        :cta="$item['cta'] ?? 'Ver mais'"
                        :variant="$cardVariant"
                    />
                @endforeach
            </div>
        @endif
    @endif
</section>
