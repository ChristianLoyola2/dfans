<?php
/**
 * Snippet #9: QR Yape en confirmación de pedido
 * Alcance: global · prioridad 10
 * Exportado de Code Snippets el 14/11/2026. La copia activa está en la base de datos.
 */

add_action('woocommerce_thankyou', 'dfans_yape_qr_confirmacion', 20);

function dfans_yape_qr_confirmacion($order_id) {

    if (!$order_id) {
        return;
    }

    $order = wc_get_order($order_id);

    if (!$order) {
        return;
    }

    // El método "Yape" está configurado sobre Cash on Delivery (COD)
    if ($order->get_payment_method() !== 'cod') {
        return;
    }

    $qr_url = home_url('/wp-content/uploads/2026/08/yape-cap.jpg');

    echo '
    <div class="dfans-yape-confirmacion" style="
        margin: 30px 0;
        padding: 30px;
        background: #f5eff8;
        border-radius: 12px;
        text-align: center;
    ">

        <h2 style="margin-bottom: 10px;">
            Paga con Yape
        </h2>

        <p style="font-size: 18px;">
            Escanea el código QR para realizar tu pago.
        </p>

        <img 
            src="' . esc_url($qr_url) . '" 
            alt="QR de pago Yape"
            style="
                display: block;
                width: 280px;
                max-width: 100%;
                height: auto;
                margin: 20px auto;
                border-radius: 8px;
            "
        >

        <h3 style="margin-top: 20px;">
            Titular: Christian Armando Loyola Lujan
        </h3>

        <p>
            Después de realizar el pago, conserva tu comprobante.
            Verificaremos el pago antes de procesar tu pedido.
        </p>

    </div>';
}
