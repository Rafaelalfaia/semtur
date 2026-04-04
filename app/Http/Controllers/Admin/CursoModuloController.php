<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveCursoModuloRequest;
use App\Models\Conteudo\Curso;
use App\Models\Conteudo\CursoModulo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CursoModuloController extends Controller
{
    public function index(Curso $curso): View
    {
        $modulos = $curso->modulos()
            ->withCount(['aulas'])
            ->paginate(20)
            ->withQueryString();

        return view('admin.cursos.modulos.index', [
            'curso' => $curso,
            'modulos' => $modulos,
            'statuses' => CursoModulo::STATUS,
        ]);
    }

    public function create(Curso $curso): View
    {
        return view('admin.cursos.modulos.create', [
            'curso' => $curso,
            'modulo' => new CursoModulo([
                'status' => CursoModulo::STATUS_RASCUNHO,
                'ordem' => 0,
            ]),
            'statuses' => CursoModulo::STATUS,
        ]);
    }

    public function store(SaveCursoModuloRequest $request, Curso $curso): RedirectResponse
    {
        $modulo = DB::transaction(function () use ($request, $curso) {
            return $this->persist(new CursoModulo(), $request, $curso);
        });

        return redirect()
            ->route('admin.cursos.modulos.edit', [$curso, $modulo])
            ->with('ok', 'Módulo criado com sucesso.');
    }

    public function edit(Curso $curso, CursoModulo $modulo): View
    {
        abort_unless($modulo->curso_id === $curso->id, 404);

        return view('admin.cursos.modulos.edit', [
            'curso' => $curso,
            'modulo' => $modulo->loadCount(['aulas']),
            'statuses' => CursoModulo::STATUS,
        ]);
    }

    public function update(SaveCursoModuloRequest $request, Curso $curso, CursoModulo $modulo): RedirectResponse
    {
        abort_unless($modulo->curso_id === $curso->id, 404);

        DB::transaction(function () use ($request, $curso, $modulo) {
            $this->persist($modulo, $request, $curso);
        });

        return back()->with('ok', 'Módulo atualizado com sucesso.');
    }

    public function destroy(Curso $curso, CursoModulo $modulo): RedirectResponse
    {
        abort_unless($modulo->curso_id === $curso->id, 404);

        if ($modulo->capa_path) {
            Storage::disk('public')->delete($modulo->capa_path);
        }

        $modulo->delete();

        return redirect()
            ->route('admin.cursos.modulos.index', $curso)
            ->with('ok', 'Módulo movido para a lixeira.');
    }

    public function publicar(Curso $curso, CursoModulo $modulo): RedirectResponse
    {
        abort_unless($modulo->curso_id === $curso->id, 404);

        $modulo->update([
            'status' => CursoModulo::STATUS_PUBLICADO,
            'published_at' => $modulo->published_at ?: now(),
            'updated_by' => auth()->id(),
        ]);

        return back()->with('ok', 'Módulo publicado.');
    }

    public function arquivar(Curso $curso, CursoModulo $modulo): RedirectResponse
    {
        abort_unless($modulo->curso_id === $curso->id, 404);

        $modulo->update([
            'status' => CursoModulo::STATUS_ARQUIVADO,
            'published_at' => null,
            'updated_by' => auth()->id(),
        ]);

        return back()->with('ok', 'Módulo arquivado.');
    }

    public function rascunho(Curso $curso, CursoModulo $modulo): RedirectResponse
    {
        abort_unless($modulo->curso_id === $curso->id, 404);

        $modulo->update([
            'status' => CursoModulo::STATUS_RASCUNHO,
            'updated_by' => auth()->id(),
        ]);

        return back()->with('ok', 'Módulo movido para rascunho.');
    }

    private function persist(CursoModulo $modulo, SaveCursoModuloRequest $request, Curso $curso): CursoModulo
    {
        $data = $request->validated();

        $modulo->fill([
            'curso_id' => $curso->id,
            'nome' => $data['nome'],
            'slug' => $data['slug'] ?? null,
            'descricao_curta' => $data['descricao_curta'] ?? null,
            'ordem' => $data['ordem'] ?? 0,
            'status' => $data['status'],
        ]);

        if (! $modulo->exists) {
            $modulo->created_by = auth()->id();
        }

        $modulo->updated_by = auth()->id();

        if ($request->boolean('remover_capa') && $modulo->capa_path) {
            Storage::disk('public')->delete($modulo->capa_path);
            $modulo->capa_path = null;
        }

        if ($request->hasFile('capa')) {
            if ($modulo->capa_path) {
                Storage::disk('public')->delete($modulo->capa_path);
            }

            $modulo->capa_path = $request->file('capa')->store('cursos/modulos/capas', 'public');
        }

        $modulo->save();

        return $modulo;
    }
}
