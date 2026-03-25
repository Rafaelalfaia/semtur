@extends('console.layout')

@section('title', 'Dashboard - Coordenador')
@section('page.title', 'Dashboard')
@section('topbar.description', 'Visao executiva do conteudo coordenado, com saude editorial, atividade recente e acoes institucionais.')

@section('topbar.nav')
  <span class="ui-console-topbar-tab is-active">Visao geral</span>
  <a href="#coord-graficos" class="ui-console-topbar-tab">Graficos</a>
  <a href="#coord-recentes" class="ui-console-topbar-tab">Recentes</a>
@endsection

@section('content')
@php
  $totais = $cards['totais'] ?? [];
  $pubs = $cards['publicados'] ?? [];
  $hoje = $cards['hoje'] ?? [];
  $mapEmp = (int) ($health['mapa']['empresas']['percent'] ?? 0);
  $mapPon = (int) ($health['mapa']['pontos']['percent'] ?? 0);
  $capaPon = (int) ($health['capas']['pontos']['percent'] ?? 0);
  $media = number_format((float)($health['midia']['media_midias_por_ponto'] ?? 0), 1, ',', '.');
@endphp

<div class="ui-console-page ui-coord-dashboard">
  <x-dashboard.page-header
    title="Visao geral do coordenador"
    subtitle="Acompanhe volume, publicacao e saude do catalogo sem sair do shell principal do console."
  >
    <x-slot:actions>
      <a href="{{ request()->fullUrlWithQuery(['refresh'=>1]) }}" class="ui-btn-secondary">
        Forcar refresh
      </a>

      <form method="POST" action="{{ route('console.cache.clear') }}" x-data @submit.prevent="if (confirm('Limpar caches do sistema agora?')) $el.submit()">
        @csrf
        <button type="submit" class="ui-btn-danger">
          Limpar cache
        </button>
      </form>
    </x-slot:actions>
  </x-dashboard.page-header>

  <div class="mt-5 flex items-center gap-2">
    <span class="ui-badge ui-badge-success">Atualizado agora</span>
    <span class="ui-badge ui-badge-neutral">Coordenacao ativa</span>
  </div>

  <div class="ui-coord-dashboard-grid mt-5">
    <div class="ui-coord-dashboard-main">
      <div class="ui-admin-dashboard-kpi-grid ui-admin-dashboard-kpi-grid--executive">
        <div class="ui-kpi-card">
          <div class="ui-kpi-label">Categorias</div>
          <div class="ui-kpi-value">{{ number_format((int) ($totais['categorias'] ?? 0)) }}</div>
          <div class="ui-kpi-helper">Total no sistema</div>
        </div>
        <div class="ui-kpi-card">
          <div class="ui-kpi-label">Empresas</div>
          <div class="ui-kpi-value">{{ number_format((int) ($totais['empresas'] ?? 0)) }}</div>
          <div class="ui-kpi-helper">Total no sistema</div>
        </div>
        <div class="ui-kpi-card">
          <div class="ui-kpi-label">Pontos</div>
          <div class="ui-kpi-value">{{ number_format((int) ($totais['pontos'] ?? 0)) }}</div>
          <div class="ui-kpi-helper">Total no sistema</div>
        </div>
        <div class="ui-kpi-card">
          <div class="ui-kpi-label">Banners</div>
          <div class="ui-kpi-value">{{ number_format((int) ($totais['banners'] ?? 0)) }}</div>
          <div class="ui-kpi-helper">Total no sistema</div>
        </div>
      </div>

      <div class="ui-coord-dashboard-health mt-4">
        <x-dashboard.section-card title="Publicados" subtitle="Volume editorial atual" class="ui-coord-dashboard-panel">
          <div class="grid grid-cols-3 gap-3 text-center">
            <div class="ui-coord-mini-stat">
              <div class="ui-coord-mini-value">{{ number_format((int)($pubs['categorias'] ?? 0)) }}</div>
              <div class="ui-coord-mini-label">Categorias</div>
            </div>
            <div class="ui-coord-mini-stat">
              <div class="ui-coord-mini-value">{{ number_format((int)($pubs['empresas'] ?? 0)) }}</div>
              <div class="ui-coord-mini-label">Empresas</div>
            </div>
            <div class="ui-coord-mini-stat">
              <div class="ui-coord-mini-value">{{ number_format((int)($pubs['pontos'] ?? 0)) }}</div>
              <div class="ui-coord-mini-label">Pontos</div>
            </div>
          </div>
        </x-dashboard.section-card>

        <x-dashboard.section-card title="Hoje" subtitle="Movimento recente" class="ui-coord-dashboard-panel">
          <div class="grid grid-cols-2 gap-3 text-center">
            <div class="ui-coord-mini-stat">
              <div class="ui-coord-mini-value">{{ number_format((int)($hoje['novos'] ?? 0)) }}</div>
              <div class="ui-coord-mini-label">Novos cadastros</div>
            </div>
            <div class="ui-coord-mini-stat">
              <div class="ui-coord-mini-value">{{ number_format((int)($hoje['publicados'] ?? 0)) }}</div>
              <div class="ui-coord-mini-label">Publicacoes</div>
            </div>
          </div>
        </x-dashboard.section-card>

        <x-dashboard.section-card title="KPIs rapidos" subtitle="Sinais de destaque" class="ui-coord-dashboard-panel">
          <div class="space-y-3">
            <div class="flex items-center justify-between text-sm">
              <span class="text-[var(--ui-text-soft)]">Empresas recomendadas</span>
              <span class="font-semibold text-[var(--ui-text-title)]">{{ number_format((int)($kpis['recomendados']['empresas'] ?? 0)) }}</span>
            </div>
            <div class="flex items-center justify-between text-sm">
              <span class="text-[var(--ui-text-soft)]">Pontos recomendados</span>
              <span class="font-semibold text-[var(--ui-text-title)]">{{ number_format((int)($kpis['recomendados']['pontos'] ?? 0)) }}</span>
            </div>
          </div>
        </x-dashboard.section-card>
      </div>
    </div>

    <div class="ui-coord-dashboard-side">
      <x-dashboard.section-card title="Saude do catalogo" subtitle="Cobertura e consistencia" class="ui-coord-dashboard-panel">
        <div class="space-y-4">
          <div>
            <div class="mb-1 flex justify-between text-xs text-[var(--ui-text-soft)]"><span>Empresas com lat/lng</span><span>{{ $mapEmp }}%</span></div>
            <div class="ui-coord-progress-track"><div class="ui-coord-progress-fill" style="width: {{ $mapEmp }}%"></div></div>
          </div>
          <div>
            <div class="mb-1 flex justify-between text-xs text-[var(--ui-text-soft)]"><span>Pontos com lat/lng</span><span>{{ $mapPon }}%</span></div>
            <div class="ui-coord-progress-track"><div class="ui-coord-progress-fill" style="width: {{ $mapPon }}%"></div></div>
          </div>
          <div>
            <div class="mb-1 flex justify-between text-xs text-[var(--ui-text-soft)]"><span>Pontos com capa</span><span>{{ $capaPon }}%</span></div>
            <div class="ui-coord-progress-track"><div class="ui-coord-progress-fill" style="width: {{ $capaPon }}%"></div></div>
          </div>
          <div class="text-xs text-[var(--ui-text-soft)]">
            Media de midias por ponto: <span class="font-semibold text-[var(--ui-text-title)]">{{ $media }}</span>
          </div>
        </div>
      </x-dashboard.section-card>

      <x-dashboard.section-card title="Recomendados ativos" subtitle="Janela atual" class="ui-coord-dashboard-panel">
        <div class="space-y-4">
          <div>
            <div class="text-xs uppercase tracking-[0.12em] text-[var(--ui-text-subtle)]">Empresas</div>
            <div class="mt-2 flex flex-wrap gap-2">
              @forelse(($tables['recomendadosAtivos']['empresas'] ?? []) as $rec)
                <span class="ui-badge ui-badge-warning">{{ $rec->empresa->nome ?? '-' }}</span>
              @empty
                <span class="text-xs text-[var(--ui-text-soft)]">Nenhum ativo</span>
              @endforelse
            </div>
          </div>
          <div>
            <div class="text-xs uppercase tracking-[0.12em] text-[var(--ui-text-subtle)]">Pontos</div>
            <div class="mt-2 flex flex-wrap gap-2">
              @forelse(($tables['recomendadosAtivos']['pontos'] ?? []) as $rec)
                <span class="ui-badge ui-badge-primary">{{ $rec->ponto->nome ?? '-' }}</span>
              @empty
                <span class="text-xs text-[var(--ui-text-soft)]">Nenhum ativo</span>
              @endforelse
            </div>
          </div>
        </div>
      </x-dashboard.section-card>
    </div>
  </div>

  <div id="coord-graficos" class="ui-coord-dashboard-charts mt-6">
    <x-dashboard.section-card title="Distribuicao por status" subtitle="Comparativo visual" class="ui-coord-dashboard-panel">
      <div class="ui-coord-chart-wrap"><canvas id="chartStatusDistribuicao" height="220"></canvas></div>
    </x-dashboard.section-card>
    <x-dashboard.section-card title="Top categorias" subtitle="Itens publicados" class="ui-coord-dashboard-panel">
      <div class="ui-coord-chart-wrap"><canvas id="chartTopCategorias" height="220"></canvas></div>
    </x-dashboard.section-card>
    <x-dashboard.section-card title="Timeline de publicacoes" subtitle="Ultimos 30 dias" class="ui-coord-dashboard-panel">
      <div class="ui-coord-chart-wrap"><canvas id="chartTimeline" height="220"></canvas></div>
    </x-dashboard.section-card>
  </div>

  <div id="coord-recentes" class="ui-coord-dashboard-lists mt-6">
    <x-dashboard.section-card title="Categorias recentes" subtitle="Ultimos registros" class="ui-coord-dashboard-panel">
      <ul class="divide-y divide-[var(--ui-border)]">
        @forelse(($tables['recentes']['categorias'] ?? []) as $c)
          <li class="py-3 first:pt-0 last:pb-0">
            <div class="flex items-center justify-between gap-3">
              <div class="min-w-0">
                <div class="font-medium text-[var(--ui-text-title)] truncate">{{ $c->nome }}</div>
                <div class="text-xs text-[var(--ui-text-soft)]">{{ $c->created_at?->format('d/m/Y H:i') }}</div>
              </div>
              <span class="ui-badge ui-badge-neutral">{{ $c->status }}</span>
            </div>
          </li>
        @empty
          <li class="py-6 text-center text-sm text-[var(--ui-text-soft)]">Sem registros</li>
        @endforelse
      </ul>
    </x-dashboard.section-card>

    <x-dashboard.section-card title="Empresas recentes" subtitle="Ultimos registros" class="ui-coord-dashboard-panel">
      <ul class="divide-y divide-[var(--ui-border)]">
        @forelse(($tables['recentes']['empresas'] ?? []) as $e)
          <li class="py-3 first:pt-0 last:pb-0">
            <div class="flex items-center justify-between gap-3">
              <div class="min-w-0">
                <div class="font-medium text-[var(--ui-text-title)] truncate">{{ $e->nome }}</div>
                <div class="text-xs text-[var(--ui-text-soft)]">{{ $e->created_at?->format('d/m/Y H:i') }}</div>
              </div>
              <span class="ui-badge ui-badge-neutral">{{ $e->status }}</span>
            </div>
          </li>
        @empty
          <li class="py-6 text-center text-sm text-[var(--ui-text-soft)]">Sem registros</li>
        @endforelse
      </ul>
    </x-dashboard.section-card>

    <x-dashboard.section-card title="Pontos recentes" subtitle="Ultimos registros" class="ui-coord-dashboard-panel">
      <ul class="divide-y divide-[var(--ui-border)]">
        @forelse(($tables['recentes']['pontos'] ?? []) as $p)
          <li class="py-3 first:pt-0 last:pb-0">
            <div class="flex items-center justify-between gap-3">
              <div class="min-w-0">
                <div class="font-medium text-[var(--ui-text-title)] truncate">{{ $p->nome }}</div>
                <div class="text-xs text-[var(--ui-text-soft)]">{{ $p->created_at?->format('d/m/Y H:i') }}</div>
              </div>
              <span class="ui-badge ui-badge-neutral">{{ $p->status }}</span>
            </div>
          </li>
        @empty
          <li class="py-6 text-center text-sm text-[var(--ui-text-soft)]">Sem registros</li>
        @endforelse
      </ul>
    </x-dashboard.section-card>
  </div>
