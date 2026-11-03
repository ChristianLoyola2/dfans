<?php
/**
 * D'FANS - Reemplaza la imagen de la portada del Inicio por un carrusel de polos de distintos géneros.
 * El carrusel es un bloque Grupo (clase "dfans-carrusel") con bloques Imagen; la animación es CSS.
 * Uso: php carrusel_portada.php [--apply]
 */
define( 'WP_USE_THEMES', false );
$_SERVER['HTTP_HOST']   = 'dfans.local';
$_SERVER['REQUEST_URI'] = '/';
require 'C:/Users/GRLL/Local Sites/dfans/app/public/wp-load.php';
wp_set_current_user( 1 );
$apply = in_array( '--apply', $argv, true );

// Producto => género mostrado en la etiqueta. Se intercalan los géneros.
$slides = array(
	79  => 'Anime',
	284 => 'Rock',
	590 => 'Series',
	410 => 'Metal',
	482 => 'Artistas',
	788 => 'Videojuegos',
	203 => 'Anime',
	662 => 'Series',
);

$imagenes = array();
foreach ( $slides as $pid => $genero ) {
	$p      = wc_get_product( $pid );
	$img    = $p->get_image_id();
	$nombre = str_replace( 'Polo ', '', $p->get_name() );
	$url    = esc_url( wp_get_attachment_image_url( $img, 'large' ) );
	$link   = esc_url( get_permalink( $pid ) );
	$alt    = esc_attr( $p->get_name() . ' de D’FANS' );
	$imagenes[] = <<<HTML
<!-- wp:image {"id":$img,"sizeSlug":"large","linkDestination":"custom","className":"dfans-slide"} -->
<figure class="wp-block-image size-large dfans-slide"><a href="$link"><img src="$url" alt="$alt" class="wp-image-$img"/></a><figcaption class="wp-element-caption">$genero · $nombre</figcaption></figure>
<!-- /wp:image -->
HTML;
	echo "  $genero · $nombre (imagen $img)\n";
}

$carrusel = "<!-- wp:group {\"className\":\"dfans-carrusel\",\"layout\":{\"type\":\"default\"}} -->\n"
	. '<div class="wp-block-group dfans-carrusel">' . implode( "\n\n", $imagenes ) . "</div>\n<!-- /wp:group -->";

$contenido = get_post_field( 'post_content', 60, 'raw' );

// Bloque a reemplazar: la imagen de la portada (clase dfans-hero-img) o un carrusel anterior.
if ( preg_match( '#<!-- wp:group \{"className":"dfans-carrusel".*?</div>\n<!-- /wp:group -->#s', $contenido, $m ) ) {
	$viejo = $m[0];
	echo "Se reemplaza el carrusel existente.\n";
} elseif ( preg_match( '#<!-- wp:image \{[^}]*"className":"dfans-hero-img"\} -->.*?<!-- /wp:image -->#s', $contenido, $m ) ) {
	$viejo = $m[0];
	echo "Se reemplaza la imagen de la portada.\n";
} else {
	exit( "No se encontró la imagen de la portada.\n" );
}

$nuevo = str_replace( $viejo, $carrusel, $contenido );
echo 'Bloques en Inicio: ', count( parse_blocks( $contenido ) ), ' -> ', count( parse_blocks( $nuevo ) ), " (nivel superior, debe ser igual)\n";

if ( ! $apply ) {
	echo "Modo revisión: no se aplicó nada.\n";
	exit;
}
$r = wp_update_post( array( 'ID' => 60, 'post_content' => wp_slash( $nuevo ) ), true );
echo is_wp_error( $r ) ? $r->get_error_message() : 'Inicio actualizado (queda una revisión con la versión anterior).', "\n";
