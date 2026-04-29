# CLAUDE.md — Corralon Project

## Descripción del proyecto

Sistema de gestión de inventario y recursos para corralones municipales (Municipalidad de Mercedes). Permite administrar insumos, maquinarias, vehículos, empleados, choferes y eventos. Soporta múltiples corralones con control de acceso por deposito.

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
  Console/Commands/       # RecalcularStocksInsumos, RehashPasswords
  Http/Controllers/       # DashboardController, Auth/VerifyEmailController
  Livewire/               # Componentes reactivos (ABM*, Transferencias*, Dashboard)
    Actions/              # Logout
    Forms/                # LoginForm
  Models/                 # Modelos Eloquent (20 modelos)
  Traits/                 # FiltraPorCorralon, FiltraPorCorralonViaDeposito
  View/Components/        # AppLayout, GuestLayout
database/
  migrations/             # 50+ migraciones
  seeders/                # 9 seeders de datos de prueba
resources/views/
  livewire/               # Templates de componentes Livewire
  layouts/                # app.blade.php (layout principal), guest.blade.php
  pages/                  # Páginas de auth (Volt)
routes/
  web.php                 # Rutas principales (15 rutas)
  auth.php                # Rutas de autenticación (Volt)
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
| `Chofer` | `choferes` | Conductores (licencia, vencimientos, vehículos asignados) |
| `Empleado` | `empleados` | Personal |
| `Evento` | `eventos` | Eventos programados |
| `Secretaria` | `secretarias` | Dependencias/secretarías municipales |
| `MovimientoInsumo` | `movimiento_insumos` | Movimientos de stock |
| `MovimientoMaquinaria` | `movimiento_maquinarias` | Movimientos de equipos |
| `TipoMovimiento` | `tipo_movimientos` | Tipos: Entrada, Salida, Transferencia, Inventario |
| `CategoriaInsumo` | `categorias_insumos` | Categorías de insumos |
| `CategoriaMaquinaria` | `categoria_maquinarias` | Categorías de maquinaria |
| `DocumentoVehiculo` | `documentos_vehiculos` | Documentos de vehículos |
| `MovimientoEncabezado` | `movimiento_encabezados` | Cabecera para agrupar movimientos |
| `TipoVehiculo` | `tipos_vehiculos` | Tipos de vehículo |
| `Cuadrilla` | `cuadrillas` | Cuadrillas de trabajo (ligadas a corralon y deposito) |

---

## Rutas principales

| Ruta | Componente | Permiso requerido |
|------|-----------|------------------|
| `/dashboard` | DashboardController | autenticado |
| `/insumos` | AbmInsumos | `lInsumosABM` |
| `/maquinarias` | AbmMaquinarias | `lMaquinariasABM` |
| `/vehiculos` | AbmVehiculos | `lVehiculosABM` |
| `/choferes` | AbmChoferes | `lChoferesABM` |
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
`lInsumosABM`, `lMaquinariasABM`, `lVehiculosABM`, `lChoferesABM`, `lCategoriasInsumosABM`, `lCategoriasMaquinariasABM`, `lDepositosABM`, `lEventosABM`, `lEmpleadosABM`, `lUsuariosABM`, `lMovimientosInsumos`, `lMovimientosMaquinarias`

### Métodos de autorización en `User`
- `puedeCrear*()`, `puedeEditar*()`, `puedeEliminar*()` — métodos individuales por entidad
- `tieneAccesoACorralon($id)` — verifica si el usuario puede acceder a un corralon específico
- `getCorralonesPermitidosIds()` — retorna array de IDs permitidos

### Acceso por corralon
- `acceso_todos_corralones` (bool) en `users`
- `corralones_permitidos` (JSON array de IDs) en `users`
- Traits `FiltraPorCorralon` y `FiltraPorCorralonViaDeposito` aplican scopes automáticamente
- Scope: `->porCorralonesPermitidos()` se usa en todas las queries de ABM

---

## Lógica de stock (Insumos)

- El stock **no se almacena directamente**, se calcula desde los movimientos
- `stock_actual` en la tabla `insumos` se sincroniza con `sincronizarStock()` tras cada movimiento
- **Stock calculado** = suma de Entradas − suma de Salidas
- Tipos de movimiento: `Entrada`, `Salida`, `Transferencia`, `Inventario`
- Transferencias: para el deposito origen es Salida, para el destino es Entrada (se determina por `id_deposito_entrada`)
- `calcularStockActual()` recalcula desde el historial de movimientos
- `stockBajoMinimo()` — bool, compara `stock_actual` con `stock_minimo`
- Alertas de stock bajo mínimo en el dashboard
- Campo `unidad` en `insumos` usa valores fijos: `UNIDAD`, `LITROS`, `TAMBOR`, `METRO`, `ROLLO X 100 MT`, `PAQUETE`, `BOLSA`, `BALDE` (validación `in:` en backend y select en UI)

## Lógica de cantidad (Maquinarias)

- `cantidad` en `maquinarias` es la cantidad total registrada en el sistema
- `getCantidadDisponibleAttribute()` — descuenta las unidades actualmente en uso (salidas sin devolución)
- `getCantidadEnDeposito($depositoId)` — cantidad disponible en un deposito específico
- `getCantidadTotalDisponible()` — total disponible en todos los depositos

## Referencias polimórficas en movimientos

