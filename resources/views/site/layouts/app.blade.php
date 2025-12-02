@extends('layouts.app')

@push('head')
  {{-- CSS/JS do Vite para o site --}}
  @vite(['resources/css/app.css','resources/js/app.js'])

  <style>
    /* Força fundo claro no site (sem interferir no console escuro) */
    html, body { background:#fff !important; }
  </style>
@endpush

@section('content')
  {{-- Link de pular para conteúdo (acessibilidade / teclado) --}}
  <a href="#conteudo"
     class="sr-only focus:not-sr-only focus:fixed focus:left-3 focus:top-3 focus:z-50
            rounded-md bg-emerald-600 px-3 py-2 text-white">
    Pular para o conteúdo
  </a>

  {{-- Top Navigation do site --}}
  @include('site.partials._top_nav')

  {{-- Conteúdo principal das páginas públicas --}}
  <main id="conteudo"
        class="flex-1 min-h-[100svh] min-h-[100dvh] bg-white text-slate-900">
    @yield('site.content')
  </main>

  {{-- SEO dinâmico a partir das yields da página --}}
  <x-seo
    :title="trim($__env->yieldContent('title',''))"
    :description="trim($__env->yieldContent('meta.description','Descubra Altamira'))"
    :image="trim($__env->yieldContent('meta.image','/images/og-default.jpg'))"
  />

  {{-- Popup de aviso (se existir) --}}
  <x-aviso-popup :aviso="$aviso ?? null" />

  {{-- Scripts específicos por página (ex.: fallbacks/carrosséis) --}}
  @stack('scripts')
@endsection
