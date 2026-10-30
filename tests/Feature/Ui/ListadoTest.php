<?php

namespace Tests\Feature\Ui;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

/**
 * Piezas del listado (spec 17) adaptadas al Corralón: <x-ui.tabla>,
 * <x-ui.buscador>, <x-ui.boton-filtros> y <x-ui.panel-filtros>.
 */
class ListadoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        View::share('errors', new ViewErrorBag);
    }

    public function test_tabla_arma_thead_tbody_y_pie_con_el_aspecto_de_siempre(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-ui.tabla>
                <x-slot:cabecera><x-ui.th>Depósito</x-ui.th></x-slot:cabecera>
                <tr><td>Central</td></tr>
                <x-slot:pie><p>pie</p></x-slot:pie>
            </x-ui.tabla>
        BLADE);

        $this->assertStringContainsString('bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden', $html);
        $this->assertStringContainsString('<div class="overflow-x-auto">', $html);
        $this->assertMatchesRegularExpression('/<thead class="bg-gradient-to-r from-gray-50 to-gray-100\/50">\s*<tr>\s*<th/', $html);
        $this->assertMatchesRegularExpression('/<tbody class="bg-white divide-y divide-gray-50">\s*<tr><td>Central/', $html);
        // El pie va dentro de la tarjeta, después de la tabla.
        $this->assertMatchesRegularExpression('/<\/table>\s*<\/div>\s*<p>pie<\/p>\s*<\/div>/', $html);
    }

    public function test_tabla_sin_cabecera_deja_el_slot_directo_en_la_tabla(): void
    {
        $html = Blade::render('<x-ui.tabla><thead><tr><th>A</th></tr></thead><tbody></tbody><tbody></tbody></x-ui.tabla>');

        $this->assertStringNotContainsString('bg-gradient-to-r', $html);
        $this->assertSame(2, substr_count($html, '<tbody>'));
    }

    public function test_buscador_lleva_lupa_wire_model_en_el_input_y_x_para_limpiar(): void
    {
        $html = Blade::render('<x-ui.buscador wire:model.live="search" placeholder="Buscar depósitos..." class="sm:max-w-sm" />');

        $this->assertStringContainsString('fa-magnifying-glass', $html);
        $this->assertMatchesRegularExpression('/<input[^>]*wire:model\.live="search"/s', $html);
        $this->assertStringContainsString('aria-label="Buscar depósitos..."', $html);
        // La clase va al contenedor, no al input.
        $this->assertMatchesRegularExpression('/<div class="relative flex-1 max-w-md sm:max-w-sm"/', $html);
        $this->assertStringContainsString("\$wire.set('search', '')", $html);
        $this->assertStringContainsString('aria-label="Limpiar búsqueda"', $html);
        $this->assertStringContainsString('rounded-xl', $html);
    }

    public function test_boton_filtros_muestra_la_burbuja_solo_con_filtros(): void
    {
        $sin = Blade::render('<x-ui.boton-filtros :activos="0" wire:click="$toggle(\'showFilters\')" />');
        $con = Blade::render('<x-ui.boton-filtros :activos="3" />');

        $this->assertStringContainsString('<span>Filtros</span>', $sin);
        $this->assertStringContainsString("wire:click=\"\$toggle('showFilters')\"", $sin);
        $this->assertStringNotContainsString('data-filtros-activos', $sin);
        $this->assertMatchesRegularExpression('/data-filtros-activos>3</', $con);
        $this->assertStringContainsString('bg-white border border-gray-300 text-gray-700', $con);
    }

    public function test_boton_filtros_solo_icono_lleva_aria_label_y_burbuja_flotante(): void
    {
        $html = Blade::render('<x-ui.boton-filtros :activos="2" solo-icono />');

        $this->assertStringNotContainsString('<span>Filtros</span>', $html);
        $this->assertStringContainsString('aria-label="Filtros (2 aplicados)"', $html);
        $this->assertStringContainsString('absolute -top-1.5 -right-1.5', $html);
    }

    public function test_panel_filtros_se_enlaza_a_la_propiedad_y_limpia_solo_con_filtros(): void
    {
        $sin = Blade::render('<x-ui.panel-filtros mostrar="showFilters" :activos="0" columnas="5">campos</x-ui.panel-filtros>');
        $con = Blade::render('<x-ui.panel-filtros mostrar="verFiltros" :activos="2" limpiar="borrarFiltros">campos</x-ui.panel-filtros>');

        $this->assertStringContainsString("entangle('showFilters')", $sin);
        $this->assertStringContainsString('lg:grid-cols-5', $sin);
        $this->assertStringContainsString('Filtros Avanzados', $sin);
        $this->assertStringNotContainsString('Limpiar filtros', $sin);
        // Sin overflow-hidden: recortaría los desplegables.
        $this->assertStringNotContainsString('overflow-hidden', $sin);

        $this->assertStringContainsString("entangle('verFiltros')", $con);
        $this->assertStringContainsString('wire:click="borrarFiltros"', $con);
        $this->assertStringContainsString('Limpiar filtros', $con);
        $this->assertStringContainsString('lg:grid-cols-4', $con);
    }

    public function test_campo_oculto_deja_el_rotulo_solo_para_lectores_de_pantalla(): void
    {
        $html = Blade::render('<x-ui.campo etiqueta="Corralón" nombre="filtro_corralon" oculto>x</x-ui.campo>');

        $this->assertMatchesRegularExpression('/<label\s+for="campo-filtro_corralon"\s+class="sr-only">/', $html);
    }
}
