@extends('site.layouts.app')

@section('title', ($pagina->seo_title ?: $pagina->titulo) . ' - Visit Altamira')
@section('meta.description', \Illuminate\Support\Str::limit(strip_tags((string) ($pagina->seo_description ?: $pagina->resumo ?: $pagina->titulo)), 160))
@section('meta.image', $pagina->hero_url ?: asset('imagens/altamira.jpg'))

@section('site.content')
@php
    use Illuminate\Support\Facades\Route;

    $hero = $pagina->hero_url ?: asset('imagens/altamira.jpg');
    $empresas = collect($pagina->empresasSelecionadas ?? [])->filter(fn ($item) => $item && $item->empresa)->values();
    $explorarUrl = Route::has('site.explorar') ? route('site.explorar') : '#';
@endphp

<div class="site-page site-page-shell">
    @include('site.partials._page_hero', [
        'backHref' => $explorarUrl,
        'breadcrumbs' => [
            ['label' => 'Inicio', 'href' => Route::has('site.home') ? route('site.home') : url('/')],
            ['label' => 'Explorar', 'href' => $explorarUrl],
            ['label' => $pagina->titulo ?: 'Onde ficar'],
        ],
        'badge' => $pagina->subtitulo ?: 'Hospedagem e conforto',
        'title' => $pagina->titulo ?: 'Onde ficar em Altamira',
        'subtitle' => $pagina->resumo ?: 'Curadoria editorial para estadias com mais conforto, contexto e boa experiencia na viagem.',
        'meta' => [
            $empresas->count().' hospedagens',
            'Curadoria oficial',
        ],
        'primaryActionLabel' => 'Ver hospedagens',
        'primaryActionHref' => '#hospedagens',
        'secondaryActionLabel' => 'Ver mais lugares',
        'secondaryActionHref' => $explorarUrl,
        'image' => $hero,
        'imageAlt' => $pagina->titulo ?: 'Onde ficar em Altamira',
    ])

    <section class="site-section">
        <div class="site-editorial-layout">
            <div class="site-editorial-main">
                @if(filled($pagina->texto_intro))
                    <section class="site-surface site-content-block">
                        <x-section-head eyebrow="Introducao" title="Estadia para diferentes perfis de viagem" />
                        <div class="site-prose">{!! nl2br(e($pagina->texto_intro)) !!}</div>
                    </section>
                @endif

                @if(filled($pagina->texto_hospedagem_local))
                    <section class="site-surface-soft site-content-block">
                        <x-section-head eyebrow="Hospedagem local" title="Onde ficar em Altamira com mais praticidade" />
                        <div class="site-prose">{!! nl2br(e($pagina->texto_hospedagem_local)) !!}</div>
                    </section>
                @endif
            </div>

            <aside class="site-editorial-aside">
                <section class="site-surface-soft site-content-block">
                    <x-section-head eyebrow="Resumo" title="Visao geral" />
                    <div class="site-stats-grid">
                        <div class="site-stat-card">
                            <span class="site-stat-label">Hospedagens</span>
                            <span class="site-stat-value">{{ $empresas->count() }}</span>
                        </div>
                        <div class="site-stat-card">
                            <span class="site-stat-label">Perfil</span>
                            <span class="site-stat-value">Estadia</span>
                        </div>
                    </div>
                </section>
            </aside>
        </div>
    </section>

    <section id="hospedagens" class="site-section">
        <x-section-head
            eyebrow="Selecao editorial"
            title="Hospedagens recomendadas"
            subtitle="Lugares publicados para quem busca localizacao, conforto e experiencia de estadia mais consistente."
        />

        @if($empresas->isEmpty())
            <div class="site-empty-state">
                <p class="site-empty-state-title">Selecao em preparacao</p>
                <p class="site-empty-state-copy">A selecao de hospedagens ainda esta sendo preparada.</p>
            </div>
        @else
            <div class="site-directory-grid">
                @foreach($empresas as $item)
                    @php
                        $empresa = $item->empresa;
                        $imagem = $empresa->foto_capa_url ?: $empresa->foto_perfil_url ?: asset('imagens/altamira.jpg');
                        $contatos = is_array($empresa->contatos ?? null) ? $empresa->contatos : [];
                        $whatsapp = $contatos['whatsapp'] ?? null;
                        $maps = $empresa->maps_url ?? ($contatos['maps'] ?? null);
                        $site = $empresa->site_url ?? ($contatos['site'] ?? null);
                        $email = $empresa->email ?? ($contatos['email'] ?? null);
                        $descricao = $item->observacao_curta ?: strip_tags((string) $empresa->descricao);
                        $urlEmpresa = Route::has('site.empresa') ? route('site.empresa', ['empresa' => $empresa->slug ?: $empresa->id]) : '#';
                    @endphp

                    <article class="site-directory-card">
                        <div class="site-directory-card-media">
                            <img src="{{ $imagem }}" alt="{{ $empresa->nome }}" class="site-directory-card-image" loading="lazy" decoding="async">
                            <div class="site-directory-card-overlay">
                                @if($item->destaque)
                                    <span class="site-badge">Destaque</span>
                                @endif
                                @foreach(collect($empresa->categorias ?? [])->take(3) as $categoria)
                                    <span class="site-badge">{{ $categoria->nome }}</span>
                                @endforeach
                            </div>
                        </div>

                        <div class="site-directory-card-body">
                            <div>
                                <h3 class="site-directory-card-title">{{ $empresa->nome }}</h3>
                                <p class="site-directory-card-subtitle">{{ collect([$empresa->bairro, $empresa->cidade])->filter()->implode(' • ') ?: 'Altamira' }}</p>
                                <p class="site-inline-meta">Uma base segura para comparar localizacao, conforto e acesso durante a viagem.</p>
                            </div>

                            @if($descricao)
                                <p class="site-directory-card-summary">{{ \Illuminate\Support\Str::limit($descricao, 150) }}</p>
                            @endif

                            <div class="site-directory-card-actions">
                                <a href="{{ $urlEmpresa }}" class="site-button-primary">Ver empresa</a>
                                @if($maps)
                                    <a href="{{ $maps }}" target="_blank" rel="noopener noreferrer" class="site-button-secondary">Ver rota</a>
                                @endif
                                @if($whatsapp)
                                    <a href="{{ $whatsapp }}" target="_blank" rel="noopener noreferrer" class="site-button-secondary">WhatsApp</a>
                                @endif
                            </div>

                            @if($site || $email)
                                <div class="site-directory-card-footer">
                                    @if($site)
                                        <a href="{{ $site }}" target="_blank" rel="noopener noreferrer" class="site-link">Site</a>
                                    @endif
                                    @if($email)
                                        <a href="mailto:{{ $email }}" class="site-link">{{ $email }}</a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    @if(filled($pagina->texto_dicas))
        <section class="site-section">
            <div class="site-surface site-content-block">
                <x-section-head eyebrow="Dicas" title="Como escolher sua estadia" />
                <div class="site-prose">{!! nl2br(e($pagina->texto_dicas)) !!}</div>
            </div>
        </section>
    @endif
</div>
@endsection
