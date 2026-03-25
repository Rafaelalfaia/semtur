@extends('site.layouts.app')

@section('title', $espaco->nome . ' • ' . $espaco->tipo_label)
@section('meta.description', \Illuminate\Support\Str::limit(strip_tags((string) ($espaco->resumo ?: $espaco->descricao)), 160))
@section('meta.image', $espaco->capa_url ?: (optional($espaco->midias->first())->url ?: asset('imagens/altamira.jpg')))

@section('site.content')
@php
    $fallback = asset('imagens/altamira.jpg');
    $capa = $espaco->capa_url ?: optional($espaco->midias->first())->url ?: $fallback;
    $galeria = collect($espaco->midias ?? []);
@endphp

<section class="bg-slate-950 text-white">
    <div class="mx-auto w-full max-w-[1200px] px-4 py-14 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-[1.2fr_0.8fr] lg:items-center">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full border border-white/15 bg-white/10 px-4 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-sky-200">
                        {{ $espaco->tipo_label }}
                    </span>

                    @if ($espaco->agendamento_disponivel)
                        <span class="rounded-full border border-emerald-400/20 bg-emerald-400/10 px-4 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-emerald-200">
                            Agendamento disponível
                        </span>
                    @endif
                </div>

                <h1 class="mt-5 text-3xl font-bold tracking-tight sm:text-5xl">
                    {{ $espaco->nome }}
                </h1>

                @if ($espaco->resumo)
                    <p class="mt-4 max-w-3xl text-sm leading-7 text-slate-300 sm:text-base">
                        {{ $espaco->resumo }}
                    </p>
                @endif

                <div class="mt-6 flex flex-wrap gap-3">
                    @if ($espaco->agendamento_disponivel)
                        <a
                            href="{{ route('site.museus.agendar', $espaco->slug) }}"
                            class="inline-flex items-center rounded-2xl bg-white px-5 py-3 text-sm font-semibold text-slate-900 hover:bg-slate-100"
                        >
                            Solicitar agendamento
                        </a>
                    @endif

                    <a
                        href="{{ route('site.museus') }}"
                        class="inline-flex items-center rounded-2xl border border-white/15 bg-white/5 px-5 py-3 text-sm font-semibold text-white hover:bg-white/10"
                    >
                        Voltar para listagem
                    </a>
                </div>
            </div>

            <div class="overflow-hidden rounded-[28px] border border-white/10 bg-white/5 shadow-2xl">
                <img src="{{ $capa }}" alt="{{ $espaco->nome }}" class="h-full w-full object-cover">
            </div>
        </div>
    </div>
</section>

<section class="bg-white">
    <div class="mx-auto grid w-full max-w-[1200px] grid-cols-1 gap-8 px-4 py-10 sm:px-6 lg:grid-cols-[1fr_360px] lg:px-8">
        <div class="space-y-8">
            @if ($espaco->descricao)
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-xl font-bold tracking-tight text-slate-900">Sobre o espaço</h2>
                    <div class="prose prose-slate mt-4 max-w-none">
                        {!! nl2br(e($espaco->descricao)) !!}
                    </div>
                </div>
            @endif

            @if ($galeria->count())
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-xl font-bold tracking-tight text-slate-900">Galeria</h2>

                    <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        @foreach ($galeria as $midia)
                            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-50">
                                <img
                                    src="{{ $midia->url }}"
                                    alt="{{ $midia->alt ?: $espaco->nome }}"
                                    class="h-64 w-full object-cover"
                                >
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($relacionados->count())
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-xl font-bold tracking-tight text-slate-900">Outros espaços relacionados</h2>

                    <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($relacionados as $item)
                            @php
                                $thumb = $item->capa_url ?: optional($item->midias->first())->url ?: $fallback;
                            @endphp

                            <a
                                href="{{ route('site.museus.show', $item->slug) }}"
                                class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-md"
                            >
                                <div class="aspect-[16/10] bg-slate-100">
                                    <img src="{{ $thumb }}" alt="{{ $item->nome }}" class="h-full w-full object-cover">
                                </div>

                                <div class="p-4">
                                    <div class="text-xs font-semibold uppercase tracking-[0.16em] text-sky-700">
                                        {{ $item->tipo_label }}
                                    </div>
                                    <h3 class="mt-2 text-sm font-semibold text-slate-900">{{ $item->nome }}</h3>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <aside class="space-y-6">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold tracking-tight text-slate-900">Informações</h2>

                <div class="mt-5 space-y-4 text-sm text-slate-600">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Tipo</div>
                        <div class="mt-1 font-medium text-slate-900">{{ $espaco->tipo_label }}</div>
                    </div>

                    @if ($espaco->endereco)
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Endereço</div>
                            <div class="mt-1 font-medium text-slate-900">{{ $espaco->endereco }}</div>
                        </div>
                    @endif

                    @if ($espaco->bairro || $espaco->cidade)
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Localidade</div>
                            <div class="mt-1 font-medium text-slate-900">
                                {{ trim(($espaco->bairro ? $espaco->bairro . ' • ' : '') . ($espaco->cidade ?: 'Altamira')) }}
                            </div>
                        </div>
                    @endif
                </div>

                @if ($espaco->maps_url)
                    <div class="mt-6">
                        <a
                            href="{{ $espaco->maps_url }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex w-full items-center justify-center rounded-2xl border border-slate-300 px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50"
                        >
                            Ver no mapa
                        </a>
                    </div>
                @endif
            </div>

            @if ($espaco->horarios->count())
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold tracking-tight text-slate-900">Horários disponíveis</h2>

                    <div class="mt-5 space-y-3">
                        @foreach ($espaco->horarios as $horario)
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <div class="font-semibold text-slate-900">{{ $horario->dia_label }}</div>
                                        <div class="mt-1 text-sm text-slate-600">{{ $horario->faixa_label }}</div>
                                    </div>

                                    @if (!is_null($horario->vagas))
                                        <div class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-700">
                                            {{ $horario->vagas }} vaga(s)
                                        </div>
                                    @endif
                                </div>

                                @if ($horario->observacao)
                                    <div class="mt-2 text-sm text-slate-500">
                                        {{ $horario->observacao }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($espaco->agendamento_disponivel)
                <div class="rounded-3xl border border-slate-200 bg-slate-900 p-6 text-white shadow-sm">
                    <h2 class="text-lg font-bold tracking-tight">Solicite sua visita</h2>

                    @if ($espaco->agendamento_instrucoes)
                        <p class="mt-3 text-sm leading-6 text-slate-300">
                            {{ $espaco->agendamento_instrucoes }}
                        </p>
                    @endif

                    <div class="mt-5">
                        <a
                            href="{{ route('site.museus.agendar', $espaco->slug) }}"
                            class="inline-flex w-full items-center justify-center rounded-2xl bg-white px-4 py-3 text-sm font-semibold text-slate-900 hover:bg-slate-100"
                        >
                            Abrir formulário de agendamento
                        </a>
                    </div>
                </div>
            @endif
        </aside>
    </div>
</section>
@endsection
