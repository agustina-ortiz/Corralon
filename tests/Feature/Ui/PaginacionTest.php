<?php

namespace Tests\Feature\Ui;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

/** <x-ui.paginacion> (spec 17): reemplaza a los links() de Livewire. */
class PaginacionTest extends TestCase
{
    private function render(int $actual, int $total, int $porPagina = 10, int $registros = 95, string $extra = ''): string
    {
        $offset = ($actual - 1) * $porPagina;

        return Blade::render(
            '<x-ui.paginacion :current-page="$a" :total-pages="$t" :offset="$o" :per-page="$p" :total-records="$r" '.$extra.' />',
            ['a' => $actual, 't' => $total, 'o' => $offset, 'p' => $porPagina, 'r' => $registros],
        );
    }

    public function test_muestra_el_rango_de_registros(): void
    {
        $this->assertStringContainsString('Mostrando 11 a 20 de 95 registros', $this->render(2, 10));
    }

    public function test_la_ultima_pagina_no_pasa_del_total(): void
    {
        $this->assertStringContainsString('Mostrando 91 a 95 de 95 registros', $this->render(10, 10));
    }

    public function test_sin_registros_lo_dice_y_no_muestra_controles(): void
    {
        $html = $this->render(1, 1, 10, 0);

        $this->assertStringContainsString('No hay registros para mostrar', $html);
        $this->assertStringNotContainsString('wire:click', $html);
    }

    public function test_una_sola_pagina_muestra_el_total_sin_botones(): void
    {
        $html = $this->render(1, 1, 10, 1);

        $this->assertStringContainsString('Mostrando 1 registro', $html);
        $this->assertStringNotContainsString('wire:click', $html);
    }

    public function test_los_botones_llaman_a_gotoPage_por_omision_y_suben_la_pantalla(): void
    {
        $html = $this->render(2, 5, 10, 45);

        $this->assertStringContainsString('wire:click="gotoPage(1)"', $html);
        $this->assertStringContainsString('wire:click="gotoPage(3)"', $html);
        $this->assertStringContainsString('aria-current="page">2<', $html);
        $this->assertStringContainsString('scrollIntoView()', $html);
    }

    public function test_la_accion_es_configurable(): void
    {
        $html = $this->render(1, 3, 10, 25, 'accion="irAPagina"');

        $this->assertStringContainsString('wire:click="irAPagina(2)"', $html);
        $this->assertStringNotContainsString('gotoPage', $html);
    }

    public function test_en_los_extremos_deshabilita_anterior_o_siguiente(): void
    {
        $primera = $this->render(1, 5, 10, 45);
        $ultima = $this->render(5, 5, 10, 45);

        $this->assertStringNotContainsString('wire:click="gotoPage(0)"', $primera);
        $this->assertStringContainsString('aria-label="Siguiente"', $primera);
        $this->assertStringNotContainsString('aria-label="Anterior"', $primera);

        $this->assertStringNotContainsString('wire:click="gotoPage(6)"', $ultima);
        $this->assertStringContainsString('aria-label="Anterior"', $ultima);
        $this->assertStringNotContainsString('aria-label="Siguiente"', $ultima);
    }

    public function test_con_muchas_paginas_resume_con_puntos_suspensivos(): void
    {
        $html = $this->render(10, 20, 10, 195);

        foreach ([1, 2, 9, 11, 19, 20] as $pagina) {
            $this->assertStringContainsString("gotoPage($pagina)", $html);
        }
        $this->assertStringNotContainsString('gotoPage(5)', $html);
        $this->assertSame(2, substr_count($html, '>…<'));
    }

    public function test_mobile_solo_ve_pagina_x_de_y_y_escritorio_los_numeros(): void
    {
        $html = $this->render(2, 5, 10, 45);

        $this->assertMatchesRegularExpression('/md:hidden[^>]*>Página 2 de 5/', $html);
        $this->assertStringContainsString('hidden md:flex', $html);
    }

    public function test_la_pagina_activa_es_azul_como_los_botones(): void
    {
        $html = $this->render(2, 5, 10, 45);

        $this->assertStringContainsString('bg-blue-600 text-white shadow-sm shadow-blue-500/30" aria-current="page"', $html);
    }

    public function test_toma_los_valores_de_un_paginador(): void
    {
        $paginador = new LengthAwarePaginator(range(1, 15), 57, 15, 2);

        $html = Blade::render('<x-ui.paginacion :paginador="$p" />', ['p' => $paginador]);

        $this->assertStringContainsString('Mostrando 16 a 30 de 57 registros', $html);
        $this->assertStringContainsString('aria-current="page">2<', $html);
        $this->assertStringContainsString('wire:click="gotoPage(4)"', $html);
        $this->assertStringNotContainsString('gotoPage(5)', $html);
    }
}
