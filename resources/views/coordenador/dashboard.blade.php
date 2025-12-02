@extends('console.layout')

@section('title', 'Dashboard — Coordenador')
@section('page.title', 'Dashboard')

@section('content')
  {{-- Ações rápidas --}}
  <div class="mb-6 flex items-center justify-between gap-3">
    <div class="flex items-center gap-2">
      <h2 class="text-xl font-semibold text-slate-100">Visão Geral</h2>
      <span class="inline-flex items-center rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-medium text-emerald-300">
        Atualizado agora
      </span>
    </div>
    <div class="flex items-center gap-2">
    <a href="{{ request()->fullUrlWithQuery(['refresh'=>1]) }}"
        class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm text-slate-200 hover:bg-white/10">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"><path d="M3 12a9 9 0 1 0 9-9v3M3 3v6h6" stroke="currentColor" stroke-width="1.5"/></svg>
        Forçar refresh
    </a>

    <form method="POST" action="{{ route('console.cache.clear') }}"
            x-data
            @submit.prevent="if (confirm('Limpar caches do sistema agora?')) $el.submit()">
        @csrf
        <button type="submit"
        class="inline-flex items-center gap-2 rounded-xl border border-red-500/30 bg-red-500/10 px-3 py-2 text-sm text-red-200 hover:bg-red-500/20">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
            <path d="M3 6h18M6 6l1 12a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-12M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2" stroke="currentColor" stroke-width="1.5"/>
        </svg>
        Limpar cache
        </button>
    </form>
    </div>



  </div>

  {{-- Cards principais --}}
  <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
    @php
      $totais = $cards['totais'] ?? [];
      $pubs   = $cards['publicados'] ?? [];
      $hoje   = $cards['hoje'] ?? [];
      $card = function($titulo,$valor,$sub=null){
        return "<div class='rounded-2xl border border-white/10 bg-white/5 p-4'>
          <div class='text-sm text-slate-400'>{$titulo}</div>
          <div class='mt-1 text-3xl font-bold text-slate-100'>".number_format((int) $valor)."</div>".
          ($sub ? "<div class='mt-1 text-xs text-slate-400'>{$sub}</div>" : "").
        "</div>";
      };
    @endphp

    {!! $card('Categorias', $totais['categorias'] ?? 0, 'Total no sistema') !!}
    {!! $card('Empresas',   $totais['empresas']   ?? 0, 'Total no sistema') !!}
    {!! $card('Pontos',     $totais['pontos']     ?? 0, 'Total no sistema') !!}
    {!! $card('Banners',    $totais['banners']    ?? 0, 'Total no sistema') !!}
  </div>

  <div class="mt-4 grid gap-4 md:grid-cols-3">
    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
      <div class="text-sm text-slate-400">Publicados</div>
      <div class="mt-3 grid grid-cols-3 gap-2 text-center">
        <div>
          <div class="text-2xl font-semibold text-slate-100">{{ number_format((int)($pubs['categorias'] ?? 0)) }}</div>
          <div class="text-xs text-slate-400">Categorias</div>
        </div>
        <div>
          <div class="text-2xl font-semibold text-slate-100">{{ number_format((int)($pubs['empresas'] ?? 0)) }}</div>
          <div class="text-xs text-slate-400">Empresas</div>
        </div>
        <div>
          <div class="text-2xl font-semibold text-slate-100">{{ number_format((int)($pubs['pontos'] ?? 0)) }}</div>
          <div class="text-xs text-slate-400">Pontos</div>
        </div>
      </div>
    </div>
    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
      <div class="text-sm text-slate-400">Hoje</div>
      <div class="mt-3 grid grid-cols-2 gap-2 text-center">
        <div>
          <div class="text-2xl font-semibold text-slate-100">{{ number_format((int)($hoje['novos'] ?? 0)) }}</div>
          <div class="text-xs text-slate-400">Novos cadastros</div>
        </div>
        <div>
          <div class="text-2xl font-semibold text-slate-100">{{ number_format((int)($hoje['publicados'] ?? 0)) }}</div>
          <div class="text-xs text-slate-400">Publicações</div>
        </div>
      </div>
    </div>
    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
      <div class="text-sm text-slate-400">KPIs rápidos</div>
      <div class="mt-3 space-y-2">
        <div class="flex items-center justify-between text-sm">
          <span class="text-slate-300">Empresas recomendadas</span>
          <span class="text-slate-100 font-semibold">{{ number_format((int)($kpis['recomendados']['empresas'] ?? 0)) }}</span>
        </div>
        <div class="flex items-center justify-between text-sm">
          <span class="text-slate-300">Pontos recomendados</span>
          <span class="text-slate-100 font-semibold">{{ number_format((int)($kpis['recomendados']['pontos'] ?? 0)) }}</span>
        </div>
      </div>
    </div>
  </div>

  {{-- Gráficos --}}
  <div class="mt-8 grid gap-4 lg:grid-cols-3">
    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
      <div class="mb-3 flex items-center justify-between">
        <h3 class="text-sm font-medium text-slate-200">Distribuição por Status</h3>
      </div>
      <canvas id="chartStatusDistribuicao" height="220"></canvas>
    </div>

    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
      <div class="mb-3 flex items-center justify-between">
        <h3 class="text-sm font-medium text-slate-200">Top Categorias (itens publicados)</h3>
      </div>
      <canvas id="chartTopCategorias" height="220"></canvas>
    </div>

    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
      <div class="mb-3 flex items-center justify-between">
        <h3 class="text-sm font-medium text-slate-200">Timeline de Publicações (30 dias)</h3>
      </div>
      <canvas id="chartTimeline" height="220"></canvas>
    </div>
  </div>

  {{-- Saúde do catálogo --}}
  <div class="mt-8 grid gap-4 lg:grid-cols-3">
    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
      <h3 class="text-sm font-medium text-slate-200 mb-4">Cobertura de Mapa</h3>
      @php
        $mapEmp = (int) ($health['mapa']['empresas']['percent'] ?? 0);
        $mapPon = (int) ($health['mapa']['pontos']['percent'] ?? 0);
      @endphp
      <div class="mb-3">
        <div class="flex justify-between text-xs text-slate-400 mb-1"><span>Empresas com lat/lng</span><span>{{ $mapEmp }}%</span></div>
        <div class="h-2 w-full rounded-full bg-white/10">
          <div class="h-2 rounded-full bg-emerald-500" style="width: {{ $mapEmp }}%"></div>
        </div>
      </div>
      <div>
        <div class="flex justify-between text-xs text-slate-400 mb-1"><span>Pontos com lat/lng</span><span>{{ $mapPon }}%</span></div>
        <div class="h-2 w-full rounded-full bg-white/10">
          <div class="h-2 rounded-full bg-emerald-500" style="width: {{ $mapPon }}%"></div>
        </div>
      </div>
    </div>

    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
      <h3 class="text-sm font-medium text-slate-200 mb-4">Capas & Mídias</h3>
      @php
        $capaPon = (int) ($health['capas']['pontos']['percent'] ?? 0);
        $media   = number_format((float)($health['midia']['media_midias_por_ponto'] ?? 0), 1, ',', '.');
      @endphp
      <div class="mb-3">
        <div class="flex justify-between text-xs text-slate-400 mb-1"><span>Pontos com capa</span><span>{{ $capaPon }}%</span></div>
        <div class="h-2 w-full rounded-full bg-white/10">
          <div class="h-2 rounded-full bg-emerald-500" style="width: {{ $capaPon }}%"></div>
        </div>
      </div>
      <div class="text-xs text-slate-400">
        Média de mídias por ponto: <span class="font-semibold text-slate-200">{{ $media }}</span>
      </div>
    </div>

    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
      <h3 class="text-sm font-medium text-slate-200 mb-4">Recomendados Ativos (janela)</h3>
      <div class="space-y-2">
        <div class="text-xs text-slate-400">Empresas</div>
        <div class="flex flex-wrap gap-2">
          @foreach(($tables['recomendadosAtivos']['empresas'] ?? []) as $rec)
            <span class="rounded-full bg-amber-500/10 px-2.5 py-1 text-xs text-amber-300">
              {{ $rec->empresa->nome ?? '—' }}
            </span>
          @endforeach
          @if(empty($tables['recomendadosAtivos']['empresas']) || !count($tables['recomendadosAtivos']['empresas']))
            <span class="text-xs text-slate-500">Nenhum ativo</span>
          @endif
        </div>
        <div class="text-xs text-slate-400 mt-3">Pontos</div>
        <div class="flex flex-wrap gap-2">
          @foreach(($tables['recomendadosAtivos']['pontos'] ?? []) as $rec)
            <span class="rounded-full bg-sky-500/10 px-2.5 py-1 text-xs text-sky-300">
              {{ $rec->ponto->nome ?? '—' }}
            </span>
          @endforeach
          @if(empty($tables['recomendadosAtivos']['pontos']) || !count($tables['recomendadosAtivos']['pontos']))
            <span class="text-xs text-slate-500">Nenhum ativo</span>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- Listas recentes --}}
  <div class="mt-8 grid gap-4 lg:grid-cols-3">
    <div class="rounded-2xl border border-white/10 bg-white/5">
      <div class="flex items-center justify-between border-b border-white/10 px-4 py-3">
        <h3 class="text-sm font-medium text-slate-200">Categorias recentes</h3>
      </div>
      <ul class="divide-y divide-white/10">
        @forelse(($tables['recentes']['categorias'] ?? []) as $c)
          <li class="px-4 py-3">
            <div class="flex items-center justify-between">
              <div>
                <div class="font-medium text-slate-100">{{ $c->nome }}</div>
                <div class="text-xs text-slate-400">{{ $c->created_at?->format('d/m/Y H:i') }}</div>
              </div>
              <span class="rounded-full bg-white/5 px-2 py-1 text-xs text-slate-300">{{ $c->status }}</span>
            </div>
          </li>
        @empty
          <li class="px-4 py-6 text-center text-sm text-slate-500">Sem registros</li>
        @endforelse
      </ul>
    </div>

    <div class="rounded-2xl border border-white/10 bg-white/5">
      <div class="flex items-center justify-between border-b border-white/10 px-4 py-3">
        <h3 class="text-sm font-medium text-slate-200">Empresas recentes</h3>
      </div>
      <ul class="divide-y divide-white/10">
        @forelse(($tables['recentes']['empresas'] ?? []) as $e)
          <li class="px-4 py-3">
            <div class="flex items-center justify-between">
              <div>
                <div class="font-medium text-slate-100">{{ $e->nome }}</div>
                <div class="text-xs text-slate-400">{{ $e->created_at?->format('d/m/Y H:i') }}</div>
              </div>
              <span class="rounded-full bg-white/5 px-2 py-1 text-xs text-slate-300">{{ $e->status }}</span>
            </div>
          </li>
        @empty
          <li class="px-4 py-6 text-center text-sm text-slate-500">Sem registros</li>
        @endforelse
      </ul>
    </div>

    <div class="rounded-2xl border border-white/10 bg-white/5">
      <div class="flex items-center justify-between border-b border-white/10 px-4 py-3">
        <h3 class="text-sm font-medium text-slate-200">Pontos recentes</h3>
      </div>
      <ul class="divide-y divide-white/10">
        @forelse(($tables['recentes']['pontos'] ?? []) as $p)
          <li class="px-4 py-3">
            <div class="flex items-center justify-between">
              <div>
                <div class="font-medium text-slate-100">{{ $p->nome }}</div>
                <div class="text-xs text-slate-400">{{ $p->created_at?->format('d/m/Y H:i') }}</div>
              </div>
              <span class="rounded-full bg-white/5 px-2 py-1 text-xs text-slate-300">{{ $p->status }}</span>
            </div>
          </li>
        @empty
          <li class="px-4 py-6 text-center text-sm text-slate-500">Sem registros</li>
        @endforelse
      </ul>
    </div>
  </div>

