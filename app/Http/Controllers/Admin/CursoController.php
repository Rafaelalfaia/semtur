<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveCursoRequest;
use App\Models\Conteudo\Curso;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
            ->appends($request->only('q', 'status'));

        return view('admin.cursos.index', [
            'cursos' => $cursos,
            'q' => $q,
            'status' => $status,
            'statuses' => Curso::STATUS,
            'publicosAlvo' => Curso::PUBLICOS_LABELS,
        ]);
    }

    public function create(): View
    {
        return view('admin.cursos.create', [
            'curso' => new Curso([
                'status' => Curso::STATUS_RASCUNHO,
                'publico_alvo' => Curso::PUBLICO_AMBOS,
                'ordem' => 0,
            ]),
            'statuses' => Curso::STATUS,
            'publicosAlvo' => Curso::PUBLICOS_LABELS,
        ]);
    }

    public function store(SaveCursoRequest $request): RedirectResponse
    {
        $curso = DB::transaction(function () use ($request) {
            return $this->persist(new Curso(), $request);
        });

        return redirect()
            ->route('admin.cursos.edit', $curso)
            ->with('ok', 'Curso criado com sucesso.');
    }

    public function edit(Curso $curso): View
    {
        $curso->loadCount(['modulos']);

        return view('admin.cursos.edit', [
            'curso' => $curso,
            'statuses' => Curso::STATUS,
            'publicosAlvo' => Curso::PUBLICOS_LABELS,
        ]);
    }

    public function update(SaveCursoRequest $request, Curso $curso): RedirectResponse
    {
        DB::transaction(function () use ($request, $curso) {
            $this->persist($curso, $request);
        });

        return back()->with('ok', 'Curso atualizado com sucesso.');
    }

    public function destroy(Curso $curso): RedirectResponse
    {
        $curso->delete();

        return redirect()
            ->route('admin.cursos.index')
            ->with('ok', 'Curso movido para a lixeira.');
    }

    public function publicar(Curso $curso): RedirectResponse
    {
        $curso->update([
            'status' => Curso::STATUS_PUBLICADO,
            'published_at' => $curso->published_at ?: now(),
            'updated_by' => auth()->id(),
        ]);

        return back()->with('ok', 'Curso publicado.');
    }

    public function arquivar(Curso $curso): RedirectResponse
    {
        $curso->update([
            'status' => Curso::STATUS_ARQUIVADO,
            'published_at' => null,
            'updated_by' => auth()->id(),
        ]);

        return back()->with('ok', 'Curso arquivado.');
    }

    public function rascunho(Curso $curso): RedirectResponse
    {
        $curso->update([
            'status' => Curso::STATUS_RASCUNHO,
            'updated_by' => auth()->id(),
        ]);

        return back()->with('ok', 'Curso movido para rascunho.');
    }

    private function persist(Curso $curso, SaveCursoRequest $request): Curso
    {
        $data = $request->validated();

        $curso->fill([
            'nome' => $data['nome'],
            'slug' => $data['slug'] ?? null,
            'descricao_curta' => $data['descricao_curta'] ?? null,
            'publico_alvo' => $data['publico_alvo'],
            'ordem' => $data['ordem'] ?? 0,
            'status' => $data['status'],
        ]);

        if (! $curso->exists) {
            $curso->created_by = auth()->id();
        }

        $curso->updated_by = auth()->id();

        if ($request->boolean('remover_capa') && $curso->capa_path) {
            Storage::disk('public')->delete($curso->capa_path);
            $curso->capa_path = null;
        }

        if ($request->hasFile('capa')) {
            if ($curso->capa_path) {
                Storage::disk('public')->delete($curso->capa_path);
            }

            $curso->capa_path = $request->file('capa')->store('cursos/capas', 'public');
        }

        $curso->save();

        return $curso;
    }
}
