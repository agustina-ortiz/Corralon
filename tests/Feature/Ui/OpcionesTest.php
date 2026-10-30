<?php

namespace Tests\Feature\Ui;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

/**
 * <x-ui.opciones> (botones segmentados para 2 a 4 valores fijos) y
 * <x-ui.grupo> (fieldset + legend + error). Sin base de datos.
 */
class OpcionesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        View::share('errors', new ViewErrorBag);
    }

    private function conErrores(array $errores): void
    {
        View::share('errors', (new ViewErrorBag)->put('default', new MessageBag($errores)));
    }

    private const COMBUSTIBLES = "['nafta' => 'Nafta', 'diesel' => 'Diesel', 'gas' => 'Gas']";

    public function test_un_radio_por_opcion_con_wire_model_y_name_del_grupo(): void
    {
        $html = Blade::render('<x-ui.opciones nombre="tipo_combustible" wire:model.live="tipo_combustible" :opciones="'.self::COMBUSTIBLES.'" />');

        $this->assertSame(3, substr_count($html, 'type="radio"'));
        $this->assertSame(3, substr_count($html, 'wire:model.live="tipo_combustible"'));
        $this->assertSame(3, substr_count($html, 'name="tipo_combustible"'));
        $this->assertStringContainsString('id="campo-tipo_combustible-nafta"', $html);
        $this->assertStringContainsString('value="diesel"', $html);
        $this->assertStringContainsString('>Gas</span>', $html);
        $this->assertStringContainsString('role="radiogroup"', $html);
        // wire:model va en los radios, no en el contenedor.
        $this->assertDoesNotMatchRegularExpression('/role="radiogroup"[^>]*wire:model/', $html);
    }

    public function test_mismo_aspecto_que_el_input_y_la_elegida_en_azul(): void
    {
        $html = Blade::render('<x-ui.opciones nombre="estado" wire:model="estado" :opciones="[\'EN USO\' => \'En Uso\', \'BAJA\' => \'Baja\']" />');

        $this->assertStringContainsString('rounded-xl border', $html);
        $this->assertStringContainsString('border-gray-200', $html);
        $this->assertStringContainsString('py-2.5', $html);
        $this->assertStringContainsString('peer-checked:bg-blue-600 peer-checked:text-white', $html);
        $this->assertStringContainsString('class="peer sr-only"', $html);
        $this->assertStringContainsString('value="EN USO"', $html);
        $this->assertStringContainsString('id="campo-estado-en-uso"', $html);
    }

    public function test_valor_vacio_es_una_opcion_mas(): void
    {
        $html = Blade::render('<x-ui.opciones nombre="licencia" wire:model="licencia" :opciones="[\'\' => \'Sin especificar\', \'PROFESIONAL\' => \'Profesional\']" />');

        $this->assertStringContainsString('value=""', $html);
        $this->assertStringContainsString('id="campo-licencia-vacio"', $html);
    }

    public function test_con_error_borde_rojo_y_aria_invalid_en_el_primero(): void
    {
        $this->conErrores(['estado' => ['Falta el estado.']]);

        $html = Blade::render('<x-ui.opciones nombre="estado" wire:model="estado" :opciones="[\'a\' => \'A\', \'b\' => \'B\']" />');

        $this->assertStringContainsString('border-red-500', $html);
        $this->assertSame(1, substr_count($html, 'aria-invalid="true"'));
        $this->assertStringContainsString('aria-describedby="error-estado ayuda-estado"', $html);
    }

    public function test_disabled_deshabilita_los_radios(): void
    {
        $html = Blade::render('<x-ui.opciones nombre="estado" :disabled="true" :opciones="[\'a\' => \'A\', \'b\' => \'B\']" />');

        $this->assertSame(2, preg_match_all('/class="peer sr-only" disabled/', $html));
    }

    public function test_grupo_es_fieldset_con_legend_obligatorio_error_y_ayuda(): void
    {
        $this->conErrores(['estado' => ['Falta el estado.']]);

        $html = Blade::render('<x-ui.grupo etiqueta="Estado" nombre="estado" obligatorio ayuda="Del vehículo">x</x-ui.grupo>');

        $this->assertStringContainsString('<fieldset class="w-full min-w-0">', $html);
        $this->assertMatchesRegularExpression('/<legend[^>]*>\s*Estado\s*<span class="text-red-600" aria-hidden="true">\*<\/span>/', $html);
        $this->assertStringContainsString('<p id="error-estado" class="mt-1 text-xs text-red-600" data-error="estado">Falta el estado.</p>', $html);
        $this->assertMatchesRegularExpression('/<p\s+id="ayuda-estado"\s+class="mt-1 text-xs text-gray-500">Del vehículo<\/p>/', $html);
    }

    public function test_el_modal_trata_un_grupo_de_radios_como_un_solo_campo(): void
    {
        $html = Blade::render('<x-ui.modal titulo="T">x</x-ui.modal>');

        $this->assertStringContainsString("const mismoGrupo = campo => campo.type === 'radio'", $html);
        $this->assertStringContainsString('! mismoGrupo(campo)', $html);
    }
}