</div>
@endsection

@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" defer></script>
  <script>
    window.addEventListener('load', function () {
      const charts = @json($charts ?? []);

      function uiVars() {
        const styles = getComputedStyle(document.documentElement);
        return {
          text: styles.getPropertyValue('--ui-text-soft').trim() || '#94A3B8',
          title: styles.getPropertyValue('--ui-text-title').trim() || '#CBD5E1',
          grid: styles.getPropertyValue('--ui-border').trim() || 'rgba(255,255,255,0.08)',
          primary: styles.getPropertyValue('--ui-primary').trim() || '#2f7d57',
        };
      }

      function makeChart(ctxId, cfg) {
        const el = document.getElementById(ctxId);
        if (!el || !window.Chart) return;
        const vars = uiVars();
        const base = {
          type: 'bar',
          data: { labels: [], datasets: [] },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: { display: true, labels: { color: vars.title } },
              tooltip: { enabled: true },
            },
            scales: {
              x: { ticks: { color: vars.text }, grid: { color: vars.grid } },
              y: { ticks: { color: vars.text }, grid: { color: vars.grid }, beginAtZero: true },
            }
          }
        };
        const conf = Object.assign({}, base, cfg || {});
        (conf.data.datasets || []).forEach(ds => {
          ds.borderWidth = 1;
          ds.borderColor = ds.borderColor || vars.primary;
          ds.backgroundColor = ds.backgroundColor || vars.primary;
        });
        return new Chart(el.getContext('2d'), conf);
      }

      if (charts.statusDistribuicao) {
        const ds = charts.statusDistribuicao.datasets || [];
        makeChart('chartStatusDistribuicao', {
          type: 'bar',
          data: {
            labels: charts.statusDistribuicao.labels || [],
            datasets: ds.map(d => ({ label: d.label, data: d.data }))
          },
          options: { plugins: { legend: { position: 'bottom' } } }
        });
      }

      if (charts.categoriasTop) {
        makeChart('chartTopCategorias', {
          type: 'bar',
          data: {
            labels: charts.categoriasTop.labels || [],
            datasets: (charts.categoriasTop.datasets || []).map(d => ({ label: d.label, data: d.data }))
          },
          options: {
            indexAxis: 'y',
            plugins: { legend: { display: false } },
          }
        });
      }

      if (charts.timelinePublicacoes) {
        makeChart('chartTimeline', {
          type: 'line',
          data: {
            labels: charts.timelinePublicacoes.labels || [],
            datasets: (charts.timelinePublicacoes.datasets || []).map(d => ({
              label: d.label, data: d.data, fill: false, tension: 0.3
            }))
          },
          options: {
            plugins: { legend: { position: 'bottom' } },
          }
        });
      }
    });
  </script>
@endpush
