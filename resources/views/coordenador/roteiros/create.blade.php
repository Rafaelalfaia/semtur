@extends('console.layout')

@section('title', 'Novo roteiro')
@section('page.title', 'Novo roteiro')
@section('topbar.description', 'Crie um roteiro com builder editorial, curadoria de pontos e empresas, mantendo o shell global intacto.')

@section('topbar.nav')
  <a href="{{ route('coordenador.roteiros.index') }}" class="ui-console-topbar-tab">Roteiros</a>
  <span class="ui-console-topbar-tab is-active">Novo roteiro</span>
@endsection

@section('content')
<div class="ui-console-page">
  @include('coordenador.partials.flash')

  <x-dashboard.page-header
    title="Criar roteiro"
    subtitle="Monte o roteiro por duracao e perfil, organize as etapas e escolha manualmente os pontos e empresas sugeridas."
  >
    <a href="{{ route('coordenador.roteiros.index') }}" class="ui-btn-secondary">Voltar</a>
  </x-dashboard.page-header>

  <form method="POST" action="{{ route('coordenador.roteiros.store') }}" enctype="multipart/form-data" class="mt-5 space-y-6">
    @csrf

    @include('coordenador.roteiros._form', ['mode' => 'create'])

    <div class="flex flex-wrap items-center gap-3 border-t border-[var(--ui-border)] pt-5">
      <button type="submit" class="ui-btn-primary">Salvar roteiro</button>
      <a href="{{ route('coordenador.roteiros.index') }}" class="ui-btn-secondary">Cancelar</a>
    </div>
  </form>
</div>
@endsection
