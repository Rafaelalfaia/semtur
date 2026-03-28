@extends('console.layout')

@section('title', 'Editar edição - '.$jogo->titulo)
@section('page.title', 'Editar edição')
@section('topbar.description', 'Atualize ano, capa e publicação da edição e acesse os submódulos de conteúdo do mesmo registro.')

@section('topbar.nav')
  <a href="{{ route('coordenador.jogos-indigenas.index') }}" class="ui-console-topbar-tab">Jogos Indígenas</a>
  <a href="{{ route('coordenador.jogos-indigenas.edicoes.index', $jogo) }}" class="ui-console-topbar-tab">Edições</a>
  <span class="ui-console-topbar-tab is-active">Editar edição</span>
@endsection

@section('content')
<div class="ui-console-page">
  @include('coordenador.partials.flash')

  <x-dashboard.page-header
    title="Editar edição"
    subtitle="Mantenha os dados editoriais atualizados e gerencie os conteúdos complementares a partir desta edição."
  >
    <x-slot:actions>
      <div class="flex flex-wrap gap-2">
        <a href="{{ route('coordenador.jogos-indigenas.edicoes.fotos.index', [$jogo, $edicao]) }}" class="ui-btn-secondary">Fotos</a>
        <a href="{{ route('coordenador.jogos-indigenas.edicoes.videos.index', [$jogo, $edicao]) }}" class="ui-btn-secondary">Vídeos</a>
        <a href="{{ route('coordenador.jogos-indigenas.edicoes.patrocinadores.index', [$jogo, $edicao]) }}" class="ui-btn-secondary">Patrocinadores</a>
        <a href="{{ route('coordenador.jogos-indigenas.edicoes.index', $jogo) }}" class="ui-btn-secondary">Voltar</a>
      </div>
    </x-slot:actions>
  </x-dashboard.page-header>

  <form method="POST" action="{{ route('coordenador.jogos-indigenas.edicoes.update', [$jogo, $edicao]) }}" enctype="multipart/form-data" class="mt-5 space-y-6">
    @csrf
    @method('PUT')

    @include('coordenador.jogos-indigenas.edicoes._form', ['mode' => 'edit'])

    <div class="flex flex-wrap items-center gap-3 border-t border-[var(--ui-border)] pt-5">
      <button type="submit" class="ui-btn-primary">Salvar alterações</button>
      <a href="{{ route('coordenador.jogos-indigenas.edicoes.index', $jogo) }}" class="ui-btn-secondary">Cancelar</a>
    </div>
  </form>
</div>
@endsection
