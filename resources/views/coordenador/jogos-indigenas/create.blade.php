@extends('console.layout')

@section('title', 'Novo jogo indígena')
@section('page.title', 'Novo jogo indígena')
@section('topbar.description', 'Cadastre a entidade principal dos Jogos Indígenas com capa, perfil, publicação e slug no padrão do console.')

@section('topbar.nav')
  <a href="{{ route('coordenador.jogos-indigenas.index') }}" class="ui-console-topbar-tab">Jogos Indígenas</a>
  <span class="ui-console-topbar-tab is-active">Novo jogo</span>
@endsection

@section('content')
<div class="ui-console-page">
  @include('coordenador.partials.flash')

  <x-dashboard.page-header
    title="Criar Jogos Indígenas"
    subtitle="Preencha os dados principais do módulo para habilitar as edições na próxima etapa operacional."
  >
    <a href="{{ route('coordenador.jogos-indigenas.index') }}" class="ui-btn-secondary">Voltar</a>
  </x-dashboard.page-header>

  <form method="POST" action="{{ route('coordenador.jogos-indigenas.store') }}" enctype="multipart/form-data" class="mt-5 space-y-6">
    @csrf

    @include('coordenador.jogos-indigenas._form', ['mode' => 'create'])

    <div class="flex flex-wrap items-center gap-3 border-t border-[var(--ui-border)] pt-5">
      <button type="submit" class="ui-btn-primary">Salvar jogo</button>
      <a href="{{ route('coordenador.jogos-indigenas.index') }}" class="ui-btn-secondary">Cancelar</a>
    </div>
  </form>
</div>
@endsection
