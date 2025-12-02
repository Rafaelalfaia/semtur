@extends('layouts.app')
@section('title','Você está offline')
@section('content')
<div class="max-w-lg mx-auto text-center py-16">
  <h1 class="text-2xl font-semibold text-gray-800">Você está offline</h1>
  <p class="mt-2 text-gray-600">Alguns recursos podem não estar disponíveis sem internet.</p>
  <a href="{{ url('/') }}" class="inline-block mt-6 px-4 py-2 rounded-md bg-green-600 text-white">Tentar novamente</a>
</div>
@endsection
