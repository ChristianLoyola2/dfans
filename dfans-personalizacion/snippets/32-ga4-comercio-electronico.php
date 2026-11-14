<?php
/**
 * Snippet #32: D'FANS - GA4 comercio electrónico
 * Alcance: global · prioridad 10
 * Exportado de Code Snippets el 14/11/2026. La copia activa está en la base de datos.
 */

/*
 * D'FANS - Google Analytics 4: eventos de comercio electrónico (10/11/2026)
 *
 * Usa el gtag que carga el snippet #30 (G-VDXZFSSXDC) y envía los eventos recomendados de GA4:
 *  - view_item       al abrir la ficha de un polo
 *  - add_to_cart     al añadir un polo al carrito (se guarda en la sesión y se envía en la página siguiente,
 *                    así funciona tanto con recarga como con el carrito por AJAX)
 *  - view_cart       al ver el carrito
 *  - begin_checkout  al entrar al checkout
 *  - purchase        en "Pedido recibido", una sola vez por pedido (meta _dfans_ga4_purchase); se imprime en el pie
 * Moneda PEN. No envía datos personales del cliente (solo productos, montos y número de pedido).
 */

/** Datos de un producto (o variación) en el formato "item" de GA4. */
function dfans_ga4_item( $producto, $cantidad = 1 ) {
	if ( ! $producto instanceof WC_Product ) {
		return null;
	}
	$padre     = $producto->get_parent_id() ? wc_get_product( $producto->get_parent_id() ) : $producto;
	$categoria = '';
	foreach ( wc_get_product_term_ids( $padre->get_id(), 'product_cat' ) as $cat ) {
		$tema = function_exists( 'dfans_tematica_de_categoria' ) ? dfans_tematica_de_categoria( $cat ) : null;
		if ( $tema ) {
			$categoria = $tema->name;
			break;
		}
	}
	$item = array(
		'item_id'   => $padre->get_sku() ? $padre->get_sku() : (string) $padre->get_id(),
		'item_name' => $padre->get_name(),
		'item_brand' => "D'FANS",
		'price'     => (float) wc_get_price_including_tax( $producto ),
		'quantity'  => (int) $cantidad,
	);
	if ( $categoria ) {
		$item['item_category'] = $categoria;
	}
	$item['item_category2'] = has_term( 'match-familiar', 'product_cat', $padre->get_id() ) ? 'Match familiar' : 'Exclusivo';
	if ( $producto->is_type( 'variation' ) ) {
		$item['item_variant'] = wc_get_formatted_variation( $producto, true, false, false );
	}
	return $item;
}

/** Imprime un evento de GA4 si gtag existe. */
function dfans_ga4_evento( $nombre, $datos ) {
	echo "<script>window.gtag&&gtag('event'," . wp_json_encode( $nombre ) . ',' . wp_json_encode( $datos ) . ");</script>\n";
}

// add_to_cart: se guarda en la sesión para enviarlo en la próxima página que se muestre.
add_action( 'woocommerce_add_to_cart', function ( $clave, $product_id, $cantidad, $variation_id ) {
	if ( ! WC()->session ) {
		return;
	}
	$item = dfans_ga4_item( wc_get_product( $variation_id ? $variation_id : $product_id ), $cantidad );
	if ( $item ) {
		$cola   = (array) WC()->session->get( 'dfans_ga4_carrito', array() );
		$cola[] = $item;
		WC()->session->set( 'dfans_ga4_carrito', $cola );
	}
}, 10, 4 );

add_action( 'wp_footer', function () {
	if ( is_admin() || ! function_exists( 'WC' ) ) {
		return;
	}
	// Eventos add_to_cart pendientes.
	if ( WC()->session ) {
		$cola = (array) WC()->session->get( 'dfans_ga4_carrito', array() );
		if ( $cola ) {
			$valor = 0;
			foreach ( $cola as $i ) {
				$valor += $i['price'] * $i['quantity'];
			}
			dfans_ga4_evento( 'add_to_cart', array( 'currency' => 'PEN', 'value' => round( $valor, 2 ), 'items' => array_values( $cola ) ) );
			WC()->session->set( 'dfans_ga4_carrito', array() );
		}
	}
	// view_item en la ficha.
	if ( is_product() ) {
		$producto = wc_get_product( get_queried_object_id() );
		$item     = dfans_ga4_item( $producto );
		if ( $item ) {
			$item['price'] = (float) ( $producto->is_type( 'variable' ) ? $producto->get_variation_price( 'min', true ) : wc_get_price_including_tax( $producto ) );
			dfans_ga4_evento( 'view_item', array( 'currency' => 'PEN', 'value' => $item['price'], 'items' => array( $item ) ) );
		}
		return;
	}
	// view_cart y begin_checkout con el contenido del carrito.
	$es_carrito  = is_cart();
	$es_checkout = is_checkout() && ! is_wc_endpoint_url( 'order-received' ) && ! is_wc_endpoint_url( 'order-pay' );
	if ( ( $es_carrito || $es_checkout ) && WC()->cart && ! WC()->cart->is_empty() ) {
		$items = array();
		foreach ( WC()->cart->get_cart() as $linea ) {
			$item = dfans_ga4_item( $linea['data'], $linea['quantity'] );
			if ( $item ) {
				$items[] = $item;
			}
		}
		dfans_ga4_evento( $es_carrito ? 'view_cart' : 'begin_checkout', array( 'currency' => 'PEN', 'value' => (float) WC()->cart->get_total( 'edit' ), 'items' => $items ) );
	}
}, 20 );

// purchase: en la página "Pedido recibido", una sola vez por pedido.
add_action( 'woocommerce_thankyou', function ( $order_id ) {
	$pedido = wc_get_order( $order_id );
	if ( ! $pedido || $pedido->get_meta( '_dfans_ga4_purchase' ) ) {
		return;
	}
	$items = array();
	foreach ( $pedido->get_items() as $linea ) {
		$item = dfans_ga4_item( $linea->get_product(), $linea->get_quantity() );
		if ( $item ) {
			$item['price'] = round( (float) $pedido->get_item_total( $linea, true ), 2 );
			$items[]       = $item;
		}
	}
	// El bloque de confirmación filtra los <script>, así que el evento se imprime en el pie (wp_footer).
	$GLOBALS['dfans_ga4_purchase'] = array(
		'transaction_id' => (string) $pedido->get_order_number(),
		'currency'       => 'PEN',
		'value'          => (float) $pedido->get_total(),
		'tax'            => (float) $pedido->get_total_tax(),
		'shipping'       => (float) $pedido->get_shipping_total(),
		'payment_type'   => $pedido->get_payment_method_title(),
		'items'          => $items,
	);
	$pedido->update_meta_data( '_dfans_ga4_purchase', current_time( 'mysql' ) );
	$pedido->save();
} );

add_action( 'wp_footer', function () {
	if ( ! empty( $GLOBALS['dfans_ga4_purchase'] ) ) {
		dfans_ga4_evento( 'purchase', $GLOBALS['dfans_ga4_purchase'] );
	}
}, 30 );

