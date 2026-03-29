<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Catalogo\{Categoria, Empresa, PontoTuristico};

class CategoriaController extends Controller
{
    public function show(Request $r, string $locale, string $slug)
    {
        $driver = DB::connection()->getDriverName();
        $like   = $driver === 'pgsql' ? 'ilike' : 'like';
        $now    = now();

        $q = trim((string) $r->input('q', ''));

        $categoria = Categoria::query()
            ->where('status','publicado')
            ->where('slug', $slug)
            ->firstOrFail();

        /* Pontos da categoria (recomendados na categoria no topo) */
        $pontosQ = PontoTuristico::query()
            ->where('status', 'publicado')
            ->whereHas('categorias', fn($qq) => $qq->where('categorias.id', $categoria->id))
            ->when($q !== '', fn($qq)=>$qq->where(function($w) use($q,$like){
                $w->where('nome',$like,"%{$q}%")->orWhere('descricao',$like,"%{$q}%");
            }))
            ->with(['midias' => fn($m) => $m->orderBy('ordem')->limit(1)]);

        if (Schema::hasTable('ponto_recomendacoes')) {
            $fk = Schema::hasColumn('ponto_recomendacoes','ponto_turistico_id') ? 'ponto_turistico_id'
                : (Schema::hasColumn('ponto_recomendacoes','ponto_id') ? 'ponto_id' : null);

            if ($fk) {
                $pontosQ->leftJoin('ponto_recomendacoes as pr', function($join) use ($fk,$categoria,$now){
                    $join->on("pr.$fk",'=','pontos_turisticos.id')
                        ->whereNull('pr.deleted_at')
                        ->whereNull('pr.deleted_at')
                        ->where('pr.categoria_id', $categoria->id)
                        ->where(function($w) use ($now){
                            $w->where('pr.ativo_forcado', true)
                            ->orWhere(function($p) use ($now){
                                $p->where(function($d) use ($now){
                                    $d->whereNull('pr.inicio_em')->orWhere('pr.inicio_em','<=',$now);
                                })->where(function($d) use ($now){
                                    $d->whereNull('pr.fim_em')->orWhere('pr.fim_em','>=',$now);
                                });
                            });
                        });
                })
                ->select('pontos_turisticos.*')
                ->orderByRaw('CASE WHEN pr.id IS NULL THEN 1 ELSE 0 END ASC')
                ->orderByRaw('COALESCE(pr.ordem, 999999) ASC');
            }
        }
        $pontos = $pontosQ->orderBy('ordem')->orderBy('nome')
            ->paginate(12)->withQueryString();

        /* Empresas da categoria (recomendadas na categoria no topo) */
        $empresasQ = Empresa::query()
            ->where('status', 'publicado')
            ->whereHas('categorias', fn($qq) => $qq->where('categorias.id', $categoria->id))
            ->when($q !== '', fn($qq)=>$qq->where('nome',$like,"%{$q}%"));

        if (Schema::hasTable('empresa_recomendacoes')) {
            $empresasQ->leftJoin('empresa_recomendacoes as er', function($join) use ($categoria,$now){
                $join->on('er.empresa_id','=','empresas.id')
                    ->whereNull('er.deleted_at')
                    ->whereNull('er.deleted_at')
                    ->where('er.categoria_id',$categoria->id)
                    ->where(function($w) use ($now){
                        $w->where('er.ativo_forcado', true)
                        ->orWhere(function($p) use ($now){
                            $p->where(function($d) use ($now){
                                $d->whereNull('er.inicio_em')->orWhere('er.inicio_em','<=',$now);
                            })->where(function($d) use ($now){
                                $d->whereNull('er.fim_em')->orWhere('er.fim_em','>=',$now);
                            });
                        });
                    });
            })
            ->select('empresas.*')
            ->orderByRaw('CASE WHEN er.id IS NULL THEN 1 ELSE 0 END ASC')
            ->orderByRaw('COALESCE(er.ordem, 999999) ASC');
        }

        $empresas = $empresasQ->orderBy('ordem')->orderBy('nome')
            ->paginate(12, ['*'], 'empresas_page')->withQueryString();

        return view('site.categorias.show', [
            'categoria' => $categoria,
            'q'         => $q,
            'pontos'    => $pontos,
            'empresas'  => $empresas,
        ]);
    }
}
