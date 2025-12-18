<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoriaMaquinaria extends Model
{
    protected $table = 'categoria_maquinarias';
    
    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    public function maquinarias(): HasMany
    {
        return $this->hasMany(Maquinaria::class, 'id_categoria_maquinaria');
    }
}
