<?php
/**
 * Actualiza las secciones 3 a 7 de Términos y condiciones (página 807) con las líneas,
 * cortes, tallas, precios, stock y medios de pago actuales (02/11/2026).
 * Guarda antes una copia del contenido en backups/terminos-807.ANTES-<fecha>.html.
 * Uso: php actualizar_terminos.php [--apply]
 */
define( 'WP_USE_THEMES', false );
$_SERVER['HTTP_HOST'] = 'dfans.local';
require 'C:/Users/GRLL/Local Sites/dfans/app/public/wp-load.php';
wp_set_current_user( 1 );

$pagina = get_post( 807 );
$c      = $pagina->post_content;

$ini = strrpos( substr( $c, 0, strpos( $c, '3. Productos</h2>' ) ), '<!-- wp:heading -->' );
$fin = strrpos( substr( $c, 0, strpos( $c, '8. Entregas</h2>' ) ), '<!-- wp:heading -->' );
if ( false === $ini || false === $fin || $fin <= $ini ) {
	exit( "No se encontraron las secciones 3 y 8; no se cambia nada.\n" );
}

function dfans_t_h2( $t ) {
	return "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">$t</h2>\n<!-- /wp:heading -->\n\n";
}
function dfans_t_p( $t ) {
	return "<!-- wp:paragraph -->\n<p>$t</p>\n<!-- /wp:paragraph -->\n\n";
}
function dfans_t_ul( $items ) {
	$li = array();
	foreach ( $items as $i ) {
		$li[] = "<!-- wp:list-item -->\n<li>$i</li>\n<!-- /wp:list-item -->";
	}
	return "<!-- wp:list -->\n<ul class=\"wp-block-list\">" . implode( "\n\n", $li ) . "</ul>\n<!-- /wp:list -->\n\n";
}

$nuevo = dfans_t_h2( '3. Productos' )
	. dfans_t_p( 'Vendemos polos negros con diseños estampados, en dos líneas:' )
	. dfans_t_ul( array(
		'<strong>Match familiar:</strong> polos unisex con el mismo diseño en tallas de adulto y de niño, para combinar en familia o comprar por gusto individual. Tallas de adulto: S, M, L, XL y XXL. Tallas de niño: 10, 12 y 14.',
		'<strong>Exclusivos D’FANS:</strong> diseños originales con un corte propio para cada público: <strong>Hombre</strong> – Oversize (S a XXL); <strong>Mujer</strong> – Baby tee de corte crop (XS a L); <strong>Niño</strong> – Regular reforzado (10, 12 y 14); <strong>Niña</strong> – Manga mariposa (10, 12 y 14).',
		'Cada ficha incluye una guía de tallas con medidas referenciales.',
		'Las fotografías son referenciales. Los colores pueden variar ligeramente según la pantalla de cada dispositivo.',
		'Las marcas, nombres y personajes que aparecen en los diseños pertenecen a sus respectivos titulares y se mencionan solo con fines descriptivos.',
	) )
	. dfans_t_h2( '4. Precios' )
	. dfans_t_p( 'Los precios están expresados en soles (S/) e <strong>incluyen el IGV</strong>. El costo de envío no está incluido y depende de la modalidad de entrega elegida. El precio válido es el que figura en la ficha del producto al momento de confirmar el pedido.' )
	. dfans_t_ul( array(
		'<strong>Match familiar:</strong> tallas de niño (10, 12 y 14) S/ 30; tallas S, M y L S/ 35; tallas XL y XXL S/ 40.',
		'<strong>Exclusivos:</strong> Oversize (hombre) S/ 45; Baby tee (mujer) S/ 40; Regular reforzado (niño) S/ 32; Manga mariposa (niña) S/ 35.',
	) )
	. dfans_t_h2( '5. Stock' )
	. dfans_t_p( 'Cada talla de cada diseño tiene stock propio. Si una talla está agotada aparece tachada y no puede añadirse al carrito. El stock se descuenta al confirmar el pedido y se libera si el pedido se anula.' )
	. dfans_t_h2( '6. Proceso de compra' )
	. dfans_t_ul( array(
		'Eliges el diseño y la talla (puedes consultar la guía de tallas) y añades el producto al carrito.',
		'En la página de pago eliges la modalidad de entrega y el medio de pago, completas tus datos, aceptas estos términos y confirmas el pedido.',
		'Recibirás un correo con el resumen de tu pedido y, cuando verifiquemos el pago, te contactaremos para coordinar la entrega.',
	) )
	. dfans_t_h2( '7. Medios de pago' )
	. dfans_t_ul( array(
		'<strong>Yape:</strong> al confirmar el pedido se muestra nuestro código QR de Yape.',
		'<strong>Transferencia bancaria:</strong> los datos de la cuenta se muestran al confirmar el pedido y en el correo de confirmación. Usa el número de pedido como referencia.',
	) )
	. dfans_t_p( 'En ambos casos tu pedido queda <strong>en espera</strong> hasta que verifiquemos el pago. Conserva tu comprobante: podemos solicitarlo para validar la operación. Si no recibimos el pago en un plazo de 48 horas, el pedido podrá anularse y el stock será liberado.' );

$c2 = substr( $c, 0, $ini ) . $nuevo . substr( $c, $fin );
$c2 = preg_replace( '/Última actualización: [^<]+\./u', 'Última actualización: 2 de noviembre de 2026.', $c2, 1 );

if ( ! in_array( '--apply', $argv, true ) ) {
	echo $nuevo;
	exit;
}
file_put_contents( 'C:/Users/GRLL/Local Sites/dfans/backups/terminos-807.ANTES-' . date( 'Ymd-Hi' ) . '.html', $c );
$r = wp_update_post( array( 'ID' => 807, 'post_content' => $c2 ), true );
echo is_wp_error( $r ) ? 'ERROR: ' . $r->get_error_message() . "\n" : "Términos actualizados (página 807).\n";
