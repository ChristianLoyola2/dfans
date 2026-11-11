<?php
/**
 * Snippet #31: D'FANS - Rediseño visual
 * Alcance: global · prioridad 10
 * Exportado de Code Snippets el 11/11/2026. La copia activa está en la base de datos.
 */

/*
 * D'FANS - Rediseño visual (09/11/2026, segunda versión el 10/11/2026)
 *
 * - Fuente "Archivo" (texto, títulos y, en versión condensada, el índice de fandoms).
 * - [dfans_fandoms]: índice de temáticas con conteo de polos y la foto de un polo al pasar el mouse.
 * - Tarjetas: los polos con tallas dicen "Elegir talla" en lugar de "Seleccionar opciones".
 * - Ficha: sin el título repetido "Descripción", envío / pago / cambios bajo el botón de compra
 *   y productos relacionados del mismo fandom (temática).
 * - Tienda: filtros de temática y público, como en Adultos y Niños.
 * - JS: índice en las páginas legales.
 * Estilos en el CSS global ("Rediseño 2026", backups/css/rediseno-2026.css).
 */

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'dfans-titulos', 'https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@62..125,400..900&display=swap', array(), null );
} );

add_filter( 'wp_resource_hints', function ( $urls, $tipo ) {
	if ( 'preconnect' === $tipo ) {
		$urls[] = array( 'href' => 'https://fonts.gstatic.com', 'crossorigin' );
	}
	return $urls;
}, 10, 2 );

// Tarjetas: el botón dice lo que pasa al tocarlo.
add_filter( 'woocommerce_product_add_to_cart_text', function ( $texto, $producto ) {
	return $producto->is_type( 'variable' ) && $producto->is_purchasable() && $producto->is_in_stock() ? 'Elegir talla' : $texto;
}, 10, 2 );

// Ficha: la pestaña ya dice "Descripción"; no se repite como título.
add_filter( 'woocommerce_product_description_heading', '__return_empty_string' );
add_filter( 'woocommerce_product_additional_information_heading', '__return_empty_string' );

// Ficha: envío, pago y cambios justo debajo del botón de compra.
add_action( 'woocommerce_after_add_to_cart_form', function () {
	$cambios = esc_url( home_url( '/cambios-y-devoluciones/' ) );
	echo '<ul class="dfans-ficha-info">'
		. '<li class="dfans-ficha-info--envio"><span>Delivery en Lima, envío a provincia o recojo gratis en Gamarra.</span></li>'
		. '<li class="dfans-ficha-info--pago"><span>Paga con tarjeta, Yape o transferencia bancaria.</span></li>'
		. '<li class="dfans-ficha-info--cambio"><span>¿No te quedó? <a href="' . $cambios . '">Cambia tu talla</a>.</span></li>'
		. '</ul>';

	// La etiqueta cosida (v3): solo datos que la tienda ya tiene (línea, corte, fandom, tallas y origen).
	global $product;
	if ( ! $product instanceof WC_Product ) {
		return;
	}
	$id    = $product->get_id();
	$filas = array();
	$filas['Línea'] = has_term( 'match-familiar', 'product_cat', $id )
		? '<a href="' . esc_url( home_url( '/product-category/match-familiar/' ) ) . '">Match familiar</a>: el mismo diseño en adulto y niño'
		: 'Exclusivo D’FANS';
	$corte = wp_get_post_terms( $id, 'pa_corte', array( 'fields' => 'names' ) );
	if ( $corte && ! is_wp_error( $corte ) ) {
		$filas['Corte'] = esc_html( implode( ', ', $corte ) );
	}
	foreach ( wc_get_product_term_ids( $id, 'product_cat' ) as $cat ) {
		$tema = function_exists( 'dfans_tematica_de_categoria' ) ? dfans_tematica_de_categoria( $cat ) : null;
		if ( $tema ) {
			$filas['Fandom'] = esc_html( DFANS_TEMATICAS[ preg_replace( '/-ninos$/', '', $tema->slug ) ] );
			break;
		}
	}
	$tallas = wc_get_product_terms( $id, 'pa_talla', array( 'fields' => 'names' ) );
	if ( $tallas && ! is_wp_error( $tallas ) ) {
		$filas['Tallas'] = esc_html( implode( ' · ', $tallas ) );
	}
	$filas['Hecho en'] = 'Gamarra, La Victoria, Lima';
	echo '<div class="dfans-etiqueta" role="group" aria-label="Etiqueta del polo"><p class="dfans-etiqueta__marca" aria-hidden="true">D’FANS</p><dl>';
	foreach ( $filas as $dt => $dd ) {
		echo '<dt>' . esc_html( $dt ) . '</dt><dd>' . $dd . '</dd>';
	}
	echo '</dl></div>';
} );

