<?php

namespace App\Http\Controllers\Coordenador;

use App\Http\Controllers\Controller;
use App\Models\EquipeMembro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class EquipeMembroController extends Controller
{
    public function index(Request $r)
    {
        $q = trim((string)$r->input('q',''));
        $itens = EquipeMembro::query()
            ->when($q !== '', fn($w)=>$w->where(function($s) use($q){
                $s->where('nome','like',"%{$q}%")
                  ->orWhere('cargo','like',"%{$q}%");
            }))
            ->ordenados()
            ->paginate(20);

        return view('coordenador.equipe.index', compact('itens','q'));
    }

    public function create()
    {
        return view('coordenador.equipe.create');
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'nome'   => ['required','string','max:160'],
            'slug'   => ['nullable','string','max:180'],
            'cargo'  => ['nullable','string','max:160'],
            'resumo' => ['nullable','string','max:280'],
            'redes'  => ['nullable','array'],
            'redes.*'=> ['nullable','string','max:255'],
            'status' => ['required','in:rascunho,publicado,arquivado'],
            'ordem'  => ['nullable','integer','min:0'],
            'foto'   => ['nullable','image','max:4096'],
        ]);

        if ($r->hasFile('foto')) {
            $data['foto_path'] = $r->file('foto')->store('equipe/fotos','public');
        }

        // slug único
        $base = $data['slug'] ?: Str::slug($data['nome']);
        $data['slug'] = $this->makeUniqueSlug($base);

        // ordem default
        $data['ordem'] = ($data['ordem'] === null || $data['ordem'] === '') ? 0 : (int)$data['ordem'];

        $data['created_by'] = Auth::id();

        $m = EquipeMembro::create($data);

        return redirect()->route('coordenador.equipe.index')->with('ok','Membro criado.');
    }

    public function edit(EquipeMembro $equipe)
    {
        // Corrige: abrir a tela de edição com a variável 'membro'
        return view('coordenador.equipe.edit', ['membro' => $equipe]);
    }

    public function update(Request $r, EquipeMembro $equipe)
    {
        $data = $r->validate([
            'nome'   => ['required','string','max:160'],
            // validação adicional ignora o próprio id e desconsidera apagados (soft deletes)
            'slug'   => [
                'nullable','string','max:180',
                Rule::unique('equipe_membros','slug')
                    ->ignore($equipe->id)
                    ->whereNull('deleted_at')
            ],
            'cargo'  => ['nullable','string','max:160'],
            'resumo' => ['nullable','string','max:280'],
            'redes'  => ['nullable','array'],
            'redes.*'=> ['nullable','string','max:255'],
            'status' => ['required','in:rascunho,publicado,arquivado'],
            'ordem'  => ['nullable','integer','min:0'],
            'foto'   => ['nullable','image','max:4096'],
        ]);

        if ($r->hasFile('foto')) {
            $data['foto_path'] = $r->file('foto')->store('equipe/fotos','public');
        }

        // Se não veio slug OU veio igual ao slugificado do nome, garantimos unicidade
        $incoming = $data['slug'] ?: Str::slug($data['nome']);
        if (!$data['slug'] || $incoming === Str::slug($equipe->nome)) {
            $data['slug'] = $this->makeUniqueSlug($incoming, $equipe->id);
        }

        // ordem default
        $data['ordem'] = ($data['ordem'] === null || $data['ordem'] === '') ? 0 : (int)$data['ordem'];

        $equipe->update($data);

        return redirect()->route('coordenador.equipe.index')->with('ok','Membro atualizado.');
    }

    public function destroy(EquipeMembro $equipe)
    {
        $equipe->delete();
        return redirect()->route('coordenador.equipe.index')->with('ok','Removido.');
    }

    /**
     * Gera um slug único com base em $base.
     * Se já existir 'base', tenta 'base-2', 'base-3'... ignorando $ignoreId quando fornecido.
     */
    private function makeUniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = Str::slug($base) ?: 'item';
        $query = EquipeMembro::withTrashed()
            ->where(function($q) use ($slug){
                $q->where('slug', $slug)
                  ->orWhere('slug', 'like', $slug.'-%');
            });

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        $exists = $query->pluck('slug')->all();

        if (!in_array($slug, $exists, true)) {
            return $slug;
        }

        // procura próximo sufixo disponível
        $i = 2;
        while (in_array("{$slug}-{$i}", $exists, true)) {
            $i++;
        }
        return "{$slug}-{$i}";
    }
}
