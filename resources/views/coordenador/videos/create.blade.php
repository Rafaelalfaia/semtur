@extends('console.layout')

@section('title', 'Novo vídeo')
@section('page.title', 'Novo vídeo')
@section('topbar.description', 'Cadastre um vídeo institucional com capa, descrição e acesso externo seguindo o padrão global do console.')

@section('topbar.nav')
  <a href="{{ route('coordenador.videos.index') }}" class="ui-console-topbar-tab">Vídeos</a>
  <span class="ui-console-topbar-tab is-active">Novo vídeo</span>
@endsection

@section('content')
<div class="ui-console-page">
  @include('coordenador.partials.flash')

  <x-dashboard.page-header
    title="Criar vídeo"
    subtitle="Cadastre vídeos oficiais com capa, descrição e link do Google Drive para abrir dentro do site."
  >
    <a href="{{ route('coordenador.videos.index') }}" class="ui-btn-secondary">Voltar</a>
  </x-dashboard.page-header>

  <form method="POST" action="{{ route('coordenador.videos.store') }}" enctype="multipart/form-data" class="mt-5 space-y-6">
    @csrf

    @include('coordenador.videos._form', ['mode' => 'create'])

    <div class="flex flex-wrap items-center gap-3 border-t border-[var(--ui-border)] pt-5">
      <button type="submit" class="ui-btn-primary">Salvar vídeo</button>
      <a href="{{ route('coordenador.videos.index') }}" class="ui-btn-secondary">Cancelar</a>
    </div>
  </form>
</div>
@endsection
