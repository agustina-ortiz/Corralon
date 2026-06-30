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
- **Exportación:** `maatwebsite/excel` (Excel/CSV) + `barryvdh/laravel-dompdf` (PDF) — requieren `composer install` en cada servidor

---

## Estructura del proyecto

```
app/
  Console/Commands/       # RecalcularStocksInsumos, RehashPasswords
  Http/Controllers/       # DashboardController, Auth/VerifyEmailController
  Livewire/               # Componentes reactivos (ABM*, Transferencias*, Dashboard)
    Actions/              # Logout
    Forms/                # LoginForm
  Exports/                # Clases de exportación a Excel (InsumosExport, MaquinariasExport, MovimientosInsumosExport, MovimientosMaquinariasExport)
  Models/                 # Modelos Eloquent (23 modelos, incluye EmpleadoMunicipal con conexión externa)
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
  web.php                 # Rutas principales (16 rutas)
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
| `Vehiculo` | `vehiculos` | Flota vehicular (nro_patrimonio, marca_modelo, anio, patente, combustible, VTV, póliza, origen, jurisdiccion_procedencia, nro_telepase, estado, id_secretaria). Estados: `EN USO`, `BAJA`, `MANTENIMIENTO` |
| `Chofer` | `choferes` | Conductores (licencia, vencimientos, vehículos asignados) |
| `Empleado` | `empleados` | Personal (tabla local, legada — el tab `/empleados` ya no la usa) |
| `Evento` | `eventos` | Eventos programados |
| `Secretaria` | `secretarias` | Dependencias/secretarías municipales. Relación `areas()` |
| `Area` | `areas` | Áreas dentro de una secretaría (`id_secretaria`, `area`) |
| `MovimientoInsumo` | `movimiento_insumos` | Movimientos de stock. Campos opcionales `id_secretaria` y `area` (Ajuste Negativo), `nro_orden_compra` y `observaciones` |
| `MovimientoMaquinaria` | `movimiento_maquinarias` | Movimientos de equipos. Campos opcionales `id_secretaria` y `area` (Ajuste Negativo), `nro_orden_compra` y `observaciones` |
| `TipoMovimiento` | `tipo_movimientos` | Tipos de movimiento (columna `tipo`: I=Insumos, M=Maquinaria, IM=ambos) |
| `CategoriaInsumo` | `categorias_insumos` | Categorías de insumos |
| `CategoriaMaquinaria` | `categoria_maquinarias` | Categorías de maquinaria |
| `DocumentoVehiculo` | `documentos_vehiculos` | Documentos de vehículos |
| `MovimientoEncabezado` | `movimiento_encabezados` | Cabecera para agrupar movimientos |
| `TipoVehiculo` | `tipos_vehiculos` | Tipos de vehículo |
| `Cuadrilla` | `cuadrillas` | Cuadrillas de trabajo (ligadas a corralon y deposito) |
| `UsuarioPermiso` | `usuario_permisos` | Permisos granulares: usuario + corralón + depósito + módulo + nivel |
| `ComprobanteMovimiento` | `comprobantes_movimiento` | Archivos adjuntos a movimientos de insumos (órdenes de compra, recibos) |
| `ComprobanteMovimientoMaquinaria` | `comprobantes_movimiento_maquinaria` | Archivos adjuntos a movimientos de maquinarias (FK a `movimiento_maquinarias`) |
| `EmpleadoMunicipal` | `in_maestro` (BD: `munimer_inasi`) | Empleados municipales del sistema INASI (conexión secundaria, solo lectura). PK: `LEGAJO`. Const `DEPTO_CORRALON = 36`. Scopes `activos()` y `porDepto($depto)`, accessor `nombre_formateado`. Es la fuente del tab `/empleados` (que ahora lista **todos** los activos, sin filtrar por DEPTO) |

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
| `/empleados` | AbmEmpleados (listado solo lectura desde `in_maestro`, todos los activos) | `empleados` |
| `/usuarios` | AbmUsuarios | `usuarios` |
| `/secretarias` | AbmSecretarias | `secretarias` |
| `/transferencias-insumos` | TransferenciasInsumos | `movimientos_insumos` |
| `/transferencias-maquinarias` | TransferenciasMaquinarias | `movimientos_maquinarias` |
| `/comprobantes/{id}/ver` | Closure (ruta) | autenticado |
| `/comprobantes/{id}/descargar` | Closure (ruta) | autenticado |
| `/comprobantes-maquinaria/{id}/ver` | Closure (ruta) | autenticado |
| `/comprobantes-maquinaria/{id}/descargar` | Closure (ruta) | autenticado |

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
`empleados`, `choferes`, `eventos`, `categorias_insumos`, `categorias_maquinarias`, `usuarios`, `secretarias`

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
- **Creación de insumo (`AbmInsumos`):** al crear un insumo con `stock_inicial > 0` se genera automáticamente un `MovimientoInsumo` de tipo **Inventario Inicial**. El modal de creación incluye un campo opcional **N° Orden de Compra / Suministro** (`nro_orden_compra`, solo en modo crear) que se guarda en ese movimiento. Ese movimiento queda editable luego en `/transferencias-insumos` (ver **Edición de movimientos**)

### Asignaciones de insumos

Los movimientos de asignación permiten asignar insumos a **vehículos**, **eventos**, **empleados** o **secretarías**:

- **Asignación con Reposición** — salida temporal (ej: insumo prestado a un evento, se devuelve después). Destinos: vehículo, evento, empleado o secretaría.
- **Asignación sin Reposición** — salida definitiva (ej: repuesto instalado en un vehículo). Destinos: vehículo, evento, empleado o secretaría.
- **Entrada Reposición** — devolver insumos previamente asignados (suma stock)
- **Baja Reposición** — cancelar pendencia sin devolver stock (ej: se usaron como repuesto definitivo). No está en NOMBRES_ENTRADA ni NOMBRES_SALIDA, no afecta stock.

Flujo en TransferenciasInsumos (modal "Nuevo Movimiento"):
1. Seleccionar insumo (paso 1)
2. Elegir tipo de movimiento (paso 2) — las asignaciones solo aparecen si hay stock > 0
3. Seleccionar tipo de destino (Vehículo, Evento o Empleado) + buscar/seleccionar el registro + cantidad (paso 3)

Los movimientos se guardan con `tipo_referencia = 'vehiculo'`, `'evento'` o `'empleado'` e `id_referencia` apuntando al registro seleccionado. Para empleados, `id_referencia` es el `LEGAJO` de la tabla `in_maestro` en la BD `munimer_inasi`.

#### Panel "Asignaciones Pendientes de Reposición"

Panel colapsable en TransferenciasInsumos (arriba de la lista de movimientos) que muestra asignaciones con reposición que aún no fueron devueltas ni dadas de baja.

- **Visibilidad**: el panel **solo se muestra si hay al menos una asignación pendiente**; si no hay pendientes, el colapsable no aparece. `$asignacionesPendientes` se calcula siempre en `render()` (ya no depende de `showAsignacionesPendientes`) y la vista lo envuelve en `@if($asignacionesPendientes->count() > 0)`.
- **Cálculo pendiente**: `SUM(Asignación con Reposición) - SUM(Entrada Reposición) - SUM(Baja Reposición)` por cada combinación insumo + tipo_referencia + id_referencia
- **Acciones por fila**: campo de cantidad + botón "Devolver" (crea Entrada Reposición, suma stock) y botón "Dar de baja" (crea Baja Reposición con confirm(), no afecta stock)
- Métodos: `devolverAsignacion()`, `darDeBajaAsignacion()`, `calcularPendiente()`

### Comprobantes adjuntos en movimientos

Los movimientos de tipo **Carga de Stock** y **Ajuste Positivo** (insumos **y** maquinarias) permiten adjuntar comprobantes (órdenes de compra, recibos, etc.):

- **Modelos:** `ComprobanteMovimiento` (tabla `comprobantes_movimiento`, FK a `movimiento_insumos`) y `ComprobanteMovimientoMaquinaria` (tabla `comprobantes_movimiento_maquinaria`, FK a `movimiento_maquinarias`)
- **Almacenamiento:** disco `local` (privado), insumos en `storage/app/private/comprobantes/` y maquinarias en `storage/app/private/comprobantes-maquinaria/`, no accesibles por URL directa
- **Archivos permitidos:** PDF, JPG, PNG — máximo 5 archivos por movimiento, 5 MB cada uno
- **Seguridad y preview:** ver sección **Seguridad de uploads** más abajo (regla `ArchivoSeguro`, preview con miniatura y botón de eliminar).
- **Campo opcional** en paso 3 del modal de nuevo movimiento
- **`nro_orden_compra`:** campo opcional en `movimiento_insumos` y `movimiento_maquinarias` para registrar el número de orden de compra o suministro (solo Carga de Stock y Ajuste Positivo). Se muestra en la lista como "OC: ..." debajo del tipo de movimiento.
- **`observaciones`:** campo opcional (text nullable) en ambas tablas de movimientos; se guarda al crear Carga de Stock / Ajuste Positivo.
- **Visualización:** ícono de clip en la lista de movimientos, con dropdown que muestra los archivos adjuntos
- **Acciones:** ver (abre inline en el navegador) y descargar (fuerza descarga) — rutas protegidas por autenticación (`comprobantes.*` y `comprobantes-maquinaria.*`)
- Ambos componentes (`TransferenciasInsumos`, `TransferenciasMaquinarias`) usan `WithFileUploads` de Livewire para la subida de archivos

### Edición de movimientos (Carga de Stock / Ajuste Positivo)

Cada movimiento de tipo **Carga de Stock** o **Ajuste Positivo** en la lista muestra un **botón de edición (lápiz ámbar)** en la columna Usuario (solo visible con permiso de crear movimientos). Abre un modal que permite editar **únicamente**:
- `nro_orden_compra`
- `observaciones`
- Comprobantes adjuntos: agregar nuevos, ver y eliminar existentes (tope total de 5 archivos)

Métodos en ambos componentes: `abrirEdicion($id)`, `guardarEdicion()`, `eliminarComprobanteExistente($id)`, `removeEditComprobante($index)`, `cerrarEdicion()`. Propiedades con prefijo `edit_*`. La edición valida que el tipo de movimiento esté en `TIPOS_EDITABLES`:
- **Insumos** (`TransferenciasInsumos`): `['Carga de Stock', 'Ajuste Positivo', 'Inventario Inicial']` — Inventario Inicial **sí** es editable.
- **Maquinarias** (`TransferenciasMaquinarias`): `['Carga de Stock', 'Ajuste Positivo']`.

El resto de los tipos (asignaciones, ajuste negativo, transferencias) **no** muestran el botón. El `in_array(...)` del blade debe coincidir con la constante de cada componente.

### Seguridad de uploads y preview

Todos los uploads del sistema (comprobantes de insumos/maquinarias y documentos de vehículos) comparten un esquema de seguridad y UX común.

**Seguridad (defensa anti web-shell):**
- **Regla `app/Rules/ArchivoSeguro.php`** (`ValidationRule` reutilizable): (1) lista negra de extensiones ejecutables/peligrosas (`php`, `phtml`, `phar`, `exe`, `sh`, `js`, `svg`, `html`, etc.), (2) lista blanca de extensiones (`pdf`, `jpg`, `jpeg`, `png` por defecto), (3) verifica el **MIME real por contenido** (`getMimeType()`/finfo) contra `application/pdf`, `image/jpeg`, `image/png`. El constructor admite listas custom.
- Se aplica como array de reglas: `['file', 'max:5120', new ArchivoSeguro()]` en `comprobantes.*` / `edit_comprobantes.*` (ambos componentes de Transferencias) y en `nuevo_documento` (AbmVehiculos). **Reemplazó** al viejo `mimes:pdf,jpg,jpeg,png`.
- **`config/livewire.php`** (publicado): `temporary_file_upload.rules` valida ya en la subida temporal: `['required','file','mimetypes:application/pdf,image/jpeg,image/png','max:10240']`. Se removió `svg` de `preview_mimes` (riesgo XSS). Es global, pero solo esos 3 componentes suben archivos.
- **`storage/app/public/.htaccess`**: impide ejecución/acceso a scripts en la carpeta pública (documentos de vehículos van al disco `public`). Los comprobantes ya están en disco privado `local`.

**Preview con miniatura + eliminar:**
- **Partial `resources/views/livewire/partials/preview-archivos.blade.php`** — recibe `$files` (array de `TemporaryUploadedFile`) y `$removeMethod` (nombre del método Livewire). Renderiza miniatura (`temporaryUrl()`) para jpg/jpeg/png e ícono PDF para el resto, con botón rojo de eliminar (X) por archivo. Se incluye vía `@include` en los 4 bloques de comprobantes (insumos/maquinarias × crear/editar).
- AbmVehiculos usa archivo único (`nuevo_documento`, no array): tiene su propio preview inline en el blade + método `quitarNuevoDocumento()`.

### Destino de Ajuste Negativo y Asignaciones (insumos y maquinarias)

Los movimientos **Ajuste Negativo**, **Asignación con Reposición** y **Asignación sin Reposición** (insumos y maquinarias) comparten un **selector de destino** con 4 opciones: **Vehículo, Evento, Empleado y Secretaría**.

- En **Ajuste Negativo** el destino es **opcional** (puede registrarse sin destino); en las asignaciones es **obligatorio**.
- El botón `seleccionarTipoDestino()` togglea (volver a tocarlo deselecciona — permite dejar Ajuste Negativo sin destino).
- Helper `destinoFields()` (en ambos componentes) resuelve `[tipo_referencia, id_referencia, id_secretaria, area]`:
  - **Secretaría** → `tipo_referencia='secretaria'`, `id_referencia=<id secretaría>`, `id_secretaria=<id>`, `area=<opcional>`
  - **Vehículo/Evento/Empleado** → `tipo_referencia=<tipo>`, `id_referencia=<id>`, `id_secretaria=null`, `area=null`
  - **Sin destino** (solo Ajuste Negativo) → `id_referencia=0`, `tipo_referencia='inventario'` (insumos) / `'deposito'` (maquinarias)
- **Secretaría**: select de secretaría (`id_secretaria_ajuste`) + campo combo **Área** (`area_ajuste`: select de áreas + texto libre con Alpine.js). El campo Área aparece **solo** cuando el destino es Secretaría.
- En la columna **Destino** del listado: badge violeta para secretaría (+ área), y el nombre del vehículo/evento/empleado para esos destinos.
- Las asignaciones a secretaría participan del panel **Asignaciones Pendientes de Reposición** y de devolver/dar de baja como cualquier otro destino.

### ABM Secretarías y Áreas

Ruta `/secretarias` — componente `AbmSecretarias`:
- CRUD de secretarías y áreas (áreas pertenecen a una secretaría)
- Modal "Ver Áreas": scrolleable (`max-h-96 overflow-y-auto`) cuando hay más de 8 áreas
- Modal "Nueva/Editar Área": select de secretaría + nombre de área

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

Los movimientos de asignación permiten asignar maquinarias a **vehículos**, **eventos**, **empleados** o **secretarías**, con la misma lógica que insumos:

- **Asignación Maquinaria con Reposición** — salida temporal (con devolución posterior). Destinos: vehículo, evento, empleado o secretaría.
- **Asignación Maquinaria sin Reposición** — salida definitiva. Destinos: vehículo, evento, empleado o secretaría.
- **Entrada Reposición Maquinaria** — devolver maquinaria previamente asignada (suma stock)
- **Baja Reposición Maquinaria** — cancelar pendencia sin devolver stock. No afecta stock.

Los movimientos se guardan con `tipo_referencia = 'vehiculo'`, `'evento'`, `'empleado'` o `'secretaria'` e `id_referencia` apuntando al registro seleccionado (para secretaría, `id_referencia` es el id de la secretaría y además se guarda `id_secretaria` + `area`). Ver **Destino de Ajuste Negativo y Asignaciones**.

#### Panel "Asignaciones Pendientes de Reposición" (Maquinarias)

Panel colapsable en TransferenciasMaquinarias (arriba de la lista de movimientos) que muestra asignaciones con reposición pendientes.

- **Visibilidad**: el panel **solo se muestra si hay al menos una asignación pendiente** (`@if($puedeCrear && $asignacionesPendientes->count() > 0)`); si no hay pendientes, el colapsable no aparece. `$asignacionesPendientes` se calcula siempre en `render()`.
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
| `InsumosSeeder` | Insumos con movimientos de Inventario Inicial (crea `MovimientoEncabezado` + `MovimientoInsumo` por cada insumo con stock > 0) |
| `InsumosEconomiaSeeder` | Insumos de la secretaría de economía con movimientos de Inventario Inicial |
| `MaquinariasSeeder` | Maquinarias con movimientos de Inventario Inicial Maquinaria (crea `MovimientoMaquinaria` por cada maquinaria con cantidad > 0) |
| `VehiculosSeeder` | Vehículos con fechas de VTV y póliza |
| `ChoferesSeeder` | Choferes con licencias y asignaciones de vehículos |

Orden de ejecución: Corralones → Depositos → Categorías → Insumos → InsumosEconomia → Maquinarias → Vehículos → Choferes

---

## Dashboard — Alertas

El dashboard muestra:
- **Stock bajo mínimo** — insumos donde `stock_actual < stock_minimo`
- **VTV próximas a vencer** — vehículos con `vencimiento_vtv` en los próximos 30 días
- **Licencias de choferes próximas a vencer** — via `licenciaProximaAVencer()`
- **Eventos próximos** — eventos con fecha cercana
- **Vehículos en uso** — vehículos con estado activo/en circulación

---

## Estadísticas y Exportación (Excel / PDF)

Los 4 listados principales tienen botones **📊 Estadísticas** (modal) y **⬇️ Exportar** (dropdown Alpine con Excel/PDF), ubicados en el header antes del botón Nuevo/Crear:

| Pantalla | Componente | Clase Export | Vista PDF |
|----------|-----------|--------------|-----------|
| `/insumos` | AbmInsumos | `InsumosExport` | `exports.insumos-pdf` |
| `/maquinarias` | AbmMaquinarias | `MaquinariasExport` | `exports.maquinarias-pdf` |
| `/transferencias-insumos` | TransferenciasInsumos | `MovimientosInsumosExport` | `exports.movimientos-insumos-pdf` |
| `/transferencias-maquinarias` | TransferenciasMaquinarias | `MovimientosMaquinariasExport` | `exports.movimientos-maquinarias-pdf` |

**Patrón en cada componente:**
- Propiedad `public $showEstadisticas` + método `toggleEstadisticas()`. `$estadisticas` se calcula en `render()` solo si `showEstadisticas` y se pasa a la vista.
- Se extrae el query filtrado a `baseQuery()` (ABMs) o `baseMovimientosQuery($depositosAccesibles)` (transferencias), **reutilizado por el listado, las estadísticas y la exportación** — los tres reflejan exactamente los mismos filtros + permisos.
- `calcularEstadisticas()` arma KPIs + distribuciones (barras CSS en el modal). `descripcionFiltros()` arma el subtítulo de filtros activos del PDF.
- `exportarExcel()` → `Excel::download(new XxxExport($query), $nombre)`.
- `exportarPdf()` → genera a archivo temporal con `$pdf->save()` y devuelve `response()->download($path, ...)->deleteFileAfterSend(true)`.

**Reglas críticas (aprendidas en producción):**
- **NO usar `response()->streamDownload()` + `print($pdf->output())`**: Livewire no captura bien el binario y el PDF llega vacío. Usar siempre archivo temporal + `response()->download()` (mismo tipo de respuesta `BinaryFileResponse` que `Excel::download`).
- **Memoria dompdf:** dompdf arma toda la tabla en un único "Cellmap" en memoria → una tabla gigante revienta (2000 filas ≈ 1 GB). Las vistas PDF parten el listado en **muchas tablas chicas** con `@forelse($x->chunk(40/45))` + `table-layout: fixed` y anchos de columna explícitos (2000 filas ≈ 190 MB). Cada `exportarPdf()` hace `@ini_set('memory_limit','1024M')` + `@set_time_limit(300)` y **aborta con `session()->flash('error', ...)` si `count() > 2000`** (deriva a Excel, que no tiene este límite).
- Excel: clases en `app/Exports/` implementan `FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle`.
- **Despliegue:** tras `git pull` correr `composer install` (instala los 2 paquetes), `npm run build` y limpiar caches. No hay migraciones. Recomendado `memory_limit ≥ 512M` en el `php.ini` del servidor por si `ini_set` está bloqueado.

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

### Mensajes de feedback (éxito y error)

Toda operación que pueda fallar (eliminar con FK, crear movimiento con stock insuficiente, etc.) debe dejar **siempre** un mensaje informativo de la razón:

- **Componente:** envolver la operación en `try/catch` y hacer `session()->flash('error', '<razón>')`. Capturar primero `\Illuminate\Database\QueryException` con un mensaje específico (ej: "No se puede eliminar la categoría porque tiene insumos o movimientos asociados.") y luego `\Exception` con un mensaje genérico de respaldo. El éxito usa `session()->flash('message', ...)` (en `AbmUsuarios` la clave de éxito es `'mensaje'`).
- **Vista (blade):** además del bloque de éxito (`session()->has('message')`, verde), todo blade ABM debe incluir un bloque rojo `@if (session()->has('error'))` que renderice `session('error')`. Sin este bloque la operación falla en silencio.
- Todos los ABM y ambas Transferencias ya cumplen este patrón. `abm-empleados` es solo lectura (no aplica).

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
- **DB secundaria:** `munimer_inasi` (empleados municipales) — conexión configurada en `config/database.php` como `munimer_inasi`, credenciales en `.env` (`DB_INASI_*`). Servidor real de RRHH: `10.0.0.19` (usuario `aplicrrhh`). Los fallbacks de la conexión están **aislados** (no usan `DB_HOST`/`DB_USERNAME` de la base local) para evitar que, si falta una `DB_INASI_*`, consulte por error la `in_maestro` de la base local con datos viejos. Tras cambiar el `.env` siempre correr `php artisan config:clear`
- **Sesiones:** almacenadas en base de datos (`SESSION_DRIVER=database`)
- **Mail:** modo `log` en desarrollo (no envía correos reales)
- **Verificación de email:** habilitada en producción
- **Caché:** driver base de datos en desarrollo
- **Cola (queue):** driver base de datos
- **Bcrypt:** `BCRYPT_ROUNDS=12`
- El proyecto está en **desarrollo activo**
