@extends('console.layout')

@section('title','Novo Banner Principal')
@section('page.title','Novo Banner Principal')
@section('topbar.description', 'Cadastre um banner destaque com metadados, imagens e janela de publicacao no padrao visual do console.')

@section('topbar.nav')
  <a href="{{ route('coordenador.banners-destaque.index') }}" class="ui-console-topbar-tab">Banners destaque</a>
  <span class="ui-console-topbar-tab is-active">Novo banner</span>
@endsection

@push('head')
  {{-- CropperJS CSS ja incluso no app.css --}}
@endpush

@section('content')
<div class="ui-console-page">
  @if ($errors->any())
    <div class="ui-alert ui-alert-danger mb-4">
      <div class="font-semibold mb-2">Corrija os campos abaixo:</div>
      <ul class="list-disc pl-5">
        @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
      </ul>
    </div>
  @endif

  <x-dashboard.page-header
    title="Novo banner principal"
    subtitle="Monte uma nova peça de destaque para a home com textos, status, janela de publicacao e imagens responsivas."
  />

  <form action="{{ route('coordenador.banners-destaque.store') }}" method="POST" enctype="multipart/form-data" class="mt-5">
    @include('coordenador.banners_destaque._form')
  </form>
</div>
@endsection
