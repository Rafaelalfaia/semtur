@extends('site.layouts.app')

@section('title', $heroTranslation?->seo_title ?: ($heroTranslation?->titulo ?: 'Museus de Altamira'))
@section('meta.description', $heroTranslation?->seo_description ?: ($heroTranslation?->lead ?: 'Explore os museus de Altamira, com informações práticas, horários e acesso rápido para visitação.'))

@section('site.content')
@php
    use App\Models\Catalogo\EspacoCultural;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    $pageBlocks = $pageBlocks ?? collect();
    $museuBlocks = [
        'hero' => $pageBlocks->get('hero'),
        'filter_section' => $pageBlocks->get('filter_section'),
        'listing_section' => $pageBlocks->get('listing_section'),
        'empty_state' => $pageBlocks->get('empty_state'),
    ];
    $museuTranslation = fn (string $key) => $museuBlocks[$key]?->getAttribute('traducao_resolvida');
    $filterTranslation = $museuTranslation('filter_section');
    $listingTranslation = $museuTranslation('listing_section');
    $emptyTranslation = $museuTranslation('empty_state');

    $fallback = asset('imagens/altamira.jpg');
    $heroBadge = $heroTranslation?->eyebrow ?: 'Museus';
    $heroTitle = $heroTranslation?->titulo ?: 'Museus de Altamira';
    $heroSubtitle = $heroTranslation?->lead ?: 'Conheça os espaços museológicos da cidade com leitura simples, horários e acesso rápido para planejar a visita.';

    $filterEyebrow = $filterTranslation?->eyebrow ?: 'Busca';
    $filterTitle = $filterTranslation?->titulo ?: 'Encontre um museu';
    $filterSubtitle = $filterTranslation?->lead ?: 'Pesquise pelo nome, bairro ou cidade para localizar o espaço ideal.';
    $listingEyebrow = $listingTranslation?->eyebrow ?: 'Museus publicados';
    $listingTitle = $listingTranslation?->titulo ?: 'Museus abertos para visitação';
    $listingSubtitle = $listingTranslation?->lead ?: 'Coleção pública de museus com horários, resumo e acesso rápido para detalhes.';
    $emptyTitle = $emptyTranslation?->titulo ?: 'Nenhum museu encontrado';
    $emptyCopy = $emptyTranslation?->lead ?: 'Ajuste a busca ou aguarde novas publicações de museus no portal.';

    $cards = $espacos->getCollection()->map(function ($espaco) use ($fallback) {
        $capa = $espaco->capa_url ?: optional($espaco->midias->first())->url ?: $fallback;

        return [
            'model' => $espaco,
            'image' => $capa,
            'summary' => $espaco->resumo
                ? $espaco->resumo
                : Str::limit(strip_tags((string) $espaco->descricao), 140),
            'horarios' => $espaco->horarios->take(3),
        ];
    });

    $heroPrimaryLabel = $heroTranslation?->cta_label ?: ($cards->first() && $cards->first()['model']->agendamento_disponivel ? 'Solicitar visita' : null);
    $heroPrimaryHref = $heroTranslation?->cta_href ?: ($cards->first() && $cards->first()['model']->agendamento_disponivel ? localized_route('site.museus.agendar', ['espaco' => $cards->first()['model']->slug]) : null);
    $canManageMuseus = auth()->check() && auth()->user()->can('espacos_culturais.update');
@endphp

