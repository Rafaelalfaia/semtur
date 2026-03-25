@extends('site.layouts.app')

@section('title', $video->titulo . ' • Vídeos • Visit Altamira')
@section('meta.description', \Illuminate\Support\Str::limit(strip_tags((string) $video->descricao), 160))
@section('meta.image', $video->capa_url ?: asset('imagens/altamira.jpg'))

@section('site.content')
@php
    use Illuminate\Support\Str;

    $cover = $video->capa_url ?: asset('imagens/altamira.jpg');
    $embedUrl = $video->embed_url;
@endphp

<section class="relative isolate overflow-hidden bg-[#07131C] text-white">
    <div class="absolute inset-0">
        <img
            src="{{ $cover }}"
            alt="{{ $video->titulo }}"
            class="h-full w-full object-cover opacity-30"
            loading="eager"
            decoding="async"
        >
        <div class="absolute inset-0 bg-gradient-to-b from-[#07131C]/55 via-[#07131C]/82 to-[#07131C]"></div>
    </div>

    <div class="relative mx-auto max-w-7xl px-4 pb-14 pt-10 sm:px-6 lg:px-8 lg:pb-20 lg:pt-14">
        <div class="max-w-3xl">
            <span class="inline-flex items-center rounded-full border border-white/15 bg-white/10 px-4 py-2 text-[11px] font-semibold uppercase tracking-[0.20em] text-cyan-100">
                Vídeo
            </span>

            <h1 class="mt-5 text-3xl font-semibold leading-tight sm:text-4xl lg:text-5xl">
                {{ $video->titulo }}
            </h1>

            <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-200 sm:text-base">
                {{ Str::limit(strip_tags((string) $video->descricao), 240) }}
            </p>

            <div class="mt-7 flex flex-wrap gap-3">
                <a
                    href="#visualizacao"
                    class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-medium text-white transition hover:bg-cyan-500"
                >
                    Assistir agora
                </a>

                <a
                    href="{{ route('site.videos') }}"
                    class="inline-flex items-center justify-center rounded-2xl border border-white/15 bg-white/10 px-5 py-3 text-sm font-medium text-white transition hover:bg-white/15"
                >
                    Voltar para vídeos
                </a>
            </div>
        </div>

        <div class="mt-8 grid gap-4 sm:grid-cols-3 lg:mt-10 lg:max-w-3xl">
            <div class="rounded-3xl border border-white/10 bg-white/10 p-5 backdrop-blur-sm">
                <div class="text-[11px] uppercase tracking-[0.18em] text-slate-300">Tipo</div>
                <div class="mt-2 text-base font-semibold text-white">Vídeo</div>
            </div>

            <div class="rounded-3xl border border-white/10 bg-white/10 p-5 backdrop-blur-sm">
                <div class="text-[11px] uppercase tracking-[0.18em] text-slate-300">Publicado</div>
                <div class="mt-2 text-base font-semibold text-white">
                    {{ optional($video->published_at)->format('d/m/Y') ?: 'Disponível' }}
                </div>
            </div>

            <div class="rounded-3xl border border-white/10 bg-white/10 p-5 backdrop-blur-sm">
                <div class="text-[11px] uppercase tracking-[0.18em] text-slate-300">Acesso</div>
                <div class="mt-2 text-base font-semibold text-white">Visualização no portal</div>
            </div>
        </div>
    </div>
</section>

