<?php
/**
 * D'FANS - Portada v4 «streetwear refinado» (13/11/2026).
 *  1. Hero: adulto y niña con el mismo polo, a pantalla completa (dos fotos), título y dos botones.
 *  2. Compra por persona: Hombre, Mujer, Niño, Niña (cuatro fotos verticales).
 *  3. Lo nuevo: 8 polos.
 *  4. Exclusivos D'FANS: foto grande y los cuatro cortes.
 *  5. Elige por fandom: [dfans_fandoms].
 *  6. Beneficios: envíos, recojo, pagos y cambios.
 * El quiz deja la portada. Copia de la portada anterior en backups/rediseno/. Uso: php rediseno4_portada.php [--apply]
 */
define( 'WP_USE_THEMES', false );
$_SERVER['HTTP_HOST']   = 'dfans.local';
$_SERVER['REQUEST_URI'] = '/';
require 'C:/Users/GRLL/Local Sites/dfans/app/public/wp-load.php';
wp_set_current_user( 1 );

function p4_url( $ruta ) {
	return esc_url( home_url( $ruta ) );
}
function p4_img( $id, $alt, $enlace = '', $clase = '' ) {
	$attr = wp_json_encode( array_filter( array( 'id' => $id, 'sizeSlug' => 'large', 'linkDestination' => $enlace ? 'custom' : 'none', 'className' => $clase ) ), JSON_UNESCAPED_SLASHES );
	$img  = '<img src="' . esc_url( wp_get_attachment_image_url( $id, 'large' ) ) . '" alt="' . esc_attr( $alt ) . '" class="wp-image-' . (int) $id . '"/>';
	return "<!-- wp:image $attr -->\n<figure class=\"wp-block-image size-large" . ( $clase ? " $clase" : '' ) . '">'
		. ( $enlace ? '<a href="' . esc_url( $enlace ) . '">' . $img . '</a>' : $img )
		. "</figure>\n<!-- /wp:image -->";
}
function p4_boton( $texto, $ruta, $clase ) {
	return "<!-- wp:button {\"className\":\"$clase\"} -->\n<div class=\"wp-block-button $clase\"><a class=\"wp-block-button__link wp-element-button\" href=\"" . p4_url( $ruta ) . '">' . esc_html( $texto ) . "</a></div>\n<!-- /wp:button -->";
}
function p4_grupo( $clase, $interior, $align = '', $layout = 'default' ) {
	$a = $align ? ",\"align\":\"$align\"" : '';
	$c = $align ? " align$align" : '';
	return "<!-- wp:group {\"className\":\"$clase\"$a,\"layout\":{\"type\":\"$layout\"}} -->\n<div class=\"wp-block-group$c $clase\">$interior</div>\n<!-- /wp:group -->";
}
function p4_seccion( $clase, $interior ) {
	return p4_grupo( "dfans-p $clase", $interior, 'full', 'constrained' );
}
function p4_cabecera( $titulo, $enlace_html = '' ) {
	return p4_grupo( 'dfans-p-cabecera', "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">$titulo</h2>\n<!-- /wp:heading -->"
		. ( $enlace_html ? "\n\n<!-- wp:paragraph {\"className\":\"dfans-p-ver\"} -->\n<p class=\"dfans-p-ver\">$enlace_html</p>\n<!-- /wp:paragraph -->" : '' ), 'wide' );
}
function p4_parrafo( $html, $clase = '' ) {
	return $clase
		? "<!-- wp:paragraph {\"className\":\"$clase\"} -->\n<p class=\"$clase\">$html</p>\n<!-- /wp:paragraph -->"
		: "<!-- wp:paragraph -->\n<p>$html</p>\n<!-- /wp:paragraph -->";
}
function p4_thumb( $slug ) {
	$p = get_page_by_path( $slug, OBJECT, 'product' );
	if ( ! $p ) {
		exit( "ERROR: no existe $slug\n" );
	}
	return (int) get_post_thumbnail_id( $p->ID );
}

$demon = '/product/polo-demon-slayer/';
$s     = array();

