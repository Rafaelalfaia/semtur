<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Conteudo\OndeFicarPagina;

class OndeFicarController extends Controller
{
    public function show()
    {
        $pagina = OndeFicarPagina::query()
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
            $pagina = new OndeFicarPagina([
                'titulo' => 'Onde ficar em Altamira',
                'subtitulo' => 'Hospedagem e conforto',
                'resumo' => 'A curadoria de hospedagem ainda está sendo preparada.',
                'texto_intro' => null,
                'texto_hospedagem_local' => null,
                'texto_dicas' => null,
                'status' => OndeFicarPagina::STATUS_RASCUNHO,
            ]);

            $pagina->setRelation('empresasSelecionadas', collect());
        } else {
            $pagina->setRelation(
                'empresasSelecionadas',
                $pagina->empresasSelecionadas->filter(fn ($item) => $item && $item->empresa)->values()
            );
        }

        return view('site.onde-ficar.show', [
            'pagina' => $pagina,
        ]);
    }
}
