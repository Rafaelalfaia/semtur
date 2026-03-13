<?php

namespace App\Http\Controllers\Coordenador;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

// ===== Models (ajuste os namespaces se necessário) =====
use App\Models\Catalogo\Categoria;
use App\Models\Catalogo\Empresa;
use App\Models\Catalogo\PontoTuristico;
use App\Models\Catalogo\PontoMidia;
use App\Models\Conteudo\Banner;
use App\Models\Catalogo\PontoRecomendacao;
use App\Models\Catalogo\EmpresaRecomendacao;

class DashboardController extends Controller
{
    /**
     * Dashboard principal do Coordenador.
     */
    public function index(Request $request)
    {
        // Cache curto para aliviar o banco (30s)
        $ttl = now()->addSeconds(30);

        $data = Cache::remember('coord:dashboard:v2', $ttl, function () {
            return $this->buildDashboardData();
        });

        // Permite “forçar refresh” sem esperar TTL (ex.: ?refresh=1)
        if (request()->boolean('refresh')) {
            Cache::forget('coord:dashboard:v2');
            $data = $this->buildDashboardData();
        }

        return view('coordenador.dashboard', $data);
    }

    // ======================================================
    //                   BUILDERS / HELPERS
    // ======================================================

    protected function buildDashboardData(): array
    {
        return [
            'cards'      => $this->cards(),
            'charts'     => [
                'statusDistribuicao' => $this->chartStatusDistribuicao(),
                'categoriasTop'      => $this->chartTopCategorias(),
                'timelinePublicacoes'=> $this->chartTimelinePublicacoes(),
            ],
            'tables'     => [
                'recentes'           => $this->recentes(),
                'recomendadosAtivos' => $this->recomendadosAtivos(),
            ],
            'health'     => $this->healthChecks(),
            'kpis'       => $this->kpisExtras(),
        ];
    }

    // ---------- CARDS PRINCIPAIS ----------
    protected function cards(): array
    {
        return [
            'totais' => [
                'categorias' => $this->safeCountTable(fn() => Categoria::count()),
                'empresas'   => $this->safeCountTable(fn() => Empresa::count()),
                'pontos'     => $this->safeCountTable(fn() => PontoTuristico::count()),
                'banners'    => $this->safeCountTable(fn() => Banner::count()),
            ],
            'publicados' => [
                'categorias' => $this->safeCountTable(fn() => Categoria::where('status', 'publicado')->count()),
                'empresas'   => $this->safeCountTable(fn() => Empresa::where('status', 'publicado')->count()),
                'pontos'     => $this->safeCountTable(fn() => PontoTuristico::where('status', 'publicado')->count()),
            ],
            'hoje' => [
                'novos'      => $this->safeCountTable(fn() => $this->countCreatedToday()),
                'publicados' => $this->safeCountTable(fn() => $this->countPublishedToday()),
            ],
        ];
    }

    protected function countCreatedToday(): int
    {
        $sum = 0;
        $sum += $this->existsTable('categorias')       ? Categoria::whereDate('created_at', today())->count() : 0;
        $sum += $this->existsTable('empresas')         ? Empresa::whereDate('created_at', today())->count() : 0;
        $sum += $this->existsTable('pontos_turisticos')? PontoTuristico::whereDate('created_at', today())->count() : 0;
        $sum += $this->existsTable('banners')          ? Banner::whereDate('created_at', today())->count() : 0;
        return $sum;
    }

    protected function countPublishedToday(): int
    {
        $sum = 0;
        $sum += $this->existsTable('categorias')        ? Categoria::whereNotNull('published_at')->whereDate('published_at', today())->count() : 0;
        $sum += $this->existsTable('empresas')          ? Empresa::whereNotNull('published_at')->whereDate('published_at', today())->count() : 0;
        $sum += $this->existsTable('pontos_turisticos') ? PontoTuristico::whereNotNull('published_at')->whereDate('published_at', today())->count() : 0;
        return $sum;
    }

    // ---------- CHART: DISTRIBUIÇÃO POR STATUS ----------
    protected function chartStatusDistribuicao(): array
    {
        $labels = ['rascunho', 'publicado', 'arquivado'];

        $categorias = $this->existsTable('categorias')
            ? $this->groupStatusCounts(Categoria::query()) : array_fill_keys($labels, 0);

        $empresas = $this->existsTable('empresas')
            ? $this->groupStatusCounts(Empresa::query()) : array_fill_keys($labels, 0);

        $pontos   = $this->existsTable('pontos_turisticos')
            ? $this->groupStatusCounts(PontoTuristico::query()) : array_fill_keys($labels, 0);

        return [
            'labels'   => $labels,
            'datasets' => [
                ['label' => 'Categorias', 'data' => array_values($categorias)],
                ['label' => 'Empresas',   'data' => array_values($empresas)],
                ['label' => 'Pontos',     'data' => array_values($pontos)],
            ],
        ];
    }