// 1. Hero.
$fotos = p4_img( 100, 'Hombre con el polo Demon Slayer de D’FANS', home_url( $demon . '?seccion=adultos&para=hombre' ) )
	. "\n\n" . p4_img( 106, 'Niña con el mismo polo Demon Slayer en talla de niño', home_url( $demon . '?seccion=ninos&para=nina' ) );
$texto = p4_grupo( 'dfans-h4__marco',
	"<!-- wp:heading {\"level\":1} -->\n<h1 class=\"wp-block-heading\">El mismo polo para toda la familia</h1>\n<!-- /wp:heading -->\n\n"
	. p4_parrafo( 'Colección Match: cada diseño en tallas de niño (10 a 14) y de adulto (S a XXL). Desde S/ 30, hechos en Gamarra.' ) . "\n\n"
	. "<!-- wp:buttons -->\n<div class=\"wp-block-buttons\">" . p4_boton( 'Comprar Match', '/product-category/match-familiar/', 'dfans-b-primario' ) . "\n\n"
	. p4_boton( 'Ver toda la tienda', '/shop/', 'dfans-b-secundario' ) . "</div>\n<!-- /wp:buttons -->"
);
// v6 (13/11/2026): el usuario eligió la "Cartelera" (sin la tarjeta de la niña y con letras más grandes).
$foto_url  = esc_url( wp_get_attachment_image_url( 100, 'full' ) );
// v8: las miniaturas cambian la foto grande (el JS está en el snippet #33).
$tambien   = '';
foreach ( array( array( 100, 'Hombre', 'adultos', 'hombre' ), array( 102, 'Mujer', 'adultos', 'mujer' ), array( 104, 'Niño', 'ninos', 'nino' ), array( 106, 'Niña', 'ninos', 'nina' ) ) as list( $id, $n, $seccion, $para ) ) {
	$tambien .= '<button type="button" class="dfans-cartelera__elige" aria-pressed="' . ( 100 === $id ? 'true' : 'false' ) . '" aria-label="' . esc_attr( "Ver el polo en $n" ) . '"'
		. ' data-foto="' . esc_url( wp_get_attachment_image_url( $id, 'full' ) ) . '" data-href="' . p4_url( $demon . "?seccion=$seccion&para=$para" ) . '">'
		. '<img src="' . esc_url( wp_get_attachment_image_url( $id, 'medium' ) ) . '" alt="" loading="lazy"></button>';
}
$cartelera = '<section class="dfans-cartelera" aria-label="Colección Match">'
	. '<a class="dfans-cartelera__foto" href="' . p4_url( $demon . '?seccion=adultos&para=hombre' ) . '" tabindex="-1" aria-hidden="true"><img src="' . $foto_url . '" alt="" fetchpriority="high"></a>'
	. '<div class="dfans-cartelera__texto">'
	. '<p class="dfans-cartelera__chip">Marca peruana</p>'
	. '<h1 class="dfans-cartelera__titulo"><span>Desata</span> <span class="dfans-energia">tu fandom</span></h1>'
	. '<p class="dfans-cartelera__bajada">Polos de anime, rock, metal, artistas y videojuegos con diseño propio, estampados en Gamarra. Más de 80 modelos, de la talla 10 a la XXL, con envíos a todo el Perú.</p>'
	. '<div class="dfans-cartelera__botones"><a class="dfans-cbtn dfans-cbtn--rojo" href="' . p4_url( '/shop/' ) . '">Comprar ahora</a><a class="dfans-cbtn dfans-cbtn--borde" href="' . p4_url( '/product-category/match-familiar/' ) . '">Colección Match</a></div>'
	. '<div class="dfans-cartelera__tambien"><span>Míralo en</span>' . $tambien . '</div>'
	. '</div></section>';
$s[] = p4_grupo( 'dfans-cartelera-wrap', "<!-- wp:html -->\n" . $cartelera . "\n<!-- /wp:html -->", 'full' );

