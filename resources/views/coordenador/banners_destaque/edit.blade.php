@extends('console.layout')

@section('title','Editar Banner Principal')
@section('page.title','Editar Banner Principal')

@push('head')
  {{-- CropperJS CSS já incluso no seu app.css --}}
@endpush

@push('scripts')
  @vite('resources/js/simple-previews.js')
@endpush



@section('content')
  <div class="max-w-4xl mx-auto space-y-6">
    <h1 class="text-2xl font-semibold text-white">Editar Banner Principal</h1>

    <form action="{{ route('coordenador.banners-destaque.update', $banner) }}"
          method="POST"
          enctype="multipart/form-data"
          class="space-y-6 rounded-xl border border-white/10 bg-[#0F1412] p-6">
      @method('PUT')
      @include('coordenador.banners_destaque._form')
    </form>
  </div>
@endsection
