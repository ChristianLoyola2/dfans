<?php
/**
 * Deja la tienda "como nueva" para producción y actualiza la Política de privacidad (03/11/2026).
 * - Pedidos: los pendientes o en espera se cancelan (devuelven su stock) y todos pasan a la papelera.
 *   No se borran definitivamente: eso lo hace el usuario desde WooCommerce → Pedidos → Papelera.
 *   No se envían correos de cancelación.
 * - Stock: toda variación publicada vuelve a 20 unidades.
 * - Privacidad (página 3): Google Analytics, cookies analíticas y datos que quedan en el navegador.
 * Uso: php limpieza_y_privacidad.php [--apply]
 */
define( 'WP_USE_THEMES', false );
$_SERVER['HTTP_HOST'] = 'dfans.local';
require 'C:/Users/GRLL/Local Sites/dfans/app/public/wp-load.php';
wp_set_current_user( 1 );
$aplicar = in_array( '--apply', $argv, true );
$copia   = 'C:/Users/GRLL/Local Sites/dfans/backups/limpieza-' . date( 'Ymd-Hi' ) . '-ANTES.json';
$antes   = array( 'pedidos' => array(), 'stock' => array() );

// Sin correos durante la limpieza.
foreach ( array( 'cancelled_order', 'customer_cancelled_order', 'customer_on_hold_order', 'customer_processing_order', 'customer_completed_order', 'customer_refunded_order', 'failed_order', 'customer_failed_order', 'customer_note' ) as $c ) {
	add_filter( "woocommerce_email_enabled_$c", '__return_false' );
}

echo "== Pedidos ==\n";
foreach ( wc_get_orders( array( 'limit' => -1, 'status' => array_keys( wc_get_order_statuses() ) ) ) as $o ) {
	$antes['pedidos'][] = array( 'id' => $o->get_id(), 'estado' => $o->get_status() );
	echo "#{$o->get_id()} {$o->get_status()} {$o->get_billing_first_name()}";
	if ( $aplicar ) {
		if ( in_array( $o->get_status(), array( 'on-hold', 'pending', 'processing' ), true ) ) {
			$o->update_status( 'cancelled', 'Pedido de prueba: cancelado al preparar la tienda para producción.' );
			echo ' -> cancelado (stock devuelto)';
		}
		$o->delete( false );
		echo ' -> papelera';
	}
	echo "\n";
}

echo "== Stock ==\n";
$vars = get_posts( array( 'post_type' => 'product_variation', 'post_status' => 'publish', 'numberposts' => -1, 'fields' => 'ids' ) );
$n    = 0;
foreach ( $vars as $vid ) {
	$v = wc_get_product( $vid );
	if ( $v && $v->get_manage_stock() && 20 !== (int) $v->get_stock_quantity() ) {
		$antes['stock'][ $vid ] = $v->get_stock_quantity();
		echo "Variación $vid ({$v->get_name()}): {$v->get_stock_quantity()} -> 20\n";
		if ( $aplicar ) {
			wc_update_product_stock( $v, 20 );
		}
		$n++;
	}
}
echo count( $vars ) . " variaciones, $n por corregir\n";

echo "== Privacidad ==\n";
$p = get_post( 3 );
$c = $p->post_content;
$r = array(
	'Última actualización: 29 de septiembre de 2026.' => 'Última actualización: 3 de noviembre de 2026.',
	'<li><strong>Al navegar:</strong> cookies técnicas necesarias para el carrito de compras y la sesión.</li>' =>
		'<li><strong>Al navegar:</strong> cookies técnicas necesarias para el carrito de compras y la sesión, y datos estadísticos de uso de la tienda mediante Google Analytics (ver punto 6).</li>',
	'<li>Procesar tu pedido, verificar tu pago con Yape y coordinar la entrega.</li>' =>
		'<li>Procesar tu pedido, verificar tu pago con Yape o transferencia y coordinar la entrega.</li>',
	"<li>Atender tus reclamos, quejas y consultas.</li>\n<!-- /wp:list-item -->" =>
		"<li>Atender tus reclamos, quejas y consultas.</li>\n<!-- /wp:list-item -->\n\n<!-- wp:list-item -->\n<li>Conocer de forma estadística y agregada cómo se usa la tienda (páginas más vistas, dispositivos, origen de las visitas) para mejorarla.</li>\n<!-- /wp:list-item -->",
	'nuestro proveedor de alojamiento web y el servicio de correo electrónico que usamos para enviarte las notificaciones.' =>
		'nuestro proveedor de alojamiento web, el servicio de correo electrónico que usamos para enviarte las notificaciones y Google LLC, que nos presta el servicio de medición estadística Google Analytics. Google puede tratar esos datos en servidores ubicados fuera del Perú.',
	'<p>Usamos cookies técnicas imprescindibles para que funcionen el carrito, el inicio de sesión y el proceso de compra.</p>' =>
		'<p>Usamos cookies técnicas imprescindibles para que funcionen el carrito, el inicio de sesión y el proceso de compra.</p>' . "\n<!-- /wp:paragraph -->\n\n<!-- wp:paragraph -->\n"
		. '<p>También usamos <strong>Google Analytics</strong>, de Google LLC, para medir de forma estadística las visitas: páginas vistas, tiempo de navegación, tipo de dispositivo y navegador, ciudad o país aproximado y de dónde llega la visita. Para ello Google usa sus propias cookies (por ejemplo, <code>_ga</code>). Google Analytics no recibe tu nombre, correo, teléfono, dirección ni datos de pago. Puedes bloquear o borrar estas cookies desde la configuración de tu navegador o usar el <a href="https://tools.google.com/dlpage/gaoptout" target="_blank" rel="noopener">complemento de inhabilitación de Google Analytics</a>; la tienda seguirá funcionando con normalidad.</p>'
		. "\n<!-- /wp:paragraph -->\n\n<!-- wp:paragraph -->\n"
		. '<p>Tus favoritos y los productos que viste recientemente se guardan solo en tu navegador (almacenamiento local) y no se envían a D’FANS.</p>',
);
$c2 = $c;
foreach ( $r as $de => $a ) {
	if ( 1 !== substr_count( $c2, $de ) ) {
		exit( "Privacidad: no se encontró una sola vez el texto «" . mb_substr( $de, 0, 60 ) . "…»; no se cambia nada.\n" );
	}
	$c2 = str_replace( $de, $a, $c2 );
}
echo "Cambios en privacidad: " . count( $r ) . " textos\n";

if ( $aplicar ) {
	$antes['privacidad'] = $c;
	file_put_contents( $copia, wp_json_encode( $antes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) );
	wp_update_post( array( 'ID' => 3, 'post_content' => $c2 ) );
	foreach ( as_get_scheduled_actions( array( 'hook' => 'woocommerce_send_queued_transactional_email', 'status' => 'pending', 'per_page' => 100 ), 'ids' ) as $a ) {
		ActionScheduler::store()->cancel_action( $a );
	}
	echo "Aplicado. Copia previa: $copia\n";
}
