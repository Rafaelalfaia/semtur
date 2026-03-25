@extends('console.layout')

@section('title', 'Editar patrocinador - '.$edicao->titulo)
@section('page.title', 'Editar patrocinador da edicao')
@section('topbar.description', 'Atualize o patrocinador mantendo o vinculo com esta edicao.')

@section('topbar.nav')
  <a href="{{ route('coordenador.jogos-indigenas.index') }}" class="ui-console-topbar-tab">Jogos Indigenas</a>
  <a href="{{ route('coordenador.jogos-indigenas.edicoes.index', $jogo) }}" class="ui-console-topbar-tab">Edicoes</a>
  <a href="{{ route('coordenador.jogos-indigenas.edicoes.patrocinadores.index', [$jogo, $edicao]) }}" class="ui-console-topbar-tab">Patrocinadores</a>
  <span class="ui-console-topbar-tab is-active">Editar patrocinador</span>
@endsection

@section('content')
<div class="ui-console-page">
  @include('coordenador.partials.flash')

  <x-dashboard.page-header title="Editar patrocinador" subtitle="Ajuste logo, URL e ordem de exibicao deste apoiador.">
    <x-slot:actions>
      <a href="{{ route('coordenador.jogos-indigenas.edicoes.patrocinadores.index', [$jogo, $edicao]) }}" class="ui-btn-secondary">Voltar</a>
    </x-slot:actions>
  </x-dashboard.page-header>

  <form method="POST" action="{{ route('coordenador.jogos-indigenas.edicoes.patrocinadores.update', [$jogo, $edicao, $patrocinador]) }}" enctype="multipart/form-data" class="mt-5 space-y-6">
    @csrf
    @method('PUT')
    @include('coordenador.jogos-indigenas.edicoes.patrocinadores._form', ['mode' => 'edit'])
    <div class="flex flex-wrap items-center gap-3 border-t border-[var(--ui-border)] pt-5">
      <button type="submit" class="ui-btn-primary">Salvar alteracoes</button>
      <a href="{{ route('coordenador.jogos-indigenas.edicoes.patrocinadores.index', [$jogo, $edicao]) }}" class="ui-btn-secondary">Cancelar</a>
    </div>
  </form>
</div>
@endsection
