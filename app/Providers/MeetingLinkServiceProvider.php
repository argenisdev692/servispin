<?php

namespace App\Providers;

use App\Services\MeetingLink\GoogleMeetLinkProvider;
use App\Services\MeetingLink\ManualMeetingLinkProvider;
use App\Services\MeetingLink\MeetingLinkProvider;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

/**
 * Resuelve el MeetingLinkProvider a partir de config('remote_assistance.meeting_provider').
 *
 * Cambiar el .env intercambia la implementación sin tocar el resto del código.
 */
class MeetingLinkServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MeetingLinkProvider::class, function () {
            $provider = config('remote_assistance.meeting_provider', 'manual');

            return match ($provider) {
                'manual' => new ManualMeetingLinkProvider,
                'google_meet' => new GoogleMeetLinkProvider,

                // Un typo en el .env no puede degradar a manual en silencio: eso
                // dejaría a Cesar pegando enlaces a mano sin entender por qué
                // dejó de funcionar la automatización.
                default => throw new InvalidArgumentException(
                    "Proveedor de enlace desconocido: [{$provider}]. Valores válidos: manual, google_meet."
                ),
            };
        });
    }
}
