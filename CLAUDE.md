# CLAUDE.md — Corralon Project

## Descripción del proyecto

Sistema de gestión de inventario y recursos para corralones municipales (Municipalidad de Mercedes). Permite administrar insumos, maquinarias, vehículos, empleados, choferes y eventos. Soporta múltiples corralones con control de acceso por deposito.

---

## Stack tecnológico

- **Backend:** Laravel 12, PHP 8.2+
- **Frontend:** Livewire 3 + Volt, Tailwind CSS 3, Alpine.js
- **UI Components:** Livewire Flux 2.9
- **Build tool:** Vite
- **Base de datos:** MySQL (`corralon`) + conexión secundaria `munimer_inasi` (empleados municipales)
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
  Models/                 # Modelos Eloquent (22 modelos, incluye EmpleadoMunicipal con conexión externa)
  Traits/                 # FiltraPorPermisos, FiltraPorPermisosCorralon
  View/Components/        # AppLayout, GuestLayout
database/
  migrations/             # 50+ migraciones
  seeders/                # 10 seeders de datos de prueba/inicialización
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
| `Rol` | `roles` | Roles del sistema (nombre + descripción) |
| `Corralon` | `corralones` | Sedes/ubicaciones |
| `Deposito` | `depositos` | Subdivisiones dentro de un corralon |
| `Insumo` | `insumos` | Items de inventario |
| `Maquinaria` | `maquinarias` | Equipos/máquinas |
| `Vehiculo` | `vehiculos` | Flota vehicular (nro_patrimonio, marca_modelo, anio, patente, combustible, VTV, póliza, origen, jurisdiccion_procedencia, nro_telepase) |
| `Chofer` | `choferes` | Conductores (licencia, vencimientos, vehículos asignados) |
| `Empleado` | `empleados` | Personal |
| `Evento` | `eventos` | Eventos programados |
| `Secretaria` | `secretarias` | Dependencias/secretarías municipales |
| `MovimientoInsumo` | `movimiento_insumos` | Movimientos de stock |
| `MovimientoMaquinaria` | `movimiento_maquinarias` | Movimientos de equipos |
| `TipoMovimiento` | `tipo_movimientos` | Tipos de movimiento (columna `tipo`: I=Insumos, M=Maquinaria, IM=ambos) |
| `CategoriaInsumo` | `categorias_insumos` | Categorías de insumos |
| `CategoriaMaquinaria` | `categoria_maquinarias` | Categorías de maquinaria |
| `DocumentoVehiculo` | `documentos_vehiculos` | Documentos de vehículos |
| `MovimientoEncabezado` | `movimiento_encabezados` | Cabecera para agrupar movimientos |
| `TipoVehiculo` | `tipos_vehiculos` | Tipos de vehículo |
| `Cuadrilla` | `cuadrillas` | Cuadrillas de trabajo (ligadas a corralon y deposito) |
| `UsuarioPermiso` | `usuario_permisos` | Permisos granulares: usuario + corralón + depósito + módulo + nivel |
| `ComprobanteMovimiento` | `comprobantes_movimiento` | Archivos adjuntos a movimientos de insumos (órdenes de compra, recibos) |
| `EmpleadoMunicipal` | `in_maestro` (BD: `munimer_inasi`) | Empleados municipales del sistema INASI (conexión secundaria, solo lectura). PK: `LEGAJO`. Scope `activos()`, accessor `nombre_formateado` |

---

## Rutas principales

| Ruta | Componente | Módulo de permiso |
|------|-----------|------------------|
| `/dashboard` | DashboardController | autenticado |
| `/insumos` | AbmInsumos | `insumos` |
| `/maquinarias` | AbmMaquinarias | `maquinarias` |
| `/vehiculos` | AbmVehiculos | `vehiculos` |
| `/choferes` | AbmChoferes | `choferes` |
| `/depositos` | AbmDepositos | `depositos` |
| `/categorias-insumos` | AbmCategoriasInsumos | `categorias_insumos` |
| `/categorias-maquinarias` | AbmCategoriasMaquinarias | `categorias_maquinarias` |
| `/eventos` | AbmEventos | `eventos` |
| `/empleados` | AbmEmpleados | `empleados` |
| `/usuarios` | AbmUsuarios | `usuarios` |
| `/transferencias-insumos` | TransferenciasInsumos | `movimientos_insumos` |
| `/transferencias-maquinarias` | TransferenciasMaquinarias | `movimientos_maquinarias` |
| `/comprobantes/{id}/ver` | Closure (ruta) | autenticado |
| `/comprobantes/{id}/descargar` | Closure (ruta) | autenticado |

