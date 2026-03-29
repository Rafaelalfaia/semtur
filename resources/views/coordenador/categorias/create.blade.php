@extends('console.layout')
@section('title','Nova categoria')
@section('page.title','Nova categoria')
@section('topbar.description', 'Cadastre uma categoria com o mesmo padrão estrutural, visual e de modos do console.')

@section('topbar.nav')
  <a href="{{ route('coordenador.categorias.index') }}" class="ui-console-topbar-tab">Categorias</a>
  <span class="ui-console-topbar-tab is-active">Nova categoria</span>
@endsection

@section('content')
<div class="ui-console-page">
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
    title="Nova categoria"
    subtitle="Crie uma nova categoria com metadados, status e ícone no mesmo padrão visual premium do console."
  />

  <form method="POST" action="{{ route('coordenador.categorias.store') }}" enctype="multipart/form-data" class="mt-5 space-y-5">
    @csrf

    <x-dashboard.section-card title="Dados da categoria" subtitle="Informações essenciais para a catalogação" class="ui-coord-dashboard-panel">
      @include('coordenador.categorias._form', [
        'categoria' => new \App\Models\Catalogo\Categoria(),
        'empresas'  => $empresas ?? collect(),
        'isCreate'  => true,
      ])
    </x-dashboard.section-card>

    <div>
      <button type="submit" class="ui-btn-primary">Salvar</button>
    </div>
  </form>
</div>
@endsection
