<?php
/**
 * D'FANS - Textos de la tienda con el pago con tarjeta (Mercado Pago) activo (10/11/2026).
 * Portada (60), pie (112), Términos (807) y Privacidad (3). Copias previas en backups/rediseno/.
 * Los textos de la barra de anuncios y de la ficha están en los snippets #29 y #31.
 * Uso: php textos_tarjeta.php [--apply]
 */
define( 'WP_USE_THEMES', false );
$_SERVER['HTTP_HOST'] = 'dfans.local';
require 'C:/Users/GRLL/Local Sites/dfans/app/public/wp-load.php';
wp_set_current_user( 1 );

$cambios = array(
	60  => array(
		'<h3 class="wp-block-heading">Paga con Yape o transferencia</h3>' => '<h3 class="wp-block-heading">Paga con tarjeta, Yape o transferencia</h3>',
		'<p>Al confirmar tu pedido ves el QR de Yape o los datos de la cuenta. Preparamos tu pedido apenas verificamos el pago.</p>' => '<p>Con tarjeta de crédito o débito el pago se confirma al instante. Con Yape o transferencia ves el QR o los datos de la cuenta al confirmar y preparamos tu pedido apenas verificamos el pago.</p>',
	),
	112 => array(
		'<span class="dfans-pill">Yape</span><span class="dfans-pill">Transferencia bancaria</span>' => '<span class="dfans-pill">Tarjeta</span><span class="dfans-pill">Yape</span><span class="dfans-pill">Transferencia bancaria</span>',
	),
	807 => array(
		'Última actualización: 2 de noviembre de 2026.' => 'Última actualización: 10 de noviembre de 2026.',
		"<ul class=\"wp-block-list\"><!-- wp:list-item -->\n<li><strong>Yape:</strong>" => "<ul class=\"wp-block-list\"><!-- wp:list-item -->\n<li><strong>Tarjeta de crédito o débito:</strong> pagas en el checkout a través de Mercado Pago, que procesa el cobro de forma segura. D’FANS no ve ni guarda los datos de tu tarjeta. Si el pago se aprueba, tu pedido pasa a preparación de inmediato; si se rechaza, no se realiza ningún cargo.</li>\n<!-- /wp:list-item -->\n\n<!-- wp:list-item -->\n<li><strong>Yape:</strong>",
		'<p>En ambos casos tu pedido queda <strong>en espera</strong>' => '<p>Con Yape o transferencia tu pedido queda <strong>en espera</strong>',
	),
	3   => array(
		'Última actualización: 3 de noviembre de 2026.' => 'Última actualización: 10 de noviembre de 2026.',
		'<li>Procesar tu pedido, verificar tu pago con Yape o transferencia y coordinar la entrega.</li>' => '<li>Procesar tu pedido, verificar tu pago (tarjeta, Yape o transferencia) y coordinar la entrega.</li>',
		'nuestro proveedor de alojamiento web, el servicio de correo electrónico' => 'nuestro proveedor de alojamiento web, Mercado Pago (procesa los pagos con tarjeta; D’FANS no recibe ni guarda los datos de tu tarjeta), el servicio de correo electrónico',
	),
);

$nuevos  = array();
$errores = array();
foreach ( $cambios as $id => $reemplazos ) {
	$c = get_post( $id )->post_content;
	$n = $c;
	foreach ( $reemplazos as $de => $a ) {
		$veces = substr_count( $n, $de );
		if ( 1 !== $veces ) {
			$errores[] = "post $id: «" . mb_substr( $de, 0, 50 ) . "…» aparece $veces veces";
			continue;
		}
		$n = str_replace( $de, $a, $n );
	}
	$nuevos[ $id ] = array( $c, $n );
}
if ( $errores ) {
	exit( "ERRORES (no se aplicó nada):\n  " . implode( "\n  ", $errores ) . "\n" );
}
echo "Todos los textos encontrados.\n";
if ( ! in_array( '--apply', $argv, true ) ) {
	exit( "Modo revisión.\n" );
}
$sello = date( 'Ymd-Hi' );
foreach ( $nuevos as $id => list( $antes, $despues ) ) {
	file_put_contents( "C:/Users/GRLL/Local Sites/dfans/backups/rediseno/post-$id.ANTES-TARJETA-$sello.html", $antes );
	wp_update_post( array( 'ID' => $id, 'post_content' => wp_slash( $despues ) ) );
	echo "Actualizado post $id\n";
}
