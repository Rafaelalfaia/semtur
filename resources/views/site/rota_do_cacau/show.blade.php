@extends('site.layouts.app')

@section('title', $edicao->titulo . ' • Rota do Cacau')
@section('meta.description', \Illuminate\Support\Str::limit(strip_tags((string) ($edicao->descricao ?: $rota->descricao)), 160))
@section('meta.image', $edicao->capa_url ?: ($rota->foto_capa_url ?: ($rota->foto_perfil_url ?: asset('imagens/altamira.jpg'))))

@section('site.content')
@php
    use Illuminate\Support\Str;

    $pageBlocks = $pageBlocks ?? collect();
    $rotaShowBlocks = [
        'hero' => $pageBlocks->get('hero'),
        'about_section' => $pageBlocks->get('about_section'),
        'gallery_section' => $pageBlocks->get('gallery_section'),
        'videos_section' => $pageBlocks->get('videos_section'),
        'sponsors_section' => $pageBlocks->get('sponsors_section'),
        'empty_state' => $pageBlocks->get('empty_state'),
        'summary_sidebar' => $pageBlocks->get('summary_sidebar'),
        'other_editions' => $pageBlocks->get('other_editions'),
    ];
    $rotaShowTranslation = fn (string $key) => $rotaShowBlocks[$key]?->getAttribute('traducao_resolvida');
    $aboutTranslation = $rotaShowTranslation('about_section');
    $galleryTranslation = $rotaShowTranslation('gallery_section');
    $videosTranslation = $rotaShowTranslation('videos_section');
    $sponsorsTranslation = $rotaShowTranslation('sponsors_section');
    $emptyTranslation = $rotaShowTranslation('empty_state');
    $summaryTranslation = $rotaShowTranslation('summary_sidebar');
    $otherEditionsTranslation = $rotaShowTranslation('other_editions');

    $cover = $heroMedia?->url ?: ($edicao->capa_url ?: ($rota->foto_capa_url ?: asset('imagens/altamira.jpg')));
    $profile = $rota->foto_perfil_url;
    $heroBadge = $heroTranslation?->eyebrow ?: ('Edição '.$edicao->ano);
    $heroTitle = $heroTranslation?->titulo ?: $edicao->titulo;
    $heroSubtitle = $heroTranslation?->lead ?: Str::limit(strip_tags((string) $edicao->descricao), 280);
    $heroPrimaryLabel = $heroTranslation?->cta_label ?: ($edicao->fotos_count > 0 ? 'Ver galeria' : ($edicao->videos_count > 0 ? 'Ver vídeos' : ui_text('ui.common.back_to_route')));
    $heroPrimaryHref = $heroTranslation?->cta_href ?: ($edicao->fotos_count > 0 ? '#galeria' : ($edicao->videos_count > 0 ? '#videos' : localized_route('site.rota_do_cacau.index')));
    $aboutEyebrow = $aboutTranslation?->eyebrow ?: 'Sobre a ediÃ§Ã£o';
    $aboutTitle = $aboutTranslation?->titulo ?: 'Contexto editorial desta publicaÃ§Ã£o';
    $galleryEyebrow = $galleryTranslation?->eyebrow ?: 'Galeria';
    $galleryTitle = $galleryTranslation?->titulo ?: 'Fotos da ediÃ§Ã£o';
    $videosEyebrow = $videosTranslation?->eyebrow ?: 'VÃ­deos';
    $videosTitle = $videosTranslation?->titulo ?: 'ConteÃºdo audiovisual da ediÃ§Ã£o';
    $sponsorsEyebrow = $sponsorsTranslation?->eyebrow ?: 'Patrocinadores';
    $sponsorsTitle = $sponsorsTranslation?->titulo ?: 'Apoiadores desta ediÃ§Ã£o';
    $emptyTitle = $emptyTranslation?->titulo ?: 'ConteÃºdos complementares em atualizaÃ§Ã£o';
    $emptyCopy = $emptyTranslation?->lead ?: 'Esta ediÃ§Ã£o jÃ¡ estÃ¡ publicada, mas ainda nÃ£o possui galeria, vÃ­deos ou patrocinadores visÃ­veis no portal.';
    $summaryEyebrow = $summaryTranslation?->eyebrow ?: 'Resumo rÃ¡pido';
    $summaryTitle = $summaryTranslation?->titulo ?: 'VisÃ£o geral da ediÃ§Ã£o';
    $otherEditionsEyebrow = $otherEditionsTranslation?->eyebrow ?: 'Outras ediÃ§Ãµes';
    $otherEditionsTitle = $otherEditionsTranslation?->titulo ?: 'Continue explorando';
