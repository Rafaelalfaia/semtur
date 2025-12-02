@extends('console.layout')

@section('title', $title)
@section('page.title', $title)

@section('content')
<div x-data="{ tab: 'resumo' }" class="space-y-6">

  {{-- Abas (Resumo | Histórico) --}}
  <div class="rounded-xl border border-white/5 bg-[#0F1412] p-4">
    <div class="flex gap-6 text-sm">
      <button @click="tab='resumo'"
        :class="tab==='resumo' ? 'text-emerald-300' : 'text-slate-400 hover:text-slate-200'">Resumo</button>
      <button @click="tab='historico'"
        :class="tab==='historico' ? 'text-emerald-300' : 'text-slate-400 hover:text-slate-200'">Histórico</button>
    </div>
  </div>

  {{-- Cards grandes (estilo do print) --}}
  <div class="grid gap-6 lg:grid-cols-2">
    <section class="rounded-xl border border-white/5 bg-[#0F1412] p-5">
      <header class="flex items-center justify-between">
        <div>
          <div class="text-sm text-slate-400">Atalhos rápidos</div>
          <h2 class="text-lg font-semibold">Publicações</h2>
        </div>
        <span class="text-emerald-300 text-xs">Ativo</span>
      </header>

      <div class="mt-4 grid gap-3 sm:grid-cols-2">
        <a href="#" class="rounded-lg bg-white/5 px-4 py-3 text-sm hover:bg-white/10">+ Nova Categoria</a>
        <a href="#" class="rounded-lg bg-white/5 px-4 py-3 text-sm hover:bg-white/10">+ Nova Empresa</a>
        <a href="#" class="rounded-lg bg-white/5 px-4 py-3 text-sm hover:bg-white/10">+ Novo Ponto Turístico</a>
        <a href="#" class="rounded-lg bg-white/5 px-4 py-3 text-sm hover:bg-white/10">Gerenciar Galerias</a>
      </div>

      <div class="mt-6">
        <button class="w-full rounded-lg bg-emerald-500/90 hover:bg-emerald-500 text-black font-semibold py-3">
          Publicar alterações
        </button>
      </div>
    </section>

    <section class="rounded-xl border border-white/5 bg-[#0F1412] p-5">
      <header class="flex items-center justify-between">
        <div>
          <div class="text-sm text-slate-400">Operações</div>
          <h2 class="text-lg font-semibold">Moderação & Destaques</h2>
        </div>
        <span class="text-emerald-300 text-xs">Ativo</span>
      </header>

      <div class="mt-4 grid gap-3 sm:grid-cols-2">
        <a href="#" class="rounded-lg bg-white/5 px-4 py-3 text-sm hover:bg-white/10">Revisar pendências</a>
        <a href="#" class="rounded-lg bg-white/5 px-4 py-3 text-sm hover:bg-white/10">Marcar como destaque</a>
        <a href="#" class="rounded-lg bg-white/5 px-4 py-3 text-sm hover:bg-white/10">Relatórios rápidos</a>
        <a href="#" class="rounded-lg bg-white/5 px-4 py-3 text-sm hover:bg-white/10">Configurar mapa</a>
      </div>

      <div class="mt-6 text-xs text-slate-400">
        Disponível: {{ number_format($stats['categorias']) }} categorias,
        {{ number_format($stats['empresas']) }} empresas,
        {{ number_format($stats['pontos']) }} pontos.
      </div>
    </section>
  </div>

  {{-- Linha de cards pequenos (status) --}}
  <div class="grid gap-4 sm:grid-cols-3">
    @foreach ([
      ['label'=>'Categorias','val'=>$stats['categorias']],
      ['label'=>'Empresas','val'=>$stats['empresas']],
      ['label'=>'Pontos Turísticos','val'=>$stats['pontos']],
    ] as $c)
      <div class="rounded-xl border border-white/5 bg-[#0F1412] p-4">
        <div class="text-sm text-slate-400">{{ $c['label'] }}</div>
        <div class="mt-1 text-2xl font-bold">{{ number_format($c['val']) }}</div>
        <a href="#" class="mt-3 inline-block text-sm text-emerald-300 hover:underline">Ver todos</a>
      </div>
    @endforeach
  </div>

</div>
@endsection
