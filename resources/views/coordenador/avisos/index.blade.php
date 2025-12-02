@extends('console.layout')
@section('title','Avisos')
@section('page.title','Avisos')

@section('content')
@php
  $u = auth()->user();
  $canManage   = $u->can('avisos.manage');
  $canPublicar = $u->can('avisos.publicar');
  $canArquivar = $u->can('avisos.arquivar');
  $showActions = $canManage || $canPublicar || $canArquivar;
@endphp

<div class="max-w-6xl mx-auto">
  @include('coordenador.partials.flash')

  {{-- Cabeçalho --}}
  <div class="mb-5 flex items-center justify-between gap-3">
    <h1 class="text-xl sm:text-2xl font-semibold">Avisos</h1>
    @can('avisos.manage')
      @if (Route::has('coordenador.avisos.create'))
        <a href="{{ route('coordenador.avisos.create') }}"
           class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-white hover:bg-emerald-700">
          + Novo Aviso
        </a>
      @endif
    @endcan
  </div>

  {{-- Filtros --}}
  <form method="get" class="mb-4 grid grid-cols-1 md:grid-cols-3 gap-3">
    <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Buscar por título, descrição ou WhatsApp…"
           class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-slate-100 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-600">
    <select name="status"
            class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-600">
      <option value="" class="bg-[#0B0F0D]">— Todos os status —</option>
      @foreach(['publicado'=>'Publicado','rascunho'=>'Rascunho','arquivado'=>'Arquivado'] as $k=>$v)
        <option value="{{ $k }}" @selected(($sts ?? '')===$k) class="bg-[#0B0F0D]">{{ $v }}</option>
      @endforeach
    </select>
    <button class="rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-slate-100 hover:bg-white/10">
      Filtrar
    </button>
  </form>

  {{-- Tabela --}}
  <div class="overflow-hidden rounded-xl border border-white/10 bg-[#0F1412]">
    <table class="min-w-full">
      <thead class="bg-white/5">
        <tr class="text-left text-slate-300 text-sm">
          <th class="px-4 py-3 font-medium">Título</th>
          <th class="px-4 py-3 font-medium">Status</th>
          <th class="px-4 py-3 font-medium">Janela</th>
          @if($showActions)
            <th class="px-4 py-3 font-medium text-right">Ações</th>
          @endif
        </tr>
      </thead>
      <tbody class="divide-y divide-white/10">
        @forelse($avisos as $aviso)
          <tr class="text-slate-200">
            <td class="px-4 py-3 align-top">
              <div class="font-medium">{{ $aviso->titulo }}</div>
              <div class="text-xs text-slate-400">Atualizado: {{ $aviso->updated_at?->format('d/m/Y H:i') }}</div>
            </td>

            <td class="px-4 py-3 align-top">
              <span class="inline-flex items-center rounded-full border border-white/15 bg-white/5 px-2 py-0.5 text-xs">
                {{ ucfirst($aviso->status) }}
              </span>
            </td>

            <td class="px-4 py-3 align-top text-sm text-slate-300">
              @if($aviso->inicio_em || $aviso->fim_em)
                {{ $aviso->inicio_em?->format('d/m/Y H:i') ?? '—' }} — {{ $aviso->fim_em?->format('d/m/Y H:i') ?? '—' }}
              @else
                Sempre
              @endif
            </td>

            @if($showActions)
              <td class="px-4 py-3 align-top">
                <div class="flex items-center justify-end gap-2">
                  @if($canPublicar && $aviso->status!=='publicado')
                    <form action="{{ route('coordenador.avisos.publicar',$aviso) }}" method="post">
                      @csrf @method('PATCH')
                      <button class="rounded border border-white/10 bg-white/5 px-2 py-1 text-xs hover:bg-white/10">
                        Publicar
                      </button>
                    </form>
                  @endif

                  @if($canArquivar && $aviso->status!=='arquivado')
                    <form action="{{ route('coordenador.avisos.arquivar',$aviso) }}" method="post">
                      @csrf @method('PATCH')
                      <button class="rounded border border-white/10 bg-white/5 px-2 py-1 text-xs hover:bg-white/10">
                        Arquivar
                      </button>
                    </form>
                  @endif

                  @can('avisos.manage')
                    <a href="{{ route('coordenador.avisos.edit',$aviso) }}"
                       class="rounded border border-white/10 bg-white/5 px-2 py-1 text-xs hover:bg-white/10">
                      Editar
                    </a>
                    <form action="{{ route('coordenador.avisos.destroy',$aviso) }}" method="post"
                          onsubmit="return confirm('Remover este aviso?');">
                      @csrf @method('DELETE')
                      <button class="rounded border border-red-500/30 bg-red-900/20 px-2 py-1 text-xs text-red-200 hover:bg-red-900/30">
                        Excluir
                      </button>
                    </form>
                  @endcan
                </div>
              </td>
            @endif
          </tr>
        @empty
          <tr>
            <td colspan="{{ 3 + (int)$showActions }}" class="px-4 py-10 text-center text-slate-400">
              Nenhum aviso encontrado.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Paginação --}}
  <div class="mt-4">
    {{ $avisos->onEachSide(1)->links() }}
  </div>
</div>
@endsection
