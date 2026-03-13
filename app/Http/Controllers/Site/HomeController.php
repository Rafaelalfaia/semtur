<?php
// app/Http/Controllers/Site/HomeController.php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Carbon;

use App\Models\Catalogo\{Categoria, Empresa, PontoTuristico};
use App\Models\Conteudo\Banner;
use App\Models\Conteudo\BannerDestaque;
use App\Services\InstagramFeed;
use App\Models\Evento;
use App\Models\EventoEdicao;

class HomeController extends Controller
{

    /** ============================== INDEX ============================== */
    public function index(Request $r, InstagramFeed $ig)
    {
        $q     = trim((string) $r->input('q', ''));
        $like  = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
        $now   = now();

        /* 0) Feed do Instagram (silencioso + cache no service) */
        $instagram = $ig->getProfileFeed('https://www.instagram.com/visitaltamira/', 8);

       /* 1) Categorias (chips) — sem limitar a 10 */
        $categorias = $this->categoriaBasicaQuery()
            ->orderBy('ordem')->orderBy('nome')
            ->get(['id','nome','slug','icone_path']);


        /* 2) Recomendações (Pontos + Empresas) */

        // ---- Pontos recomendados ----
        $temColRecomendado  = Schema::hasColumn('pontos_turisticos', 'recomendado');
        $temTabelaRecPontos = Schema::hasTable('ponto_recomendacoes');

        $fkPonto = null;
        if ($temTabelaRecPontos) {
            $fkPonto = Schema::hasColumn('ponto_recomendacoes', 'ponto_turistico_id') ? 'ponto_turistico_id'
                    : (Schema::hasColumn('ponto_recomendacoes', 'ponto_id') ? 'ponto_id' : null);
        }

       $pontosRec = collect();

        if ($temTabelaRecPontos && $fkPonto) {
            $pontosRec = PontoTuristico::query()
                ->publicados()
                ->comRecomendacaoGlobalAtiva()
                ->when($q !== '', fn($qq)=>$qq->where(function($w) use($q,$like){
                    $w->where('nome',$like,"%{$q}%")
                    ->orWhere('descricao',$like,"%{$q}%");
                }))
                ->addSelect([
                    'recomendacao_ordem' => DB::table('ponto_recomendacoes as pr')
                        ->select('pr.ordem')
                        ->whereColumn("pr.$fkPonto", 'pontos_turisticos.id')
                        ->whereNull('pr.deleted_at')
                        ->whereNull('pr.categoria_id')
                        ->orderBy('pr.ordem')
                        ->limit(1),
                ])
                ->with([
                    'midias' => fn($m) => $m->orderBy('ordem')->limit(1),
                    'recomendacoes' => fn($r) => $r->whereNull('categoria_id')->ativas()->orderBy('ordem')->limit(1),
                ])
                ->orderBy('recomendacao_ordem')
                ->limit(6)
                ->get()
                ->map(function($p){
                    $img = $p->capa_url
                        ?? $p->foto_capa_url
                        ?? optional($p->midias->first())->url
                        ?? null;

                    $ordem = $p->recomendacao_ordem ?? optional($p->recomendacoes->first())->ordem ?? 999999;

                    return [
                        'id'       => $p->id,
                        'type'     => 'ponto',
                        'title'    => $p->nome,
                        'subtitle' => $p->cidade ?? 'Altamira',
                        'image'    => $img,
                        'href'     => route('site.ponto', ['ponto' => $p->id]),
                        'ordem'    => $ordem,
                    ];
                });
        }

        // ---- Empresas recomendadas ----
        $temTabelaRecEmpresas = Schema::hasTable('empresa_recomendacoes');

        $empresasRec = collect();

        if (Schema::hasTable('empresa_recomendacoes')) {
            $empresasRec = Empresa::query()
                ->publicadas()
                ->comRecomendacaoGlobalAtiva()
                ->when($q !== '', fn($qq)=>$qq->where('nome',$like,"%{$q}%"))
                ->addSelect([
                    'recomendacao_ordem' => DB::table('empresa_recomendacoes as er')
                        ->select('er.ordem')
                        ->whereColumn('er.empresa_id', 'empresas.id')
                        ->whereNull('er.deleted_at')
                        ->whereNull('er.categoria_id')
                        ->orderBy('er.ordem')
                        ->limit(1),
                ])
                ->with([
                    'recomendacoes' => fn($r) => $r->whereNull('categoria_id')->ativas()->orderBy('ordem')->limit(1),
                ])
                ->orderBy('recomendacao_ordem')
                ->limit(6)
                ->get()
                ->map(function($e){
                    $ordem = $e->recomendacao_ordem ?? optional($e->recomendacoes->first())->ordem ?? 999999;
                    $img   = $e->capa_url ?? $e->foto_capa_url ?? $e->perfil_url ?? null;

                    return [
                        'id'       => $e->id,
                        'type'     => 'empresa',
                        'title'    => $e->nome,
                        'subtitle' => $e->cidade ?? 'Altamira',
                        'image'    => $img,
                        'href'     => route('site.empresa', ['empresa' => ($e->slug ?? $e->id)]),
                        'ordem'    => $ordem,
                    ];
                });
        }

        // Mescla, ordena por prioridade e garante até 4 itens (diversificando)
        $recomendacoes = $pontosRec
        ->concat($empresasRec)
        ->sortBy(fn($item) => [
            $item['ordem'] ?? 999999,
            mb_strtolower($item['title'] ?? ''),
        ])
        ->take(4)
        ->values();

        /* 3) Pontos (lista principal) */
        $pontosDestaque = PontoTuristico::query()
            ->when(method_exists(PontoTuristico::class, 'scopePublicados'),
                fn($q)=>$q->publicados(),
                fn($q)=>$q->where('status','publicado')
            )
            ->when($q !== '', fn($qq)=>$qq->where(function($w) use($q,$like){
                $w->where('nome',$like,"%{$q}%")->orWhere('descricao',$like,"%{$q}%");
            }))
            ->with(['midias' => fn($m) => $m->orderBy('ordem')->take(1)])
            ->orderBy('ordem')->orderBy('nome')
            ->take(6)
            ->get();

        /* 4) Hotéis via categoria 'hoteis' */
        $catHoteisId = $this->categoriaBasicaQuery()
            ->where('slug','hoteis')
            ->value('id');

        $empresasHoteis = collect();
        if ($catHoteisId) {
            $empresasHoteis = Empresa::query()
                ->when(method_exists(Empresa::class, 'scopePublicadas'),
                    fn($q)=>$q->publicadas(),
                    fn($q)=>$q->where('status','publicado')
                )
                ->when($q !== '', fn($qq)=>$qq->where('nome', $like, "%{$q}%"))
                ->whereHas('categorias', fn($c)=>$c->where('categorias.id', $catHoteisId))
                ->orderBy('nome')->take(6)->get();
        }

        /* 5) Parceiros turísticos (tolerante) */
        $empresasTurismo = collect();

        $catParceirosIds = $this->categoriaBasicaQuery()
            ->where(function ($q2) use ($like) {
                $q2->where('slug', $like, '%parceir%')
                ->orWhere('nome', $like, '%parceir%');
            })
            ->pluck('id')->all();

        if (!empty($catParceirosIds)) {
            $empresasTurismo = Empresa::query()
                ->when(method_exists(Empresa::class, 'scopePublicadas'),
                    fn($q)=>$q->publicadas(),
                    fn($q)=>$q->where('status','publicado')
                )
                ->whereHas('categorias', fn($c) => $c->whereIn('categorias.id', $catParceirosIds))
                ->orderBy('nome')
                ->take(6)
                ->get();
        }

        if ($empresasTurismo->isEmpty() && Schema::hasTable('empresa_recomendacoes')) {
            $empresasTurismo = Empresa::query()
                ->when(method_exists(Empresa::class, 'scopePublicadas'),
                    fn($q)=>$q->publicadas(),
                    fn($q)=>$q->where('status','publicado')
                )
                ->whereExists(function($sb) use ($now){
                    $sb->select(DB::raw(1))
                    ->from('empresa_recomendacoes')
                    ->whereColumn('empresas.id','empresa_recomendacoes.empresa_id')
                    ->whereNull('categoria_id')
                    ->where(function($w) use ($now){
                        $w->where('ativo_forcado', true)
                            ->orWhere(function($p) use ($now){
                                $p->where(function($d) use ($now){
                                    $d->whereNull('inicio_em')->orWhere('inicio_em','<=', $now);
                                })
                                ->where(function($d) use ($now){
                                    $d->whereNull('fim_em')->orWhere('fim_em','>=', $now);
                                });
                            });
                    });
                })
                ->orderBy('nome')
                ->take(6)
                ->get();
        }

        if ($empresasTurismo->isEmpty()) {
            $empresasTurismo = Empresa::query()
                ->when(method_exists(Empresa::class, 'scopePublicadas'),
                    fn($q)=>$q->publicadas(),
                    fn($q)=>$q->where('status','publicado')
                )
                ->orderBy('ordem')->orderBy('nome')
                ->take(6)
                ->get();
        }

        /* 6A) Banners destaque (coleção para o carrossel do topo) */
        $bannersDestaque = cache()->remember('home:banners_destaque', 600, function () {
            $agora = now();
            $q = \App\Models\Conteudo\BannerDestaque::query();

            // publicado
            $q = method_exists(\App\Models\Conteudo\BannerDestaque::class, 'scopePublicados')
                ? $q->publicados()
                : $q->where('status', 'publicado');

            // janela de publicação (se as colunas existirem)
            if (\Illuminate\Support\Facades\Schema::hasColumn('banner_destaques', 'inicio_publicacao')) {
                $q->where(function ($w) use ($agora) {
                    $w->whereNull('inicio_publicacao')->orWhere('inicio_publicacao', '<=', $agora);
                });
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('banner_destaques', 'fim_publicacao')) {
                $q->where(function ($w) use ($agora) {
                    $w->whereNull('fim_publicacao')->orWhere('fim_publicacao', '>=', $agora);
                });
            }

            // ordenação
            $q = method_exists(\App\Models\Conteudo\BannerDestaque::class, 'scopeOrdenados')
                ? $q->ordenados()
                : $q->orderBy('ordem')->orderByDesc('id');

            // pegue vários (ajuste o take se quiser mais)
            return $q->take(10)->get();
        });

        /* 6B) Banners normais (coleção para carrossel normal) — opcional */
        $bannersNormais = cache()->remember('home:banners_normais', 600, function () {
            $q = \App\Models\Conteudo\Banner::query();

            // publicado
            $q = method_exists(\App\Models\Conteudo\Banner::class, 'scopePublicados')
                ? $q->publicados()
                : $q->where('status', 'publicado');

            // ordenação
            $q = method_exists(\App\Models\Conteudo\Banner::class, 'scopeOrdenado')
                ? $q->ordenado()
                : $q->orderBy('ordem')->orderByDesc('id');

            return $q->take(10)->get();
        });


        /* 6) Banner “normal” (cache) */
        $banner = cache()->remember('home:banner', 600, function () {
            $bq = Banner::query();
            $bq = method_exists(Banner::class, 'scopePublicados') ? $bq->publicados() : $bq->where('status','publicado');
            $bq = method_exists(Banner::class, 'scopeOrdenado')   ? $bq->ordenado()    : $bq->orderBy('ordem')->orderByDesc('id');
            return $bq->first();
        });

        /* 2.1) Banner TOPO (cache) */
        $bannerTopo = cache()->remember('home:banner_topo', 600, fn()=> $this->bannerTopo());


        $eventosHome = Evento::query()
        ->select('eventos.*')
        ->addSelect(['ano_max' => EventoEdicao::selectRaw('MAX(ano)')
            ->whereColumn('evento_id','eventos.id')
            ->where('status','publicado')])
        ->whereExists(function($q){
            $q->selectRaw(1)
            ->from('evento_edicoes')
            ->whereColumn('evento_edicoes.evento_id','eventos.id')
            ->where('evento_edicoes.status','publicado');
        })
        ->orderByDesc('ano_max')
        ->with(['edicoes' => fn($q)=>$q->where('status','publicado')->orderByDesc('ano')->limit(1)])
        ->limit(3)
        ->get();

        return view('site.home', [
            'categorias'         => $categorias,
            'categoriasConteudo' => $this->categoriaPublicadasOrdenadasQuery()
                ->where(function ($q1) {
                    $q1->whereHas('pontos',   fn($qq) => $this->escopoPublicados($qq))
                    ->orWhereHas('empresas', fn($qq) => $this->escopoPublicados($qq));
                })
                ->withCount([
                    'pontos as pontos_publicados_count'     => fn($q) => $this->escopoPublicados($q),
                    'empresas as empresas_publicadas_count' => fn($q) => $this->escopoPublicados($q),
                ])
                ->with([
                    'pontos'   => fn($q) => $this->escopoPublicados($q)->orderBy('ordem')->orderBy('nome')->limit(3),
                    'empresas' => fn($q) => $this->escopoPublicados($q)->orderBy('ordem')->orderBy('nome')->limit(3),
                ])
                ->get(),

            'recomendacoes'      => $recomendacoes->take(4),
            'pontosDestaque'     => $pontosDestaque,
            'empresasHoteis'     => $empresasHoteis,
            'empresasTurismo'    => $empresasTurismo,
            'q'                  => $q,
            'banner'             => $banner,
            'bannerTopo'         => $bannerTopo,
            'instagram'          => $instagram,
            'eventosHome' => $eventosHome,
            'bannersDestaque' => $bannersDestaque,
            'bannersNormais'  => $bannersNormais,

        ]);
    }


