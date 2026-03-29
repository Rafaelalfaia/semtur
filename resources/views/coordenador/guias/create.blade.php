@extends('console.layout')

@section('title', 'Novo material')
@section('page.title', 'Novo material')
@section('topbar.description', 'Cadastre um guia ou revista com capa, descrição e acesso externo seguindo o padrão global do console.')

@section('topbar.nav')
  <a href="{{ route('coordenador.guias.index') }}" class="ui-console-topbar-tab">Guias e Revistas</a>
  <span class="ui-console-topbar-tab is-active">Novo material</span>
@endsection

@section('content')
<div class="ui-console-page">
  @include('coordenador.partials.flash')

  <x-dashboard.page-header
    title="Criar guia ou revista"
    subtitle="Cadastre materiais oficiais com capa, descricao e link do Google Drive para abrir dentro do site."
  >
    <a href="{{ route('coordenador.guias.index') }}" class="ui-btn-secondary">Voltar</a>
  </x-dashboard.page-header>

  <form method="POST" action="{{ route('coordenador.guias.store') }}" enctype="multipart/form-data" class="mt-5 space-y-6">
    @csrf

    @include('coordenador.guias._form', ['mode' => 'create'])

    <div class="flex flex-wrap items-center gap-3 border-t border-[var(--ui-border)] pt-5">
      <button type="submit" class="ui-btn-primary">Salvar material</button>
      <a href="{{ route('coordenador.guias.index') }}" class="ui-btn-secondary">Cancelar</a>
    </div>
  </form>
</div>
@endsection
