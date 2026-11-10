<?php
/**
 * Snippet #20: Checkout dinámico por tipo de entrega - D'Fans
 * Alcance: global · prioridad 10
 * Exportado de Code Snippets el 10/11/2026. La copia activa está en la base de datos.
 */

/**
 * D'Fans - Selector principal de entrega
 *
 * El cliente elige primero:
 * - Delivery en Lima
 * - Recojo en tienda - Gamarra
 * - Envío a provincia
 *
 * El selector se sincroniza con los métodos de envío
 * de WooCommerce y oculta la sección duplicada "Envío".
 */


/**
 * ============================================================
 * 1. Selector de entrega
 * ============================================================
 */
add_action( 'woocommerce_before_checkout_billing_form', function() {

    if ( ! is_checkout() ) {
        return;
    }

    ?>

    <div id="dfans-tipo-entrega" style="
        margin-bottom:25px;
        padding:20px;
        border:1px solid #ddd;
        border-radius:8px;
        background:#fafafa;
    ">

        <h3 style="
            margin-top:0;
            margin-bottom:8px;
        ">
            ¿Cómo quieres recibir tu pedido?
        </h3>

        <p style="
            margin:0 0 16px;
            color:#666;
        ">
            Selecciona una opción para continuar.
        </p>


        <!-- DELIVERY -->
        <label style="
            display:block;
            padding:14px;
            margin-bottom:10px;
            border:1px solid #ddd;
            border-radius:6px;
            background:#fff;
            cursor:pointer;
        ">

            <input
                type="radio"
                name="dfans_tipo_entrega"
                value="delivery"
            >

            🚚 <strong>Delivery en Lima</strong>

            <small style="
                display:block;
                margin-left:24px;
                margin-top:4px;
                color:#666;
            ">
                El costo depende del distrito.
            </small>

        </label>


        <!-- RECOJO -->
        <label style="
            display:block;
            padding:14px;
            margin-bottom:10px;
            border:1px solid #ddd;
            border-radius:6px;
            background:#fff;
            cursor:pointer;
        ">

            <input
                type="radio"
                name="dfans_tipo_entrega"
                value="recojo"
            >

            📍 <strong>Recojo en tienda – Gamarra</strong>

            <small style="
                display:block;
                margin-left:24px;
                margin-top:4px;
                color:#666;
            ">
                Recojo gratuito en nuestra tienda física.
            </small>

        </label>


        <!-- PROVINCIA -->
        <label style="
            display:block;
            padding:14px;
            border:1px solid #ddd;
            border-radius:6px;
            background:#fff;
            cursor:pointer;
        ">

            <input
                type="radio"
                name="dfans_tipo_entrega"
                value="provincia"
            >

            📦 <strong>Envío a provincia</strong>

            <small style="
                display:block;
                margin-left:24px;
                margin-top:4px;
                color:#666;
            ">
                Envío por agencia o Shalom.
            </small>

        </label>

    </div>

    <?php
} );


/**
 * ============================================================
 * 2. Guardar tipo de entrega en el pedido
 * ============================================================
 */
add_action(
    'woocommerce_checkout_create_order',
    function( $order, $data ) {

        if ( empty( $_POST['dfans_tipo_entrega'] ) ) {
            return;
        }

        $tipo = sanitize_text_field(
            wp_unslash( $_POST['dfans_tipo_entrega'] )
        );

        $nombres = array(
            'delivery'  => 'Delivery en Lima',
            'recojo'    => 'Recojo en tienda – Gamarra',
            'provincia' => 'Envío a provincia',
        );

        if ( isset( $nombres[ $tipo ] ) ) {

            $order->update_meta_data(
                '_dfans_tipo_entrega',
                $tipo
            );

            $order->update_meta_data(
                'Tipo de entrega',
                $nombres[ $tipo ]
            );
        }

    },
    10,
    2
);


/**
 * ============================================================
 * 3. Validación
 * ============================================================
 */
add_action(
    'woocommerce_checkout_process',
    function() {

        if ( empty( $_POST['dfans_tipo_entrega'] ) ) {

            wc_add_notice(
                'Selecciona cómo deseas recibir tu pedido.',
                'error'
            );

            return;
        }

        $tipo = sanitize_text_field(
            wp_unslash( $_POST['dfans_tipo_entrega'] )
        );


        /*
         * Delivery en Lima
         */
        if ( 'delivery' === $tipo ) {

            if ( empty( $_POST['billing_city'] ) ) {

                wc_add_notice(
                    'Para delivery debes indicar tu distrito.',
                    'error'
                );
            }

            if ( empty( $_POST['billing_address_1'] ) ) {

                wc_add_notice(
                    'Para delivery debes indicar tu dirección.',
                    'error'
                );
            }
        }


        /*
         * Envío a provincia
         */
        if ( 'provincia' === $tipo ) {

            if ( empty( $_POST['billing_state'] ) ) {

                wc_add_notice(
                    'Para un envío a provincia debes indicar tu departamento.',
                    'error'
                );
            }

            if ( empty( $_POST['billing_city'] ) ) {

                wc_add_notice(
                    'Para un envío a provincia debes indicar tu provincia o ciudad.',
                    'error'
                );
            }

            if ( empty( $_POST['billing_address_1'] ) ) {

                wc_add_notice(
                    'Para un envío a provincia debes indicar tu dirección.',
                    'error'
                );
            }
        }

    }
);