    /** ============================ EXPLORAR ============================= */
    public function explorar(Request $r)
    {
        $driver = DB::connection()->getDriverName();
        $like   = $driver === 'pgsql' ? 'ilike' : 'like';

        $catId   = $r->integer('categoria_id') ?: null;
        $catSlug = trim((string) ($r->input('categoria') ?? $r->input('cat') ?? ''));
        if (!$catId && $catSlug !== '') {
            $catId = $this->categoriaBasicaQuery()
                ->where('slug',$catSlug)->value('id');
        }

        $busca = trim((string)$r->input('busca',''));

        $pontosQ = PontoTuristico::query()
            ->where('status','publicado')
            ->with(['midias' => fn($m) => $m->orderBy('ordem')->limit(1)])
            ->when($busca !== '', fn($q)=>$q->where(function($w) use($busca,$like){
                $w->where('nome',$like,"%{$busca}%")->orWhere('descricao',$like,"%{$busca}%");
            }))
            ->when($catId, fn($q)=>$q->whereHas('categorias', fn($w)=>$w->where('categorias.id',$catId)))
            ->orderBy('ordem')->orderBy('nome');

        $pontos = $pontosQ->paginate(12)->withQueryString();

        $empresasQ = Empresa::query()
            ->where('status','publicado')
            ->when($busca !== '', fn($q)=>$q->where('nome',$like,"%{$busca}%"))
            ->when($catId, fn($q)=>$q->whereHas('categorias', fn($w)=>$w->where('categorias.id',$catId)))
            ->orderBy('ordem')->orderBy('nome');

        $empresas = $empresasQ->paginate(12, ['*'], 'empresas_page')->withQueryString();

        $categorias = $this->categoriaPublicadasOrdenadasQuery()
            ->where(function ($qq) {
                $qq->whereHas('pontos', fn($w)=>$this->escopoPublicados($w))
                   ->orWhereHas('empresas', fn($w)=>$this->escopoPublicados($w));
            })
            ->get(['id','nome','slug','icone_path']);

        $currentCat = null;
        if ($catId) {
            $currentCat = $categorias->firstWhere('id', $catId)
                ?: Categoria::find($catId);
        }

        return view('site.explorar', compact('pontos','empresas','categorias','currentCat'));
    }

