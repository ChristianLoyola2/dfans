<?php
/**
 * D'FANS - Portada, segunda versión del rediseño (10/11/2026): estructura de tienda, productos primero.
 *  1. El mismo polo (Demon Slayer) en hombre, mujer, niño y niña: la idea Match.
 *  2. Lo nuevo en la tienda (8 polos).
 *  3. Índice de fandoms [dfans_fandoms] (banda oscura).
 *  4. Los 4 cortes exclusivos.
 *  5. Quiz.
 *  6. Cómo comprar y recoger en Gamarra.
 * Copia de la portada anterior en backups/rediseno/. Uso: php rediseno2_portada.php [--apply]
 */
define( 'WP_USE_THEMES', false );
$_SERVER['HTTP_HOST']   = 'dfans.local';
$_SERVER['REQUEST_URI'] = '/';
require 'C:/Users/GRLL/Local Sites/dfans/app/public/wp-load.php';
wp_set_current_user( 1 );

/** Bloque de imagen (con id para srcset). */
function p2_img( $id, $alt, $enlace = '', $caption = '' ) {
	$attr = wp_json_encode( array( 'id' => $id, 'sizeSlug' => 'large', 'linkDestination' => $enlace ? 'custom' : 'none' ), JSON_UNESCAPED_SLASHES );
	$img  = '<img src="' . esc_url( wp_get_attachment_image_url( $id, 'large' ) ) . '" alt="' . esc_attr( $alt ) . '" class="wp-image-' . (int) $id . '"/>';
	return "<!-- wp:image $attr -->\n<figure class=\"wp-block-image size-large\">"
		. ( $enlace ? '<a href="' . esc_url( $enlace ) . '">' . $img . '</a>' : $img )
		. ( $caption ? '<figcaption class="wp-element-caption">' . esc_html( $caption ) . '</figcaption>' : '' )
		. "</figure>\n<!-- /wp:image -->";
}
function p2_url( $ruta ) {
	return esc_url( home_url( $ruta ) );
}
function p2_boton( $texto, $ruta, $clase ) {
	return "<!-- wp:button {\"className\":\"$clase\"} -->\n<div class=\"wp-block-button $clase\"><a class=\"wp-block-button__link wp-element-button\" href=\"" . p2_url( $ruta ) . '">' . esc_html( $texto ) . "</a></div>\n<!-- /wp:button -->";
}
function p2_seccion( $clase, $interior, $alinear = 'wide' ) {
	return "<!-- wp:group {\"align\":\"full\",\"className\":\"dfans-p $clase\",\"layout\":{\"type\":\"constrained\"}} -->\n<div class=\"wp-block-group alignfull dfans-p $clase\">$interior</div>\n<!-- /wp:group -->";
}
function p2_cabecera( $titulo, $texto_html = '' ) {
	return "<!-- wp:group {\"align\":\"wide\",\"className\":\"dfans-p-cabecera\",\"layout\":{\"type\":\"default\"}} -->\n<div class=\"wp-block-group alignwide dfans-p-cabecera\"><!-- wp:heading -->\n<h2 class=\"wp-block-heading\">$titulo</h2>\n<!-- /wp:heading -->"
		. ( $texto_html ? "\n\n<!-- wp:paragraph -->\n<p>$texto_html</p>\n<!-- /wp:paragraph -->" : '' )
		. "</div>\n<!-- /wp:group -->";
}

$demon = '/product/polo-demon-slayer/';
$s     = array();

// 1. Hero.
$familia = array(
	array( 100, 'Hombre', 'adultos', 'hombre' ),
	array( 102, 'Mujer', 'adultos', 'mujer' ),
	array( 104, 'Niño', 'ninos', 'nino' ),
	array( 106, 'Niña', 'ninos', 'nina' ),
);
$fotos = array();
foreach ( $familia as list( $id, $quien, $seccion, $para ) ) {
	$fotos[] = p2_img( $id, "$quien con el polo Demon Slayer de D’FANS", $demon . "?seccion=$seccion&para=$para", $quien );
}
$s[] = "<!-- wp:group {\"align\":\"full\",\"className\":\"dfans-p-hero\",\"layout\":{\"type\":\"constrained\"}} -->
<div class=\"wp-block-group alignfull dfans-p-hero\">"
	. "<!-- wp:columns {\"verticalAlignment\":\"bottom\",\"align\":\"wide\",\"className\":\"dfans-p-hero__cols\"} -->
