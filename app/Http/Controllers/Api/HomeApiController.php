<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Cache, DB, Storage};
use App\Models\Catalogo\{Categoria, Empresa, PontoTuristico};
use Illuminate\Support\Str;

class HomeApiController extends Controller
{
    /** Operador LIKE compatível com Postgres */
    private function like(): string
    {
        return DB::getDriverName() === 'pgsql' ? 'ilike' : 'like';
    }

    /** GET /api/home – blocos da Home */
    public function index(Request $r)
    {
        $q     = trim((string) $r->query('q', ''));
        $limit = max(1, min((int) $r->query('limit', 6), 24));
        $like  = $this->like();
        $qh    = md5($q);

        // Categorias (chips)
        $categorias = Cache::remember('home:categorias', 600, function () {
            return Categoria::publicadas()
                ->ordenado()
                ->take(10)
                ->get(['id','nome','slug','icone_path'])
                ->map(fn($c)=>[
                    'nome'=>$c->nome,
                    'slug'=>$c->slug,
                    'icone_url'=>$c->icone_url,
                ])->values();
        });

        // Recomendações (Pontos) via relação recomendacoes
        $recomendacoes = Cache::remember("home:recomendados:pontos:q=$qh", 300, function () use ($q, $like) {
            return PontoTuristico::publicados()
                ->whereHas('recomendacoes')
                ->when($q !== '', function($qq) use ($q, $like){
                    $qq->where(function($w) use ($q, $like){
                        $w->where('nome', $like, "%{$q}%")
                          ->orWhere('descricao', $like, "%{$q}%");
                    });
                })
                ->with(['midias'=>fn($m)=>$m->orderBy('ordem')->take(1)])
                ->ordenado()->take(4)->get()
                ->map(fn($p)=>[
                    'id'=>$p->id,
                    'nome'=>$p->nome,
                    'slug'=>$p->slug ?? (string) $p->id,
                    'cidade'=>$p->cidade,
                    'capa_url'=>$p->capa_url,
                ])->values();
        });

        // Pontos (lista principal)
        $pontos = Cache::remember("home:pontos:q=$qh:l=$limit", 300, function () use ($q, $like, $limit) {
            return PontoTuristico::publicados()
                ->when($q !== '', function($qq) use ($q, $like){
                    $qq->where(function($w) use ($q, $like){
                        $w->where('nome', $like, "%{$q}%")
                          ->orWhere('descricao', $like, "%{$q}%");
                    });
                })
                ->with(['midias'=>fn($m)=>$m->orderBy('ordem')->take(1)])
                ->ordenado()->take($limit)->get()
                ->map(fn($p)=>[
                    'id'=>$p->id,
                    'nome'=>$p->nome,
                    'slug'=>$p->slug ?? (string) $p->id,
                    'cidade'=>$p->cidade,
                    'capa_url'=>$p->capa_url,
                ])->values();
        });

        // Hotéis por categoria 'hoteis'
        $hoteis = Cache::remember("home:empresas:hoteis:q=$qh:l=$limit", 300, function () use ($q, $like, $limit) {
            $catId = Categoria::publicadas()->where('slug','hoteis')->value('id');
            if (!$catId) return collect();
            return Empresa::publicadas()
                ->whereHas('categorias', fn($c)=>$c->where('categorias.id',$catId))
                ->when($q !== '', fn($qq)=>$qq->where('nome',$like,"%{$q}%"))
                ->ordenado()->take($limit)->get()
                ->map(fn($e)=>[
                    'nome'=>$e->nome,
                    'slug'=>$e->slug,
                    'cidade'=>$e->cidade,
                    'foto_capa'=>$e->foto_capa_url,
                    'foto_perfil'=>$e->foto_perfil_url,
                ])->values();
        });

        // Empresas de turismo (ex.: 'turismo' e/ou 'parceiros')
        $empTurismo = Cache::remember("home:empresas:turismo:q=$qh:l=$limit", 300, function () use ($q, $like, $limit) {
            $catIds = Categoria::publicadas()->whereIn('slug',['turismo','parceiros'])->pluck('id')->all();
            if (!$catIds) return collect();
            return Empresa::publicadas()
                ->whereHas('categorias', fn($c)=>$c->whereIn('categorias.id',$catIds))
                ->when($q !== '', fn($qq)=>$qq->where('nome',$like,"%{$q}%"))
                ->ordenado()->take($limit)->get()
                ->map(fn($e)=>[
                    'nome'=>$e->nome,
                    'slug'=>$e->slug,
                    'cidade'=>$e->cidade,
                    'foto_capa'=>$e->foto_capa_url,
                    'foto_perfil'=>$e->foto_perfil_url,
                ])->values();
        });

        return response()->json([
            'categorias'       => $categorias,
            'recomendacoes'    => $recomendacoes,
            'pontos'           => $pontos,
            'hoteis'           => $hoteis,
            'empresas_turismo' => $empTurismo,
        ]);
    }

