<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use App\Models\Conteudo\Aviso;

class AvisoFeedController extends Controller
{
    public function ativo()
    {
        if (!Schema::hasTable('avisos')) {
            return response()->json(['aviso' => null, 'cached' => false]);
        }

        $aviso = Cache::remember('aviso:ativo', 60, function () {
            return Aviso::publicados()->janelaAtiva()->latest('updated_at')->first();
        });

        return response()->json([
            'aviso' => $aviso ? [
                'id'        => $aviso->id,
                'titulo'    => $aviso->titulo,
                'descricao' => $aviso->descricao,
                'whatsapp'  => $aviso->whatsapp,
                'imagem'    => $aviso->imagem_url,
                'link'      => $aviso->whatsapp_link,
                'updated'   => optional($aviso->updated_at)?->toIso8601String(),
            ] : null,
            'cached' => true,
        ]);
    }
}
