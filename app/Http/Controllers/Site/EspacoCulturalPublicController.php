<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Catalogo\EspacoCultural;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EspacoCulturalPublicController extends Controller
{
    private array $diasSemana = [
        0 => 'Domingo',
        1 => 'Segunda-feira',
        2 => 'Terça-feira',
        3 => 'Quarta-feira',
        4 => 'Quinta-feira',
        5 => 'Sexta-feira',
        6 => 'Sábado',
    ];

    public function index(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $tipo = (string) $request->input('tipo', 'todos');

        $like = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        $espacos = EspacoCultural::query()
            ->with([
                'midias',
                'horarios' => fn ($q) => $q->ativos()->orderBy('dia_semana')->orderBy('hora_inicio'),
            ])
            ->publicados()
            ->when(in_array($tipo, EspacoCultural::TIPOS, true), fn ($query) => $query->where('tipo', $tipo))
            ->when($q !== '', function ($query) use ($q, $like) {
                $query->where(function ($w) use ($q, $like) {
                    $w->where('nome', $like, "%{$q}%")
                        ->orWhere('resumo', $like, "%{$q}%")
                        ->orWhere('descricao', $like, "%{$q}%")
                        ->orWhere('bairro', $like, "%{$q}%")
                        ->orWhere('cidade', $like, "%{$q}%");
                });
            })
            ->ordenados()
            ->paginate(12)
            ->withQueryString();

        $destaques = EspacoCultural::query()
            ->with('midias')
            ->publicados()
            ->ordenados()
            ->limit(3)
            ->get();

        return view('site.espacos_culturais.index', [
            'espacos' => $espacos,
            'destaques' => $destaques,
            'q' => $q,
            'tipo' => $tipo,
            'diasSemana' => $this->diasSemana,
        ]);
    }

    public function show(string $slug)
    {
        $espaco = EspacoCultural::query()
            ->with([
                'midias',
                'horarios' => fn ($q) => $q->ativos()->orderBy('dia_semana')->orderBy('hora_inicio'),
            ])
            ->publicados()
            ->where('slug', $slug)
            ->firstOrFail();

        $relacionados = EspacoCultural::query()
            ->with('midias')
            ->publicados()
            ->where('id', '<>', $espaco->id)
            ->where('tipo', $espaco->tipo)
            ->ordenados()
            ->limit(3)
            ->get();

        return view('site.espacos_culturais.show', [
            'espaco' => $espaco,
            'relacionados' => $relacionados,
            'diasSemana' => $this->diasSemana,
        ]);
    }
}
