<?php
/**
 * D'FANS - Sube el logo oficial a Medios y lo usa como ícono del sitio y en los correos.
 * Uso: php logo_instalar.php   (no duplica: si el archivo ya está en Medios, lo reutiliza)
 */
define( 'WP_USE_THEMES', false );
$_SERVER['HTTP_HOST']   = 'dfans.local';
$_SERVER['REQUEST_URI'] = '/';
require 'C:/Users/GRLL/Local Sites/dfans/app/public/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
wp_set_current_user( 1 );
$dir = 'C:/Users/GRLL/Desktop/machine/produccion-dfans/logo/';
$ids = array();
foreach ( array( 'dfans-logo-texto-blanco', 'dfans-logo-completo', 'dfans-logo-d-rojo', 'dfans-icono-512', 'dfans-logo-correo' ) as $n ) {
	global $wpdb;
	$id = (int) $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_wp_attached_file' AND meta_value LIKE %s LIMIT 1", '%/' . $n . '.png' ) );
	$viejo = (int) $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_wp_attached_file' AND meta_value LIKE %s LIMIT 1", '%/' . $n . '.webp' ) );
	if ( $viejo && in_array( '--borrar-webp', $argv, true ) ) {
		wp_delete_attachment( $viejo, true );
		echo "$n: borrada la copia WebP #$viejo
";
	}
	if ( ! $id ) {
		$tmp = wp_tempnam( $n . '.png' );
		copy( $dir . $n . '.png', $tmp );
		$id = media_handle_sideload( array( 'name' => $n . '.png', 'tmp_name' => $tmp ), 0, 'Logo D’FANS' );
		if ( is_wp_error( $id ) ) {
			exit( "$n: " . $id->get_error_message() . "\n" );
		}
		update_post_meta( $id, '_wp_attachment_image_alt', 'D’FANS' );
	}
	$ids[ $n ] = $id;
	echo "$n => #$id ", wp_get_attachment_url( $id ), "\n";
}
update_option( 'dfans_site_icon_antes', get_option( 'site_icon' ) );
update_option( 'site_icon', $ids['dfans-icono-512'] );
update_option( 'woocommerce_email_header_image', wp_get_attachment_url( $ids['dfans-logo-correo'] ) );
echo 'site_icon=', get_option( 'site_icon' ), ' (antes ', get_option( 'dfans_site_icon_antes' ), ")\n";
echo 'email header=', get_option( 'woocommerce_email_header_image' ), "\n";
