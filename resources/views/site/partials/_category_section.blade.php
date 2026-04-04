@props([
    'title',
    'subtitle' => null,
    'href' => null,
    'items' => collect(),
    'empty' => ui_text('ui.common.view_more'),
    'eyebrow' => null,
    'emptyTitle' => ui_text('ui.common.view_all'),
    'gridClass' => null,
    'cardVariant' => null,
    'layout' => 'grid',
    'editor' => null,
    'sectionActions' => [],
    'emptyEditor' => null,
])

<section {{ $attributes->class('site-section site-home-category-section') }}>
    @if(!empty($editor))
        @include('site.partials._content_editor', [
            'editorTitle' => $editor['title'] ?? $title,
            'editorPage' => $editor['page'] ?? 'site.page',
            'editorKey' => $editor['key'] ?? 'section',
            'editorLabel' => $editor['label'] ?? 'Seção editorial',
            'editorLocale' => $editor['locale'] ?? route_locale(),
            'editorTriggerVariant' => $editor['trigger_variant'] ?? 'inline-compact',
            'editorTriggerLabel' => $editor['trigger_label'] ?? 'Editar texto',
            'editorFields' => $editor['fields'] ?? ['eyebrow', 'titulo', 'lead'],
            'editableTranslation' => $editor['translation'] ?? null,
            'editableStatus' => $editor['status'] ?? 'publicado',
            'editableFallback' => $editor['fallback'] ?? [],
        ])
    @endif

    <x-section-head :eyebrow="$eyebrow" :title="$title" :subtitle="$subtitle" :href="$href" />

    @if(collect($sectionActions)->isNotEmpty())
        <div class="site-inline-actions">
            @foreach($sectionActions as $action)
                @if(!empty($action['href']) && !empty($action['label']))
                    <a href="{{ $action['href'] }}" class="{{ $action['class'] ?? 'site-button-secondary' }}">{{ $action['label'] }}</a>
                @endif
            @endforeach
        </div>
    @endif

    @if(collect($items)->isEmpty())
        <div class="site-empty-state">
            @if(!empty($emptyEditor))
                @include('site.partials._content_editor', [
                    'editorTitle' => $emptyEditor['title'] ?? $emptyTitle,
                    'editorPage' => $emptyEditor['page'] ?? 'site.page',
                    'editorKey' => $emptyEditor['key'] ?? 'empty_state',
                    'editorLabel' => $emptyEditor['label'] ?? 'Estado vazio',
                    'editorLocale' => $emptyEditor['locale'] ?? route_locale(),
                    'editorTriggerVariant' => $emptyEditor['trigger_variant'] ?? 'inline-compact',
                    'editorTriggerLabel' => $emptyEditor['trigger_label'] ?? 'Editar texto',
                    'editorFields' => $emptyEditor['fields'] ?? ['titulo', 'lead'],
                    'editableTranslation' => $emptyEditor['translation'] ?? null,
                    'editableStatus' => $emptyEditor['status'] ?? 'publicado',
                    'editableFallback' => $emptyEditor['fallback'] ?? [],
                ])
            @endif
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
                                :cta="$item['cta'] ?? ui_text('ui.common.view_more')"
                                :variant="$cardVariant"
                                :admin-action="$item['admin_action'] ?? null"
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
                        :cta="$item['cta'] ?? ui_text('ui.common.view_more')"
                        :variant="$cardVariant"
                        :admin-action="$item['admin_action'] ?? null"
                    />
                @endforeach
            </div>
        @endif
    @endif
</section>

