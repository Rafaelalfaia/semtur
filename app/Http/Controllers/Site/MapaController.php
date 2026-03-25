<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Catalogo\Categoria;
use Illuminate\Http\Request;

class MapaController extends Controller
{
    public function index(Request $request)
    {
        $categorias = Categoria::query()
            ->where('status', 'publicado')
            ->orderBy('ordem')
            ->orderBy('nome')
            ->get(['id', 'nome', 'slug', 'icone_path']);

        $categoriaAtual = null;
        $categoriaParam = trim((string) $request->input('categoria', ''));

        if ($categoriaParam !== '') {
            $categoriaAtual = is_numeric($categoriaParam)
                ? $categorias->firstWhere('id', (int) $categoriaParam)
                : $categorias->firstWhere('slug', $categoriaParam);
        }

        $queryAtual = trim((string) $request->input('q', ''));

        return view('site.mapa.index', compact('categorias', 'categoriaAtual', 'queryAtual'));
    }
}
