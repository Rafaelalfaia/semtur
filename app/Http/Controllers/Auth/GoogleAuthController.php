<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        // Cadastro/login via Google é somente para o papel "Cidadao".
        // Como é público, apenas redirecionamos. O papel será garantido no callback.
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        $g = Socialite::driver('google')->stateless()->user();

        // Tenta achar por google_id
        $user = User::where('google_id', $g->getId())->first();

        // Ou pela correspondência do e-mail
        if (!$user && $g->getEmail()) {
            $user = User::where('email', strtolower($g->getEmail()))->first();
        }

        if (!$user) {
            // Criar novo cidadão (apenas)
            $user = User::create([
                'name'         => $g->getName() ?: ($g->getNickname() ?: 'Novo Cidadão'),
                'email'        => $g->getEmail() ? strtolower($g->getEmail()) : null,
                'google_id'    => $g->getId(),
                'google_email' => $g->getEmail(),
                'password'     => bcrypt(Str::random(32)), // placeholder
            ]);

            if (method_exists($user, 'assignRole')) {
                $user->assignRole('Cidadao');
            }
        } else {
            // Se já existe, só vincula google_id se ainda não tiver
            if (!$user->google_id) {
                $user->google_id    = $g->getId();
                $user->google_email = $g->getEmail();
                $user->save();
            }

            // Se o usuário NÃO for Cidadao, bloqueia login via Google
            if (method_exists($user, 'hasRole') && !$user->hasRole('Cidadao')) {
                return redirect()->route('login')->withErrors([
                    'email' => 'Login via Google é permitido somente para Cidadão.',
                ]);
            }
        }

        Auth::login($user, remember: true);
        return redirect()->route('dashboard');
    }
}
