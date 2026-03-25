<?php

namespace App\Http\Controllers\Coordenador;

use App\Http\Controllers\Controller;
use App\Models\Catalogo\Categoria;
use App\Models\Catalogo\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

class CategoriaController extends Controller
{
    // LISTA com busca, filtro status e paginação
    public function index(Request $request)
    {
        $busca  = trim((string) $request->input('busca', ''));
        $status = $request->input('status'); // rascunho|publicado|arquivado|todos
        $buscaAtiva = mb_strlen($busca) >= 3;

        if ($buscaAtiva) {
            $q = Categoria::query()->when($status && $status !== 'todos', function($qq) use ($status) {
                $qq->where('status', $status);
            })->busca($busca)->orderBy('ordem')->orderBy('nome');
        } else {
            $q = Categoria::query()->whereRaw('1 = 0');
        }

        $categorias = $q->paginate(12)->withQueryString();

        return view('coordenador.categorias.index', [
            'categorias' => $categorias,
            'busca'      => $busca,
            'status'     => $status,
            'role'       => 'coordenador',
        ]);
    }

    public function create()
    {
        $categoria = new Categoria(['status' => Categoria::STATUS_RASCUNHO, 'ordem' => 0]);
        $empresas = Empresa::orderBy('nome')->get(['id', 'nome']);
        return view('coordenador.categorias.create', compact('categoria', 'empresas'));
    }

   protected function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = Str::slug($base);
        $original = $slug;
        $i = 2;

        do {
            $query = Categoria::where('slug', $slug)->whereNull('deleted_at');
            if ($ignoreId) {
                $query->where('id', '!=', $ignoreId);
            }
            $exists = $query->exists();
            if ($exists) {
                $slug = "{$original}-{$i}";
                $i++;
            }
        } while ($exists);

        return $slug;
    }
   public function store(Request $r)
    {
        $data = $r->validate([
            'nome'      => ['required','string','max:100'],
            'descricao' => ['nullable','string','max:1000'],
            'slug'      => ['nullable','string','max:120',
                // se reutiliza SoftDeletes, garanta unicidade só entre ativos
                Rule::unique('categorias','slug')->where(fn($q) => $q->whereNull('deleted_at')),
            ],
            'status' => ['required','in:publicado,rascunho,arquivado'],
            'ordem'  => ['nullable','integer','min:0'],
            'empresas'   => ['nullable','array'],
            'empresas.*' => ['integer','exists:empresas,id'],

            // validação do arquivo (igual ao update)
            'icone'  => ['nullable','file','mimes:png,jpg,jpeg,webp,svg','max:2048'],
        ], [
            'slug.unique'  => 'Já existe uma categoria ativa com esse slug.',
            'icone.mimes'  => 'Formatos aceitos: png, jpg, jpeg, webp, svg.',
            'icone.max'    => 'O ícone pode ter no máximo 2MB.',
        ]);

        // slug (gera se não veio) e sempre garante unicidade
        $base         = $data['slug'] ?: $data['nome'];
        $data['slug'] = $this->uniqueSlug($base);
        $data['ordem'] = $data['ordem'] ?? 0;

        // salva o arquivo do ícone (DISCO "public")
        if ($r->hasFile('icone')) {
            $data['icone_path'] = $r->file('icone')->store('categorias/icones','public');
        }

        $empresas = $data['empresas'] ?? [];
        unset($data['empresas']);

        $cat = Categoria::create($data);
        $cat->empresas()->sync($empresas);

        // 🔁 volta para o INDEX (como você quer)
        return redirect()
            ->route('coordenador.categorias.index')
            ->with('ok','Categoria criada!');
    }




    public function edit(Categoria $categoria)
    {
        $categoria->load('empresas:id,nome');
        $empresas = Empresa::orderBy('nome')->get(['id', 'nome']);
        return view('coordenador.categorias.edit', compact('categoria', 'empresas'));
    }

