<?php
/**
 * D'FANS - Instala y activa el plugin oficial "Mercado Pago payments for WooCommerce" desde WordPress.org (18/10/2026).
 * No guarda credenciales: las claves de prueba las pega el usuario en WooCommerce → Ajustes → Pagos → Mercado Pago.
 * Uso: php instalar_mercadopago.php [--apply]
 */
define( 'WP_USE_THEMES', false );
$_SERVER['HTTP_HOST']   = 'dfans.local';
$_SERVER['REQUEST_URI'] = '/';
require 'C:/Users/GRLL/Local Sites/dfans/app/public/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/plugin.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/misc.php';
require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
wp_set_current_user( 1 );

$apply = in_array( '--apply', $argv, true );
$slug  = 'woocommerce-mercadopago';
$info  = plugins_api( 'plugin_information', array( 'slug' => $slug, 'fields' => array( 'sections' => false ) ) );
if ( is_wp_error( $info ) ) {
	exit( 'ERROR consultando WordPress.org: ' . $info->get_error_message() . "\n" );
}
echo "{$info->name} {$info->version} (autor: " . wp_strip_all_tags( $info->author ) . ")\nDescarga: {$info->download_link}\n";

$archivo = null;
foreach ( array_keys( get_plugins() ) as $p ) {
	if ( 0 === strpos( $p, $slug . '/' ) ) {
		$archivo = $p;
	}
}
echo 'Instalado: ', $archivo ? "sí ($archivo)" : 'no', "\n";
if ( ! $apply ) {
	exit( "Modo revisión: no se aplicó nada.\n" );
}

if ( ! $archivo ) {
	$upgrader = new Plugin_Upgrader( new Automatic_Upgrader_Skin() );
	$ok       = $upgrader->install( $info->download_link );
	if ( ! $ok || is_wp_error( $ok ) ) {
		exit( 'ERROR instalando: ' . ( is_wp_error( $ok ) ? $ok->get_error_message() : 'desconocido' ) . "\n" );
	}
	$archivo = $upgrader->plugin_info();
	echo "Instalado: $archivo\n";
}
$r = activate_plugin( $archivo );
echo is_wp_error( $r ) ? 'ERROR activando: ' . $r->get_error_message() . "\n" : "Activado.\n";
