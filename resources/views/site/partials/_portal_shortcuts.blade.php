@php
    $items = collect($experienciasEntrada ?? [])->values();
    $sectionTitle = $title ?? ui_text('ui.home.entry_title');
    $sectionEditor = $editor ?? null;
@endphp

@if($items->isNotEmpty())
    <section class="site-section site-home-entry-section">
        <div class="site-section-head">
            <div class="site-section-head-copy">
                @if($sectionEditor)
                    @include('site.partials._content_editor', [
                        'editorTitle' => $sectionEditor['title'] ?? $sectionTitle,
                        'editorPage' => $sectionEditor['page'] ?? 'site.home',
                        'editorKey' => $sectionEditor['key'] ?? 'entry_section',
                        'editorLabel' => $sectionEditor['label'] ?? 'Título da seção de experiências',
                        'editorLocale' => route_locale(),
                        'editorTriggerVariant' => 'inline-compact',
                        'editorTriggerLabel' => 'Editar texto',
                        'editorFields' => ['titulo'],
                        'editableTranslation' => $sectionEditor['translation'] ?? null,
                        'editableStatus' => $sectionEditor['status'] ?? 'publicado',
                        'editableFallback' => [
                            'titulo' => $sectionTitle,
                        ],
                    ])
                @endif
                <h2 class="site-section-head-title">{{ $sectionTitle }}</h2>
            </div>
        </div>

        <div class="site-home-entry-grid">
            @foreach($items as $item)
                @php $imageSources = site_image_sources($item['image'] ?? null, 'card'); @endphp
                <div>
                    @if(!empty($item['editor']))
                        @include('site.partials._content_editor', [
                            'editorTitle' => $item['editor']['title'],
                            'editorPage' => $item['editor']['page'],
                            'editorKey' => $item['editor']['key'],
                            'editorLabel' => $item['editor']['label'],
                            'editorLocale' => route_locale(),
                            'editorTriggerVariant' => 'inline-compact',
                            'editorTriggerLabel' => 'Editar imagem',
                            'editableTranslation' => $item['editor']['translation'],
                            'editableMedia' => $item['editor']['media'],
                            'editableStatus' => $item['editor']['status'],
                            'editorMediaSlot' => 'card',
                            'editorMediaLabel' => 'Imagem do card',
                            'editorMediaPreviewLabel' => 'imagem do card atual',
                            'editorFields' => ['media'],
                            'editableFallback' => [
                                'titulo' => $item['title'] ?? 'Imagem do card',
                            ],
                        ])
                    @endif
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
                </div>
            @endforeach
        </div>
    </section>
@endif
