<?php

/*
 * Pantalla Actividad (kit, spec 38): quién creó, modificó o borró qué y cuándo. La
 * traducción de cada registro está en App\Support\ActividadLegible.
 */
return [
    'titulo' => 'Actividad',
    'buscar' => 'Buscar por nombre, usuario o tipo de registro...',

    'filtros' => [
        'accion' => ['todos' => 'Todas las acciones', 'etiqueta' => 'Acción'],
        'tipo' => ['todos' => 'Todos los registros', 'etiqueta' => 'Tipo de registro'],
        'usuario' => ['todos' => 'Todos', 'etiqueta' => 'Quién lo hizo', 'sistema' => 'Sistema (sin usuario)', 'intranet' => 'Intranet'],
        'desde' => 'Desde',
        'hasta' => 'Hasta',
    ],

    'acciones' => [
        'created' => 'Creó',
        'updated' => 'Editó',
        'deleted' => 'Eliminó',
        'sistema' => 'Sistema',
    ],
    'acciones_filtro' => [
        'created' => 'Creaciones',
        'updated' => 'Ediciones',
        'deleted' => 'Eliminaciones',
    ],

    'grupos' => [
        'usuarios' => 'Usuarios y permisos',
    ],

    'tipos' => [
        'User' => 'Usuario',
        'UsuarioPermiso' => 'Permiso de usuario',
    ],

    'nombres' => [
        'permiso' => 'Permiso: :modulo',
    ],

    'campos' => [
        'name' => 'Nombre',
        'email' => 'Email',
        'id_rol' => 'Rol',
        'dni' => 'DNI',
        'activo' => 'Activo',
        'corralones_permitidos' => 'Corralones',
        'acceso_todos_corralones' => 'Acceso a todos los corralones',
        'id_usuario' => 'Usuario',
        'id_corralon' => 'Corralón',
        'id_deposito' => 'Depósito',
        'modulo' => 'Módulo',
        'nivel_acceso' => 'Nivel de acceso',
    ],

    'columnas' => [
        'accion' => 'Acción',
        'registro' => 'Registro',
        'usuario' => 'Quién',
        'hora' => 'Hora',
    ],

    'detalle' => [
        'campo' => 'Campo',
        'antes' => 'Antes',
        'despues' => 'Después',
        'created' => 'Datos con que se creó',
        'deleted' => 'Datos que tenía',
    ],

    'registros' => '{0} Sin registros|{1} 1 registro|[2,*] :cantidad registros',
    'resumen' => 'Cambió: :campos',
    'y' => 'y',
    'en' => 'en',
    'por' => 'Por',
    'hoy' => 'Hoy',
    'ayer' => 'Ayer',
    'si' => 'Sí',
    'no' => 'No',
    'vacio' => '(vacío)',
    'global' => 'Global',
    'registro' => 'Registro',
    'sin_nombre' => '(sin nombre)',
    'eliminado' => '#:id (ya no existe)',
    'ya_no_existe' => 'Ya no existe',
    'sistema' => 'Sistema',
    'intranet' => 'Intranet · :nombre (DNI :dni)',
    'ver_detalle' => 'Ver detalle',
    'ocultar_detalle' => 'Ocultar detalle',

    'vacios' => [
        'sin_actividad' => 'Todavía no hay actividad registrada.',
        'sin_resultados' => 'No hay actividad que coincida con los filtros.',
    ],
];
