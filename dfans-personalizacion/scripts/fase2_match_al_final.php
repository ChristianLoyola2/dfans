<?php
/**
 * D'FANS - En Adultos y Niños, la Colección Match pasa al final (después de los exclusivos). 01/10/2026
 * Solo cambia el orden de los dos bloques en las plantillas; copia en backups/fase2/.
 * Uso: php fase2_match_al_final.php [--apply]
 */
define( 'WP_USE_THEMES', false );
$_SERVER['HTTP_HOST']   = 'dfans.local';
$_SERVER['REQUEST_URI'] = '/';
require 'C:/Users/GRLL/Local Sites/dfans/app/public/wp-load.php';
wp_set_current_user( 1 );

$apply = in_array( '--apply', $argv, true );
$copia = 'C:/Users/GRLL/Local Sites/dfans/backups/fase2/';

/** Devuelve el bloque de grupo completo que empieza con la clase dada (no tienen grupos anidados). */
function dfans_bloque( $contenido, $clase ) {
	$inicio = strpos( $contenido, '<!-- wp:group {"align":"wide","className":"' . $clase . '"' );
	if ( false === $inicio ) {
		return null;
	}
	$fin = strpos( $contenido, '<!-- /wp:group -->', $inicio );
	return substr( $contenido, $inicio, $fin + strlen( '<!-- /wp:group -->' ) - $inicio );
}

foreach ( array( 'taxonomy-product_cat-ropa-de-adultos', 'taxonomy-product_cat-ropa-para-ninos' ) as $slug ) {
	$t     = get_posts( array( 'post_type' => 'wp_template', 'name' => $slug, 'post_status' => 'any', 'numberposts' => 1 ) )[0];
	$c     = $t->post_content;
	$match = dfans_bloque( $c, 'dfans-seccion dfans-seccion-match' );
	$solo  = dfans_bloque( $c, 'dfans-seccion dfans-seccion-solo' );
	if ( ! $match || ! $solo || substr_count( $match, '<!-- wp:group' ) !== 1 || substr_count( $solo, '<!-- wp:group' ) !== 1 ) {
		echo "$slug: no se encontraron los bloques como se esperaba, no se toca.\n";
		continue;
	}
	if ( strpos( $c, $solo ) < strpos( $c, $match ) ) {
		echo "$slug: Match ya está al final.\n";
		continue;
	}
	echo "$slug: Match pasa al final.\n";
	if ( ! $apply ) {
		continue;
	}
	wp_mkdir_p( $copia );
	file_put_contents( $copia . "$slug.ANTES-ORDEN.html", $c );
	$nuevo = str_replace( array( $match, $solo ), array( '%%SOLO%%', '%%MATCH%%' ), $c );
	$nuevo = str_replace( array( '%%SOLO%%', '%%MATCH%%' ), array( $solo, $match ), $nuevo );
	wp_update_post( array( 'ID' => $t->ID, 'post_content' => wp_slash( $nuevo ) ) );
}
if ( ! $apply ) {
	echo "Modo revisión: no se aplicó nada.\n";
}