@endphp

<section class="relative isolate overflow-hidden bg-[#1f3027] text-white">
    <div class="absolute inset-0">
        <img
            src="{{ $cover }}"
            alt="{{ $edicao->titulo }}"
            class="h-full w-full object-cover opacity-25"
            loading="eager"
            decoding="async"
        >
        <div class="absolute inset-0 bg-gradient-to-b from-[#1f3027]/45 via-[#1f3027]/82 to-[#1f3027]"></div>
    </div>

    <div class="relative mx-auto max-w-[1200px] px-4 pb-12 pt-8 sm:px-6 lg:px-8 lg:pb-16 lg:pt-10">
        <a
            href="{{ localized_route('site.rota_do_cacau.index') }}"
            class="inline-flex items-center rounded-full border border-white/15 bg-white/10 px-4 py-2 text-sm text-white transition hover:bg-white/15"
        >
            ← {{ ui_text('ui.common.back_to_route') }}
        </a>

        <div class="mt-5 text-sm text-white/70">
            <a href="{{ localized_route('site.home') }}" class="transition hover:text-white">Início</a>
            <span class="mx-2">/</span>
            <a href="{{ localized_route('site.rota_do_cacau.index') }}" class="transition hover:text-white">Rota do Cacau</a>
            <span class="mx-2">/</span>
            <span>{{ $edicao->titulo }}</span>
        </div>

        <div class="mt-6 grid gap-8 lg:grid-cols-[1.15fr_.85fr] lg:items-end">
            <div class="max-w-3xl">
                @include('site.partials._content_editor', [
                    'editorTitle' => $heroTitle,
                    'editorPage' => 'site.rota_do_cacau.show',
                    'editorKey' => 'hero',
                    'editorLabel' => 'Hero detalhe da Rota do Cacau',
                    'editorLocale' => route_locale(),
                    'editorTriggerVariant' => 'inline',
                    'editableTranslation' => $heroTranslation ?? null,
                    'editableHeroMedia' => $heroMedia ?? null,
                    'editableStatus' => $heroBlock?->status ?? 'publicado',
                    'editableFallback' => [
                        'eyebrow' => $heroBadge,
                        'titulo' => $heroTitle,
                        'subtitulo' => null,
                        'lead' => $heroSubtitle,
                        'conteudo' => null,
                        'cta_label' => $heroPrimaryLabel,
                        'cta_href' => $heroPrimaryHref,
                        'seo_title' => $heroTitle,
                        'seo_description' => \Illuminate\Support\Str::limit(strip_tags((string) ($edicao->descricao ?: $rota->descricao)), 160),
                    ],
                ])
                <div class="flex flex-wrap gap-2">
                    <span class="rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs text-white">{{ $heroBadge }}</span>
                    <span class="rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs text-white">
                        {{ optional($edicao->published_at)->format('d/m/Y') ?: 'Publicada' }}
                    </span>
                </div>

                <h1 class="mt-4 text-3xl font-semibold leading-tight sm:text-4xl lg:text-5xl">
                    {{ $heroTitle }}
                </h1>

                <p class="mt-4 max-w-2xl text-sm leading-7 text-white/85 sm:text-base">
                    {{ $heroSubtitle }}
                </p>

                <div class="mt-6 flex flex-wrap gap-3">
                    @if($edicao->fotos_count > 0)
                        <a href="#galeria" class="inline-flex items-center rounded-2xl bg-amber-500 px-5 py-3 text-sm font-medium text-slate-950 transition hover:bg-amber-400">Ver galeria</a>
                    @endif
                    @if($edicao->videos_count > 0)
                        <a href="#videos" class="inline-flex items-center rounded-2xl border border-white/15 bg-white/10 px-5 py-3 text-sm font-medium text-white transition hover:bg-white/15">Ver vídeos</a>
                    @endif
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-2">
                <div class="rounded-[28px] border border-white/10 bg-white/10 p-4 backdrop-blur">
                    <div class="text-[11px] uppercase tracking-[0.16em] text-white/75">Fotos</div>
                    <div class="mt-2 text-2xl font-semibold">{{ $edicao->fotos_count }}</div>
                    <p class="mt-2 text-sm leading-6 text-white/80">Galeria própria desta edição.</p>
                </div>
                <div class="rounded-[28px] border border-white/10 bg-white/10 p-4 backdrop-blur">
                    <div class="text-[11px] uppercase tracking-[0.16em] text-white/75">Vídeos</div>
                    <div class="mt-2 text-2xl font-semibold">{{ $edicao->videos_count }}</div>
                    <p class="mt-2 text-sm leading-6 text-white/80">Conteúdos audiovisuais vinculados ao ano.</p>
                </div>
                <div class="rounded-[28px] border border-white/10 bg-white/10 p-4 backdrop-blur">
                    <div class="text-[11px] uppercase tracking-[0.16em] text-white/75">Patrocinadores</div>
                    <div class="mt-2 text-2xl font-semibold">{{ $edicao->patrocinadores_count }}</div>
                    <p class="mt-2 text-sm leading-6 text-white/80">Apoios visíveis apenas nesta edição.</p>
                </div>
                @if($profile)
                    <div class="rounded-[28px] border border-white/10 bg-white/10 p-4 backdrop-blur">
                        <div class="text-[11px] uppercase tracking-[0.16em] text-white/75">Módulo</div>
                        <div class="mt-3 flex items-center gap-3">
                            <img src="{{ $profile }}" alt="{{ $rota->titulo }}" class="h-14 w-14 rounded-2xl object-cover border border-white/15">
                            <div class="text-sm font-medium text-white">{{ $rota->titulo }}</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

