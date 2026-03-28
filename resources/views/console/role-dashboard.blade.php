@extends('console.layout')

@section('title', $title ?? 'Painel')
@section('topbar.description', 'Visão compacta do papel atual, com atalhos e indicadores essenciais no mesmo shell do console.')

@section('topbar.nav')
  <span class="ui-console-topbar-tab is-active">Visão geral</span>
@endsection

@section('content')
@php
  $stats = $stats ?? ['categorias' => 0, 'empresas' => 0, 'pontos' => 0];
  $cards = [
    [
      'label' => 'Categorias',
      'value' => $stats['categorias'],
      'href'  => route('coordenador.categorias.index'),
      'desc'  => 'Taxonomia de conteúdo'
    ],
    [
      'label' => 'Empresas',
      'value' => $stats['empresas'],
      'href'  => route('coordenador.empresas.index'),
      'desc'  => 'Turismo, serviços e hospedagem'
    ],
    [
      'label' => 'Pontos turísticos',
      'value' => $stats['pontos'],
      'href'  => route('coordenador.pontos.index'),
      'desc'  => 'Atrações e experiências'
    ],
  ];
@endphp

<div class="ui-console-page">
  <x-dashboard.page-header
    :title="$title ?? 'Painel'"
    subtitle="Acesso rápido ao conteúdo operacional com leitura clara e o mesmo padrão visual do console."
  />

  <div class="ui-admin-dashboard-kpi-grid mt-5">
    @foreach ($cards as $card)
      <a href="{{ $card['href'] }}" class="ui-kpi-card">
        <div class="flex items-start justify-between gap-3">
          <span class="ui-kpi-label">{{ $card['label'] }}</span>
          <span class="ui-admin-dashboard-shortcut-arrow" aria-hidden="true">→</span>
        </div>
        <div class="ui-kpi-value">{{ number_format((int) $card['value']) }}</div>
        <p class="ui-kpi-helper">{{ $card['desc'] }}</p>
      </a>
    @endforeach
  </div>

  <div class="mt-6">
    <x-dashboard.section-card title="Escopo atual" subtitle="Atue nos módulos liberados para o seu papel">
      <div class="text-sm leading-6 text-[var(--ui-text-soft)]">
        O shell, o modo global e a futura camada de temas são compartilhados com Admin e Coordenador.
      </div>
    </x-dashboard.section-card>
  </div>
</div>
@endsection
