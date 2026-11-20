<?php

namespace Tests\Feature\Usuarios;

use App\Livewire\AbmUsuarios;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Livewire\Volt\Volt;
use Tests\TestCase;

class DniYActivoTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['id_rol' => 1]);
    }

    public function test_los_usuarios_nuevos_y_los_existentes_quedan_activos_y_sin_dni(): void
    {
        DB::table('users')->insert(['name' => 'Viejo', 'email' => 'viejo@x.com', 'password' => 'x', 'id_rol' => 2]);

        $this->assertSame(1, (int) DB::table('users')->where('email', 'viejo@x.com')->value('activo'));
        $this->assertNull(DB::table('users')->where('email', 'viejo@x.com')->value('dni'));
        $this->assertTrue(User::factory()->make()->activo);
    }

    // ---------------------------------------------------------------- login propio

    public function test_un_inactivo_no_entra_con_su_clave_y_ve_el_mensaje_generico(): void
    {
        $u = User::factory()->create(['email' => 'inactivo@x.com', 'password' => Hash::make('clave-segura'), 'activo' => false]);

        Volt::test('pages.auth.login')
            ->set('form.email', 'inactivo@x.com')
            ->set('form.password', 'clave-segura')
            ->call('login')
            ->assertHasErrors(['form.email' => trans('auth.failed')]);

        $this->assertGuest();

        $u->update(['activo' => true]);
        Volt::test('pages.auth.login')
            ->set('form.email', 'inactivo@x.com')
            ->set('form.password', 'clave-segura')
            ->call('login')
            ->assertHasNoErrors();
        $this->assertAuthenticatedAs($u);
    }

    public function test_un_desactivado_pierde_la_sesion_abierta(): void
    {
        $u = User::factory()->create(['id_rol' => 2]);

        $this->actingAs($u)->get('/dashboard')->assertOk();

        $u->update(['activo' => false]);

        $this->actingAs($u->fresh())->get('/dashboard')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_al_desactivarlo_se_borran_sus_sesiones_y_su_recordarme(): void
    {
        config(['session.driver' => 'database']);
        $u = User::factory()->create(['remember_token' => 'token-viejo']);
        $otro = User::factory()->create();
        foreach ([[$u->id, 's1'], [$u->id, 's2'], [$otro->id, 's3']] as [$id, $sesion]) {
            DB::table('sessions')->insert(['id' => $sesion, 'user_id' => $id, 'payload' => '', 'last_activity' => time()]);
        }

        $u->update(['activo' => false]);

        $this->assertSame(['s3'], DB::table('sessions')->pluck('id')->all());
        $this->assertNull(DB::table('users')->where('id', $u->id)->value('remember_token'));
    }

    // ---------------------------------------------------------------- ABM

    public function test_el_abm_carga_y_guarda_dni_y_activo(): void
    {
        Livewire::actingAs($this->admin)->test(AbmUsuarios::class)
            ->call('abrirModal', 'crear')
            ->assertSet('activo', true)
            ->set('name', 'Ana')->set('email', 'ana@x.com')->set('id_rol', 2)
            ->set('password', 'clave-segura')->set('password_confirmation', 'clave-segura')
            ->set('dni', '20123456')
            ->call('guardar')
            ->assertHasNoErrors();

        $u = User::where('email', 'ana@x.com')->first();
        $this->assertSame(20123456, $u->dni);
        $this->assertTrue($u->activo);

        Livewire::actingAs($this->admin)->test(AbmUsuarios::class)
            ->call('abrirModal', 'editar', $u->id)
            ->assertSet('dni', '20123456')
            ->assertSet('activo', true)
            ->set('dni', '')
            ->set('activo', false)
            ->call('guardar')
            ->assertHasNoErrors();

        $u->refresh();
        $this->assertNull($u->dni);
        $this->assertFalse($u->activo);
    }

    public function test_el_dni_es_numerico_y_unico(): void
    {
        User::factory()->create(['dni' => 20123456]);

        $alta = fn ($dni) => Livewire::actingAs($this->admin)->test(AbmUsuarios::class)
            ->call('abrirModal', 'crear')
            ->set('name', 'Ana')->set('email', 'ana@x.com')->set('id_rol', 2)
            ->set('password', 'clave-segura')->set('password_confirmation', 'clave-segura')
            ->set('dni', $dni)
            ->call('guardar');

        $alta('20.123.456')->assertHasErrors(['dni' => 'integer']);
        $alta('20123456')->assertHasErrors(['dni' => 'unique'])->assertSee('Este DNI ya está en otro usuario');
        $alta('0')->assertHasErrors(['dni' => 'min']);
        $this->assertDatabaseMissing('users', ['email' => 'ana@x.com']);
    }

    public function test_nadie_desactiva_su_propio_usuario(): void
    {
        User::factory()->create(['id_rol' => 1]);  // hay otro administrador

        Livewire::actingAs($this->admin)->test(AbmUsuarios::class)
            ->call('abrirModal', 'editar', $this->admin->id)
            ->set('activo', false)
            ->call('guardar')
            ->assertHasErrors(['activo'])
            ->assertSee('No podés desactivar tu propio usuario')
            ->assertSet('modalAbierto', true);

        $this->assertTrue($this->admin->fresh()->activo);
    }

    public function test_no_se_desactiva_ni_se_degrada_al_ultimo_administrador_activo(): void
    {
        $editor = User::factory()->create(['id_rol' => 2]);
        $editor->permisos()->create(['modulo' => 'usuarios', 'nivel_acceso' => 'editar']);

        Livewire::actingAs($editor)->test(AbmUsuarios::class)
            ->call('abrirModal', 'editar', $this->admin->id)
            ->set('activo', false)
            ->call('guardar')
            ->assertHasErrors(['activo'])
            ->assertSee('Tiene que quedar al menos un Administrador activo');

        Livewire::actingAs($editor)->test(AbmUsuarios::class)
            ->call('abrirModal', 'editar', $this->admin->id)
            ->set('id_rol', 2)
            ->call('guardar')
            ->assertHasErrors(['id_rol']);

        Livewire::actingAs($editor)->test(AbmUsuarios::class)
            ->call('confirmarEliminar', $this->admin->id)
            ->call('eliminar')
            ->assertDispatched('toast', tipo: 'error', mensaje: 'No se puede eliminar: tiene que quedar al menos un Administrador activo.');

        $this->admin->refresh();
        $this->assertTrue($this->admin->activo);
        $this->assertSame(1, (int) $this->admin->id_rol);

        // Con otro administrador activo, sí se puede.
        User::factory()->create(['id_rol' => 1]);
        Livewire::actingAs($editor)->test(AbmUsuarios::class)
            ->call('abrirModal', 'editar', $this->admin->id)
            ->set('activo', false)
            ->call('guardar')
            ->assertHasNoErrors();
        $this->assertFalse($this->admin->fresh()->activo);
    }

    public function test_un_administrador_inactivo_no_cuenta_como_el_que_queda(): void
    {
        User::factory()->create(['id_rol' => 1, 'activo' => false]);
        $editor = User::factory()->create(['id_rol' => 2]);
        $editor->permisos()->create(['modulo' => 'usuarios', 'nivel_acceso' => 'editar']);

        Livewire::actingAs($editor)->test(AbmUsuarios::class)
            ->call('abrirModal', 'editar', $this->admin->id)
            ->set('activo', false)
            ->call('guardar')
            ->assertHasErrors(['activo']);
    }
}
