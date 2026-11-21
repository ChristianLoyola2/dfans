<?php
/**
 * Snippet #34: D'FANS - Ventas y confianza
 * Alcance: global · prioridad 10
 * Exportado de Code Snippets el 21/11/2026. La copia activa está en la base de datos.
 */

/*
 * D'FANS - Ventas y confianza (21/11/2026)
 *
 * - Delivery gratis en Lima desde S/ 120 (método «free_shipping» de la zona Lima) y barra de progreso
 *   en el carrito, el mini carrito y el checkout. Si el delivery es gratis, se oculta «a coordinar».
 * - Pack Match: mismo diseño en talla de adulto + talla de niño = 10% menos en ese par (descuento automático).
 * - Cupón de bienvenida BIENVENIDO10 (10%, mínimo S/ 60, solo primera compra) a cambio del correo,
 *   con una ventanita que aparece una sola vez. Los correos quedan en Herramientas → Suscriptores D'FANS.
 * - «Últimas unidades» cuando quedan 5 o menos.
 * - Reseñas: estrellas en las tarjetas y la ficha, fotos en las reseñas (solo compradores, quedan para
 *   aprobar), sección «Lo que dicen los fans» en la portada ([dfans_resenas], no se muestra si no hay
 *   reseñas) y pedido de reseña en el correo de pedido completado.
 * - Facebook en el pie de página.
 */

if ( ! defined( 'DFANS_ENVIO_GRATIS' ) ) {
	define( 'DFANS_ENVIO_GRATIS', 120 );
}
if ( ! defined( 'DFANS_PACK_DESCUENTO' ) ) {
	define( 'DFANS_PACK_DESCUENTO', 0.10 );
}
if ( ! defined( 'DFANS_CUPON_BIENVENIDA' ) ) {
	define( 'DFANS_CUPON_BIENVENIDA', 'bienvenido10' );
}
if ( ! defined( 'DFANS_ULTIMAS' ) ) {
	define( 'DFANS_ULTIMAS', 5 );
}
if ( ! defined( 'DFANS_FACEBOOK' ) ) {
	define( 'DFANS_FACEBOOK', 'https://www.facebook.com/estampandoestilo/' );
}

/* ------------------------------------------------------------------ */
/* 1. Delivery gratis en Lima                                          */
/* ------------------------------------------------------------------ */

// Si el delivery gratis está disponible, se quita «Delivery en Lima — costo a coordinar».
add_filter( 'woocommerce_package_rates', function ( $rates ) {
	$gratis = false;
	foreach ( $rates as $rate ) {
		if ( 'free_shipping' === $rate->get_method_id() ) {
			$gratis = true;
		}
	}
	if ( $gratis ) {
		foreach ( $rates as $id => $rate ) {
			if ( 'flat_rate' === $rate->get_method_id() && false !== stripos( $rate->get_label(), 'delivery en lima' ) ) {
				unset( $rates[ $id ] );
			}
		}
	}
	return $rates;
}, 20 );

/** HTML de la barra (el JS la llena con los montos reales del carrito). */
function dfans_envio_barra_html() {
	return '<div class="dfans-envio" data-meta="' . esc_attr( DFANS_ENVIO_GRATIS ) . '" hidden>'
		. '<p class="dfans-envio__texto"></p>'
		. '<div class="dfans-envio__riel" aria-hidden="true"><span class="dfans-envio__barra"></span></div></div>';
}

// Carrito (bloque) y checkout (clásico).
add_filter( 'render_block_woocommerce/cart', function ( $html ) {
	return dfans_envio_barra_html() . $html;
} );
add_action( 'woocommerce_before_checkout_form', function () {
	echo dfans_envio_barra_html(); // phpcs:ignore WordPress.Security.EscapeOutput
}, 5 );

/* ------------------------------------------------------------------ */
/* 2. Pack Match                                                       */
/* ------------------------------------------------------------------ */

