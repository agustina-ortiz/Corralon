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
        'secretaria',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    // Accessor para formato de fecha legible
    public function getFechaFormateadaAttribute()
    {
        return $this->fecha->format('d/m/Y');
    }
}