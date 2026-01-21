<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Secretaria extends Model
{
    protected $table = 'secretarias';
    
    protected $fillable = [
        'id',
        'secretaria',
    ];

    public function corralones()
    {
        return $this->hasMany(Corralon::class);
    }

    public function eventos()
    {
        return $this->hasMany(Evento::class, 'secretaria_id');
    }
}