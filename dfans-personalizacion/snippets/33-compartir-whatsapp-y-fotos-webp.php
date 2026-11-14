<?php
/**
 * Snippet #33: D'FANS - Compartir, WhatsApp y fotos WebP
 * Alcance: global · prioridad 10
 * Exportado de Code Snippets el 14/11/2026. La copia activa está en la base de datos.
 */

/*
 * D'FANS - Compartir, WhatsApp, carrusel y fotos WebP (13/11/2026)
 *
 * - Vista previa al compartir (Open Graph y Twitter) y meta descripción en portada, tienda,
 *   categorías y fichas. No se activa si hay un plugin de SEO que ya lo haga.
 * - Botón flotante de WhatsApp (el mismo número que «Mi cuenta»), con el nombre del polo en la ficha.
 * - Flechas del carrusel «Lo nuevo» de la portada.
 * - Fotos nuevas en WebP y herramienta Herramientas → Fotos WebP para convertir las que ya están
 *   (procesa por lotes; el original queda igual).
 */

if ( ! defined( 'DFANS_WHATSAPP' ) ) {
	define( 'DFANS_WHATSAPP', '51912353175' );
}

/** Texto corto y limpio para la descripción. */
function dfans_meta_texto( $texto, $largo = 155 ) {
	$texto = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( strip_shortcodes( (string) $texto ) ) ) );
	return mb_strlen( $texto ) > $largo ? rtrim( mb_substr( $texto, 0, $largo - 1 ) ) . '…' : $texto;
}

add_action( 'wp_head', function () {
	if ( defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'SEOPRESS_VERSION' ) || is_admin() ) {
		return;
	}
	$sitio  = get_bloginfo( 'name' );
	$desc   = 'Polos de anime, rock, metal, artistas, películas y videojuegos hechos en Gamarra. Colección Match para toda la familia y exclusivos D’FANS. Envíos a todo el Perú.';
	$titulo = wp_get_document_title();
	$imagen = wp_get_attachment_image_url( 100, 'large' );
	$url    = home_url( add_query_arg( array() ) );
	$tipo   = 'website';
	$extra  = '';
	if ( function_exists( 'is_product' ) && is_product() ) {
		$p = wc_get_product( get_queried_object_id() );
		if ( $p ) {
			$titulo = $p->get_name() . ' | ' . $sitio;
			$desc   = dfans_meta_texto( $p->get_short_description() ? $p->get_short_description() : $p->get_description() ) ?: $desc;
			$imagen = wp_get_attachment_image_url( $p->get_image_id(), 'large' ) ?: $imagen;
			$url    = get_permalink( $p->get_id() );
			$tipo   = 'product';
			$precio = $p->is_type( 'variable' ) ? $p->get_variation_price( 'min', true ) : $p->get_price();
			if ( $precio ) {
				$extra .= '<meta property="product:price:amount" content="' . esc_attr( wc_format_decimal( $precio, 2 ) ) . '">' . "\n"
					. '<meta property="product:price:currency" content="PEN">' . "\n";
			}
		}
	} elseif ( is_tax( 'product_cat' ) ) {
		$t      = get_queried_object();
		$titulo = $t->name . ' | ' . $sitio;
		$desc   = $t->description ? dfans_meta_texto( $t->description ) : 'Polos de ' . $t->name . ' de D’FANS, hechos en Gamarra. Envíos a todo el Perú.';
		$url    = get_term_link( $t );
	} elseif ( function_exists( 'is_shop' ) && is_shop() ) {
		$titulo = 'Tienda | ' . $sitio;
		$desc   = 'Todos los polos D’FANS: colección Match para toda la familia y exclusivos en oversize, baby tee, regular y manga mariposa. Envíos a todo el Perú.';
		$url    = get_permalink( wc_get_page_id( 'shop' ) );
	} elseif ( is_singular() && ! is_front_page() ) {
		$excerpt = get_the_excerpt();
		$desc    = $excerpt ? dfans_meta_texto( $excerpt ) : $desc;
		$url     = get_permalink();
	}
	echo "\n<!-- D'FANS: vista previa al compartir -->\n"
		. '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n"
		. '<meta property="og:locale" content="es_PE">' . "\n"
		. '<meta property="og:site_name" content="' . esc_attr( $sitio ) . '">' . "\n"
		. '<meta property="og:type" content="' . esc_attr( $tipo ) . '">' . "\n"
		. '<meta property="og:title" content="' . esc_attr( $titulo ) . '">' . "\n"
		. '<meta property="og:description" content="' . esc_attr( $desc ) . '">' . "\n"
		. '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n"
		. ( $imagen ? '<meta property="og:image" content="' . esc_url( $imagen ) . '">' . "\n" : '' )
		. $extra
		. '<meta name="twitter:card" content="summary_large_image">' . "\n";
}, 5 );

