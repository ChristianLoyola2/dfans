<?php
/**
 * Snippet #28: D'FANS - Secciones Match, Adultos y Niños
 * Alcance: global · prioridad 10
 * Exportado de Code Snippets el 14/11/2026. La copia activa está en la base de datos.
 */

/*
 * D'FANS - Secciones Match, Adultos y Niños
 *
 * Los polos Match (unisex) tienen tallas de adulto (S-XXL) y de niños (10-14).
 * Según dónde está el cliente se muestra solo lo que le corresponde:
 *  - Categoría Ropa de Adultos (o sus subcategorías): fotos de adultos, precio de adultos.
 *  - Categoría Ropa para Niños (o sus subcategorías): fotos de niños, precio de niños.
 *  - Producto abierto desde una sección: el enlace lleva ?seccion=adultos|ninos.
 *  - Sin sección (Match, tienda, buscador): todas las fotos y tallas.
 * Las fotos de cada modelo están en el meta _dfans_fotos del producto.
 * Las páginas de Adultos y Niños usan las plantillas taxonomy-product_cat-ropa-de-adultos
 * y taxonomy-product_cat-ropa-para-ninos (bloques con queryId 41 = Match, 42 = solo la sección).
 */

define( 'DFANS_SECCIONES', array(
	'adultos' => 'ropa-de-adultos',
	'ninos'   => 'ropa-para-ninos',
) );

define( 'DFANS_TEMATICAS', array(
	'anime'              => 'Anime',
	'rock'               => 'Rock',
	'metal'              => 'Metal',
	'artistas'           => 'Artistas',
	'peliculas-y-series' => 'Películas y Series',
	'videojuegos'        => 'Videojuegos',
) );

/** Sección en la que está el cliente: 'adultos', 'ninos' o ''. */
function dfans_seccion() {
	if ( ( is_admin() && ! wp_doing_ajax() ) || ! did_action( 'wp' ) ) {
		return '';
	}
	if ( is_product_category() ) {
		$termino = get_queried_object();
		foreach ( DFANS_SECCIONES as $seccion => $slug ) {
			$raiz = get_term_by( 'slug', $slug, 'product_cat' );
			if ( $raiz && ( $termino->term_id === $raiz->term_id || term_is_ancestor_of( $raiz, $termino, 'product_cat' ) ) ) {
				return $seccion;
			}
		}
		return '';
	}
	if ( is_product() && isset( $_GET['seccion'] ) ) {
		$seccion = sanitize_key( wp_unslash( $_GET['seccion'] ) );
		return isset( DFANS_SECCIONES[ $seccion ] ) ? $seccion : '';
	}
	return '';
}

/** Fotos del producto por modelo, o null si no tiene. */
function dfans_fotos( $product_id ) {
	$fotos = get_post_meta( $product_id, '_dfans_fotos', true );
	return is_array( $fotos ) && isset( $fotos['hombre'], $fotos['mujer'], $fotos['nino'], $fotos['nina'] ) ? $fotos : null;
}

/** Filtros elegidos en la página de la sección. */
function dfans_filtro( $nombre ) {
	$valor = isset( $_GET[ $nombre ] ) ? sanitize_key( wp_unslash( $_GET[ $nombre ] ) ) : '';
	if ( 'tematica' === $nombre ) {
		return isset( DFANS_TEMATICAS[ $valor ] ) ? $valor : '';
	}
	$validos = 'ninos' === dfans_seccion() ? array( 'nino', 'nina' ) : array( 'hombre', 'mujer' );
	return in_array( $valor, $validos, true ) ? $valor : '';
}

// Los productos abiertos desde una sección conservan la sección (y el filtro Para) en el enlace.
add_filter( 'post_type_link', function ( $url, $post ) {
	if ( 'product' === $post->post_type && dfans_seccion() ) {
		$url = add_query_arg( array_filter( array( 'seccion' => dfans_seccion(), 'para' => dfans_filtro( 'para' ) ) ), $url );
	}
	return $url;
}, 10, 2 );

