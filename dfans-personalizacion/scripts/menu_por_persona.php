<?php
/**
 * D'FANS - Menú por persona (10/11/2026): Hombre, Mujer, Niño, Niña y Match familiar.
 * "Tienda" sale del menú (la página sigue: buscador, índice de fandoms, "Ver los N polos").
 * Copia del menú anterior en backups/rediseno/. Uso: php menu_por_persona.php [--apply]
 */
define( 'WP_USE_THEMES', false );
$_SERVER['HTTP_HOST'] = 'dfans.local';
require 'C:/Users/GRLL/Local Sites/dfans/app/public/wp-load.php';
wp_set_current_user( 1 );

$enlaces = array(
	array( 'Hombre', '/product-category/ropa-de-adultos/?para=hombre' ),
	array( 'Mujer', '/product-category/ropa-de-adultos/?para=mujer' ),
	array( 'Niño', '/product-category/ropa-para-ninos/?para=nino' ),
	array( 'Niña', '/product-category/ropa-para-ninos/?para=nina' ),
);
$bloques = array();
foreach ( $enlaces as list( $texto, $url ) ) {
	$bloques[] = '<!-- wp:navigation-link ' . wp_json_encode( array( 'label' => $texto, 'url' => $url, 'kind' => 'custom', 'isTopLevelLink' => true ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . ' /-->';
}
$bloques[] = '<!-- wp:navigation-link {"label":"Match familiar","type":"product_cat","id":88,"url":"/product-category/match-familiar/","kind":"taxonomy"} /-->';
$nuevo = implode( "\n\n", $bloques );
$antes = get_post( 4 )->post_content;
echo $nuevo, "\n";
if ( ! in_array( '--apply', $argv, true ) ) {
	exit( "Modo revisión.\n" );
}
file_put_contents( 'C:/Users/GRLL/Local Sites/dfans/backups/rediseno/menu-4.ANTES-' . date( 'Ymd-Hi' ) . '.html', $antes );
wp_update_post( array( 'ID' => 4, 'post_content' => wp_slash( $nuevo ) ) );
echo "Menú actualizado.\n";
