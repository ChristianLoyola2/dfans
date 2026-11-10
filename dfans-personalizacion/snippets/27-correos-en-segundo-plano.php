<?php
/**
 * Snippet #27: D'FANS - Correos en segundo plano
 * Alcance: global · prioridad 10
 * Exportado de Code Snippets el 10/11/2026. La copia activa está en la base de datos.
 */

/**
 * D'Fans - Correos de WooCommerce en segundo plano
 *
 * Los correos salen por Gmail (WP Mail SMTP) y cada envío tarda
 * varios segundos. Así se encolan en Action Scheduler y se envían
 * después de responder al cliente: la confirmación del pedido
 * aparece al instante.
 */
add_filter( 'woocommerce_defer_transactional_emails', '__return_true' );

