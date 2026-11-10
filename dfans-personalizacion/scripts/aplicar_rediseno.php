<?php
/**
 * D'FANS - Rediseño visual (09/11/2026).
 *  - Snippets: actualiza #29 "Fandom e interacción" y crea/actualiza "D'FANS - Rediseño visual".
 *  - CSS global: bloque "Rediseño 2026" al final (css/rediseno-2026.css).
 *  - Portada (60): muro de polos, título con palabra de fandom, categorías con foto, sin etiquetas sticker.
 *  - Pie (112), Mis favoritos (1487), plantillas Adultos/Niños (orden mezclado), plantilla 404 en español.
 *  - Orden de la tienda: líneas intercaladas (Match, oversize, baby tee, reforzado, mariposa).
 * Copias previas en backups/rediseno/. Uso: php aplicar_rediseno.php [--apply]
 */
define( 'CODE_SNIPPETS_SAFE_MODE', true );
define( 'WP_USE_THEMES', false );
$_SERVER['HTTP_HOST']   = 'dfans.local';
$_SERVER['REQUEST_URI'] = '/';
require 'C:/Users/GRLL/Local Sites/dfans/app/public/wp-load.php';
wp_set_current_user( 1 );

$apply = in_array( '--apply', $argv, true );
$base  = 'C:/Users/GRLL/Local Sites/dfans/backups/';
$copia = $base . 'rediseno/';
wp_mkdir_p( $copia );
$errores = array();

/** Reemplaza un texto que debe aparecer exactamente una vez. */
function dfans_una_vez( &$texto, $de, $a, $etiqueta, &$errores ) {
	$n = substr_count( $texto, $de );
	if ( 1 !== $n ) {
		$errores[] = "$etiqueta: aparece $n veces";
		return;
	}
	$texto = str_replace( $de, $a, $texto );
}

/** Bloque de imagen enlazada (con id para que WordPress agregue srcset). */
function dfans_bloque_imagen( $id, $clase, $enlace, $alt, $caption = '' ) {
	$src  = wp_get_attachment_image_url( $id, 'large' );
	$attr = wp_json_encode( array( 'id' => $id, 'sizeSlug' => 'large', 'linkDestination' => $enlace ? 'custom' : 'none', 'className' => $clase ), JSON_UNESCAPED_SLASHES );
	$img  = '<img src="' . esc_url( $src ) . '" alt="' . esc_attr( $alt ) . '" class="wp-image-' . (int) $id . '"/>';
	return "<!-- wp:image $attr -->\n<figure class=\"wp-block-image size-large $clase\">"
		. ( $enlace ? '<a href="' . esc_url( $enlace ) . '">' . $img . '</a>' : $img )
		. ( $caption ? '<figcaption class="wp-element-caption">' . esc_html( $caption ) . '</figcaption>' : '' )
		. "</figure>\n<!-- /wp:image -->";
}

function dfans_foto_de( $slug ) {
	$p = get_page_by_path( $slug, OBJECT, 'product' );
	return $p ? (int) get_post_thumbnail_id( $p->ID ) : 0;
}

// ---------- Portada ----------
$portada = get_post( 60 )->post_content;
$p       = $portada;

dfans_una_vez( $p, "<!-- wp:paragraph {\"className\":\"dfans-eyebrow\"} -->\n<p class=\"dfans-eyebrow\">D’FANS · Nueva colección</p>\n<!-- /wp:paragraph -->\n\n", '', 'hero: etiqueta', $errores );
dfans_una_vez( $p, '<h1 class="wp-block-heading dfans-hero-titulo">Viste tu <mark style="background-color:rgba(0, 0, 0, 0)" class="has-inline-color dfans-rojo">actitud</mark></h1>', '<h1 class="wp-block-heading dfans-hero-titulo">Viste tu <span class="dfans-palabra">fandom</span></h1>', 'hero: título', $errores );
dfans_una_vez( $p, "\n\n<!-- wp:paragraph {\"className\":\"dfans-hero-tags\"} -->\n<p class=\"dfans-hero-tags\">Rock · Música · Anime · Streetwear</p>\n<!-- /wp:paragraph -->", '', 'hero: etiquetas', $errores );

