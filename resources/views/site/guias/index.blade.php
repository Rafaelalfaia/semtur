@extends('site.layouts.app')

@section('title', 'Guias e Revistas • Visit Altamira')
@section('meta.description', 'Acesse guias e revistas oficiais para planejar sua visita, consultar materiais do destino e abrir conteúdos diretamente dentro do portal.')
@section('meta.image', asset('imagens/altamira.jpg'))

@section('site.content')
@php
    use Illuminate\Support\Str;

    $tipoAtual = (string) ($tipo ?? '');
    $qAtual = (string) ($q ?? '');
    $totalMateriais = method_exists($materiais, 'total') ? $materiais->total() : $materiais->count();
    $agrupados = collect(method_exists($materiais, 'items') ? $materiais->items() : $materiais)->groupBy('tipo');
@endphp

<section class="bg-slate-950 text-white">
    <div class="mx-auto max-w-[1200px] px-4 py-6 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-[28px] border border-white/10 bg-gradient-to-br from-emerald-700 via-emerald-800 to-slate-950">
            <div class="grid gap-8 px-5 py-8 sm:px-8 lg:grid-cols-[1.35fr_.9fr] lg:px-10 lg:py-12">
                <div class="max-w-2xl">
                    <div class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-emerald-100">
                        Materiais oficiais
                    </div>

                    <h1 class="mt-4 text-3xl font-semibold leading-tight sm:text-4xl">
                        Guias e revistas para planejar melhor sua visita
                    </h1>

                    <p class="mt-4 max-w-xl text-sm leading-7 text-emerald-50/90 sm:text-base">
                        Encontre materiais institucionais, guias de apoio e revistas com conteúdo oficial
                        para conhecer Altamira com mais contexto, praticidade e organização.
                    </p>

                    <div class="mt-6 flex flex-wrap gap-3">
                        <a
                            href="#lista-materiais"
                            class="inline-flex items-center rounded-2xl bg-white px-5 py-3 text-sm font-medium text-slate-900 transition hover:bg-emerald-50"
                        >
                            Explorar materiais
                        </a>

                        <a
                            href="{{ route('site.explorar') }}"
                            class="inline-flex items-center rounded-2xl border border-white/20 bg-white/10 px-5 py-3 text-sm font-medium text-white transition hover:bg-white/15"
                        >
                            Ver mais opções
                        </a>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-3 lg:grid-cols-1">
                    <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                        <div class="text-[11px] uppercase tracking-[0.16em] text-emerald-100/80">Acervo</div>
                        <div class="mt-2 text-lg font-semibold">{{ $totalMateriais }} materiais</div>
                        <p class="mt-2 text-sm leading-6 text-emerald-50/85">
                            Guias e revistas em um único espaço público.
                        </p>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                        <div class="text-[11px] uppercase tracking-[0.16em] text-emerald-100/80">Leitura</div>
                        <div class="mt-2 text-lg font-semibold">Visualização no portal</div>
                        <p class="mt-2 text-sm leading-6 text-emerald-50/85">
                            Ao abrir um material, o conteúdo é exibido dentro do próprio site.
                        </p>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                        <div class="text-[11px] uppercase tracking-[0.16em] text-emerald-100/80">Origem</div>
                        <div class="mt-2 text-lg font-semibold">Curadoria oficial</div>
                        <p class="mt-2 text-sm leading-6 text-emerald-50/85">
                            Conteúdo publicado e organizado pela gestão do destino.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-5 flex gap-3 overflow-x-auto pb-1">
            <a
                href="{{ route('site.guias') }}"
                class="inline-flex shrink-0 items-center rounded-full border px-4 py-2 text-sm transition {{ $tipoAtual === '' ? 'border-emerald-400 bg-emerald-500/15 text-emerald-200' : 'border-white/10 bg-white/5 text-slate-200 hover:bg-white/10' }}"
            >
                Todos
            </a>

            @foreach(($tipos ?? []) as $tipoKey => $tipoLabel)
                <a
                    href="{{ route('site.guias', array_filter(['tipo' => $tipoKey, 'q' => $qAtual ?: null])) }}"
                    class="inline-flex shrink-0 items-center rounded-full border px-4 py-2 text-sm transition {{ $tipoAtual === $tipoKey ? 'border-emerald-400 bg-emerald-500/15 text-emerald-200' : 'border-white/10 bg-white/5 text-slate-200 hover:bg-white/10' }}"
                >
                    {{ $tipoLabel }}
                </a>
            @endforeach
        </div>
    </div>
</section>