// Botón flotante de WhatsApp (no en el checkout ni en la vista rápida).
add_action( 'wp_footer', function () {
	if ( is_admin() || ( function_exists( 'is_checkout' ) && is_checkout() ) || ( function_exists( 'dfans_es_vista_rapida' ) && dfans_es_vista_rapida() ) ) {
		return;
	}
	$mensaje = 'Hola D’FANS, tengo una consulta';
	if ( function_exists( 'is_product' ) && is_product() ) {
		$mensaje = 'Hola D’FANS, tengo una consulta sobre el ' . get_the_title( get_queried_object_id() );
	}
	$url = 'https://wa.me/' . DFANS_WHATSAPP . '?text=' . rawurlencode( $mensaje );
	echo '<a class="dfans-wsp" href="' . esc_url( $url ) . '" target="_blank" rel="noopener" aria-label="Escríbenos por WhatsApp">'
		. '<svg viewBox="0 0 32 32" aria-hidden="true"><path d="M16 3a13 13 0 0 0-11.2 19.6L3 29l6.6-1.7A13 13 0 1 0 16 3zm0 23.7a10.7 10.7 0 0 1-5.5-1.5l-.4-.2-3.9 1 1-3.8-.3-.4A10.7 10.7 0 1 1 16 26.7zm5.9-8c-.3-.2-1.9-.9-2.2-1-.3-.1-.5-.2-.7.2l-1 1.2c-.2.2-.4.2-.7.1a8.7 8.7 0 0 1-4.3-3.8c-.3-.6.3-.5.9-1.7.1-.2 0-.4 0-.6l-1-2.4c-.3-.6-.5-.5-.7-.5h-.6a1.2 1.2 0 0 0-.9.4 3.6 3.6 0 0 0-1.1 2.7 6.3 6.3 0 0 0 1.3 3.3 14.4 14.4 0 0 0 5.5 4.9c2 .9 2.8.9 3.8.8a3.3 3.3 0 0 0 2.2-1.5 2.7 2.7 0 0 0 .2-1.5c-.1-.2-.3-.3-.6-.4z"/></svg>'
		. '<span>¿Dudas? Escríbenos</span></a>';
	if ( is_front_page() ) {
		// Flechas del carrusel «Lo nuevo».
		?>
		<script>
		// Miniaturas del hero: cambian la foto grande (y su enlace) por la persona elegida.
		(function () {
			var foto = document.querySelector('.dfans-cartelera__foto');
			var img = foto && foto.querySelector('img');
			var botones = document.querySelectorAll('.dfans-cartelera__elige');
			if (!img || !botones.length) { return; }
			var quieto = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
			botones.forEach(function (b) {
				// Precarga al pasar el mouse o tocar, para que el cambio sea inmediato.
				var precargar = function () { if (!b._pre) { b._pre = new Image(); b._pre.src = b.dataset.foto; } };
				b.addEventListener('mouseenter', precargar);
				b.addEventListener('touchstart', precargar, { passive: true });
				b.addEventListener('click', function () {
					if (b.getAttribute('aria-pressed') === 'true') { return; }
					botones.forEach(function (x) { x.setAttribute('aria-pressed', x === b ? 'true' : 'false'); });
					precargar();
					var poner = function () {
						img.getAnimations().forEach(function (a) { a.cancel(); });
						img.src = b.dataset.foto;
						foto.href = b.dataset.href;
						if (!quieto) {
							img.animate([{ opacity: 0, transform: 'scale(1.03)' }, { opacity: 1, transform: 'none' }], { duration: 650, easing: 'cubic-bezier(0.2, 0.7, 0.2, 1)' });
						}
					};
					if (b._pre.complete) { poner(); } else { b._pre.onload = poner; b._pre.onerror = poner; }
				});
			});
		})();
		(function () {
			var fila = document.querySelector('.dfans-p-nuevos .wc-block-product-template');
			if (!fila) { return; }
			document.querySelectorAll('.dfans-flecha').forEach(function (b) {
				b.addEventListener('click', function () {
					fila.scrollBy({ left: fila.clientWidth * 0.8 * (b.dataset.dir === 'izq' ? -1 : 1), behavior: 'smooth' });
				});
			});
		})();
		</script>
		<?php
	}

}, 40 );

