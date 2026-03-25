@extends('site.layouts.app')

@section('title', 'Roteiros em Altamira')
@section('meta.description', 'Explore roteiros por duração e perfil para viver Altamira com mais contexto, organização e curadoria local.')
@section('meta.image', asset('imagens/altamira.jpg'))

@section('site.content')
@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    $duracaoAtual = (string) ($duracao ?? '');
    $perfilAtual  = (string) ($perfil ?? '');
    $qAtual       = (string) ($q ?? '');

    $chipsPerfis = [
        'natureza_rio'      => 'Natureza e rio',
        'cultura_memoria'   => 'Cultura e memória',
        'gastronomia_local' => 'Gastronomia local',
        'base_comunitaria'  => 'Base comunitária',
        'familia_educacao'  => 'Família e educação',
    ];

    $chipsDuracao = [
        '1_dia'    => '1 dia',
        '2_3_dias' => '2 ou 3 dias',
        'meio_dia' => 'Meio dia',
    ];
@endphp

<section class="bg-slate-950 text-white">
    <div class="mx-auto max-w-[1200px] px-4 py-6 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-[28px] border border-white/10 bg-gradient-to-br from-emerald-700 via-emerald-800 to-slate-950">
            <div class="grid gap-8 px-5 py-8 sm:px-8 lg:grid-cols-[1.35fr_.9fr] lg:px-10 lg:py-12">
                <div class="max-w-2xl">
                    <div class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-emerald-100">
                        Roteiros de viagem
                    </div>

                    <h1 class="mt-4 text-3xl font-semibold leading-tight sm:text-4xl">
                        Descubra Altamira por duração e perfil
                    </h1>

                    <p class="mt-4 max-w-xl text-sm leading-7 text-emerald-50/90 sm:text-base">
                        Roteiros pensados para ajudar o visitante a entender a cidade, organizar o tempo
                        e encontrar experiências com mais contexto, paisagem, cultura e curadoria local.
                    </p>

                    <div class="mt-6 flex flex-wrap gap-3">
                        <a
                            href="#lista-roteiros"
                            class="inline-flex items-center rounded-2xl bg-white px-5 py-3 text-sm font-medium text-slate-900 transition hover:bg-emerald-50"
                        >
                            Explorar roteiros
                        </a>

                        <a
                            href="{{ route('site.explorar') ?? url('/explorar') }}"
                            class="inline-flex items-center rounded-2xl border border-white/20 bg-white/10 px-5 py-3 text-sm font-medium text-white transition hover:bg-white/15"
                        >
                            Ver mais opções
                        </a>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-3 lg:grid-cols-1">
                    <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                        <div class="text-[11px] uppercase tracking-[0.16em] text-emerald-100/80">Por duração</div>
                        <div class="mt-2 text-lg font-semibold">1 dia, meio dia ou mais tempo</div>
                        <p class="mt-2 text-sm leading-6 text-emerald-50/85">
                            Ideal para quem quer um bate-volta ou uma experiência mais completa.
                        </p>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                        <div class="text-[11px] uppercase tracking-[0.16em] text-emerald-100/80">Por perfil</div>
                        <div class="mt-2 text-lg font-semibold">Natureza, cultura, família e mais</div>
                        <p class="mt-2 text-sm leading-6 text-emerald-50/85">
                            Cada roteiro organiza a cidade a partir de um jeito de viver Altamira.
                        </p>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                        <div class="text-[11px] uppercase tracking-[0.16em] text-emerald-100/80">Curadoria</div>
                        <div class="mt-2 text-lg font-semibold">Pontos e empresas selecionados</div>
                        <p class="mt-2 text-sm leading-6 text-emerald-50/85">
                            O conteúdo mostra somente sugestões que fazem sentido dentro de cada percurso.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-5 flex gap-3 overflow-x-auto pb-1">
            <a
                href="{{ route('site.roteiros') }}"
                class="inline-flex shrink-0 items-center rounded-full border px-4 py-2 text-sm transition {{ $duracaoAtual === '' && $perfilAtual === '' ? 'border-emerald-400 bg-emerald-500/15 text-emerald-200' : 'border-white/10 bg-white/5 text-slate-200 hover:bg-white/10' }}"
            >
                Todos
            </a>

            @foreach($chipsDuracao as $key => $label)
                <a
                    href="{{ route('site.roteiros', array_filter(['duracao' => $key, 'perfil' => $perfilAtual ?: null, 'q' => $qAtual ?: null])) }}"
                    class="inline-flex shrink-0 items-center rounded-full border px-4 py-2 text-sm transition {{ $duracaoAtual === $key ? 'border-emerald-400 bg-emerald-500/15 text-emerald-200' : 'border-white/10 bg-white/5 text-slate-200 hover:bg-white/10' }}"
                >
                    {{ $label }}
                </a>
            @endforeach

            @foreach($chipsPerfis as $key => $label)
                <a
                    href="{{ route('site.roteiros', array_filter(['perfil' => $key, 'duracao' => $duracaoAtual ?: null, 'q' => $qAtual ?: null])) }}"
                    class="inline-flex shrink-0 items-center rounded-full border px-4 py-2 text-sm transition {{ $perfilAtual === $key ? 'border-emerald-400 bg-emerald-500/15 text-emerald-200' : 'border-white/10 bg-white/5 text-slate-200 hover:bg-white/10' }}"
                >
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>
</section>

