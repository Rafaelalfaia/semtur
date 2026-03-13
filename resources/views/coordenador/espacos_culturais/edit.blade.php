@extends('console.layout')

@section('title', 'Editar espaço cultural')
@section('page.title', 'Editar espaço cultural')

@section('content')
    @if(session('ok'))
        <div class="mb-4 rounded-lg bg-emerald-600/15 border border-emerald-500/30 px-3 py-2 text-emerald-200">
            {{ session('ok') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-rose-500/30 bg-rose-500/10 p-3 text-rose-200">
            <strong>Corrija os campos abaixo:</strong>
            <ul class="list-disc list-inside mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-2">
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

        <a href="{{ route('coordenador.espacos-culturais.index') }}"
           class="px-3 py-2 rounded-lg bg-white/5 text-slate-200 hover:bg-white/10">
            Voltar
        </a>
    </div>

    <form method="POST" action="{{ route('coordenador.espacos-culturais.update', $espaco) }}" class="space-y-6">
        @csrf
        @method('PUT')

        @include('coordenador.espacos_culturais._form', [
            'espaco' => $espaco,
            'diasSemana' => $diasSemana,
        ])

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap gap-2">
                <button class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white">
                    Salvar alterações
                </button>

                <a href="{{ route('coordenador.espacos-culturais.index') }}"
                   class="px-4 py-2 rounded-lg bg-white/5 text-slate-200 hover:bg-white/10">
                    Cancelar
                </a>
            </div>

            <div class="flex flex-wrap gap-2">
                <form method="POST" action="{{ route('coordenador.espacos-culturais.rascunho', $espaco) }}">
                    @csrf
                    @method('PATCH')
                    <button class="px-3 py-2 rounded bg-white/10 hover:bg-white/20 text-sm">Rascunho</button>
                </form>

                <form method="POST" action="{{ route('coordenador.espacos-culturais.publicar', $espaco) }}">
                    @csrf
                    @method('PATCH')
                    <button class="px-3 py-2 rounded bg-white/10 hover:bg-white/20 text-sm">Publicar</button>
                </form>

                <form method="POST" action="{{ route('coordenador.espacos-culturais.arquivar', $espaco) }}">
                    @csrf
                    @method('PATCH')
                    <button class="px-3 py-2 rounded bg-white/10 hover:bg-white/20 text-sm">Arquivar</button>
                </form>
            </div>
        </div>
    </form>
@endsection
