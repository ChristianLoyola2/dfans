<?php
/**
 * Snippet #13: Yape - Cambiar pedido a En espera
 * Alcance: global · prioridad 10
 * Exportado de Code Snippets el 10/11/2026. La copia activa está en la base de datos.
 */

/**
 * D'Fans - Yape: el pedido queda "En espera" desde el inicio
 *
 * Yape usa el gateway COD. WooCommerce lo dejaría en "Procesando"
 * (y enviaría el correo "Procesando tu pedido"); aquí queda
 * directamente "En espera" hasta verificar el pago, con su correo
 * "Pedido en espera". Cuando se verifica el pago, se pasa a
 * "Procesando" a mano desde el pedido.
 */
add_filter( 'woocommerce_cod_process_payment_order_status', function( $status, $order ) {
    return 'on-hold';
}, 10, 2 );

// Nota que WooCommerce añade al pedido al cambiar el estado.
add_filter( 'gettext', function( $translation, $text, $domain ) {
    if ( 'woocommerce' === $domain && 'Payment to be made upon delivery.' === $text ) {
        return 'Pago mediante Yape pendiente de verificación.';
    }
    return $translation;
}, 10, 3 );

