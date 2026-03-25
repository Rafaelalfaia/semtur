@extends('console.layout')

@section('title', 'Nova foto - '.$edicao->titulo)
@section('page.title', 'Nova foto da edicao')
@section('topbar.description', 'Cadastre uma nova foto na galeria da edicao dentro do padrao visual do console.')

@section('topbar.nav')
  <a href="{{ route('coordenador.jogos-indigenas.index') }}" class="ui-console-topbar-tab">Jogos Indigenas</a>
  <a href="{{ route('coordenador.jogos-indigenas.edicoes.index', $jogo) }}" class="ui-console-topbar-tab">Edicoes</a>
  <a href="{{ route('coordenador.jogos-indigenas.edicoes.fotos.index', [$jogo, $edicao]) }}" class="ui-console-topbar-tab">Fotos</a>
  <span class="ui-console-topbar-tab is-active">Nova foto</span>
@endsection

@section('content')
<div class="ui-console-page">
  @include('coordenador.partials.flash')

  <x-dashboard.page-header title="Cadastrar foto" subtitle="Envie a imagem, defina legenda opcional e a ordem da galeria.">
    <x-slot:actions>
      <a href="{{ route('coordenador.jogos-indigenas.edicoes.fotos.index', [$jogo, $edicao]) }}" class="ui-btn-secondary">Voltar</a>
    </x-slot:actions>
  </x-dashboard.page-header>

  <form method="POST" action="{{ route('coordenador.jogos-indigenas.edicoes.fotos.store', [$jogo, $edicao]) }}" enctype="multipart/form-data" class="mt-5 space-y-6">
    @csrf
    @include('coordenador.jogos-indigenas.edicoes.fotos._form', ['mode' => 'create'])
    <div class="flex flex-wrap items-center gap-3 border-t border-[var(--ui-border)] pt-5">
      <button type="submit" class="ui-btn-primary">Salvar foto</button>
      <a href="{{ route('coordenador.jogos-indigenas.edicoes.fotos.index', [$jogo, $edicao]) }}" class="ui-btn-secondary">Cancelar</a>
    </div>
  </form>
</div>
@endsection