/**
 * ============================================================
 * 4. JavaScript
 * ============================================================
 */
add_action( 'wp_footer', function() {

    if ( ! is_checkout() ) {
        return;
    }

    ?>

    <script>

    jQuery(function($) {

        let sincronizando = false;


        /**
         * ------------------------------------------------------
         * Ocultar la sección duplicada de WooCommerce.
         * ------------------------------------------------------
         */
        function ocultarEnvioWooCommerce() {

            $('.woocommerce-shipping-totals.shipping').hide();

        }


        /**
         * ------------------------------------------------------
         * Mostrar/ocultar campos según el tipo elegido.
         * ------------------------------------------------------
         */
        function actualizarCampos() {

            const tipo = $(
                'input[name="dfans_tipo_entrega"]:checked'
            ).val();


            const country  = $('#billing_country_field');
            const address1 = $('#billing_address_1_field');
            const address2 = $('#billing_address_2_field');
            const city     = $('#billing_city_field');
            const state    = $('#billing_state_field');
            const postcode = $('#billing_postcode_field');


            /*
             * Ocultar campos dependientes.
             */
            country.hide();
            address1.hide();
            address2.hide();
            city.hide();
            state.hide();
            postcode.hide();


            /*
             * --------------------------------------------------
             * DELIVERY EN LIMA
             * --------------------------------------------------
             */
            if ( tipo === 'delivery' ) {

                address1.show();
                address2.show();
                city.show();


                $('#billing_city_field label')
                    .contents()
                    .filter(function() {
                        return this.nodeType === 3;
                    })
                    .first()
                    .replaceWith('Distrito ');


                $('#billing_address_1').attr(
                    'placeholder',
                    'Número y nombre de la calle'
                );


                $('#billing_address_2').attr(
                    'placeholder',
                    'Interior, departamento, piso o referencia (opcional)'
                );
            }


            /*
             * --------------------------------------------------
             * RECOJO EN GAMARRA
             * --------------------------------------------------
             */
            if ( tipo === 'recojo' ) {

                if ( $('#dfans-recojo-info').length === 0 ) {

                    $('#dfans-tipo-entrega').after(`
                        <div
                            id="dfans-recojo-info"
                            style="
                                margin-bottom:25px;
                                padding:18px;
                                border-left:4px solid #222;
                                background:#f7f7f7;
                                border-radius:4px;
                            "
                        >

                            📍 <strong>Recojo en tienda – Gamarra</strong>

                            <br><br>

                            El recojo es gratuito.

                            <br><br>

                            <strong>Dirección de la tienda:</strong>

                            <br>

                            Jr. Agustín Gamarra 1215,
                            La Victoria, Lima, Perú.

                            <br><br>

                            Después de confirmar tu pedido
                            te enviaremos las indicaciones
                            y el horario disponible.

                        </div>
                    `);
                }
            }


            /*
             * --------------------------------------------------
             * PROVINCIA
             * --------------------------------------------------
             */
            if ( tipo === 'provincia' ) {

                state.show();
                city.show();
                address1.show();
                address2.show();


                $('#billing_state_field label')
                    .contents()
                    .filter(function() {
                        return this.nodeType === 3;
                    })
                    .first()
                    .replaceWith('Departamento ');


                $('#billing_city_field label')
                    .contents()
                    .filter(function() {
                        return this.nodeType === 3;
                    })
                    .first()
                    .replaceWith('Provincia / Ciudad ');


                $('#billing_address_1').attr(
                    'placeholder',
                    'Dirección'
                );


                $('#billing_address_2').attr(
                    'placeholder',
                    'Interior o referencia (opcional)'
                );
            }


            /*
             * Quitar información de Gamarra si
             * cambia a otra modalidad.
             */
            if ( tipo !== 'recojo' ) {

                $('#dfans-recojo-info').remove();

            }


            /*
             * Mantener oculto el envío original.
             */
            ocultarEnvioWooCommerce();
        }


        /**
         * ------------------------------------------------------
         * Buscar y seleccionar método real de WooCommerce
         * ------------------------------------------------------
         */
        function sincronizarMetodoWooCommerce() {

            if ( sincronizando ) {
                return;
            }


            const tipo = $(
                'input[name="dfans_tipo_entrega"]:checked'
            ).val();


            if ( ! tipo ) {
                return;
            }


            let textoBuscado = '';


            if ( tipo === 'delivery' ) {
                textoBuscado = 'delivery en lima';
            }


            if ( tipo === 'recojo' ) {
                textoBuscado = 'recojo en gamarra';
            }


            if ( tipo === 'provincia' ) {
                textoBuscado = 'envío a provincia';
            }


            if ( ! textoBuscado ) {
                return;
            }


            /*
             * Buscar radios reales de WooCommerce.
             */
            const radios = $(
                'input[name^="shipping_method"]'
            );


            if ( ! radios.length ) {
                return;
            }


            radios.each(function() {

                const radio = $(this);

                const label = $(
                    'label[for="' + radio.attr('id') + '"]'
                );


                let texto = label.text().trim().toLowerCase();


                /*
                 * También revisamos el li por compatibilidad
                 * con diferentes estructuras de WooCommerce.
                 */
                if ( ! texto ) {

                    texto = radio
                        .closest('li')
                        .text()
                        .trim()
                        .toLowerCase();
                }


                if (
                    texto.indexOf(textoBuscado) !== -1 &&
                    ! radio.is(':checked')
                ) {

                    sincronizando = true;


                    radio.prop('checked', true);

                    radio.trigger('change');


                    setTimeout(function() {

                        sincronizando = false;

                    }, 800);

                }

            });


            /*
             * Ocultar nuevamente después de cualquier
             * actualización visual.
             */
            ocultarEnvioWooCommerce();
        }


        /**
         * ------------------------------------------------------
         * Cambio de tipo de entrega
         * ------------------------------------------------------
         */
        $(document.body).on(
            'change',
            'input[name="dfans_tipo_entrega"]',
            function() {

                actualizarCampos();

                sincronizarMetodoWooCommerce();

            }
        );


        /**
         * ------------------------------------------------------
         * Después de que WooCommerce actualice el checkout.
         *
         * IMPORTANTE:
         * Aquí NO hacemos update_checkout().
         * ------------------------------------------------------
         */
        $(document.body).on(
            'updated_checkout',
            function() {

                actualizarCampos();

                setTimeout(
                    sincronizarMetodoWooCommerce,
                    150
                );

                setTimeout(
                    ocultarEnvioWooCommerce,
                    250
                );

            }
        );


        /**
         * ------------------------------------------------------
         * Observar cambios en el resumen del pedido.
         * ------------------------------------------------------
         */
        const resumen = document.querySelector(
            '#order_review'
        );


        if ( resumen ) {

            const observer = new MutationObserver(
                function() {

                    ocultarEnvioWooCommerce();

                }
            );


            observer.observe(
                resumen,
                {
                    childList: true,
                    subtree: true
                }
            );
        }


        /**
         * ------------------------------------------------------
         * Estado inicial
         * ------------------------------------------------------
         */
        actualizarCampos();

        ocultarEnvioWooCommerce();

        setTimeout(
            sincronizarMetodoWooCommerce,
            500
        );

    });

    </script>

    <?php
} );


