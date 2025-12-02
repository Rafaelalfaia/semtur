<?php

namespace App\Http\Controllers\Coordenador;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;

// Models
use App\Models\Catalogo\Categoria;
use App\Models\Catalogo\Empresa;
use App\Models\Catalogo\PontoTuristico;
use App\Models\Catalogo\PontoMidia;
use App\Models\Catalogo\PontoRecomendacao;
use App\Models\Catalogo\EmpresaRecomendacao;
use App\Models\Conteudo\Banner;
use App\Models\Conteudo\BannerDestaque;
use App\Models\Conteudo\Aviso;

class RelatorioController extends Controller
{
    public function index(Request $request)
    {
        // ----------------- Filtros -----------------
        $status       = $request->input('status', 'todos'); // publicado|rascunho|arquivado|todos
        $categoria_id = (int) $request->input('categoria_id', 0);
        $cidade       = trim((string) $request->input('cidade', ''));
        $regiao       = trim((string) $request->input('regiao', ''));
        $q            = trim((string) $request->input('q', ''));
        $data_ini     = $request->date('data_inicial');
        $data_fim     = $request->date('data_final');
        $meses        = max(3, min((int) $request->input('meses', 6), 24)); // 3..24
        $like         = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        // --------- Colunas existentes ---------
        $empHasCidade = $this->hasCol('empresas', 'cidade');
        $empHasRegiao = $this->hasCol('empresas', 'regiao');
        $ptoHasCidade = $this->hasCol('pontos_turisticos', 'cidade');
        $ptoHasRegiao = $this->hasCol('pontos_turisticos', 'regiao');

        // --------- Eventos (model/tabela) ---------
        [$eventosQ, $temModelEvento, $temTabelaEvento] = $this->resolverEventosQuery();

        // --------- KPIs ---------
        $kpis = [
            'categorias'       => Categoria::count(),
            'empresas_pub'     => Empresa::where('status','publicado')->count(),
            'pontos_pub'       => PontoTuristico::where('status','publicado')->count(),
            'eventos_totais'   => $temModelEvento
                                    ? $this->qb($eventosQ)->count()
                                    : ($temTabelaEvento ? DB::table('eventos')->count() : 0),
            'avisos_ativos'    => $this->countAtivos(Aviso::query()),
            'banners_ativos'   => $this->countAtivos(Banner::query()),
            'destaques_ativos' => $this->countAtivos(BannerDestaque::query()),
            'recs_empresas'    => EmpresaRecomendacao::count(),
            'recs_pontos'      => PontoRecomendacao::count(),
            'midias_pontos'    => PontoMidia::count(),
        ];

        // --------- Builders base ---------
        $empresasBase = Empresa::query()
            ->when($status !== 'todos', fn($q) => $q->where('status',$status))
            ->when($categoria_id>0, fn($q)=>$q->whereHas('categorias', fn($qq)=>$qq->where('categorias.id',$categoria_id)))
            ->when($cidade !== '' && $empHasCidade, fn($q)=>$q->where('cidade',$cidade))
            ->when($regiao !== '' && $empHasRegiao, fn($q)=>$q->where('regiao',$regiao))
            ->when($q !== '', fn($q1)=>$q1->where(function($w) use($q,$like){
                $w->where('nome',$like,"%{$q}%")->orWhere('descricao',$like,"%{$q}%");
            }))
            ->when($data_ini, fn($q)=>$q->whereDate('created_at','>=',$data_ini))
            ->when($data_fim, fn($q)=>$q->whereDate('created_at','<=',$data_fim));

        $pontosBase = PontoTuristico::query()
            ->when($status !== 'todos', fn($q) => $q->where('status',$status))
            ->when($categoria_id>0, fn($q)=>$q->whereHas('categorias', fn($qq)=>$qq->where('categorias.id',$categoria_id)))
            ->when($cidade !== '' && $ptoHasCidade, fn($q)=>$q->where('cidade',$cidade))
            ->when($regiao !== '' && $ptoHasRegiao, fn($q)=>$q->where('regiao',$regiao))
            ->when($q !== '', fn($q1)=>$q1->where(function($w) use($q,$like){
                $w->where('nome',$like,"%{$q}%")
                  ->orWhere('descricao',$like,"%{$q}%")
                  ->orWhere('resumo',$like,"%{$q}%");
            }))
            ->when($data_ini, fn($q)=>$q->whereDate('created_at','>=',$data_ini))
            ->when($data_fim, fn($q)=>$q->whereDate('created_at','<=',$data_fim));

        $eventosBase = $eventosQ
            ? $this->qb($eventosQ)
                ->when($data_ini, fn($q)=>$q->whereDate('created_at','>=',$data_ini))
                ->when($data_fim, fn($q)=>$q->whereDate('created_at','<=',$data_fim))
            : null;

        // --------- Quebras por status ---------
        $statusEmpresas = Empresa::select('status', DB::raw('COUNT(*) total'))
            ->groupBy('status')->pluck('total','status')->all();

        $statusPontos = PontoTuristico::select('status', DB::raw('COUNT(*) total'))
            ->groupBy('status')->pluck('total','status')->all();

        // --------- Por categoria (Top 12) ---------
        $empresasPorCategoria = Categoria::select('categorias.id','categorias.nome', DB::raw('COUNT(categoria_empresa.empresa_id) total'))
            ->leftJoin('categoria_empresa','categoria_empresa.categoria_id','=','categorias.id')
            ->leftJoin('empresas','empresas.id','=','categoria_empresa.empresa_id')
            ->when($status !== 'todos', fn($q)=>$q->where('empresas.status',$status))
            ->when($cidade !== '' && $empHasCidade, fn($q)=>$q->where('empresas.cidade',$cidade))
            ->when($regiao !== '' && $empHasRegiao, fn($q)=>$q->where('empresas.regiao',$regiao))
            ->groupBy('categorias.id','categorias.nome')
            ->orderBy('total','desc')
            ->limit(12)
            ->get();

        $pontosPorCategoria = Categoria::select('categorias.id','categorias.nome', DB::raw('COUNT(categoria_ponto_turistico.ponto_turistico_id) total'))
            ->leftJoin('categoria_ponto_turistico','categoria_ponto_turistico.categoria_id','=','categorias.id')
            ->leftJoin('pontos_turisticos','pontos_turisticos.id','=','categoria_ponto_turistico.ponto_turistico_id')
            ->when($status !== 'todos', fn($q)=>$q->where('pontos_turisticos.status',$status))
            ->when($cidade !== '' && $ptoHasCidade, fn($q)=>$q->where('pontos_turisticos.cidade',$cidade))
            ->when($regiao !== '' && $ptoHasRegiao, fn($q)=>$q->where('pontos_turisticos.regiao',$regiao))
            ->groupBy('categorias.id','categorias.nome')
            ->orderBy('total','desc')
            ->limit(12)
            ->get();

        // --------- Séries mensais ---------
        $serieEmpresas = $this->serieMensal($this->qb($empresasBase), $meses);
        $seriePontos   = $this->serieMensal($this->qb($pontosBase),   $meses);
        $serieEventos  = $eventosBase ? $this->serieMensal($this->qb($eventosBase), $meses)
                                      : $this->serieMensalNull($meses);

        // --------- Gráficos (QuickChart) ---------
        $empCatLabels = $this->wrapLabels($empresasPorCategoria->pluck('nome'), 14);
        $ptoCatLabels = $this->wrapLabels($pontosPorCategoria->pluck('nome'), 14);

        $charts = [
            'status_empresas'    => $this->qcPie('Empresas por status', $statusEmpresas),
            'status_pontos'      => $this->qcPie('Pontos por status',   $statusPontos),
            'empresas_categoria' => $this->qcBar('Empresas por categoria', $empCatLabels, $empresasPorCategoria->pluck('total')),
            'pontos_categoria'   => $this->qcBar('Pontos por categoria',   $ptoCatLabels, $pontosPorCategoria->pluck('total')),
            'evolucao_mensal'    => $this->qcLineMulti('Evolução (últimos '.$meses.' meses)',
                                       $serieEmpresas['labels'], [
                                           ['label'=>'Empresas','data'=>$serieEmpresas['data']],
                                           ['label'=>'Pontos','data'=>$seriePontos['data']],
                                           ['label'=>'Eventos','data'=>$serieEventos['data']],
                                       ]),
        ];

        // --------- Tabelas (amostra) ---------
        $empSelect = $this->pickExisting('empresas', ['id','nome','slug','status','cidade','regiao','created_at']);
        $ptoSelect = $this->pickExisting('pontos_turisticos', ['id','nome','slug','status','cidade','regiao','created_at']);
        $evtSelect = $this->pickExisting('eventos', ['id','nome','slug','created_at']);

        $tabelaEmpresas = $this->qb($empresasBase)->with('categorias:id,nome')
            ->orderBy('created_at','desc')->limit(100)->get($empSelect);

        $tabelaPontos = $this->qb($pontosBase)->with('categorias:id,nome')
            ->orderBy('created_at','desc')->limit(100)->get($ptoSelect);

        $tabelaEventos = $eventosBase
            ? $this->qb($eventosBase)->orderBy('created_at','desc')->limit(100)->get($evtSelect)
            : collect();

        // --------- View ---------
        return view('coordenador.relatorios.index', compact(
            'kpis','status','categoria_id','cidade','regiao','q','data_ini','data_fim','meses',
            'statusEmpresas','statusPontos','empresasPorCategoria','pontosPorCategoria',
            'charts','tabelaEmpresas','tabelaPontos','tabelaEventos'
        ));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $qual         = $request->input('qual','empresas'); // empresas|pontos|eventos
        $status       = $request->input('status','todos');
        $categoria_id = (int) $request->input('categoria_id', 0);
        $cidade       = trim((string) $request->input('cidade',''));
        $regiao       = trim((string) $request->input('regiao',''));
        $data_ini     = $request->date('data_inicial');
        $data_fim     = $request->date('data_final');

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="relatorio_'.$qual.'_'.now()->format('Ymd_His').'.csv"',
        ];

