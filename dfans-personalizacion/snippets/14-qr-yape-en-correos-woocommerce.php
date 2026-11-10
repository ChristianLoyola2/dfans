<?php
/**
 * Snippet #14: QR Yape en correos WooCommerce
 * Alcance: global · prioridad 10
 * Exportado de Code Snippets el 10/11/2026. La copia activa está en la base de datos.
 */

/**
 * D'Fans - QR de Yape centrado en correos WooCommerce
 */

add_action( 'woocommerce_email_after_order_table', 'dfans_qr_yape_email_centrado', 20, 4 );

function dfans_qr_yape_email_centrado( $order, $sent_to_admin, $plain_text, $email ) {

    // No mostrar en correos enviados al administrador.
    if ( $sent_to_admin ) {
        return;
    }

    if ( ! $order instanceof WC_Order ) {
        return;
    }

    // Yape actualmente utiliza el gateway COD.
    if ( $order->get_payment_method() !== 'cod' ) {
        return;
    }

    $qr_url = wp_upload_dir()['baseurl'] . '/2026/08/yape-cap.jpg';

    // Versión texto plano.
    if ( $plain_text ) {

        echo "\n";
        echo "PAGA TU PEDIDO CON YAPE\n";
        echo "Escanea el código QR para realizar el pago.\n";
        echo "Después de realizar el pago, conserva tu comprobante.\n";
        echo "Verificaremos el pago antes de procesar tu pedido.\n";
        echo "\n";

        return;
    }

    // Bloque HTML centrado y de ancho completo.
    echo '<div style="
        width:100%;
        box-sizing:border-box;
        margin:25px 0;
        padding:0;
        text-align:center;
    ">';

    echo '<div style="
        width:100%;
        max-width:600px;
        box-sizing:border-box;
        margin:0 auto;
        padding:25px 20px;
        text-align:center;
        background:#f7effa;
        border-radius:10px;
    ">';

    echo '<h2 style="
        margin:0 0 10px;
        font-size:22px;
        line-height:1.3;
        color:#222;
    ">
        Paga tu pedido con Yape
    </h2>';

    echo '<p style="
        margin:0 0 15px;
        font-size:14px;
        line-height:1.5;
        color:#555;
    ">
        Escanea el código QR para realizar el pago.
    </p>';

    echo '<img
        src="' . esc_url( $qr_url ) . '"
        alt="QR de Yape"
        style="
            display:block;
            width:260px;
            max-width:100%;
            height:auto;
            margin:15px auto;
            border:1px solid #ddd;
            border-radius:6px;
        "
    >';

    echo '<p style="
        margin:15px 0 0;
        font-size:13px;
        line-height:1.5;
        color:#666;
    ">
        Después de realizar el pago, conserva tu comprobante.<br>
        Verificaremos el pago antes de procesar tu pedido.
    </p>';

    echo '</div>';
    echo '</div>';
}
