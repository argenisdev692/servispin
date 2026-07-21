<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable) {
            return redirect()
                ->route('login')
                ->with('error', 'No se pudo iniciar sesión con Google. Inténtalo de nuevo.');
        }

        $existingUser = User::where('email', $googleUser->email)->first();

        if (! $existingUser) {
            return redirect()
                ->route('login')
                ->with('members_only', true);
        }

        if (! $existingUser->email_verified_at) {
            $existingUser->email_verified_at = now();
        }

        if (! $existingUser->google_id) {
            $existingUser->google_id = $googleUser->id;
        }

        $existingUser->save();

        Auth::login($existingUser);

        return redirect()->intended('/dashboard');
    }
}
