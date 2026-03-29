@extends('site.layouts.app')

@php
    $canonical = localized_route('site.jogos_indigenas.index');
    $title = $jogo?->titulo ?: 'Jogos Indigenas';
    $description = \Illuminate\Support\Str::limit(strip_tags($jogo?->descricao ?: 'Acompanhe a area publica dos Jogos Indigenas no portal Visit Altamira.'), 160);
    $image = $jogo?->foto_capa_url ?: $jogo?->foto_perfil_url ?: theme_asset('hero_image');
@endphp

@section('title', $title)
@section('meta.description', $description)
@section('meta.image', $image)
@section('meta.canonical', $canonical)
@section('meta.type', 'website')

@section('site.content')
@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    $heroMeta = $jogo ? array_filter([
        ($stats['edicoes'] ?? 0) ? (($stats['edicoes'] ?? 0).' edicoes') : null,
        ($stats['fotos'] ?? 0) ? (($stats['fotos'] ?? 0).' fotos') : null,
        ($stats['videos'] ?? 0) ? (($stats['videos'] ?? 0).' videos') : null,
    ]) : [];
@endphp

<div class="site-page site-page-shell site-jogos-page">
    @include('site.partials._page_hero', [
        'backHref' => localized_route('site.home'),
        'breadcrumbs' => [
            ['label' => 'Inicio', 'href' => localized_route('site.home')],
            ['label' => 'Jogos Indigenas'],
        ],
        'badge' => 'Jogos Indigenas',
        'title' => $title,
        'subtitle' => $jogo?->descricao ? Str::limit(strip_tags($jogo->descricao), 180) : 'Conteudo oficial publicado a partir do coordenador, com edicoes, midia e parceiros.',
        'meta' => $heroMeta,
        'primaryActionLabel' => $edicaoDestaque ? 'Ver edicoes' : (Route::has('site.home') ? __('ui.common.back_to_home') : null),
        'primaryActionHref' => $edicaoDestaque ? '#edicoes-jogos' : (Route::has('site.home') ? localized_route('site.home') : null),
        'secondaryActionLabel' => Route::has('site.explorar') ? 'Explorar cidade' : null,
        'secondaryActionHref' => Route::has('site.explorar') ? localized_route('site.explorar') : null,
        'image' => $image,
        'imageAlt' => $title,
        'compact' => true,
    ])

    @if(!$jogo)
        <section class="site-section">
            <div class="site-empty-state">
                <h2 class="site-empty-state-title">Ainda nao ha conteudo publicado</h2>
                <p class="site-empty-state-copy">Os Jogos Indigenas vao aparecer aqui assim que o coordenador publicar o cadastro principal e as edicoes.</p>
            </div>
        </section>
    @else
        <section class="site-section">
            <section class="site-surface site-content-block">
                <div class="site-detail-profile">
                    <img src="{{ site_image_url($jogo->foto_perfil_url ?: theme_asset('logo'), 'avatar') }}" alt="{{ $title }}" class="site-detail-avatar" loading="lazy" decoding="async">
                    <div>
                        <x-section-head eyebrow="Sobre" title="Sobre os Jogos Indigenas" subtitle="Tradicao, historia e contexto do evento em uma leitura publica mais clara e direta." />
                    </div>
                </div>

                <div class="site-prose">
                    {!! nl2br(e($jogo->descricao)) !!}
                </div>
            </section>
        </section>

        @if($edicoes->isNotEmpty())
            <section class="site-section" id="edicoes-jogos">
                <x-section-head
                    eyebrow="Jogos Indigenas"
                    title="Edicoes publicadas"
                    subtitle="Os Jogos Indigenas reunem diferentes etnias em disputas tradicionais realizadas em Altamira, em uma celebracao cultural que volta todos os anos."
                />

                <div class="site-jogos-editions">
                    @foreach($edicoes as $edicao)
                        @php
                            $photos = $edicao->fotos->take(4);
                            $videos = $edicao->videos->take(3);
                            $sponsors = $edicao->patrocinadores->take(6);
                            $cover = $edicao->capa_url ?: $jogo->foto_capa_url ?: theme_asset('hero_image');
                            $media = collect();

                            foreach ($photos as $foto) {
                                $media->push([
                                    'type' => 'photo',
                                    'src' => $foto->imagem_url,
                                    'alt' => $foto->legenda ?: $edicao->titulo,
                                    'title' => $foto->legenda ?: $edicao->titulo,
                                ]);
                            }

                            foreach ($videos as $video) {
                                if ($video->embed_url_resolvida) {
                                    $media->push([
                                        'type' => 'video',
                                        'src' => $video->embed_url_resolvida,
                                        'alt' => $video->titulo ?: 'Video da edicao',
                                        'title' => $video->titulo ?: 'Video da edicao',
                                    ]);
                                }
                            }
                        @endphp

                        <article
                            class="site-surface site-jogos-edition-card"
                            x-data="{
                                open: false,
                                index: 0,
                                items: @js($media->values()),
                                show(i) {
                                    if (!this.items.length) return;
                                    this.index = i;
                                    this.open = true;
                                    document.body.style.overflow = 'hidden';
                                },
                                close() {
                                    this.open = false;
                                    document.body.style.overflow = '';
                                },
                                next() {
                                    if (!this.items.length) return;
                                    this.index = (this.index + 1) % this.items.length;
                                },
                                prev() {
                                    if (!this.items.length) return;
                                    this.index = (this.index - 1 + this.items.length) % this.items.length;
                                },
                                current() {
                                    return this.items[this.index] || null;
                                }
                            }"
                        >
                            <div class="site-jogos-edition-media">
                                <img
                                    src="{{ site_image_url($cover, 'card') }}"
                                    alt="{{ $edicao->titulo }}"
                                    class="site-jogos-edition-image"
                                    loading="lazy"
                                    decoding="async"
                                >
                            </div>

                            <div class="site-jogos-edition-body">
                                <div class="site-jogos-edition-head">
                                    <span class="site-badge">{{ $edicao->ano }}</span>
                                    <h3 class="site-jogos-edition-title">{{ $edicao->titulo }}</h3>
                                    <p class="site-jogos-edition-summary">{{ Str::limit(strip_tags($edicao->descricao), 220) }}</p>
                                </div>

                                <div class="site-jogos-edition-stats">
                                    <span class="site-page-hero-meta-item">{{ $edicao->fotos_count }} fotos</span>
                                    <span class="site-page-hero-meta-item">{{ $edicao->videos_count }} videos</span>
                                    <span class="site-page-hero-meta-item">{{ $edicao->patrocinadores_count }} parceiros</span>
                                </div>

                                @if($photos->isNotEmpty())
                                    <div class="site-jogos-inline-block">
                                        <div class="site-jogos-inline-label">Galeria</div>
                                        <div class="site-jogos-photo-strip">
                                            @foreach($photos as $mediaIndex => $foto)
                                                <button type="button" class="site-jogos-photo-button" @click="show({{ $mediaIndex }})">
                                                    <img
                                                        src="{{ site_image_url($foto->imagem_url, 'mini') }}"
                                                        alt="{{ $foto->legenda ?: $edicao->titulo }}"
                                                        class="site-jogos-photo-thumb"
                                                        loading="lazy"
                                                        decoding="async"
                                                    >
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if($videos->isNotEmpty())
                                    <div class="site-jogos-inline-block">
                                        <div class="site-jogos-inline-label">Videos</div>
                                        <div class="site-jogos-video-links">
                                            @foreach($videos as $videoIndex => $video)
                                                @php
                                                    $videoMediaIndex = $photos->count() + $videoIndex;
                                                @endphp
                                                @if($video->embed_url_resolvida)
                                                    <button type="button" class="site-jogos-video-card" @click="show({{ $videoMediaIndex }})">
                                                        <span class="site-jogos-video-icon" aria-hidden="true">Play</span>
                                                        <span class="site-jogos-video-card-title">{{ Str::limit($video->titulo ?: 'Assistir video', 42) }}</span>
                                                    </button>
                                                @else
                                                    <span class="site-jogos-video-text">{{ Str::limit($video->titulo ?: 'Video', 42) }}</span>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if($sponsors->isNotEmpty())
                                    <div class="site-jogos-inline-block">
                                        <div class="site-jogos-inline-label">Patrocinadores</div>
                                        <div class="site-jogos-sponsor-strip">
                                            @foreach($sponsors as $patrocinador)
                                                @if(filled($patrocinador->url))
                                                    <a href="{{ $patrocinador->url }}" target="_blank" rel="noopener noreferrer" class="site-jogos-sponsor-item" aria-label="{{ $patrocinador->nome }}">
                                                        @if($patrocinador->logo_url)
                                                            <img src="{{ site_image_url($patrocinador->logo_url, 'mini') }}" alt="{{ $patrocinador->nome }}" class="site-jogos-sponsor-logo" loading="lazy" decoding="async">
                                                        @else
                                                            <span class="site-jogos-sponsor-name">{{ Str::limit($patrocinador->nome, 20) }}</span>
                                                        @endif
                                                    </a>
                                                @else
                                                    <span class="site-jogos-sponsor-item" aria-label="{{ $patrocinador->nome }}">
                                                        @if($patrocinador->logo_url)
                                                            <img src="{{ site_image_url($patrocinador->logo_url, 'mini') }}" alt="{{ $patrocinador->nome }}" class="site-jogos-sponsor-logo" loading="lazy" decoding="async">
                                                        @else
                                                            <span class="site-jogos-sponsor-name">{{ Str::limit($patrocinador->nome, 20) }}</span>
                                                        @endif
                                                    </span>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div x-show="open" x-cloak class="site-lightbox" @click.self="close()" x-transition.opacity>
                                <div class="site-lightbox-frame site-jogos-lightbox-frame">
                                    <button type="button" class="site-lightbox-close" @click="close()" aria-label="Fechar midia">&times;</button>
                                    <button type="button" class="site-lightbox-arrow is-prev" @click.stop="prev()" aria-label="Midia anterior">&#8249;</button>

                                    <template x-if="current()?.type === 'photo'">
                                        <img :src="current()?.src" :alt="current()?.alt || ''" class="site-lightbox-image">
                                    </template>

                                    <template x-if="current()?.type === 'video'">
                                        <iframe
                                            class="site-jogos-lightbox-embed"
                                            :src="current()?.src"
                                            :title="current()?.title || 'Video da edicao'"
                                            loading="lazy"
                                            allow="autoplay; encrypted-media; picture-in-picture"
                                            allowfullscreen
                                        ></iframe>
                                    </template>

                                    <button type="button" class="site-lightbox-arrow is-next" @click.stop="next()" aria-label="Proxima midia">&#8250;</button>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif
    @endif
</div>
@endsection
