<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\GoogleCalendarOAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class GoogleCalendarOAuthController extends Controller
{
    public function __construct(
        protected GoogleCalendarOAuthService $googleCalendarOAuth,
    ) {}

    public function connect(): RedirectResponse
    {
        if (! $this->googleCalendarOAuth->credentialsExist()) {
            return redirect()
                ->route('admin.remote-assistance.index')
                ->with('error', 'Falta storage/app/google-calendar/oauth-credentials.json (cliente OAuth Desktop en Google Cloud).');
        }

        return redirect()->away($this->googleCalendarOAuth->authorizationUrl());
    }

    public function callback(Request $request): RedirectResponse
    {
        if ($request->filled('error')) {
            return redirect()
                ->route('admin.remote-assistance.index')
                ->with('error', 'Google rechazó la autorización: '.$request->query('error'));
        }

        $code = trim((string) $request->query('code', ''));

        if ($code === '') {
            return redirect()
                ->route('admin.remote-assistance.index')
                ->with('error', 'Google no devolvió código de autorización.');
        }

        try {
            $token = $this->googleCalendarOAuth->exchangeCode($code);
            $this->googleCalendarOAuth->saveToken($token);
        } catch (Throwable $e) {
            return redirect()
                ->route('admin.remote-assistance.index')
                ->with('error', 'No se pudo guardar el token de Google Calendar: '.$e->getMessage());
        }

        $message = 'Google Calendar conectado correctamente.';
        if (empty($token['refresh_token'])) {
            $message .= ' Google no devolvió refresh_token: revoca el acceso en myaccount.google.com/permissions y vuelve a conectar.';
        }

        return redirect()
            ->route('admin.remote-assistance.index')
            ->with('success', $message);
    }
}