// Checkout (v3): cómo se paga, junto al botón de confirmar.
add_action( 'woocommerce_review_order_after_submit', function () {
	echo '<p class="dfans-pago-seguro"><span class="dfans-pago-seguro__candado" aria-hidden="true"></span>Pago seguro con'
		. ' <span class="dfans-pill">Tarjeta (Mercado Pago)</span><span class="dfans-pill">Yape</span><span class="dfans-pill">Transferencia</span></p>';
} );


/** Temática (Anime, Rock…) a la que pertenece una categoría, o null. */
function dfans_tematica_de_categoria( $term_id ) {
	$t = get_term( $term_id, 'product_cat' );
	while ( $t && ! is_wp_error( $t ) ) {
		if ( defined( 'DFANS_TEMATICAS' ) && isset( DFANS_TEMATICAS[ preg_replace( '/-ninos$/', '', $t->slug ) ] ) ) {
			return $t;
		}
		$t = $t->parent ? get_term( $t->parent, 'product_cat' ) : null;
	}
	return null;
}

// Relacionados: solo del mismo fandom (un polo de Naruto sugiere anime, no cualquier polo de adulto).
add_filter( 'woocommerce_get_related_product_cat_terms', function ( $terminos ) {
	$tematicas = array();
	foreach ( $terminos as $id ) {
		$tema = dfans_tematica_de_categoria( $id );
		if ( $tema ) {
			$tematicas[ $tema->term_id ] = $tema->term_id;
		}
	}
	return $tematicas ? array_values( $tematicas ) : $terminos;
} );

// Relacionados: primero de la misma línea (un polo Match sugiere polos Match; un baby tee, exclusivos).
add_filter( 'woocommerce_related_products', function ( $ids, $product_id, $args ) {
	$es_match = has_term( 'match-familiar', 'product_cat', $product_id );
	$misma    = array_values( array_filter( $ids, function ( $id ) use ( $es_match ) {
		return has_term( 'match-familiar', 'product_cat', $id ) === $es_match;
	} ) );
	// El bloque de relacionados pide hasta 100; basta con tener 4 de la misma línea.
	return count( $misma ) >= min( 4, (int) ( $args['limit'] ?? 4 ) ) ? $misma : $ids;
}, 10, 3 );

/** Filtros de la página Tienda (temática y público). */
function dfans_filtro_tienda( $nombre ) {
	$valor = isset( $_GET[ $nombre ] ) ? sanitize_key( wp_unslash( $_GET[ $nombre ] ) ) : '';
	if ( 'tematica' === $nombre ) {
		return defined( 'DFANS_TEMATICAS' ) && isset( DFANS_TEMATICAS[ $valor ] ) ? $valor : '';
	}
	return in_array( $valor, array( 'hombre', 'mujer', 'nino', 'nina' ), true ) ? $valor : '';
}

add_action( 'pre_get_posts', function ( $q ) {
	if ( is_admin() || ! $q->is_main_query() || ! $q->is_post_type_archive( 'product' ) || $q->is_search() ) {
		return;
	}
	$tax      = (array) $q->get( 'tax_query' );
	$tematica = dfans_filtro_tienda( 'tematica' );
	$para     = dfans_filtro_tienda( 'para' );
	if ( $tematica ) {
		$tax[] = array( 'taxonomy' => 'product_cat', 'field' => 'slug', 'terms' => array( $tematica, "$tematica-ninos" ) );
	}
	if ( $para ) {
		$tax[] = array( 'taxonomy' => 'pa_modelo', 'field' => 'slug', 'terms' => array( $para ) );
	}
	if ( $tematica || $para ) {
		$q->set( 'tax_query', $tax );
	}
} );

