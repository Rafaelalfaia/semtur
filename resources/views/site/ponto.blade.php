@extends('site.layouts.app')
@section('title', $ponto->nome . ' — SEMTUR')

@section('site.content')
@php
  $container = 'mx-auto w-full max-w-[420px] md:max-w-[1024px] lg:max-w-[1200px] px-4 md:px-6';
  $cidade    = $ponto->cidade ?? 'Altamira';
@endphp

{{-- HERO com imagem e degradê igual da home --}}
<section class="relative h-[46vh] md:h-[54vh] overflow-hidden">
  @if($hero)
    <img src="{{ $hero }}" alt="{{ $ponto->nome }}"
         class="absolute inset-0 w-full h-full object-cover" loading="lazy" decoding="async">
  @else
    <div class="absolute inset-0 bg-slate-200"></div>
  @endif

  {{-- degradê 00837B -> transparente (como na home) --}}
  <div class="absolute inset-0 bg-gradient-to-b from-[#00837B] via-[#00837B]/60 to-transparent"></div>

  {{-- botão voltar --}}
  <a href="{{ url()->previous() ?: route('site.home') }}"
     class="absolute top-4 left-4 z-20">
    <span class="w-12 h-12 grid place-items-center rounded-full
                 bg-white/20 backdrop-blur border border-white/30 text-white shadow-lg
                 hover:bg-white/30 transition">
      {{-- ícone seta --}}
      <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M15 18l-6-6 6-6"/>
      </svg>
    </span>
  </a>
</section>

{{-- FOLHA com conteúdo --}}
<section class="-mt-8 md:-mt-12 relative">
  <div class="{{ $container }}">
    <div class="bg-white rounded-t-[30px] shadow-[0_-10px_30px_rgba(0,0,0,0.08)] overflow-hidden">

      {{-- handle --}}
      <div class="pt-3 flex justify-center">
        <div class="h-1.5 w-32 rounded-full bg-[#C3C5C8]"></div>
      </div>

      {{-- Título + Localização (clicável) + rating fake (até ter avaliação real) --}}
      <div class="p-4 md:p-6">
        <h1 class="text-[22px] md:text-[28px] font-semibold text-[#2B3536]">{{ $ponto->nome }}</h1>

        <div class="mt-2 flex items-center justify-between text-sm">
          <a href="{{ $mapUrl }}" target="_blank" rel="noopener"
             class="inline-flex items-center gap-2 text-[#868B8B] hover:text-[#00837B] underline underline-offset-2">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7Zm0 9.5A2.5 2.5 0 1 1 12 6a2.5 2.5 0 0 1 0 5Z"/></svg>
            <span>{{ $cidade }}</span>
          </a>

          <div class="flex items-center gap-1 text-[#868B8B]">
            @for($i=0;$i<5;$i++)
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="#FCCF05"><path d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
            @endfor
            <span class="ml-1">5.0</span>
          </div>
        </div>
      </div>

      {{-- Abas simples (ancoras) --}}
      <div class="px-4 md:px-6">
        <nav class="flex gap-10">
          <a href="#descricao" class="flex flex-col items-center">
            <span class="text-[#00837B] font-semibold">Descrição</span>
            <span class="w-full border-b-2 border-[#00837B] mt-1"></span>
          </a>
          <a href="#redes" class="text-[#2B3536]/80 hover:text-[#2B3536]">Redes sociais</a>
        </nav>
      </div>

      {{-- DESCRIÇÃO --}}
      <div id="descricao" class="p-4 md:p-6">
        <h2 class="text-[16px] font-semibold text-[#2B3536]">Sobre</h2>
        <p class="mt-2 text-[14px] leading-6 text-[#868B8B] text-justify">
          {!! nl2br(e($ponto->descricao ?? '')) !!}
        </p>
      </div>

      {{-- EMPRESAS ASSOCIADAS --}}
      <div class="px-4 md:px-6 pb-4">
        <div class="flex items-center justify-between mb-3">
          <h3 class="text-[16px] font-semibold text-[#2B3536]">Empresas com pacotes</h3>
          <a href="{{ route('site.explorar', ['categoria' => 'parceiros']) }}"
             class="text-[14px] text-[#00837B]">Ver todos</a>
        </div>
        <div class="space-y-3">
          @forelse($empresas as $e)
            <x-card-list
              :title="$e->nome"
              :subtitle="$e->cidade ?? 'Altamira'"
              :image="$e->perfil_url ?? $e->capa_url"
              logo="/imagens/visitpreto.png"
              :href="route('site.explorar', ['empresa' => $e->slug])" />
          @empty
            <div class="text-sm text-slate-500">Sem empresas associadas para este ponto.</div>
          @endforelse
        </div>
      </div>

      {{-- REDES SOCIAIS --}}
      @if($redes->isNotEmpty())
      <div id="redes" class="px-4 md:px-6 pb-6">
        <h3 class="text-[16px] font-semibold text-[#2B3536] mb-3">Redes sociais</h3>
        <div class="flex flex-wrap items-center gap-3">
          @if($redes->get('instagram'))
            <a href="{{ Str::startsWith($redes['instagram'], ['http','https']) ? $redes['instagram'] : 'https://instagram.com/'.ltrim($redes['instagram'],'@') }}"
               target="_blank" rel="noopener"
               class="inline-flex items-center gap-2 px-3 py-2 rounded-full bg-[#F5F7F7] hover:bg-[#EAF4F2]">
              <img src="/imagens/ico-instagram.svg" class="w-5 h-5" alt="Instagram">
              <span class="text-sm">@{{ ltrim($redes['instagram'],'@') }}</span>
            </a>
          @endif

          @if($redes->get('facebook'))
            <a href="{{ Str::startsWith($redes['facebook'], ['http','https']) ? $redes['facebook'] : 'https://facebook.com/'.ltrim($redes['facebook'],'@') }}"
               target="_blank" rel="noopener"
               class="inline-flex items-center gap-2 px-3 py-2 rounded-full bg-[#F5F7F7] hover:bg-[#EAF4F2]">
              <img src="/imagens/ico-facebook.svg" class="w-5 h-5" alt="Facebook">
              <span class="text-sm">Facebook</span>
            </a>
          @endif

          @if($redes->get('site'))
            <a href="{{ Str::startsWith($redes['site'], ['http','https']) ? $redes['site'] : 'http://'.$redes['site'] }}"
               target="_blank" rel="noopener"
               class="inline-flex items-center gap-2 px-3 py-2 rounded-full bg-[#F5F7F7] hover:bg-[#EAF4F2]">
              <img src="/imagens/ico-link.svg" class="w-5 h-5" alt="Site">
              <span class="text-sm">Site oficial</span>
            </a>
          @endif

          @if($redes->get('whatsapp'))
            <a href="{{ 'https://wa.me/'.preg_replace('/\D/','',$redes['whatsapp']) }}"
               target="_blank" rel="noopener"
               class="inline-flex items-center gap-2 px-3 py-2 rounded-full bg-[#F5F7F7] hover:bg-[#EAF4F2]">
              <img src="/imagens/ico-whatsapp.svg" class="w-5 h-5" alt="WhatsApp">
              <span class="text-sm">WhatsApp</span>
            </a>
          @endif
        </div>
      </div>
      @endif
    </div>
  </div>
</section>

{{-- espaço pro bottom nav (mobile) --}}
<div class="h-[80px] pb-[env(safe-area-inset-bottom)] md:hidden"></div>
@include('site.partials._bottom_nav')
@endsection
