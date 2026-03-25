@extends('console.layout')

@section('title', 'Novo video')
@section('page.title', 'Novo video')
@section('topbar.description', 'Cadastre um video institucional com capa, descricao e acesso externo seguindo o padrao global do console.')

@section('topbar.nav')
  <a href="{{ route('coordenador.videos.index') }}" class="ui-console-topbar-tab">Videos</a>
  <span class="ui-console-topbar-tab is-active">Novo video</span>
@endsection

@section('content')
<div class="ui-console-page">
  @include('coordenador.partials.flash')

  <x-dashboard.page-header
    title="Criar video"
    subtitle="Cadastre videos oficiais com capa, descricao e link do Google Drive para abrir dentro do site."
  >
    <a href="{{ route('coordenador.videos.index') }}" class="ui-btn-secondary">Voltar</a>
  </x-dashboard.page-header>

  <form method="POST" action="{{ route('coordenador.videos.store') }}" enctype="multipart/form-data" class="mt-5 space-y-6">
    @csrf

    @include('coordenador.videos._form', ['mode' => 'create'])

    <div class="flex flex-wrap items-center gap-3 border-t border-[var(--ui-border)] pt-5">
      <button type="submit" class="ui-btn-primary">Salvar video</button>
      <a href="{{ route('coordenador.videos.index') }}" class="ui-btn-secondary">Cancelar</a>
    </div>
  </form>
</div>
@endsection
