<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evento extends Model
{
    protected $table = 'eventos';
    
    protected $fillable = [
        'evento',
        'fecha',
        'ubicacion',
        'secretaria_id',
        'evento_anual',
    ];

    protected $casts = [
        'fecha' => 'date',
        'evento_anual' => 'boolean',
    ];

    // Relación con Secretaria
    public function secretaria()
    {
        return $this->belongsTo(Secretaria::class, 'secretaria_id');
    }

    // Accessor para formato de fecha legible
    public function getFechaFormateadaAttribute()
    {
        return $this->fecha->format('d/m/Y');
    }
}