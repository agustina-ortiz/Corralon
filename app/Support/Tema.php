<?php

namespace App\Support;

/**
 * Clases de color de las piezas <x-ui.*> (kit de especificaciones, spec 10).
 *
 * Ninguna pieza escribe un color de acento a mano: lo pide acá. El Corralón
 * conserva sus colores de siempre (botones azules, verde municipal #77BF43 en
 * el sidebar), así que hay una sola paleta y no hay marca blanca.
 *
 * Las clases van LITERALES: Tailwind arma el CSS escaneando los archivos
 * (tailwind.config.js incluye ./app/**\/*.php) y una clase armada en tiempo
 * de ejecución nunca llega al build.
 */
final class Tema
{
    /** Header de modal: fondo blanco con título oscuro, como los modales de siempre. */
    public static function encabezadoModal(): string
    {
        return 'bg-white text-gray-900 border-b border-gray-200';
    }

    /** Botón de cerrar (X) del header de modal. */
    public static function cerrarModal(): string
    {
        return 'text-gray-400 hover:text-gray-600 hover:bg-gray-100 focus:ring-blue-500/40';
    }

    /** Icono del título del modal. */
    public static function iconoModal(): string
    {
        return 'text-blue-600';
    }

    /** Botón primario (guardar, confirmar, nuevo). */
    public static function botonPrimario(): string
    {
        return 'bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white shadow-lg shadow-blue-500/30 focus:ring-blue-500';
    }

    /** Botón secundario (cancelar, cerrar). */
    public static function botonSecundario(): string
    {
        return 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-100 focus:ring-gray-400';
    }

    /** Botón de peligro (eliminar, dar de baja). */
    public static function botonPeligro(): string
    {
        return 'bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white shadow-lg shadow-red-500/30 focus:ring-red-500';
    }

    /** Texto de acento: icono del título de pantalla, casillas marcadas. */
    public static function acentoTexto(): string
    {
        return 'text-blue-600';
    }

    /** Enlaces y botones de solo texto. */
    public static function enlace(): string
    {
        return 'text-blue-600 hover:text-blue-800';
    }

    /** Acción de fila (icono) neutra: editar, ver. Azul como las de siempre. */
    public static function accionNeutra(): string
    {
        return 'text-blue-600 hover:bg-blue-50 focus:ring-blue-500/40';
    }

    /** Anillo de foco de inputs y desplegables. */
    public static function anilloFoco(): string
    {
        return 'focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500';
    }

    /** Fondo suave de acento (opción resaltada de un desplegable, chips). */
    public static function acentoFondoSuave(): string
    {
        return 'bg-blue-50 text-blue-800';
    }

    /** La opción elegida de <x-ui.opciones>: clases `peer-checked:` del radio oculto. */
    public static function opcionElegida(): string
    {
        return 'peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600 peer-focus-visible:ring-2 peer-focus-visible:ring-blue-500';
    }
}
