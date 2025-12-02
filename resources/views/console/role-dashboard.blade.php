{{-- resources/views/console/role-dashboard.blade.php --}}
@extends('console.layout')

@section('title', $title ?? 'Painel')

@section('conteudo')
@php
  $stats = $stats ?? ['categorias'=>0,'empresas'=>0,'pontos'=>0];
  $cards = [
    [
      'label' => 'Categorias',
      'value' => $stats['categorias'],
      'href'  => route('coordenador.categorias.index'), // admin ainda não tem CRUD próprio; usa o do coordenador por enquanto
      'desc'  => 'Gerencie a taxonomia do conteúdo'
    ],
    [
      'label' => 'Empresas',
      'value' => $stats['empresas'],
      'href'  => route('coordenador.empresas.index'),
      'desc'  => 'Turismo, hotéis, serviços'
    ],
    [
      'label' => 'Pontos Turísticos',
      'value' => $stats['pontos'],
      'href'  => route('coordenador.pontos.index'),
      'desc'  => 'Atrações e experiências'
    ],
  ];
@endphp

<div class="mx-auto w-full max-w-[1200px] px-4 md:px-6 py-6 md:py-10">
  {{-- Cabeçalho --}}
  <div class="mb-6 md:mb-10">
    <h1 class="text-2xl md:text-3xl font-semibold tracking-tight">Painel do Admin</h1>
    <p class="text-slate-400 mt-1">Visão geral do conteúdo e links rápidos.</p>
  </div>

  {{-- KPIs --}}
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
    @foreach ($cards as $c)
      <a href="{{ $c['href'] }}"
         class="group rounded-2xl border border-white/5 bg-white/5 hover:bg-white/10 transition p-5 md:p-6">
        <div class="flex items-baseline justify-between">
          <span class="text-sm uppercase tracking-wider text-slate-400">{{ $c['label'] }}</span>
          <svg class="size-5 opacity-60 group-hover:opacity-100 transition" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M13.172 12L7.05 5.879 8.464 4.464 16 12l-7.536 7.536-1.414-1.415L13.172 12z"/></svg>
        </div>
        <div class="mt-2 md:mt-3 text-3xl md:text-4xl font-bold tabular-nums">{{ $c['value'] }}</div>
        <p class="mt-3 text-slate-400">{{ $c['desc'] }}</p>
      </a>
    @endforeach
  </div>

  {{-- Acesso rápido (Admin) --}}
  <div class="mt-8 md:mt-12">
    <h2 class="text-lg md:text-xl font-semibold mb-3">Atalhos de Administração</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 md:gap-4">
      <a href="{{ route('coordenador.banners.index') }}"
         class="rounded-xl border border-white/5 bg-white/5 hover:bg-white/10 transition p-4">
        <div class="font-medium">Banners</div>
        <div class="text-slate-400 text-sm">Gerenciar carrossel padrão</div>
      </a>
      <a href="{{ route('coordenador.banners_destaque.index') }}"
         class="rounded-xl border border-white/5 bg-white/5 hover:bg-white/10 transition p-4">
        <div class="font-medium">Banners de Destaque</div>
        <div class="text-slate-400 text-sm">Hero principal da Home</div>
      </a>
      <a href="{{ route('coordenador.secretaria.edit') }}"
         class="rounded-xl border border-white/5 bg-white/5 hover:bg-white/10 transition p-4">
        <div class="font-medium">Secretaria</div>
        <div class="text-slate-400 text-sm">Texto institucional e contatos</div>
      </a>
      <a href="{{ route('coordenador.equipe.index') }}"
         class="rounded-xl border border-white/5 bg-white/5 hover:bg-white/10 transition p-4">
        <div class="font-medium">Equipe</div>
        <div class="text-slate-400 text-sm">Membros e cargos</div>
      </a>
      <a href="{{ route('admin.config.perfil.edit') }}"
         class="rounded-xl border border-white/5 bg-white/5 hover:bg-white/10 transition p-4">
        <div class="font-medium">Meu Perfil (Admin)</div>
        <div class="text-slate-400 text-sm">Foto, nome, segurança</div>
      </a>
      <a href="{{ route('dashboard') }}"
         class="rounded-xl border border-white/5 bg-white/5 hover:bg-white/10 transition p-4">
        <div class="font-medium">Trocar de painel</div>
        <div class="text-slate-400 text-sm">Redireciona pelo seu papel</div>
      </a>
    </div>
  </div>

  {{-- Placeholder: últimas mudanças (futuro) --}}
  <div class="mt-10 md:mt-14">
    <h2 class="text-lg md:text-xl font-semibold mb-3">Últimas atualizações</h2>
    <div class="rounded-xl border border-white/5 bg-white/5 p-5 text-slate-400">
      Em breve: lista de itens publicados/alterados recentemente (Categorias, Empresas, Pontos, Banners).
    </div>
  </div>
</div>
@endsection
