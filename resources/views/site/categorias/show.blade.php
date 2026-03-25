@extends('site.layouts.app')
@section('title', $categoria->nome . ' em Altamira')
@section('meta.description', 'Explore pontos e empresas publicados da categoria ' . $categoria->nome . ' no portal VisitAltamira.')
@section('meta.image', theme_asset('hero_image'))
@section('meta.canonical', url()->full())

@section('site.content')
@php
    use Illuminate\Support\Facades\Route as R;
    use Illuminate\Support\Facades\Storage;

    $breadcrumbs = [
        ['label' => 'Inicio', 'href' => R::has('site.home') ? route('site.home') : url('/')],
        ['label' => 'Explorar', 'href' => R::has('site.explorar') ? route('site.explorar') : '#'],
        ['label' => $categoria->nome],
    ];

    $cardsFromPontos = $pontos->map(function ($ponto) {
        $image = $ponto->capa_url ?? $ponto->foto_capa_url ?? optional($ponto->midias->first())->url ?? null;

        return [
            'title' => $ponto->nome,
            'subtitle' => $ponto->cidade ?? 'Altamira',
            'summary' => \Illuminate\Support\Str::limit(strip_tags((string) $ponto->descricao), 125),
            'image' => $image,
            'href' => route('site.ponto', $ponto->id),
            'badge' => 'Ponto turistico',
            'cta' => 'Ver lugar',
        ];
    });

    $cardsFromEmpresas = $empresas->map(function ($empresa) {
        $image = $empresa->capa_url ?? $empresa->foto_capa_url ?? null;

        return [
            'title' => $empresa->nome,
            'subtitle' => $empresa->cidade ?? 'Altamira',
            'summary' => \Illuminate\Support\Str::limit(strip_tags((string) $empresa->descricao), 125),
            'image' => $image,
            'href' => route('site.empresa', $empresa->slug ?? $empresa->id),
            'badge' => 'Empresa',
            'cta' => 'Ver empresa',
        ];
    });
@endphp

<div class="site-page site-page-shell">
    @include('site.partials._page_hero', [
        'backHref' => R::has('site.explorar') ? route('site.explorar') : url()->previous(),
        'breadcrumbs' => $breadcrumbs,
        'badge' => 'Categoria',
        'title' => $categoria->nome,
        'subtitle' => 'Selecao editorial com pontos e empresas publicadas desta categoria.',
        'meta' => [
            $pontos->total().' pontos',
            $empresas->total().' empresas',
            filled($q) ? 'Busca: '.$q : null,
        ],
        'primaryActionLabel' => 'Explorar tudo',
        'primaryActionHref' => R::has('site.explorar') ? route('site.explorar', ['categoria' => $categoria->slug]) : '#',
        'secondaryActionLabel' => 'Mapa turistico',
        'secondaryActionHref' => R::has('site.mapa') ? route('site.mapa') : '#',
        'image' => theme_asset('hero_image'),
        'imageAlt' => $categoria->nome,
        'compact' => true,
    ])

    <section class="site-section">
        <div class="site-surface">
            <x-section-head
                eyebrow="Navegacao"
                title="Refine o que voce quer descobrir"
                subtitle="Use a busca para filtrar os conteudos publicados desta categoria sem sair do contexto da pagina."
            />

            <form method="get" class="site-search-form">
                <input
                    type="text"
                    name="q"
                    value="{{ $q }}"
                    placeholder="Buscar nesta categoria"
                    class="w-full rounded-[var(--ui-radius-control)] border border-[var(--ui-border)] bg-[var(--ui-surface-raised)] px-4 py-3 text-sm text-[var(--ui-text)] outline-none focus:border-[var(--ui-primary)] focus:ring-4 focus:ring-[var(--ui-border-focus)]"
                >
                <button type="submit" class="site-button-primary">Aplicar busca</button>
            </form>
        </div>
    </section>

    <section class="site-section">
        <x-section-head
            eyebrow="Pontos"
            title="Lugares desta categoria"
            subtitle="Uma selecao de pontos publicados para montar seu roteiro com mais contexto."
        />

        @if($cardsFromPontos->isEmpty())
            <div class="site-empty-state">
                <p class="site-empty-state-copy">Nenhum ponto publicado nesta categoria no momento.</p>
            </div>
        @else
            <div class="site-card-list-grid">
                @foreach($cardsFromPontos as $item)
                    <x-card-list
                        :title="$item['title']"
                        :subtitle="$item['subtitle']"
                        :summary="$item['summary']"
                        :image="$item['image']"
                        :href="$item['href']"
                        :badge="$item['badge']"
                        :cta="$item['cta']"
                    />
                @endforeach
            </div>
        @endif

        @if ($pontos->hasPages())
            <div class="site-surface-soft">
                {{ $pontos->appends(['q' => $q, 'tab' => 'pontos'])->onEachSide(1)->links() }}
            </div>
        @endif
    </section>

    <section class="site-section">
        <x-section-head
            eyebrow="Empresas"
            title="Empresas relacionadas"
            subtitle="Servicos e operacoes publicadas que ajudam a transformar a categoria em experiencia."
        />

        @if($cardsFromEmpresas->isEmpty())
            <div class="site-empty-state">
                <p class="site-empty-state-copy">Nenhuma empresa publicada nesta categoria no momento.</p>
            </div>
        @else
            <div class="site-card-list-grid">
                @foreach($cardsFromEmpresas as $item)
                    <x-card-list
                        :title="$item['title']"
                        :subtitle="$item['subtitle']"
                        :summary="$item['summary']"
                        :image="$item['image']"
                        :href="$item['href']"
                        :badge="$item['badge']"
                        :cta="$item['cta']"
                    />
                @endforeach
            </div>
        @endif

        @if ($empresas->hasPages())
            <div class="site-surface-soft">
                {{ $empresas->appends(['q' => $q, 'tab' => 'empresas'])->onEachSide(1)->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
