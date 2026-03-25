@extends('console.layout')
@section('title','Editar categoria')
@section('page.title','Editar categoria')
@section('topbar.description', 'Atualize uma categoria mantendo consistencia com o shell, o light/dark global e a base de temas futuros.')

@section('topbar.nav')
  <a href="{{ route('coordenador.categorias.index') }}" class="ui-console-topbar-tab">Categorias</a>
  <span class="ui-console-topbar-tab is-active">Editar categoria</span>
@endsection

@section('content')
<div class="ui-console-page">
  @if (session('ok'))
    <div class="ui-alert ui-alert-success mb-4">{{ session('ok') }}</div>
  @endif

  @if ($errors->any())
    <div class="ui-alert ui-alert-danger mb-4">
      <strong>Corrija os campos:</strong>
      <ul class="list-disc list-inside mt-2">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <x-dashboard.page-header
    title="Editar categoria"
    subtitle="Refine informacoes, status e icone mantendo o modulo alinhado ao novo console."
  />

  <form method="POST" action="{{ route('coordenador.categorias.update', $categoria) }}" enctype="multipart/form-data" class="mt-5 space-y-5">
    @csrf
    @method('PUT')

    <x-dashboard.section-card title="Dados da categoria" subtitle="Edite nome, slug, status, descricao e icone" class="ui-coord-dashboard-panel">
      @include('coordenador.categorias._form', [
        'categoria' => $categoria,
        'empresas'  => $empresas ?? collect(),
        'isCreate'  => false,
      ])
    </x-dashboard.section-card>

    <div>
      <button type="submit" class="ui-btn-primary">Salvar alteracoes</button>
    </div>
  </form>
</div>
@endsection
