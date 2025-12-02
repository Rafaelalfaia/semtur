<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Catalogo\BannerDestaque;

class BannerDestaqueFeedController extends Controller
{
    public function index()
    {
        $items = BannerDestaque::publicados()->ativosAgora()->ordenados()->get();
        return response()->json([
            'count' => $items->count(),
            'items' => $items->map(fn($b)=>[
                'id'=>$b->id,
                'titulo'=>$b->titulo,
                'subtitulo'=>$b->subtitulo,
                'link_url'=>$b->link_url,
                'target_blank'=>$b->target_blank,
                'mobile_url'=>$b->mobile_url,
                'desktop_url'=>$b->desktop_url,
                'cor_fundo'=>$b->cor_fundo,
                'overlay_opacity'=>$b->overlay_opacity,
            ]),
        ]);
    }
}
