<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vehiculo;
use App\Models\Deposito;

class VehiculoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $depositos = Deposito::pluck('id')->toArray();
        
        if (empty($depositos)) {
            $this->command->error('No hay depósitos disponibles. Por favor, ejecute primero el seeder de depósitos.');
            return;
        }

        $vehiculos = [
            [
                'vehiculo' => 'Camión Volcador Mercedes Benz 1518',
                'marca' => 'Mercedes Benz',
                'modelo' => '2020',
                'patente' => 'AB123CD',
                'nro_motor' => 'MB1518-2020-001',
                'nro_chasis' => 'CHM1518-2020-001',
                'estado' => 'disponible',
            ],
            [
                'vehiculo' => 'Camioneta Ford Ranger XLT 3.2',
                'marca' => 'Ford',
                'modelo' => '2019',
                'patente' => 'EF456GH',
                'nro_motor' => 'FRD-2019-456',
                'nro_chasis' => 'CHFRD-2019-456',
                'estado' => 'en_uso',
            ],
            [
                'vehiculo' => 'Pala Cargadora Caterpillar 950M',
                'marca' => 'Caterpillar',
                'modelo' => '2021',
                'patente' => 'MAQ001',
                'nro_motor' => 'CAT950M-2021-789',
                'nro_chasis' => 'CHCAT950M-2021-789',
                'estado' => 'disponible',
            ],
            [
                'vehiculo' => 'Motoniveladora Komatsu GD555-5',
                'marca' => 'Komatsu',
                'modelo' => '2018',
                'patente' => 'MAQ002',
                'nro_motor' => 'KOM-GD555-2018-321',
                'nro_chasis' => 'CHKOM-GD555-2018-321',
                'estado' => 'mantenimiento',
            ],
            [
                'vehiculo' => 'Camión Cisterna Iveco Tector 170E25',
                'marca' => 'Iveco',
                'modelo' => '2020',
                'patente' => 'IJ789KL',
                'nro_motor' => 'IVC-2020-654',
                'nro_chasis' => 'CHIVC-2020-654',
                'estado' => 'disponible',
            ],
            [
                'vehiculo' => 'Retroexcavadora John Deere 310SL',
                'marca' => 'John Deere',
                'modelo' => '2019',
                'patente' => 'MAQ003',
                'nro_motor' => 'JD310SL-2019-987',
                'nro_chasis' => 'CHJD310SL-2019-987',
                'estado' => 'en_uso',
            ],
            [
                'vehiculo' => 'Camioneta Toyota Hilux DX 2.4',
                'marca' => 'Toyota',
                'modelo' => '2021',
                'patente' => 'MN012OP',
                'nro_motor' => 'TOY-2021-147',
                'nro_chasis' => 'CHTOY-2021-147',
                'estado' => 'disponible',
            ],
            [
                'vehiculo' => 'Compactadora Vibrante Bomag BW 177 D-5',
                'marca' => 'Bomag',
                'modelo' => '2017',
                'patente' => 'MAQ004',
                'nro_motor' => 'BMG-2017-258',
                'nro_chasis' => 'CHBMG-2017-258',
                'estado' => 'fuera_de_servicio',
            ],
            [
                'vehiculo' => 'Camión Compactador Scania P 320',
                'marca' => 'Scania',
                'modelo' => '2019',
                'patente' => 'QR345ST',
                'nro_motor' => 'SCN-2019-369',
                'nro_chasis' => 'CHSCN-2019-369',
                'estado' => 'en_uso',
            ],
            [
                'vehiculo' => 'Minicargadora Bobcat S650',
                'marca' => 'Bobcat',
                'modelo' => '2020',
                'patente' => 'MAQ005',
                'nro_motor' => 'BBC-2020-741',
                'nro_chasis' => 'CHBBC-2020-741',
                'estado' => 'disponible',
            ],
            [
                'vehiculo' => 'Camión Grúa Volkswagen Delivery 11.180',
                'marca' => 'Volkswagen',
                'modelo' => '2018',
                'patente' => 'UV678WX',
                'nro_motor' => 'VW-2018-852',
                'nro_chasis' => 'CHVW-2018-852',
                'estado' => 'mantenimiento',
            ],
            [
                'vehiculo' => 'Excavadora Hidráulica Volvo EC140E',
                'marca' => 'Volvo',
                'modelo' => '2021',
                'patente' => 'MAQ006',
                'nro_motor' => 'VLV-2021-963',
                'nro_chasis' => 'CHVLV-2021-963',
                'estado' => 'disponible',
            ],
            [
                'vehiculo' => 'Camioneta Chevrolet S10 LS 2.8',
                'marca' => 'Chevrolet',
                'modelo' => '2020',
                'patente' => 'YZ901AB',
                'nro_motor' => 'CHV-2020-159',
                'nro_chasis' => 'CHCHV-2020-159',
                'estado' => 'en_uso',
            ],
            [
                'vehiculo' => 'Rodillo Compactador Hamm HD 12 VV',
                'marca' => 'Hamm',
                'modelo' => '2019',
                'patente' => 'MAQ007',
                'nro_motor' => 'HMM-2019-753',
                'nro_chasis' => 'CHHMM-2019-753',
                'estado' => 'disponible',
            ],
            [
                'vehiculo' => 'Camión Baranda Volcable Agrale 10000 TCA',
                'marca' => 'Agrale',
                'modelo' => '2021',
                'patente' => 'CD234EF',
                'nro_motor' => 'AGR-2021-486',
                'nro_chasis' => 'CHAGR-2021-486',
                'estado' => 'disponible',
            ],
        ];

        foreach ($vehiculos as $vehiculoData) {
            Vehiculo::create([
                'vehiculo' => $vehiculoData['vehiculo'],
                'marca' => $vehiculoData['marca'],
                'modelo' => $vehiculoData['modelo'],
                'patente' => $vehiculoData['patente'],
                'nro_motor' => $vehiculoData['nro_motor'],
                'nro_chasis' => $vehiculoData['nro_chasis'],
                'id_secretaria' => 'sec',
                'estado' => $vehiculoData['estado'],
                'id_deposito' => $depositos[array_rand($depositos)],
            ]);
        }

        $this->command->info('✓ 15 vehículos creados exitosamente');
    }
}