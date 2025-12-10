<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cuadrilla extends Model
{
    protected $table = 'cuadrillas';
    
    protected $fillable = [
        'descripcion',
        'id_corralon',
        'id_deposito',
    ];

    public function corralon(): BelongsTo
    {
        return $this->belongsTo(Corralon::class, 'id_corralon');
    }

    public function deposito(): BelongsTo
    {
        return $this->belongsTo(Deposito::class, 'id_deposito');
    }
}
