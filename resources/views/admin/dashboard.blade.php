@extends('console.admin-layout')
@section('title','Dashboard')
@section('page.title','Dashboard')

@section('content')
  <div class="mx-auto w-full max-w-[1200px] px-4 md:px-6 py-6 md:py-10">
    <div class="mb-6 md:mb-8">
      <h1 class="text-2xl md:text-3xl font-semibold tracking-tight">Dashboard (simples)</h1>
      <p class="text-slate-400 mt-1">Visão inicial do painel do Admin.</p>
    </div>

    {{-- KPIs estáticos (pode trocar por números reais depois) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
      @foreach ([
        ['label'=>'Categorias','value'=>0],
        ['label'=>'Empresas','value'=>0],
        ['label'=>'Pontos Turísticos','value'=>0],
      ] as $c)
        <div class="rounded-2xl border border-white/5 bg-white/5 p-5 md:p-6">
          <div class="text-sm uppercase tracking-wider text-slate-400">{{ $c['label'] }}</div>
          <div class="mt-2 md:mt-3 text-3xl md:text-4xl font-bold tabular-nums">{{ $c['value'] }}</div>
        </div>
      @endforeach
    </div>

    {{-- Área livre pra conteúdo futuro --}}
    <div class="mt-10 md:mt-14 rounded-xl border border-white/5 bg-white/5 p-5 text-slate-300">
      Pronto! O dashboard simples está rodando. Depois plugamos métricas reais e atalhos.
    </div>
  </div>
@endsection
