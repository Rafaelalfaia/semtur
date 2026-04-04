@extends('site.layouts.app')

@section('title', $roteiro->titulo . ' • ' . ui_text('ui.itineraries.title_suffix'))
@section('meta.description', \Illuminate\Support\Str::limit(strip_tags((string) ($roteiro->seo_description ?: $roteiro->resumo ?: $roteiro->descricao)), 160))
@section('meta.image', $roteiro->capa_url ?: asset('imagens/altamira.jpg'))

@section('site.content')
@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    $cover = $heroMedia?->url ?: ($roteiro->capa_url ?: asset('imagens/altamira.jpg'));

    $pointUrl = function ($ponto) {
        foreach (['site.pontos.show', 'site.ponto.show'] as $routeName) {
            if (Route::has($routeName)) {
                return route($routeName, $ponto->slug ?? $ponto->id);
            }
        }

        return $ponto->maps_url ?? null;
    };

    $companyUrl = function ($empresa) {
        foreach (['site.empresas.show', 'site.empresa.show'] as $routeName) {
            if (Route::has($routeName)) {
                return route($routeName, $empresa->slug ?? $empresa->id);
            }
        }

        return $empresa->maps_url ?? null;
    };

    $pointCover = function ($ponto) {
        if (!empty($ponto->capa_url)) {
            return $ponto->capa_url;
        }

        if (!empty($ponto->capa_path)) {
            return Storage::disk('public')->url($ponto->capa_path);
        }

        return asset('imagens/altamira.jpg');
    };

    $companyCover = function ($empresa) {
        if (!empty($empresa->foto_capa_url)) {
            return $empresa->foto_capa_url;
        }

        if (!empty($empresa->foto_capa_path)) {
            return Storage::disk('public')->url($empresa->foto_capa_path);
        }

        if (!empty($empresa->capa_path)) {
            return Storage::disk('public')->url($empresa->capa_path);
        }

        return asset('imagens/altamira.jpg');
    };

    $pontosUnicos = collect($roteiro->etapas ?? [])
        ->flatMap(fn ($etapa) => collect($etapa->pontos ?? []))
        ->filter(fn ($item) => $item && $item->pontoTuristico)
        ->map(fn ($item) => $item->pontoTuristico)
        ->unique('id')
        ->values();

    $empresasAgrupadas = collect($roteiro->empresasSugestao ?? [])
        ->filter(fn ($item) => $item && $item->empresa)
        ->groupBy(fn ($item) => $item->tipo_sugestao_label ?: ui_text('ui.itineraries.suggestions'));

    $temMapa = $pontosUnicos->contains(fn ($p) => filled($p->lat) && filled($p->lng));
    $heroBadge = $heroTranslation?->eyebrow ?: $roteiro->duracao_label;
    $heroTitle = $heroTranslation?->titulo ?: $roteiro->titulo;
    $heroSubtitle = $heroTranslation?->lead ?: ($roteiro->resumo ?: null);
    $heroPrimaryLabel = $heroTranslation?->cta_label ?: ui_text('ui.explore.view_place');
    $heroPrimaryHref = $heroTranslation?->cta_href ?: '#percurso';
@endphp

