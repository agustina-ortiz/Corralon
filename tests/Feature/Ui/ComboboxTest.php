<?php

namespace Tests\Feature\Ui;

use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * <x-ui.combobox> (kit, spec 16, adaptado al Corralón): modo lista y modo
 * servidor. Sin base de datos.
 */
class ComboboxTest extends TestCase
{
    /**
     * Componente de prueba. Las opciones y los atributos extra se fijan por
     * static porque Livewire instancia la clase sin argumentos.
     */
    private function componente(): Component
    {
        return new class extends Component
        {
            public static mixed $opciones = [];

            public static string $extra = '';

            public static string $modelo = 'wire:model="valor"';

            public $valor = '';

            public $busqueda = '';

            public $elegido = null;

            public function elegir($valor): void
            {
                $this->elegido = $valor;
            }

            public function render(): string
            {
                return '<div><x-ui.combobox '.static::$modelo.' :opciones="$this->opciones()" '.static::$extra.' /></div>';
            }

            public function opciones(): mixed
            {
                return static::$opciones;
            }
        };
    }

    private function html(mixed $opciones, string $extra = '', string $modelo = 'wire:model="valor"'): string
    {
        $componente = $this->componente();
        $componente::$opciones = $opciones;
        $componente::$extra = $extra;
        $componente::$modelo = $modelo;

        return html_entity_decode(Livewire::test($componente)->html());
    }

    /** @return list<array<string, mixed>> opciones en el orden de data-opciones */
    private function opcionesDe(string $html): array
    {
        preg_match('/data-opciones="(.*?)"\s+data-disabled/s', $html, $m);

        return json_decode($m[1], true);
    }

    /** @return list<string> */
    private function textos(string $html): array
    {
        return array_column($this->opcionesDe($html), 'texto');
    }

    public function test_ordena_alfabeticamente_transliterando_tildes(): void
    {
        $html = $this->html(['b' => 'Ébano', 'a' => 'Zapato', 'c' => 'ardilla', 'd' => 'Índice']);

        $this->assertSame(['ardilla', 'Ébano', 'Índice', 'Zapato'], $this->textos($html));
    }

    public function test_ordenar_false_respeta_el_orden_semantico(): void
    {
        $html = $this->html(['zeta', 'alfa', 'beta'], ':ordenar="false"');

        $this->assertSame(['zeta', 'alfa', 'beta'], $this->textos($html));
    }

    public function test_acepta_una_lista_de_modelos_o_arrays_con_clave_y_etiqueta(): void
    {
        $html = $this->html(collect([
            ['id' => 7, 'secretaria' => 'Obras Públicas'],
            ['id' => 3, 'secretaria' => 'Economía'],
        ]), 'etiqueta="secretaria"');

        $this->assertSame([['valor' => 3, 'texto' => 'Economía'], ['valor' => 7, 'texto' => 'Obras Públicas']], $this->opcionesDe($html));
    }

    public function test_lista_de_cadenas_usa_la_cadena_como_valor(): void
    {
        $html = $this->html(['Mantenimiento', 'Bacheo']);

        $this->assertSame([['valor' => 'Bacheo', 'texto' => 'Bacheo'], ['valor' => 'Mantenimiento', 'texto' => 'Mantenimiento']], $this->opcionesDe($html));
    }

    public function test_respeta_wire_model_y_wire_model_live_via_entangle(): void
    {
        $normal = $this->html(['a' => 'A']);
        $live = $this->html(['a' => 'A'], '', 'wire:model.live="valor"');

        $this->assertMatchesRegularExpression("/entangle\('valor'\)(?!\.live)/", $normal);
        $this->assertStringContainsString("entangle('valor').live", $live);
    }

    public function test_tiene_el_aspecto_de_x_ui_input_y_el_id_del_campo(): void
    {
        $html = $this->html(['a' => 'A'], 'nombre="valor"');

        $this->assertStringContainsString('id="campo-valor"', $html);
        $this->assertStringContainsString('aria-describedby="ayuda-valor"', $html);
        $this->assertStringContainsString('px-4 py-2.5 border rounded-xl', $html);
        $this->assertStringContainsString('border-gray-200', $html);
        $this->assertStringContainsString('focus:ring-blue-500/20', $html);
        $this->assertStringContainsString("'bg-blue-50 text-blue-800': resaltado === indice", $html);
    }

