@extends('console.layout')

@section('title', 'Novo video - '.$edicao->titulo)
@section('page.title', 'Novo video da edicao')
@section('topbar.description', 'Cadastre um link de video vinculado diretamente a esta edicao da Rota do Cacau.')

@section('topbar.nav')
  <a href="{{ route('coordenador.rota-do-cacau.index') }}" class="ui-console-topbar-tab">Rota do Cacau</a>
  <a href="{{ route('coordenador.rota-do-cacau.edicoes.index', $rota) }}" class="ui-console-topbar-tab">Edicoes</a>
  <a href="{{ route('coordenador.rota-do-cacau.edicoes.videos.index', [$rota, $edicao]) }}" class="ui-console-topbar-tab">Videos</a>
  <span class="ui-console-topbar-tab is-active">Novo video</span>
@endsection

@section('content')
<div class="ui-console-page">
  @include('coordenador.partials.flash')

  <x-dashboard.page-header title="Adicionar video" subtitle="Use preferencialmente o link original do Google Drive para o helper resolver o preview.">
    <x-slot:actions>
      <a href="{{ route('coordenador.rota-do-cacau.edicoes.videos.index', [$rota, $edicao]) }}" class="ui-btn-secondary">Voltar</a>
    </x-slot:actions>
  </x-dashboard.page-header>

  <form method="POST" action="{{ route('coordenador.rota-do-cacau.edicoes.videos.store', [$rota, $edicao]) }}" class="mt-5 space-y-6">
    @csrf

    @include('coordenador.rota-do-cacau.edicoes.videos._form')

    <div class="flex flex-wrap items-center gap-3 border-t border-[var(--ui-border)] pt-5">
      <button type="submit" class="ui-btn-primary">Salvar video</button>
      <a href="{{ route('coordenador.rota-do-cacau.edicoes.videos.index', [$rota, $edicao]) }}" class="ui-btn-secondary">Cancelar</a>
    </div>
  </form>
</div>
@endsection
