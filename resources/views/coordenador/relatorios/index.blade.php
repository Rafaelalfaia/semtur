@extends('console.layout')

@section('title', 'Relatórios')
@section('page.title', 'Relatórios')
@section('topbar.description', 'Consulte indicadores, gráficos e amostras editoriais no mesmo padrão visual do console.')

@section('topbar.nav')
  <span class="ui-console-topbar-tab is-active">Relatórios</span>
@endsection

@php
  use App\Models\Catalogo\Categoria;

  $u = auth()->user();
  $canView = $u?->can('relatorios.view');
  $categorias = class_exists(Categoria::class) ? Categoria::orderBy('nome')->get(['id', 'nome']) : collect();
  $vDataIni = isset($data_ini) && $data_ini ? $data_ini->format('Y-m-d') : request('data_inicial');
  $vDataFim = isset($data_fim) && $data_fim ? $data_fim->format('Y-m-d') : request('data_final');
  $tabelaEmpresas = isset($tabelaEmpresas) ? $tabelaEmpresas : collect();
  $tabelaPontos = isset($tabelaPontos) ? $tabelaPontos : collect();
  $tabelaEventos = isset($tabelaEventos) ? $tabelaEventos : collect();
  $empFirst = $tabelaEmpresas->first();
  $ptoFirst = $tabelaPontos->first();
  $empHasCidade = $empFirst && isset($empFirst->cidade);
  $empHasRegiao = $empFirst && isset($empFirst->regiao);
  $ptoHasCidade = $ptoFirst && isset($ptoFirst->cidade);
  $ptoHasRegiao = $ptoFirst && isset($ptoFirst->regiao);
  $kpis = isset($kpis) && is_array($kpis) ? $kpis : [];
  $charts = isset($charts) && is_array($charts) ? $charts : [];
@endphp

