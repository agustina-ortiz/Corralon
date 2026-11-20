<?php

/*
 * Integración con la intranet municipal (contrato en
 * C:\xampp\htdocs\intranet\docs\integracion.md §5; guía de la API en docs/api-intranet.md).
 *
 * - `url`: base de la intranet, sin barra final. El receptor SSO canjea el ticket en
 *   {url}/api/sso/v1/canjes.
 * - `clave_servicio`: la clave de servicio del corralón (64 caracteres o más, al azar).
 *   La usan los dos canales: el canje del ticket (la manda el corralón) y la API
 *   /api/intranet/v1 (la manda la intranet). Vacía = la API rechaza todo y el SSO no
 *   valida a nadie.
 * - `ips`: IPs desde las que la intranet llama a la API, separadas por coma en
 *   INTRANET_IPS. **Vacía = no se controla IP** (solo local y tests).
 *
 * Después de cambiar el .env en el servidor: `php artisan optimize:clear`
 * (y `config:cache` si el servidor cachea la config).
 */
return [
    'url' => rtrim((string) env('INTRANET_URL', ''), '/'),
    'clave_servicio' => (string) env('INTRANET_CLAVE_SERVICIO', ''),
    'ips' => array_values(array_filter(array_map('trim', explode(',', (string) env('INTRANET_IPS', ''))))),
];
