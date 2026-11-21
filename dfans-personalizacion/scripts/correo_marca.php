<?php
/**
 * D'FANS - Colores de los correos de WooCommerce (21/11/2026). Guarda los valores anteriores en dfans_correo_antes.
 * Uso: php correo_marca.php
 */
define( 'WP_USE_THEMES', false );
$_SERVER['HTTP_HOST']   = 'dfans.local';
$_SERVER['REQUEST_URI'] = '/';
require 'C:/Users/GRLL/Local Sites/dfans/app/public/wp-load.php';
$nuevos = array(
	'woocommerce_email_auto_sync_with_theme'  => 'no', // si no, WooCommerce copia los colores del tema encima.
	'woocommerce_email_background_color'      => '#f2f2f0',
	'woocommerce_email_body_background_color' => '#ffffff',
	'woocommerce_email_base_color'            => '#e0262f',
	'woocommerce_email_text_color'            => '#3d3d3a',
	'woocommerce_email_footer_text_color'     => '#a3a39f',
	'woocommerce_email_header_image_width'    => '600',
	'woocommerce_email_header_alignment'      => 'center',
);
$antes = array();
foreach ( $nuevos as $k => $v ) {
	$antes[ $k ] = get_option( $k );
	update_option( $k, $v );
	echo "$k: {$antes[$k]} -> $v\n";
}
if ( ! get_option( 'dfans_correo_antes' ) ) {
	update_option( 'dfans_correo_antes', $antes );
}
