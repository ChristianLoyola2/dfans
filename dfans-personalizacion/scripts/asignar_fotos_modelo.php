<?php
/**
 * D'FANS - Asigna a un polo sus fotos generadas: espalda del polo y fotos por modelo.
 * Carpeta esperada (backups/generadas/<carpeta>/):
 *   espalda.png, hombre-delantera.png, hombre-espalda.png, mujer-..., nino-..., nina-...
 * Uso: php asignar_fotos_modelo.php <ID producto> <carpeta> [--apply]
 */
define( 'WP_USE_THEMES', false );
$_SERVER['HTTP_HOST']   = 'dfans.local';
$_SERVER['REQUEST_URI'] = '/';
require 'C:/Users/GRLL/Local Sites/dfans/app/public/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
wp_set_current_user( 1 );

$pid     = (int) ( $argv[1] ?? 0 );
$carpeta = $argv[2] ?? '';
$apply   = in_array( '--apply', $argv, true );
$dir     = 'C:/Users/GRLL/Local Sites/dfans/backups/generadas/' . $carpeta . '/';
$prod    = wc_get_product( $pid );
if ( ! $prod || ! $prod->is_type( 'variable' ) || ! is_dir( $dir ) ) {
	exit( "Uso: php asignar_fotos_modelo.php <ID producto variable> <carpeta existente> [--apply]\n" );
}

$modelos = array( 'hombre' => 'Hombre', 'mujer' => 'Mujer', 'nino' => 'Niño', 'nina' => 'Niña' );
$faltan  = array();
foreach ( array_merge( array( 'espalda' ), ...array_map( fn( $m ) => array( "$m-delantera", "$m-espalda" ), array_keys( $modelos ) ) ) as $f ) {
	if ( ! file_exists( "$dir$f.png" ) ) {
		$faltan[] = "$f.png";
	}
}
if ( $faltan ) {
	exit( 'Faltan: ' . implode( ', ', $faltan ) . "\n" );
}

$nombre = $prod->get_name();
$slug   = sanitize_title( $nombre );
echo "$nombre (ID $pid): 9 imágenes listas.\n";
if ( ! $apply ) {
	echo "Modo revisión: no se aplicó nada.\n";
	exit;
}

/** Convierte a JPG (más liviano), sube a la biblioteca y devuelve el ID. */
function dfans_subir_jpg( $png, $nombre_archivo, $titulo, $post_id ) {
	$tmp = wp_tempnam( $nombre_archivo ) . '.jpg';
	$im  = imagecreatefrompng( $png );
	imagejpeg( $im, $tmp, 90 );
	imagedestroy( $im );
	$id = media_handle_sideload( array( 'name' => "$nombre_archivo.jpg", 'tmp_name' => $tmp ), $post_id, $titulo );
	if ( is_wp_error( $id ) ) {
		exit( "ERROR subiendo $png: " . $id->get_error_message() . "\n" );
	}
	update_post_meta( $id, '_wp_attachment_image_alt', $titulo );
	return $id;
}

// Espalda del polo: se añade a la galería principal del producto.
$espalda = dfans_subir_jpg( "{$dir}espalda.png", "$slug-espalda", "$nombre (espalda)", $pid );
$prod->set_gallery_image_ids( array_values( array_unique( array_merge( $prod->get_gallery_image_ids(), array( $espalda ) ) ) ) );
$prod->save();

// Fotos por modelo: delantera como imagen de la variación, espalda en su galería.
$img = array();
foreach ( $modelos as $m => $etiqueta ) {
	$img[ $m ] = array(
		dfans_subir_jpg( "{$dir}$m-delantera.png", "$slug-$m-delantera", "$nombre – $etiqueta (delantera)", $pid ),
		dfans_subir_jpg( "{$dir}$m-espalda.png", "$slug-$m-espalda", "$nombre – $etiqueta (espalda)", $pid ),
	);
}
$n = 0;
foreach ( $prod->get_children() as $vid ) {
	$v = wc_get_product( $vid );
	$m = $v->get_attribute( 'pa_modelo' ) ? sanitize_title( $v->get_attribute( 'pa_modelo' ) ) : '';
	if ( ! isset( $img[ $m ] ) ) {
		continue;
	}
	$v->set_image_id( $img[ $m ][0] );
	$v->set_gallery_image_ids( array( $img[ $m ][1] ) );
	$v->save();
	$n++;
}
wc_delete_product_transients( $pid );
echo "Listo: espalda en la galería y fotos asignadas a $n variaciones.\n";