/** Devuelve array( cantidad de packs, descuento en soles ) para el carrito. */
function dfans_pack_match( $cart ) {
	$ninos  = array( '10', '12', '14' );
	$grupos = array();
	foreach ( $cart->get_cart() as $item ) {
		$producto = $item['data'] ?? null;
		$padre    = (int) ( $item['product_id'] ?? 0 );
		if ( ! $producto || ! $padre || ! has_term( 'match-familiar', 'product_cat', $padre ) ) {
			continue;
		}
		$talla = strtolower( (string) ( $item['variation']['attribute_pa_talla'] ?? '' ) );
		if ( '' === $talla ) {
			continue;
		}
		$tipo = in_array( $talla, $ninos, true ) ? 'nino' : 'adulto';
		for ( $i = 0; $i < (int) $item['quantity']; $i++ ) {
			// Precio sin IGV: WooCommerce le suma el impuesto al descuento, así el total baja justo el 10%.
			$grupos[ $padre ][ $tipo ][] = (float) wc_get_price_excluding_tax( $producto );
		}
	}
	$packs     = 0;
	$descuento = 0.0;
	foreach ( $grupos as $g ) {
		if ( empty( $g['adulto'] ) || empty( $g['nino'] ) ) {
			continue;
		}
		rsort( $g['adulto'] );
		rsort( $g['nino'] );
		$n = min( count( $g['adulto'] ), count( $g['nino'] ) );
		for ( $i = 0; $i < $n; $i++ ) {
			$descuento += ( $g['adulto'][ $i ] + $g['nino'][ $i ] ) * DFANS_PACK_DESCUENTO;
		}
		$packs += $n;
	}
	return array( $packs, round( $descuento, 4 ) );
}

add_action( 'woocommerce_cart_calculate_fees', function ( $cart ) {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return;
	}
	list( $packs, $descuento ) = dfans_pack_match( $cart );
	if ( $descuento > 0 ) {
		$nombre = 1 === $packs ? 'Pack Match (adulto + niño) −10%' : sprintf( 'Pack Match ×%d (adulto + niño) −10%%', $packs );
		$cart->add_fee( $nombre, -$descuento, true );
	}
} );

// Aviso en la ficha de los polos Match.
add_action( 'woocommerce_after_add_to_cart_form', function () {
	global $product;
	if ( $product && has_term( 'match-familiar', 'product_cat', $product->get_id() ) ) {
		echo '<p class="dfans-pack-aviso"><strong>Pack Match −10%</strong> Lleva este diseño en talla de adulto y en talla de niño, y el descuento se aplica solo en el carrito.</p>';
	}
}, 4 );

/* ------------------------------------------------------------------ */
/* 3. Cupón de bienvenida                                              */
/* ------------------------------------------------------------------ */

/** ¿El cliente (por usuario o correo) ya tiene una compra? */
function dfans_ya_compro( $correo = '' ) {
	$base = array( 'limit' => 1, 'return' => 'ids', 'status' => array( 'wc-processing', 'wc-completed', 'wc-on-hold' ) );
	$id   = get_current_user_id();
	if ( $id && wc_get_orders( $base + array( 'customer_id' => $id ) ) ) {
		return true;
	}
	if ( ! $correo && function_exists( 'WC' ) && WC()->customer ) {
		$correo = WC()->customer->get_billing_email();
	}
	return $correo && is_email( $correo ) && wc_get_orders( $base + array( 'billing_email' => $correo ) );
}

add_filter( 'woocommerce_coupon_is_valid', function ( $valido, $cupon ) {
	if ( $valido && strtolower( $cupon->get_code() ) === DFANS_CUPON_BIENVENIDA && dfans_ya_compro() ) {
		throw new Exception( 'El cupón de bienvenida es solo para tu primera compra.' );
	}
	return $valido;
}, 10, 2 );

add_action( 'woocommerce_after_checkout_validation', function ( $datos, $errores ) {
	$codigos = array_map( 'strtolower', WC()->cart ? WC()->cart->get_applied_coupons() : array() );
	if ( in_array( DFANS_CUPON_BIENVENIDA, $codigos, true ) && dfans_ya_compro( $datos['billing_email'] ?? '' ) ) {
		$errores->add( 'dfans_cupon', 'El cupón de bienvenida es solo para tu primera compra. Quítalo para continuar.' );
	}
}, 10, 2 );

