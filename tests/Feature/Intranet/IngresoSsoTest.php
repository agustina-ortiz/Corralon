<?php

namespace Tests\Feature\Intranet;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IngresoSsoTest extends TestCase
{
    use RefreshDatabase;

    private const MENSAJE = 'No pudimos validar tu ingreso desde la intranet. Entrá con tu usuario y clave.';
    private const CANJE = 'http://intranet.test/api/sso/v1/canjes';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'intranet.url' => 'http://intranet.test',
            'intranet.clave_servicio' => str_repeat('k', 64),
            'intranet.ips' => [],
        ]);
    }

    private function canjeDevuelve(int $estado, array $cuerpo): void
    {
        Http::fake([self::CANJE => Http::response($cuerpo, $estado)]);
    }

    private function rechazado($respuesta): void
    {
        $respuesta->assertRedirect(route('login'))->assertSessionHas('error', self::MENSAJE);
        $this->assertGuest();
    }

    public function test_ticket_valido_entra_con_el_usuario_de_ese_dni(): void
    {
        $u = User::factory()->create(['dni' => 20123456, 'id_rol' => 2]);
        $this->canjeDevuelve(200, ['dni' => 20123456, 'apellido' => 'Pérez', 'nombre' => 'Ana']);

        $this->get('/sso/ingresar?ticket='.str_repeat('a', 40))
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($u);
        Http::assertSent(fn (Request $r) => $r->url() === self::CANJE
            && $r->method() === 'POST'
            && $r->hasHeader('Authorization', 'Bearer '.str_repeat('k', 64))
            && $r['ticket'] === str_repeat('a', 40));
    }

    public function test_respeta_la_url_a_la_que_queria_ir(): void
    {
        User::factory()->create(['dni' => 20123456, 'id_rol' => 1]);
        $this->canjeDevuelve(200, ['dni' => 20123456, 'apellido' => 'Pérez', 'nombre' => 'Ana']);

        $this->get('/usuarios')->assertRedirect(route('login'));
        $this->get('/sso/ingresar?ticket=abc')->assertRedirect('http://localhost/usuarios');
    }

    public function test_ticket_vencido_usado_o_de_otro_sistema_no_entra(): void
    {
        User::factory()->create(['dni' => 20123456]);
        // La intranet responde lo mismo para los tres casos.
        $this->canjeDevuelve(422, ['message' => 'Ticket inválido.']);

        $this->rechazado($this->get('/sso/ingresar?ticket=vencido'));
    }

    public function test_sin_ticket_no_entra_y_no_llama_a_la_intranet(): void
    {
        Http::fake();

        $this->rechazado($this->get('/sso/ingresar'));
        $this->rechazado($this->get('/sso/ingresar?ticket='));
        Http::assertNothingSent();
    }

    public function test_dni_inexistente_no_entra(): void
    {
        User::factory()->create(['dni' => 11111111]);
        $this->canjeDevuelve(200, ['dni' => 20123456, 'apellido' => 'Pérez', 'nombre' => 'Ana']);

        $this->rechazado($this->get('/sso/ingresar?ticket=abc'));
    }

    public function test_usuario_inactivo_no_entra(): void
    {
        User::factory()->create(['dni' => 20123456, 'activo' => false]);
        $this->canjeDevuelve(200, ['dni' => 20123456, 'apellido' => 'Pérez', 'nombre' => 'Ana']);

        $this->rechazado($this->get('/sso/ingresar?ticket=abc'));
    }

    public function test_si_falla_la_red_no_entra(): void
    {
        User::factory()->create(['dni' => 20123456]);
        Http::fake(fn () => throw new ConnectionException('timeout'));

        $this->rechazado($this->get('/sso/ingresar?ticket=abc'));
    }

    public function test_respuesta_sin_dni_o_error_de_la_intranet_no_entra(): void
    {
        User::factory()->create(['dni' => 20123456]);

        $this->canjeDevuelve(200, ['apellido' => 'Pérez']);
        $this->rechazado($this->get('/sso/ingresar?ticket=abc'));

        $this->canjeDevuelve(500, ['dni' => 20123456]);
        $this->rechazado($this->get('/sso/ingresar?ticket=abc'));
    }

    public function test_sin_configuracion_no_entra(): void
    {
        config(['intranet.clave_servicio' => '']);
        Http::fake();
        User::factory()->create(['dni' => 20123456]);

        $this->rechazado($this->get('/sso/ingresar?ticket=abc'));
        Http::assertNothingSent();
    }

    public function test_cierra_la_sesion_previa_aunque_el_ticket_falle(): void
    {
        $anterior = User::factory()->create(['id_rol' => 1]);
        $this->canjeDevuelve(422, ['message' => 'Ticket inválido.']);

        $this->actingAs($anterior);
        $this->rechazado($this->get('/sso/ingresar?ticket=abc'));
    }

    public function test_reemplaza_la_sesion_previa_por_el_usuario_del_ticket(): void
    {
        $anterior = User::factory()->create(['id_rol' => 1]);
        $nuevo = User::factory()->create(['dni' => 20123456, 'id_rol' => 2]);
        $this->canjeDevuelve(200, ['dni' => 20123456, 'apellido' => 'Pérez', 'nombre' => 'Ana']);

        $this->actingAs($anterior)->get('/sso/ingresar?ticket=abc')->assertRedirect();
        $this->assertAuthenticatedAs($nuevo);
    }

    public function test_el_login_muestra_el_mensaje(): void
    {
        $this->canjeDevuelve(422, ['message' => 'Ticket inválido.']);

        $this->followingRedirects()->get('/sso/ingresar?ticket=abc')
            ->assertOk()
            ->assertSee(self::MENSAJE);
    }

    public function test_tiene_throttle(): void
    {
        $this->canjeDevuelve(422, ['message' => 'Ticket inválido.']);

        for ($i = 0; $i < 30; $i++) {
            $this->get('/sso/ingresar?ticket=abc')->assertRedirect(route('login'));
        }
        $this->get('/sso/ingresar?ticket=abc')->assertStatus(429);
    }
}