Los movimientos (`movimiento_insumos`, `movimiento_maquinarias`) tienen:
- `id_referencia` — ID de la entidad asociada
- `tipo_referencia` — enum: `empleado`, `maquina`, `evento`, `secretaria`, `inventario`, `deposito`, `mantenimiento`, `Transferencia`

---

## Seeders

| Seeder | Descripción |
|--------|-------------|
| `CorralonesSeeder` | Corralones de prueba |
| `DepositosSeeder` | Depósitos vinculados a corralones |
| `CategoriasInsumosSeeder` | Categorías de insumos |
| `CategoriasMaquinariasSeeder` | Categorías de maquinaria |
| `InsumosSeeder` | Insumos con movimientos iniciales de stock |
| `InsumosEconomiaSeeder` | Insumos específicos de la secretaría de economía |
| `MaquinariasSeeder` | Maquinarias de prueba |
| `VehiculosSeeder` | Vehículos con fechas de VTV y póliza |
| `ChoferesSeeder` | Choferes con licencias y asignaciones de vehículos |

Orden de ejecución: Corralones → Depositos → Categorías → Insumos → Maquinarias → Vehículos → Choferes

---

## Dashboard — Alertas

El dashboard muestra:
- **Stock bajo mínimo** — insumos donde `stock_actual < stock_minimo`
- **VTV próximas a vencer** — vehículos con `vencimiento_vtv` en los próximos 30 días
- **Licencias de choferes próximas a vencer** — via `licenciaProximaAVencer()`
- **Eventos próximos** — eventos con fecha cercana
- **Vehículos en uso** — vehículos con estado activo/en circulación

---

## Convenciones del proyecto

### Código y UI
- Todo el código y la UI están en **español** (variables, columnas, labels, comentarios)
- Los componentes ABM (Alta/Baja/Modificación) usan `WithPagination` + modales
- Los formularios en modales tienen validación con `rules()` en Livewire
- Color de marca: verde `#77BF43` (sidebar activo, hover)
- Los seeders proveen datos de prueba para desarrollo local

### Naming conventions
- **Tablas:** plural en español (`corralones`, `depositos`, `insumos`, `choferes`)
- **Foreign keys:** `id_[modelo]` (`id_corralon`, `id_deposito`, `id_usuario`, `id_rol`)
- **Scopes:** `scopePorCorralonesPermitidos()`, `scopeConStockBajo()`
- **Métodos de permiso:** `puedeCear*()`, `puedeEditar*()`, `puedeEliminar*()`
- **Métodos de tipo:** `esEntrada()`, `esSalida()`, `esTransferencia()`
- **Accesors:** patrón `get[Atributo]Attribute()` con `$appends`

### Patrones de componentes Livewire (ABM)
- `WithPagination` — paginación (10 ítems por página por defecto)
- Filtros: búsqueda por texto + filtros múltiples (categoría, unidad, deposito, estado de stock)
- Modales para crear/editar
- Verificación de permisos antes de cada operación CRUD
- Filtrado automático por corralon via `->porCorralonesPermitidos()`

### Patrones de acceso rápido en TransferenciasInsumos

El componente `TransferenciasInsumos` implementa atajos desde la lista de movimientos:

**Abrir modal "Nuevo Movimiento" con insumo preseleccionado (paso 2):**
- Click en el nombre del insumo en cualquier fila de la lista
- Llama a `abrirModalConInsumo($insumoId)` → internamente usa `seleccionarInsumo()` que setea `insumo_seleccionado`, `tipos_movimiento_disponibles` y `paso_actual = 2`
- Solo visible si el usuario tiene `puedeCrearMovimientos`

**Abrir modal "Nueva Transferencia" con origen precargado:**
- Botón de flechas en cada fila, solo visible si `puedeCrearTransferencias`
- Para **movimientos individuales**: `abrirModalTransferenciaDesdeMovimiento($depositoId, $insumoId)` — usa el depósito del insumo como origen y pre-agrega el insumo
- Para **transferencias**: `abrirModalTransferenciaDesdeTransferencia($encabezadoId)` — usa el `depositoDestino` de la transferencia como nuevo origen y pre-agrega todos los insumos de `movimientosEntrada`
- En ambos casos se setea `id_deposito_origen` e `id_corralon_origen`

> **Atención:** `movimientosEntrada()` en `MovimientoEncabezado` tiene una condición dinámica sobre `$this->id_deposito_destino`. No usar con eager loading (`with()`); siempre llamar como método (`->movimientosEntrada()->get()`) después de tener el modelo cargado.

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
php artisan insumos:recalcular-stocks              # Recalcula stocks desde movimientos
php artisan insumos:recalcular-stocks --insumo_id= # Recalcula un insumo específico
php artisan usuarios:rehash-passwords              # Rehashea contraseñas a bcrypt
```

---

## Notas de desarrollo

- **DB local:** MySQL sin contraseña, usuario `root`, base `corralon`
- **Sesiones:** almacenadas en base de datos (`SESSION_DRIVER=database`)
- **Mail:** modo `log` en desarrollo (no envía correos reales)
- **Verificación de email:** habilitada en producción
- **Caché:** driver base de datos en desarrollo
- **Cola (queue):** driver base de datos
- **Bcrypt:** `BCRYPT_ROUNDS=12`
- El proyecto está en **desarrollo activo**