    protected function groupStatusCounts($query): array
    {
        $labels = ['rascunho', 'publicado', 'arquivado'];
        $data   = array_fill_keys($labels, 0);

        $rows = $query->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')->get();

        foreach ($rows as $r) {
            $k = $r->status;
            if (array_key_exists($k, $data)) {
                $data[$k] = (int)$r->total;
            }
        }
        return $data;
    }

    // ---------- CHART: TOP CATEGORIAS (por conteúdo publicado) ----------
    protected function chartTopCategorias(): array
    {
        if (!$this->existsTable('categorias')) {
            return ['labels' => [], 'datasets' => [['label' => 'Itens', 'data' => []]]];
        }

    // 1) Base com os counts como ALIASES (empresas_publicadas_count, pontos_publicados_count)
        $base = \App\Models\Catalogo\Categoria::query()
            ->select('categorias.id', 'categorias.nome')
            ->withCount([
                // Qualifique a coluna status para evitar ambiguidade:
                'empresas as empresas_publicadas_count' => function ($q) {
                    $q->where('empresas.status', 'publicado');
                },
                'pontos as pontos_publicados_count' => function ($q) {
                    $q->where('pontos_turisticos.status', 'publicado');
                },
            ]);

        // 2) Encapar em uma subquery e só então somar/ordenar pelos aliases
        $rows = \Illuminate\Support\Facades\DB::query()
            ->fromSub($base, 'c')
            ->selectRaw('c.id, c.nome, (COALESCE(c.empresas_publicadas_count,0) + COALESCE(c.pontos_publicados_count,0)) AS itens_publicados')
            ->orderByDesc('itens_publicados')
            ->limit(8)
            ->get();

        $labels = $rows->pluck('nome')->all();
        $data   = $rows->pluck('itens_publicados')->map(fn($v) => (int) $v)->all();

        return [
            'labels'   => $labels,
            'datasets' => [
                ['label' => 'Itens publicados', 'data' => $data],
            ],
        ];
    }



    // ---------- CHART: TIMELINE DE PUBLICAÇÕES (últimos 30 dias) ----------
    protected function chartTimelinePublicacoes(): array
    {
        $days = collect(range(29, 0))->map(fn($i) => today()->subDays($i));
        $labels = $days->map->format('d/m')->all();

        $series = [
            'Categorias' => $this->existsTable('categorias') ? $this->seriesDates(Categoria::query(), $days) : array_fill(0, 30, 0),
            'Empresas'   => $this->existsTable('empresas') ? $this->seriesDates(Empresa::query(), $days) : array_fill(0, 30, 0),
            'Pontos'     => $this->existsTable('pontos_turisticos') ? $this->seriesDates(PontoTuristico::query(), $days) : array_fill(0, 30, 0),
        ];

        return [
            'labels'   => $labels,
            'datasets' => collect($series)->map(fn($data, $label) => [
                'label' => $label, 'data' => $data
            ])->values()->all(),
        ];
    }

    protected function seriesDates($query, $days): array
    {
        $rows = $query->whereNotNull('published_at')
            ->whereBetween('published_at', [today()->subDays(29)->startOfDay(), today()->endOfDay()])
            ->select(DB::raw('DATE(published_at) as d'), DB::raw('COUNT(*) as t'))
            ->groupBy(DB::raw('DATE(published_at)'))
            ->pluck('t', 'd');

        return $days->map(function ($day) use ($rows) {
            $key = $day->toDateString();
            return (int) ($rows[$key] ?? 0);
        })->all();
    }

    // ---------- TABELAS: RECENTES & RECOMENDADOS ATIVOS ----------
    protected function recentes(): array
    {
        return [
            'categorias' => $this->existsTable('categorias')
                ? Categoria::latest('created_at')->limit(6)->get(['id','nome','status','created_at'])
                : collect(),
            'empresas'   => $this->existsTable('empresas')
                ? Empresa::latest('created_at')->limit(6)->get(['id','nome','status','created_at'])
                : collect(),
            'pontos'     => $this->existsTable('pontos_turisticos')
                ? PontoTuristico::latest('created_at')->limit(6)->get(['id','nome','status','created_at'])
                : collect(),
        ];
    }

    protected function recomendadosAtivos(): array
    {
        $resp = [
            'pontos'   => collect(),
            'empresas' => collect(),
        ];

        if ($this->existsTable('ponto_recomendacoes')) {
            $resp['pontos'] = PontoRecomendacao::query()
                ->with(['ponto:id,nome,status'])
                ->where(function ($q) {
                    $q->where('ativo_forcado', true)
                      ->orWhere(function ($w) {
                          $w->where(function ($d) {
                              $d->whereNull('inicio_em')->orWhere('inicio_em', '<=', now());
                          })->where(function ($d) {
                              $d->whereNull('fim_em')->orWhere('fim_em', '>=', now());
                          });
                      });
                })
                ->latest('updated_at')
                ->limit(8)
                ->get(['id','ponto_turistico_id','categoria_id','ativo_forcado','inicio_em','fim_em','updated_at']);
        }

        if ($this->existsTable('empresa_recomendacoes')) {
            $resp['empresas'] = EmpresaRecomendacao::query()
                ->with(['empresa:id,nome,status'])
                ->where(function ($q) {
                    $q->where('ativo_forcado', true)
                      ->orWhere(function ($w) {
                          $w->where(function ($d) {
                              $d->whereNull('inicio_em')->orWhere('inicio_em', '<=', now());
                          })->where(function ($d) {
                              $d->whereNull('fim_em')->orWhere('fim_em', '>=', now());
                          });
                      });
                })
                ->latest('updated_at')
                ->limit(8)
                ->get(['id','empresa_id','categoria_id','ativo_forcado','inicio_em','fim_em','updated_at']);
        }

        return $resp;
    }