---

## Autorización — Sistema de Permisos Granular

### Tabla `usuario_permisos`
Cada fila es un permiso individual: usuario + corralón + depósito + módulo + nivel de acceso.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_usuario` | FK → users | Usuario al que aplica |
| `id_corralon` | FK → corralones (nullable) | NULL = permiso global (módulos sin ubicación) |
| `id_deposito` | FK → depositos (nullable) | NULL = todos los depósitos del corralón |
| `modulo` | string(50) | Clave del módulo (ver constantes en `UsuarioPermiso`) |
| `nivel_acceso` | enum: `ver`, `editar` | `ver` = solo lectura, `editar` = ABM completo |

### Roles
- **Administrador** — bypasea todas las verificaciones (`esAdministrador()` retorna true)
- **Otros roles** — usan `usuario_permisos` para definir acceso granular

### Módulos por ubicación (requieren `id_corralon`)
`insumos`, `maquinarias`, `vehiculos`, `depositos`, `movimientos_insumos`, `movimientos_maquinarias`

### Módulos globales (sin `id_corralon`)
`empleados`, `choferes`, `eventos`, `categorias_insumos`, `categorias_maquinarias`, `usuarios`

### Métodos de autorización en `User`
- `esAdministrador()` — rol Administrador, acceso total
- `tieneAccesoAModulo($modulo)` — puede ver el módulo (sidebar)
- `puedeEditarEnModulo($modulo, $corralonId?, $depositoId?)` — puede ABM
- `getCorralonesParaModulo($modulo)` — IDs de corralones para un módulo
- `getDepositosPermitidosParaModulo($modulo, $corralonId?)` — IDs de depósitos permitidos
- `getModulosPermitidos()` — array de módulos accesibles
- `puedeCrear*()`, `puedeEditar*()`, `puedeEliminar*()` — retrocompatibilidad, delegan a `puedeEditarEnModulo()`
- Cache por request: `$permisosCache` evita queries repetidas

### Traits de filtrado
- `FiltraPorPermisos` — modelos con `id_deposito` (Insumo, Maquinaria, Vehiculo). Cada modelo define `const MODULO_PERMISO`.
- `FiltraPorPermisosCorralon` — modelos con `id_corralon` (Deposito)
- Scope retrocompatible: `->porCorralonesPermitidos()` sigue funcionando

### Sidebar
- Links filtrados por `$u->tieneAccesoAModulo('modulo')` en `app.blade.php`
- Secciones enteras se ocultan si el usuario no tiene acceso a ningún módulo de esa sección

### Gestión de permisos (AbmUsuarios)
- Si el rol = Administrador → acceso total, no se configuran permisos
- Si otro rol → UI de permisos:
  - **Módulos Globales**: toggle por módulo (Ver / Editar)
  - **Por Corralón**: seleccionar corralones, luego toggle módulos dentro de cada uno (Ver / Editar)
  - Soporte para depósitos específicos por módulo (opcional)

---

## Lógica de stock (Insumos)

- El stock **no se almacena directamente**, se calcula desde los movimientos
- `stock_actual` en la tabla `insumos` se sincroniza con `sincronizarStock()` tras cada movimiento
- `sincronizarStock()` usa `TipoMovimiento::NOMBRES_ENTRADA` y `NOMBRES_SALIDA` para clasificar movimientos (por nombre, no por columna `tipo`)
- **Stock calculado** = suma de Entradas − suma de Salidas
- Tipos de movimiento para insumos:
  - **Entradas** (suman stock): Carga de Stock, Ajuste Positivo, Inventario Inicial, Transferencia Entrada, Devolución, Entrada Reposición
  - **Salidas** (restan stock): Ajuste Negativo, Transferencia Salida, Asignación con Reposición, Asignación sin Reposición
  - **Neutral** (no afecta stock): Baja Reposición
- Tipos de movimiento para maquinarias:
  - **Entradas** (suman stock): Carga de Stock, Inventario Inicial Maquinaria, Transferencia Entrada Maquinaria, Devolución, Entrada Reposición Maquinaria
  - **Salidas** (restan stock): Asignación Maquinaria, Asignación Maquinaria con Reposición, Asignación Maquinaria sin Reposición, Mantenimiento Maquinaria, Transferencia Salida Maquinaria
  - **Neutral** (no afecta stock): Baja Reposición Maquinaria
- Transferencias: para el deposito origen es Salida, para el destino es Entrada (se determina por `id_deposito_entrada`)
- `calcularStockActual()` recalcula desde el historial de movimientos (usa `esEntradaPorNombre()` / `esSalidaPorNombre()`)
- `stockBajoMinimo()` — bool, compara `stock_actual` con `stock_minimo`
- Alertas de stock bajo mínimo en el dashboard
- Campo `unidad` en `insumos` usa valores fijos: `UNIDAD`, `LITROS`, `TAMBOR`, `METRO`, `ROLLO X 100 MT`, `PAQUETE`, `BOLSA`, `BALDE` (validación `in:` en backend y select en UI)

### Asignaciones de insumos

Los movimientos de asignación permiten asignar insumos a **vehículos**, **eventos** o **empleados**:

- **Asignación con Reposición** — salida temporal (ej: insumo prestado a un evento, se devuelve después). Destinos: vehículo, evento o empleado.
- **Asignación sin Reposición** — salida definitiva (ej: repuesto instalado en un vehículo). **Solo vehículos y eventos** (no empleados).
- **Entrada Reposición** — devolver insumos previamente asignados (suma stock)
- **Baja Reposición** — cancelar pendencia sin devolver stock (ej: se usaron como repuesto definitivo). No está en NOMBRES_ENTRADA ni NOMBRES_SALIDA, no afecta stock.

Flujo en TransferenciasInsumos (modal "Nuevo Movimiento"):
1. Seleccionar insumo (paso 1)
2. Elegir tipo de movimiento (paso 2) — las asignaciones solo aparecen si hay stock > 0
3. Seleccionar tipo de destino (Vehículo, Evento o Empleado) + buscar/seleccionar el registro + cantidad (paso 3)

Los movimientos se guardan con `tipo_referencia = 'vehiculo'`, `'evento'` o `'empleado'` e `id_referencia` apuntando al registro seleccionado. Para empleados, `id_referencia` es el `LEGAJO` de la tabla `in_maestro` en la BD `munimer_inasi`.

#### Panel "Asignaciones Pendientes de Reposición"

Panel colapsable en TransferenciasInsumos (arriba de la lista de movimientos) que muestra asignaciones con reposición que aún no fueron devueltas ni dadas de baja.

- **Cálculo pendiente**: `SUM(Asignación con Reposición) - SUM(Entrada Reposición) - SUM(Baja Reposición)` por cada combinación insumo + tipo_referencia + id_referencia
- **Acciones por fila**: campo de cantidad + botón "Devolver" (crea Entrada Reposición, suma stock) y botón "Dar de baja" (crea Baja Reposición con confirm(), no afecta stock)
- Métodos: `devolverAsignacion()`, `darDeBajaAsignacion()`, `calcularPendiente()`

### Comprobantes adjuntos en movimientos

Los movimientos de tipo **Carga de Stock** y **Ajuste Positivo** permiten adjuntar comprobantes (órdenes de compra, recibos, etc.):

- **Modelo:** `ComprobanteMovimiento` — tabla `comprobantes_movimiento` con FK a `movimiento_insumos`
- **Almacenamiento:** disco `local` (privado, `storage/app/private/comprobantes/`), no accesible por URL directa
- **Archivos permitidos:** PDF, JPG, PNG — máximo 5 archivos por movimiento, 5 MB cada uno
- **Campo opcional** en paso 3 del modal de nuevo movimiento
- **`nro_orden_compra`:** campo opcional en `movimiento_insumos` para registrar el número de orden de compra o suministro (solo Carga de Stock y Ajuste Positivo). Se muestra en la lista como "OC: ..." debajo del tipo de movimiento.
- **Visualización:** ícono de clip en la lista de movimientos, con dropdown que muestra los archivos adjuntos
- **Acciones:** ver (abre inline en el navegador) y descargar (fuerza descarga) — ambas rutas protegidas por autenticación
- El componente usa `WithFileUploads` de Livewire para la subida de archivos

### Columna `tipo` en `tipo_movimientos`

La columna `tipo` indica a qué **módulo** aplica el tipo de movimiento:
- `I` = solo Insumos
- `M` = solo Maquinarias
- `IM` = ambos módulos

**No** indica si es ingreso o egreso. La clasificación entrada/salida se determina por el nombre del tipo usando las constantes `NOMBRES_ENTRADA` y `NOMBRES_SALIDA` en `TipoMovimiento`.

Al buscar un tipo de movimiento en el código, usar `TipoMovimiento::where('tipo_movimiento', $nombre)->first()` — **nunca** `firstOrCreate`.

## Lógica de cantidad (Maquinarias)

- `cantidad` en `maquinarias` es la cantidad total registrada en el sistema
- `getCantidadDisponibleAttribute()` — descuenta las unidades actualmente en uso (salidas sin devolución)
- `getCantidadEnDeposito($depositoId)` — cantidad disponible en un deposito específico
- `getCantidadTotalDisponible()` — total disponible en todos los depositos

### Asignaciones de maquinarias

Los movimientos de asignación permiten asignar maquinarias a **vehículos**, **eventos** o **empleados**, con la misma lógica que insumos:

- **Asignación Maquinaria con Reposición** — salida temporal (con devolución posterior). Destinos: vehículo, evento o empleado.
- **Asignación Maquinaria sin Reposición** — salida definitiva. **Solo vehículos y eventos** (no empleados).
- **Entrada Reposición Maquinaria** — devolver maquinaria previamente asignada (suma stock)
- **Baja Reposición Maquinaria** — cancelar pendencia sin devolver stock. No afecta stock.

Los movimientos se guardan con `tipo_referencia = 'vehiculo'`, `'evento'` o `'empleado'` e `id_referencia` apuntando al registro seleccionado.

#### Panel "Asignaciones Pendientes de Reposición" (Maquinarias)

Panel colapsable en TransferenciasMaquinarias (arriba de la lista de movimientos) que muestra asignaciones con reposición pendientes.

- **Cálculo pendiente**: `SUM(Asignación Maquinaria con Reposición) - SUM(Entrada Reposición Maquinaria) - SUM(Baja Reposición Maquinaria)`
- **Acciones por fila**: campo de cantidad + botón "Devolver" y botón "Baja"
- Métodos: `devolverAsignacion()`, `darDeBajaAsignacion()`, `calcularPendienteMaquinaria()`

## Referencias polimórficas en movimientos

Los movimientos (`movimiento_insumos`, `movimiento_maquinarias`) tienen:
- `id_referencia` — ID de la entidad asociada
- `tipo_referencia` — enum: `empleado`, `maquina`, `evento`, `secretaria`, `inventario`, `deposito`, `mantenimiento`, `transferencia`, `vehiculo`

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
| `MovimientosInventarioInicialSeeder` | Crea un `MovimientoEncabezado` + `MovimientoInsumo` por cada insumo con stock > 0, usando tipo_movimiento id=7 (Inventario Inicial). Idempotente: no duplica si ya existe. Fecha de relevamiento: 2026-02-05 |

Orden de ejecución: Corralones → Depositos → Categorías → Insumos → Maquinarias → Vehículos → Choferes → MovimientosInventarioInicial

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
- **Métodos de permiso:** `puedeCrear*()`, `puedeEditar*()`, `puedeEliminar*()`
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
- **DB secundaria:** `munimer_inasi` (empleados municipales) — conexión configurada en `config/database.php` como `munimer_inasi`, credenciales en `.env` (`DB_INASI_*`). En desarrollo local puede apuntar al servidor de desarrollo (10.0.0.16)
- **Sesiones:** almacenadas en base de datos (`SESSION_DRIVER=database`)
- **Mail:** modo `log` en desarrollo (no envía correos reales)
- **Verificación de email:** habilitada en producción
- **Caché:** driver base de datos en desarrollo
- **Cola (queue):** driver base de datos
- **Bcrypt:** `BCRYPT_ROUNDS=12`
- El proyecto está en **desarrollo activo**
