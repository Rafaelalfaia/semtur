@extends('console.layout')
@section('title','Novo Aviso')
@section('page.title','Novo Aviso')

@section('content')
<div class="max-w-3xl mx-auto">
  @include('coordenador.partials.flash')
  <form action="{{ route('coordenador.avisos.store') }}" method="post" enctype="multipart/form-data"
        class="space-y-6">
    @csrf
    @include('coordenador.avisos._form', ['aviso'=>$aviso, 'mode'=>'create'])
  </form>
</div>
@endsection
