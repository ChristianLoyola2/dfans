<?php
/**
 * Snippet #26: D'FANS - Libro de Reclamaciones y datos legales
 * Alcance: global · prioridad 10
 * Exportado de Code Snippets el 14/11/2026. La copia activa está en la base de datos.
 */

/**
 * D'FANS - Libro de Reclamaciones virtual y datos legales de la empresa.
 *
 * - [dfans_empresa dato="ruc"]        Muestra un dato de la empresa (se editan abajo, en un solo lugar).
 * - [dfans_libro_reclamaciones]       Formulario de la Hoja de Reclamación virtual.
 * - Menú "Libro de Reclamaciones" en el panel: registro de hojas y respuesta del proveedor.
 */

/*
 * DATOS DE LA EMPRESA: completa los vacíos. Aparecen en las páginas legales,
 * en la Hoja de Reclamación y en los correos. Vacío = se muestra "(por completar)".
 */
function dfans_empresa_datos() {
	return array(
		'nombre_comercial' => 'D’FANS',
		// Razón social y RUC FICTICIOS (proyecto en desarrollo). Reemplazar por los reales antes de lanzar.
		// 20000000000 no supera el dígito verificador de SUNAT: no puede pertenecer a una empresa real.
		'razon_social'     => 'D’FANS STREETWEAR S.A.C.',
		'ruc'              => '20000000000',
		'direccion'        => 'Jr. Agustín Gamarra 1215, La Victoria, Lima, Perú',
		'correo'           => 'cloyola794@gmail.com',
		'telefono'         => '+51 912 353 175',
	);
}

function dfans_empresa( $dato ) {
	$datos = dfans_empresa_datos();
	return isset( $datos[ $dato ] ) && '' !== $datos[ $dato ] ? $datos[ $dato ] : '';
}

add_shortcode( 'dfans_empresa', function ( $atts ) {
	$atts  = shortcode_atts( array( 'dato' => '' ), $atts );
	$valor = dfans_empresa( $atts['dato'] );
	return '' !== $valor ? esc_html( $valor ) : '<span class="dfans-por-completar">(por completar)</span>';
} );

/*
 * Las páginas legales siempre son visibles, aunque la tienda esté en modo "Próximamente".
 */
add_filter( 'woocommerce_coming_soon_exclude', function ( $excluir ) {
	return $excluir || is_page( array( 'terminos-y-condiciones', 'politica-de-privacidad', 'cambios-y-devoluciones', 'libro-de-reclamaciones' ) );
} );

/* ------------------------------------------------------------------
 * Registro de hojas de reclamación (solo visible en el panel).
 * ---------------------------------------------------------------- */
add_action( 'init', function () {
	register_post_type( 'dfans_reclamo', array(
		'labels'       => array(
			'name'          => 'Libro de Reclamaciones',
			'singular_name' => 'Hoja de reclamación',
			'edit_item'     => 'Hoja de reclamación',
			'all_items'     => 'Hojas de reclamación',
			'search_items'  => 'Buscar hojas',
			'not_found'     => 'No hay hojas de reclamación.',
		),
		'public'       => false,
		'show_ui'      => true,
		'menu_icon'    => 'dashicons-book-alt',
		'supports'     => array( 'title' ),
		'map_meta_cap' => true,
		'capabilities' => array( 'create_posts' => 'do_not_allow' ), // Solo se crean desde el formulario.
	) );
} );

function dfans_reclamo_campos() {
	return array(
		'nombre'        => 'Nombre completo',
		'tipo_doc'      => 'Tipo de documento',
		'num_doc'       => 'Número de documento',
		'domicilio'     => 'Domicilio',
		'telefono'      => 'Teléfono',
		'correo'        => 'Correo electrónico',
		'apoderado'     => 'Padre, madre o apoderado (si es menor de edad)',
		'bien'          => 'Tipo de bien contratado',
		'pedido_num'    => 'N.° de pedido',
		'monto'         => 'Monto reclamado (S/)',
		'descripcion'   => 'Descripción del producto o servicio',
		'tipo'          => 'Tipo',
		'detalle'       => 'Detalle',
		'pedido'        => 'Pedido del consumidor',
	);
}