<section class="relative overflow-hidden bg-slate-950 text-white">
    <div class="absolute inset-0">
        <img
            src="{{ $cover }}"
            alt="{{ $roteiro->titulo }}"
            class="h-full w-full object-cover opacity-30"
            loading="eager"
            decoding="async"
        >
        <div class="absolute inset-0 bg-gradient-to-b from-slate-950/35 via-slate-950/80 to-slate-950"></div>
    </div>

    <div class="relative mx-auto max-w-[1200px] px-4 pb-10 pt-8 sm:px-6 lg:px-8 lg:pb-14 lg:pt-10">
        <a
            href="{{ localized_route('site.roteiros') }}"
            class="inline-flex items-center rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm text-slate-200 transition hover:bg-white/10"
        >
            ← {{ ui_text('ui.common.back_to_itineraries') }}
        </a>

        <div class="mt-6 grid gap-8 lg:grid-cols-[1.2fr_.8fr] lg:items-end">
            <div class="max-w-3xl">
                <div class="flex flex-wrap gap-2">
                    <span class="rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs text-white">
                        {{ $heroBadge }}
                    </span>
                    <span class="rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs text-white">
                        {{ $roteiro->perfil_label }}
                    </span>
                    @if($roteiro->intensidade_label)
                        <span class="rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs text-white">
                            {{ $roteiro->intensidade_label }}
                        </span>
                    @endif
                </div>

                <h1 class="mt-4 text-3xl font-semibold leading-tight sm:text-4xl lg:text-5xl">
                    {{ $heroTitle }}
                </h1>

                @if($heroSubtitle)
                    <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-200 sm:text-base">
                        {{ $heroSubtitle }}
                    </p>
                @endif

                <div class="mt-6 flex flex-wrap gap-3">
                    <a
                        href="{{ $heroPrimaryHref }}"
                        class="inline-flex items-center rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-medium text-white transition hover:bg-emerald-500"
                    >
                        {{ $heroPrimaryLabel }}
                    </a>

                    @if($temMapa)
                        <a
                            href="#locais"
                            class="inline-flex items-center rounded-2xl border border-white/15 bg-white/10 px-5 py-3 text-sm font-medium text-white transition hover:bg-white/15"
                        >
                            {{ ui_text('ui.itineraries.route_places') }}
                        </a>
                    @endif
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-2">
                <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                    <div class="text-[11px] uppercase tracking-[0.16em] text-emerald-100/80">{{ ui_text('ui.itineraries.stages') }}</div>
                    <div class="mt-2 text-2xl font-semibold">{{ collect($roteiro->etapas ?? [])->count() }}</div>
                    <p class="mt-1 text-sm text-slate-200/90">{{ ui_text('ui.itineraries.stages_copy') }}</p>
                </div>

                <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                    <div class="text-[11px] uppercase tracking-[0.16em] text-emerald-100/80">{{ ui_text('ui.itineraries.points') }}</div>
                    <div class="mt-2 text-2xl font-semibold">{{ $pontosUnicos->count() }}</div>
                    <p class="mt-1 text-sm text-slate-200/90">{{ ui_text('ui.itineraries.points_copy') }}</p>
                </div>

                <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                    <div class="text-[11px] uppercase tracking-[0.16em] text-emerald-100/80">{{ ui_text('ui.itineraries.companies') }}</div>
                    <div class="mt-2 text-2xl font-semibold">{{ collect($roteiro->empresasSugestao ?? [])->count() }}</div>
                    <p class="mt-1 text-sm text-slate-200/90">{{ ui_text('ui.itineraries.companies_copy') }}</p>
                </div>

                <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                    <div class="text-[11px] uppercase tracking-[0.16em] text-emerald-100/80">{{ ui_text('ui.itineraries.best_for') }}</div>
                    <div class="mt-2 text-base font-semibold leading-6">
                        {{ $roteiro->publico_label ?: ui_text('ui.itineraries.general_visitors') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="bg-slate-950 pb-16 text-white">
    <div class="mx-auto max-w-[1200px] px-4 sm:px-6 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_340px]">
            <div class="space-y-6">
                <section class="rounded-[28px] border border-white/10 bg-white/[0.03] p-6 sm:p-7">
                    <div class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-300/80">
                        {{ ui_text('ui.itineraries.about_itinerary') }}
                    </div>
                    <h2 class="mt-2 text-2xl font-semibold text-slate-100">
                        {{ ui_text('ui.itineraries.what_to_expect') }}
                    </h2>

                    @if($roteiro->descricao)
                        <div class="mt-4 prose prose-invert max-w-none prose-p:leading-8 prose-p:text-slate-300">
                            {!! nl2br(e($roteiro->descricao)) !!}
                        </div>
                    @else
                        <p class="mt-4 text-sm leading-8 text-slate-300">
                            Este roteiro foi organizado para ajudar o visitante a viver Altamira com mais clareza,
                            conectando paisagens, cultura, deslocamento e experiências que fazem sentido dentro do mesmo percurso.
                        </p>
                    @endif
                </section>

                <section id="percurso" class="rounded-[28px] border border-white/10 bg-white/[0.03] p-6 sm:p-7">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-300/80">
                                {{ ui_text('ui.itineraries.your_route') }}
                            </div>
                            <h2 class="mt-2 text-2xl font-semibold text-slate-100">
                                {{ ui_text('ui.itineraries.route_stages') }}
                            </h2>
                        </div>

                        <div class="text-sm text-slate-400">
                            {{ collect($roteiro->etapas ?? [])->count() }} {{ collect($roteiro->etapas ?? [])->count() === 1 ? 'etapa' : 'etapas' }}
                        </div>
                    </div>

                    <div class="mt-7 space-y-6">
                        @foreach(($roteiro->etapas ?? []) as $index => $etapa)
                            <article class="rounded-[26px] border border-white/10 bg-slate-950/40 p-5 sm:p-6">
                                <div class="grid gap-5 lg:grid-cols-[84px_minmax(0,1fr)]">
                                    <div class="flex flex-col items-start">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-500/15 text-lg font-semibold text-emerald-300">
                                            {{ $index + 1 }}
                                        </div>
                                        @if(!$loop->last)
                                            <div class="ml-[23px] mt-3 hidden h-full min-h-[56px] w-px bg-white/10 lg:block"></div>
                                        @endif
                                    </div>

                                    <div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h3 class="text-xl font-semibold text-slate-100">
                                                {{ $etapa->titulo }}
                                            </h3>

                                            <span class="rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs text-slate-300">
                                                {{ $etapa->tipo_bloco_label }}
                                            </span>
                                        </div>

                                        @if($etapa->subtitulo)
                                            <p class="mt-2 text-sm font-medium text-emerald-300/90">
                                                {{ $etapa->subtitulo }}
                                            </p>
                                        @endif

                                        @if($etapa->descricao)
                                            <p class="mt-3 text-sm leading-8 text-slate-300">
                                                {{ $etapa->descricao }}
                                            </p>
                                        @endif

                                        @if(collect($etapa->pontos ?? [])->count())
                                            <div class="mt-5 grid gap-4 md:grid-cols-2">
                                                @foreach(($etapa->pontos ?? []) as $item)
                                                    @continue(!$item->pontoTuristico)
                                                    @php
                                                        $ponto = $item->pontoTuristico;
                                                        $pontoHref = $pointUrl($ponto);
                                                    @endphp

                                                    <div class="overflow-hidden rounded-[24px] border border-white/10 bg-white/[0.03]">
                                                        <div class="relative h-44 overflow-hidden bg-slate-900">
                                                            <img
                                                                src="{{ $pointCover($ponto) }}"
                                                                alt="{{ $ponto->nome }}"
                                                                class="h-full w-full object-cover"
                                                                loading="lazy"
                                                                decoding="async"
                                                            >
                                                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-transparent to-transparent"></div>

                                                            <div class="absolute inset-x-0 bottom-0 p-3">
                                                                <div class="flex flex-wrap gap-2">
                                                                    @if($item->tempo_estimado_min)
                                                                        <span class="rounded-full border border-white/15 bg-black/35 px-2.5 py-1 text-[11px] text-white">
                                                                            {{ $item->tempo_estimado_min }} min
                                                                        </span>
                                                                    @endif

                                                                    @if($item->destaque)
                                                                        <span class="rounded-full border border-emerald-400/30 bg-emerald-500/15 px-2.5 py-1 text-[11px] text-emerald-200">
                                                                            {{ ui_text('ui.itineraries.highlight') }}
                                                                        </span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="p-4">
                                                            <h4 class="text-base font-semibold text-slate-100">
                                                                {{ $ponto->nome }}
                                                            </h4>

                                                            <p class="mt-2 text-sm leading-7 text-slate-300">
                                                                {{ Str::limit(strip_tags((string) ($item->observacao_curta ?: $ponto->descricao)), 120) }}
                                                            </p>

                                                            <div class="mt-4 flex flex-wrap gap-2">
                                                                @if($pontoHref)
                                                                    <a
                                                                        href="{{ $pontoHref }}"
                                                                        @if(Str::startsWith($pontoHref, ['http://', 'https://'])) target="_blank" rel="noopener noreferrer" @endif
                                                                        class="inline-flex items-center rounded-2xl bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-500"
                                                                    >
                                                                        {{ ui_text('ui.itineraries.view_point') }}
                                                                    </a>
                                                                @endif

                                                                @if($ponto->maps_url)
                                                                    <a
                                                                        href="{{ $ponto->maps_url }}"
                                                                        target="_blank"
                                                                        rel="noopener noreferrer"
                                                                        class="inline-flex items-center rounded-2xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm font-medium text-slate-200 transition hover:bg-white/10"
                                                                    >
                                                                        {{ ui_text('ui.itineraries.how_to_get') }}
                                                                    </a>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>

                @if($empresasAgrupadas->count())
                    <section class="rounded-[28px] border border-white/10 bg-white/[0.03] p-6 sm:p-7">
                        <div class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-300/80">
                            {{ ui_text('ui.itineraries.suggested_companies') }}
                        </div>
                        <h2 class="mt-2 text-2xl font-semibold text-slate-100">
                            {{ ui_text('ui.itineraries.supports_and_experiences') }}
                        </h2>
                        <p class="mt-3 text-sm leading-7 text-slate-400">
                            Aqui entram somente empresas selecionadas para combinar com esse percurso e ajudar o visitante a montar melhor a experiência.
                        </p>

                        <div class="mt-7 space-y-8">
                            @foreach($empresasAgrupadas as $grupo => $itens)
                                <div>
                                    <div class="mb-4 flex items-center justify-between gap-3">
                                        <h3 class="text-lg font-semibold text-slate-100">
                                            {{ $grupo }}
                                        </h3>
                                        <div class="text-sm text-slate-500">
                                            {{ $itens->count() }} {{ $itens->count() === 1 ? ui_text('ui.itineraries.suggestion_single') : ui_text('ui.itineraries.suggestion_plural') }}
                                        </div>
                                    </div>

                                    <div class="grid gap-4 md:grid-cols-2">
                                        @foreach($itens as $item)
                                            @php
                                                $empresa = $item->empresa;
                                                $empresaHref = $companyUrl($empresa);
                                            @endphp

                                            <article class="overflow-hidden rounded-[24px] border border-white/10 bg-slate-950/40">
                                                <div class="relative h-44 overflow-hidden bg-slate-900">
                                                    <img
                                                        src="{{ $companyCover($empresa) }}"
                                                        alt="{{ $empresa->nome }}"
                                                        class="h-full w-full object-cover"
                                                        loading="lazy"
                                                        decoding="async"
                                                    >
                                                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-transparent to-transparent"></div>

                                                    <div class="absolute inset-x-0 bottom-0 p-3">
                                                        <div class="flex flex-wrap gap-2">
                                                            <span class="rounded-full border border-white/15 bg-black/35 px-2.5 py-1 text-[11px] text-white">
                                                                {{ $item->tipo_sugestao_label }}
                                                            </span>

                                                            @if($item->destaque)
                                                                <span class="rounded-full border border-emerald-400/30 bg-emerald-500/15 px-2.5 py-1 text-[11px] text-emerald-200">
                                                                    {{ ui_text('ui.itineraries.highlight') }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="p-4">
                                                    <h4 class="text-base font-semibold text-slate-100">
                                                        {{ $empresa->nome }}
                                                    </h4>

                                                    @if($empresa->cidade || $empresa->bairro)
                                                        <div class="mt-2 text-sm text-slate-400">
                                                            {{ collect([$empresa->bairro, $empresa->cidade])->filter()->implode(' • ') }}
                                                        </div>
                                                    @endif

                                                    <p class="mt-3 text-sm leading-7 text-slate-300">
                                                        {{ Str::limit(strip_tags((string) ($item->observacao_curta ?: $empresa->descricao)), 125) }}
                                                    </p>

                                                    <div class="mt-4 flex flex-wrap gap-2">
                                                        @if($empresaHref)
                                                            <a
                                                                href="{{ $empresaHref }}"
                                                                @if(Str::startsWith($empresaHref, ['http://', 'https://'])) target="_blank" rel="noopener noreferrer" @endif
                                                                class="inline-flex items-center rounded-2xl bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-500"
                                                            >
                                                                {{ ui_text('ui.itineraries.view_company') }}
                                                            </a>
                                                        @endif

                                                        @if($empresa->maps_url)
                                                            <a
                                                                href="{{ $empresa->maps_url }}"
                                                                target="_blank"
                                                                rel="noopener noreferrer"
                                                                class="inline-flex items-center rounded-2xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm font-medium text-slate-200 transition hover:bg-white/10"
                                                            >
                                                                {{ ui_text('ui.common.open_map') }}
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </article>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                <section id="locais" class="rounded-[28px] border border-white/10 bg-white/[0.03] p-6 sm:p-7">
                    <div class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-300/80">
                        {{ ui_text('ui.itineraries.route_places') }}
                    </div>
                    <h2 class="mt-2 text-2xl font-semibold text-slate-100">
                        {{ ui_text('ui.itineraries.places_in_route') }}
                    </h2>

                    <div class="mt-6 grid gap-4 md:grid-cols-2">
                        @foreach($pontosUnicos as $ponto)
                            <div class="rounded-[24px] border border-white/10 bg-slate-950/40 p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <h3 class="text-base font-semibold text-slate-100">
                                            {{ $ponto->nome }}
                                        </h3>
                                        <div class="mt-1 text-sm text-slate-400">
                                            {{ collect([$ponto->bairro, $ponto->cidade])->filter()->implode(' • ') ?: 'Altamira' }}
                                        </div>
                                    </div>

                                    @if(filled($ponto->lat) && filled($ponto->lng))
                                        <div class="rounded-full border border-emerald-400/20 bg-emerald-500/10 px-3 py-1 text-[11px] text-emerald-200">
                                            {{ number_format((float) $ponto->lat, 3, ',', '.') }},
                                            {{ number_format((float) $ponto->lng, 3, ',', '.') }}
                                        </div>
                                    @endif
                                </div>

                                <p class="mt-3 text-sm leading-7 text-slate-300">
                                    {{ Str::limit(strip_tags((string) $ponto->descricao), 120) }}
                                </p>

                                <div class="mt-4 flex flex-wrap gap-2">
                                    @php $pontoHref = $pointUrl($ponto); @endphp

                                    @if($pontoHref)
                                        <a
                                            href="{{ $pontoHref }}"
                                            @if(Str::startsWith($pontoHref, ['http://', 'https://'])) target="_blank" rel="noopener noreferrer" @endif
                                            class="inline-flex items-center rounded-2xl bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-500"
                                        >
                                            {{ ui_text('ui.itineraries.view_details') }}
                                        </a>
                                    @endif

                                    @if($ponto->maps_url)
                                        <a
                                            href="{{ $ponto->maps_url }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex items-center rounded-2xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm font-medium text-slate-200 transition hover:bg-white/10"
                                        >
                                            {{ ui_text('ui.itineraries.how_to_get') }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                @if(($relacionados ?? collect())->count())
                    <section class="rounded-[28px] border border-white/10 bg-white/[0.03] p-6 sm:p-7">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <div class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-300/80">
                                    {{ ui_text('ui.itineraries.continue_exploring') }}
                                </div>
                                <h2 class="mt-2 text-2xl font-semibold text-slate-100">
                                    {{ ui_text('ui.itineraries.other_itineraries') }}
                                </h2>
                            </div>

                            <a
                                href="{{ localized_route('site.roteiros') }}"
                                class="text-sm text-emerald-300 transition hover:text-emerald-200"
                            >
                                {{ ui_text('ui.itineraries.view_all') }}
                            </a>
                        </div>

                        <div class="mt-6 grid gap-4 md:grid-cols-3">
                            @foreach($relacionados as $item)
                                <article class="overflow-hidden rounded-[24px] border border-white/10 bg-slate-950/40">
                                    <div class="relative h-40 overflow-hidden bg-slate-900">
                                        <img
                                            src="{{ $item->capa_url ?: asset('imagens/altamira.jpg') }}"
                                            alt="{{ $item->titulo }}"
                                            class="h-full w-full object-cover"
                                            loading="lazy"
                                            decoding="async"
                                        >
                                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-transparent to-transparent"></div>
                                    </div>

                                    <div class="p-4">
                                        <div class="flex flex-wrap gap-2">
                                            <span class="rounded-full border border-white/10 bg-white/5 px-2.5 py-1 text-[11px] text-slate-300">
                                                {{ $item->duracao_label }}
                                            </span>
                                            <span class="rounded-full border border-white/10 bg-white/5 px-2.5 py-1 text-[11px] text-slate-300">
                                                {{ $item->perfil_label }}
                                            </span>
                                        </div>

                                        <h3 class="mt-3 text-base font-semibold text-slate-100">
                                            {{ $item->titulo }}
                                        </h3>

                                        <p class="mt-2 text-sm leading-7 text-slate-300">
                                            {{ Str::limit($item->resumo, 96) }}
                                        </p>

                                        <div class="mt-4">
                                            <a
                                                href="{{ localized_route('site.roteiros.show', ['slug' => $item->slug]) }}"
                                                class="inline-flex items-center rounded-2xl bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-500"
                                            >
                                                {{ ui_text('ui.itineraries.view_itinerary') }}
                                            </a>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>

            <aside class="space-y-6 lg:sticky lg:top-6 lg:self-start">
                <section class="rounded-[28px] border border-white/10 bg-white/[0.03] p-5">
                    <div class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-300/80">
                        {{ ui_text('ui.itineraries.useful_information') }}
                    </div>
                    <h2 class="mt-2 text-lg font-semibold text-slate-100">
                        Antes de sair
                    </h2>

                    <div class="mt-5 space-y-3">
                        <div class="rounded-2xl border border-white/10 bg-slate-950/40 p-4">
                            <div class="text-[11px] uppercase tracking-[0.16em] text-slate-500">{{ ui_text('ui.itineraries.best_time') }}</div>
                            <div class="mt-1 text-sm font-medium text-slate-100">
                                {{ $roteiro->melhor_epoca ?: ui_text('ui.itineraries.local_conditions') }}
                            </div>
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-slate-950/40 p-4">
                            <div class="text-[11px] uppercase tracking-[0.16em] text-slate-500">{{ ui_text('ui.itineraries.transport') }}</div>
                            <div class="mt-1 text-sm font-medium text-slate-100">
                                {{ $roteiro->deslocamento ?: ui_text('ui.itineraries.transport_copy') }}
                            </div>
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-slate-950/40 p-4">
                            <div class="text-[11px] uppercase tracking-[0.16em] text-slate-500">{{ ui_text('ui.itineraries.ideal_profile') }}</div>
                            <div class="mt-1 text-sm font-medium text-slate-100">
                                {{ $roteiro->publico_label ?: ui_text('ui.itineraries.general_visitors') }}
                            </div>
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-slate-950/40 p-4">
                            <div class="text-[11px] uppercase tracking-[0.16em] text-slate-500">{{ ui_text('ui.itineraries.intensity') }}</div>
                            <div class="mt-1 text-sm font-medium text-slate-100">
                                {{ $roteiro->intensidade_label ?: ui_text('ui.itineraries.not_informed') }}
                            </div>
                        </div>
                    </div>
                </section>

                <section class="rounded-[28px] border border-white/10 bg-white/[0.03] p-5">
                    <div class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-300/80">
                        {{ ui_text('ui.itineraries.quick_summary') }}
                    </div>
                    <h2 class="mt-2 text-lg font-semibold text-slate-100">
                        {{ ui_text('ui.itineraries.route_overview') }}
                    </h2>

                    <div class="mt-5 space-y-3">
                        @foreach(($roteiro->etapas ?? []) as $index => $etapa)
                            <div class="rounded-2xl border border-white/10 bg-slate-950/40 px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-500/15 text-sm font-semibold text-emerald-300">
                                        {{ $index + 1 }}
                                    </div>

                                    <div class="min-w-0">
                                        <div class="truncate text-sm font-medium text-slate-100">
                                            {{ $etapa->titulo }}
                                        </div>
                                        <div class="text-xs text-slate-500">
                                            {{ collect($etapa->pontos ?? [])->count() }} {{ collect($etapa->pontos ?? [])->count() === 1 ? 'ponto' : 'pontos' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-5">
                        <a
                            href="{{ localized_route('site.roteiros') }}"
                            class="inline-flex w-full items-center justify-center rounded-2xl border border-white/10 bg-white/5 px-5 py-3 text-sm font-medium text-slate-200 transition hover:bg-white/10"
                        >
                            {{ ui_text('ui.itineraries.view_more_itineraries') }}
                        </a>
                    </div>
                </section>
            </aside>
        </div>
    </div>
</section>

@include('site.partials._content_editor', [
    'editorTitle' => $heroTitle,
    'editorPage' => 'site.roteiros.show',
    'editorKey' => 'hero',
    'editorLabel' => 'Hero detalhe de roteiro',
    'editorLocale' => route_locale(),
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
        'seo_description' => \Illuminate\Support\Str::limit(strip_tags((string) ($roteiro->seo_description ?: $roteiro->resumo ?: $roteiro->descricao)), 160),
    ],
])
@endsection
