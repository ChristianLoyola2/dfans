<?php
/**
 * D'FANS - Convierte el Polo 2Pac (ID 518) en Polo Zarcort.
 * Cambia nombre, slug, textos, categoría (Tupac → Zarcort), SKUs e imagen principal.
 * Mantiene precios, stock y variaciones. No borra ninguna imagen de la biblioteca.
 * Uso: php cambiar_2pac_a_zarcort.php [--apply]
 */
define( 'WP_USE_THEMES', false );
$_SERVER['HTTP_HOST']   = 'dfans.local';
$_SERVER['REQUEST_URI'] = '/';
require 'C:/Users/GRLL/Local Sites/dfans/app/public/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
wp_set_current_user( 1 );

$pid   = 518;
$apply = in_array( '--apply', $argv, true );
$png   = 'C:/Users/GRLL/Local Sites/dfans/backups/generadas/zarcort/frente-b.png';
$prod  = wc_get_product( $pid );
if ( ! $prod || 'Polo 2Pac' !== $prod->get_name() || ! file_exists( $png ) ) {
	exit( "El producto 518 ya no es 'Polo 2Pac' o falta frente-b.png. No se hizo nada.\n" );
}
echo "Polo 2Pac (ID $pid) → Polo Zarcort\n";
if ( ! $apply ) {
	echo "Modo revisión: no se aplicó nada.\n";
	exit;
}

// Imagen principal nueva (JPG).
$tmp = wp_tempnam( 'polo-zarcort' ) . '.jpg';
$im  = imagecreatefrompng( $png );
imagejpeg( $im, $tmp, 90 );
imagedestroy( $im );
$img = media_handle_sideload( array( 'name' => 'polo-zarcort.jpg', 'tmp_name' => $tmp ), $pid, 'Polo Zarcort' );
if ( is_wp_error( $img ) ) {
	exit( 'ERROR subiendo la imagen: ' . $img->get_error_message() . "\n" );
}
update_post_meta( $img, '_wp_attachment_image_alt', 'Polo Zarcort' );

// Categoría: la subcategoría Tupac pasa a llamarse Zarcort.
$cat = get_term_by( 'slug', 'tupac', 'product_cat' );
if ( $cat ) {
	wp_update_term( $cat->term_id, 'product_cat', array( 'name' => 'Zarcort', 'slug' => 'zarcort' ) );
}

// Producto: nombre, slug (WordPress guarda el anterior y redirige), textos, imagen y galería vacía.
$prod->set_name( 'Polo Zarcort' );
$prod->set_slug( 'polo-zarcort' );
$prod->set_short_description( 'Polo negro D’FANS con diseño de Zarcort. Disponible en modelos Hombre, Mujer, Niño y Niña.' );
$prod->set_description(
	"Zarcort, el rapero de los raps épicos de anime y videojuegos, con el lema Rap Épico y el skyline de Madrid.\n\n" .
	"<strong>Modelos:</strong> Hombre, Mujer, Niño y Niña.\n" .
	"<strong>Tallas adultos:</strong> S, M, L, XL y XXL.\n" .
	"<strong>Tallas niños:</strong> 10, 12 y 14."
);
$prod->set_image_id( $img );
$prod->set_gallery_image_ids( array() ); // La espalda nueva la añade asignar_fotos_modelo.php.
$prod->save();

// Variaciones: SKU nuevo e imagen provisional (luego se asigna la foto de cada modelo).
foreach ( $prod->get_children() as $vid ) {
	$v = wc_get_product( $vid );
	$v->set_sku( str_replace( 'DF-2PAC-', 'DF-ZARCORT-', $v->get_sku() ) );
	$v->set_image_id( $img );
	$v->set_gallery_image_ids( array() );
	$v->save();
}
wc_delete_product_transients( $pid );
echo "Listo: producto renombrado, imagen principal $img, " . count( $prod->get_children() ) . " variaciones actualizadas.\n";