<div class=\"wp-block-columns alignwide are-vertically-aligned-bottom dfans-p-hero__cols\">"
	. "<!-- wp:column {\"verticalAlignment\":\"bottom\",\"width\":\"58%\"} -->
<div class=\"wp-block-column is-vertically-aligned-bottom\" style=\"flex-basis:58%\">"
	. "<!-- wp:heading {\"level\":1} -->
<h1 class=\"wp-block-heading\">El mismo polo para toda la familia</h1>
<!-- /wp:heading --></div>
<!-- /wp:column -->

"
	. "<!-- wp:column {\"verticalAlignment\":\"bottom\",\"width\":\"42%\"} -->
<div class=\"wp-block-column is-vertically-aligned-bottom\" style=\"flex-basis:42%\">"
	. "<!-- wp:paragraph {\"className\":\"dfans-p-hero__texto\"} -->
<p class=\"dfans-p-hero__texto\">Colección Match: cada diseño viene en tallas de niño (10 a 14) y de adulto (S a XXL). Desde S/ 30, hechos en Gamarra.</p>
<!-- /wp:paragraph -->

"
	. "<!-- wp:buttons -->
<div class=\"wp-block-buttons\">" . p2_boton( 'Ver la colección Match', '/product-category/match-familiar/', 'dfans-b-primario' ) . "

" . p2_boton( 'Ver toda la tienda', '/shop/', 'dfans-b-secundario' ) . "</div>
<!-- /wp:buttons --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

"
	. "<!-- wp:group {\"align\":\"wide\",\"className\":\"dfans-p-familia\",\"layout\":{\"type\":\"default\"}} -->
