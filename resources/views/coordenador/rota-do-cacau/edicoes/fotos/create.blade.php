@extends('console.layout')

@section('title', 'Nova foto - '.$edicao->titulo)
@section('page.title', 'Nova foto da edicao')
@section('topbar.description', 'Cadastre uma foto da galeria vinculada diretamente a esta edicao da Rota do Cacau.')

@section('topbar.nav')
  <a href="{{ route('coordenador.rota-do-cacau.index') }}" class="ui-console-topbar-tab">Rota do Cacau</a>
  <a href="{{ route('coordenador.rota-do-cacau.edicoes.index', $rota) }}" class="ui-console-topbar-tab">Edicoes</a>
  <a href="{{ route('coordenador.rota-do-cacau.edicoes.fotos.index', [$rota, $edicao]) }}" class="ui-console-topbar-tab">Fotos</a>
  <span class="ui-console-topbar-tab is-active">Nova foto</span>
@endsection

@section('content')
<div class="ui-console-page">
  @include('coordenador.partials.flash')

  <x-dashboard.page-header title="Adicionar foto" subtitle="A imagem sera exibida exclusivamente na galeria desta edicao.">
    <x-slot:actions>
      <a href="{{ route('coordenador.rota-do-cacau.edicoes.fotos.index', [$rota, $edicao]) }}" class="ui-btn-secondary">Voltar</a>
    </x-slot:actions>
  </x-dashboard.page-header>

  <form method="POST" action="{{ route('coordenador.rota-do-cacau.edicoes.fotos.store', [$rota, $edicao]) }}" enctype="multipart/form-data" class="mt-5 space-y-6">
    @csrf

    @include('coordenador.rota-do-cacau.edicoes.fotos._form', ['mode' => 'create'])

    <div class="flex flex-wrap items-center gap-3 border-t border-[var(--ui-border)] pt-5">
      <button type="submit" class="ui-btn-primary">Salvar foto</button>
      <a href="{{ route('coordenador.rota-do-cacau.edicoes.fotos.index', [$rota, $edicao]) }}" class="ui-btn-secondary">Cancelar</a>
    </div>
  </form>
</div>
@endsection
