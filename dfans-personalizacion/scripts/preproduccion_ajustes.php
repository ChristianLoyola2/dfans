<?php
/**
 * Ajustes antes de producción (03/11/2026). Guarda los valores anteriores en backups/preproduccion-ajustes-ANTES.json.
 * - Nombre y descripción del sitio, correos del administrador y remitente de WooCommerce (estaban en dev-email@wpengine.local).
 * - Dirección de la tienda e ícono del sitio (favicon).
 * Uso: php preproduccion_ajustes.php [--apply]
 */
define( 'WP_USE_THEMES', false );
$_SERVER['HTTP_HOST'] = 'dfans.local';
require 'C:/Users/GRLL/Local Sites/dfans/app/public/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
wp_set_current_user( 1 );

$correo  = 'cloyola794@gmail.com'; // el mismo de los avisos de pedidos y de los datos legales (snippet #26)
$valores = array(
	'blogname'                           => 'D’FANS',
	'blogdescription'                    => 'Polos de anime, rock, metal, artistas, películas y videojuegos · Lima, Perú',
	'admin_email'                        => $correo,
	'woocommerce_email_from_name'        => 'D’FANS',
	'woocommerce_email_from_address'     => $correo,
	'woocommerce_stock_email_recipient'  => $correo,
	'woocommerce_store_address'          => 'Jr. Agustín Gamarra 1215',
	'woocommerce_store_city'             => 'La Victoria, Lima',
);
$antes = array();
foreach ( array_merge( array_keys( $valores ), array( 'site_icon', 'new_admin_email' ) ) as $k ) {
	$antes[ $k ] = get_option( $k );
}
if ( ! in_array( '--apply', $argv, true ) ) {
	foreach ( $valores as $k => $v ) {
		echo str_pad( $k, 36 ), wp_json_encode( $antes[ $k ], JSON_UNESCAPED_UNICODE ), ' -> ', $v, "\n";
	}
	echo 'site_icon: ', $antes['site_icon'], " -> favicon nuevo\n";
	exit;
}
file_put_contents( 'C:/Users/GRLL/Local Sites/dfans/backups/preproduccion-ajustes-ANTES.json', wp_json_encode( $antes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) );
foreach ( $valores as $k => $v ) {
	update_option( $k, $v );
}
delete_option( 'new_admin_email' ); delete_option( 'adminhash' );

if ( ! get_option( 'site_icon' ) ) {
	$tmp = wp_tempnam( 'favicon-dfans.png' );
	copy( 'C:/Users/GRLL/Local Sites/dfans/backups/generadas/favicon-dfans.png', $tmp );
	$id = media_handle_sideload( array( 'name' => 'favicon-dfans.png', 'tmp_name' => $tmp ), 0, 'Ícono del sitio D’FANS' );
	if ( is_wp_error( $id ) ) {
		echo 'Favicon ERROR: ', $id->get_error_message(), "\n";
	} else {
		update_post_meta( $id, '_wp_attachment_context', 'site-icon' );
		update_option( 'site_icon', $id );
		echo "Favicon: adjunto $id\n";
	}
}
echo "Ajustes aplicados.\n";