/* ------------------------------------------------------------------
 * Procesa el envío del formulario (antes de mostrar la página).
 * ---------------------------------------------------------------- */
add_action( 'template_redirect', function () {
	if ( empty( $_POST['dfans_reclamo_nonce'] ) ) {
		return;
	}

	global $dfans_reclamo_errores;
	$dfans_reclamo_errores = array();

	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dfans_reclamo_nonce'] ) ), 'dfans_reclamo' ) ) {
		$dfans_reclamo_errores[] = 'La sesión expiró. Vuelve a enviar el formulario.';
		return;
	}
	if ( ! empty( $_POST['dfans_web'] ) ) { // Campo trampa contra spam.
		return;
	}

	$d = array();
	foreach ( array_keys( dfans_reclamo_campos() ) as $k ) {
		$v       = isset( $_POST[ "dfans_$k" ] ) ? wp_unslash( $_POST[ "dfans_$k" ] ) : '';
		$d[ $k ] = in_array( $k, array( 'descripcion', 'detalle', 'pedido' ), true ) ? sanitize_textarea_field( $v ) : sanitize_text_field( $v );
	}

	foreach ( array( 'nombre', 'num_doc', 'domicilio', 'telefono', 'correo', 'descripcion', 'detalle', 'pedido' ) as $k ) {
		if ( '' === $d[ $k ] ) {
			$dfans_reclamo_errores[] = 'Completa el campo: ' . dfans_reclamo_campos()[ $k ] . '.';
		}
	}
	if ( ! in_array( $d['tipo_doc'], array( 'DNI', 'Carné de extranjería', 'Pasaporte' ), true ) ) {
		$dfans_reclamo_errores[] = 'Elige el tipo de documento.';
	}
	if ( 'DNI' === $d['tipo_doc'] && ! preg_match( '/^\d{8}$/', $d['num_doc'] ) ) {
		$dfans_reclamo_errores[] = 'El DNI debe tener 8 dígitos.';
	}
	if ( '' !== $d['correo'] && ! is_email( $d['correo'] ) ) {
		$dfans_reclamo_errores[] = 'El correo electrónico no es válido.';
	}
	if ( ! in_array( $d['bien'], array( 'Producto', 'Servicio' ), true ) ) {
		$dfans_reclamo_errores[] = 'Indica si se trata de un producto o un servicio.';
	}
	if ( ! in_array( $d['tipo'], array( 'Reclamo', 'Queja' ), true ) ) {
		$dfans_reclamo_errores[] = 'Indica si es un reclamo o una queja.';
	}
	if ( '' !== $d['monto'] && ! is_numeric( str_replace( ',', '.', $d['monto'] ) ) ) {
		$dfans_reclamo_errores[] = 'El monto reclamado debe ser un número.';
	}
	if ( empty( $_POST['dfans_acepto'] ) ) {
		$dfans_reclamo_errores[] = 'Debes aceptar el tratamiento de tus datos para registrar la hoja.';
	}
	if ( $dfans_reclamo_errores ) {
		return;
	}

	// Número correlativo: 00001-2026.
	$n      = (int) get_option( 'dfans_reclamo_ultimo', 0 ) + 1;
	$codigo = sprintf( '%05d-%s', $n, wp_date( 'Y' ) );
	update_option( 'dfans_reclamo_ultimo', $n, false );

	$id = wp_insert_post( array(
		'post_type'   => 'dfans_reclamo',
		'post_status' => 'publish',
		'post_title'  => "Hoja N.° $codigo – {$d['tipo']} – {$d['nombre']}",
	) );
	if ( ! $id || is_wp_error( $id ) ) {
		$dfans_reclamo_errores[] = 'No se pudo registrar la hoja. Inténtalo nuevamente.';
		return;
	}
	update_post_meta( $id, '_dfans_codigo', $codigo );
	update_post_meta( $id, '_dfans_fecha', wp_date( 'd/m/Y H:i' ) );
	foreach ( $d as $k => $v ) {
		update_post_meta( $id, "_dfans_$k", $v );
	}

	// Copia al consumidor y aviso a la tienda.
	$html    = dfans_reclamo_html( $id );
	$headers = array( 'Content-Type: text/html; charset=UTF-8' );
	wp_mail( $d['correo'], "Hoja de Reclamación N.° $codigo – D’FANS", $html, $headers );
	$tienda = dfans_empresa( 'correo' ) ?: get_option( 'admin_email' );
	wp_mail( $tienda, "[D’FANS] Nueva hoja de reclamación N.° $codigo ({$d['tipo']})", $html, $headers );

	wp_safe_redirect( add_query_arg( 'hoja', rawurlencode( $codigo ), get_permalink() ) );
	exit;
} );