    public function test_con_error_lleva_borde_rojo_y_aria_invalid(): void
    {
        $componente = new class extends Component
        {
            public $valor = '';

            public function validar(): void
            {
                $this->validate(['valor' => 'required']);
            }

            public function render(): string
            {
                return '<div><x-ui.combobox nombre="valor" wire:model="valor" :opciones="[\'a\' => \'A\']" /></div>';
            }
        };

        $html = Livewire::test($componente)->call('validar')->html();

        $this->assertStringContainsString('aria-invalid="true"', $html);
        $this->assertStringContainsString('aria-describedby="error-valor ayuda-valor"', $html);
        $this->assertStringContainsString('border-red-500', $html);
    }

    public function test_con_mas_de_10_opciones_aparece_el_buscador(): void
    {
        $diez = $this->html(array_map(fn ($i) => "Opción $i", range(1, 10)));
        $once = $this->html(array_map(fn ($i) => "Opción $i", range(1, 11)));
        $forzado = $this->html(['a' => 'A'], ':buscar="true"');

        $this->assertStringNotContainsString('x-ref="buscador"', $diez);
        $this->assertStringContainsString('x-ref="buscador"', $once);
        $this->assertStringContainsString('placeholder="Buscar..."', $once);
        $this->assertStringContainsString('x-ref="buscador"', $forzado);
    }

    public function test_el_filtro_por_escritura_ignora_tildes(): void
    {
        $html = $this->html(['a' => 'A']);

        $this->assertStringContainsString("normalize('NFD').replace(/\p{Diacritic}/gu, '').toLowerCase()", $html);
    }

    public function test_teclado_flechas_enter_y_escape_solo_con_el_panel_abierto(): void
    {
        $html = $this->html(['a' => 'A']);

        $this->assertStringContainsString('x-on:keydown.down.prevent="mover(1)"', $html);
        $this->assertStringContainsString('x-on:keydown.up.prevent="mover(-1)"', $html);
        $this->assertStringContainsString('x-on:keydown.enter.prevent="confirmar()"', $html);
        // Con el panel cerrado, Escape llega al modal y lo cierra.
        $this->assertStringContainsString('x-on:keydown.escape="if (abierto) { $event.stopPropagation(); cerrar()', $html);
        $this->assertStringContainsString("this.\$dispatch('ui-siguiente-campo')", $html);
    }

    public function test_placeholder_y_opcion_vacia(): void
    {
        $html = $this->html(['a' => 'A'], 'placeholder="Seleccione un corralón" vacio="— Sin asignar —"');

        $this->assertStringContainsString('>Seleccione un corralón</span>', $html);
        $this->assertMatchesRegularExpression('/<li role="option" x-on:click="limpiar\(\)"[^>]*>\s*— Sin asignar —\s*<\/li>/s', $html);
    }

    public function test_sin_vacio_no_hay_opcion_vacia_y_limpiar_deja_cadena_vacia(): void
    {
        $html = $this->html(['a' => 'A']);

        $this->assertStringContainsString('>Seleccionar...</span>', $html);
        $this->assertSame(0, preg_match('/<li role="option" x-on:click="limpiar\(\)"/', $html));
        // El vacío es '' como el <option value=""> que reemplaza.
        $this->assertStringContainsString("limpiar() { this.valor = '';", $html);
    }

    public function test_limpiable_false_saca_la_x(): void
    {
        $conX = $this->html(['a' => 'A', 'b' => 'B']);
        $sinX = $this->html(['a' => 'A', 'b' => 'B'], ':limpiable="false"');

        $this->assertStringContainsString('x-on:click.stop="limpiar()"', $conX);
        $this->assertStringNotContainsString('x-on:click.stop="limpiar()"', $sinX);
    }

    public function test_disabled_deshabilita_el_boton(): void
    {
        $html = $this->html(['a' => 'A'], ':disabled="true"');

        $this->assertStringContainsString('data-disabled="1"', $html);
        $this->assertMatchesRegularExpression('/<button type="button" x-ref="boton"[^>]*disabled/s', $html);
    }

