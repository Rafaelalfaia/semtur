<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Catalogo\PontoTuristico;

class PontoController extends Controller
{
    /**
     * Mostra o detalhe do ponto (apenas 'publicado'), aceitando slug ou id.
     */
    public function show(string $ponto)
    {
        $query = PontoTuristico::query()
            ->where('status', 'publicado')
            ->with([
                'categorias:id,nome,slug',
                'midias' => fn ($q) => $q->orderBy('ordem')->orderBy('id'),
            ]);

        $modelo = is_numeric($ponto)
            ? $query->where('id', $ponto)->firstOrFail()
            : $query->where('slug', $ponto)->firstOrFail();

        // View de detalhe (ajuste o caminho se o seu for diferente)
        return view('site.pontos.show', [
            'ponto' => $modelo,
        ]);
    }
}
