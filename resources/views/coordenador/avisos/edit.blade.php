@extends('console.layout')
@section('title','Editar Aviso')
@section('page.title','Editar Aviso')
@section('topbar.description', 'Atualize um aviso preservando o shell, o light/dark global e a futura base de temas.')

@section('topbar.nav')
  <a href="{{ route('coordenador.avisos.index') }}" class="ui-console-topbar-tab">Avisos</a>
  <span class="ui-console-topbar-tab is-active">Editar aviso</span>
@endsection

@section('content')
<div class="ui-console-page">
  @include('coordenador.partials.flash')

  <x-dashboard.page-header
    title="Editar aviso"
    subtitle="Revise conteudo, periodo de exibicao e imagem mantendo consistencia com os demais modulos do console."
  />

  <form action="{{ route('coordenador.avisos.update',$aviso) }}" method="post" enctype="multipart/form-data" class="mt-5 space-y-5">
    @csrf
    @method('PUT')
    @include('coordenador.avisos._form', ['aviso'=>$aviso, 'mode'=>'edit'])
  </form>

  @if($aviso->exists && !empty($aviso->imagem_path))
    <form id="aviso-remove-imagem-form" action="{{ route('coordenador.avisos.imagem.remover',$aviso) }}" method="post" class="hidden">
      @csrf
      @method('DELETE')
    </form>
  @endif
</div>
@endsection
