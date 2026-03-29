@extends('layouts.app')
@section('title', 'Voce esta offline')
@section('content')
<div class="max-w-lg mx-auto text-center py-16">
  <h1 class="text-2xl font-semibold text-gray-800">Voce esta offline</h1>
  <p class="mt-2 text-gray-600">Alguns recursos podem nao estar disponiveis sem internet.</p>
  <a href="{{ localized_route('site.home') }}" class="inline-block mt-6 px-4 py-2 rounded-md bg-green-600 text-white">Tentar novamente</a>
</div>
@endsection