add_filter( 'render_block_core/query-title', function ( $html ) {
	if ( ! function_exists( 'is_shop' ) || ! is_shop() || ! defined( 'DFANS_TEMATICAS' ) ) {
		return $html;
	}
	$base     = wc_get_page_permalink( 'shop' );
	$tematica = dfans_filtro_tienda( 'tematica' );
	$para     = dfans_filtro_tienda( 'para' );
	$chip     = function ( $texto, $t, $p, $activa ) use ( $base ) {
		$url = add_query_arg( array_filter( array( 'tematica' => $t, 'para' => $p ) ), $base );
		return sprintf( '<a class="dfans-chip%s" href="%s"%s>%s</a>', $activa ? ' is-activa' : '', esc_url( $url ), $activa ? ' aria-current="true"' : '', esc_html( $texto ) );
	};
	$nav  = '<nav class="dfans-filtros alignwide" aria-label="Filtrar polos"><div class="dfans-filtro-grupo"><span class="dfans-filtro-titulo">Temática</span>';
	$nav .= $chip( 'Todas', '', $para, ! $tematica );
	foreach ( DFANS_TEMATICAS as $slug => $nombre ) {
		$nav .= $chip( $nombre, $slug, $para, $tematica === $slug );
	}
	$nav .= '</div><div class="dfans-filtro-grupo"><span class="dfans-filtro-titulo">Para</span>';
	$nav .= $chip( 'Todos', $tematica, '', ! $para );
	foreach ( array( 'hombre' => 'Hombre', 'mujer' => 'Mujer', 'nino' => 'Niño', 'nina' => 'Niña' ) as $slug => $nombre ) {
		$nav .= $chip( $nombre, $tematica, $slug, $para === $slug );
	}
	return $html . $nav . '</div></nav>';
} );

// Índice de fandoms de la portada: nombre grande, ejemplos, cuántos polos hay (adultos y niños) y la foto de uno.
add_shortcode( 'dfans_fandoms', function () {
	if ( ! defined( 'DFANS_TEMATICAS' ) ) {
		return '';
	}
	$temas = array(
		'anime'              => array( 'anime', 'Demon Slayer, Naruto, One Piece y Dragon Ball', 'polo-naruto' ),
		'rock'               => array( 'rock', 'AC/DC, Queen, Nirvana y The Beatles', 'polo-queen' ),
		'metal'              => array( 'metal', 'Metallica, Iron Maiden y Slipknot', 'polo-iron-maiden' ),
		'artistas'           => array( 'artistas', 'Bad Bunny, Eminem y Travis Scott', 'polo-eminem' ),
		'peliculas-y-series' => array( 'peliculas', 'Stranger Things, Breaking Bad y El Señor de los Anillos', 'polo-el-senor-de-los-anillos' ),
		'videojuegos'        => array( 'videojuegos', 'Minecraft, Fortnite, Free Fire y Dota 2', 'polo-fortnite' ),
	);
	$cuentas = get_transient( 'dfans_fandoms_cuentas' );
	if ( ! is_array( $cuentas ) ) {
		$cuentas = array();
		foreach ( array_keys( $temas ) as $slug ) {
			$q = new WP_Query( array( 'post_type' => 'product', 'post_status' => 'publish', 'fields' => 'ids', 'posts_per_page' => -1, 'no_found_rows' => true, 'tax_query' => array( array( 'taxonomy' => 'product_cat', 'field' => 'slug', 'terms' => array( $slug, "$slug-ninos" ) ) ) ) );
			$cuentas[ $slug ] = count( $q->posts );
		}
		set_transient( 'dfans_fandoms_cuentas', $cuentas, 12 * HOUR_IN_SECONDS );
	}
	$base = wc_get_page_permalink( 'shop' );
	$html = '<ul class="dfans-fandoms alignwide">';
	foreach ( $temas as $slug => list( $clase, $ejemplos, $producto ) ) {
		$p    = get_page_by_path( $producto, OBJECT, 'product' );
		$foto = $p ? get_the_post_thumbnail( $p->ID, 'woocommerce_single', array( 'class' => 'dfans-fandoms__foto', 'alt' => '', 'loading' => 'lazy', 'sizes' => '(max-width: 781px) 72px, 420px' ) ) : '';
		$html .= sprintf(
			'<li class="dfans-fandoms__item dfans-tema--%1$s"><a href="%2$s"><span class="dfans-fandoms__nombre">%3$s</span><span class="dfans-fandoms__ejemplos">%4$s</span><span class="dfans-fandoms__cuenta">%5$d polos</span></a>%6$s</li>',
			esc_attr( $clase ),
			esc_url( add_query_arg( 'tematica', $slug, $base ) ),
			esc_html( DFANS_TEMATICAS[ $slug ] ),
			esc_html( $ejemplos ),
			(int) ( $cuentas[ $slug ] ?? 0 ),
			$foto
		);
	}
	return $html . '</ul>';
} );
add_action( 'save_post_product', function () {
	delete_transient( 'dfans_fandoms_cuentas' );
} );