<section id="lista-roteiros" class="bg-slate-950 pb-16 text-white">
    <div class="mx-auto max-w-[1200px] px-4 sm:px-6 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
            <aside class="h-fit rounded-[26px] border border-white/10 bg-white/[0.03] p-5">
                <div class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-300/80">
                    Filtros
                </div>
                <h2 class="mt-2 text-lg font-semibold text-slate-100">
                    Encontre o roteiro ideal
                </h2>

                <form method="GET" class="mt-5 space-y-4">
                    <div>
                        <label class="mb-1.5 block text-sm text-slate-300">Busca</label>
                        <input
                            type="text"
                            name="q"
                            value="{{ $qAtual }}"
                            placeholder="Ex.: rio, cultura, família..."
                            class="w-full rounded-2xl border border-white/10 bg-slate-900/80 px-4 py-3 text-sm text-slate-100 placeholder:text-slate-500 focus:border-emerald-500 focus:outline-none"
                        >
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm text-slate-300">Duração</label>
                        <select
                            name="duracao"
                            class="w-full rounded-2xl border border-white/10 bg-slate-900/80 px-4 py-3 text-sm text-slate-100 focus:border-emerald-500 focus:outline-none"
                        >
                            <option value="">Todas</option>
                            @foreach(($duracoes ?? []) as $key => $label)
                                <option value="{{ $key }}" @selected($duracaoAtual === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm text-slate-300">Perfil</label>
                        <select
                            name="perfil"
                            class="w-full rounded-2xl border border-white/10 bg-slate-900/80 px-4 py-3 text-sm text-slate-100 focus:border-emerald-500 focus:outline-none"
                        >
                            <option value="">Todos</option>
                            @foreach(($perfis ?? []) as $key => $label)
                                <option value="{{ $key }}" @selected($perfilAtual === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex flex-wrap gap-2 pt-2">
                        <button
                            type="submit"
                            class="inline-flex items-center rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-medium text-white transition hover:bg-emerald-500"
                        >
                            Aplicar filtros
                        </button>

                        <a
                            href="{{ route('site.roteiros') }}"
                            class="inline-flex items-center rounded-2xl border border-white/10 bg-white/5 px-5 py-3 text-sm font-medium text-slate-200 transition hover:bg-white/10"
                        >
                            Limpar
                        </a>
                    </div>
                </form>
            </aside>

            <div>
                <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-300/80">
                            Resultados
                        </div>
                        <h2 class="mt-2 text-2xl font-semibold text-slate-100">
                            Roteiros para explorar Altamira
                        </h2>
                        <p class="mt-1 text-sm text-slate-400">
                            Selecione um percurso pronto e descubra pontos e empresas que combinam com a experiência proposta.
                        </p>
                    </div>

                    <div class="text-sm text-slate-400">
                        {{ $roteiros->total() }} {{ $roteiros->total() === 1 ? 'roteiro encontrado' : 'roteiros encontrados' }}
                    </div>
                </div>

                @if($roteiros->count() === 0)
                    <div class="rounded-[28px] border border-dashed border-white/10 bg-white/[0.03] px-6 py-14 text-center">
                        <div class="mx-auto max-w-xl">
                            <h3 class="text-xl font-semibold text-slate-100">
                                Nenhum roteiro encontrado
                            </h3>
                            <p class="mt-3 text-sm leading-7 text-slate-400">
                                Tente ajustar a duração, o perfil ou a busca para encontrar um percurso que combine melhor com o que você procura.
                            </p>

                            <div class="mt-6">
                                <a
                                    href="{{ route('site.roteiros') }}"
                                    class="inline-flex items-center rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-medium text-white transition hover:bg-emerald-500"
                                >
                                    Ver todos os roteiros
                                </a>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="grid gap-5 xl:grid-cols-2">
                        @foreach($roteiros as $roteiro)
                            @php
                                $cover = $roteiro->capa_url ?: asset('imagens/altamira.jpg');
                            @endphp

                            <article class="group overflow-hidden rounded-[28px] border border-white/10 bg-white/[0.03] transition hover:border-emerald-500/30 hover:bg-white/[0.04]">
                                <div class="relative h-60 overflow-hidden bg-slate-900">
                                    <img
                                        src="{{ $cover }}"
                                        alt="{{ $roteiro->titulo }}"
                                        class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]"
                                        loading="lazy"
                                        decoding="async"
                                    >

                                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/15 to-transparent"></div>

                                    <div class="absolute inset-x-0 bottom-0 p-4">
                                        <div class="flex flex-wrap gap-2">
                                            <span class="rounded-full border border-white/15 bg-black/35 px-3 py-1 text-xs text-white backdrop-blur">
                                                {{ $roteiro->duracao_label }}
                                            </span>
                                            <span class="rounded-full border border-white/15 bg-black/35 px-3 py-1 text-xs text-white backdrop-blur">
                                                {{ $roteiro->perfil_label }}
                                            </span>
                                            @if($roteiro->intensidade_label)
                                                <span class="rounded-full border border-white/15 bg-black/35 px-3 py-1 text-xs text-white backdrop-blur">
                                                    {{ $roteiro->intensidade_label }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="p-5">
                                    <h3 class="text-xl font-semibold text-slate-100">
                                        {{ $roteiro->titulo }}
                                    </h3>

                                    <p class="mt-3 line-clamp-3 text-sm leading-7 text-slate-300">
                                        {{ $roteiro->resumo }}
                                    </p>

                                    <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
                                        <div class="rounded-2xl border border-white/10 bg-slate-950/40 px-3 py-3">
                                            <div class="text-[11px] uppercase tracking-[0.16em] text-slate-500">Etapas</div>
                                            <div class="mt-1 text-sm font-semibold text-slate-100">
                                                {{ (int) ($roteiro->etapas_count ?? 0) }}
                                            </div>
                                        </div>

                                        <div class="rounded-2xl border border-white/10 bg-slate-950/40 px-3 py-3">
                                            <div class="text-[11px] uppercase tracking-[0.16em] text-slate-500">Empresas</div>
                                            <div class="mt-1 text-sm font-semibold text-slate-100">
                                                {{ (int) ($roteiro->empresas_sugestao_count ?? 0) }}
                                            </div>
                                        </div>

                                        <div class="rounded-2xl border border-white/10 bg-slate-950/40 px-3 py-3 sm:col-span-2">
                                            <div class="text-[11px] uppercase tracking-[0.16em] text-slate-500">Público</div>
                                            <div class="mt-1 text-sm font-semibold text-slate-100">
                                                {{ $roteiro->publico_label ?: 'Visitantes em geral' }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-5 flex items-center justify-between gap-3">
                                        <div class="text-xs text-slate-500">
                                            {{ $roteiro->published_at?->format('d/m/Y') }}
                                        </div>

                                        <a
                                            href="{{ route('site.roteiros.show', $roteiro->slug) }}"
                                            class="inline-flex items-center rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-medium text-white transition hover:bg-emerald-500"
                                        >
                                            Ver roteiro
                                        </a>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="mt-8">
                        {{ $roteiros->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
