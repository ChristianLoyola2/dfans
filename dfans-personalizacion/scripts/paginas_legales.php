<?php
/**
 * D'FANS - Crea/actualiza las páginas legales y el Libro de Reclamaciones.
 * Los datos de la empresa salen del shortcode [dfans_empresa] (snippet "Libro de Reclamaciones").
 * Uso: php paginas_legales.php [--apply]
 */
define( 'WP_USE_THEMES', false );
$_SERVER['HTTP_HOST']   = 'dfans.local';
$_SERVER['REQUEST_URI'] = '/';
require 'C:/Users/GRLL/Local Sites/dfans/app/public/wp-load.php';
wp_set_current_user( 1 );
$apply = in_array( '--apply', $argv, true );

/* Ayudantes para escribir bloques. */
function h2( $t ) {
	return "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">$t</h2>\n<!-- /wp:heading -->";
}
function p( $t ) {
	return "<!-- wp:paragraph -->\n<p>$t</p>\n<!-- /wp:paragraph -->";
}
function ul( $items ) {
	$li = implode( "\n\n", array_map( fn( $i ) => "<!-- wp:list-item -->\n<li>$i</li>\n<!-- /wp:list-item -->", $items ) );
	return "<!-- wp:list -->\n<ul class=\"wp-block-list\">$li</ul>\n<!-- /wp:list -->";
}
function doc( ...$bloques ) {
	return implode( "\n\n", $bloques );
}

$fecha   = 'Última actualización: 29 de septiembre de 2026.';
$empresa = '<strong>[dfans_empresa dato="nombre_comercial"]</strong> (razón social: [dfans_empresa dato="razon_social"], RUC [dfans_empresa dato="ruc"]), con domicilio en [dfans_empresa dato="direccion"]';
$contacto = 'correo [dfans_empresa dato="correo"] o teléfono/WhatsApp [dfans_empresa dato="telefono"]';

$u = array(
	'privacidad' => '/politica-de-privacidad/',
	'terminos'   => '/terminos-y-condiciones/',
	'cambios'    => '/cambios-y-devoluciones/',
	'libro'      => '/libro-de-reclamaciones/',
);

/* ------------------------------------------------------------------ */
$terminos = doc(
	p( "<em>$fecha</em>" ),
	h2( '1. Quiénes somos' ),
	p( "Esta tienda virtual es operada por $empresa. Para cualquier consulta puedes escribirnos al $contacto." ),
	h2( '2. Aceptación' ),
	p( 'Al navegar y comprar en D’FANS aceptas estos Términos y condiciones, la <a href="' . $u['privacidad'] . '">Política de privacidad</a> y la <a href="' . $u['cambios'] . '">Política de cambios y devoluciones</a>.' ),
	h2( '3. Productos' ),
	ul( array(
		'Vendemos polos negros con diseños estampados, en modelos <strong>Hombre, Mujer, Niño y Niña</strong>.',
		'Tallas para adultos: S, M, L, XL y XXL. Tallas para niños: 10, 12 y 14.',
		'Las fotografías son referenciales. Los colores pueden variar ligeramente según la pantalla de cada dispositivo.',
		'Las marcas, nombres y personajes que aparecen en los diseños pertenecen a sus respectivos titulares y se mencionan solo con fines descriptivos.',
	) ),
	h2( '4. Precios' ),
	p( 'Los precios están expresados en soles (S/) e <strong>incluyen el IGV</strong>. El costo de envío no está incluido y depende de la modalidad de entrega elegida.' ),
	ul( array(
		'Tallas de niños (10, 12 y 14): S/ 30.',
		'Tallas de adultos S, M y L: S/ 35.',
		'Tallas de adultos XL y XXL: S/ 40.',
	) ),
	h2( '5. Stock' ),
	p( 'Cada combinación de modelo y talla tiene stock propio. Si una combinación está agotada, no podrá añadirse al carrito. El stock se reserva al confirmar el pedido.' ),
	h2( '6. Proceso de compra' ),
	ul( array(
		'Eliges el diseño, el modelo y la talla, y añades el producto al carrito.',
		'En el checkout eliges la modalidad de entrega, completas tus datos y confirmas el pedido.',
		'Recibirás un correo con el resumen de tu pedido.',
	) ),
	h2( '7. Pago con Yape' ),
	p( 'Al confirmar el pedido se muestra nuestro código QR de Yape. Tu pedido queda <strong>en espera</strong> hasta que verifiquemos el pago. Conserva tu comprobante: podemos solicitarlo para validar la operación. Si no recibimos el pago en un plazo de 48 horas, el pedido podrá anularse y el stock será liberado.' ),
	h2( '8. Entregas' ),
	ul( array(
		'<strong>Delivery en Lima:</strong> el costo depende del distrito y se coordina contigo después de realizar el pedido. Lo asume el cliente.',
		'<strong>Recojo en tienda (Gamarra):</strong> gratuito, en [dfans_empresa dato="direccion"]. Te enviaremos las indicaciones y el horario disponible.',
		'<strong>Envío a provincia:</strong> por agencia de transporte o Shalom. El costo del transporte lo asume el cliente y se paga directamente en la agencia.',
	) ),
	p( 'Los plazos de entrega se coordinan con cada cliente una vez verificado el pago.' ),
	h2( '9. Cambios y devoluciones' ),
	p( 'Consulta nuestra <a href="' . $u['cambios'] . '">Política de cambios y devoluciones</a>.' ),
	h2( '10. Libro de Reclamaciones' ),
	p( 'Conforme al Código de Protección y Defensa del Consumidor, contamos con un <a href="' . $u['libro'] . '">Libro de Reclamaciones virtual</a>.' ),
	h2( '11. Legislación aplicable' ),
	p( 'Estos términos se rigen por las leyes de la República del Perú.' )
);

