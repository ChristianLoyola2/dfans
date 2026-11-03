<?php
/**
 * Snippet #25: D'FANS - Modelo y tallas dinámicas
 * Alcance: global · prioridad 10
 * Exportado de Code Snippets el 03/11/2026. La copia activa está en la base de datos.
 */

add_action('wp_footer', function () {

    if (!is_product()) {
        return;
    }
    ?>
    <script>
    jQuery(function ($) {

        /*
         * D'FANS - Modelo y tallas dinámicas
         *
         * - Los 4 modelos (Hombre, Mujer, Niño, Niña) siempre se pueden elegir.
         * - Al cambiar de modelo se reinicia la talla.
         * - Solo se muestran las tallas del modelo: adultos S-XXL, niños 10-14.
         * - Precio, stock e imágenes los actualiza WooCommerce al encontrar la variación.
         */

        const TALLAS_ADULTO = ['s', 'm', 'l', 'xl', 'xxl'];
        const TALLAS_NINO   = ['10', '12', '14'];

        // Los slugs reales son "nino" y "nina"; se quitan tildes por si acaso.
        function normalizar(valor) {
            return (valor || '')
                .toString()
                .trim()
                .toLowerCase()
                .normalize('NFD')
                .replace(/[̀-ͯ]/g, '');
        }

        function tallasDelModelo(modelo) {
            modelo = normalizar(modelo);

            if (modelo === 'hombre' || modelo === 'mujer') {
                return TALLAS_ADULTO;
            }

            if (modelo === 'nino' || modelo === 'nina') {
                return TALLAS_NINO;
            }

            return null; // Sin modelo elegido: no se filtra.
        }

        $('form.variations_form').each(function () {

            const $form = $(this);

            const $modelo = $form.find(
                'select[name="attribute_pa_modelo"], select[name="attribute_modelo"]'
            ).first();

            const $talla = $form.find(
                'select[name="attribute_pa_talla"], select[name="attribute_talla"]'
            ).first();

            if (!$modelo.length || !$talla.length) {
                return;
            }

            // Copia de las opciones originales del modelo.
            const modeloOptions = $modelo.children('option').clone();

            /*
             * WooCommerce quita los modelos que no combinan con la talla elegida.
             * Los restauramos para poder cambiar de modelo en cualquier momento.
             */
            function restaurarModelos() {
                const valorActual = $modelo.val();

                if ($modelo.children('option').length === modeloOptions.length) {
                    return;
                }

                $modelo.empty().append(modeloOptions.clone());
                $modelo.val(valorActual);
            }

            /*
             * Oculta las tallas que no corresponden al modelo.
             * No vuelve a habilitar opciones: eso lo decide WooCommerce (stock, etc.).
             */
            function filtrarTallas() {
                const permitidas = tallasDelModelo($modelo.val());

                $talla.find('option').each(function () {
                    const $op   = $(this);
                    const valor = normalizar($op.val());

                    if (!valor || !permitidas) {
                        return;
                    }

                    if (!permitidas.includes(valor)) {
                        $op.prop('disabled', true).prop('hidden', true).hide();
                    }
                });

                // Si la talla elegida ya no es válida para el modelo, se limpia.
                if (permitidas && $talla.val() && !permitidas.includes(normalizar($talla.val()))) {
                    $talla.val('');
                }
            }

            /*
             * Al cambiar de modelo, se vacía la talla ANTES de que WooCommerce
             * procese el cambio (este manejador se ejecuta antes que el del formulario).
             */
            $modelo.on('change', function () {
                $talla.val('');
            });

            // WooCommerce termina de recalcular las opciones disponibles.
            $form.on('woocommerce_update_variation_values', function () {
                restaurarModelos();
                filtrarTallas();
            });

            // Estado inicial.
            restaurarModelos();
            filtrarTallas();
        });

    });
    </script>
    <?php
});


