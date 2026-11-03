<?php
/**
 * Snippet #16: Notas dinámicas de envío - D'Fans
 * Alcance: global · prioridad 10
 * Exportado de Code Snippets el 03/11/2026. La copia activa está en la base de datos.
 */

/**
 * D'Fans - Notas dinámicas para métodos de envío
 * Muestra una nota debajo de la opción de envío seleccionada.
 */

add_action( 'wp_footer', function() {

    // Solo ejecutar en Checkout.
    if ( ! is_checkout() ) {
        return;
    }
    ?>
    <script>
    jQuery(function($) {

        function mostrarNotaEnvio() {

            // Eliminar nota anterior para evitar duplicados.
            $('.dfans-nota-envio').remove();

            // Buscar el método de envío actualmente seleccionado.
            const $seleccionado = $('input[name^="shipping_method"]:checked');

            if (!$seleccionado.length) {
                return;
            }

            // Obtener la etiqueta/texto visible del método.
            const textoMetodo = $seleccionado
                .closest('li')
                .text()
                .trim()
                .toLowerCase();

            let mensaje = '';

            // DELIVERY EN LIMA
            if (textoMetodo.includes('delivery en lima')) {

                mensaje = `
                    <div class="dfans-nota-envio" style="
                        margin-top: 10px;
                        padding: 12px 15px;
                        background: #f7f7f7;
                        border-left: 4px solid #222;
                        border-radius: 4px;
                        font-size: 14px;
                        line-height: 1.55;
                        color: #333;
                    ">
                        🚚 <strong>Delivery en Lima:</strong><br>
                        El costo depende del distrito.
                        Nos comunicaremos contigo después de realizar el pedido
                        para coordinar el monto y la entrega.
                        El costo del delivery es asumido por el cliente.
                    </div>
                `;

            // RECOJO EN GAMARRA
            } else if (textoMetodo.includes('recojo en gamarra')) {

                mensaje = `
                    <div class="dfans-nota-envio" style="
                        margin-top: 10px;
                        padding: 12px 15px;
                        background: #f7f7f7;
                        border-left: 4px solid #222;
                        border-radius: 4px;
                        font-size: 14px;
                        line-height: 1.55;
                        color: #333;
                    ">
                        📍 <strong>Recojo en Gamarra:</strong><br>
                        El recojo es gratuito.
                        Después de realizar tu pedido,
                        te enviaremos las indicaciones y el horario disponible.
                    </div>
                `;

            // ENVÍO A PROVINCIA
            } else if (textoMetodo.includes('envío a provincia') ||
                       textoMetodo.includes('envio a provincia')) {

                mensaje = `
                    <div class="dfans-nota-envio" style="
                        margin-top: 10px;
                        padding: 12px 15px;
                        background: #f7f7f7;
                        border-left: 4px solid #222;
                        border-radius: 4px;
                        font-size: 14px;
                        line-height: 1.55;
                        color: #333;
                    ">
                        📦 <strong>Envío a provincia:</strong><br>
                        Realizamos envíos mediante agencia de transporte o Shalom.
                        El costo del transporte es asumido por el cliente
                        y se paga directamente en la agencia.
                    </div>
                `;
            }

            // Insertar la nota debajo de la opción seleccionada.
            if (mensaje !== '') {
                $seleccionado.closest('li').after(mensaje);
            }
        }

        // Primera carga.
        mostrarNotaEnvio();

        // Cuando WooCommerce actualiza el checkout.
        $(document.body).on('updated_checkout', function() {
            mostrarNotaEnvio();
        });

        // Cuando el cliente cambia de método.
        $(document.body).on(
            'change',
            'input[name^="shipping_method"]',
            function() {
                mostrarNotaEnvio();
            }
        );

    });

    </script>
    <?php
});