/** Hoja de reclamación en HTML (correo y panel). */
function dfans_reclamo_html( $id ) {
	$m   = function ( $k ) use ( $id ) {
		return esc_html( get_post_meta( $id, "_dfans_$k", true ) );
	};
	$e   = function ( $k ) {
		return esc_html( dfans_empresa( $k ) ?: '(por completar)' );
	};
	$fila = function ( $etiqueta, $valor ) {
		return '<tr><th style="text-align:left;padding:6px 10px;border:1px solid #ddd;background:#f6f6f6;width:38%">' . $etiqueta . '</th><td style="padding:6px 10px;border:1px solid #ddd">' . nl2br( $valor ) . '</td></tr>';
	};
	$campos = dfans_reclamo_campos();

	$h  = '<div style="font-family:Arial,sans-serif;max-width:680px;color:#111">';
	$h .= '<h2 style="margin:0 0 4px">LIBRO DE RECLAMACIONES – HOJA DE RECLAMACIÓN</h2>';
	$h .= '<p style="margin:0 0 12px"><strong>N.° ' . $m( 'codigo' ) . '</strong> · Fecha: ' . $m( 'fecha' ) . '</p>';
	$h .= '<p style="margin:0 0 12px">' . $e( 'nombre_comercial' ) . ' · Razón social: ' . $e( 'razon_social' ) . ' · RUC: ' . $e( 'ruc' ) . '<br>Dirección: ' . $e( 'direccion' ) . '</p>';
	$h .= '<table style="border-collapse:collapse;width:100%;font-size:14px">';
	$h .= '<tr><th colspan="2" style="text-align:left;padding:8px 10px;background:#111;color:#fff">1. Identificación del consumidor reclamante</th></tr>';
	foreach ( array( 'nombre', 'tipo_doc', 'num_doc', 'domicilio', 'telefono', 'correo', 'apoderado' ) as $k ) {
		$h .= $fila( $campos[ $k ], $m( $k ) ?: '—' );
	}
	$h .= '<tr><th colspan="2" style="text-align:left;padding:8px 10px;background:#111;color:#fff">2. Identificación del bien contratado</th></tr>';
	foreach ( array( 'bien', 'pedido_num', 'monto', 'descripcion' ) as $k ) {
		$h .= $fila( $campos[ $k ], $m( $k ) ?: '—' );
	}
	$h .= '<tr><th colspan="2" style="text-align:left;padding:8px 10px;background:#111;color:#fff">3. Detalle de la reclamación y pedido del consumidor</th></tr>';
	foreach ( array( 'tipo', 'detalle', 'pedido' ) as $k ) {
		$h .= $fila( $campos[ $k ], $m( $k ) );
	}
	$h .= '<tr><th colspan="2" style="text-align:left;padding:8px 10px;background:#111;color:#fff">4. Observaciones y acciones adoptadas por el proveedor</th></tr>';
	$h .= $fila( 'Respuesta', $m( 'respuesta' ) ?: 'Pendiente' );
	$h .= $fila( 'Fecha de respuesta', $m( 'fecha_respuesta' ) ?: '—' );
	$h .= '</table>';
	$h .= '<p style="font-size:12px;color:#555;margin-top:12px"><strong>Reclamo:</strong> disconformidad relacionada a los productos o servicios. <strong>Queja:</strong> disconformidad no relacionada a los productos o servicios, o malestar o descontento respecto a la atención al público.<br>';
	$h .= 'El proveedor deberá dar respuesta al reclamo o queja en un plazo no mayor a quince (15) días hábiles improrrogables.<br>';
	$h .= 'La formulación del reclamo no impide acudir a otras vías de solución de controversias ni es requisito previo para interponer una denuncia ante el INDECOPI.</p>';
	$h .= '</div>';
	return $h;
}