<div class="site-page site-page-shell site-espacos-page">
    @include('site.partials._page_hero', [
        'backHref' => localized_route('site.home'),
        'breadcrumbs' => [
            ['label' => ui_text('ui.common.home'), 'href' => localized_route('site.home')],
            ['label' => 'Museus'],
        ],
        'badge' => $heroBadge,
        'title' => $heroTitle,
        'subtitle' => $heroSubtitle,
        'meta' => [
            $espacos->total().' museus publicados',
            filled($q) ? 'Busca ativa' : null,
        ],
        'primaryActionLabel' => $heroPrimaryLabel,
        'primaryActionHref' => $heroPrimaryHref,
        'secondaryActionLabel' => 'Ver museus',
        'secondaryActionHref' => '#espacos-lista',
        'image' => $heroMedia?->url ?: ($cards->first()['image'] ?? $fallback),
        'imageAlt' => 'Museus de Altamira',
        'compact' => true,
        'textEditor' => [
            'title' => $heroTitle,
            'page' => 'site.museus',
            'key' => 'hero',
            'label' => 'Texto da capa de museus',
            'locale' => route_locale(),
            'trigger_label' => 'Editar texto',
            'fields' => ['eyebrow', 'titulo', 'lead', 'cta_label', 'cta_href'],
            'translation' => $heroTranslation ?? null,
            'status' => $heroBlock?->status ?? 'publicado',
        ],
        'imageEditor' => [
            'title' => $heroTitle,
            'page' => 'site.museus',
            'key' => 'hero',
            'label' => 'Imagem da capa de museus',
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
        <div class="site-surface site-espacos-filter-shell">
            @include('site.partials._content_editor', [
                'editorTitle' => $filterTitle,
                'editorPage' => 'site.museus',
                'editorKey' => 'filter_section',
                'editorLabel' => 'Seção de busca dos museus',
                'editorLocale' => route_locale(),
                'editorTriggerVariant' => 'inline-compact',
                'editorTriggerLabel' => 'Editar texto',
                'editorFields' => ['eyebrow', 'titulo', 'lead'],
                'editableTranslation' => $filterTranslation,
                'editableStatus' => $museuBlocks['filter_section']?->status ?? 'publicado',
                'editableFallback' => [
                    'eyebrow' => 'Busca',
                    'titulo' => 'Encontre um museu',
                    'lead' => 'Pesquise pelo nome, bairro ou cidade para localizar o espaço ideal.',
                ],
            ])

            <x-section-head
                :eyebrow="$filterEyebrow"
                :title="$filterTitle"
                :subtitle="$filterSubtitle"
            />

            <form method="GET" class="site-search-form site-espacos-search-form">
                <input
                    type="text"
                    name="q"
                    value="{{ $q }}"
                    placeholder="Buscar museu por nome, bairro ou cidade"
                    class="w-full rounded-[var(--ui-radius-control)] border border-[var(--ui-border)] bg-[var(--ui-surface-raised)] px-4 py-3 text-sm text-[var(--ui-text)] outline-none focus:border-[var(--ui-primary)] focus:ring-4 focus:ring-[var(--ui-border-focus)]"
                >

                <input type="hidden" name="tipo" value="{{ EspacoCultural::TIPO_MUSEU }}">

                <button type="submit" class="site-button-primary">Aplicar</button>
            </form>
        </div>
    </section>

    <section class="site-section" id="espacos-lista">
        @include('site.partials._content_editor', [
            'editorTitle' => $listingTitle,
            'editorPage' => 'site.museus',
            'editorKey' => 'listing_section',
            'editorLabel' => 'Seção de listagem dos museus',
            'editorLocale' => route_locale(),
            'editorTriggerVariant' => 'inline-compact',
            'editorTriggerLabel' => 'Editar texto',
            'editorFields' => ['eyebrow', 'titulo', 'lead'],
            'editableTranslation' => $listingTranslation,
            'editableStatus' => $museuBlocks['listing_section']?->status ?? 'publicado',
            'editableFallback' => [
                'eyebrow' => 'Museus publicados',
                'titulo' => 'Museus abertos para visitação',
                'lead' => 'Coleção pública de museus com horários, resumo e acesso rápido para detalhes.',
            ],
        ])

        <x-section-head
            :eyebrow="$listingEyebrow"
            :title="$listingTitle"
            :subtitle="$listingSubtitle"
        />

        @if($cards->isEmpty())
            <div class="site-empty-state">
                @include('site.partials._content_editor', [
                    'editorTitle' => $emptyTitle,
                    'editorPage' => 'site.museus',
                    'editorKey' => 'empty_state',
                    'editorLabel' => 'Estado vazio dos museus',
                    'editorLocale' => route_locale(),
                    'editorTriggerVariant' => 'inline-compact',
                    'editorTriggerLabel' => 'Editar texto',
                    'editorFields' => ['titulo', 'lead'],
                    'editableTranslation' => $emptyTranslation,
                    'editableStatus' => $museuBlocks['empty_state']?->status ?? 'publicado',
                    'editableFallback' => [
                        'titulo' => 'Nenhum museu encontrado',
                        'lead' => 'Ajuste a busca ou aguarde novas publicações de museus no portal.',
                    ],
                ])
                <p class="site-empty-state-title">{{ $emptyTitle }}</p>
                <p class="site-empty-state-copy">{{ $emptyCopy }}</p>
            </div>
        @else
            <div class="site-espacos-grid">
                @foreach($cards as $item)
                    @php($espaco = $item['model'])

                    <article class="site-surface site-espacos-card">
                        @if($canManageMuseus && Route::has('coordenador.espacos-culturais.edit'))
                            <div class="site-inline-actions">
                                <a href="{{ route('coordenador.espacos-culturais.edit', $espaco) }}" class="site-button-secondary">Editar museu</a>
                            </div>
                        @endif

                        <a href="{{ localized_route('site.museus.show', ['slug' => $espaco->slug]) }}" class="site-espacos-card-media">
                            <img src="{{ site_image_url($item['image'], 'card') }}" alt="{{ $espaco->nome }}" class="site-espacos-card-image" loading="lazy" decoding="async">
                        </a>

                        <div class="site-espacos-card-body">
                            <div class="site-espacos-card-head">
                                <div class="site-espacos-card-copy">
                                    <div class="site-espacos-card-badges">
                                        <span class="site-badge">Museu</span>
                                        @if($espaco->agendamento_disponivel)
                                            <span class="site-filter-chip is-active">Agendamento disponível</span>
                                        @endif
                                    </div>

                                    <h3 class="site-espacos-card-title">
                                        <a href="{{ localized_route('site.museus.show', ['slug' => $espaco->slug]) }}">{{ $espaco->nome }}</a>
                                    </h3>

                                    <div class="site-card-list-meta">
                                        @if($espaco->bairro)
                                            <span>{{ $espaco->bairro }}</span>
                                        @endif
                                        <span>{{ $espaco->cidade ?: ui_text('ui.common.altamira') }}</span>
                                    </div>
                                </div>
                            </div>

                            @if($item['summary'])
                                <p class="site-card-list-summary">{{ $item['summary'] }}</p>
                            @endif

                            @if($item['horarios']->isNotEmpty())
                                <div class="site-espacos-schedule-list">
                                    @foreach($item['horarios'] as $horario)
                                        <div class="site-espacos-schedule-item">
                                            <span class="site-espacos-schedule-day">{{ $horario->dia_label }}</span>
                                            <span class="site-espacos-schedule-time">{{ $horario->faixa_label }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <div class="site-inline-actions site-espacos-card-actions">
                                <a href="{{ localized_route('site.museus.show', ['slug' => $espaco->slug]) }}" class="site-button-secondary">Ver museu</a>
                                @if($espaco->agendamento_disponivel)
                                    <a href="{{ localized_route('site.museus.agendar', ['espaco' => $espaco->slug]) }}" class="site-button-primary">Agendar visita</a>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            @if ($espacos->hasPages())
                <div class="site-surface-soft site-espacos-pagination-shell">
                    {{ $espacos->links() }}
                </div>
            @endif
        @endif
    </section>
</div>
@endsection