/*
 * D'FANS - Modelo y talla como botones
 *
 * Los selectores originales siguen existiendo (ocultos) y son los que
 * usa WooCommerce; los botones solo los manejan y reflejan su estado:
 * opción elegida, tallas ocultas por el modelo y opciones sin stock.
 */
add_action('wp_footer', function () {

    if (!is_product()) {
        return;
    }
    ?>
    <script>
    jQuery(function ($) {

        $('form.variations_form').each(function () {

            const $form = $(this);

            $form.find('select[name^="attribute_"]').each(function () {

                const $select = $(this);
                const etiqueta = $form.find('label[for="' + $select.attr('id') + '"]').text().trim();
                const $grupo = $('<div class="dfans-opciones" role="radiogroup"></div>')
                    .attr('aria-label', etiqueta || 'Opciones');

                $select.addClass('dfans-select-oculto').after($grupo);

                function pintar() {
                    $grupo.empty();

                    $select.find('option').each(function () {
                        const $op   = $(this);
                        const valor = $op.val();

                        // Opción vacía y tallas que no son del modelo elegido.
                        if (!valor || $op.prop('hidden')) {
                            return;
                        }

                        const activa = $select.val() === valor;

                        $('<button type="button" class="dfans-opcion" role="radio"></button>')
                            .text($op.text())
                            .attr('data-valor', valor)
                            .attr('aria-checked', activa ? 'true' : 'false')
                            .toggleClass('is-activa', activa)
                            .prop('disabled', $op.prop('disabled'))
                            .appendTo($grupo);
                    });
                }

                $grupo.on('click', '.dfans-opcion', function () {
                    const valor = $(this).data('valor').toString();

                    if ($select.val() !== valor) {
                        $select.val(valor).trigger('change');
                    }
                });

                $select.data('dfansPintar', pintar).on('change', pintar);
                pintar();
            });

            // WooCommerce recalcula las opciones o se pulsa "Limpiar".
            $form.on('woocommerce_update_variation_values reset_data', function () {
                $form.find('select.dfans-select-oculto').each(function () {
                    const pintar = $(this).data('dfansPintar');
                    if (pintar) {
                        pintar();
                    }
                });
            });
        });

    });
    </script>
    <?php
});


/*
 * D'FANS - Tallas según la sección
 *
 * Los polos Match son unisex: tallas de adulto (S-XXL) y de niños (10-14).
 * Si el producto se abrió desde Adultos o Niños (?seccion=adultos|ninos),
 * solo se muestran las tallas de esa sección. Sin sección se ven todas,
 * agrupadas en "Adultos" y "Niños" (por CSS).
 * Se ejecuta después de los botones y los vuelve a pintar.
 */
add_action('wp_footer', function () {

    if (!is_product()) {
        return;
    }
    ?>
    <script>
    jQuery(function ($) {

        const TALLAS = {
            adultos: ['xs', 's', 'm', 'l', 'xl', 'xxl'],
            ninos:   ['10', '12', '14']
        };
        const permitidas = TALLAS[new URLSearchParams(window.location.search).get('seccion')];

        if (!permitidas) {
            return;
        }

        $('form.variations_form').each(function () {

            const $form  = $(this);
            const $talla = $form.find('select[name="attribute_pa_talla"]');

            if (!$talla.length) {
                return;
            }

            function filtrar() {
                $talla.find('option').each(function () {
                    const valor = $(this).val();
                    if (valor && !permitidas.includes(valor)) {
                        $(this).prop('disabled', true).prop('hidden', true).hide();
                    }
                });

                if ($talla.val() && !permitidas.includes($talla.val())) {
                    $talla.val('');
                }

                const pintar = $talla.data('dfansPintar');
                if (pintar) {
                    pintar();
                }
            }

            // WooCommerce vuelve a armar las opciones al recalcular.
            $form.on('woocommerce_update_variation_values', filtrar);
            filtrar();
        });

    });
    </script>
    <?php
});

