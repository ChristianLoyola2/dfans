<?php
/**
 * D'FANS - Fase 1: secciones Match / Adultos / Niños (30/09/2026)
 *  - Snippet #25: bloque "Tallas según la sección".
 *  - Snippet nuevo: "D'FANS - Secciones Match, Adultos y Niños".
 *  - CSS de secciones en los estilos globales (29).
 *  - Plantillas taxonomy-product_cat-ropa-de-adultos y taxonomy-product_cat-ropa-para-ninos.
 *  - Menú (4): Match familiar y temáticas con filtro.
 * Guarda copia de todo lo que cambia en backups/fase1/.
 * Uso: php fase1_secciones.php [--apply]
 */
define( 'CODE_SNIPPETS_SAFE_MODE', true );
define( 'WP_USE_THEMES', false );
$_SERVER['HTTP_HOST']   = 'dfans.local';
$_SERVER['REQUEST_URI'] = '/';
require 'C:/Users/GRLL/Local Sites/dfans/app/public/wp-load.php';
wp_set_current_user( 1 );

$apply = in_array( '--apply', $argv, true );
$base  = 'C:/Users/GRLL/Local Sites/dfans/backups/';
$copia = $base . 'fase1/';

function leer( $archivo ) {
	$c = file_get_contents( $archivo );
	if ( false === $c || '' === trim( $c ) ) {
		exit( "ERROR: no se pudo leer $archivo. No se aplicó nada.\n" );
	}
	return $c;
}

$bloque_tallas = leer( $base . 'snippets/25-bloque-tallas-por-seccion.php' );
$codigo_28     = leer( $base . 'snippets/28-secciones-match-adultos-ninos.php' );
$css_nuevo     = leer( $base . 'css/secciones-match-adultos-ninos.css' );
$match         = get_term_by( 'slug', 'match-familiar', 'product_cat' );
if ( ! $match ) {
	exit( "ERROR: falta la categoría Match familiar (correr antes fase1_unisex_match.php).\n" );
}

