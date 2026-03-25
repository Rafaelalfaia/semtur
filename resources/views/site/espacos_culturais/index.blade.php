@extends('site.layouts.app')

@section('title', 'Museus e Teatros')
@section('meta.description', 'Descubra museus e teatros de Altamira, consulte informações, horários e solicite agendamento de visita.')

@section('site.content')
@php
    $fallback = asset('imagens/altamira.jpg');
@endphp

<section class="bg-slate-950 text-white">
    <div class="mx-auto w-full max-w-[1200px] px-4 py-16 sm:px-6 lg:px-8">
        <div class="max-w-3xl">
            <span class="inline-flex rounded-full border border-white/15 bg-white/10 px-4 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-sky-200">
                Cultura e memória
            </span>

            <h1 class="mt-5 text-3xl font-bold tracking-tight sm:text-5xl">
                Museus e teatros de Altamira
            </h1>

            <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-300 sm:text-base">
                Conheça espaços culturais do município, veja detalhes, horários disponíveis e solicite o agendamento da sua visita.
            </p>
        </div>
    </div>
</section>

<section class="bg-slate-50">
    <div class="mx-auto w-full max-w-[1200px] px-4 py-8 sm:px-6 lg:px-8">
        <form method="GET" class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-[1fr_220px_auto]">
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Buscar</label>
                    <input
                        type="text"
                        name="q"
                        value="{{ $q }}"
                        placeholder="Museu, teatro, bairro, descrição..."
                        class="w-full rounded-2xl border-slate-300 focus:border-sky-500 focus:ring-sky-500"
                    >
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Tipo</label>
                    <select
                        name="tipo"
                        class="w-full rounded-2xl border-slate-300 focus:border-sky-500 focus:ring-sky-500"
                    >
                        <option value="todos" @selected($tipo === 'todos')>Todos</option>
                        <option value="museu" @selected($tipo === 'museu')>Museus</option>
                        <option value="teatro" @selected($tipo === 'teatro')>Teatros</option>
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center rounded-2xl bg-slate-900 px-4 py-3 text-sm font-medium text-white hover:bg-slate-800"
                    >
                        Filtrar
                    </button>
                </div>
            </div>
        </form>
    </div>
</section>

@if ($destaques->count())
<section class="bg-white">
    <div class="mx-auto w-full max-w-[1200px] px-4 py-10 sm:px-6 lg:px-8">
        <div class="mb-6">
            <h2 class="text-2xl font-bold tracking-tight text-slate-900">Destaques</h2>
            <p class="mt-2 text-sm text-slate-500">Alguns espaços culturais em evidência no portal.</p>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            @foreach ($destaques as $item)
                @php
                    $capa = $item->capa_url ?: optional($item->midias->first())->url ?: $fallback;
                @endphp

                <a
                    href="{{ route('site.museus.show', $item->slug) }}"
                    class="group overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-md"
                >
                    <div class="aspect-[16/10] overflow-hidden bg-slate-100">
                        <img
                            src="{{ $capa }}"
                            alt="{{ $item->nome }}"
                            class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]"
                        >
                    </div>

                    <div class="p-5">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-sky-50 px-3 py-1 text-xs font-semibold text-sky-700">
                                {{ $item->tipo_label }}
                            </span>

                            @if ($item->agendamento_disponivel)
                                <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                                    Agendamento disponível
                                </span>
                            @endif
                        </div>

                        <h3 class="mt-3 text-lg font-semibold text-slate-900">
                            {{ $item->nome }}
                        </h3>

                        @if ($item->resumo)
                            <p class="mt-2 text-sm leading-6 text-slate-600 line-clamp-3">
                                {{ $item->resumo }}
                            </p>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

<section class="bg-slate-50">
    <div class="mx-auto w-full max-w-[1200px] px-4 py-10 sm:px-6 lg:px-8">
        <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-slate-900">Todos os espaços</h2>
                <p class="mt-2 text-sm text-slate-500">
                    Resultados encontrados: <span class="font-semibold text-slate-800">{{ $espacos->total() }}</span>
                </p>
            </div>
        </div>

        @if ($espacos->count())
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($espacos as $espaco)
                    @php
                        $capa = $espaco->capa_url ?: optional($espaco->midias->first())->url ?: $fallback;
                    @endphp

                    <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                        <a href="{{ route('site.museus.show', $espaco->slug) }}" class="block aspect-[16/10] bg-slate-100">
                            <img src="{{ $capa }}" alt="{{ $espaco->nome }}" class="h-full w-full object-cover">
                        </a>

                        <div class="p-5">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full bg-sky-50 px-3 py-1 text-xs font-semibold text-sky-700">
                                    {{ $espaco->tipo_label }}
                                </span>

                                @if ($espaco->bairro)
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                                        {{ $espaco->bairro }}
                                    </span>
                                @endif
                            </div>

                            <h3 class="mt-3 text-lg font-semibold text-slate-900">
                                <a href="{{ route('site.museus.show', $espaco->slug) }}" class="hover:text-sky-700">
                                    {{ $espaco->nome }}
                                </a>
                            </h3>

                            @if ($espaco->resumo)
                                <p class="mt-2 text-sm leading-6 text-slate-600 line-clamp-3">
                                    {{ $espaco->resumo }}
                                </p>
                            @elseif ($espaco->descricao)
                                <p class="mt-2 text-sm leading-6 text-slate-600 line-clamp-3">
                                    {{ \Illuminate\Support\Str::limit(strip_tags((string) $espaco->descricao), 140) }}
                                </p>
                            @endif

                            @if ($espaco->horarios->count())
                                <div class="mt-4 rounded-2xl bg-slate-50 p-3">
                                    <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">
                                        Horários
                                    </div>

                                    <ul class="mt-2 space-y-1 text-sm text-slate-600">
                                        @foreach ($espaco->horarios->take(3) as $horario)
                                            <li>
                                                <span class="font-medium text-slate-800">{{ $horario->dia_label }}:</span>
                                                {{ $horario->faixa_label }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="mt-5 flex flex-wrap gap-2">
                                <a
                                    href="{{ route('site.museus.show', $espaco->slug) }}"
                                    class="inline-flex items-center rounded-2xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                                >
                                    Ver detalhes
                                </a>

                                @if ($espaco->agendamento_disponivel)
                                    <a
                                        href="{{ route('site.museus.agendar', $espaco->slug) }}"
                                        class="inline-flex items-center rounded-2xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800"
                                    >
                                        Solicitar visita
                                    </a>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $espacos->links() }}
            </div>
        @else
            <div class="rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Nenhum espaço encontrado</h2>
                <p class="mt-2 text-sm text-slate-500">Tente ajustar os filtros para ver outros resultados.</p>
            </div>
        @endif
    </div>
</section>
@endsection
