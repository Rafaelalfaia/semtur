@extends('site.layouts.app')
@section('title','Órgãos')
@section('site.content')
  <div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-semibold mb-4">Equipe Semtur</h1>
    <p class="text-slate-600">Em breve…</p>
  </div>


  {{-- Espaço p/ não cobrir conteúdo (mobile) + bottom nav --}}
<div class="h-[80px] pb-[env(safe-area-inset-bottom)] md:hidden"></div>
@includeIf('site.partials._bottom_nav')
@endsection
