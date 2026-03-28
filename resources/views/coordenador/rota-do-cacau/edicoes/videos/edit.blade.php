@extends('console.layout')

@section('title', 'Editar vídeo - '.$edicao->titulo)
@section('page.title', 'Editar vídeo da edição')
@section('topbar.description', 'Ajuste o link, a descrição e a ordem do vídeo desta edição da Rota do Cacau.')

@section('topbar.nav')
  <a href="{{ route('coordenador.rota-do-cacau.index') }}" class="ui-console-topbar-tab">Rota do Cacau</a>
  <a href="{{ route('coordenador.rota-do-cacau.edicoes.index', $rota) }}" class="ui-console-topbar-tab">Edições</a>
  <a href="{{ route('coordenador.rota-do-cacau.edicoes.videos.index', [$rota, $edicao]) }}" class="ui-console-topbar-tab">Vídeos</a>
  <span class="ui-console-topbar-tab is-active">Editar vídeo</span>
@endsection

@section('content')
<div class="ui-console-page">
  @include('coordenador.partials.flash')

  <x-dashboard.page-header title="Editar vídeo" subtitle="Mantenha o link do Drive consistente e revise o preview resolvido para esta edição.">
    <x-slot:actions>
      <a href="{{ route('coordenador.rota-do-cacau.edicoes.videos.index', [$rota, $edicao]) }}" class="ui-btn-secondary">Voltar</a>
    </x-slot:actions>
  </x-dashboard.page-header>

  <form method="POST" action="{{ route('coordenador.rota-do-cacau.edicoes.videos.update', [$rota, $edicao, $video]) }}" class="mt-5 space-y-6">
    @csrf
    @method('PUT')

    @include('coordenador.rota-do-cacau.edicoes.videos._form')

    <div class="flex flex-wrap items-center gap-3 border-t border-[var(--ui-border)] pt-5">
      <button type="submit" class="ui-btn-primary">Salvar alterações</button>
      <a href="{{ route('coordenador.rota-do-cacau.edicoes.videos.index', [$rota, $edicao]) }}" class="ui-btn-secondary">Cancelar</a>
    </div>
  </form>
</div>
@endsection