    /** =========================== HELPERS ============================== */

    /** Categoria “publicada” básica (tolerante a falta de escopo). */
    private function categoriaBasicaQuery()
    {
        $q = Categoria::query();
        if (method_exists(Categoria::class, 'scopePublicadas')) {
            $q = $q->publicadas();
        } else {
            $q = $q->where('status','publicado');
        }
        return $q;
    }

    /** Categoria publicadas + ordenado (tolerante). */
    private function categoriaPublicadasOrdenadasQuery()
    {
        $q = $this->categoriaBasicaQuery();
        if (method_exists(Categoria::class, 'scopeOrdenado')) {
            $q = $q->ordenado();
        } else {
            $q = $q->orderBy('ordem')->orderBy('nome');
        }
        return $q;
    }

    /** Aplica escopo publicados() se existir, senão where status. */
    private function escopoPublicados($builder)
    {
        if (method_exists($builder->getModel(), 'scopePublicados')) {
            return $builder->publicados();
        }
        return $builder->where('status','publicado');
    }

    /** Busca o Banner do TOPO de forma tolerante aos escopos e ao schema. */
    private function bannerTopo(): ?BannerDestaque
    {
        // Se a tabela ainda não existir (ambiente novo), evita exception:
        if (!Schema::hasTable('banner_destaques')) {
            return null;
        }

        $agora = Carbon::now();
        $bd = BannerDestaque::query();

        if (method_exists(BannerDestaque::class, 'scopePublicados')) {
            $bd = $bd->publicados();
        } else {
            $bd = $bd->where('status', 'publicado');
        }

        if (method_exists(BannerDestaque::class, 'scopeAtivosAgora')) {
            $bd = $bd->ativosAgora();
        } else {
            $bd = $bd->where(function ($q) use ($agora) {
                    $q->whereNull('inicio_publicacao')->orWhere('inicio_publicacao', '<=', $agora);
                })
                ->where(function ($q) use ($agora) {
                    $q->whereNull('fim_publicacao')->orWhere('fim_publicacao', '>=', $agora);
                });
        }

        if (method_exists(BannerDestaque::class, 'scopeOrdenados')) {
            $bd = $bd->ordenados();
        } else {
            $bd = $bd->orderBy('ordem')->orderByDesc('id');
        }

        return $bd->first();
    }
}
