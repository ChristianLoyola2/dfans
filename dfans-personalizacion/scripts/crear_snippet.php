<?php
/**
 * D'FANS - Crea o actualiza un snippet de Code Snippets desde un archivo de backups/snippets/.
 * Uso: php crear_snippet.php "<nombre del snippet>" <archivo.php> "<descripción>"
 */
define( 'CODE_SNIPPETS_SAFE_MODE', true );
define( 'WP_USE_THEMES', false );
$_SERVER['HTTP_HOST']   = 'dfans.local';
$_SERVER['REQUEST_URI'] = '/';
require 'C:/Users/GRLL/Local Sites/dfans/app/public/wp-load.php';
wp_set_current_user( 1 );
list( , $nombre, $archivo, $desc ) = array_pad( $argv, 4, '' );
if ( ! $nombre || ! $archivo ) {
	exit( "Uso: php crear_snippet.php \"nombre\" archivo.php \"descripción\"\n" );
}
$codigo = file_get_contents( 'C:/Users/GRLL/Local Sites/dfans/backups/snippets/' . $archivo );
$s      = null;
foreach ( \Code_Snippets\get_snippets() as $x ) {
	if ( $nombre === $x->name ) {
		$s = $x;
	}
}
if ( ! $s ) {
	$s        = new \Code_Snippets\Model\Snippet();
	$s->name  = $nombre;
	$s->scope = 'global';
	$s->desc  = $desc;
}
$s->code   = $codigo;
$s->active = true;
$s         = \Code_Snippets\get_snippet( \Code_Snippets\save_snippet( $s )->id );
echo "Snippet #{$s->id} {$s->name}: activo=", $s->active ? 'sí' : 'NO', ' error=', $s->code_error ? wp_json_encode( $s->code_error ) : 'ninguno', "\n";
