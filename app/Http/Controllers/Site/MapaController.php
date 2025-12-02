<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Catalogo\Categoria;

class MapaController extends Controller
{
    public function index()
    {
        // Carrega categorias publicadas para chips/filtro na página
        $categorias = Categoria::query()
            ->where('status', 'publicado')
            ->orderBy('ordem')
            ->orderBy('nome')
            ->get(['id','nome','slug','icone_path']);

        return view('site.mapa.index', compact('categorias'));
    }
}
