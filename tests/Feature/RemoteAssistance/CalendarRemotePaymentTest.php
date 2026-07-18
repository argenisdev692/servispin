<?php

namespace Tests\Feature\RemoteAssistance;

use App\Models\Appointment;
use App\Models\CompanyData;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CalendarRemotePaymentTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::create(['name' => 'Admin', 'guard_name' => 'sanctum']);
        $this->admin = User::factory()->create();
        $this->admin->assignRole($adminRole);

        CompanyData::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Cesar Gonzalez',
            'company_name' => 'ServiSpin',
            'email' => 'info@servispin.net',
            'phone' => '+34643940970',
            'user_id' => $this->admin->id,
        ]);

        config(['remote_assistance.meeting_provider' => 'manual']);
    }

    #[Test]
    public function el_calendario_no_permite_confirmar_una_remota_sin_verificar_pago(): void
    {
        $appointment = Appointment::factory()
            ->remote()
            ->for(Service::factory()->remote())
            ->create();

        $this->actingAs($this->admin)
            ->patchJson(route('admin.appointment.calendar.status.update', $appointment->id), [
                'status' => 'Confirmed',
            ])
            ->assertStatus(422)
            ->assertJsonFragment([
                'success' => false,
            ]);

        $this->assertSame(Appointment::STATUS_PENDING, $appointment->fresh()->status);
        $this->assertSame(Appointment::PAYMENT_CLAIMED, $appointment->fresh()->payment_status);
    }

    #[Test]
    public function el_calendario_no_permite_cancelar_una_remota_pendiente_de_pago_por_status_generico(): void
    {
        $appointment = Appointment::factory()
            ->remote()
            ->for(Service::factory()->remote())
            ->create();

        $this->actingAs($this->admin)
            ->patchJson(route('admin.appointment.calendar.status.update', $appointment->id), [
                'status' => 'Cancelled',
            ])
            ->assertStatus(422);

        $this->assertSame(Appointment::STATUS_PENDING, $appointment->fresh()->status);
    }

    #[Test]
    public function los_eventos_del_calendario_incluyen_datos_de_pago_remoto(): void
    {
        $appointment = Appointment::factory()
            ->remote()
            ->for(Service::factory()->remote())
            ->create([
                'payment_reference' => 'REF-TEST-123',
                'payment_amount' => 45.50,
            ]);

        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.appointment.calendar.events'));

        $response->assertStatus(200);

        $event = collect($response->json())->firstWhere('id', $appointment->id);
        $this->assertNotNull($event);
        $this->assertTrue($event['extendedProps']['isRemote']);
        $this->assertSame('REF-TEST-123', $event['extendedProps']['paymentReference']);
        $this->assertEquals(45.50, (float) $event['extendedProps']['paymentAmount']);
    }
}
