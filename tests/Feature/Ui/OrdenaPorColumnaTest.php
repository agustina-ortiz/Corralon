<?php

namespace Tests\Feature\Ui;

use App\Livewire\Traits\OrdenaPorColumna;
use App\Models\Deposito;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Blade;
use Livewire\Component;
use Livewire\Livewire;
use Tests\TestCase;

/** Listado mínimo para probar el trait y <x-ui.th> juntos (spec 17, D55). */
class ListadoOrdenablePrueba extends Component
{
    use OrdenaPorColumna;

    protected function columnasOrdenables(): array
    {
        return [
            'deposito' => 'deposito',
            'varias' => ['id_corralon', 'deposito'],
            'cerrada' => fn (Builder $q, string $direccion) => $q->orderByRaw('length(deposito) '.$direccion),
        ];
    }

    protected function ordenInicial(): array
    {
        return ['deposito', 'asc'];
    }

    /** El ORDER BY que arma, sin tocar la base. */
    public function ordenSql(): string
    {
        $sql = $this->aplicarOrden(Deposito::query())->toSql();

        return substr($sql, strpos($sql, 'order by'));
    }

    public function render()
    {
        return <<<'HTML'
            <div>
                <table><thead><tr>
                    <x-ui.th columna="deposito" :orden="$this->orden()">Depósito</x-ui.th>
                    <x-ui.th columna="varias" :orden="$this->orden()">Corralón</x-ui.th>
                    <x-ui.th derecha>Acciones</x-ui.th>
                </tr></thead></table>
                <p data-sql>{{ $this->ordenSql() }}</p>
            </div>
        HTML;
    }
}

/**
 * Orden por columna: trait OrdenaPorColumna + <x-ui.th>. Sin base de datos:
 * se compara el ORDER BY que arma la consulta.
 */
class OrdenaPorColumnaTest extends TestCase
{
    public function test_abre_con_el_orden_inicial_y_la_cabecera_lo_marca(): void
    {
        Livewire::test(ListadoOrdenablePrueba::class)
            ->assertSet('ordenColumna', 'deposito')
            ->assertSet('ordenDireccion', 'asc')
            ->assertSee('order by "deposito" asc, "depositos"."id" asc')
            ->assertSeeHtml('aria-sort="ascending"')
            ->assertSeeHtml('data-columna="deposito"')
            ->assertSeeHtml("wire:click=\"ordenarPor('deposito')\"")
            ->assertSeeHtml('title="Ordenar por Depósito"');
    }

    public function test_el_segundo_click_invierte_y_otra_columna_vuelve_a_asc(): void
    {
        $componente = Livewire::test(ListadoOrdenablePrueba::class)
            ->call('ordenarPor', 'deposito')
            ->assertSet('ordenDireccion', 'desc')
            ->assertSee('order by "deposito" desc, "depositos"."id" desc')
            ->assertSeeHtml('aria-sort="descending"');

        // Varias columnas en la misma dirección.
        $componente->call('ordenarPor', 'varias')
            ->assertSet('ordenColumna', 'varias')
            ->assertSet('ordenDireccion', 'asc')
            ->assertSee('order by "id_corralon" asc, "deposito" asc, "depositos"."id" asc');

        // Un closure.
        $componente->call('ordenarPor', 'cerrada')
            ->assertSee('order by length(deposito) asc, "depositos"."id" asc');
    }

    public function test_una_clave_desconocida_no_cambia_nada(): void
    {
        Livewire::test(ListadoOrdenablePrueba::class)
            ->call('ordenarPor', 'password')
            ->assertSet('ordenColumna', 'deposito')
            ->assertSet('ordenDireccion', 'asc');
    }

    public function test_un_estado_manipulado_cae_en_el_orden_inicial(): void
    {
        Livewire::test(ListadoOrdenablePrueba::class)
            ->set('ordenColumna', 'password')
            ->set('ordenDireccion', 'drop')
            ->assertSee('order by "deposito" asc, "depositos"."id" asc');
    }

    public function test_la_columna_sin_clave_es_un_th_comun_y_derecha_alinea(): void
    {
        $html = Blade::render('<table><tr><x-ui.th derecha>Acciones</x-ui.th><x-ui.th>Estado</x-ui.th></tr></table>');

        $this->assertStringNotContainsString('wire:click', $html);
        $this->assertStringNotContainsString('aria-sort', $html);
        $this->assertMatchesRegularExpression('/<th scope="col" class="[^"]*uppercase[^"]*text-right"[^>]*>\s*Acciones/', $html);
        $this->assertMatchesRegularExpression('/<th scope="col" class="[^"]*text-left"[^>]*>\s*Estado/', $html);
    }

    public function test_la_columna_inactiva_muestra_la_flecha_solo_al_pasar_el_mouse(): void
    {
        $html = Blade::render('<table><tr><x-ui.th columna="stock" :orden="[\'columna\' => \'insumo\', \'direccion\' => \'asc\']" derecha>Stock</x-ui.th></tr></table>');

        $this->assertStringContainsString("wire:click=\"ordenarPor('stock')\"", $html);
        $this->assertStringNotContainsString('aria-sort', $html);
        $this->assertStringContainsString('group-hover:opacity-100', $html);
        // Alineada a la derecha: la flecha va a la izquierda del rótulo.
        $this->assertStringContainsString('flex-row-reverse', $html);
    }
}
