<?php
/**
 * Snippet #5: Total dinámico por cantidad - D'Fans
 * Alcance: global · prioridad 10
 * Exportado de Code Snippets el 03/11/2026. La copia activa está en la base de datos.
 */

add_action('wp_footer', function () {
    if (!is_product()) {
        return;
    }
    ?>
    <script>
    jQuery(function($) {

        let precioUnitario = 0;

        function mostrarTotal() {
            const cantidadInput = $('.single_variation_wrap input.qty');

            if (!cantidadInput.length || precioUnitario <= 0) {
                return;
            }

            let cantidad = parseFloat(cantidadInput.val());

            if (isNaN(cantidad) || cantidad < 1) {
                cantidad = 1;
            }

            const total = precioUnitario * cantidad;

            $('#dfans-total').html(
                'Total: <strong>S/ ' + total.toFixed(2) + '</strong>'
            );
        }

        function crearTotal() {

            if ($('#dfans-total').length) {
                return;
            }

            const boton = $('.single_variation_wrap .single_add_to_cart_button');

            if (!boton.length) {
                return;
            }

            $('<div id="dfans-total" style="margin-top:15px;font-size:20px;">' +
              'Total: <strong>S/ 0.00</strong>' +
              '</div>'
            ).insertAfter(boton);
        }

        /*
         * WooCommerce encuentra una variación
         */
        $('form.variations_form').on('found_variation', function(event, variation) {

            if (variation && variation.display_price !== undefined) {

                precioUnitario = parseFloat(variation.display_price);

                crearTotal();
                mostrarTotal();
            }
        });

        /*
         * Cambiar cantidad
         */
        $(document).on(
            'input change',
            '.single_variation_wrap input.qty',
            function() {
                mostrarTotal();
            }
        );

        /*
         * Si se limpian las opciones
         */
        $('form.variations_form').on('reset_data', function() {

            precioUnitario = 0;

            $('#dfans-total').remove();
        });

    });
    </script>
    <?php
});
