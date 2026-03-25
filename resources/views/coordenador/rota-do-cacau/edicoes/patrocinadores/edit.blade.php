@extends('console.layout')

@section('title', 'Editar patrocinador - '.$edicao->titulo)
@section('page.title', 'Editar patrocinador da edicao')
@section('topbar.description', 'Atualize logo, nome, link e ordem de exibicao deste patrocinador da Rota do Cacau.')

@section('topbar.nav')
  <a href="{{ route('coordenador.rota-do-cacau.index') }}" class="ui-console-topbar-tab">Rota do Cacau</a>
  <a href="{{ route('coordenador.rota-do-cacau.edicoes.index', $rota) }}" class="ui-console-topbar-tab">Edicoes</a>
  <a href="{{ route('coordenador.rota-do-cacau.edicoes.patrocinadores.index', [$rota, $edicao]) }}" class="ui-console-topbar-tab">Patrocinadores</a>
  <span class="ui-console-topbar-tab is-active">Editar patrocinador</span>
@endsection

@section('content')
<div class="ui-console-page">
  @include('coordenador.partials.flash')

  <x-dashboard.page-header title="Editar patrocinador" subtitle="Mantenha a grade de apoiadores desta edicao organizada e consistente.">
    <x-slot:actions>
      <a href="{{ route('coordenador.rota-do-cacau.edicoes.patrocinadores.index', [$rota, $edicao]) }}" class="ui-btn-secondary">Voltar</a>
    </x-slot:actions>
  </x-dashboard.page-header>

  <form method="POST" action="{{ route('coordenador.rota-do-cacau.edicoes.patrocinadores.update', [$rota, $edicao, $patrocinador]) }}" enctype="multipart/form-data" class="mt-5 space-y-6">
    @csrf
    @method('PUT')

    @include('coordenador.rota-do-cacau.edicoes.patrocinadores._form', ['mode' => 'edit'])

    <div class="flex flex-wrap items-center gap-3 border-t border-[var(--ui-border)] pt-5">
      <button type="submit" class="ui-btn-primary">Salvar alteracoes</button>
      <a href="{{ route('coordenador.rota-do-cacau.edicoes.patrocinadores.index', [$rota, $edicao]) }}" class="ui-btn-secondary">Cancelar</a>
    </div>
  </form>
</div>
@endsection
