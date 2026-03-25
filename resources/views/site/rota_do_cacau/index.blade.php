@extends('site.layouts.app')

@section('title', ($rota?->titulo ?: 'Rota do Cacau') . ' • Visit Altamira')
@section('meta.description', \Illuminate\Support\Str::limit(strip_tags((string) ($rota?->descricao ?: 'Conheça a Rota do Cacau em Altamira, com edições publicadas, galeria, vídeos e apoiadores de cada edição.')), 160))
@section('meta.image', $rota?->foto_capa_url ?: ($rota?->foto_perfil_url ?: asset('imagens/altamira.jpg')))

@section('site.content')
@php
    use Illuminate\Support\Str;

    $cover = $rota?->foto_capa_url ?: asset('imagens/altamira.jpg');
    $profile = $rota?->foto_perfil_url;
    $totalEdicoes = $edicoes instanceof \Illuminate\Support\Collection ? $edicoes->count() : 0;
@endphp

@if(!$rota)
    <section class="bg-gradient-to-b from-[#f7fbf7] to-white py-12 md:py-16">
        <div class="mx-auto w-full max-w-[1200px] px-4 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-[32px] border border-slate-200 bg-white shadow-sm">
                <div class="bg-gradient-to-r from-[#31543c] via-[#4b6f48] to-[#7b5a2c] px-6 py-10 text-white sm:px-10">
                    <div class="text-xs font-semibold uppercase tracking-[0.20em] text-white/80">Rota do Cacau</div>
                    <h1 class="mt-3 text-3xl font-semibold tracking-[-0.03em] sm:text-4xl">Conteúdo em preparação</h1>
                    <p class="mt-4 max-w-2xl text-sm leading-7 text-white/90 sm:text-base">
                        A área pública da Rota do Cacau ainda não possui um cadastro institucional publicado.
                        Volte em breve para acompanhar as edições e os conteúdos deste módulo.
                    </p>
                </div>

                <div class="px-6 py-8 sm:px-10 sm:py-10">
                    <div class="rounded-[24px] border border-dashed border-slate-200 bg-slate-50 px-6 py-8 text-center">
                        <h2 class="text-lg font-semibold text-slate-900">Nenhum conteúdo público disponível no momento</h2>
                        <p class="mx-auto mt-3 max-w-2xl text-sm leading-7 text-slate-600">
                            Assim que o cadastro principal e as edições forem publicados no painel, esta página passará a exibir os dados reais do banco.
                        </p>
                        <div class="mt-6 flex flex-wrap justify-center gap-3">
                            <a href="{{ route('site.home') }}" class="inline-flex items-center rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-medium text-white transition hover:bg-emerald-500">
                                Voltar para a home
                            </a>
                            <a href="{{ route('site.explorar') }}" class="inline-flex items-center rounded-2xl border border-slate-300 bg-white px-5 py-3 text-sm font-medium text-slate-800 transition hover:border-emerald-300 hover:text-emerald-700">
                                Explorar o portal
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@else
    <section class="relative isolate overflow-hidden bg-[#1f3027] text-white">
        <div class="absolute inset-0">
            <img
                src="{{ $cover }}"
                alt="{{ $rota->titulo }}"
                class="h-full w-full object-cover opacity-25"
                loading="eager"
                decoding="async"
            >
            <div class="absolute inset-0 bg-gradient-to-b from-[#1f3027]/35 via-[#1f3027]/80 to-[#1f3027]"></div>
        </div>

        <div class="relative mx-auto max-w-[1200px] px-4 pb-12 pt-8 sm:px-6 lg:px-8 lg:pb-16 lg:pt-10">
            <div class="text-sm text-white/70">
                <a href="{{ route('site.home') }}" class="transition hover:text-white">Início</a>
                <span class="mx-2">/</span>
                <span>Rota do Cacau</span>
            </div>

            <div class="mt-6 grid gap-8 lg:grid-cols-[1.2fr_.8fr] lg:items-end">
                <div class="max-w-3xl">
                    <span class="inline-flex items-center rounded-full border border-white/15 bg-white/10 px-4 py-2 text-[11px] font-semibold uppercase tracking-[0.20em] text-amber-100">
                        Vivências e memória do cacau
                    </span>

                    <h1 class="mt-5 text-3xl font-semibold leading-tight sm:text-4xl lg:text-5xl">
                        {{ $rota->titulo }}
                    </h1>

                    <p class="mt-4 max-w-2xl text-sm leading-7 text-white/85 sm:text-base">
                        {{ Str::limit(strip_tags((string) $rota->descricao), 320) }}
                    </p>

                    <div class="mt-7 flex flex-wrap gap-3">
                        @if($edicaoDestaque)
                            <a
                                href="{{ route('site.rota_do_cacau.show', $edicaoDestaque->slug) }}"
                                class="inline-flex items-center justify-center rounded-2xl bg-amber-500 px-5 py-3 text-sm font-medium text-slate-950 transition hover:bg-amber-400"
                            >
                                Ver edição em destaque
                            </a>
                        @endif

                        <a
                            href="#edicoes"
                            class="inline-flex items-center justify-center rounded-2xl border border-white/15 bg-white/10 px-5 py-3 text-sm font-medium text-white transition hover:bg-white/15"
                        >
                            Explorar edições
                        </a>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-3 lg:grid-cols-1">
                    @if($profile)
                        <div class="rounded-[28px] border border-white/10 bg-white/10 p-4 backdrop-blur">
                            <div class="text-[11px] uppercase tracking-[0.18em] text-white/75">Imagem do módulo</div>
                            <img src="{{ $profile }}" alt="{{ $rota->titulo }}" class="mt-3 h-28 w-28 rounded-[24px] object-cover border border-white/15">
                        </div>
                    @endif

                    <div class="rounded-[28px] border border-white/10 bg-white/10 p-4 backdrop-blur">
                        <div class="text-[11px] uppercase tracking-[0.18em] text-white/75">Edições publicadas</div>
                        <div class="mt-2 text-2xl font-semibold">{{ $totalEdicoes }}</div>
                        <p class="mt-2 text-sm leading-6 text-white/80">
                            Conteúdo editorial organizado por ano e com materiais próprios em cada edição.
                        </p>
                    </div>

                    <div class="rounded-[28px] border border-white/10 bg-white/10 p-4 backdrop-blur">
                        <div class="text-[11px] uppercase tracking-[0.18em] text-white/75">Publicação</div>
                        <div class="mt-2 text-base font-semibold">
                            {{ optional($rota->published_at)->format('d/m/Y') ?: 'Disponível' }}
                        </div>
                        <p class="mt-2 text-sm leading-6 text-white/80">
                            A página pública exibe somente conteúdo institucional e edições publicadas.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-[#fbfaf7] py-14">
        <div class="mx-auto max-w-[1200px] px-4 sm:px-6 lg:px-8">
            <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_300px]">
                <div class="rounded-[30px] border border-[#e8e2d9] bg-white p-6 shadow-sm sm:p-8">
                    <div class="text-[11px] font-semibold uppercase tracking-[0.20em] text-[#7b5a2c]">Apresentação</div>
                    <h2 class="mt-3 text-2xl font-semibold text-slate-900">Uma vitrine pública para a Rota do Cacau</h2>
                    <div class="mt-4 text-[15px] leading-8 text-slate-600">
                        {!! nl2br(e($rota->descricao)) !!}
                    </div>
                </div>

                <aside class="rounded-[30px] border border-[#e8e2d9] bg-white p-6 shadow-sm">
                    <div class="text-[11px] font-semibold uppercase tracking-[0.20em] text-[#7b5a2c]">Resumo rápido</div>
                    <h2 class="mt-3 text-lg font-semibold text-slate-900">Visão geral</h2>

                    <div class="mt-5 space-y-3">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <div class="text-[11px] uppercase tracking-[0.16em] text-slate-500">Slug</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">/{{ $rota->slug }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <div class="text-[11px] uppercase tracking-[0.16em] text-slate-500">Última atualização</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">
                                {{ optional($rota->updated_at)->format('d/m/Y') ?: '—' }}
                            </div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <div class="text-[11px] uppercase tracking-[0.16em] text-slate-500">Edição mais recente</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">
                                {{ $edicaoDestaque?->titulo ?: 'Ainda indisponível' }}
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    <section id="edicoes" class="bg-[#fbfaf7] pb-16">
        <div class="mx-auto max-w-[1200px] px-4 sm:px-6 lg:px-8">
            <div class="mb-6">
                <div class="text-[11px] font-semibold uppercase tracking-[0.20em] text-[#7b5a2c]">Edições publicadas</div>
                <h2 class="mt-2 text-3xl font-semibold text-slate-900">Edições da Rota do Cacau</h2>
                <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-600">
                    Cada edição concentra sua própria galeria, os vídeos publicados e a grade de patrocinadores vinculada àquele ano.
                </p>
            </div>

            @if(!$edicoes->count())
                <div class="rounded-[30px] border border-dashed border-[#d8cfbf] bg-white px-6 py-14 text-center shadow-sm">
                    <h3 class="text-xl font-semibold text-slate-900">Nenhuma edição pública disponível</h3>
                    <p class="mx-auto mt-3 max-w-2xl text-sm leading-7 text-slate-600">
                        O cadastro principal já está publicado, mas ainda não há edições visíveis no portal.
                    </p>
                </div>
            @else
                @if($edicaoDestaque)
                    <article class="overflow-hidden rounded-[32px] border border-[#e8e2d9] bg-white shadow-sm">
                        <div class="grid gap-0 lg:grid-cols-[1.15fr_.85fr]">
                            <div class="relative min-h-[320px] bg-slate-200">
                                <img
                                    src="{{ $edicaoDestaque->capa_url ?: $cover }}"
                                    alt="{{ $edicaoDestaque->titulo }}"
                                    class="h-full w-full object-cover"
                                    loading="lazy"
                                    decoding="async"
                                >
                                <div class="absolute inset-0 bg-gradient-to-t from-[#1f3027] via-transparent to-transparent"></div>
                                <div class="absolute inset-x-0 bottom-0 p-6 text-white">
                                    <div class="inline-flex rounded-full border border-white/20 bg-black/25 px-3 py-1 text-xs">
                                        Edição {{ $edicaoDestaque->ano }}
                                    </div>
                                    <h3 class="mt-3 text-2xl font-semibold">{{ $edicaoDestaque->titulo }}</h3>
                                </div>
                            </div>

                            <div class="p-6 sm:p-8">
                                <div class="grid gap-3 sm:grid-cols-3">
                                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                                        <div class="text-[11px] uppercase tracking-[0.16em] text-slate-500">Fotos</div>
                                        <div class="mt-1 text-lg font-semibold text-slate-900">{{ $edicaoDestaque->fotos_count }}</div>
                                    </div>
                                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                                        <div class="text-[11px] uppercase tracking-[0.16em] text-slate-500">Vídeos</div>
                                        <div class="mt-1 text-lg font-semibold text-slate-900">{{ $edicaoDestaque->videos_count }}</div>
                                    </div>
                                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                                        <div class="text-[11px] uppercase tracking-[0.16em] text-slate-500">Patrocinadores</div>
                                        <div class="mt-1 text-lg font-semibold text-slate-900">{{ $edicaoDestaque->patrocinadores_count }}</div>
                                    </div>
                                </div>

                                <p class="mt-5 text-sm leading-7 text-slate-600">
                                    {{ Str::limit(strip_tags((string) $edicaoDestaque->descricao), 240) }}
                                </p>

                                <div class="mt-6 flex flex-wrap gap-3">
                                    <a href="{{ route('site.rota_do_cacau.show', $edicaoDestaque->slug) }}" class="inline-flex items-center rounded-2xl bg-[#31543c] px-5 py-3 text-sm font-medium text-white transition hover:bg-[#264230]">
                                        Ver edição completa
                                    </a>
                                    <span class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-medium text-slate-700">
                                        Publicada em {{ optional($edicaoDestaque->published_at)->format('d/m/Y') ?: '—' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </article>
                @endif

                @if($outrasEdicoes->count())
                    <div class="mt-8 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                        @foreach($outrasEdicoes as $edicao)
                            <article class="overflow-hidden rounded-[28px] border border-[#e8e2d9] bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                                <div class="relative h-56 overflow-hidden bg-slate-200">
                                    <img
                                        src="{{ $edicao->capa_url ?: $cover }}"
                                        alt="{{ $edicao->titulo }}"
                                        class="h-full w-full object-cover"
                                        loading="lazy"
                                        decoding="async"
                                    >
                                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-transparent"></div>
                                    <div class="absolute inset-x-0 bottom-0 p-4 text-white">
                                        <div class="text-xs font-semibold uppercase tracking-[0.18em] text-white/75">{{ $edicao->ano }}</div>
                                        <h3 class="mt-1 text-xl font-semibold">{{ $edicao->titulo }}</h3>
                                    </div>
                                </div>

                                <div class="p-5">
                                    <p class="text-sm leading-7 text-slate-600">
                                        {{ Str::limit(strip_tags((string) $edicao->descricao), 150) }}
                                    </p>

                                    <div class="mt-5 grid grid-cols-3 gap-3">
                                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-3 py-3 text-center">
                                            <div class="text-[11px] uppercase tracking-[0.14em] text-slate-500">Fotos</div>
                                            <div class="mt-1 text-sm font-semibold text-slate-900">{{ $edicao->fotos_count }}</div>
                                        </div>
                                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-3 py-3 text-center">
                                            <div class="text-[11px] uppercase tracking-[0.14em] text-slate-500">Vídeos</div>
                                            <div class="mt-1 text-sm font-semibold text-slate-900">{{ $edicao->videos_count }}</div>
                                        </div>
                                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-3 py-3 text-center">
                                            <div class="text-[11px] uppercase tracking-[0.14em] text-slate-500">Apoios</div>
                                            <div class="mt-1 text-sm font-semibold text-slate-900">{{ $edicao->patrocinadores_count }}</div>
                                        </div>
                                    </div>

                                    <div class="mt-5">
                                        <a href="{{ route('site.rota_do_cacau.show', $edicao->slug) }}" class="inline-flex items-center rounded-2xl bg-[#31543c] px-4 py-2.5 text-sm font-medium text-white transition hover:bg-[#264230]">
                                            Ver mais
                                        </a>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            @endif
        </div>
    </section>
@endif
@endsection