@endsection

@push('scripts')
  {{-- Chart.js CDN (leve e direto) --}}
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" defer></script>
  <script>
    window.addEventListener('load', function () {
      const charts = @json($charts ?? []);

      // Util para criar gráficos com fallback
      function makeChart(ctxId, cfg) {
        const el = document.getElementById(ctxId);
        if (!el || !window.Chart) return;
        const base = {
          type: 'bar',
          data: { labels: [], datasets: [] },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: { display: true, labels: { color: '#CBD5E1' } },
              tooltip: { enabled: true },
            },
            scales: {
              x: { ticks: { color: '#94A3B8' }, grid: { color: 'rgba(255,255,255,0.06)' } },
              y: { ticks: { color: '#94A3B8' }, grid: { color: 'rgba(255,255,255,0.06)' }, beginAtZero: true },
            }
          }
        };
        const conf = Object.assign({}, base, cfg || {});
        // cores automáticas (sem definir cores fixas — mantém tema)
        (conf.data.datasets || []).forEach(ds => { ds.borderWidth = 1; });
        return new Chart(el.getContext('2d'), conf);
      }

      // 1) Distribuição por status (empilha colunas)
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

      // 2) Top categorias (itens publicados) — barras horizontais
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

      // 3) Timeline de publicações — linha
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