    /** GET /api/categorias – chips do topo */
    public function categorias()
    {
        $cats = Categoria::query()
            ->where('status','publicado')
            ->orderBy('ordem')->orderBy('nome')
            ->get(['id','nome','slug','icone_path']);

        $cats->transform(function ($c) {
            $c->icone_url = $c->icone_url
                ?? ($c->icone_path ? asset('storage/'.$c->icone_path) : null);
            return $c;
        });

        return response()->json(['categorias' => $cats]);
    }


    /** GET /api/categorias/{slug}/feed – lista mista por categoria */
    public function categoriaFeed(Request $r, string $slug)
    {
        $like    = $this->like();
        $q       = trim((string) $r->query('q', ''));
        $page    = max(1, (int) $r->query('page', 1));
        $perPage = max(1, min((int) $r->query('per_page', 12), 50));

        $cat = Categoria::publicadas()->where('slug',$slug)->firstOrFail();

        $pontos = PontoTuristico::publicados()
            ->whereHas('categorias', fn($c)=>$c->where('categorias.id',$cat->id))
            ->when($q !== '', function($qq) use ($q, $like){
                $qq->where(function($w) use ($q, $like){
                    $w->where('nome',$like,"%{$q}%")->orWhere('descricao',$like,"%{$q}%");
                });
            })
            ->with(['midias'=>fn($m)=>$m->orderBy('ordem')->take(1)])
            ->ordenado()->forPage($page, $perPage)->get()
            ->map(fn($p)=>[
                'type'=>'ponto',
                'id'=>$p->id,
                'nome'=>$p->nome,
                'slug'=>$p->slug ?? (string) $p->id,
                'cidade'=>$p->cidade,
                'capa_url'=>$p->capa_url,
            ]);

        $empresas = Empresa::publicadas()
            ->whereHas('categorias', fn($c)=>$c->where('categorias.id',$cat->id))
            ->when($q !== '', fn($qq)=>$qq->where('nome',$like,"%{$q}%"))
            ->ordenado()->forPage($page, $perPage)->get()
            ->map(fn($e)=>[
                'type'=>'empresa',
                'nome'=>$e->nome,
                'slug'=>$e->slug,
                'cidade'=>$e->cidade,
                'foto_capa'=>$e->foto_capa_url,
                'foto_perfil'=>$e->foto_perfil_url,
            ]);

        $items = $pontos->merge($empresas)->values();

        return response()->json([
            'categoria' => ['nome'=>$cat->nome, 'slug'=>$cat->slug],
            'page'      => $page,
            'per_page'  => $perPage,
            'count'     => $items->count(),
            'items'     => $items,
        ]);
    }

    /** GET /api/pontos/{id} – detalhe */
    public function ponto(int $id)
    {
        $p = PontoTuristico::publicados()
            ->with([
                'midias'=>fn($m)=>$m->orderBy('ordem'),
                'categorias:id,nome,slug',
            ])->findOrFail($id);

        return response()->json([
            'id'        => $p->id,
            'nome'      => $p->nome,
            'slug'      => $p->slug ?? (string) $p->id,
            'cidade'    => $p->cidade,
            'regiao'    => $p->regiao,
            'descricao' => $p->descricao,
            'video_url' => $p->video_url,
            'lat'       => $p->lat,
            'lng'       => $p->lng,
            'categorias'=> $p->categorias->map(fn($c)=>['nome'=>$c->nome,'slug'=>$c->slug])->values(),
            'galeria'   => $p->midias->map(fn($m)=>[
                'path'=>$m->path,
                'url'=>$m->path ? Storage::url($m->path) : null,
                'alt'=>$m->alt,
            ])->values(),
        ]);
    }