// Menú por persona: marca Hombre, Mujer, Niño o Niña cuando el cliente está en esa sección
// (o en un producto abierto desde ella). Match familiar lo marca WordPress.
add_filter( 'render_block_core/navigation-link', function ( $html, $block ) {
	if ( ! function_exists( 'dfans_filtro' ) || ! function_exists( 'dfans_seccion' ) ) {
		return $html;
	}
	parse_str( (string) wp_parse_url( $block['attrs']['url'] ?? '', PHP_URL_QUERY ), $args );
	if ( empty( $args['para'] ) || ! dfans_seccion() || dfans_filtro( 'para' ) !== $args['para'] ) {
		return $html;
	}
	$html = preg_replace( '/<li class="/', '<li class="current-menu-item ', $html, 1 );
	return preg_replace( '/<a class="wp-block-navigation-item__content"/', '<a aria-current="page" class="wp-block-navigation-item__content"', $html, 1 );
}, 10, 2 );

// Con el filtro de persona, el título de la sección dice para quién es (como el menú).
add_filter( 'render_block_core/query-title', function ( $html ) {
	if ( ! function_exists( 'dfans_filtro' ) || ! function_exists( 'dfans_seccion' ) || ! dfans_seccion() ) {
		return $html;
	}
	$nombres = array( 'hombre' => 'Hombre', 'mujer' => 'Mujer', 'nino' => 'Niño', 'nina' => 'Niña' );
	$para    = dfans_filtro( 'para' );
	return isset( $nombres[ $para ] ) ? preg_replace( '#(<h1[^>]*>).*?(</h1>)#s', '${1}' . $nombres[ $para ] . '${2}', $html, 1 ) : $html;
}, 20 );

// Mi cuenta: nombres claros en el menú.
add_filter( 'woocommerce_account_menu_items', function ( $items ) {
	$nombres = array(
		'dashboard'       => 'Resumen',
		'orders'          => 'Pedidos',
		'edit-address'    => 'Direcciones',
		'edit-account'    => 'Datos de la cuenta',
		'customer-logout' => 'Cerrar sesión',
	);
	foreach ( $nombres as $clave => $texto ) {
		if ( isset( $items[ $clave ] ) ) {
			$items[ $clave ] = $texto;
		}
	}
	unset( $items['downloads'] );
	return $items;
}, 20 );

