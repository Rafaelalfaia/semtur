@extends('console.layout')

@section('title', 'Museus e Teatros')
@section('page.title', 'Museus e Teatros')

@section('content')
@php
    $u = auth()->user();

    $canCreate   = $u->can('espacos_culturais.create');
    $canEdit     = $u->can('espacos_culturais.update');
    $canDelete   = $u->can('espacos_culturais.delete');
    $canRascunho = $u->can('espacos_culturais.rascunho');
    $canPublicar = $u->can('espacos_culturais.publicar');
    $canArquivar = $u->can('espacos_culturais.arquivar');

    $dias = [
        0 => 'Dom',
        1 => 'Seg',
        2 => 'Ter',
        3 => 'Qua',
        4 => 'Qui',
        5 => 'Sex',
        6 => 'Sáb',
    ];
@endphp

@if(session('ok'))
    <div class="mb-4 rounded-lg bg-emerald-600/15 border border-emerald-500/30 px-3 py-2 text-emerald-200">
        {{ session('ok') }}
    </div>
@endif

<div class="mb-4 flex flex-col md:flex-row md:items-end md:justify-between gap-3">
    <form method="GET" class="flex flex-col sm:flex-row gap-2">
        <input type="text" name="busca" value="{{ $busca }}"
               class="rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-slate-100 w-72"
               placeholder="Buscar por nome ou descrição…">

        <select name="tipo" class="rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-slate-100">
            <option value="todos"  @selected($tipo === 'todos')>Todos os tipos</option>
            <option value="museu"  @selected($tipo === 'museu')>Museus</option>
            <option value="teatro" @selected($tipo === 'teatro')>Teatros</option>
        </select>

        <select name="status" class="rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-slate-100">
            <option value="todos"     @selected($status === 'todos')>Todos os status</option>
            <option value="publicado" @selected($status === 'publicado')>Publicado</option>
            <option value="rascunho"  @selected($status === 'rascunho')>Rascunho</option>
            <option value="arquivado" @selected($status === 'arquivado')>Arquivado</option>
        </select>

        <button class="rounded-lg bg-emerald-600 hover:bg-emerald-700 px-4 py-2 text-white">
            Filtrar
        </button>
    </form>

    @if($canCreate)
        <a href="{{ route('coordenador.espacos-culturais.create') }}"
           class="inline-flex items-center gap-2 rounded-lg bg-white/10 hover:bg-white/20 px-4 py-2 text-slate-100">
            + Novo espaço cultural
        </a>
    @endif
</div>

@if($espacos->count() === 0)
    <div class="text-slate-300/80">Nenhum museu ou teatro encontrado.</div>
@else
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach($espacos as $espaco)
            <div class="rounded-2xl overflow-hidden bg-slate-900/60 border border-white/10">
                <div class="p-4">
                    <div class="flex items-start gap-3">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2 mb-2">
                                <span class="px-2 py-0.5 text-xs rounded-full bg-sky-900/40 text-sky-200 border border-sky-700/40">
                                    {{ $espaco->tipo_label }}
                                </span>

                                @if($espaco->status === 'publicado')
                                    <span class="px-2 py-0.5 text-xs rounded-full bg-emerald-900/50 text-emerald-300 border border-emerald-700/40">
                                        Publicado
                                    </span>
                                @elseif($espaco->status === 'arquivado')
                                    <span class="px-2 py-0.5 text-xs rounded-full bg-amber-900/30 text-amber-200 border border-amber-700/40">
                                        Arquivado
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 text-xs rounded-full bg-slate-700 text-slate-300 border border-slate-600">
                                        Rascunho
                                    </span>
                                @endif
                            </div>

                            <div class="font-semibold text-slate-100 truncate">{{ $espaco->nome }}</div>
                            <div class="text-xs text-slate-400 mt-1">{{ $espaco->cidade ?: 'Altamira' }}</div>
                        </div>
                    </div>

                    @if($espaco->descricao)
                        <p class="mt-3 text-sm text-slate-300 line-clamp-3">
                            {{ $espaco->descricao }}
                        </p>
                    @endif

                    <div class="mt-4">
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-400 mb-2">
                            Horários cadastrados ({{ $espaco->horarios_count }})
                        </div>

                        @forelse($espaco->horarios->take(3) as $horario)
                            <div class="text-sm text-slate-200">
                                {{ $dias[$horario->dia_semana] ?? 'Dia' }} ·
                                {{ substr((string) $horario->hora_inicio, 0, 5) }} às
                                {{ substr((string) $horario->hora_fim, 0, 5) }}
                                @if($horario->vagas)
                                    <span class="text-slate-400">· {{ $horario->vagas }} vagas</span>
                                @endif
                            </div>
                        @empty
                            <div class="text-sm text-slate-400">Sem horários cadastrados.</div>
                        @endforelse

                        @if($espaco->horarios_count > 3)
                            <div class="text-xs text-slate-400 mt-1">
                                + {{ $espaco->horarios_count - 3 }} horário(s)
                            </div>
                        @endif
                    </div>

                    @if($espaco->maps_url)
                        <div class="mt-3 text-sm">
                            <a href="{{ $espaco->maps_url }}" target="_blank" class="text-emerald-400 hover:underline">
                                Ver no Maps
                            </a>
                        </div>
                    @endif

                    <div class="mt-4 flex flex-wrap gap-2">
                        @if($canEdit)
                            <a href="{{ route('coordenador.espacos-culturais.edit', $espaco) }}"
                               class="text-emerald-300 hover:underline text-sm">
                                Editar
                            </a>
                        @endif

                        @if($canDelete)
                            <form method="POST" action="{{ route('coordenador.espacos-culturais.destroy', $espaco) }}"
                                  class="inline"
                                  onsubmit="return confirm('Excluir este espaço cultural?');">
                                @csrf
                                @method('DELETE')
                                <button class="text-rose-300 hover:underline text-sm">Excluir</button>
                            </form>
                        @endif
                    </div>

                    <div class="mt-3 flex flex-wrap gap-2">
                        @if($canRascunho && $espaco->status !== 'rascunho')
                            <form method="POST" action="{{ route('coordenador.espacos-culturais.rascunho', $espaco) }}">
                                @csrf
                                @method('PATCH')
                                <button class="px-2 py-1 rounded bg-white/10 hover:bg-white/20 text-sm">Rascunho</button>
                            </form>
                        @endif

                        @if($canPublicar && $espaco->status !== 'publicado')
                            <form method="POST" action="{{ route('coordenador.espacos-culturais.publicar', $espaco) }}">
                                @csrf
                                @method('PATCH')
                                <button class="px-2 py-1 rounded bg-white/10 hover:bg-white/20 text-sm">Publicar</button>
                            </form>
                        @endif

                        @if($canArquivar && $espaco->status !== 'arquivado')
                            <form method="POST" action="{{ route('coordenador.espacos-culturais.arquivar', $espaco) }}">
                                @csrf
                                @method('PATCH')
                                <button class="px-2 py-1 rounded bg-white/10 hover:bg-white/20 text-sm">Arquivar</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-6">{{ $espacos->links() }}</div>
@endif
@endsection
