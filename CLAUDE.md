# CLAUDE.md — Corralon Project

## Descripción del proyecto

Sistema de gestión de inventario y recursos para corralones municipales. Permite administrar insumos, maquinarias, vehículos, empleados y eventos. Soporta múltiples corralones con control de acceso por deposito.

---

## Stack tecnológico

- **Backend:** Laravel 12, PHP 8.2+
- **Frontend:** Livewire 3 + Volt, Tailwind CSS 3, Alpine.js
- **UI Components:** Livewire Flux 2.9
- **Build tool:** Vite
- **Base de datos:** MySQL (`corralon`)
- **Auth:** Laravel Breeze (email/password)

---

## Estructura del proyecto

```
app/
  Http/Controllers/       # DashboardController, Auth
  Livewire/               # Componentes reactivos (ABM*, Transferencias*, Dashboard)
  Models/                 # Modelos Eloquent
  Traits/                 # FiltraPorCorralon, FiltraPorCorralonViaDeposito
  View/Components/        # Blade components reutilizables
database/
  migrations/             # 45+ migraciones
  seeders/                # 9 seeders de datos de prueba
resources/views/
  livewire/               # Templates de componentes Livewire
  layouts/                # app.blade.php (layout principal), guest.blade.php
  pages/                  # Páginas de auth (Volt)
routes/
  web.php                 # Rutas principales
  auth.php                # Rutas de autenticación
```

---

## Modelos principales

| Modelo | Tabla | Descripción |
|--------|-------|-------------|
| `User` | `users` | Usuarios con roles y acceso por corralon |
| `Rol` | `roles` | Permisos por módulo (booleanos) |
| `Corralon` | `corralones` | Sedes/ubicaciones |
| `Deposito` | `depositos` | Subdivisiones dentro de un corralon |
| `Insumo` | `insumos` | Items de inventario |
| `Maquinaria` | `maquinarias` | Equipos/máquinas |
| `Vehiculo` | `vehiculos` | Flota vehicular |
| `Empleado` | `empleados` | Personal |
| `Chofer` | `choferes` | Conductores |
| `Evento` | `eventos` | Eventos programados |
| `Secretaria` | `secretarias` | Dependencias/secretarías municipales |
| `MovimientoInsumo` | `movimiento_insumos` | Movimientos de stock |
| `MovimientoMaquinaria` | `movimiento_maquinarias` | Movimientos de equipos |
| `TipoMovimiento` | `tipo_movimientos` | Tipos: Entrada, Salida, Transferencia, Inventario |
| `CategoriaInsumo` | `categorias_insumos` | Categorías de insumos |
| `CategoriaMaquinaria` | `categoria_maquinarias` | Categorías de maquinaria |
| `DocumentoVehiculo` | `documento_vehiculos` | Documentos de vehículos |
| `MovimientoEncabezado` | `movimiento_encabezados` | Cabecera para agrupar movimientos |

---

## Rutas principales

| Ruta | Componente | Permiso requerido |
|------|-----------|------------------|
| `/dashboard` | DashboardController | autenticado |
| `/insumos` | AbmInsumos | `lInsumosABM` |
| `/maquinarias` | AbmMaquinarias | `lMaquinariasABM` |
| `/vehiculos` | AbmVehiculos | `lVehiculosABM` |
| `/depositos` | AbmDepositos | `lDepositosABM` |
| `/categorias-insumos` | AbmCategoriasInsumos | `lCategoriasInsumosABM` |
| `/categorias-maquinarias` | AbmCategoriasMaquinarias | `lCategoriasMaquinariasABM` |
| `/eventos` | AbmEventos | `lEventosABM` |
| `/empleados` | AbmEmpleados | `lEmpleadosABM` |
| `/usuarios` | AbmUsuarios | `lUsuariosABM` |
| `/transferencias-insumos` | TransferenciasInsumos | `lMovimientosInsumos` |
| `/transferencias-maquinarias` | TransferenciasMaquinarias | `lMovimientosMaquinarias` |

---

## Autorización (RBAC)

### Roles
- **Administrador** — acceso total
- **Visualizador** — solo lectura

### Permisos en tabla `roles` (booleanos)
`lInsumosABM`, `lMaquinariasABM`, `lVehiculosABM`, `lCategoriasInsumosABM`, `lCategoriasMaquinariasABM`, `lDepositosABM`, `lEventosABM`, `lEmpleadosABM`, `lUsuariosABM`, `lMovimientosInsumos`, `lMovimientosMaquinarias`

### Acceso por corralon
- `acceso_todos_corralones` (bool) en `users`
- `corralones_permitidos` (JSON array de IDs) en `users`
- Traits `FiltraPorCorralon` y `FiltraPorCorralonViaDeposito` aplican scopes automáticamente

---

## Lógica de stock

- El stock **no se almacena directamente**, se calcula desde los movimientos
- **Stock calculado** = suma de Entradas − suma de Salidas
- Tipos de movimiento: `Entrada`, `Salida`, `Transferencia`, `Inventario`
- Transferencias: para el deposito origen es Salida, para el destino es Entrada
- `calcularStockActual()` recalcula desde el historial de movimientos
- Alertas de stock bajo mínimo en el dashboard

---

## Convenciones del proyecto

- Todo el código y la UI están en **español** (variables, columnas, labels, comentarios)
- Los componentes ABM (Alta/Baja/Modificación) usan `WithPagination` + modales
- Los formularios en modales tienen validación con `$rules` en Livewire
- Color de marca: verde `#77BF43` (sidebar activo, hover)
- Los seeders proveen datos de prueba para desarrollo local

---

## Comandos útiles

```bash
# Desarrollo
composer install
npm install
npm run dev
php artisan serve

# Base de datos
php artisan migrate
php artisan db:seed

# Comandos personalizados
php artisan insumos:recalcular-stocks   # Recalcula stocks desde movimientos
php artisan usuarios:rehash-passwords   # Rehashea contraseñas
```

---

## Notas de desarrollo

- **DB local:** MySQL sin contraseña, usuario `root`, base `corralon`
- **Sesiones:** almacenadas en archivos
- **Mail:** modo `log` en desarrollo (no envía correos reales)
- **Verificación de email:** habilitada en producción
- El proyecto está en **desarrollo activo**; los commits recientes se enfocaron en roles/permisos, VTV de vehículos, choferes y seeders