        return response()->stream(function() use ($qual,$status,$categoria_id,$cidade,$regiao,$data_ini,$data_fim){
            $out = fopen('php://output','w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8
            $like = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

            if ($qual === 'empresas') {
                $empHasCidade = $this->hasCol('empresas','cidade');
                $empHasRegiao = $this->hasCol('empresas','regiao');

                $rows = Empresa::query()
                    ->when($status !== 'todos', fn($q)=>$q->where('status',$status))
                    ->when($categoria_id>0, fn($q)=>$q->whereHas('categorias', fn($qq)=>$qq->where('categorias.id',$categoria_id)))
                    ->when($cidade !== '' && $empHasCidade, fn($q)=>$q->where('cidade',$cidade))
                    ->when($regiao !== '' && $empHasRegiao, fn($q)=>$q->where('regiao',$regiao))
                    ->when($data_ini, fn($q)=>$q->whereDate('created_at','>=',$data_ini))
                    ->when($data_fim, fn($q)=>$q->whereDate('created_at','<=',$data_fim))
                    ->with('categorias:id,nome')
                    ->orderBy('created_at','desc')
                    ->get($this->pickExisting('empresas',['id','nome','slug','status','cidade','regiao','created_at']));

                $header = ['id','nome','slug','status'];
                if ($empHasCidade) $header[] = 'cidade';
                if ($empHasRegiao) $header[] = 'regiao';
                $header[] = 'categorias'; $header[] = 'created_at';
                fputcsv($out, $header);

                foreach($rows as $r){
                    $linha = [$r->id, $r->nome, $r->slug, $r->status];
                    if ($empHasCidade) $linha[] = $r->cidade ?? '';
                    if ($empHasRegiao) $linha[] = $r->regiao ?? '';
                    $linha[] = $r->categorias->pluck('nome')->join('; ');
                    $linha[] = $r->created_at;
                    fputcsv($out, $linha);
                }

            } elseif ($qual === 'pontos') {
                $ptoHasCidade = $this->hasCol('pontos_turisticos','cidade');
                $ptoHasRegiao = $this->hasCol('pontos_turisticos','regiao');

                $rows = PontoTuristico::query()
                    ->when($status !== 'todos', fn($q)=>$q->where('status',$status))
                    ->when($categoria_id>0, fn($q)=>$q->whereHas('categorias', fn($qq)=>$qq->where('categorias.id',$categoria_id)))
                    ->when($cidade !== '' && $ptoHasCidade, fn($q)=>$q->where('cidade',$cidade))
                    ->when($regiao !== '' && $ptoHasRegiao, fn($q)=>$q->where('regiao',$regiao))
                    ->when($data_ini, fn($q)=>$q->whereDate('created_at','>=',$data_ini))
                    ->when($data_fim, fn($q)=>$q->whereDate('created_at','<=',$data_fim))
                    ->with('categorias:id,nome')
                    ->orderBy('created_at','desc')
                    ->get($this->pickExisting('pontos_turisticos',['id','nome','slug','status','cidade','regiao','created_at']));

                $header = ['id','nome','slug','status'];
                if ($ptoHasCidade) $header[] = 'cidade';
                if ($ptoHasRegiao) $header[] = 'regiao';
                $header[] = 'categorias'; $header[] = 'created_at';
                fputcsv($out, $header);

                foreach($rows as $r){
                    $linha = [$r->id, $r->nome, $r->slug, $r->status];
                    if ($ptoHasCidade) $linha[] = $r->cidade ?? '';
                    if ($ptoHasRegiao) $linha[] = $r->regiao ?? '';
                    $linha[] = $r->categorias->pluck('nome')->join('; ');
                    $linha[] = $r->created_at;
                    fputcsv($out, $linha);
                }

            } else { // eventos
                [$eventosQ] = $this->resolverEventosQuery();
                $rows = $eventosQ
                    ? $this->qb($eventosQ)
                        ->when($data_ini, fn($q)=>$q->whereDate('created_at','>=',$data_ini))
                        ->when($data_fim, fn($q)=>$q->whereDate('created_at','<=',$data_fim))
                        ->orderBy('created_at','desc')
                        ->get($this->pickExisting('eventos',['id','nome','slug','created_at']))
                    : collect();

                fputcsv($out, ['id','nome','slug','created_at']);
                foreach($rows as $r){
                    fputcsv($out, [
                        $r->id ?? '', $r->nome ?? '', $r->slug ?? '', $r->created_at ?? ''
                    ]);
                }
            }

            fclose($out);
        }, 200, $headers);
    }