// Galería de la ficha (también dentro de la vista rápida): flechas y miniaturas enteras.
add_action( 'wp_footer', function () {
	if ( is_admin() ) {
		return;
	}
	if ( function_exists( 'is_product' ) && is_product() ) {
		// Ficha: flechas a los lados de las miniaturas para pasar de foto; la tira se corre sola.
		?>
		<script>
		(function () {
			var intentos = 0;
			function armar() {
				var galeria = document.querySelector('.woocommerce-product-gallery');
				var tira = galeria && galeria.querySelector('.flex-control-thumbs');
				var jq = window.jQuery;
				var slider = galeria && jq && jq(galeria).data('flexslider');
				if (!tira || !slider) {
					if (intentos++ < 40) { setTimeout(armar, 150); }
					return;
				}
				if (tira.parentNode.classList.contains('dfans-miniaturas') || slider.count < 2) { return; }
				var caja = document.createElement('div');
				caja.className = 'dfans-miniaturas';
				tira.parentNode.insertBefore(caja, tira);
				function flecha(dir, texto, etiqueta) {
					var b = document.createElement('button');
					b.type = 'button';
					b.className = 'dfans-mini-flecha';
					b.setAttribute('aria-label', etiqueta);
					b.textContent = texto;
					b.addEventListener('click', function () {
						var s = jq(galeria).data('flexslider');
						s.flexAnimate((s.currentSlide + dir + s.count) % s.count, true);
					});
					return b;
				}
				caja.appendChild(flecha(-1, '‹', 'Foto anterior'));
				caja.appendChild(tira);
				caja.appendChild(flecha(1, '›', 'Foto siguiente'));
				// Mantiene a la vista la miniatura de la foto que se está mostrando.
				function centrar() {
					var activa = tira.querySelector('img.flex-active');
					var li = activa && activa.parentNode;
					if (!li) { return; }
					var izq = li.offsetLeft - tira.offsetLeft;
					if (izq < tira.scrollLeft || izq + li.offsetWidth > tira.scrollLeft + tira.clientWidth) {
						tira.scrollTo({ left: izq - (tira.clientWidth - li.offsetWidth) / 2, behavior: 'smooth' });
					}
				}
				new MutationObserver(centrar).observe(tira, { subtree: true, attributes: true, attributeFilter: ['class'] });
			}
			if (document.readyState === 'complete') { armar(); } else { window.addEventListener('load', armar); }
		})();
		</script>
		<?php
	}}, 41 );

