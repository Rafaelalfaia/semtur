<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Secretaria;
use App\Models\EquipeMembro;
use App\Services\ConteudoSiteResolver;

class SecretariaController extends Controller
{
    public function show()
    {
        $sec = Secretaria::publicados()
            ->orderBy('ordem')
            ->orderByDesc('published_at')
            ->first();

        if (!$sec) {
            $sec = Secretaria::makePublicFallback();
        }

        $membros = EquipeMembro::publicados()->ordenados()->get();
        $resolver = app(ConteudoSiteResolver::class);
        $heroBlock = $resolver->bloco('site.semtur', 'hero');
        $heroTranslation = $heroBlock?->getAttribute('traducao_resolvida');
        $heroMedia = $heroBlock?->midias->first();

        return view('site.semtur.show', compact('sec', 'membros', 'heroBlock', 'heroTranslation', 'heroMedia'));
    }
}
