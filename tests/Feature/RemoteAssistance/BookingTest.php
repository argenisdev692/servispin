<?php

namespace Tests\Feature\RemoteAssistance;

use App\Http\Requests\StoreRemoteAssistanceRequest;
use App\Mail\RemoteAssistanceRequested;
use App\Models\Appointment;
use App\Models\Brand;
use App\Models\CompanyData;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Fase C (T013, T020): US-1 — solicitar asistencia remota.
 *
 * El módulo tiene un solo modo de fallo que importa de verdad: que alguien
 * reciba una videollamada sin haber pagado. Estos tests existen para eso.
 */
class BookingTest extends TestCase
{
    use RefreshDatabase;

    private Service $service;

    private Brand $brand;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();

        $this->service = Service::factory()->remote()->create();
        $this->brand = Brand::factory()->create();

        $admin = User::factory()->create();
        CompanyData::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Cesar Gonzalez',
            'company_name' => 'ServiSpin',
            'email' => 'info@servispin.net',
            'phone' => '+34643940970',
            'user_id' => $admin->id,
        ]);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'service_id' => $this->service->id,
            'brand_id' => $this->brand->id,
            'client_first_name' => 'Ana',
            'client_last_name' => 'Perez',
            'client_email' => 'ana@example.com',
            'client_phone' => '+34600111222',
            'issue_description' => 'La lavadora no centrifuga y hace un ruido metálico.',
            'start_time' => now()->addDays(3)->setTime(10, 0)->format('Y-m-d H:i:s'),
            'client_timezone' => 'America/Argentina/Buenos_Aires',
            'payment_reference' => 'SUMUP-8842-XZ',
            'payment_amount' => 30.00,
            'payer_name' => 'Ana Perez',
        ], $overrides);
    }

    #[Test]
    public function una_solicitud_valida_queda_pendiente_de_verificar_y_sin_enlace(): void
    {
        $response = $this->postJson(route('remote-assistance.store'), $this->validPayload());

        $response->assertStatus(201)->assertJson(['success' => true]);

        $appointment = Appointment::first();

        $this->assertSame(Appointment::MODALITY_REMOTE, $appointment->modality);
        $this->assertSame(Appointment::STATUS_PENDING, $appointment->status);
        $this->assertSame(Appointment::PAYMENT_CLAIMED, $appointment->payment_status);
        $this->assertSame('SUMUP-8842-XZ', $appointment->payment_reference);
        $this->assertSame('America/Argentina/Buenos_Aires', $appointment->client_timezone);
        $this->assertNotNull($appointment->payment_claimed_at);

        // FR-3: no hay enlace, y no lo hay porque nadie ha verificado el pago.
        $this->assertNull($appointment->meeting_url);
        $this->assertNull($appointment->payment_verified_at);
        $this->assertNull($appointment->payment_verified_by);

        // FR-11: sin dirección postal. Nadie se desplaza.
        $this->assertNull($appointment->address);
    }

    #[Test]
    public function la_respuesta_del_endpoint_publico_nunca_expone_un_enlace(): void
    {
        // FR-3. Aunque por un bug la cita tuviera enlace, este endpoint no puede
        // devolverlo: es público y anónimo.
        $response = $this->postJson(route('remote-assistance.store'), $this->validPayload());

        $response->assertStatus(201);
        $this->assertArrayNotHasKey('meeting_url', $response->json('data'));
        $this->assertStringNotContainsString('meet.google.com', $response->getContent());
    }

    /**
     * EL TEST MÁS IMPORTANTE DEL MÓDULO (plan §7).
     *
     * Si este test se pone en rojo, cualquiera puede inventarse una referencia de
     * pago y recibir una videollamada gratis. La verificación manual es el único
     * control que existe (research #2).
     */
    #[Test]
    public function el_email_de_solicitud_recibida_no_contiene_el_enlace_de_la_videollamada(): void
    {
        $this->postJson(route('remote-assistance.store'), $this->validPayload())->assertStatus(201);

        Mail::assertSent(RemoteAssistanceRequested::class, function (RemoteAssistanceRequested $mail) {
            $rendered = $mail->render();

            $this->assertStringNotContainsString('meet.google.com', $rendered);
            $this->assertStringNotContainsString('meet.jit.si', $rendered);
            $this->assertStringNotContainsString('8x8.vc', $rendered);
            $this->assertNull($mail->appointment->meeting_url);

            return true;
        });
    }

    #[Test]
    public function el_email_al_cliente_avisa_de_que_la_cita_no_es_firme(): void
    {
        // Criterio de aceptación de US-1: el cliente no puede quedarse creyendo
        // que ya tiene la cita reservada.
        $this->postJson(route('remote-assistance.store'), $this->validPayload())->assertStatus(201);

        Mail::assertSent(RemoteAssistanceRequested::class, function (RemoteAssistanceRequested $mail) {
            if ($mail->isForCompany) {
                return false;
            }

            return str_contains($mail->render(), 'no es firme');
        });
    }

    #[Test]
    public function sin_referencia_de_pago_se_rechaza(): void
    {
        // FR-2: sin referencia no hay nada que cotejar contra SumUp.
        $this->postJson(route('remote-assistance.store'), $this->validPayload(['payment_reference' => '']))
            ->assertStatus(422)
            ->assertJsonValidationErrors('payment_reference');

        $this->assertSame(0, Appointment::count());
    }

    #[Test]
    public function sin_importe_ni_pagador_se_rechaza(): void
    {
        $this->postJson(route('remote-assistance.store'), $this->validPayload([
            'payment_amount' => null,
            'payer_name' => '',
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['payment_amount', 'payer_name']);
    }

    /**
     * Guardarraíl de PCI-DSS (T013, FR-4, plan §8).
     *
     * Servispin está fuera del alcance de PCI-DSS por una única razón: el dato
     * de tarjeta lo captura SumUp y nunca toca este servidor. El día que alguien
     * añada un campo de tarjeta "para verificar mejor", esa propiedad se pierde
     * y el negocio entra en alcance regulatorio. Este test es la alarma.
     */
    #[Test]
    public function cualquier_campo_de_tarjeta_hace_fallar_la_solicitud(): void
    {
        // El throttle es real y hace su trabajo: 12 peticiones seguidas (una por
        // campo prohibido) lo disparan y devolvería 429 en vez del 422 que
        // queremos comprobar. Aquí se mide la validación, no el rate limit —que
        // tiene su propio test—, así que se aparta el middleware.
        $this->withoutMiddleware(ThrottleRequests::class);

        foreach (StoreRemoteAssistanceRequest::PROHIBITED_PAYMENT_FIELDS as $field) {
            $response = $this->postJson(
                route('remote-assistance.store'),
                $this->validPayload([$field => '4111111111111111'])
            );

            $response->assertStatus(422, "El campo prohibido [{$field}] fue aceptado. Eso mete a Servispin en PCI-DSS.");
            $response->assertJsonValidationErrors($field);
        }

        $this->assertSame(0, Appointment::count());
    }

    #[Test]
    public function no_se_persiste_ningun_dato_de_tarjeta_aunque_se_intente(): void
    {
        // FR-4: ni siquiera por accidente. Si el guardarraíl de arriba fallara,
        // esto comprueba que el número no acaba en la base de datos.
        $this->postJson(route('remote-assistance.store'), $this->validPayload([
            'card_number' => '4111111111111111',
        ]));

        $this->assertDatabaseMissing('appointments', ['payment_reference' => '4111111111111111']);
        $this->assertSame(0, Appointment::count());
    }

    #[Test]
    public function un_hueco_ya_ocupado_por_una_cita_presencial_se_rechaza(): void
    {
        // FR-7: la agenda del técnico es una sola. No puede estar en una
        // videollamada y en casa de un cliente a la vez.
        $start = now()->addDays(3)->setTime(10, 0);

        Appointment::factory()->create([
            'start_time' => $start,
            'end_time' => $start->copy()->addMinutes(60),
            'status' => Appointment::STATUS_CONFIRMED,
        ]);

        $this->postJson(route('remote-assistance.store'), $this->validPayload([
            'start_time' => $start->format('Y-m-d H:i:s'),
        ]))->assertStatus(422);

        $this->assertSame(0, Appointment::remote()->count());
    }

    #[Test]
    public function un_hueco_ocupado_por_una_cita_cancelada_si_esta_libre(): void
    {
        $start = now()->addDays(3)->setTime(10, 0);

        Appointment::factory()->cancelled()->create([
            'start_time' => $start,
            'end_time' => $start->copy()->addMinutes(60),
        ]);

        $this->postJson(route('remote-assistance.store'), $this->validPayload([
            'start_time' => $start->format('Y-m-d H:i:s'),
        ]))->assertStatus(201);
    }

    #[Test]
    public function no_se_puede_pedir_asistencia_remota_con_un_servicio_presencial(): void
    {
        // Si no, el formulario remoto sería una puerta para crear citas
        // presenciales sin dirección.
        $onsite = Service::factory()->create(['is_remote' => false]);

        $this->postJson(route('remote-assistance.store'), $this->validPayload([
            'service_id' => $onsite->id,
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('service_id');
    }

    #[Test]
    public function el_huso_horario_del_cliente_es_obligatorio_y_debe_ser_valido(): void
    {
        // FR-6 / R-5: mostrar una hora sin saber el huso del cliente es el fallo
        // más probable del módulo y el más caro en reputación.
        $this->postJson(route('remote-assistance.store'), $this->validPayload(['client_timezone' => null]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('client_timezone');

        $this->postJson(route('remote-assistance.store'), $this->validPayload(['client_timezone' => 'Marte/Olympus']))
            ->assertStatus(422)
            ->assertJsonValidationErrors('client_timezone');
    }

    #[Test]
    public function el_honeypot_frena_a_los_bots(): void
    {
        $this->postJson(route('remote-assistance.store'), $this->validPayload(['website_url' => 'http://spam.example']))
            ->assertStatus(422);

        $this->assertSame(0, Appointment::count());
    }

    #[Test]
    public function no_se_exige_direccion_postal(): void
    {
        // FR-11: mandar 'address' no debe ser necesario. Este test falla si
        // alguien añade la regla 'address' => 'required' copiando del flujo
        // presencial.
        $payload = $this->validPayload();
        $this->assertArrayNotHasKey('address', $payload);

        $this->postJson(route('remote-assistance.store'), $payload)->assertStatus(201);
    }
}
