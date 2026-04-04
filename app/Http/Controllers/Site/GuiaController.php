<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Site\Concerns\ResolvesEditableHero;
use App\Models\Conteudo\GuiaRevista;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GuiaController extends Controller
{
    use ResolvesEditableHero;

    public function index(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $tipo = (string) $request->input('tipo', '');

        $like = DB::getDriverName() === 'pgsql' ? 'ilike' : 'like';

        $materiais = GuiaRevista::publicados()
            ->when($tipo !== '', fn ($query) => $query->where('tipo', $tipo))
            ->when($q !== '', function ($query) use ($q, $like) {
                $query->where(function ($w) use ($q, $like) {
                    $w->where('nome', $like, "%{$q}%")
                        ->orWhere('descricao', $like, "%{$q}%");
                });
            })
            ->orderBy('tipo')
            ->orderBy('ordem')
            ->orderByDesc('published_at')
            ->orderBy('nome')
            ->paginate(12)
            ->withQueryString();

        return view('site.guias.index', array_merge([
            'materiais' => $materiais,
            'q' => $q,
            'tipo' => $tipo,
            'tipos' => GuiaRevista::TIPOS_LABELS,
        ], $this->resolveEditableHero('site.guias')));
    }

    public function show(string $locale, string $slug)
    {
        $material = GuiaRevista::publicados()
            ->where('slug', $slug)
            ->firstOrFail();

        $relacionados = GuiaRevista::publicados()
            ->where('id', '<>', $material->id)
            ->where('tipo', $material->tipo)
            ->orderBy('ordem')
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        return view('site.guias.show', array_merge([
            'material' => $material,
            'relacionados' => $relacionados,
        ], $this->resolveEditableHero('site.guias.show')));
    }
}
