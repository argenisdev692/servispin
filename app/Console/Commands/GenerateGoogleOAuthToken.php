<?php

namespace App\Console\Commands;

use App\Services\GoogleCalendarOAuthService;
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
                            {--token= : Dónde escribir el oauth-token.json (por defecto, el del config)}
                            {--manual : No abrir navegador ni escuchar en localhost; solo pegar el código}
                            {--url= : URL de callback completa (http://127.0.0.1:PUERTO/?code=...)}';

    protected $description = 'Genera el oauth-token.json de Google Calendar/Meet mediante el flujo de consentimiento';

    public function handle(): int
    {
        $this->warnIfRunningInDocker();

        $oauth = app(GoogleCalendarOAuthService::class);

        $credentialsPath = $this->option('credentials') ?? $oauth->credentialsPath();
        $tokenPath = $this->option('token') ?? $oauth->tokenPath();

        if (! is_string($credentialsPath) || ! file_exists($credentialsPath)) {
            $this->error("No encuentro las credenciales OAuth en: {$credentialsPath}");
            $this->line('Descárgalas de Google Cloud Console (ID de cliente OAuth, tipo "Aplicación de escritorio")');
            $this->line('y guárdalas ahí. Ver README-google-meet.md, paso 1.');
            $this->newLine();
            $this->line('Con Sail también puedes conectar por navegador:');
            $this->line('http://localhost/admin/google-calendar/oauth/connect');

            return self::FAILURE;
        }

        $client = $oauth->createClient();

        $callbackUrl = $this->option('url');

        if ($this->option('manual') || is_string($callbackUrl)) {
            if (! is_string($callbackUrl) || $callbackUrl === '') {
                $this->info('Modo manual: abre la URL en el navegador (sesión de la cuenta del calendario).');
                $redirectUri = $this->resolveRedirectUri($credentialsPath);
                $client->setRedirectUri($redirectUri);
                $this->line($client->createAuthUrl());
                $this->newLine();
                $this->line('Tras aceptar, copia la URL completa de la barra (aunque diga "conexión rechazada").');
                $callbackUrl = trim((string) $this->ask('Pega la URL completa o solo el parámetro code'));
            }

            $redirectUri = $this->extractRedirectUri($callbackUrl);
            if ($redirectUri !== null) {
                $client->setRedirectUri($redirectUri);
            }

            $code = $this->extractAuthorizationCode($callbackUrl);
        } else {
            // Google bloqueó el flujo OOB (urn:ietf:wg:oauth:2.0:oob) en 2023. Las apps de
            // escritorio deben usar loopback: http://127.0.0.1:PUERTO
            $redirectUri = $this->resolveRedirectUri($credentialsPath);
            $client->setRedirectUri($redirectUri);

            $authUrl = $client->createAuthUrl();

            $this->info('1. Se abrirá el navegador para autorizar con la cuenta del calendario.');
            $this->line("   Redirect URI: {$redirectUri}");
            $this->newLine();

            $code = $this->listenForAuthorizationCode($redirectUri, function () use ($authUrl, $redirectUri) {
                $this->line("   Esperando en {$redirectUri} (2 minutos)…");
                $this->line("   Si no se abre solo: {$authUrl}");
                $this->openBrowser($authUrl);
            });
        }

        if ($code === '' && ! $this->option('manual') && ! is_string($this->option('url'))) {
            $this->newLine();
            $this->warn('No se recibió el código por localhost.');
            $this->line('Si el navegador muestra "conexión rechazada", la autorización puede haber funcionado.');
            $this->line('Copia la URL de la barra (http://127.0.0.1:...?code=...) y pégala abajo.');
            $callbackUrl = trim((string) $this->ask('Pega la URL completa o solo el parámetro code'));
            $redirectUri = $this->extractRedirectUri($callbackUrl);
            if ($redirectUri !== null) {
                $client->setRedirectUri($redirectUri);
            }
            $code = $this->extractAuthorizationCode($callbackUrl);
        }

        if ($code === '') {
            $this->error('No se recibió el código de autorización.');

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

    /**
     * Resuelve la redirect URI para el flujo loopback de apps de escritorio.
     *
     * Los clientes OAuth tipo "Desktop app" aceptan cualquier puerto en 127.0.0.1.
     * Si el JSON es de tipo "web", hay que registrar la misma URI en Cloud Console.
     */
    private function resolveRedirectUri(string $credentialsPath): string
    {
        $config = json_decode((string) file_get_contents($credentialsPath), true);
        $uris = $config['installed']['redirect_uris']
            ?? $config['web']['redirect_uris']
            ?? [];

        foreach ($uris as $uri) {
            if (! is_string($uri) || str_contains($uri, 'urn:ietf:wg:oauth:2.0:oob')) {
                continue;
            }

            if (preg_match('#^https?://(127\.0\.0\.1|localhost)(:\d+)?/?$#', $uri, $matches) && empty($matches[2])) {
                $port = $this->findAvailablePort();

                return "http://127.0.0.1:{$port}";
            }

            if (str_starts_with($uri, 'http://127.0.0.1:') || str_starts_with($uri, 'http://localhost:')) {
                return $uri;
            }
        }

        $port = $this->findAvailablePort();

        return "http://127.0.0.1:{$port}";
    }

    private function findAvailablePort(): int
    {
        $socket = stream_socket_server('tcp://127.0.0.1:0', $errno, $errstr);
        if ($socket === false) {
            throw new \RuntimeException("No pude reservar un puerto local: {$errstr}");
        }

        $address = stream_socket_get_name($socket, false);
        fclose($socket);

        if (! is_string($address) || ! str_contains($address, ':')) {
            throw new \RuntimeException('No pude determinar un puerto local libre.');
        }

        return (int) substr($address, strrpos($address, ':') + 1);
    }

    private function openBrowser(string $url): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            pclose(popen('start "" '.escapeshellarg($url), 'r'));

            return;
        }

        if (PHP_OS_FAMILY === 'Darwin') {
            exec('open '.escapeshellarg($url));

            return;
        }

        exec('xdg-open '.escapeshellarg($url).' 2>/dev/null');
    }

    private function warnIfRunningInDocker(): void
    {
        if (! file_exists('/.dockerenv') && ! env('LARAVEL_SAIL')) {
            return;
        }

        $this->warn('⚠️ Estás en Docker/Sail. Usa el flujo por navegador (recomendado):');
        $this->warn('   http://localhost/admin/google-calendar/oauth/connect');
        $this->newLine();
    }

    private function extractRedirectUri(string $input): ?string
    {
        if (! str_contains($input, '://')) {
            return null;
        }

        $parts = parse_url($input);
        if (! is_array($parts) || empty($parts['host'])) {
            return null;
        }

        $scheme = $parts['scheme'] ?? 'http';
        $host = $parts['host'];

        if (isset($parts['port'])) {
            return "{$scheme}://{$host}:{$parts['port']}";
        }

        return "{$scheme}://{$host}";
    }

    private function askForAuthorizationCode(): string
    {
        $input = trim((string) $this->ask('Pega la URL completa o solo el parámetro code'));

        return $this->extractAuthorizationCode($input);
    }

    private function extractAuthorizationCode(string $input): string
    {
        if ($input === '') {
            return '';
        }

        if (str_contains($input, 'code=')) {
            $query = parse_url($input, PHP_URL_QUERY);
            if (is_string($query)) {
                parse_str($query, $params);

                return trim((string) ($params['code'] ?? ''));
            }
        }

        return $input;
    }

    /**
     * Arranca el servidor local ANTES de abrir el navegador (evita connection refused
     * si el usuario autoriza muy rápido).
     *
     * @param  callable(): void  $afterServerStarted
     */
    private function listenForAuthorizationCode(string $redirectUri, callable $afterServerStarted): string
    {
        $parts = parse_url($redirectUri);
        $host = $parts['host'] ?? '127.0.0.1';
        $port = (int) ($parts['port'] ?? 80);

        $server = @stream_socket_server("tcp://{$host}:{$port}", $errno, $errstr);
        if ($server === false) {
            throw new \RuntimeException("No pude escuchar en {$redirectUri}: {$errstr}");
        }

        stream_set_blocking($server, false);
        $afterServerStarted();

        $code = '';
        $deadline = time() + 120;

        while (time() < $deadline && $code === '') {
            $connection = @stream_socket_accept($server, 2);
            if ($connection === false) {
                usleep(200_000);

                continue;
            }

            stream_set_timeout($connection, 5);
            $request = (string) stream_get_contents($connection);

            if (preg_match('/GET \/?\?([^\s]+) HTTP/', $request, $matches)) {
                parse_str(urldecode($matches[1]), $params);

                if (! empty($params['error'])) {
                    fclose($connection);
                    fclose($server);
                    throw new \RuntimeException('Google rechazó la autorización: '.$params['error']);
                }

                $code = trim((string) ($params['code'] ?? ''));

                $body = $code !== ''
                    ? '<h1>Autorización correcta</h1><p>Puedes cerrar esta ventana y volver a la terminal.</p>'
                    : '<h1>No se recibió el código</h1><p>Vuelve a la terminal e inténtalo de nuevo.</p>';

                $response = "HTTP/1.1 200 OK\r\n"
                    ."Content-Type: text/html; charset=utf-8\r\n"
                    .'Content-Length: '.strlen($body)."\r\n"
                    ."Connection: close\r\n\r\n"
                    .$body;

                fwrite($connection, $response);
            }

            fclose($connection);
        }

        fclose($server);

        return $code;
    }
}
