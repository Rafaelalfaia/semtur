@extends('console.layout')

@section('title', 'Nova Rota do Cacau')
@section('page.title', 'Nova Rota do Cacau')
@section('topbar.description', 'Cadastre a entidade principal da Rota do Cacau com capa, perfil, publicacao e slug no padrao do console.')

@section('topbar.nav')
  <a href="{{ route('coordenador.rota-do-cacau.index') }}" class="ui-console-topbar-tab">Rota do Cacau</a>
  <span class="ui-console-topbar-tab is-active">Novo cadastro</span>
@endsection

@section('content')
<div class="ui-console-page">
  @include('coordenador.partials.flash')

  <x-dashboard.page-header
    title="Criar Rota do Cacau"
    subtitle="Preencha os dados principais do modulo para habilitar as edicoes na proxima etapa operacional."
  >
    <a href="{{ route('coordenador.rota-do-cacau.index') }}" class="ui-btn-secondary">Voltar</a>
  </x-dashboard.page-header>

  <form method="POST" action="{{ route('coordenador.rota-do-cacau.store') }}" enctype="multipart/form-data" class="mt-5 space-y-6">
    @csrf

    @include('coordenador.rota-do-cacau._form', ['mode' => 'create'])

    <div class="flex flex-wrap items-center gap-3 border-t border-[var(--ui-border)] pt-5">
      <button type="submit" class="ui-btn-primary">Salvar cadastro</button>
      <a href="{{ route('coordenador.rota-do-cacau.index') }}" class="ui-btn-secondary">Cancelar</a>
    </div>
  </form>
</div>
@endsection