/* ------------------------------------------------------------------ */
$privacidad = doc(
	p( "<em>$fecha</em>" ),
	p( "En $empresa respetamos tu privacidad y tratamos tus datos personales conforme a la Ley N.° 29733, Ley de Protección de Datos Personales, y su Reglamento." ),
	h2( '1. Qué datos recopilamos' ),
	ul( array(
		'<strong>Al comprar:</strong> nombre, apellido, dirección, distrito o ciudad, departamento, teléfono y correo electrónico.',
		'<strong>Al crear una cuenta:</strong> correo electrónico, nombre de usuario y contraseña (guardada cifrada).',
		'<strong>En el Libro de Reclamaciones:</strong> nombre, tipo y número de documento, domicilio, teléfono, correo y el detalle de tu reclamo o queja.',
		'<strong>Al navegar:</strong> cookies técnicas necesarias para el carrito de compras y la sesión.',
	) ),
	h2( '2. Para qué los usamos' ),
	ul( array(
		'Procesar tu pedido, verificar tu pago con Yape y coordinar la entrega.',
		'Enviarte correos sobre el estado de tu pedido.',
		'Gestionar tu cuenta de cliente.',
		'Atender tus reclamos, quejas y consultas.',
		'Cumplir con nuestras obligaciones legales y tributarias.',
	) ),
	p( 'No vendemos ni alquilamos tus datos personales.' ),
	h2( '3. Con quién los compartimos' ),
	p( 'Solo con quienes nos ayudan a prestar el servicio: la empresa de transporte o agencia que entrega tu pedido, nuestro proveedor de alojamiento web y el servicio de correo electrónico que usamos para enviarte las notificaciones. También podemos compartirlos cuando una autoridad competente lo requiera.' ),
	h2( '4. Cuánto tiempo los conservamos' ),
	p( 'Conservamos los datos de tus pedidos y reclamos durante el tiempo necesario para cumplir con la ley. Los datos de tu cuenta se mantienen mientras la tengas activa.' ),
	h2( '5. Tus derechos' ),
	p( "Puedes ejercer tus derechos de acceso, rectificación, cancelación y oposición (derechos ARCO) escribiéndonos al $contacto. Si consideras que no hemos atendido tu solicitud, puedes acudir a la Autoridad Nacional de Protección de Datos Personales." ),
	h2( '6. Cookies' ),
	p( 'Usamos cookies técnicas imprescindibles para que funcionen el carrito, el inicio de sesión y el proceso de compra.' ),
	h2( '7. Cambios en esta política' ),
	p( 'Podemos actualizar esta política. La fecha de la última actualización figura al inicio de esta página.' )
);

