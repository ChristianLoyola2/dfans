<?php
/**
 * D'FANS - Vuelve a cargar el CSS "Rediseño 2026" y los snippets #29 y "Rediseño visual" desde sus archivos.
 * (aplicar_rediseno.php ya cambió la portada, el pie y las plantillas; esto es para ajustes posteriores.)
 * Uso: php rediseno_actualizar.php
 */
define( 'CODE_SNIPPETS_SAFE_MODE', true );
define( 'WP_USE_THEMES', false );
$_SERVER['HTTP_HOST']   = 'dfans.local';
$_SERVER['REQUEST_URI'] = '/';
require 'C:/Users/GRLL/Local Sites/dfans/app/public/wp-load.php';
wp_set_current_user( 1 );
$base = 'C:/Users/GRLL/Local Sites/dfans/backups/';

foreach ( array( "D'FANS - Fandom e interacción" => '29-fandom-interaccion.php', "D'FANS - Rediseño visual" => '31-rediseno-visual.php' ) as $nombre => $archivo ) {
	foreach ( \Code_Snippets\get_snippets() as $s ) {
		if ( $nombre === $s->name ) {
			$s->code = file_get_contents( $base . 'snippets/' . $archivo );
			$s       = \Code_Snippets\get_snippet( \Code_Snippets\save_snippet( $s )->id );
			echo "Snippet #{$s->id} {$nombre}: activo=", $s->active ? 'sí' : 'NO', "\n";
		}
	}
}

$gs    = get_post( 29 );
$j     = json_decode( $gs->post_content, true );
$marca = '/* ---------- Rediseño 2026 ---------- */';
$pos   = strpos( $j['styles']['css'], $marca );
if ( false === $pos ) {
	exit( "ERROR: falta el bloque Rediseño 2026 en el CSS global.\n" );
}
$j['styles']['css'] = rtrim( substr( $j['styles']['css'], 0, $pos ) ) . "\n\n" . ltrim( file_get_contents( $base . 'css/rediseno-2026.css' ) ) . ( file_exists( $base . 'css/rediseno-2026-v3.css' ) ? "

" . file_get_contents( $base . 'css/rediseno-2026-v3.css' ) : '' );
wp_update_post( array( 'ID' => 29, 'post_content' => wp_slash( wp_json_encode( $j, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ) ) );
global $wpdb;
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '\\_transient\\_wc\\_related\\_%' OR option_name LIKE '\\_transient\\_timeout\\_wc\\_related\\_%'" );
echo 'CSS global: ', strlen( json_decode( get_post( 29 )->post_content, true )['styles']['css'] ), " caracteres\n";
