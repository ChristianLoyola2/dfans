<?php
/**
 * Snippet #17: Hacer teléfono obligatorio en checkout
 * Alcance: global · prioridad 10
 * Exportado de Code Snippets el 14/11/2026. La copia activa está en la base de datos.
 */

add_filter( 'option_woocommerce_checkout_phone_field', function( $value ) {
    return 'required';
} );
