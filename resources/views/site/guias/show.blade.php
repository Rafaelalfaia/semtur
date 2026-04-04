@extends('site.layouts.app')

@section('title', $material->nome . ' • ' . $material->tipo_label . ' • VisitAltamira')
@section('meta.description', \Illuminate\Support\Str::limit(strip_tags((string) $material->descricao), 160))
@section('meta.image', $material->capa_url ?: asset('imagens/altamira.jpg'))

@section('site.content')
@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    $pageBlocks = $pageBlocks ?? collect();
    $guideShowBlocks = [
        'hero' => $pageBlocks->get('hero'),
        'about_section' => $pageBlocks->get('about_section'),
        'reading_section' => $pageBlocks->get('reading_section'),
        'preview_empty_state' => $pageBlocks->get('preview_empty_state'),
        'overview_section' => $pageBlocks->get('overview_section'),
        'related_section' => $pageBlocks->get('related_section'),
    ];
    $guideShowTranslation = fn (string $key) => $guideShowBlocks[$key]?->getAttribute('traducao_resolvida');
    $aboutTranslation = $guideShowTranslation('about_section');
    $readingTranslation = $guideShowTranslation('reading_section');
    $previewEmptyTranslation = $guideShowTranslation('preview_empty_state');
    $overviewTranslation = $guideShowTranslation('overview_section');
    $relatedTranslation = $guideShowTranslation('related_section');

    $cover = $heroMedia?->url ?: ($material->capa_url ?: asset('imagens/altamira.jpg'));
    $embedUrl = $material->embed_url;
    $guiasUrl = Route::has('site.guias') ? localized_route('site.guias') : '#';
    $homeUrl = localized_route('site.home');
    $relatedItems = collect($relacionados ?? []);
    $canManageGuide = auth()->check() && auth()->user()->can('guias.update');
    $editGuideHref = $canManageGuide && Route::has('coordenador.guias.edit')
        ? route('coordenador.guias.edit', $material)
        : null;
    $heroBadge = $heroTranslation?->eyebrow ?: $material->tipo_label;
    $heroTitle = $heroTranslation?->titulo ?: $material->nome;
    $heroSubtitle = $heroTranslation?->lead ?: Str::limit(strip_tags((string) $material->descricao), 180);
    $heroPrimaryLabel = $heroTranslation?->cta_label ?: ui_text('ui.guides.read_material');
    $heroPrimaryHref = $heroTranslation?->cta_href ?: '#leitura';

    $aboutEyebrow = $aboutTranslation?->eyebrow ?: ui_text('ui.guides.about_material');
    $aboutTitle = $aboutTranslation?->titulo ?: ui_text('ui.guides.material_information', ['type' => Str::lower($material->tipo_label)]);
    $aboutSubtitle = $aboutTranslation?->lead ?: ui_text('ui.guides.editorial_summary');

    $readingEyebrow = $readingTranslation?->eyebrow ?: ui_text('ui.guides.reading');
    $readingTitle = $readingTranslation?->titulo ?: ui_text('ui.guides.material_viewing');
    $readingSubtitle = $readingTranslation?->lead ?: ui_text('ui.guides.embedded_copy');

    $previewEmptyTitle = $previewEmptyTranslation?->titulo ?: ui_text('ui.guides.preview_unavailable');
    $previewEmptyCopy = $previewEmptyTranslation?->lead ?: ui_text('ui.guides.preview_unavailable_copy');

    $overviewEyebrow = $overviewTranslation?->eyebrow ?: ui_text('ui.common.summary');
    $overviewTitle = $overviewTranslation?->titulo ?: ui_text('ui.guides.overview');

    $relatedEyebrow = $relatedTranslation?->eyebrow ?: ui_text('ui.guides.related');
    $relatedTitle = $relatedTranslation?->titulo ?: ui_text('ui.guides.more_materials', ['type' => Str::plural(Str::lower($material->tipo_label), 2)]);
@endphp