// Ventanita: solo visitantes sin sesión y fuera del carrito, el checkout y la cuenta.
add_action( 'wp_footer', function () {
	if ( is_user_logged_in() || is_admin() || ( function_exists( 'is_cart' ) && ( is_cart() || is_checkout() || is_account_page() ) ) || ( function_exists( 'dfans_es_vista_rapida' ) && dfans_es_vista_rapida() ) ) {
		return;
	}
	$privacidad = get_privacy_policy_url();
	?>
	<div class="dfans-bienvenida" role="dialog" aria-labelledby="dfans-bienvenida-titulo" hidden>
		<button type="button" class="dfans-bienvenida__cerrar" aria-label="Cerrar">×</button>
		<p class="dfans-bienvenida__chip">Primera compra</p>
		<p class="dfans-bienvenida__titulo" id="dfans-bienvenida-titulo">10% en tu primer polo D’FANS</p>
		<form class="dfans-bienvenida__form">
			<p class="dfans-bienvenida__texto">Déjanos tu correo y te damos el código. Mínimo de compra S/ 60.</p>
			<label class="screen-reader-text" for="dfans-bienvenida-correo">Correo electrónico</label>
			<input type="email" id="dfans-bienvenida-correo" name="correo" placeholder="tu@correo.com" required autocomplete="email">
			<label class="dfans-bienvenida__acepto"><input type="checkbox" name="acepto" required> Acepto recibir novedades de D’FANS<?php echo $privacidad ? ' (<a href="' . esc_url( $privacidad ) . '" target="_blank" rel="noopener">privacidad</a>)' : ''; ?>.</label>
			<button type="submit">Quiero mi 10%</button>
			<p class="dfans-bienvenida__error" role="alert" hidden></p>
		</form>
		<div class="dfans-bienvenida__listo" hidden>
			<p class="dfans-bienvenida__texto">Tu código para la primera compra:</p>
			<p class="dfans-bienvenida__codigo"><strong><?php echo esc_html( strtoupper( DFANS_CUPON_BIENVENIDA ) ); ?></strong> <button type="button" class="dfans-bienvenida__copiar">Copiar</button></p>
			<p class="dfans-bienvenida__texto">Escríbelo en el carrito. Vale una vez, desde S/ 60.</p>
		</div>
	</div>
	<script>
	(function () {
		var caja = document.querySelector('.dfans-bienvenida');
		if (!caja) { return; }
		var CLAVE = 'dfans_bienvenida', ahora = Date.now();
		try {
			var visto = JSON.parse(localStorage.getItem(CLAVE) || 'null');
			if (visto && (visto.estado === 'suscrito' || ahora - visto.t < 30 * 864e5)) { return; }
		} catch (e) {}
		function guardar(estado) { try { localStorage.setItem(CLAVE, JSON.stringify({ estado: estado, t: Date.now() })); } catch (e) {} }
		var mostrado = false;
		function mostrar() {
			if (mostrado) { return; }
			mostrado = true;
			caja.hidden = false;
			requestAnimationFrame(function () { caja.classList.add('is-visible'); });
			window.removeEventListener('scroll', alDesplazar);
		}
		function alDesplazar() {
			var h = document.documentElement;
			if ((h.scrollTop + innerHeight) / h.scrollHeight > 0.55) { mostrar(); }
		}
		setTimeout(mostrar, 15000);
		window.addEventListener('scroll', alDesplazar, { passive: true });
		caja.querySelector('.dfans-bienvenida__cerrar').addEventListener('click', function () {
			caja.classList.remove('is-visible');
			setTimeout(function () { caja.hidden = true; }, 300);
			guardar('cerrado');
		});
		var form = caja.querySelector('form'), error = caja.querySelector('.dfans-bienvenida__error');
		form.addEventListener('submit', function (ev) {
			ev.preventDefault();
			var boton = form.querySelector('button[type=submit]');
			boton.disabled = true;
			error.hidden = true;
			var d = new FormData(form);
			d.append('action', 'dfans_bienvenida');
			d.append('_ajax_nonce', '<?php echo esc_js( wp_create_nonce( 'dfans_bienvenida' ) ); ?>');
			fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', { method: 'POST', body: d, credentials: 'same-origin' })
				.then(function (r) { return r.json(); })
				.then(function (j) {
					if (!j.success) { throw new Error(j.data || 'No se pudo guardar. Inténtalo otra vez.'); }
					form.hidden = true;
					caja.querySelector('.dfans-bienvenida__listo').hidden = false;
					guardar('suscrito');
					if (typeof gtag === 'function') { gtag('event', 'generate_lead', { method: 'cupon_bienvenida' }); }
				})
				.catch(function (e) { error.textContent = e.message; error.hidden = false; boton.disabled = false; });
		});
		caja.querySelector('.dfans-bienvenida__copiar').addEventListener('click', function () {
			var b = this;
			try { navigator.clipboard.writeText('<?php echo esc_js( strtoupper( DFANS_CUPON_BIENVENIDA ) ); ?>'); b.textContent = '¡Copiado!'; } catch (e) {}
		});
	})();
	</script>
	<?php
}, 30 );

