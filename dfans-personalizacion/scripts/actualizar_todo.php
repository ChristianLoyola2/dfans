<?php
/**
 * Actualiza WordPress, plugins, temas y traducciones desde WordPress.org (03/11/2026).
 * Antes: copia de la BD en backups/ y los archivos quedan versionados en Git (rama main).
 * Uso: php actualizar_todo.php [--apply]
 */
define( 'WP_USE_THEMES', false );
$_SERVER['HTTP_HOST']   = 'dfans.local';
$_SERVER['REQUEST_URI'] = '/';
require 'C:/Users/GRLL/Local Sites/dfans/app/public/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/admin.php';
require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
wp_set_current_user( 1 );
@set_time_limit( 0 );

delete_site_transient( 'update_core' );
delete_site_transient( 'update_plugins' );
delete_site_transient( 'update_themes' );
wp_version_check( array(), true );
wp_update_plugins();
wp_update_themes();

$core    = get_preferred_from_update_core();
$plugins = array_keys( (array) get_site_transient( 'update_plugins' )->response );
$temas   = array_keys( (array) get_site_transient( 'update_themes' )->response );
echo 'WordPress: ', get_bloginfo( 'version' ), ' -> ', ( $core && 'upgrade' === $core->response ) ? $core->current : 'al día', "\n";
foreach ( $plugins as $p ) {
	echo "Plugin: $p ", get_plugins()[ $p ]['Version'], ' -> ', get_site_transient( 'update_plugins' )->response[ $p ]->new_version, "\n";
}
foreach ( $temas as $t ) {
	echo "Tema: $t\n";
}
if ( ! in_array( '--apply', $argv, true ) ) {
	exit( "Modo revisión.\n" );
}

$skin = new Automatic_Upgrader_Skin();
if ( $plugins ) {
	$r = ( new Plugin_Upgrader( $skin ) )->bulk_upgrade( $plugins );
	foreach ( (array) $r as $p => $ok ) {
		echo 'Plugin ', $p, ': ', ( $ok && ! is_wp_error( $ok ) ) ? 'actualizado' : 'ERROR ' . ( is_wp_error( $ok ) ? $ok->get_error_message() : '' ), "\n";
	}
}
if ( $temas ) {
	( new Theme_Upgrader( $skin ) )->bulk_upgrade( $temas );
	echo "Temas actualizados.\n";
}
if ( $core && 'upgrade' === $core->response ) {
	$r = ( new Core_Upgrader( $skin ) )->upgrade( $core );
	echo 'WordPress: ', is_wp_error( $r ) ? 'ERROR ' . $r->get_error_message() : "actualizado a $r", "\n";
}
wp_clean_update_cache();
$tr = ( new Language_Pack_Upgrader( $skin ) )->bulk_upgrade();
echo 'Traducciones: ', is_array( $tr ) ? count( $tr ) : 0, " paquetes\n";
foreach ( array_slice( $skin->get_upgrade_messages(), -8 ) as $m ) {
	echo '  · ', wp_strip_all_tags( $m ), "\n";
}
