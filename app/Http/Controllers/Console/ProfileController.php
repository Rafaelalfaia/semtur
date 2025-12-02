<?php

namespace App\Http\Controllers\Console;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit()
    {
        $u = Auth::user();
        return view('console.config.perfil', compact('u'));
    }

    public function update(Request $r)
    {
        $u = Auth::user();

        // normalizações
        $cpf = preg_replace('/\D+/', '', (string)$r->input('cpf'));
        $r->merge([
            'name'  => trim((string)$r->input('name')),
            'cpf'   => $cpf ?: null,
            'email' => $r->input('email') ? strtolower($r->input('email')) : null,
        ]);

        $rules = [
            'name'   => ['required','string','max:255'], // ⬅️ novo
            'email'  => ['nullable','email','max:255', Rule::unique('users','email')->ignore($u->id)],
            'cpf'    => ['nullable','digits:11', Rule::unique('users','cpf')->ignore($u->id)],
            'avatar' => ['nullable','image','max:2048'],
        ];

        if ($r->filled('password')) {
            $rules['password'] = ['required','confirmed', Password::defaults()];
            $rules['password_confirmation'] = ['required'];
        }

        $data = $r->validate($rules, [
            'name.required' => 'Informe o nome.',
            'cpf.digits'    => 'CPF deve ter 11 dígitos.',
            'avatar.image'  => 'Envie uma imagem válida.',
            'avatar.max'    => 'A imagem pode ter no máximo 2MB.',
        ]);

        // avatar
        if ($r->hasFile('avatar')) {
            $path = $r->file('avatar')->store('avatars','public');
            $u->avatar_url = \Illuminate\Support\Facades\Storage::url($path);
        }

        // nome / e-mail / cpf
        $u->name  = $data['name'];
        $u->email = $data['email'] ?? null;
        $u->cpf   = $data['cpf']   ?? null;

        // senha (se enviada)
        if (!empty($data['password'])) {
            $u->password = \Illuminate\Support\Facades\Hash::make($data['password']);
            // opcional: derrubar outras sessões
            // Auth::logoutOtherDevices($data['password']);
        }

        $u->save();

        return back()->with('ok','Perfil atualizado com sucesso.');
    }


    public function destroyPhoto()
    {
        $u = Auth::user();
        if ($u->avatar_url && str_starts_with($u->avatar_url, '/storage/')) {
            $rel = ltrim(parse_url($u->avatar_url, PHP_URL_PATH), '/');
            $rel = preg_replace('#^storage/#', '', $rel);
            if ($rel && Storage::disk('public')->exists($rel)) {
                Storage::disk('public')->delete($rel);
            }
        }
        $u->avatar_url = null;
        $u->save();

        return back()->with('ok','Foto removida.');
    }
}
