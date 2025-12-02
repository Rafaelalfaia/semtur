@extends('console.layout')

@section('title','Relatórios · Console')
@section('page.title','Relatórios')

@php
  use App\Models\Catalogo\Categoria;

  $u = auth()->user();
  $canView = $u?->can('relatorios.view');

  // Fonte de categorias (leve; opcional mover p/ controller)
  $categorias = class_exists(Categoria::class)
    ? Categoria::orderBy('nome')->get(['id','nome'])
    : collect();

  // Datas (podem chegar como Carbon/null)
  $vDataIni = isset($data_ini) && $data_ini ? $data_ini->format('Y-m-d') : request('data_inicial');
  $vDataFim = isset($data_fim) && $data_fim ? $data_fim->format('Y-m-d') : request('data_final');

  // Tabelas (collections) com fallbacks
  $tabelaEmpresas = isset($tabelaEmpresas) ? $tabelaEmpresas : collect();
  $tabelaPontos   = isset($tabelaPontos)   ? $tabelaPontos   : collect();
  $tabelaEventos  = isset($tabelaEventos)  ? $tabelaEventos  : collect();

  $empFirst = $tabelaEmpresas->first();
  $ptoFirst = $tabelaPontos->first();

  $empHasCidade = $empFirst && isset($empFirst->cidade);
  $empHasRegiao = $empFirst && isset($empFirst->regiao);
  $ptoHasCidade = $ptoFirst && isset($ptoFirst->cidade);
  $ptoHasRegiao = $ptoFirst && isset($ptoFirst->regiao);

  $kpis   = isset($kpis)   && is_array($kpis)   ? $kpis   : [];
  $charts = isset($charts) && is_array($charts) ? $charts : [];
@endphp

