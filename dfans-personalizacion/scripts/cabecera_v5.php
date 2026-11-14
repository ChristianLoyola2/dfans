<?php
/**
 * D'FANS - Cabecera v5 (13/11/2026), con la referencia del usuario: dos filas.
 *  Fila 1: buscador de polos (icono) a la izquierda, logo al centro, cuenta/favoritos/carrito a la derecha.
 *  Fila 2: el menú centrado.
 * Copia de la cabecera anterior (wp_template_part 111) en backups/rediseno/. Uso: php cabecera_v5.php [--apply]
 */
define( 'WP_USE_THEMES', false );
$_SERVER['HTTP_HOST']   = 'dfans.local';
$_SERVER['REQUEST_URI'] = '/';
require 'C:/Users/GRLL/Local Sites/dfans/app/public/wp-load.php';
wp_set_current_user( 1 );

$antes = get_post( 111 )->post_content;
if ( false === strpos( $antes, '"ref":4' ) ) {
	exit( "ERROR: la cabecera no tiene el menú 4; revisar antes de cambiar.\n" );
}
$nuevo = '<!-- wp:group {"align":"full","className":"dfans-header","layout":{"type":"default"}} -->
<div class="wp-block-group alignfull dfans-header"><!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group"><!-- wp:group {"align":"wide","className":"dfans-header__fila","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide dfans-header__fila"><!-- wp:group {"className":"dfans-header__izq","layout":{"type":"flex","flexWrap":"nowrap"}} -->
<div class="wp-block-group dfans-header__izq"><!-- wp:search {"label":"Buscar polos","showLabel":false,"placeholder":"Buscar polos","buttonText":"Buscar","buttonPosition":"button-only","buttonUseIcon":true,"isSearchFieldHidden":true,"query":{"post_type":"product"},"className":"dfans-buscar"} /--></div>
<!-- /wp:group -->

<!-- wp:site-title {"level":0,"textAlign":"center"} /-->

<!-- wp:group {"className":"dfans-header__der","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"right"}} -->
<div class="wp-block-group dfans-header__der"><!-- wp:woocommerce/customer-account {"displayStyle":"icon_only","iconStyle":"line","iconClass":"wc-block-customer-account__account-icon"} /-->

<!-- wp:woocommerce/mini-cart {"hasHiddenPrice":true} /--></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"wide","className":"dfans-header__menu","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"center"}} -->
<div class="wp-block-group alignwide dfans-header__menu"><!-- wp:navigation {"ref":4,"overlayBackgroundColor":"contrast","overlayTextColor":"base","metadata":{"ignoredHookedBlocks":["woocommerce/customer-account","woocommerce/mini-cart"]},"layout":{"type":"flex","justifyContent":"center","flexWrap":"wrap"}} /--></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->';

echo 'Cabecera: ', strlen( $antes ), ' -> ', strlen( $nuevo ), " caracteres\n";
if ( ! in_array( '--apply', $argv, true ) ) {
	exit( "Modo revisión.\n" );
}
file_put_contents( 'C:/Users/GRLL/Local Sites/dfans/backups/rediseno/cabecera-111.ANTES-V5-' . date( 'Ymd-Hi' ) . '.html', $antes );
wp_update_post( array( 'ID' => 111, 'post_content' => wp_slash( $nuevo ) ) );
echo "Cabecera actualizada.\n";