    // ---------- HEALTH CHECKS ----------
    protected function healthChecks(): array
    {
        // Cobertura de mapa (lat/lng preenchidos) e “mídia de capa” presente
        $mapEmpresas = $this->existsTable('empresas')
            ? $this->coverage(Empresa::query(), ['lat', 'lng'])
            : ['total' => 0, 'completo' => 0, 'percent' => 0];

        $mapPontos = $this->existsTable('pontos_turisticos')
            ? $this->coverage(PontoTuristico::query(), ['lat', 'lng'])
            : ['total' => 0, 'completo' => 0, 'percent' => 0];

        $capaPontos = $this->existsTable('pontos_turisticos')
            ? $this->coverage(PontoTuristico::query(), ['capa_path'])
            : ['total' => 0, 'completo' => 0, 'percent' => 0];

        // Quantidade média de mídias por ponto (PG-safe)
        $midiasPorPonto = 0;
        if ($this->existsTable('ponto_midias')) {
            // Se o model PontoMidia usa SoftDeletes, estes counts já ignoram deletados
            $totalMidias = \App\Models\Catalogo\PontoMidia::query()->count();
            $grupos      = \App\Models\Catalogo\PontoMidia::query()
                            ->distinct('ponto_turistico_id')
                            ->count('ponto_turistico_id');

            $midiasPorPonto = $grupos > 0 ? round($totalMidias / $grupos, 1) : 0.0;
        }


        return [
            'mapa' => [
                'empresas' => $mapEmpresas,
                'pontos'   => $mapPontos,
            ],
            'capas' => [
                'pontos'   => $capaPontos,
            ],
            'midia' => [
                'media_midias_por_ponto' => $midiasPorPonto,
            ],
        ];
    }

    protected function coverage($query, array $fields): array
    {
        $total = (int) $query->count();
        if ($total === 0) {
            return ['total' => 0, 'completo' => 0, 'percent' => 0];
        }

        $q = clone $query;
        foreach ($fields as $f) {
            $q->whereNotNull($f);
            // Para strings (ex.: capa_path) evitar vazios
            $q->when($this->isStringColumn($query, $f), fn($w) => $w->where($f, '<>', ''));
        }
        $completo = (int) $q->count();

        return [
            'total'   => $total,
            'completo'=> $completo,
            'percent' => (int) round(($completo / max(1, $total)) * 100),
        ];
    }

    protected function isStringColumn($query, string $column): bool
    {
        try {
            $table = $query->getModel()->getTable();
            if (!Schema::hasTable($table)) return false;
            $cols = DB::getSchemaBuilder()->getColumnType($table, $column);
            return in_array($cols, ['string','text']);
        } catch (\Throwable $e) {
            return false;
        }
    }

    // ---------- KPIs EXTRAS ----------
    protected function kpisExtras(): array
    {
        // Empresas e pontos com recomendações ativas
        $empresasDest  = $this->existsTable('empresa_recomendacoes')
            ? (int) EmpresaRecomendacao::query()->where(function ($q) {
                $q->where('ativo_forcado', true)
                  ->orWhere(function ($w) {
                      $w->where(function ($d) {
                          $d->whereNull('inicio_em')->orWhere('inicio_em', '<=', now());
                      })->where(function ($d) {
                          $d->whereNull('fim_em')->orWhere('fim_em', '>=', now());
                      });
                  });
            })->distinct('empresa_id')->count('empresa_id') : 0;

        $pontosDest    = $this->existsTable('ponto_recomendacoes')
            ? (int) PontoRecomendacao::query()->where(function ($q) {
                $q->where('ativo_forcado', true)
                  ->orWhere(function ($w) {
                      $w->where(function ($d) {
                          $d->whereNull('inicio_em')->orWhere('inicio_em', '<=', now());
                      })->where(function ($d) {
                          $d->whereNull('fim_em')->orWhere('fim_em', '>=', now());
                      });
                  });
            })->distinct('ponto_turistico_id')->count('ponto_turistico_id') : 0;

        return [
            'recomendados' => [
                'empresas' => $empresasDest,
                'pontos'   => $pontosDest,
            ],
        ];
    }

    // ======================================================
    //                 UTILITÁRIOS DE SEGURANÇA
    // ======================================================

    protected function safeCountTable(\Closure $fn): int
    {
        try {
            return (int) $fn();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    protected function existsTable(string $table): bool
    {
        try {
            return Schema::hasTable($table);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
