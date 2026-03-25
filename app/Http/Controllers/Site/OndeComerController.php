<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Conteudo\OndeComerPagina;

class OndeComerController extends Controller
{
    public function show()
    {
        $pagina = OndeComerPagina::query()
            ->publicados()
            ->with([
                'empresasSelecionadas' => fn ($q) => $q->orderBy('ordem')->orderBy('id'),
                'empresasSelecionadas.empresa' => fn ($q) => $q
                    ->publicadas()
                    ->with('categorias:id,nome,slug')
                    ->select([
                        'id',
                        'nome',
                        'slug',
                        'descricao',
                        'cidade',
                        'bairro',
                        'maps_url',
                        'telefone',
                        'email',
                        'site_url',
                        'foto_perfil_path',
                        'foto_capa_path',
                        'status',
                        'contatos',
                    ]),
            ])
            ->first();

        if (!$pagina) {
            $pagina = new OndeComerPagina([
                'titulo' => 'Onde comer em Altamira',
                'subtitulo' => 'Sabores locais',
                'resumo' => 'A curadoria gastronômica ainda está sendo preparada.',
                'texto_intro' => null,
                'texto_gastronomia_local' => null,
                'texto_dicas' => null,
                'status' => OndeComerPagina::STATUS_RASCUNHO,
            ]);

            $pagina->setRelation('empresasSelecionadas', collect());
        } else {
            $pagina->setRelation(
                'empresasSelecionadas',
                $pagina->empresasSelecionadas->filter(fn ($item) => $item && $item->empresa)->values()
            );
        }

        return view('site.onde-comer.show', [
            'pagina' => $pagina,
        ]);
    }
}