// Mi cuenta, resumen: saludo, último pedido, favoritos, dirección, pedidos recientes y ayuda.
add_action( 'woocommerce_account_dashboard', function () {
	$usuario = wp_get_current_user();
	$nombre  = $usuario->first_name ? $usuario->first_name : $usuario->display_name;
	$pedidos = wc_get_orders( array( 'customer_id' => $usuario->ID, 'limit' => 4, 'orderby' => 'date', 'order' => 'DESC', 'status' => array_keys( wc_get_order_statuses() ) ) );
	$ultimo  = $pedidos ? $pedidos[0] : null;
	$estado  = function ( $p ) {
		return '<span class="dfans-estado dfans-estado--' . esc_attr( $p->get_status() ) . '">' . esc_html( wc_get_order_status_name( $p->get_status() ) ) . '</span>';
	};
	$direccion = wc_get_account_formatted_address( 'shipping' ) ?: wc_get_account_formatted_address( 'billing' );

	echo '<div class="dfans-panel">';
	echo '<div class="dfans-panel__saludo"><h2>Hola, ' . esc_html( $nombre ) . '</h2>'
		. '<p>Desde aquí sigues tus pedidos, guardas tus direcciones y cambias tus datos.</p></div>';

	echo '<div class="dfans-panel__tarjetas">';
	// Último pedido.
	echo '<section class="dfans-tarjeta"><h3>Tu último pedido</h3>';
	if ( $ultimo ) {
		echo '<p class="dfans-tarjeta__dato">Pedido #' . esc_html( $ultimo->get_order_number() ) . '</p>'
			. '<p>' . esc_html( wc_format_datetime( $ultimo->get_date_created() ) ) . ', ' . wp_kses_post( $ultimo->get_formatted_order_total() ) . '</p>'
			. '<p>' . $estado( $ultimo ) . '</p>'
			. '<a class="dfans-tarjeta__enlace" href="' . esc_url( $ultimo->get_view_order_url() ) . '">Ver el pedido</a>';
	} else {
		echo '<p class="dfans-tarjeta__dato">Aún no tienes pedidos</p><p>Cuando compres, aquí verás en qué estado está tu pedido.</p>'
			. '<a class="dfans-tarjeta__enlace" href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '">Ver la tienda</a>';
	}
	echo '</section>';
	// Favoritos (los cuenta el navegador).
	echo '<section class="dfans-tarjeta"><h3>Tus favoritos</h3>'
		. '<p class="dfans-tarjeta__dato"><span class="dfans-fav-total">0</span> polos guardados</p>'
		. '<p>Se guardan en este navegador cuando tocas el corazón de un polo.</p>'
		. '<a class="dfans-tarjeta__enlace" href="' . esc_url( home_url( '/favoritos/' ) ) . '">Ver mis favoritos</a></section>';
	// Dirección.
	echo '<section class="dfans-tarjeta"><h3>Dirección de envío</h3>';
	if ( $direccion ) {
		echo '<address>' . wp_kses_post( $direccion ) . '</address><a class="dfans-tarjeta__enlace" href="' . esc_url( wc_get_account_endpoint_url( 'edit-address' ) ) . '">Cambiar dirección</a>';
	} else {
		echo '<p class="dfans-tarjeta__dato">Sin dirección guardada</p><p>Guárdala una vez y el checkout se llena solo.</p>'
			. '<a class="dfans-tarjeta__enlace" href="' . esc_url( wc_get_account_endpoint_url( 'edit-address' ) ) . '">Agregar dirección</a>';
	}
	echo '</section></div>';

	// Pedidos recientes.
	if ( count( $pedidos ) > 1 ) {
		echo '<section class="dfans-panel__pedidos"><h3>Pedidos recientes</h3><ul>';
		foreach ( $pedidos as $p ) {
			echo '<li><a href="' . esc_url( $p->get_view_order_url() ) . '"><span>#' . esc_html( $p->get_order_number() ) . '</span>'
				. '<span>' . esc_html( wc_format_datetime( $p->get_date_created() ) ) . '</span>'
				. '<span>' . wp_kses_post( $p->get_formatted_order_total() ) . '</span>' . $estado( $p ) . '</a></li>';
		}
		echo '</ul><a class="dfans-tarjeta__enlace" href="' . esc_url( wc_get_account_endpoint_url( 'orders' ) ) . '">Ver todos los pedidos</a></section>';
	}

	// Ayuda.
	echo '<p class="dfans-panel__ayuda">¿Dudas con un pedido o un cambio de talla? <a href="https://wa.me/51912353175" target="_blank" rel="noopener">Escríbenos por WhatsApp</a> o revisa la <a href="' . esc_url( home_url( '/cambios-y-devoluciones/' ) ) . '">política de cambios</a>.</p>';
	echo '</div>';
	?>
	<script>
	(function () {
		var total = 0;
		try { total = (JSON.parse(localStorage.getItem('dfans_favoritos')) || []).length; } catch (e) {}
		document.querySelectorAll('.dfans-fav-total').forEach(function (n) { n.textContent = total; });
	})();
	</script>
	<?php
} );

// JS: índice de las páginas legales.
add_action( 'wp_footer', function () {
	if ( is_admin() ) {
		return;
	}
	?>
	<script>
	(function () {
		'use strict';
		// Páginas legales: índice con enlaces a cada sección.
		var legales = ['/terminos-y-condiciones/', '/politica-de-privacidad/', '/cambios-y-devoluciones/'];
		var contenido = document.querySelector('.wp-block-post-content');
		if (contenido && legales.indexOf(location.pathname) !== -1) {
			var titulos = contenido.querySelectorAll(':scope > h2');
			if (titulos.length > 3) {
				var nav = document.createElement('nav');
				nav.className = 'dfans-indice';
				nav.setAttribute('aria-label', 'Secciones de esta página');
				var lista = document.createElement('ol');
				titulos.forEach(function (h, i) {
					if (!h.id) { h.id = 'seccion-' + (i + 1); }
					var li = document.createElement('li');
					var a = document.createElement('a');
					a.href = '#' + h.id;
					a.textContent = h.textContent.replace(/^\s*\d+\.\s*/, '');
					li.appendChild(a);
					lista.appendChild(li);
				});
				var titulo = document.createElement('p');
				titulo.className = 'dfans-indice__titulo';
				titulo.textContent = 'En esta página';
				nav.appendChild(titulo);
				nav.appendChild(lista);
				var primero = contenido.querySelector(':scope > p');
				if (primero && primero.compareDocumentPosition(titulos[0]) & Node.DOCUMENT_POSITION_FOLLOWING) {
					primero.insertAdjacentElement('afterend', nav);
				} else {
					titulos[0].insertAdjacentElement('beforebegin', nav);
				}
			}
		}
	})();
	</script>
	<?php
}, 40 );

