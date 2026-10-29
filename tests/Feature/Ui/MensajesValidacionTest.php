<?php

namespace Tests\Feature\Ui;

use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class MensajesValidacionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('es');
    }

    public function test_los_mensajes_salen_en_espanol_con_el_nombre_legible_del_campo(): void
    {
        $errores = Validator::make(
            ['nueva_descripcion' => '', 'nuevo_documento' => null, 'cantidad' => 'abc'],
            ['nueva_descripcion' => 'required', 'nuevo_documento' => 'required', 'cantidad' => 'numeric'],
        )->errors();

        $this->assertSame('Falta la descripción.', $errores->first('nueva_descripcion'));
        $this->assertSame('Falta el documento.', $errores->first('nuevo_documento'));
        $this->assertSame('La cantidad debe ser un número.', $errores->first('cantidad'));
    }

    public function test_los_campos_de_listas_anidadas_tambien_tienen_nombre(): void
    {
        $errores = Validator::make(
            ['insumos_transferencia' => [['cantidad' => 0]]],
            ['insumos_transferencia.*.cantidad' => 'numeric|min:0.01'],
        )->errors();

        $this->assertSame('La cantidad debe ser al menos 0.01.', $errores->first('insumos_transferencia.0.cantidad'));
    }

    public function test_seleccion_invalida_no_depende_del_genero_del_campo(): void
    {
        $errores = Validator::make(['id_secretaria' => 9], ['id_secretaria' => 'in:1'])->errors();

        $this->assertSame('El valor elegido para la secretaría no es válido.', $errores->first('id_secretaria'));
    }
}
