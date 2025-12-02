@extends('console.layout')
@section('title','Novo Ponto Turístico')
@section('page.title','Novo Ponto Turístico')

@section('content')
<form method="POST"
      action="{{ route('coordenador.pontos.store') }}"
      enctype="multipart/form-data"
      class="space-y-6">
  @csrf

  @include('coordenador.pontos._form', [
      'ponto'      => $ponto ?? null,
      'categorias' => $categorias,
      'empresas'   => $empresas,
  ])

  <div class="flex items-center gap-3">
    <button class="rounded-lg bg-emerald-600 text-black font-semibold px-4 py-2">Salvar</button>
    <a href="{{ route('coordenador.pontos.index') }}" class="text-slate-300 hover:underline">Cancelar</a>
  </div>
</form>
@endsection
