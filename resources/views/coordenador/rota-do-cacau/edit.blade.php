@extends('console.layout')

@section('title', 'Editar Rota do Cacau')
@section('page.title', 'Editar Rota do Cacau')
@section('topbar.description', 'Atualize a estrutura principal da Rota do Cacau mantendo o mesmo shell e a heranca global do console.')

@section('topbar.nav')
  <a href="{{ route('coordenador.rota-do-cacau.index') }}" class="ui-console-topbar-tab">Rota do Cacau</a>
  <span class="ui-console-topbar-tab is-active">Editar cadastro</span>
@endsection

@section('content')
<div class="ui-console-page">
  @include('coordenador.partials.flash')

  <x-dashboard.page-header
    title="Editar Rota do Cacau"
    subtitle="Ajuste conteudo, publicacao, ordem e imagens do cadastro principal."
  >
    <div class="flex flex-wrap gap-2">
      @can('rota_do_cacau.edicoes.view')
        <a href="{{ route('coordenador.rota-do-cacau.edicoes.index', $rota) }}" class="ui-btn-secondary">Ver edicoes</a>
      @endcan
      <a href="{{ route('coordenador.rota-do-cacau.index') }}" class="ui-btn-secondary">Voltar</a>
    </div>
  </x-dashboard.page-header>

  <form method="POST" action="{{ route('coordenador.rota-do-cacau.update', $rota) }}" enctype="multipart/form-data" class="mt-5 space-y-6">
    @csrf
    @method('PUT')

    @include('coordenador.rota-do-cacau._form', ['mode' => 'edit'])

    <div class="flex flex-wrap items-center gap-3 border-t border-[var(--ui-border)] pt-5">
      <button type="submit" class="ui-btn-primary">Salvar alteracoes</button>
      <a href="{{ route('coordenador.rota-do-cacau.index') }}" class="ui-btn-secondary">Cancelar</a>
    </div>
  </form>
</div>
@endsection