@section('content')
  @unless($canView)
    <div class="rounded-xl border border-amber-500/30 bg-amber-900/20 p-4 text-amber-200 max-w-3xl">
      Você não tem permissão para visualizar Relatórios.
    </div>
    @php return; @endphp
  @endunless

  {{-- Filtros --}}
  <form method="GET" class="mb-6 rounded-xl border border-white/10 bg-[#0F1412] p-4 sm:p-5">
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
      <div>
        <label class="block text-xs text-slate-400 mb-1">Status</label>
        <select name="status" class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-sm">
          @foreach(['todos'=>'Todos','publicado'=>'Publicado','rascunho'=>'Rascunho','arquivado'=>'Arquivado'] as $val=>$lbl)
            <option value="{{ $val }}" @selected(($status ?? 'todos')===$val)>{{ $lbl }}</option>
          @endforeach
        </select>
      </div>

      <div>
        <label class="block text-xs text-slate-400 mb-1">Categoria</label>
        <select name="categoria_id" class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-sm">
          <option value="0">Todas</option>
          @foreach($categorias as $c)
            <option value="{{ $c->id }}" @selected((int)($categoria_id ?? 0) === (int)$c->id)>{{ $c->nome }}</option>
          @endforeach
        </select>
      </div>

      <div>
        <label class="block text-xs text-slate-400 mb-1">Cidade</label>
        <input type="text" name="cidade" value="{{ $cidade ?? '' }}"
               class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-sm" placeholder="Opcional">
      </div>

      <div>
        <label class="block text-xs text-slate-400 mb-1">Região</label>
        <input type="text" name="regiao" value="{{ $regiao ?? '' }}"
               class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-sm" placeholder="Opcional">
      </div>

      <div>
        <label class="block text-xs text-slate-400 mb-1">Buscar</label>
        <input type="text" name="q" value="{{ $q ?? '' }}"
               class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-sm" placeholder="Nome/descrição...">
      </div>

      <div>
        <label class="block text-xs text-slate-400 mb-1">Últimos meses</label>
        <select name="meses" class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-sm">
          @foreach([3,6,12,18,24] as $m)
            <option value="{{ $m }}" @selected((int)($meses ?? 12)===$m)>{{ $m }}</option>
          @endforeach
        </select>
      </div>
    </div>

    <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
      <div>
        <label class="block text-xs text-slate-400 mb-1">Data inicial</label>
        <input type="date" name="data_inicial" value="{{ $vDataIni }}"
               class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-sm">
      </div>
      <div>
        <label class="block text-xs text-slate-400 mb-1">Data final</label>
        <input type="date" name="data_final" value="{{ $vDataFim }}"
               class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-sm">
      </div>

      <div class="sm:col-span-2 flex items-end gap-2">
        <button type="submit"
                class="inline-flex items-center rounded-lg bg-emerald-600 px-3 py-2 text-sm text-white hover:bg-emerald-700">
          Aplicar filtros
        </button>
        <a href="{{ route(\Illuminate\Support\Facades\Route::currentRouteName()) }}"
           class="inline-flex items-center rounded-lg bg-white/5 px-3 py-2 text-sm hover:bg-white/10">
          Limpar
        </a>
      </div>
    </div>
  </form>

  {{-- KPIs --}}
  <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
    @foreach([
      ['label'=>'Categorias','key'=>'categorias'],
      ['label'=>'Empresas publicadas','key'=>'empresas_pub'],
      ['label'=>'Pontos publicados','key'=>'pontos_pub'],
      ['label'=>'Eventos (total)','key'=>'eventos_totais'],
      ['label'=>'Avisos ativos','key'=>'avisos_ativos'],
      ['label'=>'Banners ativos','key'=>'banners_ativos'],
      ['label'=>'Destaques ativos','key'=>'destaques_ativos'],
      ['label'=>'Recomendações (empresas)','key'=>'recs_empresas'],
      ['label'=>'Recomendações (pontos)','key'=>'recs_pontos'],
      ['label'=>'Mídias de pontos','key'=>'midias_pontos'],
    ] as $k)
      @php
        $raw = $kpis[$k['key']] ?? 0;
        $value = is_numeric($raw) ? (int)$raw : 0;
      @endphp
      <div class="rounded-xl border border-white/10 bg-[#0F1412] p-4">
        <div class="text-xs text-slate-400">{{ $k['label'] }}</div>
        <div class="mt-1 text-2xl font-semibold">{{ number_format($value,0,',','.') }}</div>
      </div>
    @endforeach
  </div>

  {{-- Gráficos --}}
  <div class="mt-6 grid gap-4 md:grid-cols-2">
    @php
      $chartBox = function(string $label, ?string $src) {
        $safe = $src && trim($src) !== '' ? $src : null;
        return <<<HTML
          <div class="rounded-xl border border-white/10 bg-[#0F1412] p-4">
            <div class="mb-2 text-sm text-slate-300">{$label}</div>
            <!-- chart -->
            <div class="w-full rounded-lg border border-white/10 bg-white/5">
              <!-- img or placeholder -->
              <!-- will be replaced below -->
            </div>
          </div>
        HTML;
      };
    @endphp

    <div class="rounded-xl border border-white/10 bg-[#0F1412] p-4">
      <div class="mb-2 text-sm text-slate-300">Empresas por status</div>
      @if(!empty($charts['status_empresas']))
        <img src="{{ $charts['status_empresas'] }}" alt="Empresas por status" loading="lazy" class="w-full rounded-lg">
      @else
        <div class="w-full rounded-lg border border-white/10 bg-white/5 p-6 text-center text-slate-400">
          Sem dados.
        </div>
      @endif
    </div>

    <div class="rounded-xl border border-white/10 bg-[#0F1412] p-4">
      <div class="mb-2 text-sm text-slate-300">Pontos por status</div>
      @if(!empty($charts['status_pontos']))
        <img src="{{ $charts['status_pontos'] }}" alt="Pontos por status" loading="lazy" class="w-full rounded-lg">
      @else
        <div class="w-full rounded-lg border border-white/10 bg-white/5 p-6 text-center text-slate-400">
          Sem dados.
        </div>
      @endif
    </div>

    <div class="rounded-xl border border-white/10 bg-[#0F1412] p-4 md:col-span-2">
      <div class="mb-2 text-sm text-slate-300">Evolução (últimos {{ (int)($meses ?? 12) }} meses)</div>
      @if(!empty($charts['evolucao_mensal']))
        <img src="{{ $charts['evolucao_mensal'] }}" alt="Evolução mensal" loading="lazy" class="w-full rounded-lg">
      @else
        <div class="w-full rounded-lg border border-white/10 bg-white/5 p-6 text-center text-slate-400">
          Sem dados.
        </div>
      @endif
    </div>

    <div class="rounded-xl border border-white/10 bg-[#0F1412] p-4">
      <div class="mb-2 text-sm text-slate-300">Empresas por categoria (Top)</div>
      @if(!empty($charts['empresas_categoria']))
        <img src="{{ $charts['empresas_categoria'] }}" alt="Empresas por categoria" loading="lazy" class="w-full rounded-lg">
      @else
        <div class="w-full rounded-lg border border-white/10 bg-white/5 p-6 text-center text-slate-400">
          Sem dados.
        </div>
      @endif
    </div>

    <div class="rounded-xl border border-white/10 bg-[#0F1412] p-4">
      <div class="mb-2 text-sm text-slate-300">Pontos por categoria (Top)</div>
      @if(!empty($charts['pontos_categoria']))
        <img src="{{ $charts['pontos_categoria'] }}" alt="Pontos por categoria" loading="lazy" class="w-full rounded-lg">
      @else
        <div class="w-full rounded-lg border border-white/10 bg-white/5 p-6 text-center text-slate-400">
          Sem dados.
        </div>
      @endif
    </div>
  </div>

  {{-- Listas / amostras --}}
  <div class="mt-8 grid gap-6">
    {{-- EMPRESAS --}}
    <section>
      <h2 class="mb-2 text-lg font-semibold">Empresas (amostra)</h2>
      <div class="overflow-x-auto rounded-xl border border-white/10">
        <table class="min-w-full text-sm">
          <thead class="bg-white/5 text-left">
            <tr>
              <th class="px-3 py-2">ID</th>
              <th class="px-3 py-2">Nome</th>
              <th class="px-3 py-2">Slug</th>
              <th class="px-3 py-2">Status</th>
              @if($empHasCidade)<th class="px-3 py-2">Cidade</th>@endif
              @if($empHasRegiao)<th class="px-3 py-2">Região</th>@endif
              <th class="px-3 py-2">Categorias</th>
              <th class="px-3 py-2">Criado em</th>
            </tr>
          </thead>
          <tbody>
            @forelse($tabelaEmpresas as $r)
              <tr class="border-t border-white/5">
                <td class="px-3 py-2 text-slate-400">{{ $r->id }}</td>
                <td class="px-3 py-2">{{ $r->nome }}</td>
                <td class="px-3 py-2 text-slate-400">{{ $r->slug }}</td>
                <td class="px-3 py-2">
                  <span class="rounded-full bg-white/5 px-2 py-0.5 text-xs">{{ $r->status ?? '—' }}</span>
                </td>
                @if($empHasCidade)<td class="px-3 py-2">{{ $r->cidade ?? '—' }}</td>@endif
                @if($empHasRegiao)<td class="px-3 py-2">{{ $r->regiao ?? '—' }}</td>@endif
                <td class="px-3 py-2 text-slate-300">
                  {{ method_exists($r,'categorias') ? $r->categorias->pluck('nome')->join(', ') : '—' }}
                </td>
                <td class="px-3 py-2 text-slate-400">{{ optional($r->created_at)->format('d/m/Y H:i') }}</td>
              </tr>
            @empty
              <tr><td colspan="8" class="px-3 py-6 text-center text-slate-400">Nenhum registro encontrado.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </section>

    {{-- PONTOS --}}
    <section>
      <h2 class="mb-2 text-lg font-semibold">Pontos Turísticos (amostra)</h2>
      <div class="overflow-x-auto rounded-xl border border-white/10">
        <table class="min-w-full text-sm">
          <thead class="bg-white/5 text-left">
            <tr>
              <th class="px-3 py-2">ID</th>
              <th class="px-3 py-2">Nome</th>
              <th class="px-3 py-2">Slug</th>
              <th class="px-3 py-2">Status</th>
              @if($ptoHasCidade)<th class="px-3 py-2">Cidade</th>@endif
              @if($ptoHasRegiao)<th class="px-3 py-2">Região</th>@endif
              <th class="px-3 py-2">Categorias</th>
              <th class="px-3 py-2">Criado em</th>
            </tr>
          </thead>
          <tbody>
            @forelse($tabelaPontos as $r)
              <tr class="border-t border-white/5">
                <td class="px-3 py-2 text-slate-400">{{ $r->id }}</td>
                <td class="px-3 py-2">{{ $r->nome }}</td>
                <td class="px-3 py-2 text-slate-400">{{ $r->slug }}</td>
                <td class="px-3 py-2">
                  <span class="rounded-full bg-white/5 px-2 py-0.5 text-xs">{{ $r->status ?? '—' }}</span>
                </td>
                @if($ptoHasCidade)<td class="px-3 py-2">{{ $r->cidade ?? '—' }}</td>@endif
                @if($ptoHasRegiao)<td class="px-3 py-2">{{ $r->regiao ?? '—' }}</td>@endif
                <td class="px-3 py-2 text-slate-300">
                  {{ method_exists($r,'categorias') ? $r->categorias->pluck('nome')->join(', ') : '—' }}
                </td>
                <td class="px-3 py-2 text-slate-400">{{ optional($r->created_at)->format('d/m/Y H:i') }}</td>
              </tr>
            @empty
              <tr><td colspan="8" class="px-3 py-6 text-center text-slate-400">Nenhum registro encontrado.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </section>

    {{-- EVENTOS --}}
    <section>
      <h2 class="mb-2 text-lg font-semibold">Eventos (amostra)</h2>
      <div class="overflow-x-auto rounded-xl border border-white/10">
        <table class="min-w-full text-sm">
          <thead class="bg-white/5 text-left">
            <tr>
              <th class="px-3 py-2">ID</th>
              <th class="px-3 py-2">Nome</th>
              <th class="px-3 py-2">Slug</th>
              <th class="px-3 py-2">Criado em</th>
            </tr>
          </thead>
          <tbody>
            @forelse($tabelaEventos as $r)
              <tr class="border-t border-white/5">
                <td class="px-3 py-2 text-slate-400">{{ $r->id }}</td>
                <td class="px-3 py-2">{{ $r->nome ?? '—' }}</td>
                <td class="px-3 py-2 text-slate-400">{{ $r->slug ?? '—' }}</td>
                <td class="px-3 py-2 text-slate-400">{{ optional($r->created_at)->format('d/m/Y H:i') }}</td>
              </tr>
            @empty
              <tr><td colspan="4" class="px-3 py-6 text-center text-slate-400">Nenhum registro encontrado.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </section>
  </div>
@endsection
