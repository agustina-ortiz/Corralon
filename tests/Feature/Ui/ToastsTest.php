<?php

namespace Tests\Feature\Ui;

use App\Livewire\Traits\EnviaToasts;
use Illuminate\Support\Facades\Blade;
use Livewire\Component;
use Livewire\Livewire;
use Tests\TestCase;

class ToastsTest extends TestCase
{
    public function test_el_contenedor_escucha_el_evento_toast_con_sus_duraciones(): void
    {
        $html = Blade::render('<x-ui.toasts />');

        $this->assertStringContainsString('x-on:toast.window="agregar($event.detail)"', $html);
        $this->assertStringContainsString('duraciones: { exito: 4000, info: 4000, aviso: 6000, error: 8000 }', $html);
        $this->assertStringContainsString('aria-label="Cerrar aviso"', $html);
    }

    public function test_los_layouts_montan_el_contenedor_una_vez(): void
    {
        foreach (['app', 'guest'] as $layout) {
            $contenido = file_get_contents(resource_path('views/layouts/'.$layout.'.blade.php'));
            $this->assertSame(1, substr_count($contenido, '<x-ui.toasts />'), 'layouts/'.$layout);
        }
    }

    public function test_el_trait_despacha_el_evento_toast(): void
    {
        $componente = new class extends Component
        {
            use EnviaToasts;

            public function guardar(): void
            {
                $this->toastExito('Guardado');
            }

            public function fallar(): void
            {
                $this->toastError('Falló');
            }

            public function render(): string
            {
                return '<div></div>';
            }
        };

        Livewire::test($componente)
            ->call('guardar')
            ->assertDispatched('toast', tipo: 'exito', mensaje: 'Guardado')
            ->call('fallar')
            ->assertDispatched('toast', tipo: 'error', mensaje: 'Falló');
    }
}