// ...

    public function update(Request $r, Categoria $categoria)
    {
        $data = $r->validate([
            'nome'      => ['required','string','max:120'],
            'slug'      => ['nullable','string','max:140',
                Rule::unique('categorias','slug')
                    ->ignore($categoria->id)
                    ->where(fn($q) => $q->whereNull('deleted_at')),
            ],
            'descricao' => ['nullable','string','max:5000'],
            // aceita tbm svg/webp quando enviar arquivo
            'icone'     => ['nullable','file','mimes:png,jpg,jpeg,webp,svg','max:2048'],
            'ordem'     => ['nullable','integer','min:0'],
            'status'    => ['required','in:rascunho,publicado,arquivado'],
            'remover_icone' => ['nullable','boolean'],
            'empresas'   => ['nullable','array'],
            'empresas.*' => ['integer','exists:empresas,id'],
        ]);

        $empresas = $data['empresas'] ?? [];
        unset($data['empresas']);

        // slug: mantém o digitado se único; gera a partir do nome quando vazio
        $base        = trim($data['slug'] ?? '') ?: $data['nome'];
        $data['slug'] = $this->uniqueSlug($base, $categoria->id);
        $data['ordem'] = $data['ordem'] ?? 0;

        // aplica campos comuns
        $categoria->fill(Arr::except($data, ['icone','remover_icone']));

        // remover ícone (se marcado)
        if ($r->boolean('remover_icone')) {
            if ($categoria->icone_path && Storage::disk('public')->exists($categoria->icone_path)) {
                Storage::disk('public')->delete($categoria->icone_path);
            }
            $categoria->icone_path = null;
        }

        // upload novo ícone (tem prioridade sobre o “remover”)
        if ($r->hasFile('icone')) {
            $path = $r->file('icone')->store('categorias/icones','public');
            $categoria->icone_path = $path;
        }

        $categoria->save();
        $categoria->empresas()->sync($empresas);

        // se o getRouteKeyName() usa slug, garantir URL correta após alteração
        return redirect()
            ->route('coordenador.categorias.edit', $categoria->refresh())
            ->with('ok','Categoria atualizada.');
    }




    public function destroy(Categoria $categoria)
    {
        $categoria->delete();
        return redirect()->route('coordenador.categorias.index')
            ->with('ok', 'Categoria movida para a lixeira.');
    }

    public function removerIcone(Categoria $categoria)
    {
        if ($categoria->icone_path && Storage::disk('public')->exists($categoria->icone_path)) {
            Storage::disk('public')->delete($categoria->icone_path);
        }
        $categoria->update(['icone_path' => null]);
        return back()->with('ok', 'Ícone removido.');
    }

    // Ações rápidas de status
    public function publicar(Categoria $categoria)
    {
        $categoria->update(['status' => Categoria::STATUS_PUBLICADO, 'published_at' => now()]);
        return back()->with('ok', 'Categoria publicada.');
    }
    public function arquivar(Categoria $categoria)
    {
        $categoria->update(['status' => Categoria::STATUS_ARQUIVADO, 'published_at' => null]);
        return back()->with('ok', 'Categoria arquivada.');
    }
    public function rascunho(Categoria $categoria)
    {
        $categoria->update(['status' => Categoria::STATUS_RASCUNHO, 'published_at' => null]);
        return back()->with('ok', 'Categoria marcada como rascunho.');
    }

    // ====== validação única para store/update ======
    private function validateData(Request $request, ?int $id): array
    {
        return $request->validate([
            'nome'        => ['required','string','max:120'],
            'slug'        => ['nullable','string','max:140', Rule::unique('categorias','slug')->ignore($id)],
            'descricao'   => ['nullable','string','max:5000'],
            'ordem'       => ['nullable','integer','min:0'],
            'status'      => ['required', Rule::in([Categoria::STATUS_RASCUNHO, Categoria::STATUS_PUBLICADO, Categoria::STATUS_ARQUIVADO])],
            // ícone aceitando imagens comuns + svg; limite 2MB
            'icone'       => ['nullable','file','mimes:png,jpg,jpeg,webp,svg','max:2048'],
        ], [
            'status.in'   => 'Status inválido. Use rascunho, publicado ou arquivado.',
            'icone.mimes' => 'Formatos aceitos: png, jpg, jpeg, webp, svg.',
        ]);
    }
}