// 1b. Colecciones (v5, con la referencia del usuario): fila de pestañas y tres tarjetas grandes.
$cuenta_match = (int) get_term_by( 'slug', 'match-familiar', 'product_cat' )->count;
$colecciones  = array(
	array( 'Novedades', '/shop/', 'baby-tee-metal-angel', (int) wp_count_posts( 'product' )->publish . ' polos en la tienda' ),
	array( 'Colección Match', '/product-category/match-familiar/', 102, $cuenta_match . ' diseños para toda la familia' ),
	array( 'Niños', '/product-category/ropa-para-ninos/', 'polo-nino-pixel-knight', 'Tallas 10 a 14' ),
);
$pestanas = '';
$tarjetas = array();
foreach ( $colecciones as $i => list( $nombre, $ruta, $foto, $dato ) ) {
	$pestanas  .= "<!-- wp:list-item -->\n<li" . ( 0 === $i ? ' class="is-activa"' : '' ) . '><a href="' . p4_url( $ruta ) . "\">$nombre</a></li>\n<!-- /wp:list-item -->\n\n";
	$tarjetas[] = p4_grupo( 'dfans-coleccion',
		p4_img( is_int( $foto ) ? $foto : p4_thumb( $foto ), '', home_url( $ruta ) ) . "\n\n"
		. p4_parrafo( '<a href="' . p4_url( $ruta ) . '">' . esc_html( $nombre ) . '</a>', 'dfans-coleccion__nombre' ) . "\n\n"
		. p4_parrafo( esc_html( $dato ), 'dfans-coleccion__dato' )
	);
}
$col = p4_seccion( 'dfans-p-colecciones',
	"<!-- wp:heading {\"className\":\"screen-reader-text\"} -->\n<h2 class=\"wp-block-heading screen-reader-text\">Colecciones</h2>\n<!-- /wp:heading -->\n\n"
	. "<!-- wp:list {\"className\":\"dfans-pestanas alignwide\"} -->\n<ul class=\"wp-block-list dfans-pestanas alignwide\">" . rtrim( $pestanas ) . "</ul>\n<!-- /wp:list -->\n\n"
	. p4_grupo( 'dfans-colecciones', implode( "\n\n", $tarjetas ), 'wide' )
);

// 2. Compra por persona.
$personas = array(
	array( 'polo-oversize-beat-maker', 'Hombre', '/product-category/ropa-de-adultos/?para=hombre' ),
	array( 'baby-tee-chica-magica-luna', 'Mujer', '/product-category/ropa-de-adultos/?para=mujer' ),
	array( 'polo-nino-dino-gamer', 'Niño', '/product-category/ropa-para-ninos/?para=nino' ),
	array( 'polo-nina-boba-panda', 'Niña', '/product-category/ropa-para-ninos/?para=nina' ),
);
$tiles = array();
foreach ( $personas as list( $slug, $nombre, $ruta ) ) {
	$tiles[] = p4_grupo( 'dfans-persona',
		p4_img( p4_thumb( $slug ), '', home_url( $ruta ) ) . "\n\n"
		. p4_parrafo( '<a href="' . p4_url( $ruta ) . '">' . esc_html( $nombre ) . '</a>', 'dfans-persona__nombre' )
	);
}
$s[] = p4_seccion( 'dfans-p-personas', p4_cabecera( 'Compra por persona' ) . "\n\n" . p4_grupo( 'dfans-personas', implode( "\n\n", $tiles ), 'wide' ) );

