<?php
/**
 * D'FANS - Menú sin submenús de temáticas (01/10/2026)
 * "Adultos" y "Niños" pasan a ser enlaces directos; las temáticas se eligen con los filtros de cada sección.
 * Copia del menú anterior en backups/fase2/navegacion-4.ANTES-SIN-SUBMENUS.html.
 * Uso: php menu_sin_submenus.php [--apply]
 */
define( 'WP_USE_THEMES', false );
$_SERVER['HTTP_HOST']   = 'dfans.local';
$_SERVER['REQUEST_URI'] = '/';
require 'C:/Users/GRLL/Local Sites/dfans/app/public/wp-load.php';
wp_set_current_user( 1 );

$apply = in_array( '--apply', $argv, true );
$nav   = get_post( 4 );

// Cada submenú (con sus enlaces) se reemplaza por un enlace con los mismos datos.
$nuevo = preg_replace_callback(
	'#<!-- wp:navigation-submenu (\{.*?\}) -->.*?<!-- /wp:navigation-submenu -->#s',
	function ( $m ) {
		return '<!-- wp:navigation-link ' . $m[1] . ' /-->';
	},
	$nav->post_content,
	-1,
	$cambios
);
echo "Submenús convertidos en enlace: $cambios\n";
if ( ! $apply || ! $cambios ) {
	exit( $apply ? "Nada que cambiar.\n" : "Modo revisión: no se aplicó nada.\n" );
}
wp_mkdir_p( 'C:/Users/GRLL/Local Sites/dfans/backups/fase2/' );
file_put_contents( 'C:/Users/GRLL/Local Sites/dfans/backups/fase2/navegacion-4.ANTES-SIN-SUBMENUS.html', $nav->post_content );
wp_update_post( array( 'ID' => 4, 'post_content' => wp_slash( $nuevo ) ) );
echo "Menú actualizado.\n";
