<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Spatie\GoogleCalendar\Event;
use Throwable;

class TestGoogleCalendar extends Command
{
    protected $signature = 'google:calendar-test
                            {--meet : Intentar también crear un enlace de Google Meet}
                            {--keep : No borrar el evento de prueba al terminar}';

    protected $description = 'Prueba de humo: crea (y opcionalmente borra) un evento en Google Calendar';

    public function handle(): int
    {
        $profile = (string) config('google-calendar.default_auth_profile');
        $calendarId = (string) config('google-calendar.calendar_id');
        $credentials = (string) config("google-calendar.auth_profiles.{$profile}.credentials_json");

        $this->info('=== Google Calendar — prueba de humo ===');
        $this->line("Perfil:        {$profile}");
        $this->line('Calendar ID:   '.($calendarId !== '' ? $calendarId : '(vacío)'));
        $this->line("Credenciales:  {$credentials}");
        $this->newLine();

        if ($calendarId === '') {
            $this->error('Falta GOOGLE_CALENDAR_ID en el .env');

            return self::FAILURE;
        }

        if (! is_file($credentials)) {
            $this->error("No existe el fichero de credenciales: {$credentials}");

            return self::FAILURE;
        }

        if ($profile === 'service_account' && $this->option('meet')) {
            $this->warn('Con service_account + Gmail personal, Meet suele fallar');
            $this->warn('con "Invalid conference type value". Usa OAuth para Meet.');
            $this->newLine();
        }

        try {
            $start = Carbon::now()->addHour()->seconds(0);
            $end = $start->copy()->addHour();

            $event = new Event;
            $event->name = 'ServiSpin — prueba Google Calendar';
            $event->description = 'Evento de prueba creado por `php artisan google:calendar-test`. Puedes borrarlo.';
            $event->startDateTime = $start;
            $event->endDateTime = $end;

            if ($this->option('meet')) {
                $event->addMeetLink();
            }

            $event = $event->save();
        } catch (Throwable $e) {
            $this->error('Falló al crear el evento:');
            $this->line($e->getMessage());
            $this->newLine();
            $this->hintCommonFixes($profile);

            return self::FAILURE;
        }

        $googleEvent = $event->googleEvent;
        $eventId = $googleEvent?->getId() ?? '(sin id)';
        $hangout = $googleEvent?->getHangoutLink();

        $this->info('Evento creado correctamente.');
        $this->line("Event ID:  {$eventId}");
        $this->line('Inicio:    '.$start->toDateTimeString());
        $this->line('Fin:       '.$end->toDateTimeString());

        if ($this->option('meet')) {
            if ($hangout) {
                $this->info("Meet OK:   {$hangout}");
            } else {
                $this->warn('El evento se creó, pero Google no devolvió enlace de Meet.');
                if ($profile === 'service_account') {
                    $this->warn('Causa habitual: service_account + Gmail personal.');
                    $this->warn('Solución: GOOGLE_CALENDAR_AUTH_PROFILE=oauth + token OAuth.');
                }
            }
        }

        if (! $this->option('keep')) {
            try {
                $event->delete();
                $this->line('Evento de prueba borrado.');
            } catch (Throwable $e) {
                $this->warn('No se pudo borrar el evento de prueba: '.$e->getMessage());
                $this->warn('Bórralo a mano en Google Calendar.');
            }
        } else {
            $this->comment('Se dejó el evento en el calendario (--keep).');
        }

        $this->newLine();
        $this->info('Prueba terminada.');

        return self::SUCCESS;
    }

    private function hintCommonFixes(string $profile): void
    {
        $this->comment('Revisa:');
        $this->line('1. GOOGLE_CALENDAR_ID = email del calendario o el ID de "Integrate calendar".');
        $this->line('2. El calendario está compartido con el email de la service account');
        $this->line('   (xxx@....iam.gserviceaccount.com) con permiso "Make changes to events".');
        $this->line('3. El JSON está en storage/app/google-calendar/service-account-credentials.json');
        $this->line('4. Google Calendar API está habilitada en el proyecto de Cloud Console.');
        $this->line('5. php artisan config:clear después de cambiar el .env');

        if ($profile === 'oauth') {
            $this->line('6. Existe storage/app/google-calendar/oauth-token.json (google:oauth-token).');
        }
    }
}
