<?php
/**
 * Snippet #8: QR Yape en método de pago
 * Alcance: global · prioridad 10
 * Exportado de Code Snippets el 03/11/2026. La copia activa está en la base de datos.
 */

add_filter('woocommerce_gateway_description', function($description, $gateway_id) {

    // Yape está configurado usando el método "Cash on Delivery"
    if ($gateway_id !== 'cod') {
        return $description;
    }

    $qr_url = wp_upload_dir()['baseurl'] . '/2026/08/yape-cap.jpg';

    $yape_info = '
        <div class="dfans-yape-payment" style="
            margin-top:15px;
            padding:15px;
            text-align:center;
            border:1px solid #ddd;
            border-radius:10px;
            background:#fafafa;
        ">

            <p style="margin:0 0 10px 0;">
                <strong>Escanea el código QR para pagar con Yape</strong>
            </p>

            <img
                src="' . esc_url($qr_url) . '"
                alt="QR de Yape"
                style="
                    width:240px;
                    max-width:100%;
                    height:auto;
                    display:block;
                    margin:10px auto;
                "
            >

            <p style="
                margin:12px 0 0 0;
                font-size:13px;
                color:#555;
            ">
                Después de realizar el pago, conserva tu comprobante.
                Verificaremos el pago antes de procesar tu pedido.
            </p>

        </div>
    ';

    return $description . $yape_info;

}, 10, 2);