<section id="lista-materiais" class="bg-slate-950 pb-16 text-white">
    <div class="mx-auto max-w-[1200px] px-4 sm:px-6 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
            <aside class="h-fit rounded-[26px] border border-white/10 bg-white/[0.03] p-5">
                <div class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-300/80">
                    Filtros
                </div>
                <h2 class="mt-2 text-lg font-semibold text-slate-100">
                    Encontre o material ideal
                </h2>

                <form method="GET" class="mt-5 space-y-4">
                    <div>
                        <label class="mb-1.5 block text-sm text-slate-300">Busca</label>
                        <input
                            type="text"
                            name="q"
                            value="{{ $qAtual }}"
                            placeholder="Ex.: visitante, mapa, revista..."
                            class="w-full rounded-2xl border border-white/10 bg-slate-900/80 px-4 py-3 text-sm text-slate-100 placeholder:text-slate-500 focus:border-emerald-500 focus:outline-none"
                        >
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm text-slate-300">Tipo</label>
                        <select
                            name="tipo"
                            class="w-full rounded-2xl border border-white/10 bg-slate-900/80 px-4 py-3 text-sm text-slate-100 focus:border-emerald-500 focus:outline-none"
                        >
                            <option value="">Todos</option>
                            @foreach(($tipos ?? []) as $tipoKey => $tipoLabel)
                                <option value="{{ $tipoKey }}" @selected($tipoAtual === $tipoKey)>{{ $tipoLabel }}</option>
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
                            href="{{ route('site.guias') }}"
                            class="inline-flex items-center rounded-2xl border border-white/10 bg-white/5 px-5 py-3 text-sm font-medium text-slate-200 transition hover:bg-white/10"
                        >
                            Limpar
                        </a>
                    </div>
                </form>
            </aside>

            <div>
                <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-2xl font-semibold text-slate-100">
                            Acervo publicado
                        </h2>
                        <p class="mt-1 text-sm text-slate-400">
                            {{ $totalMateriais }} {{ $totalMateriais === 1 ? 'material encontrado' : 'materiais encontrados' }}
                        </p>
                    </div>
                </div>

                @if($totalMateriais === 0)
                    <div class="rounded-[28px] border border-dashed border-white/10 bg-white/[0.03] px-6 py-14 text-center">
                        <h3 class="text-xl font-semibold text-slate-100">
                            Nenhum material encontrado
                        </h3>
                        <p class="mt-3 text-sm leading-7 text-slate-400">
                            Ajuste a busca ou volte mais tarde para conferir novos guias e revistas.
                        </p>
                    </div>
                @else
                    <div class="space-y-8">
                        @forelse($agrupados as $grupoTipo => $grupoItems)
                            <section>
                                <div class="mb-4 flex items-center justify-between gap-3">
                                    <div>
                                        <div class="text-[11px] font-semibold uppercase tracking-[0.20em] text-emerald-300/80">
                                            {{ ($tipos[$grupoTipo] ?? ucfirst($grupoTipo)) }}
                                        </div>
                                        <h3 class="mt-1 text-xl font-semibold text-slate-100">
                                            {{ ($tipos[$grupoTipo] ?? ucfirst($grupoTipo)) }} publicados
                                        </h3>
                                    </div>

                                    <div class="text-sm text-slate-400">
                                        {{ count($grupoItems) }} {{ count($grupoItems) === 1 ? 'item' : 'itens' }}
                                    </div>
                                </div>

                                <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                                    @foreach($grupoItems as $material)
                                        @php
                                            $cover = $material->capa_url ?: asset('imagens/altamira.jpg');
                                        @endphp

                                        <article class="overflow-hidden rounded-[26px] border border-white/10 bg-white/[0.03]">
                                            <div class="relative h-56 overflow-hidden bg-slate-900">
                                                <img
                                                    src="{{ $cover }}"
                                                    alt="{{ $material->nome }}"
                                                    class="h-full w-full object-cover"
                                                    loading="lazy"
                                                    decoding="async"
                                                >

                                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-transparent to-transparent"></div>

                                                <div class="absolute inset-x-0 bottom-0 p-4">
                                                    <span class="inline-flex rounded-full border border-white/15 bg-black/35 px-3 py-1 text-xs text-white">
                                                        {{ $material->tipo_label }}
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="p-5">
                                                <h3 class="text-lg font-semibold text-slate-100">
                                                    {{ $material->nome }}
                                                </h3>

                                                <p class="mt-3 text-sm leading-7 text-slate-300">
                                                    {{ Str::limit(strip_tags((string) $material->descricao), 140) }}
                                                </p>

                                                <div class="mt-5 flex flex-wrap gap-2">
                                                    <a
                                                        href="{{ route('site.guias.show', $material->slug) }}"
                                                        class="inline-flex items-center rounded-2xl bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-500"
                                                    >
                                                        Abrir material
                                                    </a>

                                                    <a
                                                        href="{{ $material->link_acesso }}"
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                        class="inline-flex items-center rounded-2xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm font-medium text-slate-200 transition hover:bg-white/10"
                                                    >
                                                        Google Drive
                                                    </a>
                                                </div>
                                            </div>
                                        </article>
                                    @endforeach
                                </div>
                            </section>
                        @empty
                            <div class="rounded-[28px] border border-dashed border-white/10 bg-white/[0.03] px-6 py-14 text-center">
                                <h3 class="text-xl font-semibold text-slate-100">
                                    Nenhum material encontrado
                                </h3>
                            </div>
                        @endforelse
                    </div>

                    @if(method_exists($materiais, 'links'))
                        <div class="mt-8">
                            {{ $materiais->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
