<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use App\Models\EventoEdicao;

class EventoPublicController extends Controller
{
    // /eventos (ver todos)
    public function index()
    {
        // pega eventos com ao menos 1 edição publicada, ordenados pelo ano mais recente
        $eventos = Evento::query()
            ->select('eventos.*')
            ->addSelect(['ano_max' => EventoEdicao::selectRaw('MAX(ano)')
                ->whereColumn('evento_id','eventos.id')
                ->where('status','publicado')])
            ->whereExists(function($q){
                $q->selectRaw(1)
                  ->from('evento_edicoes')
                  ->whereColumn('evento_edicoes.evento_id','eventos.id')
                  ->where('evento_edicoes.status','publicado');
            })
            ->orderByDesc('ano_max')
            ->with(['edicoes' => fn($q) => $q->where('status','publicado')->orderByDesc('ano')])
            ->paginate(12);

        return view('site.eventos.index', compact('eventos'));
    }

    // /eventos/{slug}/{ano?}
    public function show(string $locale, string $slug, ?string $ano = null)
    {
        $evento = Evento::where('slug',$slug)->firstOrFail();
        $ano = filled($ano) ? (int) $ano : null;

        // edição escolhida (ou última publicada)
        $edicao = $evento->edicoes()
            ->when($ano, fn($q)=>$q->where('ano',$ano))
            ->where('status','publicado')
            ->orderByDesc('ano')
            ->with(['atrativos' => fn($q)=>$q->where('status','publicado')->orderBy('ordem'),
                    'midias'    => fn($q)=>$q->orderBy('ordem')])
            ->firstOrFail();

        // anos disponíveis para o seletor
        $anos = $evento->edicoes()->where('status','publicado')->orderByDesc('ano')->pluck('ano');

        return view('site.eventos.show', compact('evento','edicao','anos'));
    }
}
