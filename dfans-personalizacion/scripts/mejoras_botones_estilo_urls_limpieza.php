<?php
/**
 * D'FANS - Mejoras 3, 4, 5 y 6 (30/09/2026)
 *  3. Modelo y talla como botones (bloque añadido al snippet #25).
 *  4. Estilo de producto, carrito y checkout (CSS en los estilos globales).
 *  5. URL del QR de Yape sin dominio fijo (snippets #8 y #14).
 *  6. Limpieza: plugins sin uso desactivados y snippets viejos a la papelera.
 * Guarda copias de todo lo que cambia. Uso: php mejoras_botones_estilo_urls_limpieza.php [--apply]
 */
define( 'CODE_SNIPPETS_SAFE_MODE', true );
define( 'WP_USE_THEMES', false );
$_SERVER['HTTP_HOST']   = 'dfans.local';
$_SERVER['REQUEST_URI'] = '/';
require 'C:/Users/GRLL/Local Sites/dfans/app/public/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/plugin.php';
wp_set_current_user( 1 );

$apply = in_array( '--apply', $argv, true );
$base  = 'C:/Users/GRLL/Local Sites/dfans/backups/';
$dir   = $base . 'snippets/';

function leer( $archivo ) {
	$c = file_get_contents( $archivo );
	if ( false === $c || '' === trim( $c ) ) {
		exit( "ERROR: no se pudo leer $archivo. No se aplicó nada más.\n" );
	}
	return $c;
}

// Leer todo antes de cambiar nada.
$bloque_botones = leer( $dir . '25-bloque-botones.php' );
$css_nuevo      = leer( $base . 'css/tienda-botones-carrito-checkout.css' );

$qr_viejo = "'http://dfans.local/wp-content/uploads/2026/08/yape-cap.jpg'";
$qr_nuevo = "wp_upload_dir()['baseurl'] . '/2026/08/yape-cap.jpg'";

$plugins_sin_uso = array(
	'jetpack/jetpack.php',
	'klaviyo/klaviyo.php',
	'pinterest-for-woocommerce/pinterest-for-woocommerce.php',
	'reddit-for-woocommerce/reddit-for-woocommerce.php',
	'snapchat-for-woocommerce/snapchat-for-woocommerce.php',
	'google-listings-and-ads/google-listings-and-ads.php',
	'wpforms-lite/wpforms.php',
);
$snippets_papelera = array( 1, 2, 3, 4, 6, 7, 10, 11, 12, 15, 22, 23, 24 );

if ( ! $apply ) {
	echo "Modo revisión: no se aplicó nada.\n";
	exit;
}

// 3. Snippet #25: botones.
$s = \Code_Snippets\get_snippet( 25 );
if ( false === strpos( $s->code, 'Modelo y talla como botones' ) ) {
	file_put_contents( $dir . '25-modelo-y-tallas-dinamicas.ANTES-BOTONES.php', "<?php\n" . $s->code );
	$s->code = rtrim( $s->code ) . "\n" . $bloque_botones;
	\Code_Snippets\save_snippet( $s );
}
$s = \Code_Snippets\get_snippet( 25 );
echo '#25 activo=', $s->active ? 'si' : 'NO', ' botones=', false !== strpos( $s->code, 'dfans-opcion' ) ? 'si' : 'no', "\n";

// 4. CSS.
$g = get_post( 29 );
$j = json_decode( $g->post_content, true );
if ( false === strpos( $j['styles']['css'], 'Modelo y Talla como botones' ) ) {
	file_put_contents( $base . 'scripts/estilos-globales-29.ANTES-BOTONES.json', $g->post_content );
	$antes               = strlen( $j['styles']['css'] );
	$j['styles']['css'] .= "\n" . $css_nuevo;
	wp_update_post( array( 'ID' => 29, 'post_content' => wp_slash( wp_json_encode( $j ) ) ) );
	$despues = strlen( json_decode( get_post( 29 )->post_content, true )['styles']['css'] );
	echo "CSS: $antes -> $despues caracteres\n";
}

// 5. QR de Yape.
foreach ( array( 8, 14 ) as $id ) {
	$s = \Code_Snippets\get_snippet( $id );
	if ( false !== strpos( $s->code, $qr_viejo ) ) {
		file_put_contents( $dir . "$id-qr-yape.ANTERIOR.php", "<?php\n" . $s->code );
		$s->code = str_replace( $qr_viejo, $qr_nuevo, $s->code );
		\Code_Snippets\save_snippet( $s );
	}
	$s = \Code_Snippets\get_snippet( $id );
	echo "#$id activo=", $s->active ? 'si' : 'NO', ' dfans.local=', false !== strpos( $s->code, 'dfans.local' ) ? 'SI' : 'no', "\n";
}

// 6a. Snippets viejos: copia y papelera.
@mkdir( $dir . 'papelera' );
foreach ( $snippets_papelera as $id ) {
	$s = \Code_Snippets\get_snippet( $id );
	if ( ! $s->id ) {
		continue;
	}
	$nombre = sanitize_title( $s->name );
	file_put_contents( $dir . "papelera/$id-$nombre.php", "<?php\n// Snippet #$id: {$s->name} (activo={$s->active})\n" . $s->code );
	if ( 22 === $id && $s->active ) {
		\Code_Snippets\deactivate_snippet( 22 );
	}
	\Code_Snippets\trash_snippet( $id );
	echo "#$id a la papelera: ", \Code_Snippets\get_snippet( $id )->active, " ({$s->name})\n";
}

// 6b. Plugins sin uso: solo desactivar.
foreach ( $plugins_sin_uso as $p ) {
	if ( is_plugin_active( $p ) ) {
		deactivate_plugins( $p );
	}
	echo "$p activo=", is_plugin_active( $p ) ? 'SI' : 'no', "\n";
}
echo "Plugins activos: ", implode( ', ', get_option( 'active_plugins' ) ), "\n";
