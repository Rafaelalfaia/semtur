<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveCursoAulaRequest;
use App\Models\Conteudo\Curso;
use App\Models\Conteudo\CursoAula;
use App\Models\Conteudo\CursoModulo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CursoAulaController extends Controller
{
    public function index(Curso $curso, CursoModulo $modulo): View
    {
        abort_unless($modulo->curso_id === $curso->id, 404);

        $aulas = $modulo->aulas()
            ->paginate(20)
            ->withQueryString();

        return view('admin.cursos.aulas.index', [
            'curso' => $curso,
            'modulo' => $modulo,
            'aulas' => $aulas,
            'statuses' => CursoAula::STATUS,
        ]);
    }

    public function create(Curso $curso, CursoModulo $modulo): View
    {
        abort_unless($modulo->curso_id === $curso->id, 404);

        return view('admin.cursos.aulas.create', [
            'curso' => $curso,
            'modulo' => $modulo,
            'aula' => new CursoAula([
                'status' => CursoAula::STATUS_RASCUNHO,
                'ordem' => 0,
            ]),
            'statuses' => CursoAula::STATUS,
        ]);
    }

    public function store(SaveCursoAulaRequest $request, Curso $curso, CursoModulo $modulo): RedirectResponse
    {
        abort_unless($modulo->curso_id === $curso->id, 404);

        $aula = DB::transaction(function () use ($request, $modulo) {
            return $this->persist(new CursoAula(), $request, $modulo);
        });

        return redirect()
            ->route('admin.cursos.modulos.aulas.edit', [$curso, $modulo, $aula])
            ->with('ok', 'Aula criada com sucesso.');
    }

    public function edit(Curso $curso, CursoModulo $modulo, CursoAula $aula): View
    {
        abort_unless($modulo->curso_id === $curso->id && $aula->modulo_id === $modulo->id, 404);

        return view('admin.cursos.aulas.edit', [
            'curso' => $curso,
            'modulo' => $modulo,
            'aula' => $aula,
            'statuses' => CursoAula::STATUS,
        ]);
    }

    public function update(SaveCursoAulaRequest $request, Curso $curso, CursoModulo $modulo, CursoAula $aula): RedirectResponse
    {
        abort_unless($modulo->curso_id === $curso->id && $aula->modulo_id === $modulo->id, 404);

        DB::transaction(function () use ($request, $modulo, $aula) {
            $this->persist($aula, $request, $modulo);
        });

        return back()->with('ok', 'Aula atualizada com sucesso.');
    }

    public function destroy(Curso $curso, CursoModulo $modulo, CursoAula $aula): RedirectResponse
    {
        abort_unless($modulo->curso_id === $curso->id && $aula->modulo_id === $modulo->id, 404);

        if ($aula->capa_path) {
            Storage::disk('public')->delete($aula->capa_path);
        }

        $aula->delete();

        return redirect()
            ->route('admin.cursos.modulos.aulas.index', [$curso, $modulo])
            ->with('ok', 'Aula movida para a lixeira.');
    }

    public function publicar(Curso $curso, CursoModulo $modulo, CursoAula $aula): RedirectResponse
    {
        abort_unless($modulo->curso_id === $curso->id && $aula->modulo_id === $modulo->id, 404);

        $aula->update([
            'status' => CursoAula::STATUS_PUBLICADO,
            'published_at' => $aula->published_at ?: now(),
            'updated_by' => auth()->id(),
        ]);

        return back()->with('ok', 'Aula publicada.');
    }

    public function arquivar(Curso $curso, CursoModulo $modulo, CursoAula $aula): RedirectResponse
    {
        abort_unless($modulo->curso_id === $curso->id && $aula->modulo_id === $modulo->id, 404);

        $aula->update([
            'status' => CursoAula::STATUS_ARQUIVADO,
            'published_at' => null,
            'updated_by' => auth()->id(),
        ]);

        return back()->with('ok', 'Aula arquivada.');
    }

    public function rascunho(Curso $curso, CursoModulo $modulo, CursoAula $aula): RedirectResponse
    {
        abort_unless($modulo->curso_id === $curso->id && $aula->modulo_id === $modulo->id, 404);

        $aula->update([
            'status' => CursoAula::STATUS_RASCUNHO,
            'updated_by' => auth()->id(),
        ]);

        return back()->with('ok', 'Aula movida para rascunho.');
    }

    private function persist(CursoAula $aula, SaveCursoAulaRequest $request, CursoModulo $modulo): CursoAula
    {
        $data = $request->validated();

        $aula->fill([
            'modulo_id' => $modulo->id,
            'nome' => $data['nome'],
            'slug' => $data['slug'] ?? null,
            'descricao' => $data['descricao'] ?? null,
            'link_acesso' => $data['link_acesso'],
            'ordem' => $data['ordem'] ?? 0,
            'status' => $data['status'],
        ]);

        if (! $aula->exists) {
            $aula->created_by = auth()->id();
        }

        $aula->updated_by = auth()->id();

        if ($request->boolean('remover_capa') && $aula->capa_path) {
            Storage::disk('public')->delete($aula->capa_path);
            $aula->capa_path = null;
        }

        if ($request->hasFile('capa')) {
            if ($aula->capa_path) {
                Storage::disk('public')->delete($aula->capa_path);
            }

            $aula->capa_path = $request->file('capa')->store('cursos/aulas/capas', 'public');
        }

        $aula->save();

        return $aula;
    }
}
