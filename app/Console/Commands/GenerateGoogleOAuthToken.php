<?php

namespace App\Console\Commands;

use Google\Client as GoogleClient;
use Google\Service\Calendar as GoogleCalendar;
use Illuminate\Console\Command;

/**
 * Genera el token OAuth de Google que necesita el proveedor de enlaces Meet.
 *
 * El paquete `spatie/laravel-google-calendar` NO trae comando para esto: espera
 * que el `oauth-token.json` ya exista. Este comando lo crea, ejecutando el flujo
 * de consentimiento una vez con la cuenta de Cesar (D-6).
 *
 * Ver docs/ssd/001-asistencia-remota/README-google-meet.md, paso 3.
 */
class GenerateGoogleOAuthToken extends Command
{
    protected $signature = 'google:oauth-token
                            {--credentials= : Ruta al oauth-credentials.json (por defecto, el del config)}
                            {--token= : Dónde escribir el oauth-token.json (por defecto, el del config)}';

    protected $description = 'Genera el oauth-token.json de Google Calendar/Meet mediante el flujo de consentimiento';

    public function handle(): int
    {
        $credentialsPath = $this->option('credentials')
            ?? config('google-calendar.auth_profiles.oauth.credentials_json');
        $tokenPath = $this->option('token')
            ?? config('google-calendar.auth_profiles.oauth.token_json');

        if (! is_string($credentialsPath) || ! file_exists($credentialsPath)) {
            $this->error("No encuentro las credenciales OAuth en: {$credentialsPath}");
            $this->line('Descárgalas de Google Cloud Console (ID de cliente OAuth, tipo "Aplicación de escritorio")');
            $this->line('y guárdalas ahí. Ver README-google-meet.md, paso 1.');

            return self::FAILURE;
        }

        $client = new GoogleClient;
        $client->setAuthConfig($credentialsPath);
        $client->setScopes([GoogleCalendar::CALENDAR]);
        $client->setAccessType('offline'); // imprescindible para recibir refresh_token (research #7a)
        $client->setPrompt('consent');     // fuerza que Google devuelva refresh_token también en reautorizaciones
        $client->setRedirectUri('urn:ietf:wg:oauth:2.0:oob');

        $this->info('1. Abre esta URL EN SESIÓN DE LA CUENTA DE CESAR y acepta los permisos:');
        $this->newLine();
        $this->line($client->createAuthUrl());
        $this->newLine();

        $code = trim((string) $this->ask('2. Pega aquí el código que te da Google'));

        if ($code === '') {
            $this->error('No has pegado ningún código.');

            return self::FAILURE;
        }

        $token = $client->fetchAccessTokenWithAuthCode($code);

        if (isset($token['error'])) {
            $this->error('Google rechazó el código: '.($token['error_description'] ?? $token['error']));

            return self::FAILURE;
        }

        if (empty($token['refresh_token'])) {
            // Sin refresh_token la conexión moriría al caducar el access token (~1h).
            // Suele pasar si la cuenta ya había autorizado antes sin 'consent'.
            $this->warn('⚠️ Google no devolvió refresh_token. Revoca el acceso previo de la app en');
            $this->warn('   https://myaccount.google.com/permissions y vuelve a ejecutar este comando.');
        }

        $dir = dirname($tokenPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($tokenPath, json_encode($token, JSON_PRETTY_PRINT));

        $this->newLine();
        $this->info("✓ Token guardado en: {$tokenPath}");
        $this->warn('NO lo commitees (ya está en .gitignore) y verifica que sobrevive a un deploy (R-7).');
        $this->line('Ahora pon REMOTE_ASSISTANCE_MEETING_PROVIDER=google_meet y haz la prueba de humo (T011d).');

        return self::SUCCESS;
    }
}
