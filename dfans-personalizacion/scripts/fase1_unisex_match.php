<?php
/**
 * D'FANS - Fase 1: polos unisex y colección Match (30/09/2026)
 *
 * Por cada polo variable con modelos Hombre/Mujer/Niño/Niña:
 *  - Guarda las fotos de los 4 modelos en el meta _dfans_fotos (no borra ninguna imagen).
 *  - Hombre + talla  -> talla de Adulto unisex (S-XXL); Niño + talla -> talla de Niños unisex (10-14).
 *  - Elimina las variaciones de Mujer y Niña (mismas prendas, ahora unisex).
 *  - "Modelo" deja de ser una opción de compra; queda como dato oculto "Para" (sirve para filtrar).
 *  - SKU sin la letra del modelo (DF-DB-H-M -> DF-DB-M, DF-DB-NO-10 -> DF-DB-10). Stock 20 por talla.
 *  - Añade el polo a la categoría nueva "Match familiar".
 * Copia de las variaciones en backups/fase1/variaciones-antes.json.
 * Uso: php fase1_unisex_match.php [--apply]
 */
define( 'WP_USE_THEMES', false );
$_SERVER['HTTP_HOST']   = 'dfans.local';
$_SERVER['REQUEST_URI'] = '/';
require 'C:/Users/GRLL/Local Sites/dfans/app/public/wp-load.php';
wp_set_current_user( 1 );

$apply  = in_array( '--apply', $argv, true );
$dir    = 'C:/Users/GRLL/Local Sites/dfans/backups/fase1/';
$quedan = array( 'hombre', 'nino' );
$se_van = array( 'mujer', 'nina' );

$ids = get_posts( array( 'post_type' => 'product', 'post_status' => 'publish', 'numberposts' => -1, 'fields' => 'ids', 'orderby' => 'ID', 'order' => 'ASC' ) );

$plan   = array();
$copia  = array();
$errores = array();
foreach ( $ids as $pid ) {
	$prod = wc_get_product( $pid );
	if ( ! $prod->is_type( 'variable' ) || ! isset( $prod->get_attributes()['pa_modelo'] ) ) {
		continue;
	}
	$fotos = array();
	$vars  = array();
	foreach ( $prod->get_children() as $vid ) {
		$v      = wc_get_product( $vid );
		$attrs  = $v->get_attributes();
		$modelo = $attrs['pa_modelo'] ?? '';
		$talla  = $attrs['pa_talla'] ?? '';
		$vars[] = array( $vid, $modelo, $talla );
		$copia[ $pid ][] = array(
			'id' => $vid, 'modelo' => $modelo, 'talla' => $talla, 'sku' => $v->get_sku(), 'precio' => $v->get_regular_price(),
			'stock' => $v->get_stock_quantity(), 'imagen' => $v->get_image_id(), 'galeria' => $v->get_gallery_image_ids(),
		);
		if ( $modelo && ! isset( $fotos[ $modelo ] ) ) {
			$fotos[ $modelo ] = array( 'delantera' => (int) $v->get_image_id(), 'espalda' => (int) ( $v->get_gallery_image_ids()[0] ?? 0 ) );
		}
	}
	$conteo = array_count_values( array_column( $vars, 1 ) );
	$ok     = 5 === ( $conteo['hombre'] ?? 0 ) && 5 === ( $conteo['mujer'] ?? 0 ) && 3 === ( $conteo['nino'] ?? 0 ) && 3 === ( $conteo['nina'] ?? 0 );
	foreach ( array( 'hombre', 'mujer', 'nino', 'nina' ) as $m ) {
		if ( empty( $fotos[ $m ]['delantera'] ) || empty( $fotos[ $m ]['espalda'] ) ) {
			$ok = false;
		}
	}
	if ( ! $ok ) {
		$errores[] = "$pid {$prod->get_name()}: " . wp_json_encode( $conteo ) . ' fotos=' . wp_json_encode( $fotos );
		continue;
	}
	$plan[ $pid ] = array( $prod, $fotos, $vars );
}

echo count( $plan ), " polos para convertir.\n";
if ( $errores ) {
	echo "No cumplen la estructura esperada (no se tocan):\n  ", implode( "\n  ", $errores ), "\n";
}
if ( ! $apply ) {
	echo "Modo revisión: no se aplicó nada.\n";
	exit;
}
if ( $errores ) {
	exit( "Se detiene: revisar los polos de arriba antes de aplicar.\n" );
}

wp_mkdir_p( $dir );
if ( ! file_exists( $dir . 'variaciones-antes.json' ) ) {
	file_put_contents( $dir . 'variaciones-antes.json', wp_json_encode( $copia, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) );
}

// Categoría Match familiar.
$match = get_term_by( 'slug', 'match-familiar', 'product_cat' );
if ( ! $match ) {
	$r     = wp_insert_term( 'Match familiar', 'product_cat', array(
		'slug'        => 'match-familiar',
		'description' => 'El mismo polo para toda la familia: adultos en tallas S a XXL y niños en tallas 10 a 14. Cómpralo para hacer match o solo para ti.',
	) );
	$match = get_term( $r['term_id'], 'product_cat' );
}

// "Modelo" pasa a llamarse "Para" (el slug pa_modelo no cambia).
wc_update_attribute( wc_attribute_taxonomy_id_by_name( 'pa_modelo' ), array( 'name' => 'Para', 'slug' => 'modelo', 'type' => 'select', 'order_by' => 'menu_order', 'has_archives' => false ) );

$terminos_para = get_terms( array( 'taxonomy' => 'pa_modelo', 'hide_empty' => false, 'fields' => 'ids' ) );
$borradas      = 0;
$convertidas   = 0;
foreach ( $plan as $pid => list( $prod, $fotos, $vars ) ) {
	update_post_meta( $pid, '_dfans_fotos', $fotos );

	foreach ( $vars as list( $vid, $modelo, $talla ) ) {
		$v = wc_get_product( $vid );
		if ( in_array( $modelo, $se_van, true ) ) {
			$v->delete( true );
			$borradas++;
			continue;
		}
		$v->set_attributes( array( 'pa_talla' => $talla ) );
		$v->set_sku( preg_replace( '/-(H|NO)-/', '-', $v->get_sku(), 1 ) );
		$v->set_manage_stock( true );
		$v->set_stock_quantity( 20 );
		$v->set_gallery_image_ids( array() );
		$v->save();
		delete_post_meta( $vid, 'attribute_pa_modelo' );
		$convertidas++;
	}

	$attrs = $prod->get_attributes();
	$para  = $attrs['pa_modelo'];
	$para->set_options( $terminos_para );
	$para->set_variation( false );
	$para->set_visible( false );
	$attrs['pa_modelo'] = $para;
	$prod = wc_get_product( $pid );
	$prod->set_attributes( $attrs );
	$prod->set_default_attributes( array() );
	$prod->set_category_ids( array_values( array_unique( array_merge( $prod->get_category_ids(), array( $match->term_id ) ) ) ) );
	$prod->save();
	WC_Product_Variable::sync( $pid );
	wc_delete_product_transients( $pid );

	$p = wc_get_product( $pid );
	echo "$pid {$p->get_name()}: ", count( $p->get_children() ), ' variaciones, precio ', $p->get_price_html() ? strip_tags( $p->get_price_html() ) : '?', "\n";
}
echo "Convertidas: $convertidas. Eliminadas: $borradas. Match familiar: término {$match->term_id}.\n";
