@extends('console.layout')

@section('title','Banners - Console')
@section('page.title','Banners')
@section('topbar.description', 'Gerencie os banners secundarios exibidos na home com a mesma base visual do console compartilhado.')

@section('topbar.nav')
  <span class="ui-console-topbar-tab is-active">Banners</span>
  @can('banners.manage')
    <a href="{{ route('coordenador.banners.create') }}" class="ui-console-topbar-tab">Novo banner</a>
  @endcan
@endsection

@section('content')
@php
  $u = auth()->user();
  $canManage = $u->can('banners.manage');
@endphp

<div class="ui-console-page">
  @if(session('ok'))
    <div class="ui-alert ui-alert-success mb-4">{{ session('ok') }}</div>
  @endif

  <x-dashboard.page-header
    title="Banners"
    subtitle="Gerencie os destaques secundarios exibidos na home com visual mais limpo, institucional e compativel com o modo global."
  >
    @can('banners.manage')
      <a href="{{ route('coordenador.banners.create') }}" class="ui-btn-primary">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M11 11V5h2v6h6v2h-6v6h-2v-6H5v-2z"/></svg>
        Novo banner
      </a>
    @endcan
  </x-dashboard.page-header>

  <x-dashboard.section-card title="Lista de banners" subtitle="Formato sugerido 345x135 com cantos arredondados" class="ui-coord-dashboard-panel mt-5">
    <div class="ui-table-shell">
      <table class="w-full text-sm">
        <thead class="ui-table-head">
          <tr>
            <th class="px-4 py-3 text-left w-[130px]">Preview</th>
            <th class="px-4 py-3 text-left">Titulo</th>
            <th class="px-4 py-3 text-left">Status</th>
            <th class="px-4 py-3 text-left">Ordem</th>
            <th class="px-4 py-3 text-left">Atualizado</th>
            @if($canManage)
              <th class="px-4 py-3 text-right w-[190px]">Acoes</th>
            @endif
          </tr>
        </thead>
        <tbody>
          @forelse($banners as $b)
            <tr class="ui-table-row">
              <td class="px-4 py-3">
                @if($b->imagem_url)
                  <img src="{{ $b->imagem_url }}" alt="" class="ui-banner-card-thumb">
                @else
                  <div class="ui-banner-card-thumb ui-banner-card-thumb-empty"></div>
                @endif
              </td>

              <td class="px-4 py-3">
                <div class="font-semibold text-[var(--ui-text-title)]">{{ $b->titulo ?? '—' }}</div>
                @if($b->subtitulo)
                  <div class="mt-1 text-xs text-[var(--ui-text-soft)] line-clamp-1">{{ $b->subtitulo }}</div>
                @endif
              </td>

              <td class="px-4 py-3">
                @if($b->status === 'publicado')
                  <span class="ui-badge ui-badge-success">Publicado</span>
                @elseif($b->status === 'arquivado')
                  <span class="ui-badge ui-badge-warning">Arquivado</span>
                @else
                  <span class="ui-badge ui-badge-neutral">Rascunho</span>
                @endif
              </td>

              <td class="px-4 py-3 font-semibold text-[var(--ui-text-title)]">{{ $b->ordem }}</td>

              <td class="px-4 py-3 text-[var(--ui-text-soft)]">
                {{ optional($b->updated_at)->format('d/m/Y H:i') ?? '—' }}
              </td>

              @if($canManage)
                <td class="px-4 py-3 text-right">
                  <div class="ui-banner-module-actions justify-end">
                    <a href="{{ route('coordenador.banners.edit',$b) }}" class="ui-btn-secondary">Editar</a>
                    <form action="{{ route('coordenador.banners.destroy',$b) }}" method="post" class="inline" onsubmit="return confirm('Remover o banner &quot;{{ $b->titulo }}&quot;?')">
                      @csrf
                      @method('DELETE')
                      <button class="ui-btn-danger">Excluir</button>
                    </form>
                  </div>
                </td>
              @endif
            </tr>
          @empty
            <tr class="ui-table-row">
              <td colspan="{{ 5 + (int)$canManage }}" class="px-4 py-10 text-center text-[var(--ui-text-soft)]">
                Nenhum banner.
                @can('banners.manage')
                  Clique em <span class="font-semibold text-[var(--ui-text-title)]">Novo banner</span> para criar.
                @endcan
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-4">
      {{ $banners->links() }}
    </div>
  </x-dashboard.section-card>
</div>
@endsection