/* ------------------------------------------------------------------
 * Formulario público: [dfans_libro_reclamaciones]
 * ---------------------------------------------------------------- */
add_shortcode( 'dfans_libro_reclamaciones', function () {
	if ( ! empty( $_GET['hoja'] ) ) {
		$codigo = sanitize_text_field( wp_unslash( $_GET['hoja'] ) );
		return '<div class="dfans-reclamo-ok"><h3>Tu hoja de reclamación fue registrada</h3>'
			. '<p>Número de hoja: <strong>' . esc_html( $codigo ) . '</strong></p>'
			. '<p>Enviamos una copia a tu correo electrónico. Te responderemos en un plazo no mayor a quince (15) días hábiles.</p></div>';
	}

	global $dfans_reclamo_errores;
	$v = function ( $k ) {
		return isset( $_POST[ "dfans_$k" ] ) ? esc_attr( wp_unslash( $_POST[ "dfans_$k" ] ) ) : '';
	};
	$sel = function ( $k, $opcion ) use ( $v ) {
		return $v( $k ) === $opcion ? ' selected' : '';
	};
	$chk = function ( $k, $opcion ) use ( $v ) {
		return $v( $k ) === $opcion ? ' checked' : '';
	};

	ob_start();
	?>
	<form class="dfans-reclamo" method="post" novalidate>
		<p class="dfans-reclamo-cabecera">
			<strong>Fecha:</strong> <?php echo esc_html( wp_date( 'd/m/Y' ) ); ?> ·
			<strong><?php echo esc_html( dfans_empresa( 'nombre_comercial' ) ); ?></strong> ·
			Razón social: <?php echo do_shortcode( '[dfans_empresa dato="razon_social"]' ); ?> ·
			RUC: <?php echo do_shortcode( '[dfans_empresa dato="ruc"]' ); ?><br>
			Dirección: <?php echo do_shortcode( '[dfans_empresa dato="direccion"]' ); ?>
		</p>

		<?php if ( ! empty( $dfans_reclamo_errores ) ) : ?>
			<div class="dfans-reclamo-errores" role="alert"><ul><li><?php echo implode( '</li><li>', array_map( 'esc_html', $dfans_reclamo_errores ) ); ?></li></ul></div>
		<?php endif; ?>

		<fieldset>
			<legend>1. Identificación del consumidor reclamante</legend>
			<label>Nombre completo *<input type="text" name="dfans_nombre" value="<?php echo $v( 'nombre' ); ?>" required></label>
			<div class="dfans-reclamo-fila">
				<label>Tipo de documento *
					<select name="dfans_tipo_doc" required>
						<option value="">Elige…</option>
						<?php foreach ( array( 'DNI', 'Carné de extranjería', 'Pasaporte' ) as $o ) : ?>
							<option<?php echo $sel( 'tipo_doc', $o ); ?>><?php echo esc_html( $o ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<label>Número de documento *<input type="text" name="dfans_num_doc" value="<?php echo $v( 'num_doc' ); ?>" required></label>
			</div>
			<label>Domicilio *<input type="text" name="dfans_domicilio" value="<?php echo $v( 'domicilio' ); ?>" required></label>
			<div class="dfans-reclamo-fila">
				<label>Teléfono *<input type="tel" name="dfans_telefono" value="<?php echo $v( 'telefono' ); ?>" required></label>
				<label>Correo electrónico *<input type="email" name="dfans_correo" value="<?php echo $v( 'correo' ); ?>" required></label>
			</div>
			<label>Si eres menor de edad: nombre del padre, madre o apoderado<input type="text" name="dfans_apoderado" value="<?php echo $v( 'apoderado' ); ?>"></label>
		</fieldset>

		<fieldset>
			<legend>2. Identificación del bien contratado</legend>
			<div class="dfans-reclamo-opciones">
				<label><input type="radio" name="dfans_bien" value="Producto"<?php echo $chk( 'bien', 'Producto' ) ?: ( '' === $v( 'bien' ) ? ' checked' : '' ); ?>> Producto</label>
				<label><input type="radio" name="dfans_bien" value="Servicio"<?php echo $chk( 'bien', 'Servicio' ); ?>> Servicio</label>
			</div>
			<div class="dfans-reclamo-fila">
				<label>N.° de pedido (si lo tienes)<input type="text" name="dfans_pedido_num" value="<?php echo $v( 'pedido_num' ); ?>"></label>
				<label>Monto reclamado (S/)<input type="text" inputmode="decimal" name="dfans_monto" value="<?php echo $v( 'monto' ); ?>"></label>
			</div>
			<label>Descripción del producto o servicio *<textarea name="dfans_descripcion" rows="3" required><?php echo esc_textarea( isset( $_POST['dfans_descripcion'] ) ? wp_unslash( $_POST['dfans_descripcion'] ) : '' ); ?></textarea></label>
		</fieldset>

		<fieldset>
			<legend>3. Detalle de la reclamación y pedido del consumidor</legend>
			<div class="dfans-reclamo-opciones">
				<label><input type="radio" name="dfans_tipo" value="Reclamo"<?php echo $chk( 'tipo', 'Reclamo' ) ?: ( '' === $v( 'tipo' ) ? ' checked' : '' ); ?>> <strong>Reclamo:</strong> disconformidad relacionada a los productos o servicios.</label>
				<label><input type="radio" name="dfans_tipo" value="Queja"<?php echo $chk( 'tipo', 'Queja' ); ?>> <strong>Queja:</strong> disconformidad no relacionada a los productos o servicios, o malestar respecto a la atención al público.</label>
			</div>
			<label>Detalle *<textarea name="dfans_detalle" rows="4" required><?php echo esc_textarea( isset( $_POST['dfans_detalle'] ) ? wp_unslash( $_POST['dfans_detalle'] ) : '' ); ?></textarea></label>
			<label>Pedido *<textarea name="dfans_pedido" rows="3" required><?php echo esc_textarea( isset( $_POST['dfans_pedido'] ) ? wp_unslash( $_POST['dfans_pedido'] ) : '' ); ?></textarea></label>
		</fieldset>

		<p class="dfans-reclamo-trampa" aria-hidden="true"><label>Web<input type="text" name="dfans_web" tabindex="-1" autocomplete="off"></label></p>

		<label class="dfans-reclamo-acepto"><input type="checkbox" name="dfans_acepto" value="1"<?php echo empty( $_POST['dfans_acepto'] ) ? '' : ' checked'; ?>> Acepto que mis datos sean usados para atender esta hoja de reclamación, según la <a href="<?php echo esc_url( get_privacy_policy_url() ); ?>">política de privacidad</a>.</label>

		<p class="dfans-reclamo-aviso">
			El proveedor deberá dar respuesta al reclamo o queja en un plazo no mayor a quince (15) días hábiles improrrogables.
			La formulación del reclamo no impide acudir a otras vías de solución de controversias ni es requisito previo para interponer una denuncia ante el INDECOPI.
			Recibirás una copia de esta hoja en tu correo electrónico.
		</p>

		<?php wp_nonce_field( 'dfans_reclamo', 'dfans_reclamo_nonce' ); ?>
		<button type="submit" class="wp-element-button">Enviar hoja de reclamación</button>
	</form>
	<?php
	return ob_get_clean();
} );

/* ------------------------------------------------------------------
 * Panel: ver la hoja y registrar la respuesta del proveedor.
 * ---------------------------------------------------------------- */
add_action( 'add_meta_boxes_dfans_reclamo', function () {
	add_meta_box( 'dfans_reclamo_hoja', 'Hoja de reclamación', function ( $post ) {
		echo dfans_reclamo_html( $post->ID ); // Contenido ya escapado.
	}, 'dfans_reclamo', 'normal', 'high' );

	add_meta_box( 'dfans_reclamo_respuesta', '4. Observaciones y acciones adoptadas por el proveedor', function ( $post ) {
		wp_nonce_field( 'dfans_reclamo_respuesta', 'dfans_reclamo_respuesta_nonce' );
		echo '<p><textarea name="dfans_respuesta" rows="6" style="width:100%">' . esc_textarea( get_post_meta( $post->ID, '_dfans_respuesta', true ) ) . '</textarea></p>';
		echo '<p><label><input type="checkbox" name="dfans_enviar_respuesta" value="1"> Enviar la respuesta al correo del consumidor al guardar</label></p>';
		$fecha = get_post_meta( $post->ID, '_dfans_fecha_respuesta', true );
		echo $fecha ? '<p>Última respuesta registrada: ' . esc_html( $fecha ) . '</p>' : '';
	}, 'dfans_reclamo', 'normal', 'default' );
} );

add_action( 'save_post_dfans_reclamo', function ( $post_id ) {
	if ( empty( $_POST['dfans_reclamo_respuesta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dfans_reclamo_respuesta_nonce'] ) ), 'dfans_reclamo_respuesta' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$respuesta = sanitize_textarea_field( wp_unslash( $_POST['dfans_respuesta'] ?? '' ) );
	if ( $respuesta !== get_post_meta( $post_id, '_dfans_respuesta', true ) ) {
		update_post_meta( $post_id, '_dfans_respuesta', $respuesta );
		update_post_meta( $post_id, '_dfans_fecha_respuesta', wp_date( 'd/m/Y H:i' ) );
	}
	if ( ! empty( $_POST['dfans_enviar_respuesta'] ) && '' !== $respuesta ) {
		$codigo = get_post_meta( $post_id, '_dfans_codigo', true );
		wp_mail(
			get_post_meta( $post_id, '_dfans_correo', true ),
			"Respuesta a tu Hoja de Reclamación N.° $codigo – D’FANS",
			dfans_reclamo_html( $post_id ),
			array( 'Content-Type: text/html; charset=UTF-8' )
		);
	}
} );

// Columnas del listado.
add_filter( 'manage_dfans_reclamo_posts_columns', function () {
	return array( 'cb' => '<input type="checkbox">', 'title' => 'Hoja', 'dfans_fecha' => 'Fecha', 'dfans_estado' => 'Estado' );
} );
add_action( 'manage_dfans_reclamo_posts_custom_column', function ( $col, $id ) {
	if ( 'dfans_fecha' === $col ) {
		echo esc_html( get_post_meta( $id, '_dfans_fecha', true ) );
	}
	if ( 'dfans_estado' === $col ) {
		echo get_post_meta( $id, '_dfans_respuesta', true ) ? '✅ Respondida' : '⏳ Pendiente';
	}
}, 10, 2 );

