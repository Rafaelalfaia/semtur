@extends('console.layout')

@section('title', 'Editar roteiro')
@section('page.title', 'Editar roteiro')
@section('topbar.description', 'Atualize um roteiro com a mesma base estrutural, visual e de modos usada no restante do console.')

@section('topbar.nav')
  <a href="{{ route('coordenador.roteiros.index') }}" class="ui-console-topbar-tab">Roteiros</a>
  <span class="ui-console-topbar-tab is-active">Editar roteiro</span>
@endsection

@section('content')
<div class="ui-console-page">
  @include('coordenador.partials.flash')

  <x-dashboard.page-header
    title="Editar roteiro"
    subtitle="Atualize os textos, reorganize as etapas e controle exatamente o que aparece no site."
  >
    <div class="flex flex-wrap gap-2">
      @if(($roteiro->status ?? null) === 'publicado' && Route::has('site.roteiros.show'))
        <a href="{{ route('site.roteiros.show', $roteiro->slug) }}" target="_blank" class="ui-btn-secondary">Ver no site</a>
      @endif
      <a href="{{ route('coordenador.roteiros.index') }}" class="ui-btn-secondary">Voltar</a>
    </div>
  </x-dashboard.page-header>

  <div class="mt-5 flex flex-wrap gap-2">
    @can('roteiros.publicar')
      @if(($roteiro->status ?? null) !== 'publicado')
        <form method="POST" action="{{ route('coordenador.roteiros.publicar', $roteiro) }}">
          @csrf
          @method('PATCH')
          <button class="ui-btn-primary">Publicar agora</button>
        </form>
      @endif
    @endcan

    @can('roteiros.rascunho')
      @if(($roteiro->status ?? null) !== 'rascunho')
        <form method="POST" action="{{ route('coordenador.roteiros.rascunho', $roteiro) }}">
          @csrf
          @method('PATCH')
          <button class="ui-btn-secondary">Mover para rascunho</button>
        </form>
      @endif
    @endcan

    @can('roteiros.arquivar')
      @if(($roteiro->status ?? null) !== 'arquivado')
        <form method="POST" action="{{ route('coordenador.roteiros.arquivar', $roteiro) }}">
          @csrf
          @method('PATCH')
          <button class="ui-btn-secondary">Arquivar</button>
        </form>
      @endif
    @endcan

    @can('roteiros.delete')
      <form method="POST" action="{{ route('coordenador.roteiros.destroy', $roteiro) }}" onsubmit="return confirm('Excluir este roteiro?');">
        @csrf
        @method('DELETE')
        <button class="ui-btn-danger">Excluir</button>
      </form>
    @endcan
  </div>

  <form method="POST" action="{{ route('coordenador.roteiros.update', $roteiro) }}" enctype="multipart/form-data" class="mt-5 space-y-6">
    @csrf
    @method('PUT')

    @include('coordenador.roteiros._form', ['mode' => 'edit'])

    <div class="flex flex-wrap items-center gap-3 border-t border-[var(--ui-border)] pt-5">
      <button type="submit" class="ui-btn-primary">Salvar alteracoes</button>
      <a href="{{ route('coordenador.roteiros.index') }}" class="ui-btn-secondary">Cancelar</a>
    </div>
  </form>
</div>
@endsection