<div class="site-page site-page-shell site-guide-detail-page">
    @include('site.partials._page_hero', [
        'backHref' => $guiasUrl,
        'breadcrumbs' => [
            ['label' => ui_text('ui.common.home'), 'href' => $homeUrl],
            ['label' => ui_text('ui.guides.index_title'), 'href' => $guiasUrl],
            ['label' => $heroTitle],
        ],
        'badge' => $heroBadge,
        'title' => $heroTitle,
        'subtitle' => $heroSubtitle,
        'meta' => [],
        'primaryActionLabel' => $heroPrimaryLabel,
        'primaryActionHref' => $heroPrimaryHref,
        'secondaryActionLabel' => ui_text('ui.common.back_to_guides'),
        'secondaryActionHref' => $guiasUrl,
        'image' => $cover,
        'imageAlt' => $heroTitle,
        'compact' => true,
        'textEditor' => [
            'title' => $heroTitle,
            'page' => 'site.guias.show',
            'key' => 'hero',
            'label' => 'Texto da capa do detalhe de guia',
            'locale' => route_locale(),
            'trigger_label' => 'Editar texto',
            'fields' => ['eyebrow', 'titulo', 'lead', 'cta_label', 'cta_href', 'seo_title', 'seo_description'],
            'translation' => $heroTranslation ?? null,
            'status' => $heroBlock?->status ?? 'publicado',
        ],
        'imageEditor' => [
            'title' => $heroTitle,
            'page' => 'site.guias.show',
            'key' => 'hero',
            'label' => 'Imagem da capa do detalhe de guia',
            'locale' => route_locale(),
            'trigger_label' => 'Editar imagem',
            'translation' => $heroTranslation ?? null,
            'media' => $heroMedia ?? null,
            'status' => $heroBlock?->status ?? 'publicado',
            'media_slot' => 'hero',
            'media_label' => 'Imagem da capa',
            'preview_label' => 'imagem atual da capa',
        ],
    ])

    <section class="site-section">
        <div class="site-editorial-layout">
            <div class="site-editorial-main">
                <section class="site-surface site-content-block">
                    @if($editGuideHref)
                        <div class="site-inline-actions">
                            <a href="{{ $editGuideHref }}" class="site-button-secondary">Editar material</a>
                        </div>
                    @endif

                    @include('site.partials._content_editor', [
                        'editorTitle' => $aboutTitle,
                        'editorPage' => 'site.guias.show',
                        'editorKey' => 'about_section',
                        'editorLabel' => 'Seção sobre o material',
                        'editorLocale' => route_locale(),
                        'editorTriggerVariant' => 'inline-compact',
                        'editorTriggerLabel' => 'Editar texto',
                        'editorFields' => ['eyebrow', 'titulo', 'lead'],
                        'editableTranslation' => $aboutTranslation,
                        'editableStatus' => $guideShowBlocks['about_section']?->status ?? 'publicado',
                        'editableFallback' => [
                            'eyebrow' => ui_text('ui.guides.about_material'),
                            'titulo' => ui_text('ui.guides.material_information', ['type' => Str::lower($material->tipo_label)]),
                            'lead' => ui_text('ui.guides.editorial_summary'),
                        ],
                    ])
                    <x-section-head
                        :eyebrow="$aboutEyebrow"
                        :title="$aboutTitle"
                        :subtitle="$aboutSubtitle"
                    />

                    <div class="site-prose">
                        {!! nl2br(e($material->descricao)) !!}
                    </div>
                </section>

                <section id="leitura" class="site-surface site-content-block">
                    @include('site.partials._content_editor', [
                        'editorTitle' => $readingTitle,
                        'editorPage' => 'site.guias.show',
                        'editorKey' => 'reading_section',
                        'editorLabel' => 'Seção de leitura do guia',
                        'editorLocale' => route_locale(),
                        'editorTriggerVariant' => 'inline-compact',
                        'editorTriggerLabel' => 'Editar texto',
                        'editorFields' => ['eyebrow', 'titulo', 'lead'],
                        'editableTranslation' => $readingTranslation,
                        'editableStatus' => $guideShowBlocks['reading_section']?->status ?? 'publicado',
                        'editableFallback' => [
                            'eyebrow' => ui_text('ui.guides.reading'),
                            'titulo' => ui_text('ui.guides.material_viewing'),
                            'lead' => ui_text('ui.guides.embedded_copy'),
                        ],
                    ])
                    <x-section-head
                        :eyebrow="$readingEyebrow"
                        :title="$readingTitle"
                        :subtitle="$readingSubtitle"
                    />

                    <div class="site-inline-actions">
                        @if(filled($material->link_acesso))
                            <a
                                href="{{ $material->link_acesso }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="site-button-secondary"
                            >
                                {{ ui_text('ui.guides.open_drive') }}
                            </a>
                        @endif
                    </div>

                    @if($embedUrl)
                        <div class="overflow-hidden rounded-[24px] border border-slate-200 bg-white">
                            <iframe
                                src="{{ $embedUrl }}"
                                title="{{ $material->nome }}"
                                class="h-[75vh] min-h-[620px] w-full"
                                loading="lazy"
                                referrerpolicy="strict-origin-when-cross-origin"
                                allowfullscreen
                            ></iframe>
                        </div>

                        <p class="site-empty-state-copy">
                            {{ ui_text('ui.guides.viewer_fallback') }}
                        </p>
                    @else
                        <div class="site-empty-state">
                            @include('site.partials._content_editor', [
                                'editorTitle' => $previewEmptyTitle,
                                'editorPage' => 'site.guias.show',
                                'editorKey' => 'preview_empty_state',
                                'editorLabel' => 'Estado vazio da leitura de guia',
                                'editorLocale' => route_locale(),
                                'editorTriggerVariant' => 'inline-compact',
                                'editorTriggerLabel' => 'Editar texto',
                                'editorFields' => ['titulo', 'lead'],
                                'editableTranslation' => $previewEmptyTranslation,
                                'editableStatus' => $guideShowBlocks['preview_empty_state']?->status ?? 'publicado',
                                'editableFallback' => [
                                    'titulo' => ui_text('ui.guides.preview_unavailable'),
                                    'lead' => ui_text('ui.guides.preview_unavailable_copy'),
                                ],
                            ])
                            <p class="site-empty-state-title">{{ $previewEmptyTitle }}</p>
                            <p class="site-empty-state-copy">{{ $previewEmptyCopy }}</p>

                            @if(filled($material->link_acesso))
                                <a
                                    href="{{ $material->link_acesso }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="site-button-primary"
                                >
                                    {{ ui_text('ui.guides.open_material') }}
                                </a>
                            @endif
                        </div>
                    @endif
                </section>
            </div>

            <aside class="site-editorial-aside">
                <section class="site-surface-soft site-content-block">
                    @include('site.partials._content_editor', [
                        'editorTitle' => $overviewTitle,
                        'editorPage' => 'site.guias.show',
                        'editorKey' => 'overview_section',
                        'editorLabel' => 'Resumo do detalhe de guia',
                        'editorLocale' => route_locale(),
                        'editorTriggerVariant' => 'inline-compact',
                        'editorTriggerLabel' => 'Editar texto',
                        'editorFields' => ['eyebrow', 'titulo'],
                        'editableTranslation' => $overviewTranslation,
                        'editableStatus' => $guideShowBlocks['overview_section']?->status ?? 'publicado',
                        'editableFallback' => [
                            'eyebrow' => ui_text('ui.common.summary'),
                            'titulo' => ui_text('ui.guides.overview'),
                        ],
                    ])
                    <x-section-head :eyebrow="$overviewEyebrow" :title="$overviewTitle" />

                    <div class="site-stats-grid">
                        <div class="site-stat-card">
                            <span class="site-stat-label">{{ ui_text('ui.guides.type') }}</span>
                            <span class="site-stat-value">{{ $material->tipo_label }}</span>
                        </div>
                    </div>

                    <div class="site-inline-actions">
                        <a href="{{ $guiasUrl }}" class="site-button-secondary">{{ ui_text('ui.guides.view_more_materials') }}</a>
                    </div>
                </section>

                @if($relatedItems->isNotEmpty())
                    <section class="site-surface-soft site-content-block">
                        @include('site.partials._content_editor', [
                            'editorTitle' => $relatedTitle,
                            'editorPage' => 'site.guias.show',
                            'editorKey' => 'related_section',
                            'editorLabel' => 'Seção de materiais relacionados',
                            'editorLocale' => route_locale(),
                            'editorTriggerVariant' => 'inline-compact',
                            'editorTriggerLabel' => 'Editar texto',
                            'editorFields' => ['eyebrow', 'titulo'],
                            'editableTranslation' => $relatedTranslation,
                            'editableStatus' => $guideShowBlocks['related_section']?->status ?? 'publicado',
                            'editableFallback' => [
                                'eyebrow' => ui_text('ui.guides.related'),
                                'titulo' => ui_text('ui.guides.more_materials', ['type' => Str::plural(Str::lower($material->tipo_label), 2)]),
                            ],
                        ])
                        <x-section-head
                            :eyebrow="$relatedEyebrow"
                            :title="$relatedTitle"
                        />

                        <div class="space-y-4">
                            @foreach($relatedItems as $rel)
                                <a
                                    href="{{ localized_route('site.guias.show', ['slug' => $rel->slug]) }}"
                                    class="block rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:border-emerald-300 hover:bg-emerald-50"
                                >
                                    <div class="text-xs font-semibold uppercase tracking-[0.16em] text-emerald-700">
                                        {{ $rel->tipo_label }}
                                    </div>
                                    <div class="mt-2 text-sm font-semibold text-slate-900">
                                        {{ $rel->nome }}
                                    </div>
                                    <p class="mt-2 text-sm leading-6 text-slate-600">
                                        {{ Str::limit(strip_tags((string) $rel->descricao), 90) }}
                                    </p>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif
            </aside>
        </div>
    </section>
</div>
@endsection
