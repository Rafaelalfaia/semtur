@extends('site.layouts.app')
@section('title', $empresa->nome . ' — VisitAltamira')

@section('site.content')
@php
  $container = 'mx-auto w-full max-w-[420px] md:max-w-[1024px] lg:max-w-[1200px] px-4 md:px-6';
  $hero = $empresa->capa_url ?? $empresa->perfil_url;
@endphp

<section class="relative h-[36vh] md:h-[44vh] overflow-hidden">
  @if($hero)
    <img src="{{ $hero }}" alt="{{ $empresa->nome }}"
         class="absolute inset-0 w-full h-full object-cover">
  @else
    <div class="absolute inset-0 bg-slate-200"></div>
  @endif
  <div class="absolute inset-0 bg-gradient-to-b from-[#00837B] to-transparent opacity-80"></div>
</section>

<section class="-mt-8 md:-mt-12 relative">
  <div class="{{ $container }}">
    <div class="bg-white rounded-t-[30px] shadow-[0_-10px_30px_rgba(0,0,0,0.08)] p-4 md:p-6">
      <h1 class="text-[22px] md:text-[28px] font-semibold text-[#2B3536]">{{ $empresa->nome }}</h1>
      <div class="mt-2 text-[#868B8B]">{{ $empresa->cidade ?? 'Altamira' }}</div>

      @if($empresa->descricao)
        <div class="mt-4 text-[14px] leading-6 text-[#868B8B]">{!! nl2br(e($empresa->descricao)) !!}</div>
      @endif
    </div>
  </div>
</section>

<div class="h-[80px] pb-[env(safe-area-inset-bottom)] md:hidden"></div>
@include('site.partials._bottom_nav')
@endsection