// Imagen principal según la sección; con el filtro Para, la del modelo elegido.
add_filter( 'woocommerce_product_get_image_id', function ( $image_id, $product ) {
	$seccion = dfans_seccion();
	$fotos   = $seccion ? dfans_fotos( $product->get_id() ) : null;
	if ( ! $fotos ) {
		return $image_id;
	}
	$para = dfans_filtro( 'para' );
	if ( $para ) {
		return $fotos[ $para ]['delantera'];
	}
	return 'adultos' === $seccion ? $fotos['hombre']['delantera'] : $fotos['nino']['delantera'];
}, 10, 2 );

// Galería según la sección; sin sección se añaden las fotos de los 4 modelos.
add_filter( 'woocommerce_product_get_gallery_image_ids', function ( $ids, $product ) {
	if ( ( is_admin() && ! wp_doing_ajax() ) || ! did_action( 'wp' ) ) {
		return $ids;
	}
	$fotos = dfans_fotos( $product->get_id() );
	if ( ! $fotos ) {
		return $ids;
	}
	$seccion = dfans_seccion();
	$para    = $seccion ? dfans_filtro( 'para' ) : '';
	if ( $para ) {
		return array( $fotos[ $para ]['espalda'] );
	}
	if ( 'adultos' === $seccion ) {
		return array( $fotos['mujer']['delantera'], $fotos['hombre']['espalda'], $fotos['mujer']['espalda'] );
	}
	if ( 'ninos' === $seccion ) {
		return array( $fotos['nina']['delantera'], $fotos['nino']['espalda'], $fotos['nina']['espalda'] );
	}
	$modelos = array();
	foreach ( array( 'delantera', 'espalda' ) as $lado ) {
		foreach ( array( 'hombre', 'mujer', 'nino', 'nina' ) as $m ) {
			$modelos[] = $fotos[ $m ][ $lado ];
		}
	}
	return array_values( array_unique( array_merge( $ids, $modelos ) ) );
}, 10, 2 );

// Precio según la sección (adultos S/35-S/40, niños S/30).
add_filter( 'woocommerce_get_price_html', function ( $html, $product ) {
	$seccion = dfans_seccion();
	if ( ! $seccion || ! $product->is_type( 'variable' ) ) {
		return $html;
	}
	$tallas  = 'adultos' === $seccion ? array( 'xs', 's', 'm', 'l', 'xl', 'xxl' ) : array( '10', '12', '14' );
	$precios = array();
	foreach ( $product->get_variation_prices( true )['price'] as $vid => $precio ) {
		if ( in_array( get_post_meta( $vid, 'attribute_pa_talla', true ), $tallas, true ) ) {
			$precios[] = $precio;
		}
	}
	if ( ! $precios ) {
		return $html;
	}
	return min( $precios ) === max( $precios ) ? wc_price( min( $precios ) ) : wc_format_price_range( min( $precios ), max( $precios ) );
}, 10, 2 );

// Tarjetas: etiqueta MATCH o del corte (Oversize, Baby tee…) y segunda foto al pasar el ratón:
// en Match, la de mujer o niña según la sección (con el filtro Para, la espalda del mismo modelo);
// en los exclusivos, la espalda con modelo.
add_filter( 'render_block_woocommerce/product-image', function ( $html, $block, $instance ) {
	$product_id = $instance->context['postId'] ?? 0;
	if ( ! $product_id || ! $html ) {
		return $html;
	}
	$extra   = '';
	$seccion = dfans_seccion();
	$para    = $seccion ? dfans_filtro( 'para' ) : '';
	$fotos   = dfans_fotos( $product_id );
	$alt     = 0;
	if ( $fotos && $para ) {
		$alt = $fotos[ $para ]['espalda'];
	} elseif ( $fotos && $seccion ) {
		$alt = 'adultos' === $seccion ? $fotos['mujer']['delantera'] : $fotos['nina']['delantera'];
	} elseif ( ! $fotos ) {
		$producto = wc_get_product( $product_id );
		$alt      = $producto ? ( $producto->get_gallery_image_ids()[0] ?? 0 ) : 0;
	}
	if ( $alt ) {
		$extra .= wp_get_attachment_image( $alt, 'large', false, array( 'class' => 'dfans-img-alt', 'loading' => 'lazy', 'alt' => '' ) );
	}
	$corte = wp_get_post_terms( $product_id, 'pa_corte', array( 'fields' => 'names' ) );
	if ( has_term( 'match-familiar', 'product_cat', $product_id ) ) {
		$extra .= '<span class="dfans-badge-match">Match</span>';
	} elseif ( $corte && ! is_wp_error( $corte ) ) {
		$extra .= '<span class="dfans-badge-match dfans-badge-corte">' . esc_html( $corte[0] ) . '</span>';
	}
	if ( ! $extra ) {
		return $html;
	}
	return preg_replace_callback( '/<img\b[^>]*>/', function ( $m ) use ( $extra ) {
		return $m[0] . $extra;
	}, $html, 1 );
}, 10, 3 );

