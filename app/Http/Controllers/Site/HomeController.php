<?php
// app/Http/Controllers/Site/HomeController.php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Catalogo\{Categoria, Empresa, PontoTuristico};
use App\Models\Conteudo\Banner;
use App\Models\Conteudo\BannerDestaque;
use App\Models\Conteudo\Video;
use App\Services\InstagramFeed;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HomeController extends Controller
{
    /** ============================== INDEX ============================== */
    public function index(Request $request, InstagramFeed $instagramFeed)
    {
        $context = $this->makeSearchContext($request->input('q', ''));

        return view('site.home', array_merge(
            $this->buildHomeViewData($context, $instagramFeed),
            ['q' => $context['term']]
        ));
    }

    /** ============================ EXPLORAR ============================= */
    public function explorar(Request $request)
    {
        $filters = $this->resolveExploreFilters($request);
        $categorias = $this->publishedCategoriesWithContent()
            ->get(['id', 'nome', 'slug', 'icone_path']);

        $pontos = $this->buildExplorePontosQuery($filters)->paginate(12)->withQueryString();
        $empresas = $this->buildExploreEmpresasQuery($filters)->paginate(12, ['*'], 'empresas_page')->withQueryString();

        $currentCat = null;
        if ($filters['catId']) {
            $currentCat = $categorias->firstWhere('id', $filters['catId'])
                ?: Categoria::find($filters['catId']);
        }

        return view('site.explorar', compact('pontos', 'empresas', 'categorias', 'currentCat'));
    }

    /** ======================== HOME COMPOSITION ========================= */

    private function buildHomeViewData(array $context, InstagramFeed $instagramFeed): array
    {
        return [
            'pontosDestaque' => $this->featuredPontos($context),
            'recomendacoes' => $this->homeRecommendations($context),
            'categoriasHome' => $this->homeCategoryRails(),
            'mapCategories' => $this->homeMapCategories(),
            'experienciasEntrada' => $this->entryExperiences(),
            'atalhosPremium' => $this->premiumUtilityEntries(),
            'banner' => $this->intermediateBanner(),
            'bannerIntermediario' => $this->intermediateBanner(),
            'bannerTopo' => $this->topBanner(),
            'instagram' => $this->instagramFeed($instagramFeed),
            'videosHome' => $this->homeVideos(),
            'bannersDestaque' => $this->featuredBanners(),
        ];
    }

    private function makeSearchContext(?string $term): array
    {
        $driver = DB::connection()->getDriverName();

        return [
            'term' => trim((string) $term),
            'like' => $driver === 'pgsql' ? 'ilike' : 'like',
            'now' => now(),
        ];
    }

    private function instagramFeed(InstagramFeed $instagramFeed): array
    {
        return $instagramFeed->getProfileFeed('https://www.instagram.com/visitaltamira/', 8);
    }

    private function publishedCategoriesWithContent()
    {
        return $this->categoriaPublicadasOrdenadasQuery()
            ->where(function ($query) {
                $query->whereHas('pontos', fn ($related) => $this->escopoPublicados($related))
                    ->orWhereHas('empresas', fn ($related) => $this->escopoPublicados($related));
            });
    }

    private function homeRecommendations(array $context): Collection
    {
        return $this->recommendedPontos($context)
            ->concat($this->recommendedEmpresas($context))
            ->sortBy(fn ($item) => [
                $item['ordem'] ?? 999999,
                mb_strtolower($item['title'] ?? ''),
            ])
            ->take(8)
            ->values();
    }

    private function recommendedPontos(array $context): Collection
    {
        if (!Schema::hasTable('ponto_recomendacoes')) {
            return collect();
        }

        $foreignKey = Schema::hasColumn('ponto_recomendacoes', 'ponto_turistico_id')
            ? 'ponto_turistico_id'
            : (Schema::hasColumn('ponto_recomendacoes', 'ponto_id') ? 'ponto_id' : null);

        if (!$foreignKey) {
            return collect();
        }

        return PontoTuristico::query()
            ->publicados()
            ->comRecomendacaoGlobalAtiva()
            ->when($context['term'] !== '', fn ($query) => $this->applyPontoSearch($query, $context['term'], $context['like']))
            ->addSelect([
                'recomendacao_ordem' => DB::table('ponto_recomendacoes as pr')
                    ->select('pr.ordem')
                    ->whereColumn("pr.$foreignKey", 'pontos_turisticos.id')
                    ->whereNull('pr.deleted_at')
                    ->whereNull('pr.categoria_id')
                    ->orderBy('pr.ordem')
                    ->limit(1),
            ])
            ->with([
                'midias' => fn ($query) => $query->orderBy('ordem')->limit(1),
                'recomendacoes' => fn ($query) => $query->whereNull('categoria_id')->ativas()->orderBy('ordem')->limit(1),
            ])
            ->orderBy('recomendacao_ordem')
            ->limit(8)
            ->get()
            ->map(function ($ponto) {
                $image = $ponto->capa_url
                    ?? $ponto->foto_capa_url
                    ?? optional($ponto->midias->first())->url
                    ?? null;

                return [
                    'id' => $ponto->id,
                    'type' => 'ponto',
                    'title' => $ponto->nome,
                    'subtitle' => $ponto->cidade ?? 'Altamira',
                    'image' => $image,
                    'href' => route('site.ponto', ['ponto' => $ponto->id]),
                    'badge' => 'Recomendado',
                    'ordem' => $ponto->recomendacao_ordem ?? optional($ponto->recomendacoes->first())->ordem ?? 999999,
                ];
            });
    }

    private function recommendedEmpresas(array $context): Collection
    {
        if (!Schema::hasTable('empresa_recomendacoes')) {
            return collect();
        }

        return Empresa::query()
            ->publicadas()
            ->comRecomendacaoGlobalAtiva()
            ->when($context['term'] !== '', fn ($query) => $query->where('nome', $context['like'], "%{$context['term']}%"))
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
                'recomendacoes' => fn ($query) => $query->whereNull('categoria_id')->ativas()->orderBy('ordem')->limit(1),
            ])
            ->orderBy('recomendacao_ordem')
            ->limit(8)
            ->get()
            ->map(function ($empresa) {
                return [
                    'id' => $empresa->id,
                    'type' => 'empresa',
                    'title' => $empresa->nome,
                    'subtitle' => $empresa->cidade ?? 'Altamira',
                    'image' => $empresa->capa_url ?? $empresa->foto_capa_url ?? $empresa->perfil_url ?? null,
                    'href' => route('site.empresa', ['empresa' => ($empresa->slug ?? $empresa->id)]),
                    'badge' => 'Curadoria do coordenador',
                    'ordem' => $empresa->recomendacao_ordem ?? optional($empresa->recomendacoes->first())->ordem ?? 999999,
                ];
            });
    }

    private function featuredPontos(array $context)
    {
        return $this->publishedPontosQuery()
            ->when($context['term'] !== '', fn ($query) => $this->applyPontoSearch($query, $context['term'], $context['like']))
            ->with(['midias' => fn ($query) => $query->orderBy('ordem')->take(1)])
            ->orderBy('ordem')
            ->orderBy('nome')
            ->take(6)
            ->get();
    }

    private function homeCategoryRails(): Collection
    {
        return $this->categoriaPublicadasOrdenadasQuery()
            ->whereHas('pontos', fn ($query) => $this->escopoPublicados($query))
            ->withCount([
                'pontos as pontos_publicados_count' => fn ($query) => $this->escopoPublicados($query),
            ])
            ->with([
                'pontos' => fn ($query) => $this->escopoPublicados($query)
                    ->with(['midias' => fn ($midias) => $midias->orderBy('ordem')->limit(1)])
                    ->orderBy('ordem')
                    ->orderBy('nome')
                    ->limit(8),
            ])
            ->get()
            ->filter(fn ($categoria) => $categoria->pontos->isNotEmpty())
            ->values();
    }

    private function homeMapCategories(): Collection
    {
        return $this->publishedCategoriesWithContent()
            ->get(['id', 'nome', 'slug', 'icone_path']);
    }

    private function strategicHighlights(): array
    {
        return [
            [
                'key' => 'rota_do_cacau',
                'title' => 'Rota do Cacau',
                'subtitle' => 'Experiência emblemática',
                'summary' => 'Um acesso editorial para descobrir a rota com mais identidade, memória e território.',
                'href' => route('site.rota_do_cacau.index'),
            ],
            [
                'key' => 'jogos_indigenas',
                'title' => 'Jogos Indígenas',
                'subtitle' => 'Cultura viva',
                'summary' => 'Um bloco de entrada para o conteúdo simbólico e coletivo dos jogos.',
                'href' => route('site.jogos_indigenas.index'),
            ],
            [
                'key' => 'onde_comer',
                'title' => 'Onde Comer',
                'subtitle' => 'Sabores do destino',
                'summary' => 'Acesso estratégico para planejar refeições sem transformar a home em vitrine genérica.',
                'href' => route('site.onde_comer'),
            ],
            [
                'key' => 'onde_ficar',
                'title' => 'Onde Ficar',
                'subtitle' => 'Base da viagem',
                'summary' => 'Acesso direto para organizar a estadia com mais clareza.',
                'href' => route('site.onde_ficar'),
            ],
            [
                'key' => 'museus',
                'title' => 'Museu e Teatro',
                'subtitle' => 'Patrimônio e cena',
                'summary' => 'Uma entrada cultural para aprofundar a experiência do destino.',
                'href' => route('site.museus'),
            ],
        ];
    }

    private function entryExperiences(): array
    {
        return [
            [
                'key' => 'rota_do_cacau',
                'title' => 'Rota do Cacau',
                'href' => route('site.rota_do_cacau.index'),
                'image' => asset('imagens/rota.png'),
            ],
            [
                'key' => 'jogos_indigenas',
                'title' => 'Jogos Indígenas',
                'href' => route('site.jogos_indigenas.index'),
                'image' => asset('imagens/jogos.png'),
            ],
            [
                'key' => 'museus',
                'title' => 'Museu e Teatro',
                'href' => route('site.museus'),
                'image' => asset('imagens/museu.png'),
            ],
        ];
    }

    private function premiumUtilityEntries(): array
    {
        return [
            [
                'key' => 'onde_comer',
                'title' => 'Onde Comer',
                'summary' => 'Restaurantes, sabores e paradas gastronômicas para organizar o roteiro com mais intenção.',
                'href' => route('site.onde_comer'),
                'image' => asset('imagens/comer.png'),
                'eyebrow' => 'Acesso utilitário',
                'cta' => 'Explorar sabores',
            ],
            [
                'key' => 'onde_ficar',
                'title' => 'Onde Ficar',
                'summary' => 'Hospedagens e bases de apoio para montar a estadia com clareza e conforto.',
                'href' => route('site.onde_ficar'),
                'image' => asset('imagens/ficar.png'),
                'eyebrow' => 'Planejamento da viagem',
                'cta' => 'Ver hospedagens',
            ],
            [
                'key' => 'guias',
                'title' => 'Revistas & Guias',
                'summary' => 'Materiais oficiais publicados pelo coordenador para aprofundar a descoberta antes e durante a visita.',
                'href' => route('site.guias'),
                'image' => asset('imagens/guias.png'),
                'eyebrow' => 'Conteúdo oficial',
                'cta' => 'Abrir materiais',
            ],
        ];
    }

    private function featuredBanners()
    {
        return cache()->remember('home:banners_destaque', 600, function () {
            $agora = now();
            $query = BannerDestaque::query();

            $query = method_exists(BannerDestaque::class, 'scopePublicados')
                ? $query->publicados()
                : $query->where('status', 'publicado');

            if (Schema::hasColumn('banner_destaques', 'inicio_publicacao')) {
                $query->where(function ($where) use ($agora) {
                    $where->whereNull('inicio_publicacao')->orWhere('inicio_publicacao', '<=', $agora);
                });
            }

            if (Schema::hasColumn('banner_destaques', 'fim_publicacao')) {
                $query->where(function ($where) use ($agora) {
                    $where->whereNull('fim_publicacao')->orWhere('fim_publicacao', '>=', $agora);
                });
            }

            $query = method_exists(BannerDestaque::class, 'scopeOrdenados')
                ? $query->ordenados()
                : $query->orderBy('ordem')->orderByDesc('id');

            return $query->take(10)->get();
        });
    }

    private function intermediateBanner(): ?Banner
    {
        return cache()->remember('home:banner_intermediario', 600, function () {
            $query = Banner::query();
            $query = method_exists(Banner::class, 'scopePublicados')
                ? $query->publicados()
                : $query->where('status', 'publicado');
            $query = method_exists(Banner::class, 'scopeOrdenado')
                ? $query->ordenado()
                : $query->orderBy('ordem')->orderByDesc('id');

            return $query->first();
        });
    }

    private function topBanner(): ?BannerDestaque
    {
        return cache()->remember('home:banner_topo', 600, fn () => $this->bannerTopo());
    }

    private function homeVideos()
    {
        return cache()->remember('home:videos', 600, function () {
            return Video::query()
                ->publicados()
                ->select([
                    'id',
                    'titulo',
                    'slug',
                    'descricao',
                    'capa_path',
                    'link_acesso',
                    'published_at',
                    'ordem',
                ])
                ->orderBy('ordem')
                ->orderByDesc('published_at')
                ->orderBy('titulo')
                ->take(4)
                ->get();
        });
    }

    /** =========================== EXPLORE DATA ========================= */

    private function resolveExploreFilters(Request $request): array
    {
        $context = $this->makeSearchContext($request->input('busca', ''));
        $catId = $request->integer('categoria_id') ?: null;
        $catSlug = trim((string) ($request->input('categoria') ?? $request->input('cat') ?? ''));

        if (!$catId && $catSlug !== '') {
            $catId = $this->categoriaBasicaQuery()
                ->where('slug', $catSlug)
                ->value('id');
        }

        return [
            'term' => $context['term'],
            'like' => $context['like'],
            'catId' => $catId,
            'catSlug' => $catSlug,
        ];
    }

    private function buildExplorePontosQuery(array $filters)
    {
        return $this->publishedPontosQuery()
            ->with(['midias' => fn ($query) => $query->orderBy('ordem')->limit(1)])
            ->when($filters['term'] !== '', fn ($query) => $this->applyPontoSearch($query, $filters['term'], $filters['like']))
            ->when($filters['catId'], fn ($query) => $query->whereHas('categorias', fn ($related) => $related->where('categorias.id', $filters['catId'])))
            ->orderBy('ordem')
            ->orderBy('nome');
    }

    private function buildExploreEmpresasQuery(array $filters)
    {
        return $this->publishedEmpresasQuery()
            ->when($filters['term'] !== '', fn ($query) => $query->where('nome', $filters['like'], "%{$filters['term']}%"))
            ->when($filters['catId'], fn ($query) => $query->whereHas('categorias', fn ($related) => $related->where('categorias.id', $filters['catId'])))
            ->orderBy('ordem')
            ->orderBy('nome');
    }

    private function publishedPontosQuery()
    {
        return method_exists(PontoTuristico::class, 'scopePublicados')
            ? PontoTuristico::query()->publicados()
            : PontoTuristico::query()->where('status', 'publicado');
    }

    private function publishedEmpresasQuery()
    {
        return method_exists(Empresa::class, 'scopePublicadas')
            ? Empresa::query()->publicadas()
            : Empresa::query()->where('status', 'publicado');
    }

    private function applyPontoSearch($query, string $term, string $like)
    {
        return $query->where(function ($where) use ($term, $like) {
            $where->where('nome', $like, "%{$term}%")
                ->orWhere('descricao', $like, "%{$term}%");
        });
    }

    /** =========================== HELPERS ============================== */

    /** Categoria "publicada" básica (tolerante a falta de escopo). */
    private function categoriaBasicaQuery()
    {
        $query = Categoria::query();

        if (method_exists(Categoria::class, 'scopePublicadas')) {
            return $query->publicadas();
        }

        return $query->where('status', 'publicado');
    }

    /** Categoria publicadas + ordenado (tolerante). */
    private function categoriaPublicadasOrdenadasQuery()
    {
        $query = $this->categoriaBasicaQuery();

        if (method_exists(Categoria::class, 'scopeOrdenado')) {
            return $query->ordenado();
        }

        return $query->orderBy('ordem')->orderBy('nome');
    }

    /** Aplica escopo publicados() se existir, senão where status. */
    private function escopoPublicados($builder)
    {
        if (method_exists($builder->getModel(), 'scopePublicados')) {
            return $builder->publicados();
        }

        return $builder->where('status', 'publicado');
    }

    /** Busca o Banner do TOPO de forma tolerante aos escopos e ao schema. */
    private function bannerTopo(): ?BannerDestaque
    {
        if (!Schema::hasTable('banner_destaques')) {
            return null;
        }

        $agora = Carbon::now();
        $query = BannerDestaque::query();

        if (method_exists(BannerDestaque::class, 'scopePublicados')) {
            $query = $query->publicados();
        } else {
            $query = $query->where('status', 'publicado');
        }

        if (method_exists(BannerDestaque::class, 'scopeAtivosAgora')) {
            $query = $query->ativosAgora();
        } else {
            $query = $query->where(function ($where) use ($agora) {
                $where->whereNull('inicio_publicacao')->orWhere('inicio_publicacao', '<=', $agora);
            })->where(function ($where) use ($agora) {
                $where->whereNull('fim_publicacao')->orWhere('fim_publicacao', '>=', $agora);
            });
        }

        if (method_exists(BannerDestaque::class, 'scopeOrdenados')) {
            $query = $query->ordenados();
        } else {
            $query = $query->orderBy('ordem')->orderByDesc('id');
        }

        return $query->first();
    }
}

