<?php
/**
 * D'FANS - Últimos créditos de Higgsfield (02/10/2026)
 *  - Polo Niño Level Up: nuevas fotos con modelo (sin figuras de Minecraft en el fondo).
 *    Reemplaza la imagen principal, la de las variaciones y la espalda con modelo de la galería.
 *  - Lima Underground, Kitsune Blossom y Princesa Guerrera: se añaden las fotos en percha (frente y espalda).
 * No borra imágenes de la biblioteca. Uso: php fotos_extra_lote2.php [--apply]
 */
define( 'WP_USE_THEMES', false );
$_SERVER['HTTP_HOST']   = 'dfans.local';
$_SERVER['REQUEST_URI'] = '/';
require 'C:/Users/GRLL/Local Sites/dfans/app/public/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
wp_set_current_user( 1 );

$apply = in_array( '--apply', $argv, true );
$g     = 'C:/Users/GRLL/Local Sites/dfans/backups/generadas/';

$reemplazos = array(
	'polo-nino-level-up' => array( 'modelo' => $g . 'exclusivos/no-level-up-modelo.png', 'modelo-espalda' => $g . 'exclusivos/no-level-up-modelo-espalda.png' ),
);
$percha = array(
	'polo-oversize-lima-underground' => $g . 'exclusivos2/h-lima-underground',
	'baby-tee-kitsune-blossom'       => $g . 'exclusivos2/m-kitsune-blossom',
	'polo-nina-princesa-guerrera'    => $g . 'exclusivos2/na-princesa-guerrera',
);

$faltan = array();
foreach ( $reemplazos as $f ) {
	foreach ( $f as $archivo ) {
		file_exists( $archivo ) || $faltan[] = $archivo;
	}
}
foreach ( $percha as $base ) {
	foreach ( array( '-frente.png', '-espalda.png' ) as $s ) {
		file_exists( $base . $s ) || $faltan[] = $base . $s;
	}
}
foreach ( array_merge( array_keys( $reemplazos ), array_keys( $percha ) ) as $slug ) {
	get_page_by_path( $slug, OBJECT, 'product' ) || $faltan[] = "producto $slug";
}
if ( $faltan ) {
	exit( 'Faltan: ' . implode( ', ', $faltan ) . "\n" );
}
echo "Todo listo.\n";
if ( ! $apply ) {
	exit( "Modo revisión: no se aplicó nada.\n" );
}

function dfans_subir( $png, $nombre, $titulo, $post_id ) {
	$tmp = wp_tempnam( $nombre ) . '.jpg';
	$im  = imagecreatefrompng( $png );
	imagejpeg( $im, $tmp, 90 );
	imagedestroy( $im );
	$id = media_handle_sideload( array( 'name' => "$nombre.jpg", 'tmp_name' => $tmp ), $post_id, $titulo );
	if ( is_wp_error( $id ) ) {
		exit( "ERROR subiendo $png: " . $id->get_error_message() . "\n" );
	}
	update_post_meta( $id, '_wp_attachment_image_alt', $titulo );
	return $id;
}

foreach ( $reemplazos as $slug => $f ) {
	$p      = wc_get_product( get_page_by_path( $slug, OBJECT, 'product' )->ID );
	$nombre = $p->get_name();
	$viejo  = array( 'modelo' => $p->get_image_id(), 'galeria' => $p->get_gallery_image_ids() );
	$nuevo  = dfans_subir( $f['modelo'], "$slug-modelo-v2", "$nombre (con modelo)", $p->get_id() );
	$espal  = dfans_subir( $f['modelo-espalda'], "$slug-modelo-espalda-v2", "$nombre (con modelo, espalda)", $p->get_id() );
	$gal    = $viejo['galeria'];
	$gal[0] = $espal; // La primera de la galería es la espalda con modelo.
	$p->set_image_id( $nuevo );
	$p->set_gallery_image_ids( $gal );
	$p->save();
	foreach ( $p->get_children() as $vid ) {
		$v = wc_get_product( $vid );
		$v->set_image_id( $nuevo );
		$v->save();
	}
	wc_delete_product_transients( $p->get_id() );
	echo "$nombre: imagen {$viejo['modelo']} -> $nuevo, galería " . implode( ',', $viejo['galeria'] ) . ' -> ' . implode( ',', $gal ) . "\n";
}

foreach ( $percha as $slug => $base ) {
	$p      = wc_get_product( get_page_by_path( $slug, OBJECT, 'product' )->ID );
	$nombre = $p->get_name();
	$gal    = $p->get_gallery_image_ids();
	$gal[]  = dfans_subir( "$base-frente.png", "$slug-frente", "$nombre (delantera)", $p->get_id() );
	$gal[]  = dfans_subir( "$base-espalda.png", "$slug-espalda", "$nombre (espalda)", $p->get_id() );
	$p->set_gallery_image_ids( $gal );
	$p->save();
	wc_delete_product_transients( $p->get_id() );
	echo "$nombre: galería " . implode( ',', $gal ) . "\n";
}
