<?php

namespace Tests\Feature\Intranet;

use App\Models\Corralon;
use App\Models\Deposito;
use App\Models\Rol;
use App\Models\User;
use App\Models\UsuarioPermiso;
use App\Support\SinActividad;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class ApiUsuariosTest extends TestCase
{
    use RefreshDatabase;

    private const ACTOR = ['dni' => 20123456, 'nombre' => 'Pérez, Ana'];

    private string $clave;
    private Corralon $corralon;
    private Deposito $deposito;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clave = str_repeat('s', 64);
        config(['intranet.clave_servicio' => $this->clave, 'intranet.ips' => [], 'intranet.url' => 'http://intranet.test']);

        $this->corralon = Corralon::create(['descripcion' => 'Corralón Centro']);
        $this->deposito = Deposito::create(['deposito' => 'Galpón 1', 'id_corralon' => $this->corralon->id]);
    }

    private function api(string $metodo, string $uri, array $datos = [], ?string $clave = null)
    {
        return $this->json($metodo, '/api/intranet/v1/'.$uri, $datos, ['Authorization' => 'Bearer '.($clave ?? $this->clave)]);
    }

    private function alta(array $extra = []): array
    {
        return array_merge([
            'dni' => 30111222,
            'name' => 'Carlos Gómez',
            'email' => 'carlos@mercedes.gob.ar',
            'password' => 'clave-segura',
            'password_confirmation' => 'clave-segura',
            'id_rol' => 2,
            'actor' => self::ACTOR,
        ], $extra);
    }

    // ---------------------------------------------------------------- puerta

    public function test_sin_clave_o_con_clave_mala_da_401(): void
    {
        $this->getJson('/api/intranet/v1/alta/opciones')->assertStatus(401)->assertExactJson(['message' => 'No autorizado.']);
        $this->api('GET', 'alta/opciones', [], 'otra-clave')->assertStatus(401)->assertExactJson(['message' => 'No autorizado.']);
        $this->api('POST', 'usuarios', $this->alta(), 'otra-clave')->assertStatus(401);
        $this->assertDatabaseMissing('users', ['email' => 'carlos@mercedes.gob.ar']);
    }

    public function test_sin_clave_configurada_rechaza_todo(): void
    {
        config(['intranet.clave_servicio' => '']);

        $this->json('GET', '/api/intranet/v1/alta/opciones', [], ['Authorization' => 'Bearer '])->assertStatus(401);
    }

    public function test_ip_fuera_de_la_lista_da_403(): void
    {
        config(['intranet.ips' => ['10.0.0.5']]);
        $this->api('GET', 'alta/opciones')->assertStatus(403);

        config(['intranet.ips' => ['10.0.0.5', '127.0.0.1']]);
        $this->api('GET', 'alta/opciones')->assertOk();
    }

    public function test_tiene_throttle(): void
    {
        for ($i = 0; $i < 60; $i++) {
            $this->api('GET', 'usuarios/1')->assertStatus(404);
        }
        $this->api('GET', 'usuarios/1')->assertStatus(429);
    }

    public function test_no_usa_sesion_ni_csrf(): void
    {
        $this->api('POST', 'usuarios', $this->alta())->assertCreated();
        $this->assertGuest();
    }

    // ---------------------------------------------------------------- opciones

    public function test_opciones_trae_los_campos_y_las_listas_del_modal(): void
    {
        Rol::create(['nombre' => 'Encargado', 'descripcion' => 'Encargado de corralón']);

        $r = $this->api('GET', 'alta/opciones')->assertOk();

        $this->assertSame(
            ['name', 'email', 'password', 'password_confirmation', 'id_rol', 'permisos_globales', 'corralones_seleccionados', 'permisos_por_corralon', 'depositos_especificos'],
            array_column($r->json('campos'), 'nombre'),
        );
        $roles = collect($r->json('campos'))->firstWhere('nombre', 'id_rol')['opciones'];
        $this->assertSame(['Encargado', 'Visualizador'], array_column($roles, 'nombre'));  // sin Administrador
        $this->assertSame([['valor' => 'ver', 'etiqueta' => 'Solo ver'], ['valor' => 'editar', 'etiqueta' => 'Ver y editar']], $r->json('niveles'));
        $this->assertSame([[
            'id' => $this->corralon->id, 'nombre' => 'Corralón Centro',
            'depositos' => [['id' => $this->deposito->id, 'nombre' => 'Galpón 1']],
        ]], $r->json('corralones'));

        $globales = collect($r->json('campos'))->firstWhere('nombre', 'permisos_globales')['modulos'];
        $this->assertSame(['clave' => 'vehiculos', 'etiqueta' => 'Vehículos (todos)', 'mixto' => true], $globales[0]);
        $this->assertContains('usuarios', array_column($globales, 'clave'));
        $porCorralon = collect($r->json('campos'))->firstWhere('nombre', 'permisos_por_corralon')['modulos'];
        $this->assertSame(UsuarioPermiso::MODULOS_POR_UBICACION, array_column($porCorralon, 'clave'));
    }

    // ---------------------------------------------------------------- ver

    public function test_ver_por_dni(): void
    {
        $u = User::factory()->create(['dni' => 30111222, 'name' => 'Carlos', 'email' => 'c@x.com']);

        $this->api('GET', 'usuarios/30111222')->assertOk()
            ->assertExactJson(['id' => $u->id, 'dni' => 30111222, 'nombre' => 'Carlos', 'email' => 'c@x.com', 'activo' => true]);
        $this->api('GET', 'usuarios/99999')->assertNotFound()->assertExactJson(['message' => 'No existe.']);
    }

    // ---------------------------------------------------------------- alta

    public function test_alta_crea_el_usuario_con_la_misma_logica_que_la_pantalla(): void
    {
        $c = (string) $this->corralon->id;

        $r = $this->api('POST', 'usuarios', $this->alta([
            'permisos_globales' => ['vehiculos' => 'ver', 'choferes' => 'editar'],
            'permisos_por_corralon' => [$c => ['insumos' => 'editar', 'vehiculos' => 'editar', 'maquinarias' => 'ver']],
            'depositos_especificos' => [$c => ['insumos' => [$this->deposito->id]]],
            'es_superadmin' => true,
            'acceso_todos_corralones' => true,
        ]))->assertCreated();

        $u = User::findOrFail($r->json('id'));
        $this->assertSame(['id' => $u->id], $r->json());
        $this->assertSame(30111222, $u->dni);
        $this->assertTrue($u->activo);
        $this->assertFalse($u->acceso_todos_corralones);
        $this->assertSame([$this->corralon->id], $u->corralones_permitidos);  // se suma solo
        $this->assertTrue(Hash::check('clave-segura', $u->password));

        $permisos = $u->permisos()->orderBy('modulo')->get()
            ->map(fn ($p) => [$p->modulo, $p->id_corralon, $p->id_deposito, $p->nivel_acceso])->all();
        $this->assertSame([
            ['choferes', null, null, 'editar'],
            ['insumos', $this->corralon->id, $this->deposito->id, 'editar'],
            ['maquinarias', $this->corralon->id, null, 'ver'],
            ['vehiculos', null, null, 'ver'],   // el mixto global saltea el del corralón
        ], $permisos);
    }

    public function test_el_usuario_creado_entra_y_tiene_sus_permisos(): void
    {
        $this->api('POST', 'usuarios', $this->alta(['permisos_globales' => ['usuarios' => 'ver']]))->assertCreated();
        $u = User::where('dni', 30111222)->first();

        $this->assertTrue($u->tieneAccesoAModulo('usuarios'));
        $this->assertFalse($u->puedeEditarUsuarios());
    }

    public function test_alta_invalida_da_422_con_los_campos_del_modal(): void
    {
        User::factory()->create(['email' => 'carlos@mercedes.gob.ar']);

        $this->api('POST', 'usuarios', $this->alta([
            'name' => '', 'password_confirmation' => 'otra', 'id_rol' => 999,
        ]))->assertStatus(422)
            ->assertJsonStructure(['message', 'errors'])
            ->assertJsonValidationErrors(['name', 'email', 'password', 'id_rol'])
            ->assertJsonPath('errors.email.0', 'Este email ya está registrado')
            ->assertJsonPath('errors.password.0', 'Las contraseñas no coinciden');
    }

    public function test_dni_obligatorio_y_unico(): void
    {
        $this->api('POST', 'usuarios', $this->alta(['dni' => null]))->assertStatus(422)->assertJsonValidationErrors(['dni']);

        User::factory()->create(['dni' => 30111222]);
        $this->api('POST', 'usuarios', $this->alta())->assertStatus(422)
            ->assertJsonPath('errors.dni.0', 'Este DNI ya está en otro usuario');
    }

    public function test_sin_actor_valido_da_422_en_actor(): void
    {
        $this->api('POST', 'usuarios', $this->alta(['actor' => null]))->assertStatus(422)->assertJsonValidationErrors(['actor']);
        $this->api('POST', 'usuarios', $this->alta(['actor' => ['dni' => 'x', 'nombre' => 'A']]))->assertStatus(422)->assertJsonValidationErrors(['actor']);
        $this->api('PATCH', 'usuarios/30111222/estado', ['activo' => false])->assertStatus(422)->assertJsonValidationErrors(['actor']);
        $this->assertSame(0, User::count());
    }

    public function test_desde_la_api_no_se_crea_un_administrador(): void
    {
        $this->api('POST', 'usuarios', $this->alta(['id_rol' => 1]))->assertStatus(422)
            ->assertJsonPath('errors.id_rol.0', 'Desde la intranet no se pueden crear usuarios Administradores.');
        $this->assertSame(0, User::count());
    }

    public function test_matriz_con_modulos_corralones_o_depositos_desconocidos_da_422(): void
    {
        $c = (string) $this->corralon->id;
        $otro = Corralon::create(['descripcion' => 'Otro']);
        $ajeno = Deposito::create(['deposito' => 'Ajeno', 'id_corralon' => $otro->id]);

        $this->api('POST', 'usuarios', $this->alta(['permisos_globales' => ['insumos' => 'ver']]))
            ->assertStatus(422)->assertJsonValidationErrors(['permisos_globales']);
        $this->api('POST', 'usuarios', $this->alta(['permisos_globales' => ['choferes' => 'admin']]))
            ->assertStatus(422)->assertJsonValidationErrors(['permisos_globales.choferes']);
        $this->api('POST', 'usuarios', $this->alta(['permisos_por_corralon' => ['9999' => ['insumos' => 'ver']]]))
            ->assertStatus(422)->assertJsonValidationErrors(['corralones_seleccionados']);
        $this->api('POST', 'usuarios', $this->alta(['permisos_por_corralon' => [$c => ['choferes' => 'ver']]]))
            ->assertStatus(422)->assertJsonValidationErrors(["permisos_por_corralon.{$c}"]);
        $this->api('POST', 'usuarios', $this->alta([
            'permisos_por_corralon' => [$c => ['insumos' => 'ver']],
            'depositos_especificos' => [$c => ['insumos' => [$ajeno->id]]],
        ]))->assertStatus(422)->assertJsonValidationErrors(["depositos_especificos.{$c}.insumos"]);

        $this->assertSame(0, User::count());
    }

    public function test_el_alta_queda_en_actividad_como_hecha_desde_la_intranet(): void
    {
        $this->api('POST', 'usuarios', $this->alta(['permisos_globales' => ['choferes' => 'ver']]))->assertCreated();

        $actividades = Activity::all();
        $this->assertCount(2, $actividades);  // el usuario y su permiso
        foreach ($actividades as $a) {
            $this->assertNull($a->causer_id);
            $this->assertSame('intranet', $a->properties['origen']);
            $this->assertSame(20123456, $a->properties['actor_dni']);
            $this->assertSame('Pérez, Ana', $a->properties['actor_nombre']);
        }

        $admin = SinActividad::correr(fn () => User::factory()->create(['id_rol' => 1]));
        Livewire::actingAs($admin)->test(\App\Livewire\Actividad::class)
            ->assertSee('Intranet · Pérez, Ana (DNI 20123456)');
    }

    // ---------------------------------------------------------------- vínculo

    public function test_vincula_el_dni_a_un_usuario_existente_por_email(): void
    {
        $u = User::factory()->create(['email' => 'existe@x.com', 'id_rol' => 2]);

        $this->api('PATCH', 'usuarios/30111222/vinculo', ['email' => 'existe@x.com', 'actor' => self::ACTOR])
            ->assertOk()->assertExactJson(['id' => $u->id]);
        $this->assertSame(30111222, $u->fresh()->dni);

        $a = Activity::where('event', 'updated')->sole();
        $this->assertSame(['dni' => 30111222], $a->properties['attributes']);
        $this->assertSame('intranet', $a->properties['origen']);

        // Repetirlo no cambia nada ni deja actividad.
        $this->api('PATCH', 'usuarios/30111222/vinculo', ['email' => 'existe@x.com', 'actor' => self::ACTOR])->assertOk();
        $this->assertSame(1, Activity::where('event', 'updated')->count());
    }

    public function test_vinculo_errores(): void
    {
        User::factory()->create(['email' => 'otrodni@x.com', 'dni' => 11111111]);
        User::factory()->create(['email' => 'libre@x.com']);
        User::factory()->create(['email' => 'tiene@x.com', 'dni' => 30111222]);
        User::factory()->create(['email' => 'admin@x.com', 'id_rol' => 1]);

        $vincular = fn ($dni, $email) => $this->api('PATCH', "usuarios/{$dni}/vinculo", ['email' => $email, 'actor' => self::ACTOR]);

        $vincular(30111222, 'nadie@x.com')->assertNotFound()->assertExactJson(['message' => 'No existe.']);
        $vincular(22222222, 'otrodni@x.com')->assertStatus(422)->assertJsonValidationErrors(['email']);
        $vincular(30111222, 'libre@x.com')->assertStatus(422)->assertJsonValidationErrors(['dni']);
        $vincular(33333333, 'admin@x.com')->assertStatus(422)->assertJsonValidationErrors(['email']);
        $vincular(33333333, '')->assertStatus(422)->assertJsonValidationErrors(['email']);
    }

    // ---------------------------------------------------------------- estado

    public function test_desactiva_y_reactiva_un_usuario(): void
    {
        $u = User::factory()->create(['dni' => 30111222, 'id_rol' => 2]);

        $this->api('PATCH', 'usuarios/30111222/estado', ['activo' => false, 'actor' => self::ACTOR])
            ->assertOk()->assertExactJson(['id' => $u->id, 'activo' => false]);
        $this->assertFalse($u->fresh()->activo);

        $a = Activity::where('event', 'updated')->sole();
        $this->assertSame(['activo' => false], $a->properties['attributes']);
        $this->assertSame('Pérez, Ana', $a->properties['actor_nombre']);

        $this->api('PATCH', 'usuarios/30111222/estado', ['activo' => true, 'actor' => self::ACTOR])
            ->assertOk()->assertExactJson(['id' => $u->id, 'activo' => true]);
        $this->assertTrue($u->fresh()->activo);
    }

    public function test_estado_errores(): void
    {
        User::factory()->create(['dni' => 30111222, 'id_rol' => 1]);
        User::factory()->create(['id_rol' => 1]);  // aunque haya otro administrador

        $this->api('PATCH', 'usuarios/99999999/estado', ['activo' => false, 'actor' => self::ACTOR])->assertNotFound();
        $this->api('PATCH', 'usuarios/30111222/estado', ['activo' => false, 'actor' => self::ACTOR])
            ->assertStatus(422)->assertJsonPath('errors.activo.0', 'Desde la intranet no se puede activar ni desactivar a un Administrador.');
        $this->api('PATCH', 'usuarios/30111222/estado', ['activo' => 'quizas', 'actor' => self::ACTOR])
            ->assertStatus(422)->assertJsonValidationErrors(['activo']);
        $this->assertTrue(User::where('dni', 30111222)->value('activo'));
    }

    public function test_el_desactivado_por_la_intranet_no_entra_por_sso(): void
    {
        User::factory()->create(['dni' => 30111222, 'id_rol' => 2]);
        $this->api('PATCH', 'usuarios/30111222/estado', ['activo' => false, 'actor' => self::ACTOR])->assertOk();

        \Illuminate\Support\Facades\Http::fake(['http://intranet.test/api/sso/v1/canjes' => \Illuminate\Support\Facades\Http::response(['dni' => 30111222, 'apellido' => 'G', 'nombre' => 'C'])]);
        $this->get('/sso/ingresar?ticket=abc')->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
