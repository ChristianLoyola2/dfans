<?php
/**
 * Exporta al repositorio (carpeta dfans-personalizacion/) el código propio de D'FANS que vive en la base de datos:
 * snippets activos de Code Snippets, CSS adicional de los estilos globales y el HTML de bloques de la portada,
 * cabecera, pie y plantillas de secciones. No exporta ajustes, pedidos, clientes ni claves (03/11/2026).
 */
define( 'WP_USE_THEMES', false );
$_SERVER['HTTP_HOST'] = 'dfans.local';
require 'C:/Users/GRLL/Local Sites/dfans/app/public/wp-load.php';
global $wpdb;
$dest = 'C:/Users/GRLL/Local Sites/dfans/app/public/dfans-personalizacion';
foreach ( array( '', '/snippets', '/css', '/bloques' ) as $d ) {
	wp_mkdir_p( $dest . $d );
}
foreach ( $wpdb->get_results( "SELECT id, name, description, code, scope, priority FROM {$wpdb->prefix}snippets WHERE active=1 ORDER BY id" ) as $s ) {
	$nombre = sprintf( '%02d-%s.php', $s->id, sanitize_title( remove_accents( str_replace( array( "D'FANS - ", " - D'Fans" ), '', $s->name ) ) ) );
	$cab    = "<?php\n/**\n * Snippet #{$s->id}: {$s->name}\n * Alcance: {$s->scope} · prioridad {$s->priority}\n * Exportado de Code Snippets el " . date( 'd/m/Y' ) . ". La copia activa está en la base de datos.\n */\n\n";
	file_put_contents( "$dest/snippets/$nombre", $cab . ltrim( preg_replace( '/^<\?php\s*/', '', $s->code ) ) . "\n" );
	echo "snippet $nombre\n";
}
$gs  = json_decode( get_post( 29 )->post_content, true );
$css = $gs['styles']['css'] ?? '';
file_put_contents( "$dest/css/estilos-globales-css-adicional.css", "/* CSS adicional de los estilos globales (post 29), exportado el " . date( 'd/m/Y' ) . " */\n" . $css );
echo 'css ', strlen( $css ), " bytes\n";
foreach ( array( 60 => 'portada-inicio', 111 => 'cabecera', 112 => 'pie', 1102 => 'plantilla-ropa-de-adultos', 1103 => 'plantilla-ropa-para-ninos' ) as $id => $n ) {
	$p = get_post( $id );
	if ( $p ) {
		file_put_contents( "$dest/bloques/$n.html", $p->post_content );
		echo "bloques $n.html\n";
	}
}
