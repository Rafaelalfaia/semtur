@extends('console.layout')

@section('title','Editar Banner Principal')
@section('page.title','Editar Banner Principal')
@section('topbar.description', 'Atualize um banner destaque mantendo consistencia com o shell, o modo global e a futura camada de temas.')

@section('topbar.nav')
  <a href="{{ route('coordenador.banners-destaque.index') }}" class="ui-console-topbar-tab">Banners destaque</a>
  <span class="ui-console-topbar-tab is-active">Editar banner</span>
@endsection

@push('head')
  {{-- CropperJS CSS ja incluso no app.css --}}
@endpush

@section('content')
<div class="ui-console-page">
  @if(session('ok'))
    <div class="ui-alert ui-alert-success mb-4">{{ session('ok') }}</div>
  @endif

  @if ($errors->any())
    <div class="ui-alert ui-alert-danger mb-4">
      <div class="font-semibold mb-2">Corrija os campos abaixo:</div>
      <ul class="list-disc pl-5">
        @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
      </ul>
    </div>
  @endif

  <x-dashboard.page-header
    title="Editar banner principal"
    subtitle="Refine os dados da vitrine principal da home sem sair do padrao compartilhado do console."
  />

  <form action="{{ route('coordenador.banners-destaque.update', $banner) }}" method="POST" enctype="multipart/form-data" class="mt-5">
    @method('PUT')
    @include('coordenador.banners_destaque._form')
  </form>
</div>
@endsection
