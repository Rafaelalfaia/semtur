@extends('console.layout')
@section('title','Eventos — Coordenador')

@section('content')
@php
  use Illuminate\Support\Facades\Storage;

  $u = auth()->user();

  // guards por permissão
  $canManage      = $u->can('eventos.manage');                       // criar/editar/excluir evento
  $canSeeEdicoes  = $u->canany(['eventos.manage','eventos.edicoes.manage']); // acessar gerência de edições
@endphp

<div class="max-w-6xl mx-auto space-y-6">

  @if(session('ok'))
    <div class="rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-2 text-emerald-300">
      {{ session('ok') }}
    </div>
  @endif

  <div class="flex items-center justify-between">
    <h1 class="text-xl font-semibold">Eventos</h1>

    @can('eventos.manage')
      <a href="{{ route('coordenador.eventos.create') }}"
         class="inline-flex items-center rounded-lg bg-emerald-600 hover:bg-emerald-500 px-4 py-2 text-white">
        + Novo evento
      </a>
    @endcan
  </div>

  {{-- Filtros --}}
  <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-3">
    <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Buscar por nome/cidade..."
           class="w-full rounded bg-neutral-900 border border-neutral-700 px-3 py-2">
    <select name="status" class="w-full rounded bg-neutral-900 border border-neutral-700 px-3 py-2">
      <option value="">— Status —</option>
      @foreach(['publicado','rascunho','arquivado'] as $st)
        <option value="{{ $st }}" @selected(($status ?? '')===$st)>{{ ucfirst($st) }}</option>
      @endforeach
    </select>
    <button class="rounded bg-neutral-800 border border-neutral-700 px-3 py-2">Filtrar</button>
  </form>

  <div class="overflow-x-auto rounded-xl border border-white/5">
    <table class="w-full text-sm">
      <thead class="bg-neutral-900/60">
        <tr class="text-left">
          <th class="px-3 py-2">Evento</th>
          <th class="px-3 py-2">Cidade</th>
          <th class="px-3 py-2">Status</th>
          <th class="px-3 py-2">Edições</th>
          <th class="px-3 py-2 text-right">Ações</th>
        </tr>
      </thead>
      <tbody>
        @forelse($eventos as $e)
          <tr class="border-t border-white/5">
            <td class="px-3 py-2">
              <div class="flex items-center gap-3">
                @if(!empty($e->perfil_path))
                  <img src="{{ Storage::disk('public')->url($e->perfil_path) }}"
                       class="w-10 h-10 rounded object-cover" alt="">
                @endif
                <div>
                  <div class="font-medium">{{ $e->nome }}</div>
                  @if(!empty($e->slug))
                    <div class="text-xs text-neutral-400">/{{ $e->slug }}</div>
                  @endif
                </div>
              </div>
            </td>

            <td class="px-3 py-2">{{ $e->cidade ?? '—' }}</td>

            <td class="px-3 py-2">
              <span class="px-2 py-1 rounded text-xs bg-neutral-800 border border-neutral-700">
                {{ $e->status ?? 'rascunho' }}
              </span>
            </td>

            <td class="px-3 py-2">{{ $e->edicoes()->count() }}</td>

            <td class="px-3 py-2">
              <div class="flex items-center gap-2 justify-end">
                @if($canSeeEdicoes && Route::has('coordenador.eventos.edicoes.index'))
                  <a class="px-3 py-1 rounded border border-neutral-700 hover:bg-neutral-800"
                     href="{{ route('coordenador.eventos.edicoes.index',$e) }}">
                    Edições
                  </a>
                @endif

                @can('eventos.manage')
                  <a class="px-3 py-1 rounded border border-neutral-700 hover:bg-neutral-800"
                     href="{{ route('coordenador.eventos.edit',$e) }}">
                    Editar
                  </a>
                  <form method="POST" action="{{ route('coordenador.eventos.destroy',$e) }}"
                        onsubmit="return confirm('Remover evento e edições?');">
                    @csrf @method('DELETE')
                    <button class="px-3 py-1 rounded border border-red-700 text-red-300 hover:bg-red-900/20">
                      Excluir
                    </button>
                  </form>
                @endcan
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="px-3 py-10 text-center text-neutral-400">
              Nenhum evento encontrado.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div>{{ $eventos->links() }}</div>
</div>
@endsection