// 3. Lo nuevo.
$total = (int) wp_count_posts( 'product' )->publish;
$s[]   = p4_seccion( 'dfans-p-nuevos',
	p4_cabecera( 'Lo nuevo', '<a href="' . p4_url( '/shop/' ) . '">Ver los ' . $total . ' polos</a><button type="button" class="dfans-flecha" data-dir="izq" aria-label="Ver anteriores">‹</button><button type="button" class="dfans-flecha" data-dir="der" aria-label="Ver siguientes">›</button>' )
	. "\n\n" . '<!-- wp:woocommerce/product-collection {"queryId":61,"query":{"perPage":12,"pages":1,"offset":0,"postType":"product","order":"asc","orderBy":"menu_order","search":"","exclude":[],"inherit":false,"taxQuery":[],"isProductCollectionBlock":true,"featured":false,"woocommerceOnSale":false,"woocommerceStockStatus":["instock","onbackorder"],"woocommerceAttributes":[],"woocommerceHandPickedProducts":[]},"tagName":"div","displayLayout":{"type":"flex","columns":4,"shrinkColumns":true},"dimensions":{"widthType":"fill"},"queryContextIncludes":["collection"],"align":"wide"} -->' . "\n"
	. '<div class="wp-block-woocommerce-product-collection alignwide"><!-- wp:woocommerce/product-template -->' . "\n"
	. '<!-- wp:woocommerce/product-image {"showSaleBadge":false,"imageSizing":"thumbnail","isDescendentOfQueryLoop":true} /-->' . "\n"
	. '<!-- wp:post-title {"level":3,"isLink":true,"__woocommerceNamespace":"woocommerce/product-collection/product-title"} /-->' . "\n"
	. '<!-- wp:woocommerce/product-price {"isDescendentOfQueryLoop":true} /-->' . "\n"
	. "<!-- /wp:woocommerce/product-template --></div>\n<!-- /wp:woocommerce/product-collection -->"
);

// 4. Exclusivos.
$cortes = array(
	array( 'Oversize', 'para él', '/product-category/ropa-de-adultos/?para=hombre' ),
	array( 'Baby tee', 'para ella', '/product-category/ropa-de-adultos/?para=mujer' ),
	array( 'Regular reforzado', 'para niño', '/product-category/ropa-para-ninos/?para=nino' ),
	array( 'Manga mariposa', 'para niña', '/product-category/ropa-para-ninos/?para=nina' ),
);
$lista = '';
foreach ( $cortes as list( $c, $para, $ruta ) ) {
	$lista .= "<!-- wp:list-item -->\n<li><a href=\"" . p4_url( $ruta ) . "\">$c</a> <span>$para</span></li>\n<!-- /wp:list-item -->\n\n";
}
$panel = p4_grupo( 'dfans-editorial__texto',
	"<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">Exclusivos D’FANS</h2>\n<!-- /wp:heading -->\n\n"
	. p4_parrafo( 'Diseños propios que no están en la colección Match, en cuatro cortes pensados para cada persona.' ) . "\n\n"
	. "<!-- wp:list {\"className\":\"dfans-cortes\"} -->\n<ul class=\"wp-block-list dfans-cortes\">" . rtrim( $lista ) . "</ul>\n<!-- /wp:list -->"
);
$s[] = p4_grupo( 'dfans-p dfans-editorial', p4_img( p4_thumb( 'polo-oversize-boss-fight' ), 'Polo oversize Boss Fight de la línea exclusiva', home_url( '/product/polo-oversize-boss-fight/' ), 'dfans-editorial__foto' ) . "\n\n" . $panel, 'full' );

// 5. Fandoms.
$s[] = p4_seccion( 'dfans-p-fandoms', p4_cabecera( 'Elige por fandom' ) . "\n\n<!-- wp:shortcode -->\n[dfans_fandoms]\n<!-- /wp:shortcode -->" );

// 6. Beneficios.
$beneficios = array(
	array( 'envio', 'Envíos a todo el Perú', 'Delivery en Lima y envío a provincia por agencia.' ),
	array( 'tienda', 'Recojo gratis en Gamarra', 'Jr. Agustín Gamarra, La Victoria.' ),
	array( 'pago', 'Tarjeta, Yape o transferencia', 'Con tarjeta el pago se confirma al instante.' ),
	array( 'cambio', 'Cambios de talla', 'Si no te quedó, cambias tu talla.' ),
);
$b = '';
foreach ( $beneficios as list( $icono, $t, $d ) ) {
	$b .= p4_grupo( "dfans-ventaja dfans-ventaja--$icono", "<!-- wp:heading {\"level\":3} -->\n<h3 class=\"wp-block-heading\">$t</h3>\n<!-- /wp:heading -->\n\n" . p4_parrafo( $d ) ) . "\n\n";
}
$s[] = p4_seccion( 'dfans-p-ventajas', p4_grupo( 'dfans-ventajas', rtrim( $b ), 'wide' ) );

