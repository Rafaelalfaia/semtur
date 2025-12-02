<?php

namespace App\Http\Controllers\Coordenador;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\Conteudo\Aviso;

class AvisoController extends Controller
{
    public function index(Request $r)
    {
        $q   = trim((string) $r->input('q',''));
        $sts = (string) $r->input('status','');

        $avisos = Aviso::query()
            ->when($q, function($w) use($q){
                $w->where('titulo','like',"%{$q}%")
                  ->orWhere('descricao','like',"%{$q}%")
                  ->orWhere('whatsapp','like',"%{$q}%");
            })
            ->when($sts !== '', fn($w)=>$w->where('status',$sts))
            ->orderByDesc('updated_at')
            ->paginate(15)
            ->withQueryString();

        return view('coordenador.avisos.index', compact('avisos','q','sts'));
    }

    public function create()
    {
        $aviso = new Aviso();
        return view('coordenador.avisos.create', compact('aviso'));
    }

    public function store(Request $r)
    {
        $data = $this->validateData($r);

        // Upload imagem (opcional)
        if ($r->hasFile('imagem')) {
            $data['imagem_path'] = $r->file('imagem')->store('avisos','public');
        }

        $aviso = Aviso::create($data);

        return redirect()
            ->route('coordenador.avisos.edit', $aviso)
            ->with('ok','Aviso criado com sucesso.');
    }

    public function edit(Aviso $aviso)
    {
        return view('coordenador.avisos.edit', compact('aviso'));
    }

    public function update(Request $r, Aviso $aviso)
    {
        $data = $this->validateData($r, updating:true);

        if ($r->hasFile('imagem')) {
            // apaga anterior
            if ($aviso->imagem_path) {
                Storage::disk('public')->delete($aviso->imagem_path);
            }
            $data['imagem_path'] = $r->file('imagem')->store('avisos','public');
        }

        $aviso->update($data);

        return redirect()
            ->route('coordenador.avisos.edit', $aviso)
            ->with('ok','Aviso atualizado com sucesso.');
    }

    public function destroy(Aviso $aviso)
    {
        if ($aviso->imagem_path) {
            Storage::disk('public')->delete($aviso->imagem_path);
        }
        $aviso->delete();

        return redirect()
            ->route('coordenador.avisos.index')
            ->with('ok','Aviso removido.');
    }

    public function removerImagem(Aviso $aviso)
    {
        if ($aviso->imagem_path) {
            Storage::disk('public')->delete($aviso->imagem_path);
            $aviso->update(['imagem_path'=>null]);
        }
        return back()->with('ok','Imagem removida.');
    }

    public function publicar(Aviso $aviso)
    {
        $aviso->update(['status' => 'publicado']);
        return back()->with('ok','Aviso publicado.');
    }

    public function arquivar(Aviso $aviso)
    {
        $aviso->update(['status' => 'arquivado']);
        return back()->with('ok','Aviso arquivado.');
    }

    /** -------- Helpers -------- */
    private function validateData(Request $r, bool $updating=false): array
    {
        $like = config('database.default') === 'pgsql' ? 'ilike' : 'like';

        return $r->validate([
            'titulo'     => ['required','string','max:160'],
            'descricao'  => ['required','string','max:8000'],
            'whatsapp'   => ['nullable','string','max:20', 'regex:/^\+?\d{10,15}$/'],
            'status'     => ['required', Rule::in(['publicado','rascunho','arquivado'])],
            'inicio_em'  => ['nullable','date'],
            'fim_em'     => ['nullable','date','after_or_equal:inicio_em'],
            'imagem'     => [$updating ? 'nullable' : 'nullable','image','max:4096'], // ~4MB
        ], [
            'whatsapp.regex' => 'Informe somente números com DDI/DDD (ex: 5593999998888).'
        ]);
    }
}
