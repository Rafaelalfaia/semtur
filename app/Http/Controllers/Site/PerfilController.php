<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash;

class PerfilController extends Controller
{
    public function index()
    {
        $u = Auth::user();
        return view('site.perfil.index', compact('u'));
    }

    public function editar()
    {
        $u = Auth::user();
        return view('site.perfil.editar', compact('u'));
    }

    public function atualizar(Request $r)
    {
        $u = Auth::user();

        $rules = [
            'name'   => ['required','string','max:255'],
            'email'  => ['nullable','email','max:255', Rule::unique('users','email')->ignore($u->id)],
            'phone'  => ['nullable','string','max:30'],
            'avatar' => ['nullable','image','max:2048'],
        ];

        if (empty($u->cpf)) {
            $rules['cpf'] = ['nullable','digits:11', Rule::unique('users','cpf')];
        }

        // Se quiser trocar a senha, pede só nova + confirmação
        if ($r->filled('password')) {
            $rules['password'] = ['required','confirmed', Password::defaults()];
            $rules['password_confirmation'] = ['required'];
        }

        $data = $r->validate($rules, [
            'avatar.image' => 'Envie uma imagem válida.',
            'avatar.max'   => 'A imagem pode ter no máximo 2MB.',
            'cpf.digits'   => 'CPF deve ter 11 dígitos.',
        ]);

        if ($r->hasFile('avatar')) {
            $path = $r->file('avatar')->store('avatars','public');
            $u->avatar_url = \Storage::url($path);
        }

        $u->name  = $data['name'];
        $u->email = $data['email'] ?? null;
        $u->phone = $data['phone'] ?? null;

        if (empty($u->cpf) && !empty($data['cpf'])) {
            $u->cpf = $data['cpf'];
        }

        if (!empty($data['password'])) {
            $u->password = Hash::make($data['password']);
        }

        $u->save();

        return redirect()->route('site.perfil.index')->with('status','Perfil atualizado!');
    }

    public function redes()
    {
        $u = Auth::user();
        $socials = $u->socials ?? [];
        return view('site.perfil.redes', compact('u','socials'));
    }

    public function redesAtualizar(Request $r)
    {
        $u = Auth::user();
        $data = $r->validate([
            'instagram' => ['nullable','string','max:255'],
            'facebook'  => ['nullable','string','max:255'],
            'site'      => ['nullable','string','max:255'],
        ]);

        $u->socials = [
            'instagram' => $data['instagram'] ?? null,
            'facebook'  => $data['facebook'] ?? null,
            'site'      => $data['site'] ?? null,
        ];
        $u->save();

        return redirect()->route('site.perfil.index')->with('status','Redes sociais salvas!');
    }
}
