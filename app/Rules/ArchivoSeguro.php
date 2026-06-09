<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

/**
 * Valida que un archivo subido sea seguro.
 *
 * Defensa en profundidad sobre la regla `mimes` de Laravel:
 *  1) Bloquea extensiones ejecutables / peligrosas (web shells, binarios, scripts),
 *     sin importar el MIME declarado.
 *  2) Exige que la extensión esté en una lista blanca (por defecto PDF/JPG/PNG).
 *  3) Verifica que el CONTENIDO REAL del archivo (MIME detectado por finfo)
 *     coincida con un tipo permitido. Así un .php renombrado a .jpg (doble
 *     extensión o MIME falsificado) es rechazado igualmente.
 */
class ArchivoSeguro implements ValidationRule
{
    /** Extensiones peligrosas: nunca se aceptan, sin importar el MIME. */
    public const EXTENSIONES_BLOQUEADAS = [
        'php', 'php2', 'php3', 'php4', 'php5', 'php6', 'php7', 'php8',
        'phtml', 'pht', 'phps', 'phar', 'inc',
        'exe', 'com', 'bat', 'cmd', 'msi', 'scr', 'dll', 'sys',
        'sh', 'bash', 'bin', 'run', 'cgi', 'pl', 'py', 'rb',
        'asp', 'aspx', 'jsp', 'jspx', 'jar', 'war',
        'js', 'mjs', 'vbs', 'ps1', 'htaccess', 'htm', 'html', 'svg', 'xml',
    ];

    /** MIME types realmente permitidos (verificados por contenido). */
    protected array $mimesPermitidos;

    /** Extensiones permitidas (deben coincidir con el MIME real). */
    protected array $extensionesPermitidas;

    public function __construct(
        array $extensionesPermitidas = ['pdf', 'jpg', 'jpeg', 'png'],
        array $mimesPermitidos = ['application/pdf', 'image/jpeg', 'image/png'],
    ) {
        $this->extensionesPermitidas = array_map('strtolower', $extensionesPermitidas);
        $this->mimesPermitidos = $mimesPermitidos;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile) {
            $fail('El archivo no es válido.');
            return;
        }

        if (! $value->isValid()) {
            $fail('El archivo no se subió correctamente.');
            return;
        }

        // Extensión declarada por el cliente (la que ve el usuario).
        $extCliente = strtolower($value->getClientOriginalExtension());

        // 1) Lista negra: rechazar cualquier extensión ejecutable / peligrosa.
        if (in_array($extCliente, self::EXTENSIONES_BLOQUEADAS, true)) {
            $fail('Tipo de archivo no permitido por seguridad.');
            return;
        }

        // 2) La extensión debe estar en la lista blanca.
        if (! in_array($extCliente, $this->extensionesPermitidas, true)) {
            $fail('Solo se permiten archivos PDF, JPG o PNG.');
            return;
        }

        // 3) El contenido real (MIME por finfo) debe coincidir con lo permitido.
        $mimeReal = $value->getMimeType();
        if (! in_array($mimeReal, $this->mimesPermitidos, true)) {
            $fail('El contenido del archivo no coincide con un PDF o imagen válida.');
            return;
        }
    }
}
