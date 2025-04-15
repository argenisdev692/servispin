<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\CompanyData;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Mail\AppointmentReminder;

class SendAppointmentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appointments:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminder emails for upcoming appointments';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Get company data for sending emails
        $companyData = CompanyData::first();
        if (!$companyData) {
            $this->error('No company data found');
            return 1;
        }

        // Find appointments scheduled for tomorrow
        $tomorrow = Carbon::tomorrow()->toDateString();
        $appointments = Appointment::whereDate('start_time', $tomorrow)
            ->where('status', 'Confirmed')
            ->get();

        $this->info("Found " . $appointments->count() . " appointments for tomorrow.");

        // Send reminder emails
        foreach ($appointments as $appointment) {
            try {
                // Send to client
                Mail::to($appointment->client_email)
                    ->send(new AppointmentReminder($appointment, $companyData));

                // Also send a copy to company if needed
                if ($companyData->email) {
                    Mail::to($companyData->email)
                        ->send(new AppointmentReminder($appointment, $companyData, true));
                }

                $this->info("Sent reminder for appointment ID: " . $appointment->id);
            } catch (\Exception $e) {
                $this->error("Failed to send reminder for appointment ID: " . $appointment->id . " - " . $e->getMessage());
            }
        }

        return 0;
    }
}
