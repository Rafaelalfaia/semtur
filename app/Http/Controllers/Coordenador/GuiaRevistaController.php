<?php

namespace App\Http\Controllers\Coordenador;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveGuiaRevistaRequest;
use App\Models\Conteudo\GuiaRevista;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class GuiaRevistaController extends Controller
{
    public function index(Request $request)
    {
        $busca = trim((string) $request->input('busca', ''));
        $status = (string) $request->input('status', 'todos');
        $tipo = (string) $request->input('tipo', 'todos');

        $like = DB::getDriverName() === 'pgsql' ? 'ilike' : 'like';

        $materiais = GuiaRevista::query()
            ->when($status !== 'todos' && $status !== '', fn ($q) => $q->where('status', $status))
            ->when($tipo !== 'todos' && $tipo !== '', fn ($q) => $q->where('tipo', $tipo))
            ->when($busca !== '', function ($q) use ($busca, $like) {
                $q->where(function ($w) use ($busca, $like) {
                    $w->where('nome', $like, "%{$busca}%")
                        ->orWhere('descricao', $like, "%{$busca}%");
                });
            })
            ->orderBy('tipo')
            ->orderBy('ordem')
            ->orderByDesc('published_at')
            ->orderBy('nome')
            ->paginate(12)
            ->withQueryString();

        return view('coordenador.guias.index', [
            'materiais' => $materiais,
            'busca' => $busca,
            'status' => $status,
            'tipo' => $tipo,
            'tipos' => GuiaRevista::TIPOS_LABELS,
            'statuses' => GuiaRevista::STATUS,
        ]);
    }

    public function create()
    {
        return view('coordenador.guias.create', [
            'guia' => new GuiaRevista([
                'tipo' => GuiaRevista::TIPO_GUIA,
                'status' => GuiaRevista::STATUS_RASCUNHO,
                'ordem' => 0,
            ]),
            'tipos' => GuiaRevista::TIPOS_LABELS,
            'statuses' => GuiaRevista::STATUS,
        ]);
    }

    public function store(SaveGuiaRevistaRequest $request)
    {
        $guia = DB::transaction(function () use ($request) {
            return $this->persist(new GuiaRevista(), $request);
        });

        return redirect()
            ->route('coordenador.guias.edit', $guia)
            ->with('ok', 'Material criado com sucesso.');
    }

    public function edit(GuiaRevista $guia)
    {
        return view('coordenador.guias.edit', [
            'guia' => $guia,
            'tipos' => GuiaRevista::TIPOS_LABELS,
            'statuses' => GuiaRevista::STATUS,
        ]);
    }

    public function update(SaveGuiaRevistaRequest $request, GuiaRevista $guia)
    {
        DB::transaction(function () use ($request, $guia) {
            $this->persist($guia, $request);
        });

        return back()->with('ok', 'Material atualizado com sucesso.');
    }

    public function destroy(GuiaRevista $guia)
    {
        $guia->delete();

        return back()->with('ok', 'Material movido para a lixeira.');
    }

    public function publicar(GuiaRevista $guia)
    {
        $guia->update([
            'status' => GuiaRevista::STATUS_PUBLICADO,
            'published_at' => $guia->published_at ?: now(),
            'updated_by' => auth()->id(),
        ]);

        return back()->with('ok', 'Material publicado.');
    }

    public function arquivar(GuiaRevista $guia)
    {
        $guia->update([
            'status' => GuiaRevista::STATUS_ARQUIVADO,
            'published_at' => null,
            'updated_by' => auth()->id(),
        ]);

        return back()->with('ok', 'Material arquivado.');
    }

    public function rascunho(GuiaRevista $guia)
    {
        $guia->update([
            'status' => GuiaRevista::STATUS_RASCUNHO,
            'updated_by' => auth()->id(),
        ]);

        return back()->with('ok', 'Material movido para rascunho.');
    }

    private function persist(GuiaRevista $guia, SaveGuiaRevistaRequest $request): GuiaRevista
    {
        $data = $request->validated();

        $guia->fill([
            'tipo' => $data['tipo'],
            'nome' => $data['nome'],
            'slug' => $data['slug'] ?? null,
            'descricao' => $data['descricao'],
            'link_acesso' => $data['link_acesso'],
            'ordem' => $data['ordem'] ?? 0,
            'status' => $data['status'],
        ]);

        if (!$guia->exists) {
            $guia->created_by = auth()->id();
        }

        $guia->updated_by = auth()->id();

        if ($request->boolean('remover_capa') && $guia->capa_path) {
            Storage::disk('public')->delete($guia->capa_path);
            $guia->capa_path = null;
        }

        if ($request->hasFile('capa')) {
            if ($guia->capa_path) {
                Storage::disk('public')->delete($guia->capa_path);
            }

            $guia->capa_path = $request->file('capa')->store('guias/capas', 'public');
        }

        $guia->save();

        return $guia;
    }
}
