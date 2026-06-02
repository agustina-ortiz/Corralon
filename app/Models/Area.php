<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $table = 'areas';

    protected $fillable = [
        'id_secretaria',
        'area',
    ];

    public function secretaria()
    {
        return $this->belongsTo(Secretaria::class, 'id_secretaria');
    }
}
