@extends('console.layout')

@section('title', 'Editar edicao - '.$rota->titulo)
@section('page.title', 'Editar edicao')
@section('topbar.description', 'Atualize ano, capa e publicacao da edicao e acesse os submodulos de conteudo do mesmo registro.')

@section('topbar.nav')
  <a href="{{ route('coordenador.rota-do-cacau.index') }}" class="ui-console-topbar-tab">Rota do Cacau</a>
  <a href="{{ route('coordenador.rota-do-cacau.edicoes.index', $rota) }}" class="ui-console-topbar-tab">Edicoes</a>
  <span class="ui-console-topbar-tab is-active">Editar edicao</span>
@endsection

@section('content')
<div class="ui-console-page">
  @include('coordenador.partials.flash')

  <x-dashboard.page-header
    title="Editar edicao"
    subtitle="Mantenha os dados editoriais atualizados e gerencie os conteudos complementares a partir desta edicao."
  >
    <x-slot:actions>
      <div class="flex flex-wrap gap-2">
        @can('rota_do_cacau.edicoes.fotos.view')
          <a href="{{ route('coordenador.rota-do-cacau.edicoes.fotos.index', [$rota, $edicao]) }}" class="ui-btn-secondary">Fotos</a>
        @endcan
        @can('rota_do_cacau.edicoes.videos.view')
          <a href="{{ route('coordenador.rota-do-cacau.edicoes.videos.index', [$rota, $edicao]) }}" class="ui-btn-secondary">Videos</a>
        @endcan
        @can('rota_do_cacau.edicoes.patrocinadores.view')
          <a href="{{ route('coordenador.rota-do-cacau.edicoes.patrocinadores.index', [$rota, $edicao]) }}" class="ui-btn-secondary">Patrocinadores</a>
        @endcan
        <a href="{{ route('coordenador.rota-do-cacau.edicoes.index', $rota) }}" class="ui-btn-secondary">Voltar</a>
      </div>
    </x-slot:actions>
  </x-dashboard.page-header>

  <form method="POST" action="{{ route('coordenador.rota-do-cacau.edicoes.update', [$rota, $edicao]) }}" enctype="multipart/form-data" class="mt-5 space-y-6">
    @csrf
    @method('PUT')

    @include('coordenador.rota-do-cacau.edicoes._form', ['mode' => 'edit'])

    <div class="flex flex-wrap items-center gap-3 border-t border-[var(--ui-border)] pt-5">
      <button type="submit" class="ui-btn-primary">Salvar alteracoes</button>
      <a href="{{ route('coordenador.rota-do-cacau.edicoes.index', $rota) }}" class="ui-btn-secondary">Cancelar</a>
    </div>
  </form>
</div>
@endsection