// Carrusel -> muro de polos (6 polos, uno por fandom).
$ini = strpos( $p, '<!-- wp:group {"className":"dfans-carrusel"' );
$fin = strpos( $p, '<!-- /wp:group -->', strpos( $p, '<!-- /wp:image --></div>', $ini ) );
if ( false === $ini || false === $fin ) {
	$errores[] = 'hero: no se encontró el carrusel';
} else {
	$muro = array(
		array( 98, 'anime', 'polo-demon-slayer', 'Polo Demon Slayer' ),
		array( 285, 'rock', 'polo-ac-dc', 'Polo AC/DC' ),
		array( 591, 'peliculas', 'polo-stranger-things', 'Polo Stranger Things' ),
		array( 411, 'metal', 'polo-metallica', 'Polo Metallica' ),
		array( 483, 'artistas', 'polo-bad-bunny', 'Polo Bad Bunny' ),
		array( 789, 'videojuegos', 'polo-minecraft', 'Polo Minecraft' ),
	);
	$html = "<!-- wp:group {\"className\":\"dfans-muro\",\"layout\":{\"type\":\"default\"}} -->\n<div class=\"wp-block-group dfans-muro\">";
	$items = array();
	foreach ( $muro as list( $id, $tema, $slug, $nombre ) ) {
		$items[] = dfans_bloque_imagen( $id, "dfans-muro__polo dfans-tema--$tema", home_url( "/product/$slug/" ), $nombre, $nombre );
	}
	$html .= implode( "\n\n", $items ) . "</div>\n";
	$p     = substr( $p, 0, $ini ) . $html . substr( $p, $fin );
}

// Categorías: sin número ni "Ver diseños →"; con la foto de un polo de ese fandom.
dfans_una_vez( $p, "<!-- wp:paragraph {\"className\":\"dfans-eyebrow\"} -->\n<p class=\"dfans-eyebrow\">Categorías</p>\n<!-- /wp:paragraph -->\n\n", '', 'categorías: etiqueta', $errores );
$cats = array(
	'anime'       => array( '01', 'polo-naruto', 'Demon Slayer · Naruto · One Piece · Dragon Ball', 'Demon Slayer, Naruto, One Piece y Dragon Ball' ),
	'rock'        => array( '02', 'polo-queen', 'AC/DC · Queen · Nirvana · The Beatles', 'AC/DC, Queen, Nirvana y The Beatles' ),
	'metal'       => array( '03', 'polo-iron-maiden', 'Metallica · Iron Maiden · Slipknot', 'Metallica, Iron Maiden y Slipknot' ),
	'artistas'    => array( '04', 'polo-eminem', 'Bad Bunny · Eminem · Travis Scott', 'Bad Bunny, Eminem y Travis Scott' ),
	'peliculas'   => array( '05', 'polo-el-senor-de-los-anillos', 'Stranger Things · El Señor de los Anillos', 'Stranger Things y El Señor de los Anillos' ),
	'videojuegos' => array( '06', 'polo-fortnite', 'Minecraft · Fortnite · Free Fire · Dota 2', 'Minecraft, Fortnite, Free Fire y Dota 2' ),
);
foreach ( $cats as $tema => list( $num, $slug, $ejemplos, $ejemplos_nuevo ) ) {
	$foto = dfans_foto_de( $slug );
	if ( ! $foto ) {
		$errores[] = "categoría $tema: sin foto de $slug";
		continue;
	}
	dfans_una_vez( $p, "<!-- wp:paragraph {\"className\":\"dfans-cat-num\"} -->\n<p class=\"dfans-cat-num\">$num</p>\n<!-- /wp:paragraph -->", dfans_bloque_imagen( $foto, 'dfans-cat-img', '', '' ), "categoría $tema: número", $errores );
	dfans_una_vez( $p, ">$ejemplos<", ">$ejemplos_nuevo<", "categoría $tema: ejemplos", $errores );
}
$p = str_replace( "\n\n<!-- wp:paragraph {\"className\":\"dfans-cat-cta\"} -->\n<p class=\"dfans-cat-cta\">Ver diseños →</p>\n<!-- /wp:paragraph -->", '', $p, $n_cta );
if ( 6 !== $n_cta ) {
	$errores[] = "categorías: se esperaban 6 'Ver diseños' y hay $n_cta";
}