/* ------------------------------------------------------------------ */
$cambios = doc(
	p( "<em>$fecha</em>" ),
	h2( '1. Producto con falla' ),
	p( 'Si tu polo llegó con una falla de fábrica (estampado defectuoso, costura abierta, mancha) o no corresponde a lo que pediste, avísanos dentro de los <strong>7 días calendario</strong> desde que lo recibiste. Te lo cambiamos por el mismo producto o, si no hay stock, te devolvemos el dinero. En este caso, nosotros asumimos el costo del envío.' ),
	h2( '2. Cambio de talla o modelo' ),
	p( 'Puedes pedir un cambio de talla o de modelo dentro de los <strong>7 días calendario</strong> desde que recibiste el producto, siempre que:' ),
	ul( array(
		'El polo esté sin usar, sin lavar y con su etiqueta.',
		'Haya stock de la talla o el modelo que necesitas.',
		'Si la nueva talla tiene otro precio, se cobra o se devuelve la diferencia.',
	) ),
	p( 'El costo del envío del cambio lo asume el cliente. También puedes hacer el cambio de forma gratuita en nuestra tienda de Gamarra.' ),
	h2( '3. Cómo solicitarlo' ),
	p( "Escríbenos al $contacto indicando tu número de pedido, el motivo y, si hay una falla, fotos del producto. Te responderemos con los pasos a seguir." ),
	h2( '4. Devolución del dinero' ),
	p( 'Cuando corresponda una devolución, la haremos por Yape o transferencia en un plazo máximo de 7 días hábiles desde que recibamos y revisemos el producto.' ),
	h2( '5. Libro de Reclamaciones' ),
	p( 'Si no estás conforme con la solución, puedes registrar tu reclamo en nuestro <a href="' . $u['libro'] . '">Libro de Reclamaciones virtual</a>.' )
);

/* ------------------------------------------------------------------ */
$libro = doc(
	p( 'Conforme a lo establecido en el Código de Protección y Defensa del Consumidor, esta tienda cuenta con un Libro de Reclamaciones a tu disposición. Completa la Hoja de Reclamación y recibirás una copia en tu correo electrónico.' ),
	"<!-- wp:shortcode -->\n[dfans_libro_reclamaciones]\n<!-- /wp:shortcode -->"
);

$paginas = array(
	array( 3, 'Política de privacidad', 'politica-de-privacidad', $privacidad ),
	array( 0, 'Términos y condiciones', 'terminos-y-condiciones', $terminos ),
	array( 11, 'Cambios y devoluciones', 'cambios-y-devoluciones', $cambios ),
	array( 0, 'Libro de Reclamaciones', 'libro-de-reclamaciones', $libro ),
);

$ids = array();
foreach ( $paginas as list( $id, $titulo, $slug, $contenido ) ) {
	if ( ! $id ) {
		$existe = get_page_by_path( $slug );
		$id     = $existe ? $existe->ID : 0;
	}
	$bloques = count( array_filter( wp_list_pluck( parse_blocks( $contenido ), 'blockName' ) ) );
	echo str_pad( $titulo, 26 ), $id ? "actualizar ID $id" : 'crear', " ($bloques bloques) -> /$slug/\n";
	if ( ! $apply ) {
		continue;
	}
	$datos = array(
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_title'   => $titulo,
		'post_name'    => $slug,
		'post_content' => wp_slash( $contenido ),
	);
	if ( $id ) {
		$datos['ID'] = $id;
	}
	$r = $id ? wp_update_post( $datos, true ) : wp_insert_post( $datos, true );
	if ( is_wp_error( $r ) ) {
		exit( 'ERROR: ' . $r->get_error_message() . "\n" );
	}
	$ids[ $slug ] = $r;
	echo "   -> ID $r ", get_permalink( $r ), "\n";
}

if ( $apply ) {
	update_option( 'wp_page_for_privacy_policy', $ids['politica-de-privacidad'] );
	update_option( 'woocommerce_terms_page_id', $ids['terminos-y-condiciones'] );
	echo "Páginas asignadas en WordPress (privacidad) y WooCommerce (términos).\n";
} else {
	echo "Modo revisión: no se aplicó nada.\n";
}
