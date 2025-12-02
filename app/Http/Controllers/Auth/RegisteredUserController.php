<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisteredUserController extends Controller
{
    public function create()
    {
        // Cadastro público = padrão Cidadao (apenas se não houver papel atribuído depois)
        return view('auth.register', [
            'public_role' => 'Cidadao',
        ]);
    }

    public function store(Request $r): RedirectResponse
    {
        // Normaliza CPF (apenas dígitos) e e-mail (lowercase)
        $cpf = preg_replace('/\D+/', '', (string) $r->input('cpf'));
        $r->merge([
            'cpf'   => $cpf ?: null,
            'email' => $r->filled('email') ? strtolower($r->input('email')) : null,
        ]);

        // Validação: pelo menos um (email OU cpf)
        $r->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['nullable', 'email', 'max:255', Rule::unique(User::class, 'email')],
            'cpf'                   => ['nullable', 'digits:11', Rule::unique(User::class, 'cpf')],
            'password'              => ['required', 'confirmed', Password::defaults()],
        ], [
            'cpf.digits'            => 'Informe um CPF válido com 11 dígitos (apenas números).',
            'email.email'           => 'Informe um e-mail válido.',
        ]);

        if (!$r->email && !$r->cpf) {
            return back()
                ->withErrors([
                    'email' => 'Informe e-mail ou CPF.',
                    'cpf'   => 'Informe e-mail ou CPF.',
                ])
                ->withInput();
        }

        // Criação do usuário
        $user = User::create([
            'name'     => $r->name,
            'email'    => $r->email,   // pode ser null
            'cpf'      => $r->cpf,     // pode ser null
            'password' => Hash::make($r->password),
        ]);

        // Regra de papéis:
        // 👉 Atribui "Cidadao" SOMENTE se o usuário ainda não tiver nenhum papel.
        if (method_exists($user, 'roles') && method_exists($user, 'assignRole')) {
            if (!$user->roles()->exists()) {
                try {
                    $user->assignRole('Cidadao'); // requer que o seeder tenha criado este papel
                } catch (\Throwable $e) {
                    // Se o papel não existir ainda, não quebra o fluxo do cadastro
                    // Opcional: logar $e->getMessage()
                }
            }
        } else {
            // Caso não use Spatie Permission, você poderia ter um campo 'role' simples:
            // if (empty($user->role)) { $user->role = 'Cidadao'; $user->save(); }
        }

        // Evento + login
        event(new Registered($user));
        Auth::login($user, remember: true);

        // Redirect seguro (se não houver rota 'dashboard', vai pra '/')
        $dest = '/';
        try { $dest = route('dashboard'); } catch (\Throwable $e) {}
        return redirect()->intended($dest);
    }
}