    // ================= Helpers =================

    private function countAtivos($query): int
    {
        if (method_exists($query->getModel(), 'scopeAtivosNoMomento')) {
            return $query->ativosNoMomento()->count();
        }
        if (Schema::hasColumn($query->getModel()->getTable(), 'status')) {
            return $query->where('status','publicado')->count();
        }
        return $query->count();
    }

    /** Clona com segurança um Builder (Eloquent ou Query) */
    private function qb($builder)
    {
        return clone $builder;
    }

    /** Model/Tabela de eventos → Query “usável” */
    private function resolverEventosQuery(): array
    {
        $candidatos = [
            'App\Models\Evento\Evento',
            'App\Models\Eventos\Evento',
            'App\Models\Conteudo\Evento',
            'App\Models\Site\Evento',
            'App\Models\Evento',
        ];
        foreach ($candidatos as $cls) {
            if (class_exists($cls)) {
                return [$cls::query(), true, Schema::hasTable((new $cls)->getTable())];
            }
        }
        $temTabela = Schema::hasTable('eventos');
        return [$temTabela ? DB::table('eventos') : null, false, $temTabela];
    }

    /** Série mensal por created_at (PG/MySQL) */
    private function serieMensal($query, int $meses = 6): array
    {
        $inicio = now()->startOfMonth()->subMonths($meses-1);
        $fim    = now()->endOfMonth();
        $driver = DB::connection()->getDriverName();

        if (!$query) return $this->serieMensalNull($meses);

        if ($driver === 'pgsql') {
            $rows = $this->qb($query)->selectRaw("to_char(date_trunc('month', created_at), 'YYYY-MM') as ym, COUNT(*) total")
                ->whereBetween('created_at', [$inicio, $fim])
                ->groupBy('ym')->orderBy('ym')->pluck('total','ym')->all();
        } else {
            $rows = $this->qb($query)->selectRaw("DATE_FORMAT(DATE_FORMAT(created_at,'%Y-%m-01'), '%Y-%m') as ym, COUNT(*) total")
                ->whereBetween('created_at', [$inicio, $fim])
                ->groupBy('ym')->orderBy('ym')->pluck('total','ym')->all();
        }

        $labels = []; $data = [];
        $c = $inicio->copy();
        while ($c <= $fim) {
            $ym = $c->format('Y-m');
            $labels[] = $ym;
            $data[]   = (int)($rows[$ym] ?? 0);
            $c->addMonth();
        }
        return ['labels'=>$labels,'data'=>$data];
    }

