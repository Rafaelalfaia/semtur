<?php

namespace App\Http\Controllers\Coordenador;

use App\Http\Controllers\Controller;
use App\Models\Secretaria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SecretariaController extends Controller
{
    // Mostra o form único
    public function edit()
    {
        $secretaria = Secretaria::instance();
        return view('coordenador.secretaria.edit', compact('secretaria'));
    }

    // Atualiza o registro único
    public function update(Request $r)
    {
        $secretaria = Secretaria::instance();

        $data = $r->validate([
        'nome'       => ['required','string','max:160'],
        'slug'       => ['nullable','string','max:180',"unique:secretaria,slug,{$secretaria->id}"],
        'descricao'  => ['nullable','string'],

        'maps_url'   => ['nullable','string','max:500'], // mantido
        // REMOVIDO: endereco/bairro/cidade/lat/lng

        'redes'      => ['nullable','array'],
        'redes.*'    => ['nullable','string','max:255'],

        'status'     => ['required','in:rascunho,publicado,arquivado'],
        'ordem'      => ['nullable','integer','min:0'],

        'foto'       => ['nullable','image','max:4096'],
        'foto_capa'  => ['nullable','image','max:8192'],
    ]);

       // Uploads
        if ($r->hasFile('foto')) {
            $data['foto_path'] = $r->file('foto')->store('secretaria/fotos','public');
        }
        if ($r->hasFile('foto_capa')) {
            $data['foto_capa_path'] = $r->file('foto_capa')->store('secretaria/capas','public');
        }

        // Ordem padrão se vier vazia
        $data['ordem'] = isset($data['ordem']) ? (int)$data['ordem'] : 0;

        // Slug opcional
        if (empty($data['slug'])) {
            $data['slug'] = \Illuminate\Support\Str::slug($data['nome']);
        }

        $data['created_by'] = $secretaria->created_by ?? \Illuminate\Support\Facades\Auth::id();

        $secretaria->update($data);

        return back()->with('ok','Informações institucionais atualizadas.');
    }
}
