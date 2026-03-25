@extends('console.layout')

@section('title', 'Nova edicao - '.$rota->titulo)
@section('page.title', 'Nova edicao')
@section('topbar.description', 'Cadastre o ano, a capa e os dados editoriais da edicao da Rota do Cacau dentro do padrao do console.')

@section('topbar.nav')
  <a href="{{ route('coordenador.rota-do-cacau.index') }}" class="ui-console-topbar-tab">Rota do Cacau</a>
  <a href="{{ route('coordenador.rota-do-cacau.edicoes.index', $rota) }}" class="ui-console-topbar-tab">Edicoes</a>
  <span class="ui-console-topbar-tab is-active">Nova edicao</span>
@endsection

@section('content')
<div class="ui-console-page">
  @include('coordenador.partials.flash')

  <x-dashboard.page-header
    title="Criar edicao"
    subtitle="Defina o ano, a capa e a base editorial para depois alimentar fotos, videos e patrocinadores."
  >
    <x-slot:actions>
      <a href="{{ route('coordenador.rota-do-cacau.edicoes.index', $rota) }}" class="ui-btn-secondary">Voltar</a>
    </x-slot:actions>
  </x-dashboard.page-header>

  <form method="POST" action="{{ route('coordenador.rota-do-cacau.edicoes.store', $rota) }}" enctype="multipart/form-data" class="mt-5 space-y-6">
    @csrf

    @include('coordenador.rota-do-cacau.edicoes._form', ['mode' => 'create'])

    <div class="flex flex-wrap items-center gap-3 border-t border-[var(--ui-border)] pt-5">
      <button type="submit" class="ui-btn-primary">Salvar edicao</button>
      <a href="{{ route('coordenador.rota-do-cacau.edicoes.index', $rota) }}" class="ui-btn-secondary">Cancelar</a>
    </div>
  </form>
</div>
@endsection