    private function serieMensalNull(int $meses): array
    {
        $inicio = now()->startOfMonth()->subMonths($meses-1);
        $fim    = now()->endOfMonth();
        $labels = [];
        $c = $inicio->copy();
        while ($c <= $fim) {
            $labels[] = $c->format('Y-m');
            $c->addMonth();
        }
        return ['labels'=>$labels,'data'=>array_fill(0, count($labels), 0)];
    }

    /** Verificação de coluna com cache */
    private function hasCol(string $table, string $col): bool
    {
        static $cache = [];
        $key = $table.'.'.$col;
        if (!array_key_exists($key, $cache)) {
            $cache[$key] = Schema::hasColumn($table, $col);
        }
        return $cache[$key];
    }

    /** Seleciona colunas existentes para SELECT */
    private function pickExisting(string $table, array $cols): array
    {
        return array_values(array_filter($cols, fn($c) => $c && $this->hasCol($table, $c)));
    }

    /** Quebra rótulos em várias linhas para Chart.js */
    private function wrapLabels($labels, int $max = 14): array
    {
        return collect($labels)->map(function($l) use ($max) {
            $l = (string)$l;
            if (mb_strlen($l) <= $max) return $l;
            // quebra por espaços, injeta \n
            return trim(preg_replace('/(.{1,'.$max.'})(\s+|$)/u', "\$1\n", $l));
        })->toArray();
    }

