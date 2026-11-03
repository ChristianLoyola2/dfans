<?php
/**
 * D'FANS - Fase 2: secciones para las líneas exclusivas (01/10/2026)
 *  - Snippet #25: la talla XS (baby tee de mujer) también es de adultos.
 *  - Plantillas de Adultos y Niños: "Solo adultos/niños" pasa a "Exclusivos para él y para ella" / "para niño y niña".
 * (El snippet #28 y el CSS se actualizan con fase1_ajustes.php.)
 * Copias en backups/fase2/. Uso: php fase2_secciones.php [--apply]
 */
define( 'CODE_SNIPPETS_SAFE_MODE', true );
define( 'WP_USE_THEMES', false );
$_SERVER['HTTP_HOST']   = 'dfans.local';
$_SERVER['REQUEST_URI'] = '/';
require 'C:/Users/GRLL/Local Sites/dfans/app/public/wp-load.php';
wp_set_current_user( 1 );

$apply = in_array( '--apply', $argv, true );
$copia = 'C:/Users/GRLL/Local Sites/dfans/backups/fase2/';

$tallas_viejo = "adultos: ['s', 'm', 'l', 'xl', 'xxl'],";
$tallas_nuevo = "adultos: ['xs', 's', 'm', 'l', 'xl', 'xxl'],";

$titulos = array(
	'taxonomy-product_cat-ropa-de-adultos' => array( 'Solo adultos', 'Exclusivos para él y para ella', 'Diseños propios D’FANS con un corte pensado para cada uno: oversize para él y baby tee para ella.' ),
	'taxonomy-product_cat-ropa-para-ninos' => array( 'Solo niños', 'Exclusivos para niño y niña', 'Diseños propios D’FANS: polo reforzado para él y manga mariposa para ella.' ),
);

$s25 = \Code_Snippets\get_snippet( 25 );
echo '#25 tiene la lista de tallas a cambiar: ', false !== strpos( $s25->code, $tallas_viejo ) ? 'si' : 'no (¿ya aplicado?)', "\n";
if ( ! $apply ) {
	echo "Modo revisión: no se aplicó nada.\n";
	exit;
}
wp_mkdir_p( $copia );

if ( false !== strpos( $s25->code, $tallas_viejo ) ) {
	file_put_contents( $copia . '25-modelo-y-tallas-dinamicas.ANTES-XS.php', "<?php\n" . $s25->code );
	$s25->code = str_replace( $tallas_viejo, $tallas_nuevo, $s25->code );
	\Code_Snippets\save_snippet( $s25 );
}
$s25 = \Code_Snippets\get_snippet( 25 );
echo '#25 activo=', $s25->active ? 'si' : 'NO', ' xs=', false !== strpos( $s25->code, $tallas_nuevo ) ? 'si' : 'no', "\n";

foreach ( $titulos as $slug => list( $viejo, $nuevo, $texto ) ) {
	$t = get_posts( array( 'post_type' => 'wp_template', 'name' => $slug, 'post_status' => 'any', 'numberposts' => 1 ) )[0] ?? null;
	if ( ! $t ) {
		echo "Falta la plantilla $slug\n";
		continue;
	}
	$buscar = '<h2 class="wp-block-heading">' . $viejo . '</h2>' . "\n<!-- /wp:heading -->";
	if ( false === strpos( $t->post_content, $buscar ) ) {
		echo "Plantilla $slug: título ya cambiado o distinto, no se toca.\n";
		continue;
	}
	file_put_contents( $copia . "$slug.ANTERIOR.html", $t->post_content );
	$reemplazo = '<h2 class="wp-block-heading">' . $nuevo . ' <span class="dfans-tag">Exclusivo</span></h2>' . "\n<!-- /wp:heading -->\n\n<!-- wp:paragraph -->\n<p>" . esc_html( $texto ) . "</p>\n<!-- /wp:paragraph -->";
	wp_update_post( array( 'ID' => $t->ID, 'post_content' => wp_slash( str_replace( $buscar, $reemplazo, $t->post_content ) ) ) );
	echo "Plantilla $slug: \"$nuevo\".\n";
}
