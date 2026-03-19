<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ChoferesSeeder extends Seeder
{
    public function run()
    {
        $choferes = [
            ['nombre' => 'Marengo, Jorge', 'dni' => '23418584', 'num' => '7760', 'lic' => 'PROFESIONAL', 'tipo' => 'A1.4.B2.C.3.D1.D.3.D4', 'venc' => '2026-07-18', 'dom' => '1 N°1323', 'sec' => 2, 'area' => 'Transito', 'vehiculos' => []],
            ['nombre' => 'Paladino, Pablo', 'dni' => '8046-TEMP', 'num' => '8046', 'lic' => 'PROFESIONAL', 'tipo' => 'A.1.4.B.2.D1.D4', 'venc' => '2027-08-04', 'dom' => '39 N°3285', 'sec' => 2, 'area' => 'Transito', 'vehiculos' => [232]],
            ['nombre' => 'Calloni, Juan', 'dni' => '35377035', 'num' => '7627', 'lic' => 'PROFESIONAL', 'tipo' => 'A.1 .2 B.2 D.1', 'venc' => '2026-10-30', 'dom' => '60 N°751', 'sec' => 2, 'area' => 'Transito', 'vehiculos' => [237]],
            ['nombre' => 'Flores, Paula', 'dni' => '25023461', 'num' => '7763', 'lic' => 'PROFESIONAL', 'tipo' => 'A.1.2B.1.D.1.D.4', 'venc' => '2027-01-07', 'dom' => '103 N°3079', 'sec' => 2, 'area' => 'Transito', 'vehiculos' => []],
            ['nombre' => 'Lopez, Jesica', 'dni' => '28730245', 'num' => '7201', 'lic' => 'PROFESIONAL', 'tipo' => 'A.1.4B.2D.1D.3D.4', 'venc' => '2026-06-26', 'dom' => '109 N°455', 'sec' => 2, 'area' => 'Transito', 'vehiculos' => []],
            ['nombre' => 'Carreño, Nestor', 'dni' => '17207085', 'num' => '7761', 'lic' => 'PROFESIONAL', 'tipo' => 'A.4B.2D.1D.4', 'venc' => null, 'dom' => '49 N°301', 'sec' => 2, 'area' => 'Transito', 'vehiculos' => [235]],
            ['nombre' => 'Laporta, Pedro', 'dni' => '8047-TEMP', 'num' => '8047', 'lic' => 'PROFESIONAL', 'tipo' => null, 'venc' => '2027-08-05', 'dom' => null, 'sec' => 2, 'area' => 'Transito', 'vehiculos' => [232]],
            ['nombre' => 'Avila, Morena', 'dni' => '7930-TEMP', 'num' => '7930', 'lic' => 'PROFESIONAL', 'tipo' => null, 'venc' => null, 'dom' => null, 'sec' => 2, 'area' => 'Transito', 'vehiculos' => [237]],
            ['nombre' => 'Cerisola, Marcelo', 'dni' => '20726920', 'num' => '8042', 'lic' => 'PROFESIONAL', 'tipo' => 'A.1.3.B.1.D.1.D.4', 'venc' => '2026-08-02', 'dom' => '10 N°694', 'sec' => 2, 'area' => 'Transito', 'vehiculos' => []],
            ['nombre' => 'Visconti, Angel', 'dni' => 'V-TEMP-1', 'num' => 'V-TEMP-1', 'lic' => 'PROFESIONAL', 'tipo' => 'A.1.4 D.1 D.3 D.4 G.1 G.2', 'venc' => '2026-05-21', 'dom' => null, 'sec' => 2, 'area' => 'Transito', 'vehiculos' => [61]],
            ['nombre' => 'Mosqueda, Diego', 'dni' => '32686975', 'num' => '7757', 'lic' => 'PROFESIONAL', 'tipo' => 'A.1.4.B.2.D1.D4', 'venc' => '2026-05-04', 'dom' => '21 N°1176', 'sec' => 2, 'area' => 'Transito', 'vehiculos' => []],
            ['nombre' => 'Avila, Rocio', 'dni' => '41925797', 'num' => '8210', 'lic' => 'PROFESIONAL', 'tipo' => 'A.1.2 B.1 D.4', 'venc' => '2028-01-27', 'dom' => '134 N°966', 'sec' => 2, 'area' => 'Proteccion civil', 'vehiculos' => [258]],
            ['nombre' => 'Lore, Agustina', 'dni' => '7918-TEMP', 'num' => '7918', 'lic' => null, 'tipo' => null, 'venc' => null, 'dom' => null, 'sec' => 2, 'area' => 'Proteccion civil', 'vehiculos' => [258]],
            ['nombre' => 'Sequeira, Walter Gabriel', 'dni' => '32975353', 'num' => '8065', 'lic' => 'COMUN', 'tipo' => 'A.1.4 B.2', 'venc' => '2027-07-27', 'dom' => '13 N°1238', 'sec' => 2, 'area' => 'Secretaria', 'vehiculos' => [293]],
            ['nombre' => 'Zunino, Ignacio', 'dni' => '36345307', 'num' => '7591', 'lic' => 'COMUN', 'tipo' => 'A1.3 B.1 D.2', 'venc' => '2027-11-11', 'dom' => '43 N°259', 'sec' => 2, 'area' => 'Pintura vial', 'vehiculos' => [233, 228]],
            ['nombre' => 'Pollero, Cristian', 'dni' => '20526915', 'num' => '1599', 'lic' => 'COMUN', 'tipo' => 'A.1.4 B.2 G.1 G.2', 'venc' => '2028-01-30', 'dom' => '45 N°771', 'sec' => 2, 'area' => 'Pintura vial', 'vehiculos' => [233, 228]],
            ['nombre' => 'Hayes, Raimiro', 'dni' => '30342687', 'num' => '7759', 'lic' => 'PROFESONAL', 'tipo' => 'A1.4. B.2 D.1 D.4', 'venc' => '2028-02-13', 'dom' => '17 N°897', 'sec' => 2, 'area' => 'Pintura vial', 'vehiculos' => [233, 228]],
            ['nombre' => 'Duran, Juan Agustin', 'dni' => '36345069', 'num' => '8254', 'lic' => 'COMUN', 'tipo' => 'A1.1 B 2', 'venc' => '2031-01-12', 'dom' => '13 N°744', 'sec' => 2, 'area' => 'Pintura vial', 'vehiculos' => [233, 228]],
            ['nombre' => 'Cotignola, Gaston', 'dni' => '27182357', 'num' => '8200', 'lic' => 'PROFESIONAL', 'tipo' => 'A.1.4 B.2 D.1 D.1 D.3 D.4 E.1 G.1 G.2', 'venc' => '2027-01-11', 'dom' => '19 N°1979', 'sec' => 2, 'area' => 'Pintura vial', 'vehiculos' => [233, 228]],
            ['nombre' => 'Fernandez, Eduardo', 'dni' => '1607-TEMP', 'num' => '1607', 'lic' => null, 'tipo' => null, 'venc' => null, 'dom' => null, 'sec' => 2, 'area' => 'Pintura vial', 'vehiculos' => [233, 228]],
            ['nombre' => 'Araya, Roberto', 'dni' => '31114280', 'num' => '7695', 'lic' => 'COMUN', 'tipo' => 'A.1.4 B.2', 'venc' => '2026-11-09', 'dom' => '16 N°1559', 'sec' => 2, 'area' => 'Secretaria', 'vehiculos' => []],
            ['nombre' => 'Ferreyra da Silva, Paulino', 'dni' => '16196609', 'num' => '8199', 'lic' => 'COMUN', 'tipo' => 'A.1.4 B.2', 'venc' => '2029-01-04', 'dom' => '108 n°1963', 'sec' => 2, 'area' => 'Pintura Vial', 'vehiculos' => [233, 228]],
        ];

        foreach ($choferes as $data) {
            $choferId = DB::table('choferes')->insertGetId([
                'nombre' => $data['nombre'],
                'dni' => $data['dni'],
                'numero_empleado' => $data['num'],
                'licencia' => $data['lic'],
                'tipo_licencia' => $data['tipo'],
                'vencimiento_licencia' => $data['venc'],
                'domicilio' => $data['dom'],
                'secretaria_id' => $data['sec'],
                'area' => $data['area'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($data['vehiculos'] as $vehiculoId) {
                DB::table('choferes_vehiculos')->insert([
                    'chofer_id' => $choferId,
                    'vehiculo_id' => $vehiculoId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}