// Adultos y niños, destacado y pasos: sin etiquetas, sin puntos medios, enlace limpio.
dfans_una_vez( $p, "<!-- wp:paragraph {\"className\":\"dfans-eyebrow\"} -->\n<p class=\"dfans-eyebrow\">Para toda la familia</p>\n<!-- /wp:paragraph -->\n\n", '', 'familia: etiqueta', $errores );
dfans_una_vez( $p, 'oversize para él y baby tee para ella (XS a L)</p>', 'oversize para él y baby tee para ella (XS a L).</p>', 'familia: adultos', $errores );
dfans_una_vez( $p, 'manga mariposa para niña · Tallas 10, 12 y 14</p>', 'manga mariposa para niña. Tallas 10, 12 y 14.</p>', 'familia: niños', $errores );
dfans_una_vez( $p, "<!-- wp:paragraph {\"className\":\"dfans-eyebrow\"} -->\n<p class=\"dfans-eyebrow\">Colección Match · Diseño destacado</p>\n<!-- /wp:paragraph -->\n\n", '', 'destacado: etiqueta', $errores );
dfans_una_vez( $p, '<strong>Adultos S · M · L</strong>', '<strong>Adultos S, M y L</strong>', 'destacado: precio 1', $errores );
dfans_una_vez( $p, '<strong>Adultos XL · XXL</strong>', '<strong>Adultos XL y XXL</strong>', 'destacado: precio 2', $errores );
dfans_una_vez( $p, '<strong>Niños 10 · 12 · 14</strong>', '<strong>Niños 10, 12 y 14</strong>', 'destacado: precio 3', $errores );
dfans_una_vez( $p, 'href="http://dfans.local/?post_type=product&#038;p=79">Ver producto</a>', 'href="' . esc_url( home_url( '/product/polo-demon-slayer/' ) ) . '">Ver el polo Demon Slayer</a>', 'destacado: enlace', $errores );
dfans_una_vez( $p, "<!-- wp:paragraph {\"className\":\"dfans-eyebrow\"} -->\n<p class=\"dfans-eyebrow\">Cómo comprar</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">En 4 pasos</h2>", "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">Cómo comprar</h2>", 'pasos: título', $errores );
foreach ( array( '01' => '1', '02' => '2', '03' => '3', '04' => '4' ) as $de => $a ) {
	dfans_una_vez( $p, "<p class=\"dfans-paso-num\">$de</p>", "<p class=\"dfans-paso-num\">$a</p>", "pasos: $de", $errores );
}
dfans_una_vez( $p, "<p class=\"dfans-paso-num\">3</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:heading {\"level\":3} -->\n<h3 class=\"wp-block-heading\">Paga con Yape</h3>", "<p class=\"dfans-paso-num\">3</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:heading {\"level\":3} -->\n<h3 class=\"wp-block-heading\">Paga</h3>", 'pasos: pago título', $errores );
dfans_una_vez( $p, '<p>Confirma tu pedido y paga escaneando nuestro código QR.</p>', '<p>Confirma tu pedido y paga con Yape (código QR) o por transferencia.</p>', 'pasos: pago texto', $errores );

// ---------- Pie ----------
$pie = get_post( 112 )->post_content;
$f   = $pie;
dfans_una_vez( $f, '<p class="dfans-footer-dato"><strong>Pagos:</strong> Yape y transferencia bancaria</p>', '<p class="dfans-footer-dato"><strong>Pagos:</strong> <span class="dfans-pill">Yape</span><span class="dfans-pill">Transferencia bancaria</span></p>', 'pie: pagos', $errores );
dfans_una_vez( $f, '>📖 Libro de Reclamaciones</a>', '>Libro de Reclamaciones</a>', 'pie: libro', $errores );
dfans_una_vez( $f, '© 2026 D’FANS · Lima, Perú · Viste tu actitud', '© 2026 D’FANS. Polos de fandom hechos en Gamarra, Lima.', 'pie: legal', $errores );

