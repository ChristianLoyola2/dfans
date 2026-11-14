<?php
/**
 * D'FANS - Crea (o actualiza) el snippet "D'FANS - GA4 comercio electrónico" desde snippets/32-ga4-comercio-electronico.php.
 * Uso: php crear_snippet_ga4.php
 */
define( 'CODE_SNIPPETS_SAFE_MODE', true );
define( 'WP_USE_THEMES', false );
$_SERVER['HTTP_HOST']   = 'dfans.local';
$_SERVER['REQUEST_URI'] = '/';
require 'C:/Users/GRLL/Local Sites/dfans/app/public/wp-load.php';
wp_set_current_user( 1 );
$nombre = "D'FANS - GA4 comercio electrónico";
$codigo = file_get_contents( 'C:/Users/GRLL/Local Sites/dfans/backups/snippets/32-ga4-comercio-electronico.php' );
$s      = null;
foreach ( \Code_Snippets\get_snippets() as $x ) {
	if ( $nombre === $x->name ) {
		$s = $x;
	}
}
if ( ! $s ) {
	$s = new \Code_Snippets\Model\Snippet();
	$s->name  = $nombre;
	$s->scope = 'global';
	$s->desc  = 'Eventos de comercio electrónico de GA4: view_item, add_to_cart, view_cart, begin_checkout y purchase.';
}
$s->code   = $codigo;
$s->active = true;
$s         = \Code_Snippets\get_snippet( \Code_Snippets\save_snippet( $s )->id );
echo "Snippet #{$s->id} {$s->name}: activo=", $s->active ? 'sí' : 'NO', ' error=', $s->code_error ? wp_json_encode( $s->code_error ) : 'ninguno', "\n";
