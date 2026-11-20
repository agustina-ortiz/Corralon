<?php

namespace Tests\Feature\Actividad;

use App\Actions\Usuarios\GuardarUsuario;
use App\Livewire\AbmUsuarios;
use App\Livewire\Actividad;
use App\Models\Corralon;
use App\Models\User;
use App\Models\UsuarioPermiso;
use App\Support\ActorIntranet;
use App\Support\SinActividad;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Spatie\Activitylog\ActivityLogStatus;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class ActividadTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Corralon $corralon;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = SinActividad::correr(fn () => User::factory()->create(['id_rol' => 1, 'name' => 'Admin General']));
        $this->corralon = Corralon::create(['descripcion' => 'Corralón Centro']);
    }

    private function crearDesdePantalla(array $extra = [])
    {
        $c = Livewire::actingAs($this->admin)->test(AbmUsuarios::class)->call('abrirModal', 'crear');
        foreach (array_merge([
            'name' => 'Juan Pérez', 'email' => 'juan@x.com', 'id_rol' => 2,
            'password' => 'clave-segura', 'password_confirmation' => 'clave-segura',
        ], $extra) as $campo => $valor) {
            $c->set($campo, $valor);
        }

        return $c->call('guardar')->assertHasNoErrors();
    }

    public function test_el_alta_de_un_usuario_queda_registrada_sin_la_clave(): void
    {
        $this->crearDesdePantalla();
        $u = User::where('email', 'juan@x.com')->first();

        $a = Activity::where('subject_type', User::class)->where('subject_id', $u->id)->sole();
        $this->assertSame('created', $a->event);
        $this->assertSame('Juan Pérez', $a->description);
        $this->assertSame('juan@x.com', $a->contexto);
        $this->assertSame($this->admin->id, $a->causer_id);
        $this->assertArrayNotHasKey('password', $a->properties['attributes']);
        $this->assertArrayNotHasKey('remember_token', $a->properties['attributes']);
        $this->assertSame('juan@x.com', $a->properties['attributes']['email']);
    }

    public function test_una_edicion_registra_solo_lo_que_cambio_y_sin_cambios_no_deja_nada(): void
    {
        $this->crearDesdePantalla();
        $u = User::where('email', 'juan@x.com')->first();
        Activity::query()->delete();

        Livewire::actingAs($this->admin)->test(AbmUsuarios::class)
            ->call('abrirModal', 'editar', $u->id)->call('guardar')->assertHasNoErrors();
        $this->assertSame(0, Activity::count());

        Livewire::actingAs($this->admin)->test(AbmUsuarios::class)
            ->call('abrirModal', 'editar', $u->id)->set('name', 'Juan P. Pérez')->call('guardar');

        $a = Activity::sole();
        $this->assertSame('updated', $a->event);
        $this->assertSame(['name' => 'Juan P. Pérez'], $a->properties['attributes']);
        $this->assertSame(['name' => 'Juan Pérez'], $a->properties['old']);
    }

    public function test_los_permisos_se_registran_al_darlos_cambiarlos_y_sacarlos(): void
    {
        $a = (string) $this->corralon->id;
        $this->crearDesdePantalla([
            'corralones_seleccionados' => [$a],
            'permisos_por_corralon' => [$a => ['insumos' => 'ver']],
            'permisos_globales' => ['choferes' => 'editar'],
        ]);
        $u = User::where('email', 'juan@x.com')->first();

        $altas = Activity::where('subject_type', UsuarioPermiso::class)->where('event', 'created')->pluck('description')->sort()->values()->all();
        $this->assertSame(['Permiso: Choferes', 'Permiso: Insumos'], $altas);
        $this->assertSame('Juan Pérez · Corralón Centro',
            Activity::where('description', 'Permiso: Insumos')->value('contexto'));

        Activity::query()->delete();
        Livewire::actingAs($this->admin)->test(AbmUsuarios::class)
            ->call('abrirModal', 'editar', $u->id)
            ->call('toggleModuloCorralon', $a, 'insumos', 'editar')   // ver → editar
            ->call('toggleModuloGlobal', 'choferes', 'editar')        // lo saca
            ->call('guardar');

        $this->assertSame(['deleted', 'updated'], Activity::where('subject_type', UsuarioPermiso::class)->orderBy('event')->pluck('event')->all());
        $cambio = Activity::where('event', 'updated')->sole();
        $this->assertSame(['nivel_acceso' => 'editar'], $cambio->properties['attributes']);
    }

    public function test_las_preferencias_del_dashboard_no_dejan_actividad(): void
    {
        $this->admin->fresh()->update(['dashboard_widgets' => ['cards' => ['insumos']]]);

        $this->assertSame(0, Activity::count());
    }

    public function test_sin_actividad_apaga_el_registro_y_lo_devuelve_aunque_falle(): void
    {
        SinActividad::correr(fn () => User::factory()->create());
        $this->assertSame(0, Activity::count());

        try {
            SinActividad::correr(function () {
                throw new \RuntimeException('falla');
            });
        } catch (\RuntimeException) {
        }

        $this->assertTrue(! app(ActivityLogStatus::class)->disabled());
        User::factory()->create();
        $this->assertSame(1, Activity::count());
    }

    public function test_el_rehash_de_claves_no_deja_actividad(): void
    {
        DB::table('users')->insert(['name' => 'Viejo', 'email' => 'viejo@x.com', 'password' => 'texto-plano', 'id_rol' => 2]);

        Artisan::call('users:rehash-passwords');

        $this->assertStringStartsWith('$2y$', DB::table('users')->where('email', 'viejo@x.com')->value('password'));
        $this->assertSame(0, Activity::count());
        $this->assertTrue(! app(ActivityLogStatus::class)->disabled());
    }

    public function test_si_no_se_puede_registrar_la_actividad_el_guardado_sigue(): void
    {
        Schema::drop('activity_log');

        $usuario = app(GuardarUsuario::class)->ejecutar(null, [
            'name' => 'Sin auditoría', 'email' => 'sin@x.com', 'id_rol' => 2, 'password' => 'clave-segura',
            'permisos_globales' => ['choferes' => 'ver'],
        ]);

        $this->assertDatabaseHas('users', ['email' => 'sin@x.com']);
        $this->assertDatabaseHas('usuario_permisos', ['id_usuario' => $usuario->id, 'modulo' => 'choferes']);
    }

    public function test_lo_hecho_desde_la_intranet_queda_sin_causer_y_con_el_actor(): void
    {
        $actor = new ActorIntranet(20123456, 'Pérez, Ana');

        $this->actingAs($this->admin);  // aunque haya sesión, el autor es el actor
        $actor->registrando(fn () => app(GuardarUsuario::class)->ejecutar(null, [
            'name' => 'Desde intranet', 'email' => 'intra@x.com', 'id_rol' => 2, 'password' => 'clave-segura',
        ]));

        $a = Activity::sole();
        $this->assertNull($a->causer_id);
        $this->assertSame('intranet', $a->properties['origen']);
        $this->assertSame(20123456, $a->properties['actor_dni']);
        $this->assertSame('Pérez, Ana', $a->properties['actor_nombre']);
        $this->assertNull(ActorIntranet::actual());
    }

    // ---------------------------------------------------------------- pantalla

    public function test_la_pantalla_la_ven_solo_los_administradores(): void
    {
        $this->get(route('actividad'))->assertRedirect(route('login'));

        $comun = SinActividad::correr(fn () => User::factory()->create(['id_rol' => 2]));
        UsuarioPermiso::create(['id_usuario' => $comun->id, 'modulo' => 'usuarios', 'nivel_acceso' => 'editar']);
        $this->actingAs($comun)->get(route('actividad'))->assertForbidden();

        $this->actingAs($this->admin)->get(route('actividad'))->assertOk()->assertSee('Actividad');
    }

    public function test_la_pantalla_muestra_quien_hizo_cada_cosa_y_filtra(): void
    {
        $this->crearDesdePantalla();
        (new ActorIntranet(20123456, 'Pérez, Ana'))->registrando(fn () => app(GuardarUsuario::class)->ejecutar(null, [
            'name' => 'Desde intranet', 'email' => 'intra@x.com', 'id_rol' => 2, 'password' => 'clave-segura',
        ]));

        Livewire::actingAs($this->admin)->test(Actividad::class)
            ->assertSee('juan@x.com')
            ->assertSee('Admin General')
            ->assertSee('Intranet · Pérez, Ana (DNI 20123456)')
            ->assertSee('Creó')
            ->set('filtroUsuario', 'intranet')
            ->assertSee('intra@x.com')
            ->assertDontSee('juan@x.com')
            ->set('filtroUsuario', (string) $this->admin->id)
            ->assertSee('juan@x.com')
            ->assertDontSee('intra@x.com')
            ->set('filtroUsuario', '')
            ->set('buscar', 'Pérez, Ana')
            ->assertSee('intra@x.com')
            ->assertDontSee('juan@x.com')
            ->set('buscar', '')
            ->set('filtroEvento', 'deleted')
            ->assertSee('No hay actividad que coincida con los filtros.');
    }
}