/**
 * ============================================================
 * 5. Campos obligatorios según el tipo de entrega
 * ============================================================
 *
 * WooCommerce exige para Perú dirección, ciudad, departamento
 * y código postal, pero este snippet los oculta según la
 * modalidad (el código postal siempre). Sin esto ningún
 * pedido pasa la validación. La dirección, el distrito y el
 * departamento se siguen exigiendo en la validación (punto 3)
 * cuando la modalidad los necesita.
 */
add_filter( 'woocommerce_checkout_fields', function( $fields ) {

    foreach ( array( 'billing_address_1', 'billing_city', 'billing_state', 'billing_postcode' ) as $campo ) {

        if ( isset( $fields['billing'][ $campo ] ) ) {
            $fields['billing'][ $campo ]['required'] = false;
        }
    }

    return $fields;

}, 10000 );


/**
 * ============================================================
 * 6. Departamento según el tipo de entrega
 * ============================================================
 *
 * La zona de envío depende del departamento. Delivery y
 * recojo solo existen en la zona Lima, así que el
 * departamento (oculto) se fija en Lima. En provincia, si
 * seguía en Lima, se vacía para que el cliente elija el suyo.
 */
add_action( 'wp_footer', function() {

    if ( ! is_checkout() ) {
        return;
    }

    ?>
    <script>
    jQuery(function($) {

        $(document.body).on('change', 'input[name="dfans_tipo_entrega"]', function() {

            const tipo   = $(this).val();
            const estado = $('#billing_state');
            const actual = estado.val();
            let nuevo    = actual;

            if ( tipo === 'delivery' || tipo === 'recojo' ) {
                nuevo = 'LIM';
            }

            if ( tipo === 'provincia' && actual === 'LIM' ) {
                nuevo = '';
            }

            if ( nuevo !== actual ) {
                estado.val(nuevo).trigger('change');
                $(document.body).trigger('update_checkout');
            }
        });

    });
    </script>
    <?php
} );