// ---------- Mis favoritos: sugerencias debajo ----------
$fav  = get_post( 1487 )->post_content;
$sug  = "\n\n<!-- wp:group {\"className\":\"dfans-sugerencias\",\"layout\":{\"type\":\"constrained\"}} -->\n<div class=\"wp-block-group dfans-sugerencias\"><!-- wp:heading -->\n<h2 class=\"wp-block-heading\">Te pueden gustar</h2>\n<!-- /wp:heading -->\n\n"
	. '<!-- wp:woocommerce/product-collection {"queryId":51,"query":{"perPage":4,"pages":1,"offset":0,"postType":"product","order":"asc","orderBy":"menu_order","search":"","exclude":[],"inherit":false,"taxQuery":[],"isProductCollectionBlock":true,"featured":false,"woocommerceOnSale":false,"woocommerceStockStatus":["instock","onbackorder"],"woocommerceAttributes":[],"woocommerceHandPickedProducts":[]},"tagName":"div","displayLayout":{"type":"flex","columns":4,"shrinkColumns":true},"dimensions":{"widthType":"fill"},"queryContextIncludes":["collection"],"align":"wide"} -->' . "\n"
	. '<div class="wp-block-woocommerce-product-collection alignwide"><!-- wp:woocommerce/product-template -->' . "\n"
	. '<!-- wp:woocommerce/product-image {"showSaleBadge":false,"imageSizing":"thumbnail","isDescendentOfQueryLoop":true} /-->' . "\n"
	. '<!-- wp:post-title {"level":3,"isLink":true,"__woocommerceNamespace":"woocommerce/product-collection/product-title"} /-->' . "\n"
	. '<!-- wp:woocommerce/product-price {"isDescendentOfQueryLoop":true} /-->' . "\n"
	. '<!-- wp:woocommerce/product-button {"isDescendentOfQueryLoop":true} /-->' . "\n"
	. "<!-- /wp:woocommerce/product-template --></div>\n<!-- /wp:woocommerce/product-collection --></div>\n<!-- /wp:group -->";
$fav2 = false === strpos( $fav, 'dfans-sugerencias' ) ? $fav . $sug : $fav;

// ---------- Plantillas Adultos / Niños: orden intercalado ----------
$plantillas = array();
foreach ( array( 1102, 1103 ) as $tid ) {
	$c = get_post( $tid )->post_content;
	$n = substr_count( $c, '"orderBy":"title"' );
	if ( 2 !== $n ) {
		$errores[] = "plantilla $tid: se esperaban 2 orderBy title y hay $n";
	}
	$plantillas[ $tid ] = array( $c, str_replace( '"orderBy":"title"', '"orderBy":"menu_order"', $c ) );
}

