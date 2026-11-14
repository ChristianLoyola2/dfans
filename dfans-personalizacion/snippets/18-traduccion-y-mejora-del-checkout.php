<?php
/**
 * Snippet #18: Traducción y mejora del checkout - D'Fans
 * Alcance: global · prioridad 10
 * Exportado de Code Snippets el 14/11/2026. La copia activa está en la base de datos.
 */

/**
 * D'Fans - Checkout completamente en español
 * WooCommerce clásico
 */

add_filter( 'woocommerce_checkout_fields', function( $fields ) {

    /*
     * DATOS DE FACTURACIÓN
     */

    // Nombre
    if ( isset( $fields['billing']['billing_first_name'] ) ) {
        $fields['billing']['billing_first_name']['label'] = 'Nombre';
        $fields['billing']['billing_first_name']['placeholder'] = 'Tu nombre';
    }

    // Apellido
    if ( isset( $fields['billing']['billing_last_name'] ) ) {
        $fields['billing']['billing_last_name']['label'] = 'Apellido';
        $fields['billing']['billing_last_name']['placeholder'] = 'Tu apellido';
    }

    // País
    if ( isset( $fields['billing']['billing_country'] ) ) {
        $fields['billing']['billing_country']['label'] = 'País / Región';
    }

    // Dirección principal
    if ( isset( $fields['billing']['billing_address_1'] ) ) {
        $fields['billing']['billing_address_1']['label'] = 'Dirección';
        $fields['billing']['billing_address_1']['placeholder'] = 'Número y nombre de la calle';
    }

    // Dirección adicional
    if ( isset( $fields['billing']['billing_address_2'] ) ) {
        $fields['billing']['billing_address_2']['label'] = 'Interior / departamento / referencia';
        $fields['billing']['billing_address_2']['placeholder'] = 'Ej.: Dpto. 302, interior 5, piso 3';
    }

    // Ciudad
    if ( isset( $fields['billing']['billing_city'] ) ) {
        $fields['billing']['billing_city']['label'] = 'Ciudad';
        $fields['billing']['billing_city']['placeholder'] = 'Tu ciudad';
    }

    // Departamento / Región
    if ( isset( $fields['billing']['billing_state'] ) ) {
        $fields['billing']['billing_state']['label'] = 'Departamento / Región';
    }

    // Código postal
    if ( isset( $fields['billing']['billing_postcode'] ) ) {
        $fields['billing']['billing_postcode']['label'] = 'Código postal';
        $fields['billing']['billing_postcode']['placeholder'] = 'Código postal';
    }

    // Teléfono
    if ( isset( $fields['billing']['billing_phone'] ) ) {
        $fields['billing']['billing_phone']['label'] = 'Teléfono';
        $fields['billing']['billing_phone']['placeholder'] = 'Número de celular';
        $fields['billing']['billing_phone']['required'] = true;
    }

    // Correo
    if ( isset( $fields['billing']['billing_email'] ) ) {
        $fields['billing']['billing_email']['label'] = 'Correo electrónico';
        $fields['billing']['billing_email']['placeholder'] = 'tu-correo@ejemplo.com';
    }

    /*
     * NOTAS DEL PEDIDO
     */

    if ( isset( $fields['order']['order_comments'] ) ) {
        $fields['order']['order_comments']['label'] = 'Notas del pedido';
        $fields['order']['order_comments']['placeholder'] =
            'Indica alguna referencia o detalle especial para tu pedido (opcional)';
    }

    return $fields;

}, 9999 );


/*
 * TRADUCCIONES DE TEXTOS GENERALES DE WOOCOMMERCE
 *
 * IMPORTANTE:
 * Aceptamos 3 argumentos porque gettext los proporciona.
 */

add_filter( 'gettext', function( $translation, $text, $domain ) {

    if ( 'woocommerce' !== $domain ) {
        return $translation;
    }

    $traducciones = array(

        // Checkout
        'Billing details' => 'Datos de facturación',
        'Your order' => 'Tu pedido',
        'Product' => 'Producto',
        'Subtotal' => 'Subtotal',
        'Total' => 'Total',
        'Payment' => 'Pago',
        'Shipment' => 'Envío',
        'Shipping' => 'Envío',

        // Campos
        'First name' => 'Nombre',
        'Last name' => 'Apellido',
        'Country / Region' => 'País / Región',
        'Street address' => 'Dirección',
        'Town / City' => 'Ciudad',
        'State / County' => 'Departamento / Región',
        'Postcode / ZIP' => 'Código postal',
        'Phone' => 'Teléfono',
        'Email address' => 'Correo electrónico',

        // Dirección
        'House number and street name' => 'Número y nombre de la calle',
        'Apartment, suite, unit, etc. (optional)' => 'Interior / departamento / referencia (opcional)',

        // Notas
        'Order notes' => 'Notas del pedido',

        // Cupones
        'Have a coupon?' => '¿Tienes un cupón?',
        'Click here to enter your code' => 'Haz clic aquí para ingresar tu código',

        // Botón
        'Place order' => 'Realizar pedido',

        // Opcional
        '(optional)' => '(opcional)',
        'optional' => 'opcional',

        // Datos adicionales
        'Additional information' => 'Información adicional',
    );

    if ( isset( $traducciones[ $text ] ) ) {
        return $traducciones[ $text ];
    }

    return $translation;

}, 9999, 3 );


/*
 * Etiqueta de la línea 2 de dirección en español peruano.
 * La traducción de WooCommerce dice "Colonia, Apartamento...";
 * se corrige en los campos por defecto y en los datos por país
 * (estos últimos los usa el JavaScript del checkout).
 */
add_filter( 'woocommerce_default_address_fields', function( $fields ) {
    if ( isset( $fields['address_2'] ) ) {
        $fields['address_2']['label'] = 'Departamento, interior o referencia';
    }
    return $fields;
}, 9999 );

add_filter( 'woocommerce_get_country_locale', function( $locale ) {
    if ( isset( $locale['PE']['address_2'] ) ) {
        $locale['PE']['address_2']['label'] = 'Departamento, interior o referencia';
    }
    return $locale;
}, 9999 );