function dfans_bienvenida_ajax() {
	if ( ! check_ajax_referer( 'dfans_bienvenida', false, false ) ) {
		wp_send_json_error( 'La página estaba abierta hace mucho. Recárgala e inténtalo otra vez.' );
	}
	$correo = sanitize_email( wp_unslash( $_POST['correo'] ?? '' ) );
	if ( ! is_email( $correo ) || strlen( $correo ) > 120 ) {
		wp_send_json_error( 'Revisa tu correo, parece que tiene un error.' );
	}
	if ( empty( $_POST['acepto'] ) ) {
		wp_send_json_error( 'Marca la casilla para recibir el código.' );
	}
	// Límite simple: 5 intentos por hora por IP.
	$ip    = preg_replace( '/[^0-9a-f:.]/i', '', (string) ( $_SERVER['REMOTE_ADDR'] ?? '' ) );
	$clave = 'dfans_bienv_' . md5( $ip );
	$veces = (int) get_transient( $clave );
	if ( $veces >= 5 ) {
		wp_send_json_error( 'Demasiados intentos. Prueba en un rato.' );
	}
	set_transient( $clave, $veces + 1, HOUR_IN_SECONDS );
	$lista = get_option( 'dfans_suscriptores', array() );
	if ( ! is_array( $lista ) ) {
		$lista = array();
	}
	if ( ! isset( $lista[ strtolower( $correo ) ] ) ) {
		$lista[ strtolower( $correo ) ] = current_time( 'mysql' );
		update_option( 'dfans_suscriptores', $lista, false );
	}
	wp_send_json_success( array( 'codigo' => strtoupper( DFANS_CUPON_BIENVENIDA ) ) );
}
add_action( 'wp_ajax_dfans_bienvenida', 'dfans_bienvenida_ajax' );
add_action( 'wp_ajax_nopriv_dfans_bienvenida', 'dfans_bienvenida_ajax' );

// Herramientas → Suscriptores D'FANS (lista y descarga en CSV).
add_action( 'admin_menu', function () {
	add_management_page( 'Suscriptores D’FANS', 'Suscriptores D’FANS', 'manage_options', 'dfans-suscriptores', function () {
		$lista = get_option( 'dfans_suscriptores', array() );
		$lista = is_array( $lista ) ? $lista : array();
		arsort( $lista );
		echo '<div class="wrap"><h1>Suscriptores D’FANS</h1><p>Correos que pidieron el cupón de bienvenida y aceptaron recibir novedades: <strong>' . count( $lista ) . '</strong>.</p>';
		echo '<p><a class="button" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=dfans_suscriptores_csv' ), 'dfans_csv' ) ) . '">Descargar CSV</a></p>';
		echo '<table class="widefat striped"><thead><tr><th>Correo</th><th>Fecha</th></tr></thead><tbody>';
		foreach ( $lista as $correo => $fecha ) {
			echo '<tr><td>' . esc_html( $correo ) . '</td><td>' . esc_html( $fecha ) . '</td></tr>';
		}
		echo '</tbody></table></div>';
	} );
} );
add_action( 'admin_post_dfans_suscriptores_csv', function () {
	if ( ! current_user_can( 'manage_options' ) || ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'dfans_csv' ) ) {
		wp_die( 'Sin permiso.' );
	}
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=suscriptores-dfans.csv' );
	$f = fopen( 'php://output', 'w' );
	fputcsv( $f, array( 'correo', 'fecha' ) );
	foreach ( (array) get_option( 'dfans_suscriptores', array() ) as $correo => $fecha ) {
		fputcsv( $f, array( $correo, $fecha ) );
	}
	exit;
} );

/* ------------------------------------------------------------------ */
/* 4. Últimas unidades                                                 */
/* ------------------------------------------------------------------ */

/** Unidades disponibles del producto (suma las tallas). Null si no se controla el stock. */
function dfans_unidades( $producto ) {
	static $cache = array();
	$id = $producto->get_id();
	if ( ! array_key_exists( $id, $cache ) ) {
		$total     = null;
		$variantes = $producto->is_type( 'variable' ) ? array_filter( array_map( 'wc_get_product', $producto->get_children() ) ) : array( $producto );
		foreach ( $variantes as $v ) {
			if ( $v->managing_stock() ) {
				$total = (int) $total + max( 0, (int) $v->get_stock_quantity() );
			}
		}
		$cache[ $id ] = $total;
	}
	return $cache[ $id ];
}
function dfans_quedan_pocas( $producto ) {
	$n = $producto ? dfans_unidades( $producto ) : null;
	return null !== $n && $n > 0 && $n <= DFANS_ULTIMAS;
}

add_filter( 'render_block_woocommerce/product-image', function ( $html, $block, $instance ) {
	$producto = wc_get_product( $instance->context['postId'] ?? 0 );
	if ( ! $producto || ! dfans_quedan_pocas( $producto ) || false === ( $pos = strrpos( $html, '</div>' ) ) ) {
		return $html;
	}
	return substr_replace( $html, '<span class="dfans-ultimas">Últimas unidades</span>', $pos, 0 );
}, 20, 3 );

/* ------------------------------------------------------------------ */
/* 5. Reseñas                                                          */
/* ------------------------------------------------------------------ */

