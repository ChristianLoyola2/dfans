<?php
/**
 * Snippet #19: Textos finales checkout en español - D'Fans
 * Alcance: global · prioridad 10
 * Exportado de Code Snippets el 14/11/2026. La copia activa está en la base de datos.
 */

add_action( 'wp_footer', function() {

    // Ejecutar únicamente en el checkout.
    if ( ! is_checkout() ) {
        return;
    }

    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function () {

        /*
         * ============================================================
         * D'Fans - Checkout completamente en español
         * ============================================================
         */

        function traducirCheckout() {

            const reemplazos = {
                'Ship to a different address?':
                    '¿Enviar a una dirección diferente?',

                'Product':
                    'Producto',

                'Subtotal':
                    'Subtotal',

                'Your order':
                    'Tu pedido',

                'Shipment':
                    'Envío',

                'Shipping':
                    'Envío',

                'Payment':
                    'Pago',

                'Billing details':
                    'Datos de facturación',

                'First name':
                    'Nombre',

                'Last name':
                    'Apellido',

                'Country / Region':
                    'País / Región',

                'Street address':
                    'Dirección',

                'Town / City':
                    'Ciudad',

                'State / County':
                    'Departamento / Región',

                'Postcode / ZIP':
                    'Código postal',

                'Phone':
                    'Teléfono',

                'Email address':
                    'Correo electrónico',

                'Order notes':
                    'Notas del pedido',

                'Have a coupon?':
                    '¿Tienes un cupón?',

                'Click here to enter your code':
                    'Haz clic aquí para ingresar tu código',

                'Place order':
                    'Realizar pedido',

                'Additional information':
                    'Información adicional',

                'House number and street name':
                    'Número y nombre de la calle',

                'Apartment, suite, unit, etc. (optional)':
                    'Interior / departamento / referencia (opcional)'
            };


            /*
             * Recorremos los textos visibles de la página.
             */

            const walker = document.createTreeWalker(
                document.body,
                NodeFilter.SHOW_TEXT
            );

            const nodos = [];

            while (walker.nextNode()) {
                nodos.push(walker.currentNode);
            }


            nodos.forEach(function(node) {

                let texto = node.nodeValue.trim();

                if ( ! texto ) {
                    return;
                }

                Object.keys(reemplazos).forEach(function(original) {

                    if ( texto === original ) {

                        node.nodeValue =
                            node.nodeValue.replace(
                                original,
                                reemplazos[original]
                            );

                    }

                });

            });

        }


        /*
         * ============================================================
         * Traducir texto de privacidad
         * ============================================================
         */

        function traducirPrivacidad() {

            const privacidad = document.querySelector(
                '.woocommerce-privacy-policy-text'
            );

            if ( ! privacidad ) {
                return;
            }


            /*
             * Conservamos el enlace original de WooCommerce.
             */

            const enlace = privacidad.querySelector('a');


            if ( enlace ) {

                const href = enlace.getAttribute('href');


                privacidad.innerHTML = `
                    Tus datos personales serán utilizados para procesar
                    tu pedido, brindarte soporte durante tu experiencia
                    en este sitio web y para otros fines descritos en nuestra
                    <a href="${href}" target="_blank">
                        política de privacidad
                    </a>.
                `;

            } else {

                privacidad.textContent =
                    'Tus datos personales serán utilizados para procesar tu pedido, brindarte soporte durante tu experiencia en este sitio web y para otros fines descritos en nuestra política de privacidad.';

            }

        }


        /*
         * ============================================================
         * Ejecutar al cargar
         * ============================================================
         */

        traducirCheckout();
        traducirPrivacidad();


        /*
         * ============================================================
         * WooCommerce actualiza el checkout mediante AJAX.
         * ============================================================
         */

        jQuery(document.body).on(
            'updated_checkout',
            function() {

                traducirCheckout();
                traducirPrivacidad();

            }
        );


        /*
         * ============================================================
         * Ejecutar nuevamente después de pequeñas demoras.
         * ============================================================
         */

        setTimeout(function() {

            traducirCheckout();
            traducirPrivacidad();

        }, 500);


        setTimeout(function() {

            traducirCheckout();
            traducirPrivacidad();

        }, 1500);


        setTimeout(function() {

            traducirCheckout();
            traducirPrivacidad();

        }, 3000);

    });
    </script>
    <?php

});