    /** GET /api/empresas/{slug} – detalhe */
    public function empresa(string $slug)
    {
        $e = Empresa::publicadas()
            ->where('slug',$slug)
            ->with(['categorias:id,nome,slug'])
            ->firstOrFail();

        return response()->json([
            'nome'        => $e->nome,
            'slug'        => $e->slug,
            'cidade'      => $e->cidade,
            'regiao'      => $e->regiao,
            'descricao'   => $e->descricao,
            'contatos'    => $e->contatos,
            'lat'         => $e->lat,
            'lng'         => $e->lng,
            'foto_capa'   => $e->foto_capa_url,
            'foto_perfil' => $e->foto_perfil_url,
            'categorias'  => $e->categorias->map(fn($c)=>['nome'=>$c->nome,'slug'=>$c->slug])->values(),
        ]);
    }

    public function mapaFeed(Request $r)
{
    $q           = trim((string) $r->input('q', ''));
    $cidade      = trim((string) $r->input('cidade', ''));
    $categoriaId = $r->input('categoria_id');
    $categoria   = trim((string) $r->input('categoria', '')); // slug
    $like        = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

    // helper p/ resolver nomes de colunas de coordenadas em tempo de execução
    $coords = function (string $table) {
        $lat = Schema::hasColumn($table, 'lat')       ? 'lat'
             : (Schema::hasColumn($table, 'latitude') ? 'latitude' : null);
        $lng = Schema::hasColumn($table, 'lng')         ? 'lng'
             : (Schema::hasColumn($table, 'longitude')  ? 'longitude' : null);
        return [$lat, $lng];
    };

    [$latP, $lngP] = $coords('pontos_turisticos');
    [$latE, $lngE] = $coords('empresas');

    // filtro por categoria (id ou slug)
    $byCategoria = function ($q) use ($categoriaId, $categoria) {
        if ($categoriaId) {
            $q->whereHas('categorias', fn($c) => $c->where('categorias.id', $categoriaId));
        } elseif ($categoria !== '') {
            $q->whereHas('categorias', fn($c) => $c->where('categorias.slug', $categoria));
        }
    };

    // ---------------- Pontos ----------------
    $pontosQ = PontoTuristico::query()
        ->where('status', 'publicado')
        ->when($q      !== '', fn($w) => $w->where(function($x) use($q,$like){
            $x->where('nome', $like, "%{$q}%")->orWhere('descricao',$like,"%{$q}%");
        }))
        ->when($cidade !== '', fn($w) => $w->where('cidade', $like, "%{$cidade}%"))
        ->tap($byCategoria)
        ->with(['categorias:id,slug'])
        ->select(['id','nome','cidade']);

    if ($latP && $lngP) $pontosQ->addSelect([$latP, $lngP]);

    $pontos = $pontosQ->get()->filter(function($p) use ($latP,$lngP){
            return $latP && $lngP && !is_null($p->{$latP}) && !is_null($p->{$lngP});
        })->map(function($p) use ($latP,$lngP){
            return [
                'type'   => 'ponto',
                'id'     => $p->id,
                'title'  => $p->nome,
                'cidade' => $p->cidade,
                'lat'    => (float) $p->{$latP},
                'lng'    => (float) $p->{$lngP},
                'url'    => route('site.ponto', $p->id),
                'cats'   => [
                    'ids'   => $p->categorias->pluck('id')->all(),
                    'slugs' => $p->categorias->pluck('slug')->all(),
                ],
            ];
        });

    // ---------------- Empresas ----------------
    $empresasQ = Empresa::query()
        ->where('status', 'publicado')
        ->when($q      !== '', fn($w) => $w->where('nome', $like, "%{$q}%"))
        ->when($cidade !== '', fn($w) => $w->where('cidade', $like, "%{$cidade}%"))
        ->tap($byCategoria)
        ->with(['categorias:id,slug'])
        ->select(['id','slug','nome','cidade']);

    if ($latE && $lngE) $empresasQ->addSelect([$latE, $lngE]);

    $empresas = $empresasQ->get()->filter(function($e) use ($latE,$lngE){
            return $latE && $lngE && !is_null($e->{$latE}) && !is_null($e->{$lngE});
        })->map(function($e) use ($latE,$lngE){
            $slugOrId = $e->slug ?: $e->id;
            return [
                'type'   => 'empresa',
                'id'     => $e->id,
                'title'  => $e->nome,
                'cidade' => $e->cidade,
                'lat'    => (float) $e->{$latE},
                'lng'    => (float) $e->{$lngE},
                'url'    => route('site.empresa', $slugOrId),
                'cats'   => [
                    'ids'   => $e->categorias->pluck('id')->all(),
                    'slugs' => $e->categorias->pluck('slug')->all(),
                ],
            ];
        });

    return response()->json([
        'markers' => $pontos->concat($empresas)->values()
    ]);
}
}