<div class=\"wp-block-group alignwide dfans-p-familia\">" . implode( "

", $fotos ) . "</div>
<!-- /wp:group --></div>
<!-- /wp:group -->";

// 2. Lo nuevo.
$s[] = p2_seccion( 'dfans-p-nuevos',
	p2_cabecera( 'Lo nuevo en la tienda', '<a href="' . p2_url( '/shop/' ) . '">Ver los ' . (int) wp_count_posts( 'product' )->publish . ' polos</a>' )
	. "\n\n" . '<!-- wp:woocommerce/product-collection {"queryId":61,"query":{"perPage":8,"pages":1,"offset":0,"postType":"product","order":"asc","orderBy":"menu_order","search":"","exclude":[],"inherit":false,"taxQuery":[],"isProductCollectionBlock":true,"featured":false,"woocommerceOnSale":false,"woocommerceStockStatus":["instock","onbackorder"],"woocommerceAttributes":[],"woocommerceHandPickedProducts":[]},"tagName":"div","displayLayout":{"type":"flex","columns":4,"shrinkColumns":true},"dimensions":{"widthType":"fill"},"queryContextIncludes":["collection"],"align":"wide"} -->' . "\n"
	. '<div class="wp-block-woocommerce-product-collection alignwide"><!-- wp:woocommerce/product-template -->' . "\n"
	. '<!-- wp:woocommerce/product-image {"showSaleBadge":false,"imageSizing":"thumbnail","isDescendentOfQueryLoop":true} /-->' . "\n"
	. '<!-- wp:post-title {"level":3,"isLink":true,"__woocommerceNamespace":"woocommerce/product-collection/product-title"} /-->' . "\n"
	. '<!-- wp:woocommerce/product-price {"isDescendentOfQueryLoop":true} /-->' . "\n"
	. "<!-- /wp:woocommerce/product-template --></div>\n<!-- /wp:woocommerce/product-collection -->"
);

// 3. Fandoms.
$s[] = p2_seccion( 'dfans-p-fandoms',
	p2_cabecera( 'Elige por fandom', 'Cada temática junta los polos Match y los exclusivos, de adulto y de niño.' )
	. "\n\n<!-- wp:shortcode -->\n[dfans_fandoms]\n<!-- /wp:shortcode -->"
);

// 4. Cortes exclusivos.
$cortes = array(
	array( 'polo-oversize-beat-maker', 'Oversize', 'Para él, tallas S a XXL. Hombros caídos y largo extra.', 'S/ 45', '/product-category/ropa-de-adultos/?para=hombre' ),
	array( 'baby-tee-chica-magica-luna', 'Baby tee', 'Para ella, tallas XS a L. Entallado y de largo crop.', 'S/ 40', '/product-category/ropa-de-adultos/?para=mujer' ),
	array( 'polo-nino-dino-gamer', 'Regular reforzado', 'Para niño, tallas 10 a 14. Holgado y con doble costura.', 'S/ 32', '/product-category/ropa-para-ninos/?para=nino' ),
	array( 'polo-nina-boba-panda', 'Manga mariposa', 'Para niña, tallas 10 a 14. Manga con vuelo.', 'S/ 35', '/product-category/ropa-para-ninos/?para=nina' ),
);
$tarjetas = array();
foreach ( $cortes as list( $slug, $nombre, $texto, $precio, $ruta ) ) {
	$p = get_page_by_path( $slug, OBJECT, 'product' );
	if ( ! $p ) {
		exit( "ERROR: no existe $slug\n" );
	}
	$tarjetas[] = "<!-- wp:group {\"className\":\"dfans-p-corte\",\"layout\":{\"type\":\"default\"}} -->\n<div class=\"wp-block-group dfans-p-corte\">"
		. p2_img( (int) get_post_thumbnail_id( $p->ID ), '', home_url( $ruta ) ) . "\n\n"
		. "<!-- wp:heading {\"level\":3} -->\n<h3 class=\"wp-block-heading\"><a href=\"" . p2_url( $ruta ) . "\">$nombre</a></h3>\n<!-- /wp:heading -->\n\n"
		. "<!-- wp:paragraph -->\n<p>$texto</p>\n<!-- /wp:paragraph -->\n\n"
		. "<!-- wp:paragraph {\"className\":\"dfans-p-precio\"} -->\n<p class=\"dfans-p-precio\">$precio</p>\n<!-- /wp:paragraph --></div>\n<!-- /wp:group -->";
}
$s[] = p2_seccion( 'dfans-p-cortes',
	p2_cabecera( 'Exclusivos D’FANS: un corte para cada uno', 'Diseños propios que no están en la colección Match.' )
	. "\n\n<!-- wp:group {\"align\":\"wide\",\"className\":\"dfans-p-cortes__grilla\",\"layout\":{\"type\":\"default\"}} -->\n<div class=\"wp-block-group alignwide dfans-p-cortes__grilla\">" . implode( "\n\n", $tarjetas ) . "</div>\n<!-- /wp:group -->"
);

// 5. Quiz.
$s[] = p2_seccion( 'dfans-p-quiz dfans-seccion-quiz', "<!-- wp:shortcode -->\n[dfans_quiz]\n<!-- /wp:shortcode -->" );

// 6. Cómo comprar.
$cols = array(
	array( 'Recoge gratis en Gamarra', 'Jr. Agustín Gamarra 1215, La Victoria. Te escribimos cuando tu pedido esté listo para recoger.' ),
	array( 'Delivery en Lima y envíos a provincia', 'En Lima coordinamos contigo el costo según tu distrito. A provincia enviamos por agencia o Shalom y pagas el flete al recoger.' ),
	array( 'Paga con Yape o transferencia', 'Al confirmar tu pedido ves el QR de Yape o los datos de la cuenta. Preparamos tu pedido apenas verificamos el pago.' ),
);
$html = '';
foreach ( $cols as list( $t, $p ) ) {
	$html .= "<!-- wp:column -->\n<div class=\"wp-block-column\"><!-- wp:heading {\"level\":3} -->\n<h3 class=\"wp-block-heading\">$t</h3>\n<!-- /wp:heading -->\n\n<!-- wp:paragraph -->\n<p>$p</p>\n<!-- /wp:paragraph --></div>\n<!-- /wp:column -->\n\n";
}
$s[] = p2_seccion( 'dfans-p-visita',
	p2_cabecera( 'Compra en la web, recoge en Gamarra' )
	. "\n\n<!-- wp:columns {\"align\":\"wide\",\"className\":\"dfans-p-visita__cols\"} -->\n<div class=\"wp-block-columns alignwide dfans-p-visita__cols\">" . rtrim( $html ) . "</div>\n<!-- /wp:columns -->"
);

$nuevo = implode( "\n\n", $s );
$antes = get_post( 60 )->post_content;
echo 'Portada: ', strlen( $antes ), ' -> ', strlen( $nuevo ), " caracteres, 6 secciones\n";
if ( ! in_array( '--apply', $argv, true ) ) {
	exit( "Modo revisión.\n" );
}
file_put_contents( 'C:/Users/GRLL/Local Sites/dfans/backups/rediseno/portada-60.ANTES-V2-' . date( 'Ymd-Hi' ) . '.html', $antes );
wp_update_post( array( 'ID' => 60, 'post_content' => wp_slash( $nuevo ) ) );
echo "Portada actualizada.\n";
