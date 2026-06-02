<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'corralones_permitidos',
        'acceso_todos_corralones',
        'dashboard_widgets',
        'id_rol',
    ];

    protected $hidden = [
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'corralones_permitidos' => 'array',
        'acceso_todos_corralones' => 'boolean',
        'dashboard_widgets' => 'array',
    ];

    // Cache de permisos para evitar queries repetidas en un mismo request
    protected $permisosCache = null;

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol');
    }

    public function permisos()
    {
        return $this->hasMany(UsuarioPermiso::class, 'id_usuario');
    }

    /**
     * Retorna las claves activas de una sección del dashboard ('cards' o 'widgets'),
     * filtrando por las preferencias del usuario y sus permisos de rol.
     */
    public function dashboardActivosPara(string $seccion): array
    {
        $config    = config("dashboard.{$seccion}", []);
        $guardados = $this->dashboard_widgets[$seccion] ?? array_keys($config);

        return array_values(array_filter(
            $guardados,
            fn($key) => isset($config[$key]) && $this->tieneAccesoAModulo($config[$key]['permiso_modulo'] ?? $key)
        ));
    }

    // ================================================================
    // SISTEMA DE PERMISOS GRANULAR
    // ================================================================

    public function esAdministrador(): bool
    {
        return $this->rol && $this->rol->nombre === 'Administrador';
    }

    /**
     * Carga y cachea los permisos del usuario para el request actual
     */
    protected function getPermisosCache()
    {
        if ($this->permisosCache === null) {
            $this->permisosCache = $this->permisos()->get();
        }
        return $this->permisosCache;
    }

    /**
     * Limpia el cache de permisos (llamar después de modificar permisos)
     */
    public function limpiarCachePermisos(): void
    {
        $this->permisosCache = null;
    }

    /**
     * Verifica si el usuario tiene acceso a un módulo (ver o editar)
     */
    public function tieneAccesoAModulo(string $modulo): bool
    {
        if ($this->esAdministrador()) return true;

        return $this->getPermisosCache()->where('modulo', $modulo)->isNotEmpty();
    }

    /**
     * Verifica si el usuario puede editar (ABM) en un módulo
     */
    public function puedeEditarEnModulo(string $modulo, ?int $corralonId = null, ?int $depositoId = null): bool
    {
        if ($this->esAdministrador()) return true;

        $permisos = $this->getPermisosCache()
            ->where('modulo', $modulo)
            ->where('nivel_acceso', 'editar');

        if ($corralonId) {
            $permisos = $permisos->filter(function ($p) use ($corralonId) {
                return $p->id_corralon === null || $p->id_corralon == $corralonId;
            });
        }

        if ($depositoId) {
            $permisos = $permisos->filter(function ($p) use ($depositoId) {
                return $p->id_deposito === null || $p->id_deposito == $depositoId;
            });
        }

        return $permisos->isNotEmpty();
    }

    /**
     * Verifica si el usuario tiene acceso a un corralón específico
     */
    public function tieneAccesoACorralon($corralonId): bool
    {
        if ($this->esAdministrador()) return true;

        return $this->getPermisosCache()
            ->where('id_corralon', $corralonId)
            ->isNotEmpty();
    }

    /**
     * Obtiene los IDs de corralones a los que el usuario tiene acceso
     */
    public function getCorralonesPermitidosIds(): array
    {
        if ($this->esAdministrador()) {
            return Corralon::pluck('id')->toArray();
        }

        return $this->getPermisosCache()
            ->whereNotNull('id_corralon')
            ->pluck('id_corralon')
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Obtiene los IDs de corralones donde el usuario tiene acceso a un módulo específico
     */
    public function getCorralonesParaModulo(string $modulo): array
    {
        if ($this->esAdministrador()) {
            return Corralon::pluck('id')->toArray();
        }

        return $this->getPermisosCache()
            ->where('modulo', $modulo)
            ->whereNotNull('id_corralon')
            ->pluck('id_corralon')
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Obtiene los IDs de depósitos permitidos para un módulo
     */
    public function getDepositosPermitidosParaModulo(string $modulo, ?int $corralonId = null): array
    {
        if ($this->esAdministrador()) {
            $query = Deposito::query();
            if ($corralonId) $query->where('id_corralon', $corralonId);
            return $query->pluck('id')->toArray();
        }

        $permisos = $this->getPermisosCache()->where('modulo', $modulo);

        if ($corralonId) {
            $permisos = $permisos->where('id_corralon', $corralonId);
        }

        // Corralones donde tiene acceso a TODOS los depósitos (id_deposito = null)
        $corralonesConTodos = $permisos->whereNull('id_deposito')
            ->pluck('id_corralon')
            ->filter()
            ->toArray();

        // Depósitos específicos
        $depositosEspecificos = $permisos->whereNotNull('id_deposito')
            ->pluck('id_deposito')
            ->toArray();

        // Obtener IDs de depósitos de corralones con acceso total
        $depositosDeCorralonesCompletos = [];
        if (!empty($corralonesConTodos)) {
            $depositosDeCorralonesCompletos = Deposito::whereIn('id_corralon', $corralonesConTodos)
                ->pluck('id')
                ->toArray();
        }

        return collect(array_merge($depositosDeCorralonesCompletos, $depositosEspecificos))
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Obtiene los módulos a los que el usuario tiene acceso
     */
    public function getModulosPermitidos(): array
    {
        if ($this->esAdministrador()) {
            return array_keys(UsuarioPermiso::MODULOS);
        }

        return $this->getPermisosCache()
            ->pluck('modulo')
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Relación con los corralones permitidos (para retrocompatibilidad)
     */
    public function corralones()
    {
        $ids = $this->getCorralonesPermitidosIds();
        return Corralon::whereIn('id', $ids)->get();
    }

    // ================================================================
    // MÉTODOS DE PERMISOS POR MÓDULO (retrocompatibilidad)
    // Ahora delegan al sistema granular
    // ================================================================

    // --- INSUMOS ---
    public function puedeCrearInsumos(): bool { return $this->puedeEditarEnModulo('insumos'); }
    public function puedeEditarInsumos(): bool { return $this->puedeEditarEnModulo('insumos'); }
    public function puedeEliminarInsumos(): bool { return $this->puedeEditarEnModulo('insumos'); }

    // --- MAQUINARIAS ---
    public function puedeCrearMaquinarias(): bool { return $this->puedeEditarEnModulo('maquinarias'); }
    public function puedeEditarMaquinarias(): bool { return $this->puedeEditarEnModulo('maquinarias'); }
    public function puedeEliminarMaquinarias(): bool { return $this->puedeEditarEnModulo('maquinarias'); }

    // --- VEHÍCULOS ---
    public function puedeCrearVehiculos(): bool { return $this->puedeEditarEnModulo('vehiculos'); }
    public function puedeEditarVehiculos(): bool { return $this->puedeEditarEnModulo('vehiculos'); }
    public function puedeEliminarVehiculos(): bool { return $this->puedeEditarEnModulo('vehiculos'); }

    // --- CATEGORÍAS INSUMOS ---
    public function puedeCrearCategoriasInsumos(): bool { return $this->puedeEditarEnModulo('categorias_insumos'); }
    public function puedeEditarCategoriasInsumos(): bool { return $this->puedeEditarEnModulo('categorias_insumos'); }
    public function puedeEliminarCategoriasInsumos(): bool { return $this->puedeEditarEnModulo('categorias_insumos'); }

    // --- CATEGORÍAS MAQUINARIAS ---
    public function puedeCrearCategoriasMaquinarias(): bool { return $this->puedeEditarEnModulo('categorias_maquinarias'); }
    public function puedeEditarCategoriasMaquinarias(): bool { return $this->puedeEditarEnModulo('categorias_maquinarias'); }
    public function puedeEliminarCategoriasMaquinarias(): bool { return $this->puedeEditarEnModulo('categorias_maquinarias'); }

    // --- DEPÓSITOS ---
    public function puedeCrearDepositos(): bool { return $this->puedeEditarEnModulo('depositos'); }
    public function puedeEditarDepositos(): bool { return $this->puedeEditarEnModulo('depositos'); }
    public function puedeEliminarDepositos(): bool { return $this->puedeEditarEnModulo('depositos'); }

    // --- EVENTOS ---
    public function puedeCrearEventos(): bool { return $this->puedeEditarEnModulo('eventos'); }
    public function puedeEditarEventos(): bool { return $this->puedeEditarEnModulo('eventos'); }
    public function puedeEliminarEventos(): bool { return $this->puedeEditarEnModulo('eventos'); }

    // --- EMPLEADOS ---
    public function puedeCrearEmpleados(): bool { return $this->puedeEditarEnModulo('empleados'); }
    public function puedeEditarEmpleados(): bool { return $this->puedeEditarEnModulo('empleados'); }
    public function puedeEliminarEmpleados(): bool { return $this->puedeEditarEnModulo('empleados'); }

    // --- CHOFERES ---
    public function puedeCrearChoferes(): bool { return $this->puedeEditarEnModulo('choferes'); }
    public function puedeEditarChoferes(): bool { return $this->puedeEditarEnModulo('choferes'); }
    public function puedeEliminarChoferes(): bool { return $this->puedeEditarEnModulo('choferes'); }

    // --- USUARIOS ---
    public function puedeCrearUsuarios(): bool { return $this->puedeEditarEnModulo('usuarios'); }
    public function puedeEditarUsuarios(): bool { return $this->puedeEditarEnModulo('usuarios'); }
    public function puedeEliminarUsuarios(): bool { return $this->puedeEditarEnModulo('usuarios'); }

    // --- SECRETARIAS ---
    public function puedeCrearSecretarias(): bool { return $this->puedeEditarEnModulo('secretarias'); }
    public function puedeEditarSecretarias(): bool { return $this->puedeEditarEnModulo('secretarias'); }
    public function puedeEliminarSecretarias(): bool { return $this->puedeEditarEnModulo('secretarias'); }

    // --- MOVIMIENTOS INSUMOS ---
    public function puedeCrearMovimientosInsumos(): bool { return $this->puedeEditarEnModulo('movimientos_insumos'); }
    public function puedeCrearTransferenciasInsumos(): bool { return $this->puedeEditarEnModulo('movimientos_insumos'); }

    // --- MOVIMIENTOS MAQUINARIAS ---
    public function puedeCrearMovimientosMaquinarias(): bool { return $this->puedeEditarEnModulo('movimientos_maquinarias'); }
    public function puedeEditarMovimientosMaquinarias(): bool { return $this->puedeEditarEnModulo('movimientos_maquinarias'); }
    public function puedeEliminarMovimientosMaquinarias(): bool { return $this->puedeEditarEnModulo('movimientos_maquinarias'); }
    public function puedeCrearTransferenciasMaquinarias(): bool { return $this->puedeEditarEnModulo('movimientos_maquinarias'); }
}
