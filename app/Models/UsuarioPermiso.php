<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsuarioPermiso extends Model
{
    protected $table = 'usuario_permisos';

    protected $fillable = [
        'id_usuario',
        'id_corralon',
        'id_deposito',
        'modulo',
        'nivel_acceso',
    ];

    // Módulos vinculados a corralón/depósito
    const MODULOS_POR_UBICACION = [
        'insumos',
        'maquinarias',
        'vehiculos',
        'depositos',
        'movimientos_insumos',
        'movimientos_maquinarias',
    ];

    // Módulos globales (no dependen de corralón/depósito)
    const MODULOS_GLOBALES = [
        'empleados',
        'choferes',
        'eventos',
        'categorias_insumos',
        'categorias_maquinarias',
        'usuarios',
        'secretarias',
    ];

    // Todos los módulos disponibles
    const MODULOS = [
        'insumos' => 'Insumos',
        'maquinarias' => 'Maquinarias',
        'vehiculos' => 'Vehículos',
        'depositos' => 'Depósitos',
        'movimientos_insumos' => 'Movimientos Insumos',
        'movimientos_maquinarias' => 'Movimientos Maquinarias',
        'empleados' => 'Empleados',
        'choferes' => 'Choferes',
        'eventos' => 'Eventos',
        'categorias_insumos' => 'Categorías Insumos',
        'categorias_maquinarias' => 'Categorías Maquinarias',
        'usuarios' => 'Usuarios',
        'secretarias' => 'Secretarías',
    ];

    const NIVELES_ACCESO = [
        'ver' => 'Solo ver',
        'editar' => 'Ver y editar',
    ];

    public static function esModuloGlobal(string $modulo): bool
    {
        return in_array($modulo, self::MODULOS_GLOBALES);
    }

    public static function esModuloPorUbicacion(string $modulo): bool
    {
        return in_array($modulo, self::MODULOS_POR_UBICACION);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function corralon(): BelongsTo
    {
        return $this->belongsTo(Corralon::class, 'id_corralon');
    }

    public function deposito(): BelongsTo
    {
        return $this->belongsTo(Deposito::class, 'id_deposito');
    }
}
