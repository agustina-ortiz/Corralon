<?php

/*
 * Integración con la intranet municipal: receptor SSO y API /api/intranet/v1
 * (contrato en intranet/docs/integracion.md §5).
 */
return [
    // Único mensaje para cualquier falla del ingreso por ticket (contrato §5).
    'sso_rechazado' => 'No pudimos validar tu ingreso desde la intranet. Entrá con tu usuario y clave.',

    'api' => [
        'no_autorizado' => 'No autorizado.',
        'ip_no_permitida' => 'Acceso no permitido desde esta dirección.',
        'no_existe' => 'No existe.',
    ],

    'errores' => [
        'administrador_alta' => 'Desde la intranet no se pueden crear usuarios Administradores.',
        'administrador_estado' => 'Desde la intranet no se puede activar ni desactivar a un Administrador.',
        'administrador_vinculo' => 'Desde la intranet no se puede vincular a un Administrador.',
        'email_con_otro_dni' => 'Ese usuario ya está vinculado a otro DNI (:dni).',
        'dni_en_otro_usuario' => 'Ese DNI ya está en otro usuario.',
        'modulo_desconocido' => 'El corralón no tiene estos módulos: :modulos.',
        'corralon_desconocido' => 'El corralón elegido no existe.',
        'deposito_desconocido' => 'El depósito elegido no existe o no es de ese corralón.',
        'nivel_desconocido' => 'El nivel de acceso tiene que ser "ver" o "editar".',
        'dni_obligatorio' => 'Falta el DNI.',
    ],

    'atributos' => [
        'dni' => 'el DNI',
        'actor' => 'el administrador de la intranet',
        'actor.dni' => 'el DNI del administrador de la intranet',
        'actor.nombre' => 'el nombre del administrador de la intranet',
        'activo' => 'el estado',
    ],
];