// Bloques de las páginas de sección: 41 = Colección Match, 42 = solo de la sección.
add_filter( 'query_loop_block_query_vars', function ( $query, $block ) {
	$query_id = $block->context['queryId'] ?? null;
	$seccion  = dfans_seccion();
	if ( ! in_array( $query_id, array( 41, 42 ), true ) || ! $seccion ) {
		return $query;
	}
	$tax = array(
		'relation' => 'AND',
		array( 'taxonomy' => 'product_cat', 'field' => 'slug', 'terms' => array( DFANS_SECCIONES[ $seccion ] ) ),
		array( 'taxonomy' => 'product_cat', 'field' => 'slug', 'terms' => array( 'match-familiar' ), 'operator' => 41 === $query_id ? 'IN' : 'NOT IN' ),
	);
	if ( ! empty( $query['tax_query'] ) ) {
		$tax[] = $query['tax_query'];
	}
	$tematica = dfans_filtro( 'tematica' );
	if ( $tematica ) {
		$tax[] = array( 'taxonomy' => 'product_cat', 'field' => 'slug', 'terms' => array( 'ninos' === $seccion ? "$tematica-ninos" : $tematica ) );
	}
	$para = dfans_filtro( 'para' );
	if ( $para ) {
		$tax[] = array( 'taxonomy' => 'pa_modelo', 'field' => 'slug', 'terms' => array( $para ) );
	}
	$query['tax_query'] = $tax;
	return $query;
}, 20, 2 );

// El bloque de exclusivos no se muestra mientras no tenga productos.
// Con el filtro Para, los títulos nombran al público: "Match Hombres", "Exclusivos para ella"…
add_filter( 'render_block_core/group', function ( $html, $block ) {
	$clase = $block['attrs']['className'] ?? '';
	if ( false !== strpos( $clase, 'dfans-seccion-solo' ) && false === strpos( $html, 'wc-block-product ' ) ) {
		return '';
	}
	$para = false !== strpos( $clase, 'dfans-seccion' ) && dfans_seccion() ? dfans_filtro( 'para' ) : '';
	if ( ! $para ) {
		return $html;
	}
	$publico = array(
		'hombre' => array( 'Match Hombres', 'Exclusivos para él' ),
		'mujer'  => array( 'Match Mujeres', 'Exclusivos para ella' ),
		'nino'   => array( 'Match Niños', 'Exclusivos para niño' ),
		'nina'   => array( 'Match Niñas', 'Exclusivos para niña' ),
	);
	$texto_corte = array(
		'hombre' => 'Diseños propios D’FANS en corte oversize: hombros caídos, manga ancha y largo extra.',
		'mujer'  => 'Diseños propios D’FANS en baby tee: entallado a la cintura y largo crop.',
		'nino'   => 'Diseños propios D’FANS en polo reforzado: holgado para jugar y con doble costura.',
		'nina'   => 'Diseños propios D’FANS con manga mariposa y cintura ligeramente entallada.',
	);
	return str_replace(
		array(
			'>Colección Match ', '>Exclusivos para él y para ella ', '>Exclusivos para niño y niña ',
			'>Diseños propios D’FANS con un corte pensado para cada uno: oversize para él y baby tee para ella.<',
			'>Diseños propios D’FANS: polo reforzado para él y manga mariposa para ella.<',
		),
		array(
			'>' . $publico[ $para ][0] . ' ', '>' . $publico[ $para ][1] . ' ', '>' . $publico[ $para ][1] . ' ',
			'>' . $texto_corte[ $para ] . '<', '>' . $texto_corte[ $para ] . '<',
		),
		$html
	);
}, 10, 2 );