<section class="bg-white">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-12">
        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
            <div class="space-y-6">
                <section class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm sm:p-7">
                    <div class="text-[11px] font-semibold uppercase tracking-[0.20em] text-cyan-700">
                        Sobre o vídeo
                    </div>
                    <h2 class="mt-2 text-2xl font-semibold text-slate-900">
                        Informações do conteúdo
                    </h2>

                    <div class="mt-4 space-y-4 text-[15px] leading-8 text-slate-600">
                        {!! nl2br(e($video->descricao)) !!}
                    </div>
                </section>

                <section id="visualizacao" class="rounded-[28px] border border-slate-200 bg-[#F4FBFD] p-6 shadow-sm sm:p-7">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <div class="text-[11px] font-semibold uppercase tracking-[0.20em] text-cyan-700">
                                Visualização
                            </div>
                            <h2 class="mt-2 text-2xl font-semibold text-slate-900">
                                Exibição do vídeo
                            </h2>
                            <p class="mt-2 text-sm leading-7 text-slate-500">
                                O conteúdo abaixo é carregado dentro do portal para facilitar a consulta do visitante.
                            </p>
                        </div>

                        <a
                            href="{{ $video->link_acesso }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                        >
                            Abrir no Google Drive
                        </a>
                    </div>

                    @if($embedUrl)
                        <div class="mt-6 overflow-hidden rounded-[24px] border border-slate-200 bg-white">
                            <iframe
                                src="{{ $embedUrl }}"
                                title="{{ $video->titulo }}"
                                class="h-[75vh] min-h-[620px] w-full"
                                loading="lazy"
                                referrerpolicy="strict-origin-when-cross-origin"
                                allowfullscreen
                            ></iframe>
                        </div>

                        <p class="mt-3 text-xs leading-6 text-slate-500">
                            Se o visualizador não carregar corretamente no seu dispositivo, use o botão
                            “Abrir no Google Drive”.
                        </p>
                    @else
                        <div class="mt-6 rounded-[24px] border border-dashed border-slate-300 bg-white px-6 py-12 text-center">
                            <h3 class="text-lg font-semibold text-slate-900">
                                Preview indisponível
                            </h3>
                            <p class="mt-2 text-sm leading-7 text-slate-500">
                                Este vídeo não possui um formato compatível de visualização interna.
                            </p>

                            <div class="mt-4">
                                <a
                                    href="{{ $video->link_acesso }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-medium text-white transition hover:bg-cyan-500"
                                >
                                    Abrir vídeo
                                </a>
                            </div>
                        </div>
                    @endif
                </section>
            </div>

            <aside class="space-y-6 lg:sticky lg:top-6 lg:self-start">
                <section class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-[11px] font-semibold uppercase tracking-[0.20em] text-cyan-700">
                        Resumo rápido
                    </div>
                    <h2 class="mt-2 text-lg font-semibold text-slate-900">
                        Visão geral
                    </h2>

                    <div class="mt-5 space-y-3">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <div class="text-[11px] uppercase tracking-[0.16em] text-slate-500">Tipo</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">Vídeo</div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <div class="text-[11px] uppercase tracking-[0.16em] text-slate-500">Publicado em</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">
                                {{ optional($video->published_at)->format('d/m/Y') ?: '—' }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-5">
                        <a
                            href="{{ route('site.videos') }}"
                            class="inline-flex w-full items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                        >
                            Ver mais vídeos
                        </a>
                    </div>
                </section>

                @if(($relacionados ?? collect())->count())
                    <section class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="text-[11px] font-semibold uppercase tracking-[0.20em] text-cyan-700">
                            Relacionados
                        </div>
                        <h2 class="mt-2 text-lg font-semibold text-slate-900">
                            Mais vídeos
                        </h2>

                        <div class="mt-5 space-y-4">
                            @foreach($relacionados as $item)
                                <a
                                    href="{{ route('site.videos.show', $item->slug) }}"
                                    class="block rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:border-cyan-300 hover:bg-cyan-50"
                                >
                                    <div class="text-xs font-semibold uppercase tracking-[0.16em] text-cyan-700">
                                        Vídeo
                                    </div>
                                    <div class="mt-2 text-sm font-semibold text-slate-900">
                                        {{ $item->titulo }}
                                    </div>
                                    <p class="mt-2 text-sm leading-6 text-slate-600">
                                        {{ Str::limit(strip_tags((string) $item->descricao), 90) }}
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
