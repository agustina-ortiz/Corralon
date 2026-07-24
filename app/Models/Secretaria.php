<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Secretaria extends Model
{
    protected $table = 'secretarias';

    protected $fillable = [
        'id',
        'secretaria',
    ];

    /**
     * Depósitos asignados a la secretaría (pivote muchos-a-muchos).
     * Las asignaciones se cargan directamente en la base (tabla depositos_secretarias).
     */
    public function depositos(): BelongsToMany
    {
        return $this->belongsToMany(
            Deposito::class,
            'depositos_secretarias',
            'id_secretaria',
            'id_deposito'
        )->withTimestamps();
    }

    public function corralones()
    {
        return $this->hasMany(Corralon::class);
    }

    public function areas()
    {
        return $this->hasMany(Area::class, 'id_secretaria');
    }

    public function eventos()
    {
        return $this->hasMany(Evento::class, 'secretaria_id');
    }
}