    public function test_relee_las_opciones_cuando_livewire_cambia_data_opciones(): void
    {
        $html = $this->html(['a' => 'A']);

        $this->assertStringContainsString("attributeFilter: ['data-opciones']", $html);
        $this->assertStringContainsString('get opciones() { this.version;', $html);
    }

    public function test_con_mas_de_200_opciones_y_debug_avisa_en_el_log(): void
    {
        config(['app.debug' => true]);
        Log::spy();

        $this->html(array_map(fn ($i) => "Insumo $i", range(1, 201)));

        Log::shouldHaveReceived('warning')->once()->withArgs(
            fn (string $mensaje) => str_contains($mensaje, 'recibió 201 opciones') && str_contains($mensaje, 'wire:model="valor"'),
        );
    }

    public function test_con_200_o_menos_o_sin_debug_no_avisa(): void
    {
        config(['app.debug' => true]);
        Log::spy();
        $this->html(array_map(fn ($i) => "Insumo $i", range(1, 200)));

        config(['app.debug' => false]);
        $this->html(array_map(fn ($i) => "Insumo $i", range(1, 201)));

        Log::shouldNotHaveReceived('warning');
    }

    public function test_opciones_de_dos_lineas_con_insignia_detalle_y_cifra(): void
    {
        $html = $this->html([[
            'id' => 5, 'nombre' => 'Cemento', 'insignia' => 'Construcción', 'detalle' => 'Depósito Central',
            'cifra' => '12,00', 'subcifra' => 'BOLSA', 'cifra_color' => 'verde', 'nota' => '(agregado)', 'deshabilitada' => true,
        ]]);

        $this->assertSame([[
            'valor' => 5, 'texto' => 'Cemento', 'detalle' => 'Depósito Central', 'insignia' => 'Construcción',
            'cifra' => '12,00', 'subcifra' => 'BOLSA', 'cifra_color' => 'verde', 'nota' => '(agregado)', 'deshabilitada' => true,
        ]], $this->opcionesDe($html));
        $this->assertStringContainsString('x-text="opcion.insignia"', $html);
        $this->assertStringContainsString('x-text="opcion.cifra"', $html);
        $this->assertStringContainsString("opcion.cifra_color === 'naranja' ? 'text-orange-600' : 'text-green-600'", $html);
    }

    public function test_modo_servidor_enlaza_el_buscador_y_llama_a_la_accion(): void
    {
        $html = $this->html(
            [['id' => 1, 'nombre' => 'Cemento']],
            'nombre="busqueda" buscar-en="busqueda" accion="elegir" placeholder="Buscar insumo..." sin-resultados="No hay insumos"',
            '',
        );

        // El texto vive en Alpine y va al servidor con debounce (no wire:model:
        // la respuesta del pedido que carga la lista pisaba lo tipeado).
        $this->assertStringContainsString('x-on:input="resaltado = primeraHabilitada(); enviarBusqueda()"', $html);
        $this->assertStringContainsString('setTimeout(() => $wire.set(this.buscarEn, this.busqueda), 300)', $html);
        $this->assertStringContainsString("buscarEn: 'busqueda'", $html);
        $this->assertStringNotContainsString('wire:model.live="busqueda"', $html);
        $this->assertStringContainsString("accion: 'elegir'", $html);
        $this->assertStringContainsString('servidor: true', $html);
        // Sin wire:model no hay entangle.
        $this->assertStringContainsString('valor: null,', $html);
        $this->assertStringNotContainsString('entangle(', $html);
        $this->assertStringContainsString("this.\$dispatch('combobox-abierto')", $html);
        $this->assertStringContainsString('No hay insumos', $html);
        $this->assertStringContainsString('>Buscar insumo...</span>', $html);
        // Siempre con buscador, sin X y sin ordenar en el navegador.
        $this->assertStringContainsString('x-ref="buscador"', $html);
        $this->assertStringNotContainsString('x-on:click.stop="limpiar()"', $html);
    }

    public function test_modo_servidor_no_reordena_los_resultados(): void
    {
        $html = $this->html(['b' => 'Zapato', 'a' => 'Arena'], 'buscar-en="busqueda" accion="elegir"', '');

        $this->assertSame(['Zapato', 'Arena'], $this->textos($html));
    }
}
