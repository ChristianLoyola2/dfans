<?php
/**
 * D'FANS - Fandom e interacción (18/10/2026)
 *  - Snippet "D'FANS - Fandom e interacción" desde backups/snippets/29-fandom-interaccion.php (lo crea o lo actualiza).
 *  - Portada (60): sección del quiz "¿Cuál es tu fandom?" antes de "Para toda la familia".
 *  - Página "Mis favoritos" (/favoritos/) con [dfans_favoritos].
 * El CSS se aplica con fase1_ajustes.php (incluye css/fandom-interaccion.css). Copias en backups/fase3/.
 * Uso: php aplicar_fandom.php [--apply]
 */
define( 'CODE_SNIPPETS_SAFE_MODE', true );
define( 'WP_USE_THEMES', false );
$_SERVER['HTTP_HOST']   = 'dfans.local';
$_SERVER['REQUEST_URI'] = '/';
require 'C:/Users/GRLL/Local Sites/dfans/app/public/wp-load.php';
wp_set_current_user( 1 );

$apply  = in_array( '--apply', $argv, true );
$base   = 'C:/Users/GRLL/Local Sites/dfans/backups/';
$copia  = $base . 'fase3/';
$codigo = file_get_contents( $base . 'snippets/29-fandom-interaccion.php' );
if ( ! $codigo ) {
	exit( "ERROR: no se pudo leer el snippet.\n" );
}
$nombre = "D'FANS - Fandom e interacción";

$quiz = '<!-- wp:group {"align":"full","className":"dfans-seccion dfans-seccion-quiz","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull dfans-seccion dfans-seccion-quiz"><!-- wp:shortcode -->
[dfans_quiz]
<!-- /wp:shortcode --></div>
<!-- /wp:group -->

';

$inicio = get_post( 60 )->post_content;
$ancla  = strpos( $inicio, '<p class="dfans-eyebrow">Para toda la familia</p>' );
$antes  = false !== $ancla ? strrpos( substr( $inicio, 0, $ancla ), '<!-- wp:group {"align":"full"' ) : false;
echo 'Portada: ', false !== strpos( $inicio, '[dfans_quiz]' ) ? "el quiz ya está.\n" : ( false !== $antes ? "se inserta el quiz antes de 'Para toda la familia'.\n" : "NO se encontró dónde insertar el quiz.\n" );
echo 'Página favoritos: ', get_page_by_path( 'favoritos' ) ? "ya existe.\n" : "se crea.\n";
if ( ! $apply ) {
	exit( "Modo revisión: no se aplicó nada.\n" );
}
wp_mkdir_p( $copia );

// Snippet.
$sn = null;
foreach ( \Code_Snippets\get_snippets() as $s ) {
	if ( $nombre === $s->name ) {
		$sn = $s;
	}
}
if ( ! $sn ) {
	$sn         = new \Code_Snippets\Model\Snippet();
	$sn->name   = $nombre;
	$sn->desc   = 'Barra de anuncios, quiz, favoritos, vista rápida, guía de tallas, barra de compra móvil, vistos recientemente y animaciones.';
	$sn->scope  = 'global';
	$sn->active = true;
} else {
	file_put_contents( $copia . '29-fandom-interaccion.ANTERIOR.php', "<?php\n" . $sn->code );
}
$sn->code = $codigo;
$sn       = \Code_Snippets\save_snippet( $sn );
$sn       = \Code_Snippets\get_snippet( $sn->id );
echo "Snippet #{$sn->id} activo=", $sn->active ? 'si' : 'NO', "\n";

// Portada.
if ( false === strpos( $inicio, '[dfans_quiz]' ) && false !== $antes ) {
	file_put_contents( $copia . 'post-60.ANTES-QUIZ.html', $inicio );
	wp_update_post( array( 'ID' => 60, 'post_content' => wp_slash( substr_replace( $inicio, $quiz, $antes, 0 ) ) ) );
	echo "Quiz añadido a la portada.\n";
}

// Página de favoritos.
if ( ! get_page_by_path( 'favoritos' ) ) {
	$id = wp_insert_post( array(
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_title'   => 'Mis favoritos',
		'post_name'    => 'favoritos',
		'post_content' => "<!-- wp:paragraph -->\n<p>Los polos que marcaste con ♥. Se guardan en este navegador.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:shortcode -->\n[dfans_favoritos]\n<!-- /wp:shortcode -->",
	) );
	echo "Página Mis favoritos creada (#$id).\n";
}
