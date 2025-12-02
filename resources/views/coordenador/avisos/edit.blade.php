@extends('console.layout')
@section('title','Editar Aviso')
@section('page.title','Editar Aviso')

@section('content')
<div class="max-w-3xl mx-auto">
  @include('coordenador.partials.flash')

  <form action="{{ route('coordenador.avisos.update',$aviso) }}" method="post" enctype="multipart/form-data"
        class="space-y-6">
    @csrf @method('PUT')
    @include('coordenador.avisos._form', ['aviso'=>$aviso, 'mode'=>'edit'])
  </form>
</div>
@endsection
