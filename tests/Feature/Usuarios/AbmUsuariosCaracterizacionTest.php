<?php

namespace Tests\Feature\Usuarios;

use App\Livewire\AbmUsuarios;
use App\Models\Corralon;
use App\Models\Deposito;
use App\Models\Rol;
use App\Models\User;
use App\Models\UsuarioPermiso;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Tests de caracterización de AbmUsuarios::guardar(): fijan lo que hace hoy la
 * pantalla (incluidas sus rarezas) antes de sacar el guardado a una Action.
 * Si alguno falla después de un refactor, cambió el comportamiento.
 */
class AbmUsuariosCaracterizacionTest extends TestCase
{
    use RefreshDatabase;

    // La migración de roles inserta 1 = Administrador y 2 = Visualizador.
    private const ADMIN = 1;
    private const VISUALIZADOR = 2;

    private User $admin;
    private Corralon $corralonA;
    private Corralon $corralonB;
    private Deposito $depositoA1;
    private Deposito $depositoA2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['id_rol' => self::ADMIN]);
        $this->corralonA = Corralon::create(['descripcion' => 'Corralón Centro']);
        $this->corralonB = Corralon::create(['descripcion' => 'Corralón Norte']);
        $this->depositoA1 = Deposito::create(['deposito' => 'Galpón 1', 'id_corralon' => $this->corralonA->id]);
        $this->depositoA2 = Deposito::create(['deposito' => 'Galpón 2', 'id_corralon' => $this->corralonA->id]);
    }

    private function nuevoUsuario(array $extra = []): array
    {
        return array_merge([
            'name' => 'Juan Pérez',
            'email' => 'juan@mercedes.gob.ar',
            'password' => 'clave-segura',
            'password_confirmation' => 'clave-segura',
            'id_rol' => self::VISUALIZADOR,
        ], $extra);
    }

    private function crearDesdePantalla(array $campos, ?User $quien = null)
    {
        $componente = Livewire::actingAs($quien ?? $this->admin)->test(AbmUsuarios::class)
            ->call('abrirModal', 'crear');

        foreach ($campos as $campo => $valor) {
            $componente->set($campo, $valor);
        }

        return $componente->call('guardar');
    }

    private function permisosDe(User $usuario): array
    {
        return UsuarioPermiso::where('id_usuario', $usuario->id)
            ->orderBy('modulo')->orderBy('id_corralon')->orderBy('id_deposito')
            ->get(['id_corralon', 'id_deposito', 'modulo', 'nivel_acceso'])
            ->map(fn ($p) => [$p->modulo, $p->id_corralon, $p->id_deposito, $p->nivel_acceso])
            ->all();
    }

    private function usuarioConPermisoUsuarios(string $nivel): User
    {
        $u = User::factory()->create(['id_rol' => self::VISUALIZADOR]);
        UsuarioPermiso::create(['id_usuario' => $u->id, 'id_corralon' => null, 'modulo' => 'usuarios', 'nivel_acceso' => $nivel]);

        return $u;
    }

    // ---------------------------------------------------------------- permisos del que guarda

    public function test_sin_permiso_de_editar_usuarios_no_abre_el_alta_ni_guarda(): void
    {
        $soloVer = $this->usuarioConPermisoUsuarios('ver');

        Livewire::actingAs($soloVer)->test(AbmUsuarios::class)
            ->call('abrirModal', 'crear')
            ->assertSet('modalAbierto', false)
            ->assertDispatched('toast', tipo: 'error', mensaje: 'No tienes permisos para crear usuarios.');

        Livewire::actingAs($soloVer)->test(AbmUsuarios::class)
            ->set('modo', 'crear')
            ->set('name', 'X')->set('email', 'x@x.com')->set('id_rol', self::VISUALIZADOR)
            ->set('password', 'clave-segura')->set('password_confirmation', 'clave-segura')
            ->call('guardar')
            ->assertDispatched('toast', tipo: 'error', mensaje: 'No tienes permisos para crear usuarios.')
            ->assertSet('modalAbierto', false);

        $this->assertDatabaseMissing('users', ['email' => 'x@x.com']);
    }

    public function test_sin_permiso_de_editar_usuarios_no_edita(): void
    {
        $soloVer = $this->usuarioConPermisoUsuarios('ver');
        $otro = User::factory()->create(['name' => 'Original']);

        Livewire::actingAs($soloVer)->test(AbmUsuarios::class)
            ->call('abrirModal', 'editar', $otro->id)
            ->assertSet('modalAbierto', false)
            ->assertDispatched('toast', tipo: 'error', mensaje: 'No tienes permisos para editar usuarios.');

        Livewire::actingAs($soloVer)->test(AbmUsuarios::class)
            ->set('modo', 'editar')->set('usuario_id', $otro->id)
            ->set('name', 'Cambiado')->set('email', $otro->email)->set('id_rol', self::VISUALIZADOR)
            ->call('guardar')
            ->assertDispatched('toast', tipo: 'error', mensaje: 'No tienes permisos para editar usuarios.');

        $this->assertSame('Original', $otro->fresh()->name);
    }

    public function test_con_permiso_global_de_editar_usuarios_crea(): void
    {
        $editor = $this->usuarioConPermisoUsuarios('editar');

        $this->crearDesdePantalla($this->nuevoUsuario(), $editor)
            ->assertHasNoErrors()
            ->assertDispatched('toast', tipo: 'exito', mensaje: 'Usuario creado exitosamente');

        $this->assertDatabaseHas('users', ['email' => 'juan@mercedes.gob.ar']);
    }

    // ---------------------------------------------------------------- validaciones

    public function test_alta_valida_campos_obligatorios_con_sus_mensajes(): void
    {
        $this->crearDesdePantalla([])
            ->assertHasErrors(['name', 'email', 'id_rol', 'password'])
            ->assertSee('El nombre es obligatorio')
            ->assertSee('El email es obligatorio')
            ->assertSee('Debe seleccionar un rol')
            ->assertSee('La contraseña es obligatoria');

        $this->assertSame(1, User::count());
    }

    public function test_alta_valida_formato_email_rol_y_clave(): void
    {
        $this->crearDesdePantalla($this->nuevoUsuario([
            'email' => 'no-es-mail', 'id_rol' => 999, 'password' => 'corta', 'password_confirmation' => 'corta',
        ]))->assertHasErrors(['email' => 'email', 'id_rol' => 'exists', 'password' => 'min']);

        $this->crearDesdePantalla($this->nuevoUsuario(['password_confirmation' => 'otra-distinta']))
            ->assertHasErrors(['password' => 'confirmed'])
            ->assertSee('Las contraseñas no coinciden');
    }

    public function test_alta_rechaza_email_repetido(): void
    {
        User::factory()->create(['email' => 'juan@mercedes.gob.ar']);

        $this->crearDesdePantalla($this->nuevoUsuario())
            ->assertHasErrors(['email' => 'unique'])
            ->assertSee('Este email ya está registrado');
    }

    public function test_edicion_permite_conservar_el_mismo_email_y_rechaza_el_de_otro(): void
    {
        $u = User::factory()->create(['email' => 'propio@x.com']);
        User::factory()->create(['email' => 'ajeno@x.com']);

        Livewire::actingAs($this->admin)->test(AbmUsuarios::class)
            ->call('abrirModal', 'editar', $u->id)
            ->set('name', 'Nuevo nombre')
            ->call('guardar')
            ->assertHasNoErrors()
            ->assertDispatched('toast', tipo: 'exito', mensaje: 'Usuario actualizado exitosamente');
        $this->assertSame('Nuevo nombre', $u->fresh()->name);

        Livewire::actingAs($this->admin)->test(AbmUsuarios::class)
            ->call('abrirModal', 'editar', $u->id)
            ->set('email', 'ajeno@x.com')
            ->call('guardar')
            ->assertHasErrors(['email' => 'unique']);
    }

    // ---------------------------------------------------------------- datos del usuario

    public function test_alta_guarda_datos_y_clave_hasheada_y_cierra_el_modal(): void
    {
        $this->crearDesdePantalla($this->nuevoUsuario())
            ->assertSet('modalAbierto', false)
            ->assertSet('name', '');

        $u = User::where('email', 'juan@mercedes.gob.ar')->first();
        $this->assertSame('Juan Pérez', $u->name);
        $this->assertSame(self::VISUALIZADOR, (int) $u->id_rol);
        $this->assertTrue(Hash::check('clave-segura', $u->password));
        $this->assertNull($u->email_verified_at);
    }

    public function test_clave_opcional_en_edicion(): void
    {
        $u = User::factory()->create(['password' => Hash::make('clave-vieja-1')]);

        Livewire::actingAs($this->admin)->test(AbmUsuarios::class)
            ->call('abrirModal', 'editar', $u->id)
            ->assertSet('password', '')
            ->set('name', 'Otro nombre')
            ->call('guardar')
            ->assertHasNoErrors();
        $this->assertTrue(Hash::check('clave-vieja-1', $u->fresh()->password));

        Livewire::actingAs($this->admin)->test(AbmUsuarios::class)
            ->call('abrirModal', 'editar', $u->id)
            ->set('password', 'corta')
            ->set('password_confirmation', 'corta')
            ->call('guardar')
            ->assertHasErrors(['password' => 'min']);

        Livewire::actingAs($this->admin)->test(AbmUsuarios::class)
            ->call('abrirModal', 'editar', $u->id)
            ->set('password', 'clave-nueva-2')
            ->set('password_confirmation', 'clave-nueva-2')
            ->call('guardar')
            ->assertHasNoErrors();
        $this->assertTrue(Hash::check('clave-nueva-2', $u->fresh()->password));
    }

    // ---------------------------------------------------------------- administrador

    public function test_administrador_tiene_acceso_a_todos_los_corralones_y_no_guarda_matriz(): void
    {
        $this->crearDesdePantalla($this->nuevoUsuario([
            'id_rol' => self::ADMIN,
            'corralones_seleccionados' => [(string) $this->corralonA->id],
            'permisos_globales' => ['choferes' => 'editar'],
            'permisos_por_corralon' => [(string) $this->corralonA->id => ['insumos' => 'ver']],
        ]))->assertHasNoErrors();

        $u = User::where('email', 'juan@mercedes.gob.ar')->first();
        $this->assertTrue($u->acceso_todos_corralones);
        $this->assertNull($u->corralones_permitidos);
        $this->assertSame([], $this->permisosDe($u));
    }

    public function test_pasar_a_administrador_borra_los_permisos_que_tenia(): void
    {
        $u = User::factory()->create(['id_rol' => self::VISUALIZADOR]);
        UsuarioPermiso::create(['id_usuario' => $u->id, 'id_corralon' => null, 'modulo' => 'choferes', 'nivel_acceso' => 'ver']);

        Livewire::actingAs($this->admin)->test(AbmUsuarios::class)
            ->call('abrirModal', 'editar', $u->id)
            ->set('id_rol', self::ADMIN)
            ->call('guardar')
            ->assertHasNoErrors();

        $u->refresh();
        $this->assertTrue($u->acceso_todos_corralones);
        $this->assertNull($u->corralones_permitidos);
        $this->assertSame([], $this->permisosDe($u));
    }

    // ---------------------------------------------------------------- no administrador

    public function test_no_admin_guarda_los_corralones_seleccionados_como_enteros(): void
    {
        $a = (string) $this->corralonA->id;
        $b = (string) $this->corralonB->id;

        // Un corralón seleccionado sin módulos igual queda en corralones_permitidos.
        $this->crearDesdePantalla($this->nuevoUsuario([
            'corralones_seleccionados' => [$a, $b],
            'permisos_por_corralon' => [$a => ['insumos' => 'ver'], $b => []],
        ]));

        $u = User::where('email', 'juan@mercedes.gob.ar')->first();
        $this->assertFalse($u->acceso_todos_corralones);
        $this->assertSame([$this->corralonA->id, $this->corralonB->id], $u->corralones_permitidos);
        $this->assertSame([['insumos', $this->corralonA->id, null, 'ver']], $this->permisosDe($u));
    }

    public function test_no_admin_sin_corralones_queda_con_lista_vacia(): void
    {
        $this->crearDesdePantalla($this->nuevoUsuario());

        $u = User::where('email', 'juan@mercedes.gob.ar')->first();
        $this->assertFalse($u->acceso_todos_corralones);
        $this->assertSame([], $u->corralones_permitidos);
        $this->assertSame([], $this->permisosDe($u));
    }

    public function test_permisos_globales_se_guardan_sin_corralon_ni_deposito(): void
    {
        $this->crearDesdePantalla($this->nuevoUsuario([
            'permisos_globales' => ['choferes' => 'editar', 'eventos' => 'ver', 'vehiculos' => 'ver'],
        ]));

        $u = User::where('email', 'juan@mercedes.gob.ar')->first();
        $this->assertSame([
            ['choferes', null, null, 'editar'],
            ['eventos', null, null, 'ver'],
            ['vehiculos', null, null, 'ver'],
        ], $this->permisosDe($u));
    }

    public function test_permiso_por_corralon_sin_depositos_especificos_es_para_todo_el_corralon(): void
    {
        $a = (string) $this->corralonA->id;
        $b = (string) $this->corralonB->id;

        $this->crearDesdePantalla($this->nuevoUsuario([
            'corralones_seleccionados' => [$a, $b],
            'permisos_por_corralon' => [
                $a => ['insumos' => 'editar', 'depositos' => 'ver'],
                $b => ['maquinarias' => 'ver'],
            ],
            // Una lista vacía de depósitos cuenta como "todos".
            'depositos_especificos' => [$a => ['insumos' => []]],
        ]));

        $u = User::where('email', 'juan@mercedes.gob.ar')->first();
        $this->assertSame([
            ['depositos', $this->corralonA->id, null, 'ver'],
            ['insumos', $this->corralonA->id, null, 'editar'],
            ['maquinarias', $this->corralonB->id, null, 'ver'],
        ], $this->permisosDe($u));
    }

    public function test_permiso_con_depositos_especificos_graba_una_fila_por_deposito(): void
    {
        $a = (string) $this->corralonA->id;

        $this->crearDesdePantalla($this->nuevoUsuario([
            'corralones_seleccionados' => [$a],
            'permisos_por_corralon' => [$a => ['insumos' => 'editar', 'maquinarias' => 'ver']],
            'depositos_especificos' => [$a => ['insumos' => [(string) $this->depositoA1->id, (string) $this->depositoA2->id]]],
        ]));

        $u = User::where('email', 'juan@mercedes.gob.ar')->first();
        $this->assertSame([
            ['insumos', $this->corralonA->id, $this->depositoA1->id, 'editar'],
            ['insumos', $this->corralonA->id, $this->depositoA2->id, 'editar'],
            ['maquinarias', $this->corralonA->id, null, 'ver'],
        ], $this->permisosDe($u));
    }

    public function test_modulo_mixto_con_permiso_global_saltea_el_permiso_por_corralon(): void
    {
        $a = (string) $this->corralonA->id;

        $this->crearDesdePantalla($this->nuevoUsuario([
            'permisos_globales' => ['vehiculos' => 'ver'],
            'corralones_seleccionados' => [$a],
            'permisos_por_corralon' => [$a => ['vehiculos' => 'editar', 'insumos' => 'ver']],
            'depositos_especificos' => [$a => ['vehiculos' => [(string) $this->depositoA1->id]]],
        ]));

        $u = User::where('email', 'juan@mercedes.gob.ar')->first();
        $this->assertSame([
            ['insumos', $this->corralonA->id, null, 'ver'],
            ['vehiculos', null, null, 'ver'],
        ], $this->permisosDe($u));
    }

    public function test_modulo_mixto_sin_permiso_global_se_guarda_por_corralon(): void
    {
        $a = (string) $this->corralonA->id;

        $this->crearDesdePantalla($this->nuevoUsuario([
            'corralones_seleccionados' => [$a],
            'permisos_por_corralon' => [$a => ['vehiculos' => 'editar']],
        ]));

        $u = User::where('email', 'juan@mercedes.gob.ar')->first();
        $this->assertSame([['vehiculos', $this->corralonA->id, null, 'editar']], $this->permisosDe($u));
    }

    // ---------------------------------------------------------------- edición

    public function test_edicion_carga_los_permisos_en_la_pantalla(): void
    {
        $u = User::factory()->create(['id_rol' => self::VISUALIZADOR]);
        $a = $this->corralonA->id;
        foreach ([
            [null, null, 'choferes', 'editar'],
            [null, null, 'vehiculos', 'ver'],
            [$a, null, 'maquinarias', 'ver'],
            [$a, $this->depositoA1->id, 'insumos', 'editar'],
            [$a, $this->depositoA2->id, 'insumos', 'editar'],
        ] as [$c, $d, $m, $n]) {
            UsuarioPermiso::create(['id_usuario' => $u->id, 'id_corralon' => $c, 'id_deposito' => $d, 'modulo' => $m, 'nivel_acceso' => $n]);
        }

        Livewire::actingAs($this->admin)->test(AbmUsuarios::class)
            ->call('abrirModal', 'editar', $u->id)
            ->assertSet('modo', 'editar')
            ->assertSet('usuario_id', $u->id)
            ->assertSet('modalAbierto', true)
            ->assertSet('permisos_globales', ['choferes' => 'editar', 'vehiculos' => 'ver'])
            ->assertSet('corralones_seleccionados', [(string) $a])
            ->assertSet('permisos_por_corralon', [(string) $a => ['maquinarias' => 'ver', 'insumos' => 'editar']])
            ->assertSet('depositos_especificos', [(string) $a => ['insumos' => [(string) $this->depositoA1->id, (string) $this->depositoA2->id]]]);
    }

    public function test_edicion_reemplaza_todos_los_permisos(): void
    {
        $u = User::factory()->create(['id_rol' => self::VISUALIZADOR]);
        $a = (string) $this->corralonA->id;
        $b = (string) $this->corralonB->id;
        UsuarioPermiso::create(['id_usuario' => $u->id, 'id_corralon' => null, 'modulo' => 'choferes', 'nivel_acceso' => 'editar']);
        UsuarioPermiso::create(['id_usuario' => $u->id, 'id_corralon' => $this->corralonA->id, 'modulo' => 'insumos', 'nivel_acceso' => 'ver']);

        Livewire::actingAs($this->admin)->test(AbmUsuarios::class)
            ->call('abrirModal', 'editar', $u->id)
            ->call('toggleModuloGlobal', 'choferes', 'editar')     // lo apaga
            ->call('toggleModuloGlobal', 'eventos', 'ver')
            ->call('toggleCorralon', $a)                            // saca A con sus permisos
            ->call('toggleCorralon', $b)
            ->call('toggleModuloCorralon', $b, 'maquinarias', 'editar')
            ->call('guardar')
            ->assertHasNoErrors();

        $u->refresh();
        $this->assertSame([$this->corralonB->id], $u->corralones_permitidos);
        $this->assertSame([
            ['eventos', null, null, 'ver'],
            ['maquinarias', $this->corralonB->id, null, 'editar'],
        ], $this->permisosDe($u));
    }

    public function test_edicion_sin_cambios_conserva_los_permisos(): void
    {
        $u = User::factory()->create(['id_rol' => self::VISUALIZADOR]);
        UsuarioPermiso::create(['id_usuario' => $u->id, 'id_corralon' => null, 'modulo' => 'vehiculos', 'nivel_acceso' => 'ver']);
        UsuarioPermiso::create(['id_usuario' => $u->id, 'id_corralon' => $this->corralonA->id, 'id_deposito' => $this->depositoA1->id, 'modulo' => 'insumos', 'nivel_acceso' => 'editar']);
        $antes = $this->permisosDe($u);

        Livewire::actingAs($this->admin)->test(AbmUsuarios::class)
            ->call('abrirModal', 'editar', $u->id)
            ->call('guardar')
            ->assertHasNoErrors();

        $this->assertSame($antes, $this->permisosDe($u));
    }

    // ---------------------------------------------------------------- errores

    public function test_un_error_al_guardar_permisos_revierte_todo_y_avisa(): void
    {
        // Un corralón inexistente rompe la FK de usuario_permisos: no queda ni el usuario.
        $this->crearDesdePantalla($this->nuevoUsuario([
            'corralones_seleccionados' => ['9999'],
            'permisos_por_corralon' => ['9999' => ['insumos' => 'ver']],
        ]))->assertDispatched('toast', fn ($evento, $params) => $params['tipo'] === 'error'
            && str_starts_with($params['mensaje'], 'Error al guardar: '))
            ->assertSet('modalAbierto', false);

        $this->assertDatabaseMissing('users', ['email' => 'juan@mercedes.gob.ar']);
    }
}
