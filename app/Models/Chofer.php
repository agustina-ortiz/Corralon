<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Chofer extends Model
{
    protected $table = 'choferes';

    protected $fillable = [
        'nombre',
        'dni',
        'numero_empleado',
        'licencia',
        'tipo_licencia',
        'vencimiento_licencia',
        'domicilio',
        'secretaria_id',
        'area',
    ];

    protected $casts = [
        'vencimiento_licencia' => 'date',
    ];

    public function secretaria(): BelongsTo
    {
        return $this->belongsTo(Secretaria::class, 'secretaria_id');
    }

    public function vehiculos(): BelongsToMany
    {
        return $this->belongsToMany(Vehiculo::class, 'choferes_vehiculos', 'chofer_id', 'vehiculo_id');
    }

    public function licenciaVencida(): bool
    {
        return $this->vencimiento_licencia && $this->vencimiento_licencia->isPast();
    }

    public function licenciaProximaAVencer(int $dias = 30): bool
    {
        return $this->vencimiento_licencia
            && !$this->vencimiento_licencia->isPast()
            && $this->vencimiento_licencia->diffInDays(now()) <= $dias;
    }
}