    // ---------- QuickChart ----------
    private function palette(int $n): array {
        $base = ['#60a5fa','#34d399','#fbbf24','#f87171','#a78bfa','#f472b6','#22d3ee','#f59e0b','#10b981','#eab308','#fb7185','#93c5fd'];
        return array_slice(array_merge($base,$base,$base),0,$n);
    }

    private function qcBase(array $cfg, int $w=920, int $h=380): string
    {
        $defaults = [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => ['labels'=>['color'=>'#e2e8f0','font'=>['size'=>12]]],
                'title'  => ['color'=>'#e2e8f0','font'=>['size'=>14]],
            ],
            'layout' => ['padding'=>['top'=>8,'right'=>8,'bottom'=>26,'left'=>8]],
            'scales' => [
                'x' => [
                    'ticks'=>[
                        'color'=>'#94a3b8','font'=>['size'=>10],
                        'maxRotation'=>35,'minRotation'=>35,'autoSkip'=>true,'maxTicksLimit'=>12
                    ],
                    'grid'=>['color'=>'rgba(148,163,184,0.15)']
                ],
                'y' => [
                    'ticks'=>['color'=>'#94a3b8','font'=>['size'=>10]],
                    'grid'=>['color'=>'rgba(148,163,184,0.15)']
                ],
            ],
            'elements'=>['line'=>['tension'=>0.35]],
        ];
        $cfg['options'] = array_replace_recursive($defaults, $cfg['options'] ?? []);

        $payload = json_encode([
            'type'    => $cfg['type']    ?? 'bar',
            'data'    => $cfg['data']    ?? (object)[],
            'options' => $cfg['options'],
        ], JSON_UNESCAPED_UNICODE);

        $qs = http_build_query([
            'c' => $payload,
            'w' => $w, 'h' => $h,
            'format' => 'png',
            'backgroundColor' => 'transparent',
        ]);

        return "https://quickchart.io/chart?{$qs}";
    }

    private function qcPie(string $titulo, array $map, int $w=420, int $h=420): string
    {
        $labels = array_keys($map);
        $data   = array_values($map);
        $colors = $this->palette(count($labels));

        return $this->qcBase([
            'type' => 'doughnut',
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderColor' => '#0F1412',
                    'borderWidth' => 2,
                ]],
            ],
            'options' => [
                'plugins' => [
                    'legend' => ['display'=>true, 'position'=>'bottom', 'labels'=>['color'=>'#e2e8f0','boxWidth'=>12]],
                    'title'  => ['display'=>true, 'text'=>$titulo],
                ],
                'cutout' => '60%',
            ],
        ], $w, $h);
    }

    private function qcBar(string $titulo, $labels, $data, int $w=920, int $h=360): string
    {
        return $this->qcBase([
            'type' => 'bar',
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'label' => $titulo,
                    'data' => $data,
                    'backgroundColor' => '#60a5fa',
                    'borderRadius' => 6,
                    'barPercentage' => 0.7,
                    'categoryPercentage' => 0.7,
                ]],
            ],
            'options' => [
                'plugins' => [
                    'legend' => ['display'=>false],
                    'title'  => ['display'=>true, 'text'=>$titulo],
                ],
            ],
        ], $w, $h);
    }

    private function qcLineMulti(string $titulo, $labels, array $datasets, int $w=920, int $h=360): string
    {
        $palette = $this->palette(count($datasets));
        $ds = [];
        foreach ($datasets as $i => $d) {
            $ds[] = [
                'label' => $d['label'],
                'data' => $d['data'],
                'borderColor' => $palette[$i],
                'backgroundColor' => $palette[$i],
                'fill' => false,
            ];
        }

        return $this->qcBase([
            'type' => 'line',
            'data' => ['labels'=>$labels, 'datasets'=>$ds],
            'options' => [
                'plugins' => [
                    'legend' => ['display'=>true, 'position'=>'bottom'],
                    'title'  => ['display'=>true, 'text'=>$titulo],
                ],
            ],
        ], $w, $h);
    }
}
