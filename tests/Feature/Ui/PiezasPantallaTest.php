<?php

namespace Tests\Feature\Ui;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

/**
 * Piezas chicas que comparten las pantallas (kit, specs 14 y 15):
 * <x-ui.pagina>, <x-ui.boton>, <x-ui.accion>, <x-ui.badge>, <x-ui.vacio> y
 * <x-ui.campo> + <x-ui.input>/<x-ui.textarea>/<x-ui.checkbox>.
 */
class PiezasPantallaTest extends TestCase
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

    public function test_pagina_renderiza_titulo_icono_y_contenido(): void
    {
        $html = Blade::render('<x-ui.pagina titulo="Depósitos" icono="fa-warehouse">cuerpo</x-ui.pagina>');

        $this->assertStringContainsString('<h1', $html);
        $this->assertStringContainsString('<span>Depósitos</span>', $html);
        $this->assertStringContainsString('class="fa-solid fa-warehouse text-blue-600"', $html);
        $this->assertStringContainsString('cuerpo', $html);
    }

    public function test_boton_primario_usa_el_degrade_azul_y_el_type_por_omision_es_button(): void
    {
        $html = Blade::render('<x-ui.boton icono="fa-plus" wire:click="crear">Nuevo Depósito</x-ui.boton>');

        $this->assertStringContainsString('<button type="button"', $html);
        $this->assertStringContainsString('bg-gradient-to-r from-blue-600 to-blue-700', $html);
        $this->assertStringContainsString('wire:click="crear"', $html);
        $this->assertStringContainsString('class="fa-solid fa-plus"', $html);
        $this->assertStringContainsString('Nuevo Depósito', $html);
    }

    public function test_boton_variantes_y_submit(): void
    {
        $secundario = Blade::render('<x-ui.boton variante="secundario">Cancelar</x-ui.boton>');
        $peligro = Blade::render('<x-ui.boton variante="peligro" type="submit">Eliminar</x-ui.boton>');

        $this->assertStringContainsString('bg-white border border-gray-300 text-gray-700', $secundario);
        $this->assertStringContainsString('<button type="submit"', $peligro);
        $this->assertStringContainsString('from-red-600 to-red-700', $peligro);
    }

    public function test_boton_chico_achica_relleno_y_texto_sin_duplicar_medidas(): void
    {
        $normal = Blade::render('<x-ui.boton icono="fa-check">Aprobar</x-ui.boton>');
        $chico = Blade::render('<x-ui.boton variante="secundario" tamano="chico" icono="fa-check">Aprobar</x-ui.boton>');
        $enlace = Blade::render('<x-ui.boton variante="enlace" tamano="chico">Ver</x-ui.boton>');

        $this->assertStringContainsString('px-5 py-2.5 text-base', $normal);
        $this->assertStringContainsString('px-3 py-1.5 text-xs', $chico);
        $this->assertStringContainsString('px-1 py-0 text-xs', $enlace);

        // Una sola medida por botón: con dos px-/py- gana la que Tailwind genere última.
        foreach ([$normal, $chico, $enlace] as $html) {
            preg_match('/<button[^>]*class="([^"]+)"/', $html, $clase);
            $this->assertSame(1, preg_match_all('/(?<![\w-])px-[\d.]+/', $clase[1]), $clase[1]);
            $this->assertSame(1, preg_match_all('/(?<![\w-])py-[\d.]+/', $clase[1]), $clase[1]);
        }

        $this->assertStringNotContainsString('tamano', $chico);
    }

    public function test_boton_con_href_interno_navega_con_livewire_y_externo_no(): void
    {
        $interno = Blade::render('<x-ui.boton href="/insumos">Insumos</x-ui.boton>');
        $pdf = Blade::render('<x-ui.boton href="/x.pdf" :navegar="false">PDF</x-ui.boton>');
        $nueva = Blade::render('<x-ui.boton href="/insumos" target="_blank">Insumos</x-ui.boton>');

        $this->assertStringContainsString('wire:navigate', $interno);
        $this->assertStringNotContainsString('wire:navigate', $pdf);
        $this->assertStringNotContainsString('wire:navigate', $nueva);
    }

    public function test_accion_es_solo_icono_con_etiqueta_en_title_y_aria_label(): void
    {
        $html = Blade::render('<x-ui.accion icono="fa-regular fa-trash-can" color="peligro" etiqueta="Eliminar" wire:click="confirmarEliminar(3)" />');

        $this->assertStringContainsString('title="Eliminar"', $html);
        $this->assertStringContainsString('aria-label="Eliminar"', $html);
        $this->assertStringContainsString('class="fa-regular fa-trash-can"', $html);
        $this->assertStringContainsString('text-red-600', $html);
        $this->assertStringContainsString('w-9 h-9', $html);
        $this->assertStringNotContainsString('>Eliminar<', $html);

        $neutra = Blade::render('<x-ui.accion icono="fa-pen" etiqueta="Editar" />');
        $this->assertStringContainsString('class="fa-solid fa-pen"', $neutra);
        $this->assertStringContainsString('text-blue-600', $neutra);
    }

    public function test_badge_solo_colores_semanticos(): void
    {
        $this->assertStringContainsString('bg-emerald-100 text-emerald-800', Blade::render('<x-ui.badge color="verde">EN USO</x-ui.badge>'));
        $this->assertStringContainsString('bg-red-100 text-red-800', Blade::render('<x-ui.badge color="red">BAJA</x-ui.badge>'));
        $this->assertStringContainsString('bg-gray-100 text-gray-700', Blade::render('<x-ui.badge color="fucsia">X</x-ui.badge>'));
    }

    public function test_vacio_muestra_icono_y_texto(): void
    {
        $html = Blade::render('<x-ui.vacio icono="fa-warehouse">Todavía no hay depósitos</x-ui.vacio>');

        $this->assertStringContainsString('data-vacio', $html);
        $this->assertStringContainsString('fa-solid fa-warehouse', $html);
        $this->assertStringContainsString('Todavía no hay depósitos', $html);
    }

    public function test_campo_con_rotulo_obligatorio_y_control_enlazado(): void
    {
        $html = Blade::render('<x-ui.campo etiqueta="Depósito" nombre="deposito" obligatorio><x-ui.input nombre="deposito" wire:model="deposito" /></x-ui.campo>');

        $this->assertStringContainsString('for="campo-deposito"', $html);
        $this->assertStringContainsString('id="campo-deposito"', $html);
        $this->assertStringContainsString('<span class="text-red-600" aria-hidden="true">*</span>', $html);
        $this->assertStringContainsString('border-gray-200', $html);
        $this->assertStringNotContainsString('data-error', $html);
    }

    public function test_el_error_y_la_ayuda_quedan_asociados_al_control(): void
    {
        $this->conErrores(['nombre' => ['Falta el nombre.']]);

        $html = Blade::render('<x-ui.campo etiqueta="Nombre" nombre="nombre" ayuda="Como figura en el DNI."><x-ui.input nombre="nombre" /></x-ui.campo>');

        $this->assertStringContainsString('aria-describedby="error-nombre ayuda-nombre"', $html);
        $this->assertStringContainsString('aria-invalid="true"', $html);
        $this->assertStringContainsString('border-red-500', $html);
        $this->assertStringContainsString('id="error-nombre"', $html);
        $this->assertStringContainsString('Falta el nombre.', $html);
        $this->assertStringContainsString('id="ayuda-nombre"', $html);

        View::share('errors', new ViewErrorBag);
        $sinError = Blade::render('<x-ui.campo etiqueta="Nombre" nombre="nombre"><x-ui.textarea nombre="nombre" /></x-ui.campo>');

        $this->assertStringContainsString('aria-describedby="ayuda-nombre"', $sinError);
        $this->assertStringNotContainsString('aria-invalid', $sinError);
    }

    public function test_checkbox_con_rotulo_al_lado(): void
    {
        $html = Blade::render('<x-ui.checkbox nombre="evento_anual" etiqueta="Evento anual" ayuda="Se repite todos los años" wire:model="evento_anual" />');

        $this->assertStringContainsString('type="checkbox"', $html);
        $this->assertStringContainsString('id="campo-evento_anual"', $html);
        $this->assertStringContainsString('Evento anual', $html);
        $this->assertStringContainsString('Se repite todos los años', $html);
    }
}
