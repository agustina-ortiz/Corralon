# Manual de Usuario — Sistema de Gestión de Stock

**Municipalidad de Mercedes**
**Versión:** 1.0 — Mayo 2026

---

## Tabla de Contenidos

1. [Introducción](#1-introducción)
2. [Ingreso al Sistema](#2-ingreso-al-sistema)
3. [Navegación General](#3-navegación-general)
4. [Dashboard](#4-dashboard)
5. [Insumos](#5-insumos)
6. [Maquinarias](#6-maquinarias)
7. [Vehículos](#7-vehículos)
8. [Choferes](#8-choferes)
9. [Empleados](#9-empleados)
10. [Eventos](#10-eventos)
11. [Depósitos](#11-depósitos)
12. [Categorías de Insumos](#12-categorías-de-insumos)
13. [Categorías de Maquinarias](#13-categorías-de-maquinarias)
14. [Movimientos de Insumos](#14-movimientos-de-insumos)
15. [Movimientos de Maquinarias](#15-movimientos-de-maquinarias)
16. [Administración de Usuarios](#16-administración-de-usuarios)
17. [Preguntas Frecuentes](#17-preguntas-frecuentes)

---

## 1. Introducción

El **Sistema de Gestión de Stock** es una aplicación web diseñada para administrar los recursos de los corralones de la Municipalidad de Mercedes. Permite gestionar:

- **Insumos** (materiales, repuestos, consumibles) con control de stock
- **Maquinarias** (equipos y herramientas)
- **Vehículos** (flota vehicular con documentación y vencimientos)
- **Choferes** (conductores con licencias y asignaciones)
- **Empleados** y **Eventos**
- **Movimientos** de entrada, salida, transferencia y asignación de insumos y maquinarias

El sistema soporta múltiples corralones y depósitos, con un esquema de permisos granular que controla qué puede ver y hacer cada usuario.

---

## 2. Ingreso al Sistema

### Pantalla de Login

Al acceder al sistema se presenta la pantalla de inicio de sesión.

**Pasos para ingresar:**

1. Ingrese su **correo electrónico** en el campo correspondiente.
2. Ingrese su **contraseña**.
3. (Opcional) Marque la casilla **"Recordarme"** si desea que el sistema mantenga su sesión activa.
4. Haga clic en el botón **"Ingresar al sistema"**.

Si las credenciales son correctas, será redirigido al Dashboard. Si son incorrectas, se mostrará un mensaje de error.

> **Nota:** Si olvidó su contraseña, contacte al administrador del sistema para que la restablezca.

### Cerrar Sesión

Para cerrar sesión, haga clic en el **botón rojo de logout** ubicado en la parte inferior del menú lateral (sidebar), junto a su nombre y correo electrónico.

---

## 3. Navegación General

### Menú Lateral (Sidebar)

El menú lateral izquierdo es la forma principal de navegar entre las secciones del sistema. Está organizado en:

- **Dashboard** — Pantalla principal con resúmenes y alertas.
- **INVENTARIO** — Sección que agrupa:
  - Insumos
  - Maquinaria
  - Vehículos
  - Categorías Insumos
  - Categorías Maquinarias
  - Depósitos
  - Eventos
  - Movimientos Insumos
  - Movimientos Maquinarias
- **RECURSOS** — Sección que agrupa:
  - Empleados
  - Choferes
  - Usuarios

El enlace activo se resalta en **verde**. Solo se muestran las secciones a las que usted tiene acceso según sus permisos.

### Navegación en Dispositivos Móviles

En pantallas pequeñas (celulares, tablets), el menú lateral se oculta automáticamente. Use el botón de **menú hamburguesa** (tres líneas horizontales) en la esquina superior izquierda para abrirlo.

### Elementos Comunes en Todas las Pantallas

La mayoría de las pantallas del sistema comparten estos elementos:

| Elemento | Descripción |
|----------|-------------|
| **Barra de búsqueda** | Campo de texto para filtrar registros por nombre u otros campos |
| **Botón "Filtros"** | Abre un panel desplegable con filtros avanzados. Muestra un número indicando cuántos filtros están activos |
| **Botón "Nuevo..."** | Abre el formulario para crear un nuevo registro. Solo visible si tiene permisos de edición |
| **Tabla de datos** | Lista paginada de registros (10 por página). Navegue entre páginas con los botones al pie de la tabla |
| **Acciones por fila** | Botones de editar (azul) y eliminar (rojo) en cada registro |

---

## 4. Dashboard

El Dashboard es la pantalla principal del sistema. Muestra un resumen general del estado de los recursos y alertas importantes.

### Tarjetas de Resumen

En la parte superior se muestran hasta 4 tarjetas con contadores:

| Tarjeta | Descripción |
|---------|-------------|
| **Total Insumos** | Cantidad total de insumos registrados |
| **Maquinaria** | Cantidad total de maquinarias |
| **Vehículos** | Cantidad total de vehículos |
| **Próximos Eventos** | Cantidad de eventos próximos |

### Widgets de Alertas

Debajo de las tarjetas se muestran paneles de alerta:

#### Insumos con Stock Bajo
Muestra los insumos cuyo stock actual está por debajo del stock mínimo configurado. Cada fila indica:
- Nombre del insumo y categoría
- Depósito y corralón donde se encuentra
- Stock actual (en naranja) vs. stock mínimo
- Cuántas unidades faltan para alcanzar el mínimo

#### VTVs Próximas a Vencer
Lista los vehículos cuya Verificación Técnica Vehicular está por vencer o ya venció:
- **Rojo**: VTV ya vencida
- **Naranja**: vence en los próximos 7 días
- **Amarillo**: vence en los próximos 30 días

#### Vehículos en Uso
Muestra los vehículos que actualmente están en estado "En Uso".

#### Próximos Eventos
Lista los eventos programados más cercanos con su fecha, ubicación y secretaría.

### Personalizar el Dashboard

Puede elegir qué tarjetas y widgets se muestran:

1. Haga clic en **"Personalizar panel"** (esquina superior derecha del Dashboard).
2. Se abrirá una ventana con casillas de verificación para cada tarjeta y widget.
3. Active o desactive los que desee ver.
4. Haga clic en **"Guardar"**.

Sus preferencias se guardan y se mantendrán en futuras sesiones.

---

## 5. Insumos

La sección de Insumos permite gestionar todos los materiales, repuestos y consumibles del inventario.

### Ver Insumos

Al ingresar a **Insumos** se muestra una tabla con todos los insumos a los que tiene acceso, con las siguientes columnas:

| Columna | Descripción |
|---------|-------------|
| **Insumo** | Nombre del insumo |
| **Categoría** | Categoría a la que pertenece (etiqueta azul) |
| **Stock Actual** | Cantidad en stock. Se muestra en rojo si está por debajo del mínimo |
| **Unidad** | Unidad de medida (Unidad, Litros, Metro, etc.) |
| **Stock Mínimo** | Cantidad mínima deseada en stock |
| **Depósito** | Depósito donde se almacena |
| **Corralón** | Corralón al que pertenece (etiqueta azul) |

Los insumos con stock bajo el mínimo se resaltan con fondo rojo claro.

### Filtrar Insumos

Puede buscar y filtrar insumos de varias formas:

- **Búsqueda rápida:** Escriba en el campo "Buscar insumos..." para filtrar por nombre.
- **Filtros avanzados:** Haga clic en el botón **"Filtros"** para desplegar:
  - **Categoría:** Filtra por categoría de insumo.
  - **Unidad de Medida:** Filtra por tipo de unidad.
  - **Depósito:** Filtra por depósito.
  - **Solo stock bajo mínimo:** Muestra únicamente los insumos con stock bajo.
- Para quitar todos los filtros, haga clic en **"Limpiar filtros"**.

### Crear un Insumo

1. Haga clic en **"Nuevo Insumo"** (botón azul, esquina superior derecha).
2. Complete el formulario:
   - **Insumo** (obligatorio): Nombre descriptivo.
   - **Categoría** (obligatorio): Seleccione de la lista.
   - **Unidad** (obligatorio): Seleccione la unidad de medida (Unidad, Litros, Tambor, Metro, Rollo x 100 mt, Paquete, Bolsa, Balde).
   - **Stock Inicial** (opcional): Cantidad inicial. Si se ingresa, se generará automáticamente un movimiento de inventario inicial.
   - **Stock Mínimo** (obligatorio): Cantidad mínima para alertas.
   - **Depósito** (obligatorio): Seleccione el depósito.
3. Haga clic en **"Crear"**.

### Editar un Insumo

1. Haga clic en el botón **editar** (ícono de lápiz azul) en la fila del insumo.
2. Modifique los campos necesarios. El stock actual se muestra como solo lectura (se modifica a través de movimientos).
3. Haga clic en **"Actualizar"**.

### Eliminar un Insumo

1. Haga clic en el botón **eliminar** (ícono de papelera rojo) en la fila del insumo.
2. Confirme la eliminación en el diálogo que aparece.

> **Importante:** El stock de los insumos no se modifica directamente. Para agregar o quitar stock, utilice la sección [Movimientos de Insumos](#14-movimientos-de-insumos).

---

## 6. Maquinarias

Permite gestionar los equipos y maquinarias de los corralones.

### Ver Maquinarias

La tabla muestra:

| Columna | Descripción |
|---------|-------------|
| **Maquinaria** | Nombre del equipo |
| **Categoría** | Categoría (etiqueta azul) |
| **Cantidad** | Cantidad total. Verde si > 0, rojo si = 0 |
| **Estado** | "Disponible" (verde) o "No Disponible" (rojo) |
| **Depósito** | Depósito asignado |
| **Corralón** | Corralón (etiqueta azul) |

### Filtrar Maquinarias

- **Búsqueda rápida:** Campo "Buscar maquinarias...".
- **Filtros avanzados:**
  - **Categoría:** Filtra por categoría de maquinaria.
  - **Estado:** Disponible o No Disponible.
  - **Depósito:** Filtra por depósito.

### Crear una Maquinaria

1. Haga clic en **"Nueva Maquinaria"**.
2. Complete:
   - **Maquinaria** (obligatorio): Nombre del equipo.
   - **Categoría** (obligatorio): Seleccione de la lista.
   - **Cantidad** (obligatorio): Cantidad total.
   - **Estado** (obligatorio): Disponible o No Disponible.
   - **Corralón** (obligatorio): Al seleccionar un corralón, se habilita el campo Depósito.
   - **Depósito** (obligatorio): Seleccione el depósito dentro del corralón.
3. Haga clic en **"Crear"**.

### Editar una Maquinaria

1. Haga clic en el botón **editar** en la fila correspondiente.
2. Modifique los campos. La cantidad aparece como solo lectura en modo edición (se modifica a través de movimientos).
3. Haga clic en **"Actualizar"**.

> **Nota:** La cantidad disponible se calcula descontando las unidades en uso (asignadas y no devueltas). Para mover o asignar maquinarias, use [Movimientos de Maquinarias](#15-movimientos-de-maquinarias).

---

## 7. Vehículos

Gestiona la flota vehicular incluyendo documentación, vencimientos y estados.

### Ver Vehículos

| Columna | Descripción |
|---------|-------------|
| **Nro. Patrimonio** | Número de patrimonio del vehículo |
| **Vehículo** | Descripción del vehículo |
| **Patente** | Patente/matrícula |
| **Estado** | Estado actual con código de color |
| **Corralón** | Corralón asignado |
| **Vencimiento VTV** | Fecha de vencimiento de la VTV |
| **Documentos** | Botón con cantidad de documentos adjuntos |

**Estados posibles:**
- **Disponible** (verde): Listo para usar.
- **En Uso** (azul): Actualmente en circulación.
- **Mantenimiento** (amarillo): En reparación o servicio.
- **Fuera de Servicio** (rojo): No operativo.

### Filtrar Vehículos

- **Búsqueda:** Por número de patrimonio, descripción, marca/modelo o patente.
- **Filtros:**
  - **Marca/Modelo**
  - **Estado**
  - **Depósito**

### Crear un Vehículo

1. Haga clic en **"Nuevo Vehículo"**.
2. Complete las secciones del formulario:

**Información Básica:**
- Nro. Patrimonio
- Vehículo (obligatorio): Descripción.
- Marca/Modelo (obligatorio)
- Año
- Patente
- Tipo de Combustible (Nafta, Diesel, Gas)
- Vencimiento Oblea (solo si el combustible es Gas)

**Identificación Técnica:**
- Nro. Motor
- Nro. Chasis

**Seguro y Vencimientos:**
- Nro. Póliza
- Vencimiento Póliza
- Vencimiento VTV

**Origen y Telepase:**
- Origen
- Jurisdicción de Procedencia
- Nro. Telepase

**Estado y Ubicación:**
- Estado (obligatorio)
- Depósito (obligatorio)

3. Haga clic en **"Crear"**.

### Documentos de un Vehículo

Cada vehículo puede tener documentos adjuntos (seguros, títulos, etc.):

1. En la tabla de vehículos, haga clic en el botón de **documentos** (ícono de documento) de la fila correspondiente.
2. Se abre una ventana que muestra los documentos existentes.
3. Si tiene permisos de edición, puede agregar nuevos documentos con una descripción.
4. Use los botones de **ver** (abre en el navegador) o **descargar** para acceder a cada documento.

---

## 8. Choferes

Gestiona los conductores, sus licencias y la asignación de vehículos.

### Ver Choferes

| Columna | Descripción |
|---------|-------------|
| **Legajo** | Número de empleado (etiqueta azul) |
| **Nombre** | Nombre completo |
| **DNI** | Documento de identidad |
| **Licencia** | Tipo de licencia (Profesional o Común) |
| **Venc. Licencia** | Fecha de vencimiento. Rojo si venció, amarillo si vence en 30 días |
| **Área** | Área de trabajo |
| **Vehículos** | Vehículos asignados (etiquetas verdes con patente). "Sin asignar" si no tiene |

### Crear / Editar un Chofer

1. Haga clic en **"Nuevo Chofer"** o en el botón **editar** de la fila.
2. Complete:
   - **Nombre y Apellido** (obligatorio)
   - **DNI** (obligatorio)
   - **N° Empleado** (obligatorio)
   - **Tipo de Licencia:** Profesional o Común
   - **Vencimiento Licencia:** Fecha de vencimiento
   - **Categorías:** Categorías de licencia (ej: "A.1.4 B.2 D.1")
   - **Área:** Área de trabajo
   - **Domicilio**
   - **Secretaría:** Seleccione de la lista

3. **Asignación de vehículos:** En la parte inferior del formulario hay una lista de vehículos disponibles con casillas de verificación. Puede buscar vehículos por patente o patrimonio. Marque las casillas de los vehículos que desea asignar a este chofer.

4. Haga clic en **"Crear"** o **"Actualizar"**.

---

## 9. Empleados

Gestión básica de empleados internos del sistema.

### Ver Empleados

Tabla simple con: **Legajo**, **Apellido**, **Nombre** y acciones.

### Crear / Editar un Empleado

Campos:
- **Legajo** (obligatorio)
- **Apellido** (obligatorio)
- **Nombre** (obligatorio)

> **Nota:** Este módulo gestiona empleados locales del sistema. Los empleados municipales del sistema INASI se utilizan como destino en las asignaciones de insumos y maquinarias (ver sección de Movimientos).

---

## 10. Eventos

Gestiona eventos programados (actos, festividades, operativos, etc.).

### Ver Eventos

| Columna | Descripción |
|---------|-------------|
| **Evento** | Nombre del evento |
| **Fecha** | Fecha programada |
| **Ubicación** | Lugar del evento |
| **Secretaría** | Secretaría organizadora |
| **Anual** | "Sí" (verde) si se repite cada año, "No" (gris) si no |

### Crear / Editar un Evento

Campos:
- **Evento** (obligatorio): Nombre.
- **Fecha** (obligatorio)
- **Ubicación** (obligatorio)
- **Secretaría:** Seleccione de la lista (opcional).
- **Evento Anual:** Marque si el evento se repite anualmente.

---

## 11. Depósitos

Los depósitos son subdivisiones dentro de cada corralón. Representan ubicaciones físicas donde se almacenan insumos, maquinarias y vehículos.

### Ver Depósitos

Tabla con: **ID**, **Depósito** (nombre), **Corralón**, **Ubicación** y acciones.

Si tiene acceso a múltiples corralones, aparece un filtro desplegable para seleccionar el corralón.

### Crear / Editar un Depósito

Campos:
- **Depósito** (obligatorio): Nombre descriptivo.
- **Corralón** (obligatorio): Seleccione a qué corralón pertenece.

---

## 12. Categorías de Insumos

Permite organizar los insumos en categorías para facilitar su clasificación y búsqueda.

### Ver Categorías

Tabla con: **ID**, **Nombre**, **Descripción** y acciones.

### Crear / Editar una Categoría

Campos:
- **Nombre** (obligatorio)
- **Descripción** (opcional)

---

## 13. Categorías de Maquinarias

Idéntico a Categorías de Insumos, pero para clasificar maquinarias.

---

## 14. Movimientos de Insumos

Esta es una de las secciones más importantes del sistema. Aquí se registran todos los movimientos que afectan el stock de los insumos: entradas, salidas, transferencias entre depósitos y asignaciones.

### Pantalla Principal

La pantalla muestra una lista de todos los movimientos registrados con las siguientes columnas:

| Columna | Descripción |
|---------|-------------|
| **Fecha** | Fecha y hora del movimiento |
| **Detalles** | Nombre del insumo (clic para crear nuevo movimiento con ese insumo). Para transferencias con múltiples insumos muestra un indicador de cantidad |
| **Cantidad** | Cantidad movida con indicador de entrada (flecha verde hacia abajo) o salida (flecha roja hacia arriba) |
| **Origen** | Depósito de origen (etiqueta roja) |
| **Destino** | Depósito destino o entidad asignada (etiqueta verde) |
| **Tipo** | Tipo de movimiento (etiqueta con color según tipo) |
| **Usuario** | Usuario que realizó el movimiento |

Las transferencias con múltiples insumos se pueden expandir haciendo clic en la flecha a la izquierda para ver el detalle de cada insumo.

### Filtrar Movimientos

Haga clic en **"Filtros"** para acceder a:

- **Fecha Desde / Fecha Hasta:** Rango de fechas.
- **Depósito:** Filtrar por depósito.
- **Tipo de Movimiento:** Filtrar por tipo específico.
- **Usuario:** Filtrar por quién registró el movimiento.
- **Insumo:** Buscar por nombre de insumo.
- **Categoría:** Filtrar por categoría de insumo.

### Crear un Movimiento Individual

Los movimientos individuales se usan para registrar entradas, salidas y asignaciones de un insumo.

1. Haga clic en **"Nuevo Movimiento"** (botón azul).
2. **Paso 1 — Seleccionar Insumo:** Busque y seleccione el insumo.
3. **Paso 2 — Tipo de Movimiento:** Elija qué tipo de operación realizar:

| Tipo | Efecto en Stock | Descripción |
|------|-----------------|-------------|
| **Carga de Stock** | Suma | Ingreso de mercadería nueva |
| **Ajuste Positivo** | Suma | Corrección de inventario a favor |
| **Inventario Inicial** | Suma | Registro de stock inicial |
| **Devolución** | Suma | Devolución de material |
| **Ajuste Negativo** | Resta | Corrección de inventario en contra |
| **Asignación con Reposición** | Resta | Salida temporal, se espera devolución |
| **Asignación sin Reposición** | Resta | Salida definitiva |

4. **Paso 3 — Completar datos:** Según el tipo elegido:

   **Para Carga de Stock y Ajuste Positivo:**
   - Cantidad
   - N° Orden de Compra (opcional): Número de la orden de compra o suministro
   - Comprobantes (opcional): Adjunte hasta 5 archivos (PDF, JPG o PNG, máx. 5 MB cada uno) como respaldo

   **Para Asignaciones (con o sin reposición):**
   - Tipo de destino: **Vehículo**, **Evento** o **Empleado**
   - Busque y seleccione el destino
   - Cantidad

   > **Nota:** La "Asignación sin Reposición" solo permite como destino Vehículos y Eventos (no Empleados).

5. Confirme el movimiento.

**Atajo rápido:** También puede hacer clic en el **nombre de un insumo** en la lista de movimientos para abrir directamente el modal con ese insumo preseleccionado (salta al Paso 2).

### Crear una Transferencia

Las transferencias mueven insumos de un depósito a otro.

1. Haga clic en **"Nueva Transferencia"** (botón violeta).
2. Complete:
   - **Corralón de origen** y **Depósito de origen**
   - **Corralón de destino** y **Depósito de destino**
   - **Fecha**
   - **Observaciones** (opcional)
3. Agregue los insumos a transferir:
   - Seleccione el insumo de la lista
   - Ingrese la cantidad
   - Puede agregar múltiples insumos en una sola transferencia
4. Confirme la transferencia.

**Atajo rápido:** En cada fila de la lista de movimientos hay un **botón con flechas** que permite crear una nueva transferencia tomando como origen el depósito destino del movimiento seleccionado, precargando los mismos insumos.

### Panel de Asignaciones Pendientes de Reposición

Encima de la lista de movimientos hay un panel colapsable (botón naranja) que muestra todas las asignaciones con reposición que aún no fueron devueltas.

Cada fila muestra:
- **Insumo** y depósito
- **Asignado a:** Vehículo, Evento o Empleado (con nombre)
- **Pendiente:** Cantidad pendiente de devolución
- **Campo de cantidad:** Ingrese cuántas unidades devolver

**Acciones disponibles:**

- **Devolver** (botón verde): Registra una devolución (Entrada Reposición). El stock del insumo sube.
- **Dar de baja** (botón rojo): Cancela la pendencia sin devolver el material (Baja Reposición). El stock NO se modifica. Útil cuando un insumo se consumió definitivamente. Se pide confirmación antes de ejecutar.

### Comprobantes Adjuntos

Los movimientos de tipo "Carga de Stock" y "Ajuste Positivo" pueden tener comprobantes adjuntos (órdenes de compra, recibos, facturas).

- En la lista de movimientos, los que tienen comprobantes muestran un **ícono de clip**.
- Haga clic en el ícono para ver los archivos adjuntos.
- Puede **ver** cada archivo (se abre en el navegador) o **descargarlo**.
- Si el movimiento tiene un N° de Orden de Compra, se muestra como "OC: ..." debajo del tipo de movimiento.

---

## 15. Movimientos de Maquinarias

Funciona de manera similar a los Movimientos de Insumos, adaptado para maquinarias.

### Pantalla Principal

Lista de movimientos con: **Fecha**, **Maquinaria** (nombre y categoría), **Cantidad**, **Origen**, **Destino**, **Estado**, **Tipo** y **Usuario**.

### Filtros Disponibles

- Fecha Desde / Fecha Hasta
- Depósito
- Tipo de Movimiento
- Usuario
- Maquinaria (búsqueda por nombre)
- Categoría

### Crear un Movimiento de Maquinaria

1. Haga clic en **"Nuevo Movimiento"**.
2. Seleccione la maquinaria y el tipo de movimiento:

| Tipo | Efecto | Descripción |
|------|--------|-------------|
| **Carga de Stock** | Suma | Ingreso de maquinaria nueva |
| **Inventario Inicial Maquinaria** | Suma | Registro de stock inicial |
| **Devolución** | Suma | Devolución de maquinaria |
| **Asignación Maquinaria con Reposición** | Resta | Salida temporal, se espera devolución |
| **Asignación Maquinaria sin Reposición** | Resta | Salida definitiva |
| **Mantenimiento Maquinaria** | Resta | Envío a mantenimiento |

3. Complete los datos según el tipo (cantidad, destino, etc.).

> **Nota:** Al igual que con insumos, la "Asignación sin Reposición" solo permite Vehículos y Eventos como destino.

### Panel de Asignaciones Pendientes (Maquinarias)

Funciona igual que el de insumos:
- Muestra maquinarias asignadas con reposición pendiente
- Acciones: **Devolver** (suma stock) y **Dar de baja** (no afecta stock)

---

## 16. Administración de Usuarios

Esta sección permite gestionar los usuarios del sistema y sus permisos de acceso. Solo accesible para usuarios con permiso en el módulo "Usuarios".

### Ver Usuarios

| Columna | Descripción |
|---------|-------------|
| **Nombre** | Nombre completo (columna ordenable) |
| **Email** | Correo electrónico (columna ordenable) |
| **Rol** | Rol asignado. "Administrador" se muestra en violeta |
| **Permisos** | Resumen de permisos: "Acceso total" para administradores; para otros roles muestra los corralones y módulos asignados |

### Filtros

- **Rol:** Filtrar por rol
- **Tipo de Acceso:** Todos / Administradores / Acceso limitado
- **Corralón:** Filtrar por corralón asignado

### Crear / Editar un Usuario

**Datos básicos:**
- **Nombre** (obligatorio)
- **Email** (obligatorio)

**Rol:**
Seleccione el rol del usuario. Cada rol se presenta como una tarjeta con su nombre y descripción. El rol **Administrador** otorga acceso total al sistema sin necesidad de configurar permisos adicionales.

**Contraseña:**
- Al crear: ingrese la contraseña y confírmela.
- Al editar: deje en blanco para mantener la contraseña actual. Complete ambos campos solo si desea cambiarla.

### Configuración de Permisos (roles no Administrador)

Si el usuario tiene un rol distinto a Administrador, aparece la sección **"Permisos de Acceso"** con dos paneles:

#### Módulos Globales
Son módulos que no dependen de un corralón específico:
- Empleados
- Choferes
- Eventos
- Categorías Insumos
- Categorías Maquinarias
- Usuarios

Para cada módulo, puede configurar:
- **Ver:** El usuario puede consultar los datos pero no modificarlos.
- **Editar:** El usuario puede crear, modificar y eliminar registros.
- Sin seleccionar ninguno: el usuario no tiene acceso al módulo.

#### Permisos por Corralón
Permite asignar acceso a módulos específicos dentro de cada corralón:

1. **Seleccione los corralones** a los que el usuario tendrá acceso (botones azules).
2. Para cada corralón seleccionado, configure los módulos:
   - Insumos
   - Maquinarias
   - Vehículos
   - Depósitos
   - Movimientos Insumos
   - Movimientos Maquinarias

Cada módulo puede ser **Ver** (solo lectura) o **Editar** (lectura y escritura).

> **Ejemplo:** Un usuario con rol "Operador" podría tener:
> - Acceso global: Ver Empleados, Ver Choferes
> - Corralón "Central": Editar Insumos, Editar Movimientos Insumos, Ver Maquinarias
> - Corralón "Norte": Ver Insumos

---

## 17. Preguntas Frecuentes

### General

**P: No puedo ver alguna sección del menú.**
R: Su usuario no tiene permisos para esa sección. Contacte al administrador para solicitar acceso.

**P: Los datos que veo parecen incompletos.**
R: El sistema filtra automáticamente los datos según los corralones y depósitos a los que usted tiene acceso. Si necesita ver más datos, solicite permisos adicionales.

### Insumos y Stock

**P: ¿Cómo agrego stock a un insumo?**
R: Vaya a **Movimientos Insumos** > **Nuevo Movimiento** > seleccione el insumo > elija "Carga de Stock" > ingrese la cantidad.

**P: ¿Puedo modificar el stock directamente?**
R: No. El stock se calcula automáticamente a partir de los movimientos registrados. Esto garantiza la trazabilidad de todas las operaciones.

**P: ¿Qué significa "Stock bajo mínimo"?**
R: Significa que la cantidad actual del insumo está por debajo del nivel mínimo configurado. Aparece como alerta en el Dashboard y se resalta en rojo en la lista de insumos.

**P: ¿Qué diferencia hay entre "Asignación con Reposición" y "sin Reposición"?**
R: La **con reposición** es una salida temporal — se espera que el material se devuelva (aparece en el panel de Pendientes). La **sin reposición** es una salida definitiva — no se espera devolución.

### Transferencias

**P: ¿Puedo transferir múltiples insumos a la vez?**
R: Sí. Use el botón **"Nueva Transferencia"** para crear una transferencia con varios insumos en una sola operación.

**P: ¿Qué hace el botón de flechas en la lista de movimientos?**
R: Permite crear rápidamente una nueva transferencia tomando como origen el destino del movimiento seleccionado. Esto es útil para reenviar insumos que acaban de llegar.

### Vehículos

**P: ¿Cómo veo los documentos de un vehículo?**
R: En la tabla de vehículos, haga clic en el botón de **documentos** (ícono de documento con número) en la fila del vehículo.

**P: ¿Qué significan los colores en el vencimiento de VTV?**
R: Rojo = vencida, naranja = vence en menos de 7 días, amarillo = vence en menos de 30 días.

### Usuarios y Permisos

**P: ¿Puedo eliminar mi propio usuario?**
R: No. El sistema no permite que un usuario se elimine a sí mismo.

**P: ¿Qué pasa si asigno el rol Administrador?**
R: Los administradores tienen acceso total a todas las secciones y todos los corralones. No es necesario configurar permisos individuales.

---

*Manual generado para el Sistema de Gestión de Stock — Municipalidad de Mercedes*
