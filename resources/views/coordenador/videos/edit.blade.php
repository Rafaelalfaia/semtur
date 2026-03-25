@extends('console.layout')

@section('title', 'Editar video')
@section('page.title', 'Editar video')
@section('topbar.description', 'Atualize videos institucionais mantendo o shell, o modo global e a futura base de temas.')

@section('topbar.nav')
  <a href="{{ route('coordenador.videos.index') }}" class="ui-console-topbar-tab">Videos</a>
  <span class="ui-console-topbar-tab is-active">Editar video</span>
@endsection

@section('content')
<div class="ui-console-page">
  @include('coordenador.partials.flash')

  <x-dashboard.page-header
    title="Editar video"
    subtitle="Atualize o conteudo, teste o link e controle exatamente o que aparece na area publica."
  >
    <div class="flex flex-wrap gap-2">
      @if(($video->status ?? null) === 'publicado' && Route::has('site.videos.show'))
        <a href="{{ route('site.videos.show', $video->slug) }}" target="_blank" class="ui-btn-secondary">Ver no site</a>
      @endif
      <a href="{{ route('coordenador.videos.index') }}" class="ui-btn-secondary">Voltar</a>
    </div>
  </x-dashboard.page-header>

  <div class="mt-5 flex flex-wrap gap-2">
    @can('videos.publicar')
      @if(($video->status ?? null) !== 'publicado')
        <form method="POST" action="{{ route('coordenador.videos.publicar', $video) }}">
          @csrf
          @method('PATCH')
          <button class="ui-btn-primary">Publicar agora</button>
        </form>
      @endif
    @endcan

    @can('videos.rascunho')
      @if(($video->status ?? null) !== 'rascunho')
        <form method="POST" action="{{ route('coordenador.videos.rascunho', $video) }}">
          @csrf
          @method('PATCH')
          <button class="ui-btn-secondary">Mover para rascunho</button>
        </form>
      @endif
    @endcan

    @can('videos.arquivar')
      @if(($video->status ?? null) !== 'arquivado')
        <form method="POST" action="{{ route('coordenador.videos.arquivar', $video) }}">
          @csrf
          @method('PATCH')
          <button class="ui-btn-secondary">Arquivar</button>
        </form>
      @endif
    @endcan

    @can('videos.delete')
      <form method="POST" action="{{ route('coordenador.videos.destroy', $video) }}" onsubmit="return confirm('Excluir este video?');">
        @csrf
        @method('DELETE')
        <button class="ui-btn-danger">Excluir</button>
      </form>
    @endcan
  </div>

  <form method="POST" action="{{ route('coordenador.videos.update', $video) }}" enctype="multipart/form-data" class="mt-5 space-y-6">
    @csrf
    @method('PUT')

    @include('coordenador.videos._form', ['mode' => 'edit'])

    <div class="flex flex-wrap items-center gap-3 border-t border-[var(--ui-border)] pt-5">
      <button type="submit" class="ui-btn-primary">Salvar alteracoes</button>
      <a href="{{ route('coordenador.videos.index') }}" class="ui-btn-secondary">Cancelar</a>
    </div>
  </form>
</div>
@endsection