/** Estrellas en texto (★), coloreadas con CSS según la nota. */
function dfans_estrellas( $nota ) {
	$nota = max( 0, min( 5, (float) $nota ) );
	return '<span class="dfans-estrellas" style="--nota:' . esc_attr( $nota ) . '" role="img" aria-label="' . esc_attr( sprintf( '%s de 5 estrellas', wc_format_localized_decimal( round( $nota, 1 ) ) ) ) . '">★★★★★</span>';
}

// Estrellas junto al precio (tarjetas y ficha), solo si el polo tiene reseñas.
add_filter( 'render_block_woocommerce/product-price', function ( $html, $block, $instance ) {
	$producto = wc_get_product( $instance->context['postId'] ?? 0 );
	// En la ficha WooCommerce ya muestra sus estrellas; aquí solo van en las tarjetas.
	if ( ! $producto || ! $producto->get_review_count() || ( is_product() && (int) get_queried_object_id() === $producto->get_id() ) ) {
		return $html;
	}
	$n     = $producto->get_review_count();
	$texto = dfans_estrellas( $producto->get_average_rating() ) . '<span class="dfans-valoracion__total">' . esc_html( wc_format_localized_decimal( round( (float) $producto->get_average_rating(), 1 ) ) ) . ' (' . $n . ')</span>';
	return $html . '<div class="dfans-valoracion">' . $texto . '</div>';
}, 20, 3 );

// Fotos en las reseñas: el formulario acepta imágenes (solo si el cliente compró el polo).
add_action( 'comment_form_logged_in_after', function () {
	if ( ! is_product() ) {
		return;
	}
	$compro = wc_customer_bought_product( '', get_current_user_id(), get_the_ID() );
	if ( ! $compro ) {
		return;
	}
	echo '<p class="dfans-resena-fotos"><label for="dfans-fotos">Sube hasta 3 fotos con tu polo (opcional)</label>'
		. '<input type="file" id="dfans-fotos" name="dfans_fotos[]" accept="image/jpeg,image/png,image/webp" multiple>'
		. '<small>JPG, PNG o WebP, máximo 5 MB cada una. Las reseñas con foto se publican después de revisarlas.</small></p>'
		. '<script>(function(){var f=document.getElementById("commentform");if(f){f.enctype="multipart/form-data";}})();</script>';
} );

