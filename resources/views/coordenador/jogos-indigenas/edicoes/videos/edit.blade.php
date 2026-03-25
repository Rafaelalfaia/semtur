@extends('console.layout')

@section('title', 'Editar video - '.$edicao->titulo)
@section('page.title', 'Editar video da edicao')
@section('topbar.description', 'Atualize os dados do video mantendo o vinculo com a edicao.')

@section('topbar.nav')
  <a href="{{ route('coordenador.jogos-indigenas.index') }}" class="ui-console-topbar-tab">Jogos Indigenas</a>
  <a href="{{ route('coordenador.jogos-indigenas.edicoes.index', $jogo) }}" class="ui-console-topbar-tab">Edicoes</a>
  <a href="{{ route('coordenador.jogos-indigenas.edicoes.videos.index', [$jogo, $edicao]) }}" class="ui-console-topbar-tab">Videos</a>
  <span class="ui-console-topbar-tab is-active">Editar video</span>
@endsection

@section('content')
<div class="ui-console-page">
  @include('coordenador.partials.flash')

  <x-dashboard.page-header title="Editar video" subtitle="Ajuste o link, o preview e a ordem do conteudo audiovisual desta edicao.">
    <x-slot:actions>
      <a href="{{ route('coordenador.jogos-indigenas.edicoes.videos.index', [$jogo, $edicao]) }}" class="ui-btn-secondary">Voltar</a>
    </x-slot:actions>
  </x-dashboard.page-header>

  <form method="POST" action="{{ route('coordenador.jogos-indigenas.edicoes.videos.update', [$jogo, $edicao, $video]) }}" class="mt-5 space-y-6">
    @csrf
    @method('PUT')
    @include('coordenador.jogos-indigenas.edicoes.videos._form')
    <div class="flex flex-wrap items-center gap-3 border-t border-[var(--ui-border)] pt-5">
      <button type="submit" class="ui-btn-primary">Salvar alteracoes</button>
      <a href="{{ route('coordenador.jogos-indigenas.edicoes.videos.index', [$jogo, $edicao]) }}" class="ui-btn-secondary">Cancelar</a>
    </div>
  </form>
</div>
@endsection
