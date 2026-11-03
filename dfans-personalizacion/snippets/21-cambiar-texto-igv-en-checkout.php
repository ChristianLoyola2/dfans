<?php
/**
 * Snippet #21: Cambiar texto IGV en checkout - D'Fans
 * Alcance: global · prioridad 10
 * Exportado de Code Snippets el 03/11/2026. La copia activa está en la base de datos.
 */

add_action( 'wp_footer', function() {

    if ( ! is_cart() && ! is_checkout() ) {
        return;
    }
    ?>

    <script>
    jQuery(function($) {

        function traducirIGV() {

            $('.includes_tax').each(function() {

                $(this).text('(incluye IGV)');

            });

        }

        traducirIGV();

        $(document.body).on(
            'updated_checkout updated_cart_totals',
            function() {
                traducirIGV();
            }
        );

    });
    </script>

    <?php
} );