add_action( 'comment_post', function ( $comment_id ) {
	$comentario = get_comment( $comment_id );
	if ( ! $comentario || 'review' !== $comentario->comment_type || empty( $_FILES['dfans_fotos']['name'][0] ) ) {
		return;
	}
	$usuario  = (int) $comentario->user_id;
	$producto = (int) $comentario->comment_post_ID;
	if ( ! $usuario || ! wc_customer_bought_product( '', $usuario, $producto ) ) {
		return;
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$archivos = $_FILES['dfans_fotos'];
	$ids      = array();
	$total    = min( 3, count( (array) $archivos['name'] ) );
	for ( $i = 0; $i < $total; $i++ ) {
		if ( UPLOAD_ERR_OK !== (int) $archivos['error'][ $i ] || (int) $archivos['size'][ $i ] > 5 * MB_IN_BYTES ) {
			continue;
		}
		$tipo = wp_check_filetype_and_ext( $archivos['tmp_name'][ $i ], $archivos['name'][ $i ] );
		if ( ! in_array( $tipo['type'], array( 'image/jpeg', 'image/png', 'image/webp' ), true ) ) {
			continue;
		}
		$archivo = array(
			'name'     => sanitize_file_name( 'resena-' . $comment_id . '-' . ( $i + 1 ) . '.' . $tipo['ext'] ),
			'type'     => $tipo['type'],
			'tmp_name' => $archivos['tmp_name'][ $i ],
			'error'    => 0,
			'size'     => $archivos['size'][ $i ],
		);
		$id = media_handle_sideload( $archivo, $producto, 'Foto de reseña' );
		if ( ! is_wp_error( $id ) ) {
			$ids[] = (int) $id;
		}
	}
	if ( $ids ) {
		update_comment_meta( $comment_id, 'dfans_fotos', $ids );
		wp_set_comment_status( $comment_id, 'hold' ); // Las fotos se revisan antes de publicarse.
	}
} );

// Muestra las fotos debajo del texto de la reseña.
add_filter( 'comment_text', function ( $texto, $comentario ) {
	if ( ! $comentario || 'review' !== $comentario->comment_type ) {
		return $texto;
	}
	$ids = (array) get_comment_meta( $comentario->comment_ID, 'dfans_fotos', true );
	$ids = array_filter( array_map( 'intval', $ids ) );
	if ( ! $ids ) {
		return $texto;
	}
	$html = '<div class="dfans-resena-galeria">';
	foreach ( $ids as $id ) {
		$html .= '<a href="' . esc_url( wp_get_attachment_image_url( $id, 'large' ) ) . '" target="_blank" rel="noopener">' . wp_get_attachment_image( $id, 'thumbnail', false, array( 'loading' => 'lazy', 'alt' => 'Foto de un cliente con su polo' ) ) . '</a>';
	}
	return $texto . $html . '</div>';
}, 20, 2 );

// Portada: «Lo que dicen los fans» (no se muestra nada mientras no haya reseñas de 4 o 5 estrellas).
add_shortcode( 'dfans_resenas', function () {
	$resenas = get_comments( array(
		'type'       => 'review',
		'status'     => 'approve',
		'number'     => 6,
		'meta_query' => array( array( 'key' => 'rating', 'value' => 4, 'compare' => '>=', 'type' => 'NUMERIC' ) ),
	) );
	if ( ! $resenas ) {
		return '';
	}
	$html = '<section class="dfans-resenas alignwide" aria-labelledby="dfans-resenas-titulo">'
		. '<div class="dfans-resenas__cabecera"><h2 id="dfans-resenas-titulo">Lo que dicen los fans</h2><p>Reseñas de clientes que ya compraron.</p></div>'
		. '<div class="dfans-resenas__lista">';
	foreach ( $resenas as $r ) {
		$producto = wc_get_product( $r->comment_post_ID );
		$fotos    = array_filter( array_map( 'intval', (array) get_comment_meta( $r->comment_ID, 'dfans_fotos', true ) ) );
		$nombre   = get_comment_author( $r ); // Nombre real del cliente (sección 7).
		$html    .= '<article class="dfans-resena">'
			. ( $fotos ? '<div class="dfans-resena__foto">' . wp_get_attachment_image( reset( $fotos ), 'medium', false, array( 'loading' => 'lazy', 'alt' => 'Foto de un cliente con su polo' ) ) . '</div>' : '' )
			. dfans_estrellas( (int) get_comment_meta( $r->comment_ID, 'rating', true ) )
			. '<p class="dfans-resena__texto">' . esc_html( wp_trim_words( $r->comment_content, 34, '…' ) ) . '</p>'
			. '<p class="dfans-resena__autor"><strong>' . esc_html( $nombre ? $nombre : 'Cliente' ) . '</strong>'
			. ( get_comment_meta( $r->comment_ID, 'verified', true ) ? ' <span class="dfans-resena__verificado">Compra verificada</span>' : '' )
			. ( $producto ? '<br><a href="' . esc_url( $producto->get_permalink() ) . '">' . esc_html( $producto->get_name() ) . '</a>' : '' ) . '</p>'
			. '</article>';
	}
	return $html . '</div></section>';
} );

// Correo de pedido completado: invitación a dejar reseña de cada polo.
add_action( 'woocommerce_email_after_order_table', function ( $pedido, $para_admin, $texto_plano, $correo ) {
	if ( $para_admin || ! $correo || 'customer_completed_order' !== $correo->id ) {
		return;
	}
	$polos = array();
	foreach ( $pedido->get_items() as $item ) {
		$id = (int) $item->get_product_id();
		if ( $id && ! isset( $polos[ $id ] ) ) {
			$polos[ $id ] = get_the_title( $id );
		}
	}
	if ( ! $polos ) {
		return;
	}
	if ( $texto_plano ) {
		echo "\n¿Qué te pareció? Deja tu reseña (puedes subir una foto con tu polo):\n";
		foreach ( $polos as $id => $nombre ) {
			echo '- ' . esc_html( $nombre ) . ': ' . esc_url_raw( get_permalink( $id ) . '#reviews' ) . "\n";
		}
		return;
	}
	echo '<div style="margin:24px 0;padding:20px;border:1px solid #ececea;border-radius:8px;"><p style="margin:0 0 6px;font-size:18px;font-weight:bold;color:#0b0b0b;">¿Qué te pareció tu polo?</p>'
		. '<p style="margin:0 0 14px;">Tu reseña ayuda a otros fans a elegir. Si quieres, sube una foto con tu polo.</p>';
	foreach ( $polos as $id => $nombre ) {
		echo '<p style="margin:0 0 8px;"><a href="' . esc_url( get_permalink( $id ) . '#reviews' ) . '" style="color:#e0262f;font-weight:bold;">Dejar reseña: ' . esc_html( $nombre ) . '</a></p>';
	}
	echo '</div>';
}, 20, 4 );

/* ------------------------------------------------------------------ */
/* 6. Facebook en el pie                                               */
/* ------------------------------------------------------------------ */

add_filter( 'render_block_core/paragraph', function ( $html, $block ) {
	if ( empty( $block['attrs']['className'] ) || false === strpos( $block['attrs']['className'], 'dfans-footer-logo' ) ) {
		return $html;
	}
	return $html . '<p class="dfans-redes"><a href="' . esc_url( DFANS_FACEBOOK ) . '" target="_blank" rel="noopener" aria-label="D’FANS en Facebook">'
		. '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M13.5 21v-7.5h2.5l.4-3h-2.9V8.6c0-.9.3-1.5 1.5-1.5h1.5V4.4c-.3 0-1.2-.1-2.2-.1-2.2 0-3.7 1.3-3.7 3.8v2.4H8v3h2.6V21z"/></svg>'
		. '<span>Síguenos en Facebook</span></a></p>';
}, 30, 2 );

/* ------------------------------------------------------------------ */
/* JS: barra de delivery gratis y notas del checkout                   */
/* ------------------------------------------------------------------ */
add_action( 'wp_footer', function () {
	if ( is_admin() ) {
		return;
	}
	?>
	<script>
	(function () {
		var META = <?php echo (float) DFANS_ENVIO_GRATIS; ?>;
		function soles(n) { return 'S/ ' + n.toFixed(2); }
		function pintar(subtotal, items) {
			document.querySelectorAll('.dfans-envio').forEach(function (b) {
				if (!items) { b.hidden = true; return; }
				b.hidden = false;
				var falta = Math.max(0, META - subtotal);
				b.classList.toggle('is-gratis', falta <= 0);
				b.querySelector('.dfans-envio__texto').innerHTML = falta > 0
					? 'Te faltan <strong>' + soles(falta) + '</strong> para el <strong>delivery gratis en Lima</strong>.'
					: '<strong>¡Tienes delivery gratis en Lima!</strong> Tu compra supera ' + soles(META) + '.';
				b.querySelector('.dfans-envio__barra').style.width = Math.min(100, subtotal / META * 100) + '%';
			});
		}
		function desdeTotales(t, items) {
			var d = Math.pow(10, t.currency_minor_unit || 2);
			var sub = (parseInt(t.total_items || 0, 10) + parseInt(t.total_items_tax || 0, 10) - parseInt(t.total_discount || 0, 10) - parseInt(t.total_discount_tax || 0, 10)) / d;
			pintar(sub, items);
		}
		function cargar() {
			if (!document.querySelector('.dfans-envio')) { return; }
			fetch('<?php echo esc_url( rest_url( 'wc/store/v1/cart' ) ); ?>', { credentials: 'same-origin' })
				.then(function (r) { return r.json(); })
				.then(function (c) { desdeTotales(c.totals || {}, c.items_count || 0); })
				.catch(function () {});
		}
		// Mini carrito: agrega la barra cuando se abre.
		new MutationObserver(function () {
			var pie = document.querySelector('.wc-block-mini-cart__footer');
			if (pie && !pie.previousElementSibling?.classList?.contains('dfans-envio')) {
				pie.insertAdjacentHTML('beforebegin', <?php echo wp_json_encode( dfans_envio_barra_html() ); ?>);
				cargar();
			}
		}).observe(document.body, { childList: true, subtree: true });
		// Carrito de bloques: se actualiza al cambiar cantidades.
		if (window.wp && wp.data && wp.data.select && wp.data.select('wc/store/cart')) {
			var antes = '';
			wp.data.subscribe(function () {
				var s = wp.data.select('wc/store/cart'), t = s.getCartTotals && s.getCartTotals(), c = s.getCartData && s.getCartData();
				if (!t || !c) { return; }
				var firma = JSON.stringify([t.total_items, t.total_discount, c.itemsCount]);
				if (firma !== antes) { antes = firma; desdeTotales(t, c.itemsCount); }
			});
		}
		document.addEventListener('wc-blocks_added_to_cart', cargar);
		document.addEventListener('wc-blocks_removed_from_cart', cargar);
		if (window.jQuery) {
			jQuery(document.body).on('added_to_cart removed_from_cart updated_cart_totals updated_checkout applied_coupon removed_coupon', cargar);
			// Checkout: si el delivery es gratis, corrige los textos de «costo a coordinar».
			jQuery(document.body).on('updated_checkout', function () {
				setTimeout(function () {
					var gratis = false;
					jQuery('input[name^="shipping_method"]').each(function () {
						var t = jQuery(this).closest('li').text().toLowerCase();
						if (this.value.indexOf('free_shipping') === 0 || (t.indexOf('delivery en lima') > -1 && t.indexOf('gratis') > -1)) { gratis = true; }
					});
					var tarjeta = jQuery('input[name="dfans_tipo_entrega"][value="delivery"]').closest('label').find('small');
					if (tarjeta.length) {
						if (!tarjeta.data('original')) { tarjeta.data('original', tarjeta.html()); }
						tarjeta.html(gratis ? '<strong style="color:#16a34a">Gratis</strong> por tu compra de ' + soles(META) + ' o más.' : tarjeta.data('original'));
					}
					var nota = jQuery('.dfans-nota-envio');
					if (gratis && nota.length && nota.text().toLowerCase().indexOf('delivery en lima') > -1) {
						nota.html('🚚 <strong>Delivery gratis en Lima:</strong><br>Tu compra supera ' + soles(META) + ', así que el delivery corre por nuestra cuenta. Te escribiremos para coordinar la entrega.');
					}
				}, 60);
			});
		}
		if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', cargar); } else { cargar(); }
	})();
	</script>
	<?php
}, 45 );

/* ------------------------------------------------------------------ */
/* 7. Reseñas con el nombre real del cliente                           */
/* ------------------------------------------------------------------ */

/** «Nombre I.» con los datos de la cuenta (o de facturación). Vacío si el cliente no tiene nombre. */
function dfans_nombre_resena( $usuario_id ) {
	$usuario_id = (int) $usuario_id;
	if ( ! $usuario_id ) {
		return '';
	}
	$nombre   = trim( (string) ( get_user_meta( $usuario_id, 'first_name', true ) ?: get_user_meta( $usuario_id, 'billing_first_name', true ) ) );
	$apellido = trim( (string) ( get_user_meta( $usuario_id, 'last_name', true ) ?: get_user_meta( $usuario_id, 'billing_last_name', true ) ) );
	if ( '' === $nombre ) {
		return '';
	}
	$nombre = mb_convert_case( mb_strtolower( strtok( $nombre, ' ' ) ), MB_CASE_TITLE );
	return $apellido ? $nombre . ' ' . mb_strtoupper( mb_substr( $apellido, 0, 1 ) ) . '.' : $nombre;
}

// Para reseñar hay que tener nombre y apellido en la cuenta; la reseña se guarda con ese nombre.
add_filter( 'preprocess_comment', function ( $datos ) {
	if ( 'review' !== ( $datos['comment_type'] ?? '' ) && 'product' !== get_post_type( (int) ( $datos['comment_post_ID'] ?? 0 ) ) ) {
		return $datos;
	}
	// Las respuestas de la tienda (administrador) no se tocan.
	if ( current_user_can( 'moderate_comments' ) ) {
		return $datos;
	}
	$id     = get_current_user_id();
	$nombre = dfans_nombre_resena( $id );
	if ( ! $id || '' === $nombre || '' === trim( (string) ( get_user_meta( $id, 'last_name', true ) ?: get_user_meta( $id, 'billing_last_name', true ) ) ) ) {
		wp_die(
			'<p>Para publicar tu reseña necesitamos tu <strong>nombre y apellido</strong> (se muestra tu nombre y la inicial del apellido).</p>'
			. '<p><a href="' . esc_url( wc_get_account_endpoint_url( 'edit-account' ) ) . '">Agrégalos en Mi cuenta → Datos de la cuenta</a> y vuelve a enviar tu reseña.</p>',
			'Falta tu nombre',
			array( 'response' => 200, 'back_link' => true )
		);
	}
	$datos['comment_author'] = $nombre;
	return $datos;
}, 5 );

// Al mostrar cualquier reseña, se usa el nombre real de la cuenta.
add_filter( 'get_comment_author', function ( $autor, $comentario_id, $comentario ) {
	if ( $comentario && 'review' === $comentario->comment_type && $comentario->user_id ) {
		$real = dfans_nombre_resena( $comentario->user_id );
		return '' !== $real ? $real : $autor;
	}
	return $autor;
}, 20, 3 );

// En el formulario: «Tu reseña se publicará como: Nombre I.».
add_filter( 'comment_form_logged_in', function ( $html ) {
	if ( ! is_product() ) {
		return $html;
	}
	$nombre = dfans_nombre_resena( get_current_user_id() );
	if ( '' === $nombre ) {
		return '<p class="dfans-resena-nombre">Para dejar tu reseña, agrega tu nombre y apellido en <a href="' . esc_url( wc_get_account_endpoint_url( 'edit-account' ) ) . '">Mi cuenta → Datos de la cuenta</a>.</p>';
	}
	return '<p class="dfans-resena-nombre">Tu reseña se publicará como <strong>' . esc_html( $nombre ) . '</strong>.</p>';
} );

