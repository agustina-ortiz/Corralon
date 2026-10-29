<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CategoriaLicencia extends Model
{
    protected $table = 'categorias_licencia';

    protected $fillable = [
        'codigo',
        'descripcion',
        'orden',
    ];

    public function choferes(): BelongsToMany
    {
        return $this->belongsToMany(Chofer::class, 'choferes_categorias_licencia', 'categoria_licencia_id', 'chofer_id');
    }
}
