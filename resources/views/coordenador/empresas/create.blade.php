@extends('console.layout')

@section('title','Nova empresa')
@section('page.title','Nova empresa')

@section('content')
<form method="POST" action="{{ route('coordenador.empresas.store') }}" enctype="multipart/form-data" class="space-y-6">
  @csrf
  @include('coordenador.empresas._form', ['empresa'=>$empresa, 'categorias'=>$categorias, 'selecionadas'=>$selecionadas])
  <div class="flex justify-end gap-2">
    <a href="{{ route('coordenador.empresas.index') }}" class="px-4 py-2 rounded-lg bg-white/5 text-slate-200 hover:bg-white/10">Cancelar</a>
    <button class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white">Salvar</button>
  </div>
</form>
@endsection
