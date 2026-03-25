@extends('console.layout')

@section('title', 'Editar material')
@section('page.title', 'Editar material')
@section('topbar.description', 'Atualize materiais institucionais mantendo o shell, o modo global e a futura base de temas.')

@section('topbar.nav')
  <a href="{{ route('coordenador.guias.index') }}" class="ui-console-topbar-tab">Guias e Revistas</a>
  <span class="ui-console-topbar-tab is-active">Editar material</span>
@endsection

@section('content')
<div class="ui-console-page">
  @include('coordenador.partials.flash')

  <x-dashboard.page-header
    title="Editar guia ou revista"
    subtitle="Atualize o conteudo, teste o link e controle exatamente o que aparece na area publica."
  >
    <div class="flex flex-wrap gap-2">
      @if(($guia->status ?? null) === 'publicado' && Route::has('site.guias.show'))
        <a href="{{ route('site.guias.show', $guia->slug) }}" target="_blank" class="ui-btn-secondary">Ver no site</a>
      @endif
      <a href="{{ route('coordenador.guias.index') }}" class="ui-btn-secondary">Voltar</a>
    </div>
  </x-dashboard.page-header>

  <div class="mt-5 flex flex-wrap gap-2">
    @can('guias.publicar')
      @if(($guia->status ?? null) !== 'publicado')
        <form method="POST" action="{{ route('coordenador.guias.publicar', $guia) }}">
          @csrf
          @method('PATCH')
          <button class="ui-btn-primary">Publicar agora</button>
        </form>
      @endif
    @endcan

    @can('guias.rascunho')
      @if(($guia->status ?? null) !== 'rascunho')
        <form method="POST" action="{{ route('coordenador.guias.rascunho', $guia) }}">
          @csrf
          @method('PATCH')
          <button class="ui-btn-secondary">Mover para rascunho</button>
        </form>
      @endif
    @endcan

    @can('guias.arquivar')
      @if(($guia->status ?? null) !== 'arquivado')
        <form method="POST" action="{{ route('coordenador.guias.arquivar', $guia) }}">
          @csrf
          @method('PATCH')
          <button class="ui-btn-secondary">Arquivar</button>
        </form>
      @endif
    @endcan

    @can('guias.delete')
      <form method="POST" action="{{ route('coordenador.guias.destroy', $guia) }}" onsubmit="return confirm('Excluir este material?');">
        @csrf
        @method('DELETE')
        <button class="ui-btn-danger">Excluir</button>
      </form>
    @endcan
  </div>

  <form method="POST" action="{{ route('coordenador.guias.update', $guia) }}" enctype="multipart/form-data" class="mt-5 space-y-6">
    @csrf
    @method('PUT')

    @include('coordenador.guias._form', ['mode' => 'edit'])

    <div class="flex flex-wrap items-center gap-3 border-t border-[var(--ui-border)] pt-5">
      <button type="submit" class="ui-btn-primary">Salvar alteracoes</button>
      <a href="{{ route('coordenador.guias.index') }}" class="ui-btn-secondary">Cancelar</a>
    </div>
  </form>
</div>
@endsection
