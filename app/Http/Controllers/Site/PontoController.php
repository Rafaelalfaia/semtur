<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Catalogo\PontoTuristico;

class PontoController extends Controller
{
    /**
     * Mostra o detalhe do ponto (apenas 'publicado'), aceitando slug ou id.
     */
    public function show(string $locale, string $ponto)
    {
        $query = PontoTuristico::query()
            ->where('status', 'publicado')
            ->with([
                'categorias:id,nome,slug',
                'midias' => fn ($q) => $q->orderBy('ordem')->orderBy('id'),
                'empresas' => fn ($q) => $q
                    ->where('status', 'publicado')
                    ->with('categorias:id,nome,slug')
                    ->orderBy('ordem')
                    ->orderBy('nome'),
            ]);

        $modelo = is_numeric($ponto)
            ? $query->where('id', $ponto)->firstOrFail()
            : $query->where('slug', $ponto)->firstOrFail();

        return view('site.pontos.show', [
            'ponto' => $modelo,
            'empresasRelacionadas' => $modelo->empresas,
        ]);
    }
}
