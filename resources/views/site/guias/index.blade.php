@extends('site.layouts.app')

@section('title', 'Guias e Revistas • Visit Altamira')
@section('meta.description', 'Acesse guias e revistas oficiais para planejar sua visita, consultar materiais do destino e abrir conteudos diretamente dentro do portal.')
@section('meta.image', asset('imagens/altamira.jpg'))

@section('site.content')
@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    $tipoAtual = (string) ($tipo ?? '');
    $qAtual = (string) ($q ?? '');
    $totalMateriais = method_exists($materiais, 'total') ? $materiais->total() : $materiais->count();
    $agrupados = collect(method_exists($materiais, 'items') ? $materiais->items() : $materiais)->groupBy('tipo');
    $explorarUrl = localized_route('site.explorar');
    $homeUrl = localized_route('site.home');
    $heroMeta = array_filter([
    ]);
@endphp

<div class="site-page site-page-shell site-guides-page">
    @include('site.partials._page_hero', [
        'backHref' => $homeUrl,
        'breadcrumbs' => [
            ['label' => 'Inicio', 'href' => $homeUrl],
            ['label' => 'Guias e revistas'],
        ],
        'badge' => 'Materiais oficiais',
        'title' => 'Guias e revistas',
        'subtitle' => 'Materiais publicados para planejar a visita com mais clareza, contexto e leitura direta dentro do portal.',
        'meta' => $heroMeta,
        'primaryActionLabel' => 'Explorar materiais',
        'primaryActionHref' => '#lista-materiais',
        'secondaryActionLabel' => 'Ver mais opcoes',
        'secondaryActionHref' => $explorarUrl,
        'image' => asset('imagens/altamira.jpg'),
        'imageAlt' => 'Guias e revistas de Altamira',
        'compact' => true,
    ])

    <section class="site-section">
        <div class="site-surface-soft">
            <div class="site-app-toolbar site-app-toolbar--stacked">
                <div>
                    <p class="site-app-eyebrow">Filtros</p>
                    <h2 class="site-app-title">Encontre o material ideal</h2>
                    <p class="site-app-copy">Refine por tipo ou busca sem sair do padrao visual do portal.</p>
                </div>
            </div>

            @if(!empty($tipos))
                <div class="site-guides-filter-chips">
                    <a href="{{ localized_route('site.guias') }}" class="{{ $tipoAtual === '' ? 'site-year-chip is-active' : 'site-year-chip' }}">
                        Todos
                    </a>

                    @foreach(($tipos ?? []) as $tipoKey => $tipoLabel)
                        <a
                            href="{{ localized_route('site.guias', array_filter(['tipo' => $tipoKey, 'q' => $qAtual ?: null])) }}"
                            class="{{ $tipoAtual === $tipoKey ? 'site-year-chip is-active' : 'site-year-chip' }}"
                        >
                            {{ $tipoLabel }}
                        </a>
                    @endforeach
                </div>
            @endif

            <form method="GET" class="site-guides-filter-form">
                <div class="site-guides-filter-field">
                    <label class="site-guides-filter-label" for="guias-busca">Buscar</label>
                    <input
                        id="guias-busca"
                        type="text"
                        name="q"
                        value="{{ $qAtual }}"
                        placeholder="Ex.: visitante, mapa, revista..."
                        class="site-search-input"
                    >
                </div>

                <div class="site-guides-filter-field">
                    <label class="site-guides-filter-label" for="guias-tipo">Categoria</label>
                    <select id="guias-tipo" name="tipo" class="site-search-input site-search-select">
                        <option value="">Todos</option>
                        @foreach(($tipos ?? []) as $tipoKey => $tipoLabel)
                            <option value="{{ $tipoKey }}" @selected($tipoAtual === $tipoKey)>{{ $tipoLabel }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="site-guides-filter-actions">
                    <button type="submit" class="site-button-primary">Aplicar filtros</button>

                    @if($qAtual !== '' || $tipoAtual !== '')
                        <a href="{{ localized_route('site.guias') }}" class="site-button-secondary">Limpar</a>
                    @endif
                </div>
            </form>
        </div>
    </section>

    <section id="lista-materiais" class="site-section">
        @if($totalMateriais === 0)
            <div class="site-empty-state">
                <p class="site-empty-state-title">Nenhum material encontrado</p>
                <p class="site-empty-state-copy">Ajuste a busca ou volte mais tarde para conferir novos guias e revistas.</p>
            </div>
        @else
            <div class="site-guides-app-shell">
                @forelse($agrupados as $grupoTipo => $grupoItems)
                    <section class="site-guides-app-group">
                        <div class="site-guides-app-group-head">
                            <div>
                                <p class="site-app-eyebrow">Biblioteca</p>
                                <h2 class="site-guides-app-group-title">{{ ($tipos[$grupoTipo] ?? ucfirst($grupoTipo)) }}</h2>
                            </div>
                            <span class="site-guides-app-group-count">{{ count($grupoItems) }}</span>
                        </div>

                        <div class="site-directory-grid">
                            @foreach($grupoItems as $material)
                                @php
                                    $cover = $material->capa_url ?: asset('imagens/altamira.jpg');
                                @endphp

                                <article class="site-directory-card">
                                    <div class="site-directory-card-media">
                                        <img
                                            src="{{ $cover }}"
                                            alt="{{ $material->nome }}"
                                            class="site-directory-card-image"
                                            loading="lazy"
                                            decoding="async"
                                        >

                                        <div class="site-directory-card-overlay">
                                            <span class="site-badge">{{ $material->tipo_label }}</span>
                                        </div>
                                    </div>

                                    <div class="site-directory-card-body">
                                        <div>
                                            <h3 class="site-directory-card-title">{{ $material->nome }}</h3>
                                            <p class="site-inline-meta">Material oficial com leitura e consulta dentro do portal.</p>
                                        </div>

                                        <p class="site-directory-card-summary">
                                            {{ Str::limit(strip_tags((string) $material->descricao), 140) }}
                                        </p>

                                        <div class="site-directory-card-actions">
                                            <a href="{{ localized_route('site.guias.show', ['slug' => $material->slug]) }}" class="site-button-primary">Abrir material</a>

                                            @if(filled($material->link_acesso))
                                                <a
                                                    href="{{ $material->link_acesso }}"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="site-button-secondary"
                                                >
                                                    Google Drive
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @empty
                    <div class="site-empty-state">
                        <p class="site-empty-state-title">Nenhum material encontrado</p>
                        <p class="site-empty-state-copy">Ajuste a busca ou volte mais tarde para conferir novos materiais.</p>
                    </div>
                @endforelse
            </div>

            @if(method_exists($materiais, 'links'))
                <div class="site-surface-soft">
                    {{ $materiais->links() }}
                </div>
            @endif
        @endif
    </section>
</div>
@endsection
