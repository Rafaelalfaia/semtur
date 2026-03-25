@extends('console.layout')

@section('title', 'Editar foto - '.$edicao->titulo)
@section('page.title', 'Editar foto da edicao')
@section('topbar.description', 'Atualize a imagem, a legenda e a ordem da foto desta edicao.')

@section('topbar.nav')
  <a href="{{ route('coordenador.jogos-indigenas.index') }}" class="ui-console-topbar-tab">Jogos Indigenas</a>
  <a href="{{ route('coordenador.jogos-indigenas.edicoes.index', $jogo) }}" class="ui-console-topbar-tab">Edicoes</a>
  <a href="{{ route('coordenador.jogos-indigenas.edicoes.fotos.index', [$jogo, $edicao]) }}" class="ui-console-topbar-tab">Fotos</a>
  <span class="ui-console-topbar-tab is-active">Editar foto</span>
@endsection

@section('content')
<div class="ui-console-page">
  @include('coordenador.partials.flash')

  <x-dashboard.page-header title="Editar foto" subtitle="Mantenha a galeria da edicao organizada e com boa leitura visual.">
    <x-slot:actions>
      <a href="{{ route('coordenador.jogos-indigenas.edicoes.fotos.index', [$jogo, $edicao]) }}" class="ui-btn-secondary">Voltar</a>
    </x-slot:actions>
  </x-dashboard.page-header>

  <form method="POST" action="{{ route('coordenador.jogos-indigenas.edicoes.fotos.update', [$jogo, $edicao, $foto]) }}" enctype="multipart/form-data" class="mt-5 space-y-6">
    @csrf
    @method('PUT')
    @include('coordenador.jogos-indigenas.edicoes.fotos._form', ['mode' => 'edit'])
    <div class="flex flex-wrap items-center gap-3 border-t border-[var(--ui-border)] pt-5">
      <button type="submit" class="ui-btn-primary">Salvar alteracoes</button>
      <a href="{{ route('coordenador.jogos-indigenas.edicoes.fotos.index', [$jogo, $edicao]) }}" class="ui-btn-secondary">Cancelar</a>
    </div>
  </form>
</div>
@endsection
