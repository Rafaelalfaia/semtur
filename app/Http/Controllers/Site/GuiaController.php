<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Conteudo\GuiaRevista;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GuiaController extends Controller
{
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

        return view('site.guias.index', [
            'materiais' => $materiais,
            'q' => $q,
            'tipo' => $tipo,
            'tipos' => GuiaRevista::TIPOS_LABELS,
        ]);
    }

    public function show(string $slug)
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

        return view('site.guias.show', [
            'material' => $material,
            'relacionados' => $relacionados,
        ]);
    }
}
