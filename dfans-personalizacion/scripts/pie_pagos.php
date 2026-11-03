<?php
/** Pie (parte 112): "Pagos: Yape" -> "Pagos: Yape y transferencia bancaria" (02/11/2026). Copia previa en backups/fase2/. */
define( 'WP_USE_THEMES', false );
$_SERVER['HTTP_HOST'] = 'dfans.local';
require 'C:/Users/GRLL/Local Sites/dfans/app/public/wp-load.php';
wp_set_current_user( 1 );
$p = get_post( 112 );
$viejo = '<strong>Pagos:</strong> Yape</p>';
if ( 1 !== substr_count( $p->post_content, $viejo ) ) {
	exit( "Texto no encontrado o repetido; no se cambia nada.\n" );
}
file_put_contents( 'C:/Users/GRLL/Local Sites/dfans/backups/fase2/post-112.ANTES-PAGOS.html', $p->post_content );
$r = wp_update_post( array( 'ID' => 112, 'post_content' => str_replace( $viejo, '<strong>Pagos:</strong> Yape y transferencia bancaria</p>', $p->post_content ) ), true );
echo is_wp_error( $r ) ? 'ERROR: ' . $r->get_error_message() . "\n" : "Pie actualizado.\n";
