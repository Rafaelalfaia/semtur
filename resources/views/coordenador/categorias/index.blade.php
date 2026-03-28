@extends('console.layout')
@section('title','Categorias - Coordenador')
@section('page.title','Categorias')
@section('topbar.description', 'Gerencie categorias com filtros, status editoriais e o mesmo padrão visual do console compartilhado.')

@section('topbar.nav')
  <span class="ui-console-topbar-tab is-active">Categorias</span>
  @can('categorias.create')
    <a href="{{ route('coordenador.categorias.create') }}" class="ui-console-topbar-tab">Nova categoria</a>
  @endcan
@endsection

@section('content')
<div class="ui-console-page">
  @if(session('ok'))
    <div class="ui-alert ui-alert-success mb-4">{{ session('ok') }}</div>
  @endif

  <x-dashboard.page-header
    title="Categorias"
    subtitle="Organize a catalogação do portal com filtros, status e uma leitura mais limpa, leve e consistente com o novo console."
  >
    @can('categorias.create')
      <a href="{{ route('coordenador.categorias.create') }}" class="ui-btn-primary">Nova categoria</a>
    @endcan
  </x-dashboard.page-header>

  <x-dashboard.section-card title="Filtros" subtitle="Busque por nome e refine pelo status editorial" class="ui-coord-dashboard-panel mt-5">
    <form method="GET" class="grid grid-cols-1 gap-3 md:grid-cols-[240px_220px_auto]">
      <input
        type="text"
        name="busca"
        value="{{ $busca }}"
        placeholder="Digite pelo menos 3 letras..."
        class="ui-form-control"
      >
      @php $opts = ['todos'=>'Todos','rascunho'=>'Rascunho','publicado'=>'Publicado','arquivado'=>'Arquivado']; @endphp
      <select name="status" class="ui-form-select">
        @foreach($opts as $k=>$v)
          <option value="{{ $k }}" @selected($status===$k)>{{ $v }}</option>
        @endforeach
      </select>
      <button class="ui-btn-secondary">Filtrar</button>
    </form>
  </x-dashboard.section-card>

  <div class="ui-category-grid mt-5">
    @forelse($categorias as $c)
      @php
        $isDraft = $c->status === 'rascunho';
        $isPub   = $c->status === 'publicado';
        $isArch  = $c->status === 'arquivado';
      @endphp

      <article class="ui-category-card">
        <div class="flex items-start gap-3">
          <div class="ui-category-icon-shell">
            @if($c->icone_url)
              <img src="{{ $c->icone_url }}" alt="Ícone {{ $c->nome }}" class="ui-category-icon-image">
            @else
              <span class="text-xs text-[var(--ui-text-soft)]">sem ícone</span>
            @endif
          </div>

          <div class="flex-1 min-w-0">
            <div class="font-semibold text-[var(--ui-text-title)]">{{ $c->nome }}</div>
            <div class="text-xs text-[var(--ui-text-soft)]">/{{ $c->slug }}</div>
            <div class="mt-2">
              @if($isPub)
                <span class="ui-badge ui-badge-success">Publicado</span>
              @elseif($isArch)
                <span class="ui-badge ui-badge-danger">Arquivado</span>
              @else
                <span class="ui-badge ui-badge-neutral">Rascunho</span>
              @endif
            </div>
          </div>
        </div>

        <div class="mt-4 flex items-center gap-2">
          @can('categorias.update')
            <a href="{{ route('coordenador.categorias.edit',$c) }}" class="ui-btn-secondary">Editar</a>
          @endcan

          @can('categorias.delete')
            <form method="POST" action="{{ route('coordenador.categorias.destroy',$c) }}" onsubmit="return confirm('Remover categoria?');" class="inline">
              @csrf
              @method('DELETE')
              <button class="ui-btn-danger">Excluir</button>
            </form>
          @endcan
        </div>

        <div class="mt-4 ui-category-status-actions">
          @can('categorias.rascunho')
            <form method="POST" action="{{ route('coordenador.categorias.rascunho',$c) }}">
              @csrf
              @method('PATCH')
              <button @disabled($isDraft) class="ui-category-status-btn {{ $isDraft ? 'is-disabled' : '' }}">Rascunho</button>
            </form>
          @endcan

          @can('categorias.publicar')
            <form method="POST" action="{{ route('coordenador.categorias.publicar',$c) }}">
              @csrf
              @method('PATCH')
              <button @disabled($isPub) class="ui-category-status-btn {{ $isPub ? 'is-disabled' : '' }}">Publicar</button>
            </form>
          @endcan

          @can('categorias.arquivar')
            <form method="POST" action="{{ route('coordenador.categorias.arquivar',$c) }}">
              @csrf
              @method('PATCH')
              <button @disabled($isArch) class="ui-category-status-btn {{ $isArch ? 'is-disabled' : '' }}">Arquivar</button>
            </form>
          @endcan
        </div>
      </article>
    @empty
      <x-dashboard.section-card title="Nenhuma categoria" subtitle="{{ mb_strlen(trim((string) $busca)) < 3 ? 'Digite pelo menos 3 letras para pesquisar categorias.' : 'Nenhuma categoria encontrada para a busca informada.' }}" class="ui-coord-dashboard-panel">
        <p class="text-sm text-[var(--ui-text-soft)]">
          {{ mb_strlen(trim((string) $busca)) < 3 ? 'A listagem fica vazia até que a pesquisa tenha no mínimo 3 letras.' : 'Ajuste os filtros ou tente outro termo de busca.' }}
        </p>
      </x-dashboard.section-card>
    @endforelse
  </div>

  <div class="mt-5">{{ $categorias->links() }}</div>
</div>
@endsection
