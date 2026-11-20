<?php

namespace App\Support;

/**
 * El administrador de la intranet que hace un pedido a `/api/intranet/v1` (contrato en
 * intranet/docs/integracion.md §5). No es un usuario del corralón y no se crea ninguno
 * para representarlo: llega en cada pedido que escribe (`actor: {dni, nombre}`) y vive
 * lo que dura ese pedido.
 *
 * Mientras corre `registrando()`, todo lo que entra a la Actividad (`RegistraActividad`)
 * queda con causer nulo y `properties` `origen = intranet`, `actor_dni` y
 * `actor_nombre`. La pantalla de Actividad lo muestra como "Intranet · Nombre (DNI n)".
 */
final class ActorIntranet
{
    private static ?self $actual = null;

    public function __construct(
        public readonly int $dni,
        public readonly string $nombre,
    ) {}

    /** El actor del pedido en curso, o null si lo que se guarda no viene de la intranet. */
    public static function actual(): ?self
    {
        return self::$actual;
    }

    /**
     * Corre `$accion` con este actor como autor de la Actividad.
     *
     * @template T
     *
     * @param  callable(): T  $accion
     * @return T
     */
    public function registrando(callable $accion): mixed
    {
        $anterior = self::$actual;
        self::$actual = $this;

        try {
            return $accion();
        } finally {
            self::$actual = $anterior;
        }
    }

    /** @return array{origen: string, actor_dni: int, actor_nombre: string} */
    public function propiedades(): array
    {
        return ['origen' => 'intranet', 'actor_dni' => $this->dni, 'actor_nombre' => $this->nombre];
    }
}