// ---------- Plantilla 404 ----------
$foto404 = dfans_foto_de( 'polo-nirvana' );
$p404    = '<!-- wp:template-part {"slug":"header","theme":"twentytwentyfive"} /-->' . "\n\n"
	. '<!-- wp:group {"tagName":"main","className":"dfans-404","layout":{"type":"constrained"}} -->' . "\n"
	. '<main class="wp-block-group dfans-404"><!-- wp:columns {"verticalAlignment":"center","align":"wide"} -->' . "\n"
	. '<div class="wp-block-columns alignwide are-vertically-aligned-center"><!-- wp:column {"verticalAlignment":"center","width":"58%"} -->' . "\n"
	. '<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:58%"><!-- wp:heading {"level":1} -->' . "\n"
	. '<h1 class="wp-block-heading">Esta página no existe</h1>' . "\n<!-- /wp:heading -->\n\n"
	. "<!-- wp:paragraph -->\n<p>El enlace está mal escrito o el polo ya no está en la tienda. Busca lo que querías:</p>\n<!-- /wp:paragraph -->\n\n"
	. '<!-- wp:search {"label":"Buscar polos","showLabel":false,"placeholder":"Busca un anime, banda, serie o juego","buttonText":"Buscar","query":{"post_type":"product"}} /-->' . "\n\n"
	. "<!-- wp:paragraph -->\n<p>O entra por sección: <a href=\"" . esc_url( home_url( '/product-category/ropa-de-adultos/' ) ) . '">ropa de adultos</a>, <a href="' . esc_url( home_url( '/product-category/ropa-para-ninos/' ) ) . '">ropa para niños</a> o <a href="' . esc_url( home_url( '/product-category/match-familiar/' ) ) . "\">Match familiar</a>.</p>\n<!-- /wp:paragraph --></div>\n<!-- /wp:column -->\n\n"
	. '<!-- wp:column {"verticalAlignment":"center","width":"42%"} -->' . "\n"
	. '<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:42%">' . ( $foto404 ? dfans_bloque_imagen( $foto404, 'dfans-404-foto', home_url( '/product/polo-nirvana/' ), 'Polo Nirvana de D’FANS' ) : '' ) . "</div>\n<!-- /wp:column --></div>\n<!-- /wp:columns --></main>\n<!-- /wp:group -->\n\n"
	. '<!-- wp:template-part {"slug":"footer","theme":"twentytwentyfive"} /-->';
$t404 = get_posts( array( 'post_type' => 'wp_template', 'name' => '404', 'post_status' => 'any', 'numberposts' => 1, 'tax_query' => array( array( 'taxonomy' => 'wp_theme', 'field' => 'name', 'terms' => get_stylesheet() ) ) ) );

// ---------- Orden de la tienda: líneas intercaladas ----------
$lineas = array( 'match' => array(), 'oversize' => array(), 'baby-tee-crop' => array(), 'regular-reforzado' => array(), 'manga-mariposa' => array() );
$orden_antes = array();
foreach ( get_posts( array( 'post_type' => 'product', 'post_status' => 'publish', 'numberposts' => -1, 'orderby' => array( 'menu_order' => 'ASC', 'title' => 'ASC' ) ) ) as $prod ) {
	$orden_antes[ $prod->ID ] = $prod->menu_order;
	$corte = wp_get_post_terms( $prod->ID, 'pa_corte', array( 'fields' => 'slugs' ) );
	$clave = $corte && isset( $lineas[ $corte[0] ] ) ? $corte[0] : 'match';
	$lineas[ $clave ][] = $prod->ID;
}
$orden = array();
for ( $i = 0; count( $orden ) < count( $orden_antes ); $i++ ) {
	foreach ( $lineas as $ids ) {
		if ( isset( $ids[ $i ] ) ) {
			$orden[] = $ids[ $i ];
		}
	}
}

echo 'Portada: ', strlen( $portada ), ' -> ', strlen( $p ), " caracteres\n";
echo 'Pie: ', $pie === $f ? 'sin cambios' : 'cambia', "\n";
echo 'Favoritos: ', $fav === $fav2 ? 'ya tiene sugerencias' : 'se agregan sugerencias', "\n";
echo '404: ', $t404 ? 'se actualiza la plantilla ' . $t404[0]->ID : 'se crea la plantilla', "\n";
echo 'Orden: ', implode( ', ', array_map( function ( $k, $v ) { return "$k " . count( $v ); }, array_keys( $lineas ), $lineas ) ), "\n";
if ( $errores ) {
	exit( "ERRORES (no se aplicó nada):\n  " . implode( "\n  ", $errores ) . "\n" );
}
if ( ! $apply ) {
	exit( "Modo revisión: no se aplicó nada.\n" );
}

