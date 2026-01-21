<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Corralon extends Model
{
    protected $table = 'corralones'; // Especificar el nombre de la tabla
    
    protected $fillable = [
        'descripcion',
        'ubicacion',
        'secretaria_id',
    ];

    public function getNombreAttribute()
    {
        return $this->descripcion;
    }

    public function secretaria()
    {
        return $this->belongsTo(Secretaria::class);
    }

    public function depositos(): HasMany
    {
        return $this->hasMany(Deposito::class, 'id_corralon');
    }

    public function cuadrillas(): HasMany
    {
        return $this->hasMany(Cuadrilla::class, 'id_corralon');
    }

}
