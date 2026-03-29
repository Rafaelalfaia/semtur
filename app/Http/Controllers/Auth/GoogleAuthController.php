<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        $googleId = (string) $googleUser->getId();
        $email = $googleUser->getEmail() ? strtolower($googleUser->getEmail()) : null;
        $name = $googleUser->getName() ?: $googleUser->getNickname() ?: ($email ? strtok($email, '@') : 'Usuario');

        $user = User::where('google_id', $googleId)->first();

        if (! $user && $email) {
            $user = User::where('email', $email)->first();
        }

        if (! $user) {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'google_id' => $googleId,
                'avatar_url' => $googleUser->getAvatar(),
                'password' => Hash::make(Str::random(40)),
            ]);
        } else {
            $user->update([
                'name' => $name,
                'google_id' => $googleId,
                'avatar_url' => $googleUser->getAvatar(),
            ]);
        }

        if (method_exists($user, 'roles') && method_exists($user, 'assignRole')) {
            if (! $user->roles()->exists()) {
                $user->assignRole('Cidadao');
            }
        }

        Auth::login($user, remember: true);

        return redirect()->intended(localized_route('site.home', [
            'locale' => route_locale(null, $request),
        ]));
    }
}