// Copias previas.
$sello = date( 'Ymd-Hi' );
file_put_contents( $copia . "portada-60.ANTES-$sello.html", $portada );
file_put_contents( $copia . "pie-112.ANTES-$sello.html", $pie );
file_put_contents( $copia . "favoritos-1487.ANTES-$sello.html", $fav );
foreach ( $plantillas as $tid => list( $antes ) ) {
	file_put_contents( $copia . "plantilla-$tid.ANTES-$sello.html", $antes );
}
file_put_contents( $copia . "orden-productos.ANTES-$sello.json", wp_json_encode( $orden_antes ) );
$gs = get_post( 29 );
file_put_contents( $copia . "estilos-globales-29.ANTES-$sello.json", $gs->post_content );

// Snippets.
foreach ( array( "D'FANS - Fandom e interacción" => '29-fandom-interaccion.php', "D'FANS - Rediseño visual" => '31-rediseno-visual.php' ) as $nombre => $archivo ) {
	$sn = null;
	foreach ( \Code_Snippets\get_snippets() as $s ) {
		if ( $nombre === $s->name ) {
			$sn = $s;
		}
	}
	if ( ! $sn ) {
		$sn         = new \Code_Snippets\Model\Snippet();
		$sn->name   = $nombre;
		$sn->desc   = 'Fuente de títulos, "Elegir talla", envío/pago/cambios en la ficha, relacionados del mismo fandom, filtros en la tienda, muro de polos e índice legal.';
		$sn->scope  = 'global';
		$sn->active = true;
	} else {
		file_put_contents( $copia . sanitize_file_name( $archivo ) . ".ANTES-$sello.php", "<?php\n" . $sn->code );
	}
	$sn->code = file_get_contents( $base . 'snippets/' . $archivo );
	$sn       = \Code_Snippets\save_snippet( $sn );
	$sn       = \Code_Snippets\get_snippet( $sn->id );
	echo "Snippet #{$sn->id} {$nombre}: activo=", $sn->active ? 'sí' : 'NO', "\n";
}

// CSS global: bloque "Rediseño 2026" al final.
$j     = json_decode( $gs->post_content, true );
$marca = '/* ---------- Rediseño 2026 ---------- */';
$css   = $j['styles']['css'];
$pos   = strpos( $css, $marca );
if ( false !== $pos ) {
	$css = substr( $css, 0, $pos );
}
$j['styles']['css'] = rtrim( $css ) . "\n\n" . ltrim( file_get_contents( $base . 'css/rediseno-2026.css' ) );
wp_update_post( array( 'ID' => 29, 'post_content' => wp_slash( wp_json_encode( $j, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ) ) );
echo 'CSS global: ', strlen( json_decode( get_post( 29 )->post_content, true )['styles']['css'] ), " caracteres\n";

wp_update_post( array( 'ID' => 60, 'post_content' => wp_slash( $p ) ) );
wp_update_post( array( 'ID' => 112, 'post_content' => wp_slash( $f ) ) );
wp_update_post( array( 'ID' => 1487, 'post_content' => wp_slash( $fav2 ) ) );
foreach ( $plantillas as $tid => list( , $despues ) ) {
	wp_update_post( array( 'ID' => $tid, 'post_content' => wp_slash( $despues ) ) );
}
if ( $t404 ) {
	file_put_contents( $copia . "plantilla-404.ANTES-$sello.html", $t404[0]->post_content );
	wp_update_post( array( 'ID' => $t404[0]->ID, 'post_content' => wp_slash( $p404 ) ) );
} else {
	$id404 = wp_insert_post( array( 'post_type' => 'wp_template', 'post_name' => '404', 'post_title' => 'Página no encontrada', 'post_status' => 'publish', 'post_content' => wp_slash( $p404 ) ) );
	wp_set_object_terms( $id404, get_stylesheet(), 'wp_theme' );
	echo "Plantilla 404 creada: $id404\n";
}
foreach ( $orden as $k => $id ) {
	wp_update_post( array( 'ID' => $id, 'menu_order' => $k + 1 ) );
}
global $wpdb;
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '\\_transient\\_wc\\_related\\_%' OR option_name LIKE '\\_transient\\_timeout\\_wc\\_related\\_%'" );
wc_delete_product_transients();
echo "Aplicado.\n";