<section class="bg-[#fbfaf7] py-14">
    <div class="mx-auto max-w-[1200px] px-4 sm:px-6 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
            <div class="space-y-6">
                <section class="rounded-[30px] border border-[#e8e2d9] bg-white p-6 shadow-sm sm:p-8">
                    @include('site.partials._content_editor', [
                        'editorTitle' => $aboutTitle,
                        'editorPage' => 'site.rota_do_cacau.show',
                        'editorKey' => 'about_section',
                        'editorLabel' => 'Seção sobre da edição',
                        'editorLocale' => route_locale(),
                        'editorTriggerVariant' => 'inline',
                        'editableTranslation' => $aboutTranslation,
                        'editableStatus' => $rotaShowBlocks['about_section']?->status ?? 'publicado',
                        'editableFallback' => [
                            'eyebrow' => 'Sobre a edição',
                            'titulo' => 'Contexto editorial desta publicação',
                        ],
                    ])
                    <div class="text-[11px] font-semibold uppercase tracking-[0.20em] text-[#7b5a2c]">{{ $aboutEyebrow }}</div>
                    <h2 class="mt-3 text-2xl font-semibold text-slate-900">{{ $aboutTitle }}</h2>
                    <div class="mt-4 text-[15px] leading-8 text-slate-600">
                        {!! nl2br(e($edicao->descricao)) !!}
                    </div>
                </section>

                @if($edicao->fotos->count())
                    <section id="galeria" class="rounded-[30px] border border-[#e8e2d9] bg-white p-6 shadow-sm sm:p-8">
                        @include('site.partials._content_editor', [
                            'editorTitle' => $galleryTitle,
                            'editorPage' => 'site.rota_do_cacau.show',
                            'editorKey' => 'gallery_section',
                            'editorLabel' => 'Seção galeria da edição',
                            'editorLocale' => route_locale(),
                            'editorTriggerVariant' => 'inline',
                            'editableTranslation' => $galleryTranslation,
                            'editableStatus' => $rotaShowBlocks['gallery_section']?->status ?? 'publicado',
                            'editableFallback' => [
                                'eyebrow' => 'Galeria',
                                'titulo' => 'Fotos da edição',
                            ],
                        ])
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <div class="text-[11px] font-semibold uppercase tracking-[0.20em] text-[#7b5a2c]">{{ $galleryEyebrow }}</div>
                                <h2 class="mt-2 text-2xl font-semibold text-slate-900">{{ $galleryTitle }}</h2>
                            </div>
                            <div class="text-sm text-slate-500">{{ $edicao->fotos_count }} {{ $edicao->fotos_count === 1 ? 'foto' : 'fotos' }}</div>
                        </div>

                        <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                            @foreach($edicao->fotos as $foto)
                                <figure class="overflow-hidden rounded-[24px] border border-slate-200 bg-slate-50">
                                    <img
                                        src="{{ $foto->imagem_url ?: $cover }}"
                                        alt="{{ $foto->legenda ?: $edicao->titulo }}"
                                        class="h-64 w-full object-cover"
                                        loading="lazy"
                                        decoding="async"
                                    >
                                    @if($foto->legenda)
                                        <figcaption class="px-4 py-3 text-sm leading-6 text-slate-600">{{ $foto->legenda }}</figcaption>
                                    @endif
                                </figure>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if($edicao->videos->count())
                    <section id="videos" class="rounded-[30px] border border-[#e8e2d9] bg-white p-6 shadow-sm sm:p-8">
                        @include('site.partials._content_editor', [
                            'editorTitle' => $videosTitle,
                            'editorPage' => 'site.rota_do_cacau.show',
                            'editorKey' => 'videos_section',
                            'editorLabel' => 'Seção vídeos da edição',
                            'editorLocale' => route_locale(),
                            'editorTriggerVariant' => 'inline',
                            'editableTranslation' => $videosTranslation,
                            'editableStatus' => $rotaShowBlocks['videos_section']?->status ?? 'publicado',
                            'editableFallback' => [
                                'eyebrow' => 'Vídeos',
                                'titulo' => 'Conteúdo audiovisual da edição',
                            ],
                        ])
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <div class="text-[11px] font-semibold uppercase tracking-[0.20em] text-[#7b5a2c]">{{ $videosEyebrow }}</div>
                                <h2 class="mt-2 text-2xl font-semibold text-slate-900">{{ $videosTitle }}</h2>
                            </div>
                            <div class="text-sm text-slate-500">{{ $edicao->videos_count }} {{ $edicao->videos_count === 1 ? 'vídeo' : 'vídeos' }}</div>
                        </div>

                        <div class="mt-6 grid gap-6 lg:grid-cols-2">
                            @foreach($edicao->videos as $video)
                                <article class="overflow-hidden rounded-[26px] border border-slate-200 bg-slate-50">
                                    @if($video->embed_url_resolvida)
                                        <div class="aspect-video overflow-hidden bg-slate-200">
                                            <iframe
                                                src="{{ $video->embed_url_resolvida }}"
                                                title="{{ $video->titulo }}"
                                                class="h-full w-full"
                                                loading="lazy"
                                                referrerpolicy="strict-origin-when-cross-origin"
                                                allowfullscreen
                                            ></iframe>
                                        </div>
                                    @else
                                        <div class="flex aspect-video items-center justify-center bg-slate-100 px-6 text-center text-sm leading-7 text-slate-500">
                                            Este vídeo não possui preview incorporado no momento.
                                        </div>
                                    @endif

                                    <div class="p-5">
                                        <h3 class="text-lg font-semibold text-slate-900">{{ $video->titulo }}</h3>
                                        @if($video->descricao)
                                            <p class="mt-3 text-sm leading-7 text-slate-600">{{ Str::limit(strip_tags((string) $video->descricao), 180) }}</p>
                                        @endif
                                        <div class="mt-4 flex flex-wrap gap-2">
                                            <a
                                                href="{{ $video->drive_url }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="inline-flex items-center rounded-2xl bg-[#31543c] px-4 py-2.5 text-sm font-medium text-white transition hover:bg-[#264230]"
                                            >
                                                Abrir no Google Drive
                                            </a>
                                            @if($video->embed_url_resolvida)
                                                <a
                                                    href="{{ $video->embed_url_resolvida }}"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="inline-flex items-center rounded-2xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:border-[#7b5a2c] hover:text-[#7b5a2c]"
                                                >
                                                    Abrir preview
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if($edicao->patrocinadores->count())
                    <section id="patrocinadores" class="rounded-[30px] border border-[#e8e2d9] bg-white p-6 shadow-sm sm:p-8">
                        @include('site.partials._content_editor', [
                            'editorTitle' => $sponsorsTitle,
                            'editorPage' => 'site.rota_do_cacau.show',
                            'editorKey' => 'sponsors_section',
                            'editorLabel' => 'Seção patrocinadores da edição',
                            'editorLocale' => route_locale(),
                            'editorTriggerVariant' => 'inline',
                            'editableTranslation' => $sponsorsTranslation,
                            'editableStatus' => $rotaShowBlocks['sponsors_section']?->status ?? 'publicado',
                            'editableFallback' => [
                                'eyebrow' => 'Patrocinadores',
                                'titulo' => 'Apoiadores desta edição',
                            ],
                        ])
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <div class="text-[11px] font-semibold uppercase tracking-[0.20em] text-[#7b5a2c]">{{ $sponsorsEyebrow }}</div>
                                <h2 class="mt-2 text-2xl font-semibold text-slate-900">{{ $sponsorsTitle }}</h2>
                            </div>
                            <div class="text-sm text-slate-500">{{ $edicao->patrocinadores_count }} {{ $edicao->patrocinadores_count === 1 ? 'patrocinador' : 'patrocinadores' }}</div>
                        </div>

                        <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                            @foreach($edicao->patrocinadores as $patrocinador)
                                @php
                                    $tag = $patrocinador->url ? 'a' : 'div';
                                @endphp
                                <{{ $tag }}
                                    @if($patrocinador->url)
                                        href="{{ $patrocinador->url }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    @endif
                                    class="block rounded-[24px] border border-slate-200 bg-slate-50 p-5 transition hover:border-[#d8cfbf] hover:bg-white"
                                >
                                    <div class="flex h-28 items-center justify-center overflow-hidden rounded-[20px] border border-slate-200 bg-white p-4">
                                        @if($patrocinador->logo_url)
                                            <img src="{{ $patrocinador->logo_url }}" alt="{{ $patrocinador->nome }}" class="h-full w-full object-contain">
                                        @else
                                            <span class="text-sm text-slate-400">{{ $patrocinador->nome }}</span>
                                        @endif
                                    </div>
                                    <div class="mt-4 text-base font-semibold text-slate-900">{{ $patrocinador->nome }}</div>
                                    @if($patrocinador->url)
                                        <div class="mt-2 text-sm text-[#7b5a2c]">Visitar link</div>
                                    @endif
                                </{{ $tag }}>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if(!$temConteudoComplementar)
                    <section class="rounded-[30px] border border-dashed border-[#d8cfbf] bg-white px-6 py-10 text-center shadow-sm">
                        @include('site.partials._content_editor', [
                            'editorTitle' => $emptyTitle,
                            'editorPage' => 'site.rota_do_cacau.show',
                            'editorKey' => 'empty_state',
                            'editorLabel' => 'Estado vazio da edição',
                            'editorLocale' => route_locale(),
                            'editorTriggerVariant' => 'inline',
                            'editableTranslation' => $emptyTranslation,
                            'editableStatus' => $rotaShowBlocks['empty_state']?->status ?? 'publicado',
                            'editableFallback' => [
                                'titulo' => 'Conteúdos complementares em atualização',
                                'lead' => 'Esta edição já está publicada, mas ainda não possui galeria, vídeos ou patrocinadores visíveis no portal.',
                            ],
                        ])
                        <h2 class="text-xl font-semibold text-slate-900">{{ $emptyTitle }}</h2>
                        <p class="mx-auto mt-3 max-w-2xl text-sm leading-7 text-slate-600">
                            {{ $emptyCopy }}
                        </p>
                    </section>
                @endif
            </div>

            <aside class="space-y-6 lg:sticky lg:top-6 lg:self-start">
                <section class="rounded-[30px] border border-[#e8e2d9] bg-white p-5 shadow-sm">
                    @include('site.partials._content_editor', [
                        'editorTitle' => $summaryTitle,
                        'editorPage' => 'site.rota_do_cacau.show',
                        'editorKey' => 'summary_sidebar',
                        'editorLabel' => 'Resumo lateral da edição',
                        'editorLocale' => route_locale(),
                        'editorTriggerVariant' => 'inline',
                        'editableTranslation' => $summaryTranslation,
                        'editableStatus' => $rotaShowBlocks['summary_sidebar']?->status ?? 'publicado',
                        'editableFallback' => [
                            'eyebrow' => 'Resumo rápido',
                            'titulo' => 'Visão geral da edição',
                        ],
                    ])
                    <div class="text-[11px] font-semibold uppercase tracking-[0.20em] text-[#7b5a2c]">{{ $summaryEyebrow }}</div>
                    <h2 class="mt-2 text-lg font-semibold text-slate-900">{{ $summaryTitle }}</h2>

                    <div class="mt-5 space-y-3">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <div class="text-[11px] uppercase tracking-[0.16em] text-slate-500">Ano</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">{{ $edicao->ano }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <div class="text-[11px] uppercase tracking-[0.16em] text-slate-500">Publicado em</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">{{ optional($edicao->published_at)->format('d/m/Y') ?: '—' }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <div class="text-[11px] uppercase tracking-[0.16em] text-slate-500">Fotos</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">{{ $edicao->fotos_count }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <div class="text-[11px] uppercase tracking-[0.16em] text-slate-500">Vídeos</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">{{ $edicao->videos_count }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <div class="text-[11px] uppercase tracking-[0.16em] text-slate-500">Patrocinadores</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">{{ $edicao->patrocinadores_count }}</div>
                        </div>
                    </div>
                </section>

                @if($outrasEdicoes->count())
                    <section class="rounded-[30px] border border-[#e8e2d9] bg-white p-5 shadow-sm">
                        @include('site.partials._content_editor', [
                            'editorTitle' => $otherEditionsTitle,
                            'editorPage' => 'site.rota_do_cacau.show',
                            'editorKey' => 'other_editions',
                            'editorLabel' => 'Outras edições da rota',
                            'editorLocale' => route_locale(),
                            'editorTriggerVariant' => 'inline',
                            'editableTranslation' => $otherEditionsTranslation,
                            'editableStatus' => $rotaShowBlocks['other_editions']?->status ?? 'publicado',
                            'editableFallback' => [
                                'eyebrow' => 'Outras edições',
                                'titulo' => 'Continue explorando',
                            ],
                        ])
                        <div class="text-[11px] font-semibold uppercase tracking-[0.20em] text-[#7b5a2c]">{{ $otherEditionsEyebrow }}</div>
                        <h2 class="mt-2 text-lg font-semibold text-slate-900">{{ $otherEditionsTitle }}</h2>

                        <div class="mt-5 space-y-4">
                            @foreach($outrasEdicoes as $item)
                                <a
                                    href="{{ localized_route('site.rota_do_cacau.show', ['slug' => $item->slug]) }}"
                                    class="block rounded-[24px] border border-slate-200 bg-slate-50 p-4 transition hover:border-[#d8cfbf] hover:bg-white"
                                >
                                    <div class="text-xs font-semibold uppercase tracking-[0.16em] text-[#7b5a2c]">{{ $item->ano }}</div>
                                    <div class="mt-2 text-sm font-semibold text-slate-900">{{ $item->titulo }}</div>
                                    <p class="mt-2 text-sm leading-6 text-slate-600">
                                        {{ Str::limit(strip_tags((string) $item->descricao), 88) }}
                                    </p>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif
            </aside>
        </div>
    </div>
</section>

@endsection
