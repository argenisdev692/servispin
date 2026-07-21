<?php

namespace App\Services;

use Google\Client as GoogleClient;
use Google\Service\Calendar as GoogleCalendar;
use RuntimeException;

class GoogleCalendarOAuthService
{
    public function credentialsPath(): string
    {
        return (string) config('google-calendar.auth_profiles.oauth.credentials_json');
    }

    public function tokenPath(): string
    {
        return (string) config('google-calendar.auth_profiles.oauth.token_json');
    }

    public function redirectUri(): string
    {
        return (string) config('google-calendar.oauth_redirect_uri', 'http://localhost');
    }

    public function credentialsExist(): bool
    {
        return file_exists($this->credentialsPath());
    }

    public function tokenExists(): bool
    {
        return file_exists($this->tokenPath());
    }

    public function createClient(): GoogleClient
    {
        $credentialsPath = $this->credentialsPath();

        if (! file_exists($credentialsPath)) {
            throw new RuntimeException("No encuentro oauth-credentials.json en: {$credentialsPath}");
        }

        $client = new GoogleClient;
        $client->setAuthConfig($credentialsPath);
        $client->setScopes([GoogleCalendar::CALENDAR]);
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        $client->setRedirectUri($this->redirectUri());

        return $client;
    }

    public function authorizationUrl(): string
    {
        return $this->createClient()->createAuthUrl();
    }

    /**
     * @return array<string, mixed>
     */
    public function exchangeCode(string $code): array
    {
        $token = $this->createClient()->fetchAccessTokenWithAuthCode($code);

        if (isset($token['error'])) {
            throw new RuntimeException(
                (string) ($token['error_description'] ?? $token['error'])
            );
        }

        return $token;
    }

    /**
     * @param  array<string, mixed>  $token
     */
    public function saveToken(array $token): void
    {
        $tokenPath = $this->tokenPath();
        $dir = dirname($tokenPath);

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($tokenPath, json_encode($token, JSON_PRETTY_PRINT));
    }

    public function isCalendarOAuthCallback(string $code, ?string $scope): bool
    {
        return $code !== '' && is_string($scope) && str_contains($scope, GoogleCalendar::CALENDAR);
    }
}
