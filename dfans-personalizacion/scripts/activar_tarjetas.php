<?php
/**
 * D'FANS - Activa el pago con tarjeta de Mercado Pago (Checkout API, woo-mercado-pago-custom) en modo de prueba (10/11/2026).
 * Las credenciales las vinculó el usuario desde el plugin; aquí no se leen ni se escriben claves.
 * Deja apagados los demás medios de Mercado Pago (la tienda ya tiene su propio Yape con QR).
 * Uso: php activar_tarjetas.php [--apply]
 */
define( 'WP_USE_THEMES', false );
$_SERVER['HTTP_HOST'] = 'dfans.local';
require 'C:/Users/GRLL/Local Sites/dfans/app/public/wp-load.php';
wp_set_current_user( 1 );
$pasarelas = WC()->payment_gateways()->payment_gateways();
echo 'Modo de prueba de Mercado Pago: ', get_option( 'checkbox_checkout_test_mode', 'yes' ), "\n";
foreach ( $pasarelas as $id => $g ) {
	if ( 0 === strpos( $id, 'woo-mercado-pago' ) || in_array( $id, array( 'cod', 'bacs' ), true ) ) {
		echo str_pad( $id, 28 ), $g->enabled, "  {$g->title}\n";
	}
}
if ( ! in_array( '--apply', $argv, true ) ) {
	exit( "Modo revisión.\n" );
}
$antes = array();
foreach ( $pasarelas as $id => $g ) {
	if ( 0 === strpos( $id, 'woo-mercado-pago' ) ) {
		$antes[ $id ] = get_option( "woocommerce_{$id}_settings" );
	}
}
file_put_contents( 'C:/Users/GRLL/Local Sites/dfans/backups/rediseno/mercadopago-pasarelas.ANTES-' . date( 'Ymd-Hi' ) . '.json', wp_json_encode( $antes ) );
update_option( 'checkbox_checkout_test_mode', 'yes' );
$tarjeta = $pasarelas['woo-mercado-pago-custom'];
$tarjeta->update_option( 'enabled', 'yes' );
$tarjeta->update_option( 'title', 'Tarjeta de crédito o débito' );
echo "Tarjetas activadas (modo de prueba).\n";
