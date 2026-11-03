<?php
/**
 * Convierte a JPG las imágenes PNG en uso, sin cambiar su ID de adjunto (02/11/2026).
 * - Los PNG originales y sus tamaños quedan en disco (no se borra nada).
 * - El adjunto pasa a apuntar al JPG y se regeneran sus tamaños.
 * - Las URL antiguas en el contenido de páginas/partes se cambian por las nuevas.
 * - Los PNG con transparencia se dejan como están.
 * Uso: php optimizar_png_a_jpg.php           (solo muestra qué haría)
 *      php optimizar_png_a_jpg.php --apply
 */
define( 'WP_USE_THEMES', false );
$_SERVER['HTTP_HOST'] = 'dfans.local';
require 'C:/Users/GRLL/Local Sites/dfans/app/public/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
wp_set_current_user( 1 );
ini_set( 'memory_limit', '1024M' );

$aplicar = in_array( '--apply', $argv, true );
global $wpdb;

// Adjuntos PNG referenciados como imagen destacada, galería, fotos por modelo o en contenido.
$png = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_type='attachment' AND post_mime_type='image/png'" );
$usos = array();
foreach ( $png as $id ) {
	$id  = (int) $id;
	$ref = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key='_thumbnail_id' AND meta_value=%d", $id ) );
	$ref += (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key IN ('_product_image_gallery','_dfans_fotos') AND CONCAT(',', REPLACE(REPLACE(REPLACE(meta_value,'\"',','),':',','),';',','), ',') LIKE %s", '%,' . $id . ',%' ) );
	$ref += (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type NOT IN ('revision','attachment') AND post_content LIKE %s", '%wp-image-' . $id . '"%' ) );
	if ( $ref ) {
		$usos[ $id ] = $ref;
	}
}

$registro = array();
$total_antes = $total_despues = 0;
foreach ( $usos as $id => $ref ) {
	$archivo = get_attached_file( $id );
	$meta    = wp_get_attachment_metadata( $id );
	if ( ! $archivo || ! file_exists( $archivo ) ) {
		echo "#$id: archivo no encontrado, se omite\n";
		continue;
	}
	$im = imagecreatefrompng( $archivo );
	if ( ! $im ) {
		echo "#$id: no se pudo leer, se omite\n";
		continue;
	}
	// ¿Tiene transparencia real? Se revisa una muestra de píxeles.
	$w = imagesx( $im );
	$h = imagesy( $im );
	$alfa = false;
	if ( ! imageistruecolor( $im ) || ( imagecolortransparent( $im ) >= 0 ) ) {
		imagepalettetotruecolor( $im );
	}
	for ( $y = 0; $y < $h && ! $alfa; $y += 7 ) {
		for ( $x = 0; $x < $w; $x += 7 ) {
			if ( ( ( imagecolorat( $im, $x, $y ) >> 24 ) & 0x7F ) > 10 ) {
				$alfa = true;
				break;
			}
		}
	}
	if ( $alfa ) {
		echo "#$id: tiene transparencia, se deja en PNG\n";
		imagedestroy( $im );
		continue;
	}

	$nuevo = preg_replace( '/\.png$/i', '.jpg', $archivo );
	if ( file_exists( $nuevo ) ) {
		$nuevo = preg_replace( '/\.png$/i', '-opt.jpg', $archivo );
	}
	$antes = filesize( $archivo );
	if ( ! $aplicar ) {
		echo "#$id ($ref usos) " . basename( $archivo ) . ' ' . round( $antes / 1024 ) . " KB -> " . basename( $nuevo ) . "\n";
		imagedestroy( $im );
		continue;
	}

	// Fondo blanco por si acaso y guardado como JPG progresivo.
	$lienzo = imagecreatetruecolor( $w, $h );
	imagefill( $lienzo, 0, 0, imagecolorallocate( $lienzo, 255, 255, 255 ) );
	imagecopy( $lienzo, $im, 0, 0, 0, 0, $w, $h );
	imageinterlace( $lienzo, true );
	imagejpeg( $lienzo, $nuevo, 86 );
	imagedestroy( $im );
	imagedestroy( $lienzo );

	// URL antiguas (original y cada tamaño) para reemplazarlas luego.
	$base_url = trailingslashit( dirname( wp_get_attachment_url( $id ) ) );
	$viejas   = array( 'full' => wp_get_attachment_url( $id ) );
	foreach ( (array) ( $meta['sizes'] ?? array() ) as $nombre => $t ) {
		$viejas[ $nombre ] = $base_url . $t['file'];
	}

	update_attached_file( $id, $nuevo );
	wp_update_post( array( 'ID' => $id, 'post_mime_type' => 'image/jpeg' ) );
	$meta_nueva = wp_generate_attachment_metadata( $id, $nuevo );
	wp_update_attachment_metadata( $id, $meta_nueva );

	$nuevas = array( 'full' => wp_get_attachment_url( $id ) );
	foreach ( (array) ( $meta_nueva['sizes'] ?? array() ) as $nombre => $t ) {
		$nuevas[ $nombre ] = $base_url . $t['file'];
	}
	$pares = array();
	foreach ( $viejas as $nombre => $url ) {
		$pares[ $url ] = $nuevas[ $nombre ] ?? $nuevas['full'];
	}
	$despues = filesize( $nuevo );
	$total_antes += $antes;
	$total_despues += $despues;
	$registro[ $id ] = array( 'png' => $archivo, 'jpg' => $nuevo, 'meta_anterior' => $meta, 'urls' => $pares );
	echo "#$id " . basename( $archivo ) . ' ' . round( $antes / 1024 ) . ' KB -> ' . round( $despues / 1024 ) . " KB\n";
}

if ( $aplicar && $registro ) {
	// Cambia las URL en el contenido (páginas, partes de plantilla, plantillas, bloques).
	$todas = array();
	foreach ( $registro as $r ) {
		$todas += $r['urls'];
	}
	uksort( $todas, function ( $a, $b ) { return strlen( $b ) - strlen( $a ); } ); // primero las más largas
	$posts = $wpdb->get_results( "SELECT ID, post_content FROM {$wpdb->posts} WHERE post_type NOT IN ('revision','attachment') AND post_content LIKE '%.png%'" );
	foreach ( $posts as $p ) {
		$c = strtr( $p->post_content, $todas );
		if ( $c !== $p->post_content ) {
			$wpdb->update( $wpdb->posts, array( 'post_content' => $c ), array( 'ID' => $p->ID ) );
			clean_post_cache( $p->ID );
			echo "Contenido actualizado: post {$p->ID}\n";
		}
	}
	file_put_contents( 'C:/Users/GRLL/Local Sites/dfans/backups/optimizar-imagenes-registro-' . date( 'Ymd-Hi' ) . '.json', wp_json_encode( $registro, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
	echo 'Total originales: ' . round( $total_antes / 1048576, 1 ) . ' MB -> JPG: ' . round( $total_despues / 1048576, 1 ) . " MB\n";
}
