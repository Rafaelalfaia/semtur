@extends('console.layout')
@section('title','Avisos')
@section('page.title','Avisos')
@section('topbar.description', 'Gerencie os avisos do console com filtros, status e a mesma base visual usada nos demais módulos.')

@section('topbar.nav')
  <span class="ui-console-topbar-tab is-active">Avisos</span>
  @can('avisos.manage')
    @if (Route::has('coordenador.avisos.create'))
      <a href="{{ route('coordenador.avisos.create') }}" class="ui-console-topbar-tab">Novo aviso</a>
    @endif
  @endcan
@endsection

@section('content')
@php
  $u = auth()->user();
  $canManage   = $u->can('avisos.manage');
  $canPublicar = $u->can('avisos.publicar');
  $canArquivar = $u->can('avisos.arquivar');
  $showActions = $canManage || $canPublicar || $canArquivar;
@endphp

<div class="ui-console-page">
  @include('coordenador.partials.flash')

  <x-dashboard.page-header
    title="Avisos"
    subtitle="Acompanhe publicação, janelas de exibição e ações editoriais em uma visão mais limpa e consistente com o novo console."
  >
    @can('avisos.manage')
      @if (Route::has('coordenador.avisos.create'))
        <a href="{{ route('coordenador.avisos.create') }}" class="ui-btn-primary">Novo aviso</a>
      @endif
    @endcan
  </x-dashboard.page-header>

  <x-dashboard.section-card title="Filtros" subtitle="Busque por título, descrição, WhatsApp e status" class="ui-coord-dashboard-panel mt-5">
    <form method="get" class="grid grid-cols-1 gap-3 md:grid-cols-[minmax(0,1fr)_240px_auto]">
      <input
        type="text"
        name="q"
        value="{{ $q ?? '' }}"
        placeholder="Buscar por título, descrição ou WhatsApp..."
        class="ui-form-control"
      >
      <select name="status" class="ui-form-select">
        <option value="">Todos os status</option>
        @foreach(['publicado'=>'Publicado','rascunho'=>'Rascunho','arquivado'=>'Arquivado'] as $k=>$v)
          <option value="{{ $k }}" @selected(($sts ?? '')===$k)>{{ $v }}</option>
        @endforeach
      </select>
      <button class="ui-btn-secondary">Filtrar</button>
    </form>
  </x-dashboard.section-card>

  <x-dashboard.section-card title="Lista de avisos" subtitle="Controle status, janela de exibição e ações do módulo" class="ui-coord-dashboard-panel mt-5">
    <div class="ui-table-shell">
      <table class="min-w-full">
        <thead class="ui-table-head">
          <tr class="text-left text-sm">
            <th class="px-4 py-3 font-medium">Título</th>
            <th class="px-4 py-3 font-medium">Status</th>
            <th class="px-4 py-3 font-medium">Janela</th>
            @if($showActions)
              <th class="px-4 py-3 font-medium text-right">Ações</th>
            @endif
          </tr>
        </thead>
        <tbody>
          @forelse($avisos as $aviso)
            <tr class="ui-table-row">
              <td class="px-4 py-3 align-top">
                <div class="font-semibold text-[var(--ui-text-title)]">{{ $aviso->titulo }}</div>
                <div class="mt-1 text-xs text-[var(--ui-text-soft)]">Atualizado: {{ $aviso->updated_at?->format('d/m/Y H:i') }}</div>
              </td>

              <td class="px-4 py-3 align-top">
                @if($aviso->status === 'publicado')
                  <span class="ui-badge ui-badge-success">Publicado</span>
                @elseif($aviso->status === 'arquivado')
                  <span class="ui-badge ui-badge-warning">Arquivado</span>
                @else
                  <span class="ui-badge ui-badge-neutral">Rascunho</span>
                @endif
              </td>

              <td class="px-4 py-3 align-top text-sm text-[var(--ui-text-soft)]">
                @if($aviso->inicio_em || $aviso->fim_em)
                  {{ $aviso->inicio_em?->format('d/m/Y H:i') ?? '—' }} — {{ $aviso->fim_em?->format('d/m/Y H:i') ?? '—' }}
                @else
                  Sempre
                @endif
              </td>

              @if($showActions)
                <td class="px-4 py-3 align-top">
                  <div class="ui-aviso-actions justify-end">
                    @if($canPublicar && $aviso->status !== 'publicado')
                      <form action="{{ route('coordenador.avisos.publicar',$aviso) }}" method="post">
                        @csrf
                        @method('PATCH')
                        <button class="ui-btn-secondary">Publicar</button>
                      </form>
                    @endif

                    @if($canArquivar && $aviso->status !== 'arquivado')
                      <form action="{{ route('coordenador.avisos.arquivar',$aviso) }}" method="post">
                        @csrf
                        @method('PATCH')
                        <button class="ui-btn-secondary">Arquivar</button>
                      </form>
                    @endif

                    @can('avisos.manage')
                      <a href="{{ route('coordenador.avisos.edit',$aviso) }}" class="ui-btn-secondary">Editar</a>
                      <form action="{{ route('coordenador.avisos.destroy',$aviso) }}" method="post" onsubmit="return confirm('Remover este aviso?');">
                        @csrf
                        @method('DELETE')
                        <button class="ui-btn-danger">Excluir</button>
                      </form>
                    @endcan
                  </div>
                </td>
              @endif
            </tr>
          @empty
            <tr class="ui-table-row">
              <td colspan="{{ 3 + (int)$showActions }}" class="px-4 py-10 text-center text-[var(--ui-text-soft)]">
                Nenhum aviso encontrado.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-4">
      {{ $avisos->onEachSide(1)->links() }}
    </div>
  </x-dashboard.section-card>
</div>
@endsection