// Filtros de la sección: temática y para quién.
add_shortcode( 'dfans_filtros', function () {
	$seccion = dfans_seccion();
	if ( ! $seccion ) {
		return '';
	}
	$base     = get_term_link( DFANS_SECCIONES[ $seccion ], 'product_cat' );
	$tematica = dfans_filtro( 'tematica' );
	$para     = dfans_filtro( 'para' );
	$personas = 'adultos' === $seccion ? array( 'hombre' => 'Hombre', 'mujer' => 'Mujer' ) : array( 'nino' => 'Niño', 'nina' => 'Niña' );

	$chip = function ( $texto, $t, $p, $activa ) use ( $base ) {
		$url = add_query_arg( array_filter( array( 'tematica' => $t, 'para' => $p ) ), $base );
		return sprintf( '<a class="dfans-chip%s" href="%s"%s>%s</a>', $activa ? ' is-activa' : '', esc_url( $url ), $activa ? ' aria-current="true"' : '', esc_html( $texto ) );
	};

	$html  = '<nav class="dfans-filtros" aria-label="Filtrar polos">';
	$html .= '<div class="dfans-filtro-grupo"><span class="dfans-filtro-titulo">Temática</span>';
	$html .= $chip( 'Todas', '', $para, ! $tematica );
	foreach ( DFANS_TEMATICAS as $slug => $nombre ) {
		$html .= $chip( $nombre, $slug, $para, $tematica === $slug );
	}
	$html .= '</div><div class="dfans-filtro-grupo"><span class="dfans-filtro-titulo">Para</span>';
	$html .= $chip( 'Todos', $tematica, '', ! $para );
	foreach ( $personas as $slug => $nombre ) {
		$html .= $chip( $nombre, $tematica, $slug, $para === $slug );
	}
	return $html . '</div></nav>';
} );

// Ruta de navegación del producto según la sección (Inicio / Ropa de Adultos / Anime / Polo).
add_filter( 'woocommerce_breadcrumb_main_term', function ( $principal, $terminos ) {
	if ( ! is_product() ) {
		return $principal;
	}
	$seccion = dfans_seccion();
	$raiz    = get_term_by( 'slug', $seccion ? DFANS_SECCIONES[ $seccion ] : 'match-familiar', 'product_cat' );
	if ( ! $raiz ) {
		return $principal;
	}
	foreach ( $terminos as $t ) {
		if ( $seccion && $t->parent === $raiz->term_id ) {
			return $t;
		}
	}
	foreach ( $terminos as $t ) {
		if ( $t->term_id === $raiz->term_id ) {
			return $t;
		}
	}
	return $principal;
}, 10, 2 );

// Con el filtro Para, al elegir la talla la foto sigue siendo la del modelo elegido (no la del hombre o niño).
add_filter( 'woocommerce_available_variation', function ( $data, $product, $variation ) {
	$para  = dfans_seccion() ? dfans_filtro( 'para' ) : '';
	$fotos = $para ? dfans_fotos( $product->get_id() ) : null;
	if ( $fotos ) {
		$data['image']    = wc_get_product_attachment_props( $fotos[ $para ]['delantera'] );
		$data['image_id'] = $fotos[ $para ]['delantera'];
	}
	return $data;
}, 10, 3 );

// Las subcategorías de temática (Adultos › Anime, Niños › Rock, Anime › Naruto…) llevan a su sección
// con el filtro de temática ya aplicado, para que todo se vea con el mismo diseño y filtros.
add_action( 'template_redirect', function () {
	if ( ! is_product_category() ) {
		return;
	}
	$termino = get_queried_object();
	foreach ( DFANS_SECCIONES as $slug_raiz ) {
		$raiz = get_term_by( 'slug', $slug_raiz, 'product_cat' );
		if ( ! $raiz || $termino->term_id === $raiz->term_id || ! term_is_ancestor_of( $raiz, $termino, 'product_cat' ) ) {
			continue;
		}
		// Subcategoría directa de la sección (la temática), aunque se entre por una más profunda.
		$tema = $termino;
		while ( $tema->parent && $tema->parent !== $raiz->term_id ) {
			$tema = get_term( $tema->parent, 'product_cat' );
		}
		$tematica = preg_replace( '/-ninos$/', '', $tema->slug );
		if ( isset( DFANS_TEMATICAS[ $tematica ] ) ) {
			wp_safe_redirect( add_query_arg( 'tematica', $tematica, get_term_link( $raiz ) ), 302 );
			exit;
		}
	}
} );

