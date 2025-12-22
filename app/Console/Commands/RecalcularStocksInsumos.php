<?php

namespace App\Console\Commands;

use App\Models\Insumo;
use Illuminate\Console\Command;

class RecalcularStocksInsumos extends Command
{
    protected $signature = 'insumos:recalcular-stocks {--insumo_id=}';
    protected $description = 'Recalcula el stock actual de todos los insumos basado en sus movimientos';

    public function handle()
    {
        $insumoId = $this->option('insumo_id');
        
        if ($insumoId) {
            $insumo = Insumo::find($insumoId);
            if (!$insumo) {
                $this->error("Insumo con ID {$insumoId} no encontrado");
                return 1;
            }
            
            $insumo->sincronizarStock();
            $this->info("Stock recalculado para insumo: {$insumo->insumo}");
            return 0;
        }
        
        $this->info('Recalculando stocks de todos los insumos...');
        $bar = $this->output->createProgressBar(Insumo::count());
        
        Insumo::chunk(100, function ($insumos) use ($bar) {
            foreach ($insumos as $insumo) {
                $insumo->sincronizarStock();
                $bar->advance();
            }
        });
        
        $bar->finish();
        $this->newLine();
        $this->info('¡Stocks recalculados correctamente!');
        
        return 0;
    }
}