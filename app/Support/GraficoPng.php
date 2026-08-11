<?php

namespace App\Support;

/**
 * Genera gráficos como imágenes PNG (GD) embebibles en PDF vía data URI.
 *
 * dompdf no renderiza confiablemente el SVG de los gráficos de la vista
 * (stroke-dasharray sobre círculos), así que para el PDF la torta/dona se
 * dibuja con GD. Las barras del PDF se arman con HTML/CSS en la vista.
 */
class GraficoPng
{
    /** Misma paleta que los partials de gráficos de la vista. */
    public const PALETA = ['#77BF43', '#3B82F6', '#F59E0B', '#EF4444', '#8B5CF6', '#14B8A6', '#EC4899', '#6366F1', '#84CC16', '#F97316', '#06B6D4', '#A855F7'];

    /**
     * Dona a partir de [['label'=>, 'value'=>], ...].
     * Devuelve un data URI (base64) o cadena vacía si no hay datos / no hay GD.
     */
    public static function donut(array $data, int $size = 220): string
    {
        if (!function_exists('imagecreatetruecolor')) {
            return '';
        }

        $segmentos = array_values(array_filter($data, fn($d) => (float) ($d['value'] ?? 0) > 0));
        $total = array_sum(array_map(fn($d) => (float) $d['value'], $segmentos));
        if ($total <= 0) {
            return '';
        }

        // Se dibuja a 4x y se reduce: GD no antialiasa arcos, el downsample sí suaviza.
        $escala = 4;
        $lado   = $size * $escala;

        $grande = imagecreatetruecolor($lado, $lado);
        $blanco = imagecolorallocate($grande, 255, 255, 255);
        imagefilledrectangle($grande, 0, 0, $lado, $lado, $blanco);

        $cx = (int) ($lado / 2);
        $cy = (int) ($lado / 2);
        $d  = (int) ($lado * 0.96);

        // Ángulos acumulados redondeados: evita huecos entre porciones.
        $acumulado = 0.0;
        $inicioDeg = -90;
        $ultimo    = count($segmentos) - 1;

        foreach ($segmentos as $i => $seg) {
            $acumulado += (float) $seg['value'];
            $finDeg = $i === $ultimo ? 270 : (int) round(-90 + ($acumulado / $total) * 360);
            if ($finDeg <= $inicioDeg) {
                $finDeg = $inicioDeg + 1; // porciones minúsculas: al menos 1 grado
            }
            imagefilledarc($grande, $cx, $cy, $d, $d, $inicioDeg, $finDeg, self::color($grande, self::PALETA[$i % count(self::PALETA)]), IMG_ARC_PIE);
            $inicioDeg = $finDeg;
        }

        // Agujero central de la dona
        imagefilledellipse($grande, $cx, $cy, (int) ($d * 0.55), (int) ($d * 0.55), $blanco);

        $chico = imagecreatetruecolor($size, $size);
        imagefilledrectangle($chico, 0, 0, $size, $size, imagecolorallocate($chico, 255, 255, 255));
        imagecopyresampled($chico, $grande, 0, 0, 0, 0, $size, $size, $lado, $lado);
        imagedestroy($grande);

        ob_start();
        imagepng($chico);
        $png = ob_get_clean();
        imagedestroy($chico);

        return 'data:image/png;base64,' . base64_encode($png);
    }

    /** Asigna un color GD a partir de un hex "#RRGGBB". */
    private static function color($img, string $hex): int
    {
        $hex = ltrim($hex, '#');
        return imagecolorallocate(
            $img,
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2))
        );
    }
}
