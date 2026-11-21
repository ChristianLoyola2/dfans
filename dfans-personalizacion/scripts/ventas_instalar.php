<?php
/**
 * D'FANS - Configura lo que usa el snippet #34 «Ventas y confianza» (21/11/2026). Se puede correr varias veces.
 * - Delivery gratis en la zona Lima desde S/ 120 (método free_shipping).
 * - Cupón BIENVENIDO10: 10%, mínimo S/ 60, una vez por cliente.
 * - Reseñas solo de clientes que compraron el polo (el valor anterior queda en dfans_resenas_antes).
 */
define( 'WP_USE_THEMES', false );
$_SERVER['HTTP_HOST']   = 'dfans.local';
$_SERVER['REQUEST_URI'] = '/';
require 'C:/Users/GRLL/Local Sites/dfans/app/public/wp-load.php';

// 1. Delivery gratis en Lima.
$lima = null;
foreach ( WC_Shipping_Zones::get_zones() as $z ) {
	if ( 'Lima' === $z['zone_name'] ) {
		$lima = new WC_Shipping_Zone( $z['id'] );
	}
}
if ( ! $lima ) {
	exit( "No encontré la zona Lima.\n" );
}
$metodo = null;
foreach ( $lima->get_shipping_methods() as $m ) {
	if ( 'free_shipping' === $m->id ) {
		$metodo = $m;
	}
}
if ( ! $metodo ) {
	$instancia = $lima->add_shipping_method( 'free_shipping' );
	foreach ( $lima->get_shipping_methods() as $m ) {
		if ( (int) $m->instance_id === (int) $instancia ) {
			$metodo = $m;
		}
	}
}
$opciones = array_merge( (array) get_option( $metodo->get_instance_option_key(), array() ), array(
	'title'            => '🚚 Delivery en Lima — Gratis',
	'requires'         => 'min_amount',
	'min_amount'       => '120',
	'ignore_discounts' => 'no',
) );
update_option( $metodo->get_instance_option_key(), $opciones );
echo "Delivery gratis en Lima: método #{$metodo->instance_id}, desde S/ 120\n";

// 2. Cupón de bienvenida.
$id = wc_get_coupon_id_by_code( 'bienvenido10' );
$c  = new WC_Coupon( $id ? $id : 0 );
$c->set_code( 'bienvenido10' );
$c->set_discount_type( 'percent' );
$c->set_amount( 10 );
$c->set_minimum_amount( 60 );
$c->set_usage_limit_per_user( 1 );
$c->set_individual_use( false );
$c->set_description( 'Bienvenida: 10% en la primera compra (ventanita del sitio). Solo primera compra, lo controla el snippet #34.' );
$c->save();
echo 'Cupón BIENVENIDO10 #', $c->get_id(), "\n";

// 3. Reseñas solo de compradores.
if ( false === get_option( 'dfans_resenas_antes' ) ) {
	update_option( 'dfans_resenas_antes', get_option( 'woocommerce_review_rating_verification_required' ) );
}
update_option( 'woocommerce_review_rating_verification_required', 'yes' );
echo 'Reseñas solo de compradores: ', get_option( 'woocommerce_review_rating_verification_required' ), ' (antes ', get_option( 'dfans_resenas_antes' ), ")\n";
