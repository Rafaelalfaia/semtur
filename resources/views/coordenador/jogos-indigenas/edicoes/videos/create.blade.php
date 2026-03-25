@extends('console.layout')

@section('title', 'Novo video - '.$edicao->titulo)
@section('page.title', 'Novo video da edicao')
@section('topbar.description', 'Cadastre um video por link do Google Drive dentro do padrao do console.')

@section('topbar.nav')
  <a href="{{ route('coordenador.jogos-indigenas.index') }}" class="ui-console-topbar-tab">Jogos Indigenas</a>
  <a href="{{ route('coordenador.jogos-indigenas.edicoes.index', $jogo) }}" class="ui-console-topbar-tab">Edicoes</a>
  <a href="{{ route('coordenador.jogos-indigenas.edicoes.videos.index', [$jogo, $edicao]) }}" class="ui-console-topbar-tab">Videos</a>
  <span class="ui-console-topbar-tab is-active">Novo video</span>
@endsection

@section('content')
<div class="ui-console-page">
  @include('coordenador.partials.flash')

  <x-dashboard.page-header title="Cadastrar video" subtitle="Informe o link original do Drive e, se quiser, uma URL de embed personalizada.">
    <x-slot:actions>
      <a href="{{ route('coordenador.jogos-indigenas.edicoes.videos.index', [$jogo, $edicao]) }}" class="ui-btn-secondary">Voltar</a>
    </x-slot:actions>
  </x-dashboard.page-header>

  <form method="POST" action="{{ route('coordenador.jogos-indigenas.edicoes.videos.store', [$jogo, $edicao]) }}" class="mt-5 space-y-6">
    @csrf
    @include('coordenador.jogos-indigenas.edicoes.videos._form')
    <div class="flex flex-wrap items-center gap-3 border-t border-[var(--ui-border)] pt-5">
      <button type="submit" class="ui-btn-primary">Salvar video</button>
      <a href="{{ route('coordenador.jogos-indigenas.edicoes.videos.index', [$jogo, $edicao]) }}" class="ui-btn-secondary">Cancelar</a>
    </div>
  </form>
</div>
@endsection
