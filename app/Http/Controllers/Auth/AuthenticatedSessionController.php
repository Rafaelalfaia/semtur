<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $r): RedirectResponse
    {
        $login    = trim((string) $r->input('login'));
        $password = (string) $r->input('password');

        $r->validate([
            'login'    => ['required','string','max:255'],
            'password' => ['required','string'],
        ]);

        // Decide se é e-mail ou CPF
        $isEmail = filter_var($login, FILTER_VALIDATE_EMAIL);
        $field   = $isEmail ? 'email' : 'cpf';

        // Normaliza: se for CPF, mantenha só dígitos
        $loginNormalized = $isEmail ? strtolower($login) : preg_replace('/\D+/', '', $login);

        $remember = $r->boolean('remember');

        // 1ª tentativa: como está salvo (se você salva cpf limpo)
        if (! Auth::attempt([$field => $loginNormalized, 'password' => $password], $remember)) {
            // 2ª tentativa: se você salva o CPF FORMATADO (000.000.000-00), tente com máscara
            if (! $isEmail && strlen($loginNormalized) === 11) {
                $masked = substr($loginNormalized,0,3).'.'.substr($loginNormalized,3,3).'.'.substr($loginNormalized,6,3).'-'.substr($loginNormalized,9,2);
                if (! Auth::attempt([$field => $masked, 'password' => $password], $remember)) {
                    return back()->withErrors(['login' => 'Credenciais inválidas.'])->onlyInput('login');
                }
            } else {
                return back()->withErrors(['login' => 'Credenciais inválidas.'])->onlyInput('login');
            }
        }

        $r->session()->regenerate();

        // Redireciona conforme o papel
        return redirect()->intended($this->redirectByRole());
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    protected function redirectByRole(): string
    {
        $user = Auth::user();

        // Spatie\Permission
        if (method_exists($user, 'hasRole')) {
            if ($user->hasRole('Admin'))        return route('admin.dashboard');
            if ($user->hasRole('Coordenador'))  return route('coordenador.dashboard');
            if ($user->hasRole('Tecnico'))      return route('tecnico.dashboard');
            if ($user->hasRole('Cidadao'))      return route('site.home');
        }

        // Fallbacks (se não usar Spatie)
        if (property_exists($user, 'role')) {
            return match ($user->role) {
                'Admin'       => route('admin.dashboard'),
                'Coordenador' => route('coordenador.dashboard'),
                'Tecnico'     => route('tecnico.dashboard'),
                'Cidadao'     => route('site.home'),
                default       => route('dashboard'), // genérico, se existir
            };
        }

        return route('dashboard'); // último fallback
    }
}
