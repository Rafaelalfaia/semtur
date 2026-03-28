@extends('site.layouts.app')

@section('title', $material->nome . ' � ' . $material->tipo_label . ' � Visit Altamira')
@section('meta.description', \Illuminate\Support\Str::limit(strip_tags((string) $material->descricao), 160))
@section('meta.image', $material->capa_url ?: asset('imagens/altamira.jpg'))

@section('site.content')
@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    $cover = $material->capa_url ?: asset('imagens/altamira.jpg');
    $embedUrl = $material->embed_url;
    $guiasUrl = Route::has('site.guias') ? route('site.guias') : '#';
    $homeUrl = Route::has('site.home') ? route('site.home') : url('/');
    $relatedItems = collect($relacionados ?? []);
@endphp

<div class="site-page site-page-shell">
    @include('site.partials._page_hero', [
        'backHref' => $guiasUrl,
        'breadcrumbs' => [
            ['label' => 'In�cio', 'href' => $homeUrl],
            ['label' => 'Guias e revistas', 'href' => $guiasUrl],
            ['label' => $material->nome],
        ],
        'badge' => $material->tipo_label,
        'title' => $material->nome,
        'subtitle' => Str::limit(strip_tags((string) $material->descricao), 180),
        'meta' => [

        ],
        'primaryActionLabel' => 'Ler material',
        'primaryActionHref' => '#leitura',
        'secondaryActionLabel' => __('ui.common.back_to_guides'),
        'secondaryActionHref' => $guiasUrl,
        'image' => $cover,
        'imageAlt' => $material->nome,
        'compact' => true,
    ])

    <section class="site-section">
        <div class="site-editorial-layout">
            <div class="site-editorial-main">
                <section class="site-surface site-content-block">
                    <x-section-head
                        eyebrow="Sobre o material"
                        title="Informa��es do {{ Str::lower($material->tipo_label) }}"
                        subtitle="Resumo editorial e leitura principal do conte�do publicado."
                    />

                    <div class="site-prose">
                        {!! nl2br(e($material->descricao)) !!}
                    </div>
                </section>

                <section id="leitura" class="site-surface site-content-block">
                    <x-section-head
                        eyebrow="Leitura"
                        title="Visualiza��o do material"
                        subtitle="O conte�do abaixo � carregado dentro do portal para facilitar a consulta do visitante."
                    />

                    <div class="site-inline-actions">
                        @if(filled($material->link_acesso))
                            <a
                                href="{{ $material->link_acesso }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="site-button-secondary"
                            >
                                Abrir no Google Drive
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
                            Se o visualizador n�o carregar corretamente no seu dispositivo, use o bot�o �Abrir no Google Drive�.
                        </p>
                    @else
                        <div class="site-empty-state">
                            <p class="site-empty-state-title">Preview indispon�vel</p>
                            <p class="site-empty-state-copy">Este material n�o possui um formato compat�vel de visualiza��o interna.</p>

                            @if(filled($material->link_acesso))
                                <a
                                    href="{{ $material->link_acesso }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="site-button-primary"
                                >
                                    Abrir material
                                </a>
                            @endif
                        </div>
                    @endif
                </section>
            </div>

            <aside class="site-editorial-aside">
                <section class="site-surface-soft site-content-block">
                    <x-section-head eyebrow="Resumo" title="Vis�o geral" />

                    <div class="site-stats-grid">
                        <div class="site-stat-card">
                            <span class="site-stat-label">Tipo</span>
                            <span class="site-stat-value">{{ $material->tipo_label }}</span>
                        </div>


                    </div>

                    <div class="site-inline-actions">
                        <a href="{{ $guiasUrl }}" class="site-button-secondary">Ver mais materiais</a>
                    </div>
                </section>

                @if($relatedItems->isNotEmpty())
                    <section class="site-surface-soft site-content-block">
                        <x-section-head
                            eyebrow="Relacionados"
                            title="Mais {{ Str::plural(Str::lower($material->tipo_label), 2) }}"
                        />

                        <div class="space-y-4">
                            @foreach($relatedItems as $rel)
                                <a
                                    href="{{ route('site.guias.show', $rel->slug) }}"
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