/** Bloque de productos de la sección (queryId 41 = Match, 42 = solo la sección). */
function dfans_bloque_productos( $query_id, $sin_resultados ) {
	$attrs = array(
		'queryId'              => $query_id,
		'query'                => array(
			'woocommerceAttributes'    => array(),
			'woocommerceStockStatus'   => array( 'instock', 'outofstock', 'onbackorder' ),
			'taxQuery'                 => new stdClass(),
			'isProductCollectionBlock' => true,
			'perPage'                  => 12,
			'pages'                    => 0,
			'offset'                   => 0,
			'postType'                 => 'product',
			'order'                    => 'asc',
			'orderBy'                  => 'title',
			'author'                   => '',
			'search'                   => '',
			'exclude'                  => array(),
			'sticky'                   => '',
			'inherit'                  => false,
		),
		'tagName'              => 'div',
		'displayLayout'        => array( 'type' => 'flex', 'columns' => 4, 'shrinkColumns' => true ),
		'queryContextIncludes' => array( 'collection' ),
		'align'                => 'wide',
	);
	return '<!-- wp:woocommerce/product-collection ' . wp_json_encode( $attrs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . ' -->
<div class="wp-block-woocommerce-product-collection alignwide"><!-- wp:woocommerce/product-template -->
<!-- wp:woocommerce/product-image {"showSaleBadge":false,"imageSizing":"thumbnail","isDescendentOfQueryLoop":true} -->
<!-- wp:woocommerce/product-sale-badge {"isDescendentOfQueryLoop":true,"align":"right"} /-->
<!-- /wp:woocommerce/product-image -->
<!-- wp:post-title {"textAlign":"center","level":3,"isLink":true,"fontSize":"medium","__woocommerceNamespace":"woocommerce/product-collection/product-title","style":{"typography":{"lineHeight":"1.4"}}} /-->
<!-- wp:woocommerce/product-price {"textAlign":"center","isDescendentOfQueryLoop":true,"fontSize":"small","style":{"spacing":{"margin":{"bottom":"1rem"}}}} /-->
<!-- wp:woocommerce/product-button {"textAlign":"center","isDescendentOfQueryLoop":true,"fontSize":"small","style":{"spacing":{"margin":{"bottom":"1rem"}}}} /-->
<!-- /wp:woocommerce/product-template -->
<!-- wp:query-pagination {"layout":{"type":"flex","justifyContent":"center"}} -->
<!-- wp:query-pagination-previous /-->
<!-- wp:query-pagination-numbers /-->
<!-- wp:query-pagination-next /-->
<!-- /wp:query-pagination -->
<!-- wp:woocommerce/product-collection-no-results -->
<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">' . esc_html( $sin_resultados ) . '</p>
<!-- /wp:paragraph -->
<!-- /wp:woocommerce/product-collection-no-results --></div>
<!-- /wp:woocommerce/product-collection -->';
}

function dfans_plantilla( $match_texto, $solo_titulo ) {
	return '<!-- wp:template-part {"slug":"header"} /-->

<!-- wp:group {"tagName":"main","className":"dfans-seccion-pagina","layout":{"type":"constrained"}} -->
<main class="wp-block-group dfans-seccion-pagina"><!-- wp:woocommerce/breadcrumbs /-->

<!-- wp:query-title {"type":"archive","showPrefix":false,"align":"wide"} /-->

<!-- wp:term-description {"align":"wide"} /-->

<!-- wp:woocommerce/store-notices /-->

<!-- wp:group {"align":"wide","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide"><!-- wp:shortcode -->
[dfans_filtros]
<!-- /wp:shortcode --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"wide","className":"dfans-seccion dfans-seccion-match","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide dfans-seccion dfans-seccion-match"><!-- wp:heading -->
<h2 class="wp-block-heading">Colección Match <span class="dfans-tag">Familia</span></h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>' . esc_html( $match_texto ) . '</p>
<!-- /wp:paragraph -->

' . dfans_bloque_productos( 41, 'No hay polos Match con este filtro.' ) . '</div>
<!-- /wp:group -->

<!-- wp:group {"align":"wide","className":"dfans-seccion dfans-seccion-solo","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide dfans-seccion dfans-seccion-solo"><!-- wp:heading -->
<h2 class="wp-block-heading">' . esc_html( $solo_titulo ) . '</h2>
<!-- /wp:heading -->

' . dfans_bloque_productos( 42, 'No hay polos con este filtro.' ) . '</div>
<!-- /wp:group --></main>
<!-- /wp:group -->

<!-- wp:template-part {"slug":"footer"} /-->';
}

$plantillas = array(
	'taxonomy-product_cat-ropa-de-adultos' => array(
		'Sección Adultos',
		dfans_plantilla( 'El mismo diseño también viene en tallas de niños: cómpralo para hacer match con tus hijos o solo para ti.', 'Solo adultos' ),
	),
	'taxonomy-product_cat-ropa-para-ninos' => array(
		'Sección Niños',
		dfans_plantilla( 'El mismo diseño también viene en tallas de adulto: haz match con papá y mamá, o llévalo solo para ti.', 'Solo niños' ),
	),
);

/** Enlaces del menú: temáticas de una sección con filtro. */
function dfans_submenu( $etiqueta, $term_id, $slug ) {
	$items = '';
	foreach ( array( 'anime' => 'Anime', 'rock' => 'Rock', 'metal' => 'Metal', 'artistas' => 'Artistas', 'peliculas-y-series' => 'Películas y Series', 'videojuegos' => 'Videojuegos' ) as $t => $nombre ) {
		$items .= '<!-- wp:navigation-link ' . wp_json_encode( array( 'label' => $nombre, 'url' => "/product-category/$slug/?tematica=$t", 'kind' => 'custom' ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . " /-->\n\n";
	}
	return '<!-- wp:navigation-submenu ' . wp_json_encode( array( 'label' => $etiqueta, 'type' => 'product_cat', 'id' => $term_id, 'url' => "/product-category/$slug/", 'kind' => 'taxonomy' ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . " -->\n" . $items . '<!-- /wp:navigation-submenu -->';
}
$nav     = get_post( 4 );
$partes  = explode( '<!-- wp:navigation-submenu', $nav->post_content, 2 );
$menu    = rtrim( $partes[0] ) . "\n\n" .
	'<!-- wp:navigation-link ' . wp_json_encode( array( 'label' => 'Match familiar', 'type' => 'product_cat', 'id' => $match->term_id, 'url' => '/product-category/match-familiar/', 'kind' => 'taxonomy' ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . " /-->\n\n" .
	dfans_submenu( 'Adultos', 65, 'ropa-de-adultos' ) . "\n\n" . dfans_submenu( 'Niños', 66, 'ropa-para-ninos' );

echo "Plantillas: ", implode( ', ', array_keys( $plantillas ) ), "\nMenú nuevo:\n", preg_replace( '/"url":"[^"]*",?|<!-- \/wp:[a-z-]+ -->|\n\n/', '', $menu ), "\n";
if ( ! $apply ) {
	echo "Modo revisión: no se aplicó nada.\n";
	exit;
}
wp_mkdir_p( $copia );

// Snippet #25: tallas según la sección.
$s = \Code_Snippets\get_snippet( 25 );
if ( false === strpos( $s->code, 'Tallas según la sección' ) ) {
	file_put_contents( $copia . '25-modelo-y-tallas-dinamicas.ANTES-SECCIONES.php', "<?php\n" . $s->code );
	$s->code = rtrim( $s->code ) . "\n" . $bloque_tallas;
	\Code_Snippets\save_snippet( $s );
}
$s = \Code_Snippets\get_snippet( 25 );
echo '#25 activo=', $s->active ? 'si' : 'NO', ' tallas por sección=', false !== strpos( $s->code, 'Tallas según la sección' ) ? 'si' : 'no', "\n";

// Snippet nuevo de secciones.
$existente = null;
foreach ( \Code_Snippets\get_snippets() as $sn ) {
	if ( "D'FANS - Secciones Match, Adultos y Niños" === $sn->name ) {
		$existente = $sn;
	}
}
if ( ! $existente ) {
	$sn         = new \Code_Snippets\Model\Snippet();
	$sn->name   = "D'FANS - Secciones Match, Adultos y Niños";
	$sn->desc   = 'Fotos, precios, tallas, filtros y bloques según la sección (Adultos / Niños). Fase 1, 30/09/2026.';
	$sn->code   = $codigo_28;
	$sn->scope  = 'global';
	$sn->active = true;
	$existente  = \Code_Snippets\save_snippet( $sn );
}
$sn = \Code_Snippets\get_snippet( $existente->id );
echo "Snippet secciones #{$sn->id} activo=", $sn->active ? 'si' : 'NO', ' largo=', strlen( $sn->code ), "\n";

// CSS.
$g = get_post( 29 );
$j = json_decode( $g->post_content, true );
if ( false === strpos( $j['styles']['css'], 'Secciones Match, Adultos y Niños' ) ) {
	file_put_contents( $copia . 'estilos-globales-29.ANTES-SECCIONES.json', $g->post_content );
	$antes               = strlen( $j['styles']['css'] );
	$j['styles']['css'] .= "\n" . $css_nuevo;
	wp_update_post( array( 'ID' => 29, 'post_content' => wp_slash( wp_json_encode( $j ) ) ) );
	echo "CSS: $antes -> ", strlen( json_decode( get_post( 29 )->post_content, true )['styles']['css'] ), " caracteres\n";
}

// Plantillas de Adultos y Niños.
$tema = get_stylesheet();
foreach ( $plantillas as $slug => list( $titulo, $contenido ) ) {
	$ya = get_posts( array( 'post_type' => 'wp_template', 'name' => $slug, 'post_status' => 'any', 'tax_query' => array( array( 'taxonomy' => 'wp_theme', 'field' => 'name', 'terms' => $tema ) ) ) );
	if ( $ya ) {
		echo "Plantilla $slug ya existe (#{$ya[0]->ID}), no se toca.\n";
		continue;
	}
	$id = wp_insert_post( array(
		'post_type'    => 'wp_template',
		'post_name'    => $slug,
		'post_title'   => $titulo,
		'post_status'  => 'publish',
		'post_content' => wp_slash( $contenido ),
	), true );
	if ( is_wp_error( $id ) ) {
		exit( "ERROR plantilla $slug: " . $id->get_error_message() . "\n" );
	}
	wp_set_object_terms( $id, $tema, 'wp_theme' );
	echo "Plantilla $slug creada (#$id)\n";
}

// Menú.
if ( false === strpos( $nav->post_content, 'match-familiar' ) ) {
	file_put_contents( $copia . 'navegacion-4.ANTERIOR.html', $nav->post_content );
	wp_update_post( array( 'ID' => 4, 'post_content' => wp_slash( $menu ) ) );
	echo "Menú actualizado.\n";
}