// Fotos nuevas en WebP (más livianas); el archivo original subido no cambia.
add_filter( 'image_editor_output_format', function ( $formatos, $archivo = '' ) {
	// El logo queda en PNG: los correos (Outlook) no muestran WebP.
	if ( $archivo && preg_match( '/^dfans-(logo|icono)/', wp_basename( (string) $archivo ) ) ) {
		return $formatos;
	}
	$formatos['image/jpeg'] = 'image/webp';
	$formatos['image/png']  = 'image/webp';
	return $formatos;
}, 10, 2 );

/** Convierte a WebP los tamaños de una foto ya subida. Devuelve true si la procesó. */
function dfans_webp_convertir( $id ) {
	$archivo = get_attached_file( $id );
	if ( ! $archivo || ! file_exists( $archivo ) || ! wp_attachment_is_image( $id ) ) {
		update_post_meta( $id, '_dfans_webp', 'omitida' );
		return false;
	}
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$meta = wp_create_image_subsizes( $archivo, $id );
	update_post_meta( $id, '_dfans_webp', is_array( $meta ) ? 'si' : 'error' );
	return is_array( $meta );
}

add_action( 'admin_menu', function () {
	add_management_page( 'Fotos WebP', 'Fotos WebP', 'manage_options', 'dfans-webp', function () {
		global $wpdb;
		$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type='attachment' AND post_mime_type IN ('image/jpeg','image/png')" );
		$hechas = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key='_dfans_webp'" );
		echo '<div class="wrap"><h1>Fotos WebP</h1><p>Crea versiones WebP (más livianas) de los tamaños de cada foto. El archivo original no cambia. Se procesa de a 4 fotos para no cargar el servidor.</p>'
			. '<p><strong id="dfans-webp-estado">' . $hechas . ' de ' . $total . ' fotos listas.</strong></p>'
			. '<p><button class="button button-primary" id="dfans-webp-iniciar">Convertir las que faltan</button></p></div>';
		$nonce = wp_create_nonce( 'dfans_webp' );
		?>
		<script>
		(function () {
			var b = document.getElementById('dfans-webp-iniciar'), e = document.getElementById('dfans-webp-estado'), parar = false;
			function lote() {
				if (parar) { return; }
				var d = new FormData(); d.append('action', 'dfans_webp_lote'); d.append('_ajax_nonce', '<?php echo esc_js( $nonce ); ?>');
				fetch(ajaxurl, { method: 'POST', body: d, credentials: 'same-origin' }).then(function (r) { return r.json(); }).then(function (j) {
					if (!j.success) { e.textContent = 'Error: ' + (j.data || 'sin respuesta'); b.disabled = false; return; }
					e.textContent = j.data.hechas + ' de ' + j.data.total + ' fotos listas.';
					if (j.data.quedan > 0) { setTimeout(lote, 400); } else { e.textContent += ' Terminado.'; b.disabled = false; }
				}).catch(function () { e.textContent += ' (se cortó; pulsa otra vez para seguir)'; b.disabled = false; });
			}
			b.addEventListener('click', function () { b.disabled = true; lote(); });
		})();
		</script>
		<?php
	} );
} );

add_action( 'wp_ajax_dfans_webp_lote', function () {
	check_ajax_referer( 'dfans_webp' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( 'sin permiso' );
	}
	global $wpdb;
	$ids = $wpdb->get_col( "SELECT p.ID FROM {$wpdb->posts} p LEFT JOIN {$wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key = '_dfans_webp' WHERE p.post_type = 'attachment' AND p.post_mime_type IN ('image/jpeg','image/png') AND m.meta_id IS NULL ORDER BY p.ID LIMIT 4" );
	foreach ( $ids as $id ) {
		dfans_webp_convertir( (int) $id );
	}
	$total  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type='attachment' AND post_mime_type IN ('image/jpeg','image/png')" );
	$hechas = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key='_dfans_webp'" );
	wp_send_json_success( array( 'hechas' => $hechas, 'total' => $total, 'quedan' => max( 0, $total - $hechas ) ) );
} );

