<?php

namespace App\Http\Controllers\Coordenador;

use App\Http\Controllers\Controller;
use App\Models\Conteudo\Curso;
use App\Models\Conteudo\CursoAula;
use App\Models\Conteudo\CursoModulo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CursoController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->input('q', ''));
        $status = (string) $request->input('status', 'todos');
        $like = DB::getDriverName() === 'pgsql' ? 'ilike' : 'like';

        $cursos = Curso::query()
            ->when($status !== 'todos' && $status !== '', fn ($query) => $query->where('status', $status))
            ->when(auth()->user()?->hasRole('Coordenador'), function ($query) {
                $query->whereIn('publico_alvo', [Curso::PUBLICO_COORDENADOR, Curso::PUBLICO_AMBOS]);
            })
            ->when(auth()->user()?->hasRole('Tecnico'), function ($query) {
                $query->whereIn('publico_alvo', [Curso::PUBLICO_TECNICO, Curso::PUBLICO_AMBOS]);
            })
            ->when($q !== '', function ($query) use ($q, $like) {
                $query->where(function ($inner) use ($q, $like) {
                    $inner->where('nome', $like, "%{$q}%")
                        ->orWhere('descricao_curta', $like, "%{$q}%");
                });
            })
            ->withCount(['modulos'])
            ->orderBy('ordem')
            ->orderByDesc('published_at')
            ->orderBy('nome')
            ->paginate(12)
            ->withQueryString();

        return view('coordenador.cursos.index', [
            'cursos' => $cursos,
            'q' => $q,
            'status' => $status,
            'statuses' => Curso::STATUS,
        ]);
    }

    public function show(Curso $curso): View
    {
        $this->guardAudience($curso);

        $curso->load([
            'modulos' => fn ($query) => $query->withCount('aulas'),
        ]);

        return view('coordenador.cursos.show', ['curso' => $curso]);
    }

    public function showModulo(Curso $curso, CursoModulo $modulo): View
    {
        $this->guardAudience($curso);
        abort_unless($modulo->curso_id === $curso->id, 404);

        $modulo->load([
            'aulas' => fn ($query) => $query->orderBy('ordem')->orderByDesc('published_at')->orderBy('nome'),
        ]);

        return view('coordenador.cursos.modulo-show', [
            'curso' => $curso,
            'modulo' => $modulo,
        ]);
    }

    public function showAula(Curso $curso, CursoModulo $modulo, CursoAula $aula): View
    {
        $this->guardAudience($curso);
        abort_unless($modulo->curso_id === $curso->id && $aula->modulo_id === $modulo->id, 404);

        return view('coordenador.cursos.aula-show', [
            'curso' => $curso,
            'modulo' => $modulo,
            'aula' => $aula,
        ]);
    }

    private function guardAudience(Curso $curso): void
    {
        $user = auth()->user();

        if (! $user) {
            abort(403);
        }

        if ($user->hasRole('Coordenador')) {
            abort_unless(in_array($curso->publico_alvo, [Curso::PUBLICO_COORDENADOR, Curso::PUBLICO_AMBOS], true), 403);
            return;
        }

        if ($user->hasRole('Tecnico')) {
            abort_unless(in_array($curso->publico_alvo, [Curso::PUBLICO_TECNICO, Curso::PUBLICO_AMBOS], true), 403);
        }
    }
}
