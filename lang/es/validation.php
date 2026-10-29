<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mensajes de validación
    |--------------------------------------------------------------------------
    |
    | Trato de usted. :Attribute (mayúscula) cuando el campo abre la oración.
    | Redacción neutra en género ("Falta :attribute.", "El valor elegido para
    | :attribute..."): los nombres de 'attributes' llevan su artículo.
    | Los nombres de los campos se traducen en la clave 'attributes' del final.
    |
    */

    'accepted' => 'Debe aceptar :attribute.',
    'accepted_if' => 'Debe aceptar :attribute cuando :other sea :value.',
    'active_url' => ':Attribute no es una URL válida.',
    'after' => ':Attribute debe ser una fecha posterior a :date.',
    'after_or_equal' => ':Attribute debe ser una fecha posterior o igual a :date.',
    'alpha' => ':Attribute solo puede contener letras.',
    'alpha_dash' => ':Attribute solo puede contener letras, números, guiones y guiones bajos.',
    'alpha_num' => ':Attribute solo puede contener letras y números.',
    'any_of' => 'El valor de :attribute no es válido.',
    'array' => ':Attribute debe ser un conjunto de valores.',
    'ascii' => ':Attribute solo puede contener caracteres alfanuméricos y símbolos de un byte.',
    'before' => ':Attribute debe ser una fecha anterior a :date.',
    'before_or_equal' => ':Attribute debe ser una fecha anterior o igual a :date.',
    'between' => [
        'array' => ':Attribute debe tener entre :min y :max elementos.',
        'file' => ':Attribute debe pesar entre :min y :max kilobytes.',
        'numeric' => ':Attribute debe estar entre :min y :max.',
        'string' => ':Attribute debe tener entre :min y :max caracteres.',
    ],
    'boolean' => ':Attribute debe ser verdadero o falso.',
    'can' => ':Attribute contiene un valor no autorizado.',
    'confirmed' => 'La confirmación de :attribute no coincide.',
    'contains' => 'A :attribute le falta un valor requerido.',
    'current_password' => 'La contraseña es incorrecta.',
    'date' => ':Attribute no es una fecha válida.',
    'date_equals' => ':Attribute debe ser una fecha igual a :date.',
    'date_format' => ':Attribute no coincide con el formato :format.',
    'decimal' => ':Attribute debe tener :decimal decimales.',
    'declined' => 'Debe rechazar :attribute.',
    'declined_if' => 'Debe rechazar :attribute cuando :other sea :value.',
    'different' => ':Attribute y :other deben ser distintos.',
    'digits' => ':Attribute debe tener :digits dígitos.',
    'digits_between' => ':Attribute debe tener entre :min y :max dígitos.',
    'dimensions' => 'Las dimensiones de la imagen de :attribute no son válidas.',
    'distinct' => ':Attribute tiene un valor duplicado.',
    'doesnt_contain' => ':Attribute no puede contener ninguno de los siguientes valores: :values.',
    'doesnt_end_with' => ':Attribute no puede terminar con ninguno de los siguientes valores: :values.',
    'doesnt_start_with' => ':Attribute no puede empezar con ninguno de los siguientes valores: :values.',
    'email' => ':Attribute no tiene un formato válido.',
    'encoding' => ':Attribute debe estar codificado en :encoding.',
    'ends_with' => ':Attribute debe terminar con uno de los siguientes valores: :values.',
    'enum' => 'El valor elegido para :attribute no es válido.',
    'exists' => 'El valor elegido para :attribute no es válido.',
    'extensions' => ':Attribute debe tener una de las siguientes extensiones: :values.',
    'file' => ':Attribute debe ser un archivo.',
    'filled' => 'Falta :attribute.',
    'gt' => [
        'array' => ':Attribute debe tener más de :value elementos.',
        'file' => ':Attribute debe pesar más de :value kilobytes.',
        'numeric' => ':Attribute debe ser mayor que :value.',
        'string' => ':Attribute debe tener más de :value caracteres.',
    ],
    'gte' => [
        'array' => ':Attribute debe tener :value elementos o más.',
        'file' => ':Attribute debe pesar :value kilobytes o más.',
        'numeric' => ':Attribute debe ser mayor o igual que :value.',
        'string' => ':Attribute debe tener :value caracteres o más.',
    ],
    'hex_color' => ':Attribute debe ser un color hexadecimal válido.',
    'image' => ':Attribute debe ser una imagen.',
    'in' => 'El valor elegido para :attribute no es válido.',
    'in_array' => ':Attribute debe existir en :other.',
    'in_array_keys' => ':Attribute debe contener al menos una de las siguientes claves: :values.',
    'integer' => ':Attribute debe ser un número entero.',
    'ip' => ':Attribute debe ser una dirección IP válida.',
    'ipv4' => ':Attribute debe ser una dirección IPv4 válida.',
    'ipv6' => ':Attribute debe ser una dirección IPv6 válida.',
    'json' => ':Attribute debe ser una cadena JSON válida.',
    'list' => ':Attribute debe ser una lista.',
    'lowercase' => ':Attribute debe estar en minúsculas.',
    'lt' => [
        'array' => ':Attribute debe tener menos de :value elementos.',
        'file' => ':Attribute debe pesar menos de :value kilobytes.',
        'numeric' => ':Attribute debe ser menor que :value.',
        'string' => ':Attribute debe tener menos de :value caracteres.',
    ],
    'lte' => [
        'array' => ':Attribute no puede tener más de :value elementos.',
        'file' => ':Attribute debe pesar :value kilobytes o menos.',
        'numeric' => ':Attribute debe ser menor o igual que :value.',
        'string' => ':Attribute debe tener :value caracteres o menos.',
    ],
    'mac_address' => ':Attribute debe ser una dirección MAC válida.',
    'max' => [
        'array' => ':Attribute no puede tener más de :max elementos.',
        'file' => ':Attribute no puede pesar más de :max kilobytes.',
        'numeric' => ':Attribute no puede ser mayor que :max.',
        'string' => ':Attribute no puede tener más de :max caracteres.',
    ],
    'max_digits' => ':Attribute no puede tener más de :max dígitos.',
    'mimes' => ':Attribute debe ser un archivo de tipo: :values.',
    'mimetypes' => ':Attribute debe ser un archivo de tipo: :values.',
    'min' => [
        'array' => ':Attribute debe tener al menos :min elementos.',
        'file' => ':Attribute debe pesar al menos :min kilobytes.',
        'numeric' => ':Attribute debe ser al menos :min.',
        'string' => ':Attribute debe tener al menos :min caracteres.',
    ],
    'min_digits' => ':Attribute debe tener al menos :min dígitos.',
    'missing' => ':Attribute no debe estar presente.',
    'missing_if' => ':Attribute no debe estar presente cuando :other sea :value.',
    'missing_unless' => ':Attribute no debe estar presente salvo que :other sea :value.',
    'missing_with' => ':Attribute no debe estar presente cuando :values esté presente.',
    'missing_with_all' => ':Attribute no debe estar presente cuando :values estén presentes.',
    'multiple_of' => ':Attribute debe ser múltiplo de :value.',
    'not_in' => 'El valor elegido para :attribute no es válido.',
    'not_regex' => 'El formato de :attribute no es válido.',
    'numeric' => ':Attribute debe ser un número.',
    'password' => [
        'letters' => ':Attribute debe contener al menos una letra.',
        'mixed' => ':Attribute debe contener al menos una mayúscula y una minúscula.',
        'numbers' => ':Attribute debe contener al menos un número.',
        'symbols' => ':Attribute debe contener al menos un símbolo.',
        'uncompromised' => ':Attribute apareció en una filtración de datos. Elija otra contraseña.',
    ],
    'present' => ':Attribute debe estar presente.',
    'present_if' => ':Attribute debe estar presente cuando :other sea :value.',
    'present_unless' => ':Attribute debe estar presente salvo que :other sea :value.',
    'present_with' => ':Attribute debe estar presente cuando :values esté presente.',
    'present_with_all' => ':Attribute debe estar presente cuando :values estén presentes.',
    'prohibited' => 'No se admite :attribute.',
    'prohibited_if' => 'No se admite :attribute cuando :other sea :value.',
    'prohibited_if_accepted' => 'No se admite :attribute cuando se acepta :other.',
    'prohibited_if_declined' => 'No se admite :attribute cuando se rechaza :other.',
    'prohibited_unless' => 'No se admite :attribute salvo que :other esté en :values.',
    'prohibits' => ':Attribute impide que :other esté presente.',
    'regex' => 'El formato de :attribute no es válido.',
    'required' => 'Falta :attribute.',
    'required_array_keys' => ':Attribute debe contener entradas para: :values.',
    'required_if' => 'Falta :attribute cuando :other es :value.',
    'required_if_accepted' => 'Falta :attribute cuando se acepta :other.',
    'required_if_declined' => 'Falta :attribute cuando se rechaza :other.',
    'required_unless' => 'Falta :attribute salvo que :other esté en :values.',
    'required_with' => 'Falta :attribute cuando :values está presente.',
    'required_with_all' => 'Falta :attribute cuando :values están presentes.',
    'required_without' => 'Falta :attribute cuando :values no está presente.',
    'required_without_all' => 'Falta :attribute cuando ninguno de :values está presente.',
    'same' => ':Attribute debe coincidir con :other.',
    'size' => [
        'array' => ':Attribute debe tener :size elementos.',
        'file' => ':Attribute debe pesar :size kilobytes.',
        'numeric' => ':Attribute debe ser :size.',
        'string' => ':Attribute debe tener :size caracteres.',
    ],
    'starts_with' => ':Attribute debe empezar con uno de los siguientes valores: :values.',
    'string' => ':Attribute debe ser texto.',
    'timezone' => ':Attribute debe ser una zona horaria válida.',
    'unique' => ':Attribute ya está en uso.',
    'uploaded' => 'No se pudo subir :attribute.',
    'uppercase' => ':Attribute debe estar en mayúsculas.',
    'url' => ':Attribute debe ser una URL válida.',
    'ulid' => ':Attribute debe ser un ULID válido.',
    'uuid' => ':Attribute debe ser un UUID válido.',

    /*
    |--------------------------------------------------------------------------
    | Mensajes de validación personalizados
    |--------------------------------------------------------------------------
    |
    | Reglas de negocio con mensaje propio: 'campo.regla' => 'mensaje'.
    |
    */

    'custom' => [
        //
    ],

    /*
    |--------------------------------------------------------------------------
    | Nombres de los campos
    |--------------------------------------------------------------------------
    |
    | Reemplaza :attribute por un nombre legible. Se van agregando a medida
    | que aparecen los formularios.
    |
    */

    'attributes' => [
        // Acceso y usuarios
        'email'                                => 'el correo electrónico',
        'password'                             => 'la contraseña',
        'password_confirmation'                => 'la confirmación de la contraseña',
        'current_password'                     => 'la contraseña actual',
        'form.email'                           => 'el correo electrónico',
        'form.password'                        => 'la contraseña',
        'name'                                 => 'el nombre',
        'id_rol'                               => 'el rol',

        // Ubicaciones y catálogos
        'nombre'                               => 'el nombre',
        'descripcion'                          => 'la descripción',
        'ubicacion'                            => 'la ubicación',
        'id_corralon'                          => 'el corralón',
        'id_deposito'                          => 'el depósito',
        'deposito'                             => 'el depósito',
        'secretaria'                           => 'la secretaría',
        'id_secretaria'                        => 'la secretaría',
        'secretaria_id'                        => 'la secretaría',
        'area'                                 => 'el área',
        'evento'                               => 'el evento',
        'fecha'                                => 'la fecha',
        'evento_anual'                         => 'evento anual',

        // Insumos y maquinarias
        'insumo'                               => 'el insumo',
        'id_categoria'                         => 'la categoría',
        'unidad'                               => 'la unidad',
        'stock_minimo'                         => 'el stock mínimo',
        'stock_inicial'                        => 'el stock inicial',
        'maquinaria'                           => 'la maquinaria',
        'id_categoria_maquinaria'              => 'la categoría',
        'estado'                               => 'el estado',
        'cantidad'                             => 'la cantidad',

        // Movimientos y transferencias
        'id_deposito_origen'                   => 'el depósito de origen',
        'id_deposito_destino'                  => 'el depósito de destino',
        'id_deposito_origen_tf'                => 'el depósito de origen',
        'id_deposito_destino_tf'               => 'el depósito de destino',
        'insumos_transferencia'                => 'la lista de insumos a transferir',
        'insumos_transferencia.*.cantidad'     => 'la cantidad',
        'maquinarias_transferencia'            => 'la lista de maquinarias a transferir',
        'maquinarias_transferencia.*.cantidad' => 'la cantidad',
        'observaciones'                        => 'el texto de las observaciones',
        'observaciones_transferencia'          => 'el texto de las observaciones',
        'nro_orden_compra'                     => 'el N° de orden de compra',
        'comprobantes'                         => 'la lista de comprobantes',
        'comprobantes.*'                       => 'el comprobante',
        'tipo_destino'                         => 'el tipo de destino',
        'id_referencia'                        => 'el destino',
        'id_secretaria_ajuste'                 => 'la secretaría',
        'edit_nro_orden_compra'                => 'el N° de orden de compra',
        'edit_observaciones'                   => 'el texto de las observaciones',
        'edit_comprobantes'                    => 'la lista de comprobantes',
        'edit_comprobantes.*'                  => 'el comprobante',
        'multi_observaciones'                  => 'el texto de las observaciones',

        // Vehículos y documentos
        'vehiculo'                             => 'el vehículo',
        'marca_modelo'                         => 'la marca y modelo',
        'nro_motor'                            => 'el N° de motor',
        'nro_chasis'                           => 'el N° de chasis',
        'anio'                                 => 'el año',
        'patente'                              => 'la patente',
        'tipo_combustible'                     => 'el tipo de combustible',
        'nro_poliza'                           => 'el N° de póliza',
        'vencimiento_poliza'                   => 'el vencimiento de la póliza',
        'vencimiento_vtv'                      => 'el vencimiento de la VTV',
        'origen'                               => 'el origen',
        'jurisdiccion_procedencia'             => 'la jurisdicción de procedencia',
        'nro_telepase'                         => 'el N° de telepase',
        'nro_patrimonio'                       => 'el N° de patrimonio',
        'vencimiento_oblea'                    => 'el vencimiento de la oblea',
        'nuevo_documento'                      => 'el documento',
        'nueva_descripcion'                    => 'la descripción',

        // Choferes
        'dni'                                  => 'el DNI',
        'numero_empleado'                      => 'el N° de empleado',
        'licencia'                             => 'la licencia',
        'vencimiento_licencia'                 => 'el vencimiento de la licencia',
        'domicilio'                            => 'el domicilio',
        'categoriasSeleccionadas'              => 'la selección de categorías de licencia',
        'categoriasSeleccionadas.*'            => 'la categoría de licencia',
    ],

];
