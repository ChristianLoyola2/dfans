<?php
/**
 * D'FANS - Fase 1, ajustes (30/09/2026)
 *  - Snippet #28: vuelve a cargar el código desde backups/snippets/28-secciones-match-adultos-ninos.php.
 *  - CSS: reemplaza el bloque "Secciones Match, Adultos y Niños" de los estilos globales por el del archivo.
 *  - Textos de los polos: "modelos Hombre, Mujer, Niño y Niña" -> unisex (copia en backups/fase1/textos-antes.json).
 * Uso: php fase1_ajustes.php [--apply]
 */
define( 'CODE_SNIPPETS_SAFE_MODE', true );
define( 'WP_USE_THEMES', false );
$_SERVER['HTTP_HOST']   = 'dfans.local';
$_SERVER['REQUEST_URI'] = '/';
require 'C:/Users/GRLL/Local Sites/dfans/app/public/wp-load.php';
wp_set_current_user( 1 );

$apply  = in_array( '--apply', $argv, true );
$base   = 'C:/Users/GRLL/Local Sites/dfans/backups/';
$codigo = file_get_contents( $base . 'snippets/28-secciones-match-adultos-ninos.php' );
$css    = file_get_contents( $base . 'css/secciones-match-adultos-ninos.css' );
// Bloque "Fandom e interacción" (18/10/2026): va siempre después del de secciones.
if ( file_exists( $base . 'css/fandom-interaccion.css' ) ) {
	$css = rtrim( $css ) . "\n\n" . ltrim( file_get_contents( $base . 'css/fandom-interaccion.css' ) );
}
// Bloque "Rediseño 2026" (09/11/2026): siempre al final, ajusta todo lo anterior.
if ( file_exists( $base . 'css/rediseno-2026.css' ) ) {
	$css = rtrim( $css ) . "\n\n" . ltrim( file_get_contents( $base . 'css/rediseno-2026.css' ) );
}
if ( ! $codigo || ! $css ) {
	exit( "ERROR: no se pudieron leer los archivos.\n" );
}
$marca = '/* ---------- Secciones Match, Adultos y Niños ---------- */';

$cambios_texto = array(
	'Disponible en modelos Hombre, Mujer, Niño y Niña.'            => 'Unisex, en tallas de adulto (S a XXL) y de niños (10 a 14).',
	'<strong>Modelos:</strong> Hombre, Mujer, Niño y Niña.'       => '<strong>Corte:</strong> unisex, para toda la familia (Colección Match).',
);
$textos = array();
foreach ( get_posts( array( 'post_type' => 'product', 'post_status' => 'publish', 'numberposts' => -1 ) ) as $p ) {
	$excerpt = strtr( $p->post_excerpt, $cambios_texto );
	$content = strtr( $p->post_content, $cambios_texto );
	// Demon Slayer tiene la línea con otro formato (sin punto final y con atributos).
	$content = preg_replace( '/(<strong[^>]*>)Modelos:(<\/strong>) Hombre, Mujer, Niño y Niña/u', '$1Corte:$2 unisex, para toda la familia (Colección Match)', $content );
	if ( $excerpt !== $p->post_excerpt || $content !== $p->post_content ) {
		$textos[ $p->ID ] = array( $p, $excerpt, $content );
	}
}
echo count( $textos ), " polos con texto para actualizar.\n";
if ( ! $apply ) {
	echo "Modo revisión: no se aplicó nada.\n";
	exit;
}

// Snippet #28.
$s = \Code_Snippets\get_snippet( 28 );
if ( "D'FANS - Secciones Match, Adultos y Niños" !== $s->name ) {
	exit( "ERROR: el snippet 28 no es el de secciones.\n" );
}
$s->code = $codigo;
\Code_Snippets\save_snippet( $s );
$s = \Code_Snippets\get_snippet( 28 );
echo '#28 activo=', $s->active ? 'si' : 'NO', ' ruta=', false !== strpos( $s->code, 'woocommerce_breadcrumb_main_term' ) ? 'si' : 'no', "\n";

// CSS.
$g   = get_post( 29 );
$j   = json_decode( $g->post_content, true );
$pos = strpos( $j['styles']['css'], $marca );
if ( false === $pos ) {
	exit( "ERROR: no está el bloque de secciones en el CSS.\n" );
}
$j['styles']['css'] = rtrim( substr( $j['styles']['css'], 0, $pos ) ) . "\n\n" . ltrim( $css );
wp_update_post( array( 'ID' => 29, 'post_content' => wp_slash( wp_json_encode( $j ) ) ) );
echo 'CSS: ', strlen( json_decode( get_post( 29 )->post_content, true )['styles']['css'] ), " caracteres\n";

// Textos.
$copia = array();
foreach ( $textos as $id => list( $p, $excerpt, $content ) ) {
	$copia[ $id ] = array( 'post_excerpt' => $p->post_excerpt, 'post_content' => $p->post_content );
	wp_update_post( array( 'ID' => $id, 'post_excerpt' => wp_slash( $excerpt ), 'post_content' => wp_slash( $content ) ) );
}
if ( ! file_exists( $base . 'fase1/textos-antes.json' ) ) {
	file_put_contents( $base . 'fase1/textos-antes.json', wp_json_encode( $copia, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) );
}
echo count( $copia ), " textos actualizados.\n";
