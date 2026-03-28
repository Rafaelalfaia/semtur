@extends('console.layout')
@section('title','Novo Aviso')
@section('page.title','Novo Aviso')
@section('topbar.description', 'Cadastre um aviso com o mesmo padrão visual, estrutural e de modos do console.')

@section('topbar.nav')
  <a href="{{ route('coordenador.avisos.index') }}" class="ui-console-topbar-tab">Avisos</a>
  <span class="ui-console-topbar-tab is-active">Novo aviso</span>
@endsection

@section('content')
<div class="ui-console-page">
  @include('coordenador.partials.flash')

  <x-dashboard.page-header
    title="Novo aviso"
    subtitle="Monte um aviso com descrição, janela de exibição e imagem opcional sem sair do padrão compartilhado do console."
  />

  <form action="{{ route('coordenador.avisos.store') }}" method="post" enctype="multipart/form-data" class="mt-5 space-y-5">
    @csrf
    @include('coordenador.avisos._form', ['aviso'=>$aviso, 'mode'=>'create'])
  </form>
</div>
@endsection