@section('content')
<div class="ui-console-page">
  @unless($canView)
    <div class="ui-alert ui-alert-warning max-w-3xl">
      Você não tem permissão para visualizar relatórios.
    </div>
    @php return; @endphp
  @endunless

  <x-dashboard.page-header
    title="Relatórios"
    subtitle="Acompanhe indicadores editoriais, evolução e amostras dos conteúdos publicados."
  />

  <x-dashboard.section-card title="Filtros" subtitle="Refine por status, categoria, localização, busca e período" class="ui-coord-dashboard-panel mt-5">
    <form method="GET" class="space-y-4">
      <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
        <div>
          <label class="ui-form-label">Status</label>
          <select name="status" class="ui-form-select">
            @foreach(['todos' => 'Todos', 'publicado' => 'Publicado', 'rascunho' => 'Rascunho', 'arquivado' => 'Arquivado'] as $val => $lbl)
              <option value="{{ $val }}" @selected(($status ?? 'todos') === $val)>{{ $lbl }}</option>
            @endforeach
          </select>
        </div>

        <div>
          <label class="ui-form-label">Categoria</label>
          <select name="categoria_id" class="ui-form-select">
            <option value="0">Todas</option>
            @foreach($categorias as $c)
              <option value="{{ $c->id }}" @selected((int)($categoria_id ?? 0) === (int)$c->id)>{{ $c->nome }}</option>
            @endforeach
          </select>
        </div>

        <div>
          <label class="ui-form-label">Cidade</label>
          <input type="text" name="cidade" value="{{ $cidade ?? '' }}" class="ui-form-control" placeholder="Opcional">
        </div>

        <div>
          <label class="ui-form-label">Região</label>
          <input type="text" name="regiao" value="{{ $regiao ?? '' }}" class="ui-form-control" placeholder="Opcional">
        </div>

        <div>
          <label class="ui-form-label">Buscar</label>
          <input type="text" name="q" value="{{ $q ?? '' }}" class="ui-form-control" placeholder="Nome/descrição...">
        </div>

        <div>
          <label class="ui-form-label">Últimos meses</label>
          <select name="meses" class="ui-form-select">
            @foreach([3, 6, 12, 18, 24] as $m)
              <option value="{{ $m }}" @selected((int)($meses ?? 12) === $m)>{{ $m }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div>
          <label class="ui-form-label">Data inicial</label>
          <input type="date" name="data_inicial" value="{{ $vDataIni }}" class="ui-form-control">
        </div>

        <div>
          <label class="ui-form-label">Data final</label>
          <input type="date" name="data_final" value="{{ $vDataFim }}" class="ui-form-control">
        </div>

        <div class="sm:col-span-2 flex items-end gap-2">
          <button type="submit" class="ui-btn-primary">Aplicar filtros</button>
          <a href="{{ route(\Illuminate\Support\Facades\Route::currentRouteName()) }}" class="ui-btn-secondary">Limpar</a>
        </div>
      </div>
    </form>
  </x-dashboard.section-card>

  <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
    @foreach([
      ['label' => 'Categorias', 'key' => 'categorias'],
      ['label' => 'Empresas publicadas', 'key' => 'empresas_pub'],
      ['label' => 'Pontos publicados', 'key' => 'pontos_pub'],
      ['label' => 'Eventos (total)', 'key' => 'eventos_totais'],
      ['label' => 'Avisos ativos', 'key' => 'avisos_ativos'],
      ['label' => 'Banners ativos', 'key' => 'banners_ativos'],
      ['label' => 'Destaques ativos', 'key' => 'destaques_ativos'],
      ['label' => 'Recomendações (empresas)', 'key' => 'recs_empresas'],
      ['label' => 'Recomendações (pontos)', 'key' => 'recs_pontos'],
      ['label' => 'Mídias de pontos', 'key' => 'midias_pontos'],
    ] as $k)
      @php
        $raw = $kpis[$k['key']] ?? 0;
        $value = is_numeric($raw) ? (int) $raw : 0;
      @endphp
      <div class="ui-metric-card">
        <div class="ui-metric-label">{{ $k['label'] }}</div>
        <div class="ui-metric-value">{{ number_format($value, 0, ',', '.') }}</div>
      </div>
    @endforeach
  </div>

  <div class="mt-5 grid gap-4 md:grid-cols-2">
    <x-dashboard.section-card title="Empresas por status" subtitle="Leitura visual do recorte atual" class="ui-coord-dashboard-panel">
      @if(!empty($charts['status_empresas']))
        <img src="{{ $charts['status_empresas'] }}" alt="Empresas por status" loading="lazy" class="w-full rounded-lg">
      @else
        <div class="ui-empty-state"><div class="ui-empty-state-title">Sem dados</div></div>
      @endif
    </x-dashboard.section-card>

    <x-dashboard.section-card title="Pontos por status" subtitle="Leitura visual do recorte atual" class="ui-coord-dashboard-panel">
      @if(!empty($charts['status_pontos']))
        <img src="{{ $charts['status_pontos'] }}" alt="Pontos por status" loading="lazy" class="w-full rounded-lg">
      @else
        <div class="ui-empty-state"><div class="ui-empty-state-title">Sem dados</div></div>
      @endif
    </x-dashboard.section-card>

    <x-dashboard.section-card title="Evolução mensal" subtitle="Últimos {{ (int)($meses ?? 12) }} meses" class="ui-coord-dashboard-panel md:col-span-2">
      @if(!empty($charts['evolucao_mensal']))
        <img src="{{ $charts['evolucao_mensal'] }}" alt="Evolução mensal" loading="lazy" class="w-full rounded-lg">
      @else
        <div class="ui-empty-state"><div class="ui-empty-state-title">Sem dados</div></div>
      @endif
    </x-dashboard.section-card>

    <x-dashboard.section-card title="Empresas por categoria" subtitle="Categorias com mais ocorrências" class="ui-coord-dashboard-panel">
      @if(!empty($charts['empresas_categoria']))
        <img src="{{ $charts['empresas_categoria'] }}" alt="Empresas por categoria" loading="lazy" class="w-full rounded-lg">
      @else
        <div class="ui-empty-state"><div class="ui-empty-state-title">Sem dados</div></div>
      @endif
    </x-dashboard.section-card>

    <x-dashboard.section-card title="Pontos por categoria" subtitle="Categorias com mais ocorrências" class="ui-coord-dashboard-panel">
      @if(!empty($charts['pontos_categoria']))
        <img src="{{ $charts['pontos_categoria'] }}" alt="Pontos por categoria" loading="lazy" class="w-full rounded-lg">
      @else
        <div class="ui-empty-state"><div class="ui-empty-state-title">Sem dados</div></div>
      @endif
    </x-dashboard.section-card>
  </div>

  <div class="mt-5 grid gap-6">
    <x-dashboard.section-card title="Empresas" subtitle="Amostra dos registros do recorte atual" class="ui-coord-dashboard-panel">
      <div class="ui-table-shell">
        <table class="min-w-full text-sm">
          <thead class="ui-table-head">
            <tr>
              <th class="px-3 py-2 text-left">ID</th>
              <th class="px-3 py-2 text-left">Nome</th>
              <th class="px-3 py-2 text-left">Slug</th>
              <th class="px-3 py-2 text-left">Status</th>
              @if($empHasCidade)<th class="px-3 py-2 text-left">Cidade</th>@endif
              @if($empHasRegiao)<th class="px-3 py-2 text-left">Região</th>@endif
              <th class="px-3 py-2 text-left">Categorias</th>
              <th class="px-3 py-2 text-left">Criado em</th>
            </tr>
          </thead>
          <tbody>
            @forelse($tabelaEmpresas as $r)
              <tr class="ui-table-row">
                <td class="px-3 py-2 text-[var(--ui-text-soft)]">{{ $r->id }}</td>
                <td class="px-3 py-2">{{ $r->nome }}</td>
                <td class="px-3 py-2 text-[var(--ui-text-soft)]">{{ $r->slug }}</td>
                <td class="px-3 py-2"><span class="ui-badge ui-badge-neutral">{{ $r->status ?? '—' }}</span></td>
                @if($empHasCidade)<td class="px-3 py-2">{{ $r->cidade ?? '—' }}</td>@endif
                @if($empHasRegiao)<td class="px-3 py-2">{{ $r->regiao ?? '—' }}</td>@endif
                <td class="px-3 py-2">{{ method_exists($r, 'categorias') ? $r->categorias->pluck('nome')->join(', ') : '—' }}</td>
                <td class="px-3 py-2 text-[var(--ui-text-soft)]">{{ optional($r->created_at)->format('d/m/Y H:i') }}</td>
              </tr>
            @empty
              <tr class="ui-table-row"><td colspan="8" class="px-3 py-6 text-center text-[var(--ui-text-soft)]">Nenhum registro encontrado.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </x-dashboard.section-card>

    <x-dashboard.section-card title="Pontos turísticos" subtitle="Amostra dos registros do recorte atual" class="ui-coord-dashboard-panel">
      <div class="ui-table-shell">
        <table class="min-w-full text-sm">
          <thead class="ui-table-head">
            <tr>
              <th class="px-3 py-2 text-left">ID</th>
              <th class="px-3 py-2 text-left">Nome</th>
              <th class="px-3 py-2 text-left">Slug</th>
              <th class="px-3 py-2 text-left">Status</th>
              @if($ptoHasCidade)<th class="px-3 py-2 text-left">Cidade</th>@endif
              @if($ptoHasRegiao)<th class="px-3 py-2 text-left">Região</th>@endif
              <th class="px-3 py-2 text-left">Categorias</th>
              <th class="px-3 py-2 text-left">Criado em</th>
            </tr>
          </thead>
          <tbody>
            @forelse($tabelaPontos as $r)
              <tr class="ui-table-row">
                <td class="px-3 py-2 text-[var(--ui-text-soft)]">{{ $r->id }}</td>
                <td class="px-3 py-2">{{ $r->nome }}</td>
                <td class="px-3 py-2 text-[var(--ui-text-soft)]">{{ $r->slug }}</td>
                <td class="px-3 py-2"><span class="ui-badge ui-badge-neutral">{{ $r->status ?? '—' }}</span></td>
                @if($ptoHasCidade)<td class="px-3 py-2">{{ $r->cidade ?? '—' }}</td>@endif
                @if($ptoHasRegiao)<td class="px-3 py-2">{{ $r->regiao ?? '—' }}</td>@endif
                <td class="px-3 py-2">{{ method_exists($r, 'categorias') ? $r->categorias->pluck('nome')->join(', ') : '—' }}</td>
                <td class="px-3 py-2 text-[var(--ui-text-soft)]">{{ optional($r->created_at)->format('d/m/Y H:i') }}</td>
              </tr>
            @empty
              <tr class="ui-table-row"><td colspan="8" class="px-3 py-6 text-center text-[var(--ui-text-soft)]">Nenhum registro encontrado.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </x-dashboard.section-card>

    <x-dashboard.section-card title="Eventos" subtitle="Amostra dos registros do recorte atual" class="ui-coord-dashboard-panel">
      <div class="ui-table-shell">
        <table class="min-w-full text-sm">
          <thead class="ui-table-head">
            <tr>
              <th class="px-3 py-2 text-left">ID</th>
              <th class="px-3 py-2 text-left">Nome</th>
              <th class="px-3 py-2 text-left">Slug</th>
              <th class="px-3 py-2 text-left">Criado em</th>
            </tr>
          </thead>
          <tbody>
            @forelse($tabelaEventos as $r)
              <tr class="ui-table-row">
                <td class="px-3 py-2 text-[var(--ui-text-soft)]">{{ $r->id }}</td>
                <td class="px-3 py-2">{{ $r->nome ?? '—' }}</td>
                <td class="px-3 py-2 text-[var(--ui-text-soft)]">{{ $r->slug ?? '—' }}</td>
                <td class="px-3 py-2 text-[var(--ui-text-soft)]">{{ optional($r->created_at)->format('d/m/Y H:i') }}</td>
              </tr>
            @empty
              <tr class="ui-table-row"><td colspan="4" class="px-3 py-6 text-center text-[var(--ui-text-soft)]">Nenhum registro encontrado.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </x-dashboard.section-card>
  </div>
</div>
@endsection
