<?php
/** D'FANS - Convierte a WebP los tamaños de todas las fotos (local). Uso: php webp_todo.php */
define( 'WP_USE_THEMES', false );
$_SERVER['HTTP_HOST'] = 'dfans.local';
require 'C:/Users/GRLL/Local Sites/dfans/app/public/wp-load.php';
global $wpdb;
$ids = $wpdb->get_col( "SELECT p.ID FROM {$wpdb->posts} p LEFT JOIN {$wpdb->postmeta} m ON m.post_id=p.ID AND m.meta_key='_dfans_webp' WHERE p.post_type='attachment' AND p.post_mime_type IN ('image/jpeg','image/png') AND m.meta_id IS NULL ORDER BY p.ID" );
echo count( $ids ), " por convertir\n";
$ok = 0;
foreach ( $ids as $i => $id ) {
	if ( dfans_webp_convertir( (int) $id ) ) { $ok++; }
	if ( 0 === $i % 50 ) { echo "$i...\n"; }
}
echo "Listas: $ok\n";
