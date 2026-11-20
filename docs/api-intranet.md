# API para la intranet municipal (`/api/intranet/v1`)

Contrato general: `C:\xampp\htdocs\intranet\docs\integracion.md` §5. Esta guía documenta
lo propio del Corralón: la forma de `GET alta/opciones` y los campos del alta.

## Puerta

- `Authorization: Bearer {INTRANET_CLAVE_SERVICIO}`. Sin clave o con clave mala:
  **401** `{"message": "No autorizado."}` (comparación con `hash_equals`; sin clave
  configurada rechaza todo).
- IP fuera de `INTRANET_IPS`: **403**. Vacía = no se controla IP (solo local y tests).
- `throttle:60,1`. JSON siempre (también los 404, 422 y 429). Sin Sanctum ni sesión.
- Los pedidos que escriben llevan `actor: {"dni": 20123456, "nombre": "Pérez, Ana"}`;
  sin actor válido → **422** en `actor`.

Middleware `App\Http\Middleware\ClaveServicioIntranet`, rutas en `routes/api.php`,
controlador `App\Http\Controllers\Api\Intranet\UsuarioController`.

## Administradores: fuera de la API

Es el equivalente del "superadmin" del contrato. Desde la API **no se crea** un usuario
con rol Administrador (422 en `id_rol`), **no se activa ni desactiva** uno (422 en
`activo`) y **no se le vincula el DNI** (422 en `email`): eso se hace a mano desde
Usuarios en el corralón. `GET alta/opciones` no ofrece el rol Administrador. Cualquier
`es_superadmin` o `acceso_todos_corralones` en el cuerpo se ignora.

## `GET alta/opciones`

```json
{
  "campos": [
    {"nombre": "name", "etiqueta": "Nombre", "tipo": "texto", "obligatorio": true, "max": 255},
    {"nombre": "email", "etiqueta": "Email", "tipo": "email", "obligatorio": true, "max": 255},
    {"nombre": "password", "etiqueta": "Contraseña", "tipo": "clave", "obligatorio": true, "min": 8},
    {"nombre": "password_confirmation", "etiqueta": "Confirmar Contraseña", "tipo": "clave", "obligatorio": true},
    {"nombre": "id_rol", "etiqueta": "Rol", "tipo": "seleccion", "obligatorio": true,
     "opciones": [{"id": 2, "nombre": "Visualizador", "descripcion": "Solo puede visualizar…"}]},
    {"nombre": "permisos_globales", "etiqueta": "Módulos Globales", "tipo": "permisos_globales", "obligatorio": false,
     "ayuda": "…", "modulos": [{"clave": "vehiculos", "etiqueta": "Vehículos (todos)", "mixto": true},
                               {"clave": "empleados", "etiqueta": "Empleados", "mixto": false}, "…"]},
    {"nombre": "corralones_seleccionados", "etiqueta": "Corralones", "tipo": "corralones", "obligatorio": false, "ayuda": "…"},
    {"nombre": "permisos_por_corralon", "etiqueta": "Permisos por Corralón", "tipo": "permisos_por_corralon", "obligatorio": false,
     "ayuda": "…", "modulos": [{"clave": "insumos", "etiqueta": "Insumos", "mixto": false}, "…"]},
    {"nombre": "depositos_especificos", "etiqueta": "Depósitos específicos", "tipo": "depositos_especificos", "obligatorio": false, "ayuda": "…"}
  ],
  "niveles": [{"valor": "ver", "etiqueta": "Solo ver"}, {"valor": "editar", "etiqueta": "Ver y editar"}],
  "corralones": [{"id": 1, "nombre": "Corralón Centro", "depositos": [{"id": 3, "nombre": "Galpón 1"}]}]
}
```

- `campos` va en el orden del modal del corralón; `nombre` es el nombre del campo en el
  cuerpo del alta y en los errores 422.
- Los `tipo` de la matriz:
  - `permisos_globales`: un nivel (de `niveles`) o nada por cada módulo de `modulos`.
  - `corralones`: varios de `corralones` (los ids).
  - `permisos_por_corralon`: para cada corralón elegido, un nivel o nada por cada
    módulo de `modulos`.
  - `depositos_especificos`: opcional, para un módulo de un corralón, algunos de los
    `depositos` de ese corralón. Sin depósitos = todos los del corralón. (La pantalla
    del corralón hoy no lo muestra, pero lo conserva y el guardado lo admite.)
- Un módulo `mixto` (Vehículos) está en las dos secciones: con permiso global ve todos
  los vehículos y el permiso por corralón de ese módulo se ignora.
- El rol Administrador no se ofrece. Todos los roles que se ofrecen usan la matriz.

## `POST usuarios`

```json
{
  "dni": 30111222,
  "name": "Carlos Gómez",
  "email": "carlos@mercedes.gob.ar",
  "password": "********", "password_confirmation": "********",
  "id_rol": 2,
  "permisos_globales": {"choferes": "editar", "vehiculos": "ver"},
  "corralones_seleccionados": [1],
  "permisos_por_corralon": {"1": {"insumos": "editar", "maquinarias": "ver"}},
  "depositos_especificos": {"1": {"insumos": [3]}},
  "actor": {"dni": 20123456, "nombre": "Pérez, Ana"}
}
```

- Usa `App\Actions\Usuarios\GuardarUsuario`, la misma lógica y las mismas reglas que la
  pantalla Usuarios. El usuario nace **activo**.
- `dni` obligatorio (entero de 1 a 99999999, sin puntos) y único.
- Los corralones que aparecen en `permisos_por_corralon` o `depositos_especificos` se
  suman solos a `corralones_seleccionados`.
- Un módulo, corralón o depósito que el corralón no tiene es un **422** (no se ignora):
  en `permisos_globales`, `corralones_seleccionados`, `permisos_por_corralon.{id}` o
  `depositos_especificos.{id}.{modulo}`; un nivel que no es `ver`/`editar`, en
  `permisos_globales.{modulo}` o `permisos_por_corralon.{id}.{modulo}`.
- `201 {"id": n}` · `422 {"message", "errors": {"campo": ["mensaje"]}}`.

## Los demás

| Pedido | Cuerpo | Respuestas |
|---|---|---|
| `GET usuarios/{dni}` | — | `200 {"id", "dni", "nombre", "email", "activo"}` · `404 {"message": "No existe."}` |
| `PATCH usuarios/{dni}/vinculo` | `email`, `actor` | `200 {"id"}` · `404` sin usuario con ese email · `422` en `email` si ya tiene otro DNI o es Administrador · `422` en `dni` si el DNI está en otro usuario. Repetirlo no cambia nada |
| `PATCH usuarios/{dni}/estado` | `activo` (bool), `actor` | `200 {"id", "activo"}` · `404` · `422` en `activo` si es Administrador. Desactivar cierra sus sesiones |

## Actividad

Todo lo que escribe la API queda en Actividad (`activity_log`) con `causer` nulo y
`properties.origen = "intranet"`, `actor_dni`, `actor_nombre`; la pantalla `/actividad`
lo muestra como **"Intranet · Pérez, Ana (DNI 20123456)"**. El alta deja un registro del
usuario y uno por cada permiso.

## SSO

`GET /sso/ingresar?ticket=…` (web, `throttle:30,1`): canjea el ticket en
`{INTRANET_URL}/api/sso/v1/canjes` y entra con el usuario **activo** de ese DNI. Cualquier
falla vuelve al login con "No pudimos validar tu ingreso desde la intranet. Entrá con tu
usuario y clave.". El corralón no tiene "Uso del panel": el ingreso no se registra aparte.
