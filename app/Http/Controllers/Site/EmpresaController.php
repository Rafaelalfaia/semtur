<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Catalogo\Empresa;

class EmpresaController extends Controller
{
    /**
     * Mostra o detalhe da empresa (apenas 'publicado'), aceitando slug ou id.
     */
    public function show(string $empresa)
    {
        $query = Empresa::query()
            ->where('status', 'publicado')
            ->with([
                'categorias:id,nome,slug',
            ]);

        $modelo = is_numeric($empresa)
            ? $query->where('id', $empresa)->firstOrFail()
            : $query->where('slug', $empresa)->firstOrFail();

        // View de detalhe (ajuste o caminho se o seu for diferente)
        return view('site.empresas.show', [
            'empresa' => $modelo,
        ]);
    }
}