// A (v7): Explora por fandom, en círculos con fotos de personas usando polos de cada fandom.
$circulos = array(
	array( 'Anime', '/shop/?tematica=anime', 177 ),
	array( 'Rock', '/shop/?tematica=rock', 856 ),
	array( 'Metal', '/shop/?tematica=metal', 818 ),
	array( 'Artistas', '/shop/?tematica=artistas', 946 ),
	array( 'Películas', '/shop/?tematica=peliculas-y-series', 1016 ),
	array( 'Videojuegos', '/shop/?tematica=videojuegos', 829 ),
	array( 'Match', '/product-category/match-familiar/', 206 ),
	array( 'Niños', '/product-category/ropa-para-ninos/', p4_thumb( 'polo-nina-dino-rosa' ) ),
);
$c = '';
foreach ( $circulos as list( $n, $ruta, $img ) ) {
	$c .= '<a class="dfans-circ" href="' . p4_url( $ruta ) . '"><span class="dfans-circ__foto"><img src="' . esc_url( wp_get_attachment_image_url( $img, 'medium_large' ) ) . '" alt="" loading="lazy"></span><span class="dfans-circ__nombre">' . esc_html( $n ) . '</span></a>';
}
$circ = p4_seccion( 'dfans-p-circulos', p4_cabecera( 'Explora por fandom' ) . "\n\n" . p4_grupo( 'dfans-circulos-wrap', "<!-- wp:html -->\n<nav class=\"dfans-circulos\" aria-label=\"Fandoms\">$c</nav>\n<!-- /wp:html -->", 'wide' ) );

// B (v7): dos banners de exclusivos (para él y para ella) en lugar del bloque de exclusivos.
$banner = function ( $img, $titulo, $texto, $boton, $ruta, $clase ) {
	return '<a class="dfans-banner" href="' . p4_url( $ruta ) . '"><img src="' . esc_url( wp_get_attachment_image_url( $img, 'full' ) ) . '" alt="" loading="lazy">'
		. '<span class="dfans-banner__texto"><span class="dfans-banner__titulo">' . $titulo . '</span><span class="dfans-banner__bajada">' . $texto . '</span><span class="dfans-cbtn ' . $clase . '">' . $boton . '</span></span></a>';
};
$banners = p4_seccion( 'dfans-p-banners', "<!-- wp:html -->\n<div class=\"dfans-banners\">"
	. $banner( p4_thumb( 'polo-oversize-boss-fight' ), 'Exclusivos para él', 'Oversize con hombros caídos y largo extra. Tallas S a XXL.', 'Ver oversize', '/product-category/ropa-de-adultos/?para=hombre', 'dfans-cbtn--rojo' )
	. $banner( p4_thumb( 'baby-tee-dark-kawaii' ), 'Exclusivos para ella', 'Baby tee entallado y de largo crop. Tallas XS a L.', 'Ver baby tee', '/product-category/ropa-de-adultos/?para=mujer', 'dfans-cbtn--borde' )
	. "</div>\n<!-- /wp:html -->" );

// Orden v7: hero, fandoms en círculos, colecciones, lo nuevo (carrusel), exclusivos (2 banners), compra por persona y ventajas.
// Salen el bloque de exclusivos con lista de cortes y el índice de fandoms (los reemplazan B y A).
$s     = array( $s[0], $circ, $col, $s[2], $banners, $s[1], $s[5] );
$nuevo = implode( "\n\n", $s );
$antes = get_post( 60 )->post_content;
echo 'Portada: ', strlen( $antes ), ' -> ', strlen( $nuevo ), " caracteres, 6 secciones\n";
if ( ! in_array( '--apply', $argv, true ) ) {
	exit( "Modo revisión.\n" );
}
file_put_contents( 'C:/Users/GRLL/Local Sites/dfans/backups/rediseno/portada-60.ANTES-V4-' . date( 'Ymd-Hi' ) . '.html', $antes );
wp_update_post( array( 'ID' => 60, 'post_content' => wp_slash( $nuevo ) ) );
echo "Portada actualizada.\n";
