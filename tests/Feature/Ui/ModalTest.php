<?php

namespace Tests\Feature\Ui;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class ModalTest extends TestCase
{
    private function render(string $blade): string
    {
        return Blade::render($blade);
    }

    public function test_renderiza_titulo_icono_solid_y_el_slot(): void
    {
        $html = $this->render('<x-ui.modal titulo="Nuevo Depósito" icono="fa-plus">Contenido del body</x-ui.modal>');

        $this->assertStringContainsString('Nuevo Depósito', $html);
        $this->assertStringContainsString('fa-solid fa-plus', $html);
        $this->assertStringContainsString('Contenido del body', $html);
        $this->assertStringContainsString('role="dialog"', $html);
        $this->assertStringContainsString('aria-modal="true"', $html);
    }

    public function test_respeta_un_icono_que_ya_trae_estilo(): void
    {
        $html = $this->render('<x-ui.modal titulo="x" icono="fa-regular fa-calendar">.</x-ui.modal>');

        $this->assertStringContainsString('fa-regular fa-calendar', $html);
        $this->assertStringNotContainsString('fa-solid fa-regular', $html);
    }

    public function test_la_x_y_escape_usan_la_misma_accion_de_cierre_y_el_backdrop_no_cierra(): void
    {
        $html = $this->render('<x-ui.modal titulo="x" cerrar="cerrarFormulario">.</x-ui.modal>');

        $this->assertStringContainsString('$wire.cerrarFormulario()', $html);
        $this->assertStringContainsString('x-on:keydown.escape.window="if (esElDeArriba()) cerrar()"', $html);
        $this->assertStringContainsString('aria-label="Cerrar"', $html);

        // Un click afuera sin querer no tira un formulario a medio cargar.
        $this->assertStringNotContainsString('x-on:click.self', $html);
        $this->assertStringNotContainsString('click.outside', $html);
    }

    public function test_escape_y_f2_solo_actuan_en_el_modal_visible_de_mayor_z(): void
    {
        $html = $this->render('<x-ui.modal titulo="x">.</x-ui.modal>');

        $this->assertStringContainsString('m.getClientRects().length > 0', $html);
        $this->assertStringContainsString('getComputedStyle(m).zIndex', $html);
    }

    public function test_teclado_primero_foco_inicial_enter_tabea_y_f2_guarda(): void
    {
        $html = $this->render(<<<'BLADE'
            <x-ui.modal titulo="x" submit="guardar">
                <input name="a">
                <x-slot:footer><button type="submit">Guardar</button></x-slot:footer>
            </x-ui.modal>
        BLADE);

        $this->assertStringContainsString('this.enfocarPrimero();', $html);
        $this->assertStringContainsString('x-on:keydown.enter="enterTabea($event)"', $html);
        $this->assertStringContainsString("['TEXTAREA', 'BUTTON', 'A', 'SELECT'].includes(campo.tagName)", $html);
        $this->assertStringContainsString('if (! this.siguienteCampo(campo)) this.guardar();', $html);
        $this->assertStringContainsString("if (\$event.key === 'F2' && esElDeArriba()) { \$event.preventDefault(); guardar() }", $html);
        $this->assertStringContainsString('$refs.formulario.requestSubmit()', $html);
        $this->assertStringContainsString("querySelector('[data-atajo-guardar]')?.click()", $html);
    }

    public function test_el_foco_queda_adentro_vuelve_a_quien_lo_abrio_y_busca_el_primer_error(): void
    {
        $html = $this->render('<x-ui.modal titulo="x" submit="guardar">.</x-ui.modal>');

        $this->assertStringContainsString('x-trap.inert="true"', $html);
        $this->assertStringContainsString('this.anterior = document.activeElement', $html);
        $this->assertStringContainsString('x-on:submit="enfocarError()"', $html);
    }

    public function test_por_omision_cierra_con_close_modal(): void
    {
        $this->assertStringContainsString('$wire.closeModal()', $this->render('<x-ui.modal titulo="x">.</x-ui.modal>'));
    }

    public function test_scroll_lock_y_altura_limitada_al_viewport_con_solo_el_body_scrolleando(): void
    {
        $html = $this->render('<x-ui.modal titulo="x">.</x-ui.modal>');

        $this->assertStringContainsString("document.body.classList.add('overflow-hidden')", $html);
        $this->assertStringContainsString("document.body.classList.remove('overflow-hidden')", $html);
        $this->assertStringContainsString('max-h-full', $html);
        $this->assertMatchesRegularExpression('/x-ref="cuerpo"[^>]*overflow-y-auto/', $html);
        $this->assertStringContainsString('x-on:scroll.self="$el.scrollTop = 0; $el.scrollLeft = 0"', $html);
        $this->assertStringContainsString('otros.length === 0', $html);
    }

    public function test_anchos(): void
    {
        $this->assertStringContainsString('w-fit', $this->render('<x-ui.modal titulo="x">.</x-ui.modal>'));

        foreach (['sm', 'md', 'lg', 'xl', '2xl', '3xl', '4xl', '5xl', '6xl'] as $ancho) {
            $html = $this->render('<x-ui.modal titulo="x" ancho="'.$ancho.'">.</x-ui.modal>');
            $this->assertStringContainsString('w-full max-w-'.$ancho, $html);
        }
    }

    public function test_submit_envuelve_body_y_footer_en_un_form_con_display_contents(): void
    {
        $html = $this->render(<<<'BLADE'
            <x-ui.modal titulo="x" submit="save">
                <input name="a">
                <x-slot:footer><button type="submit">Guardar</button></x-slot:footer>
            </x-ui.modal>
        BLADE);

        $this->assertStringContainsString('<form wire:submit.prevent="save" x-ref="formulario" class="contents">', $html);
        $this->assertMatchesRegularExpression('/<form[^>]*>.*<input name="a">.*Guardar.*<\/form>/s', $html);
        $this->assertStringNotContainsString('<form', $this->render('<x-ui.modal titulo="x">.</x-ui.modal>'));
    }

    public function test_z_index_para_modales_apilados_con_clases_literales(): void
    {
        $this->assertStringContainsString(' z-50 ', $this->render('<x-ui.modal titulo="x">.</x-ui.modal>'));
        $this->assertStringContainsString(' z-[60] ', $this->render('<x-ui.modal titulo="x" :z="60">.</x-ui.modal>'));
        $this->assertStringContainsString(' z-[70] ', $this->render('<x-ui.modal titulo="x" :z="70">.</x-ui.modal>'));
    }
}
