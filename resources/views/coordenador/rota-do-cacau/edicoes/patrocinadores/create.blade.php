@extends('console.layout')

@section('title', 'Novo patrocinador - '.$edicao->titulo)
@section('page.title', 'Novo patrocinador da edicao')
@section('topbar.description', 'Cadastre um patrocinador vinculado diretamente a esta edicao da Rota do Cacau.')

@section('topbar.nav')
  <a href="{{ route('coordenador.rota-do-cacau.index') }}" class="ui-console-topbar-tab">Rota do Cacau</a>
  <a href="{{ route('coordenador.rota-do-cacau.edicoes.index', $rota) }}" class="ui-console-topbar-tab">Edicoes</a>
  <a href="{{ route('coordenador.rota-do-cacau.edicoes.patrocinadores.index', [$rota, $edicao]) }}" class="ui-console-topbar-tab">Patrocinadores</a>
  <span class="ui-console-topbar-tab is-active">Novo patrocinador</span>
@endsection

@section('content')
<div class="ui-console-page">
  @include('coordenador.partials.flash')

  <x-dashboard.page-header title="Adicionar patrocinador" subtitle="Apoios e logos ficam vinculados a esta edicao, sem misturar com o cadastro principal.">
    <x-slot:actions>
      <a href="{{ route('coordenador.rota-do-cacau.edicoes.patrocinadores.index', [$rota, $edicao]) }}" class="ui-btn-secondary">Voltar</a>
    </x-slot:actions>
  </x-dashboard.page-header>

  <form method="POST" action="{{ route('coordenador.rota-do-cacau.edicoes.patrocinadores.store', [$rota, $edicao]) }}" enctype="multipart/form-data" class="mt-5 space-y-6">
    @csrf

    @include('coordenador.rota-do-cacau.edicoes.patrocinadores._form', ['mode' => 'create'])

    <div class="flex flex-wrap items-center gap-3 border-t border-[var(--ui-border)] pt-5">
      <button type="submit" class="ui-btn-primary">Salvar patrocinador</button>
      <a href="{{ route('coordenador.rota-do-cacau.edicoes.patrocinadores.index', [$rota, $edicao]) }}" class="ui-btn-secondary">Cancelar</a>
    </div>
  </form>
</div>
@endsection
