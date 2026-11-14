<?php
/**
 * Snippet #29: D'FANS - Fandom e interacción
 * Alcance: global · prioridad 10
 * Exportado de Code Snippets el 14/11/2026. La copia activa está en la base de datos.
 */

/*
 * D'FANS - Fandom e interacción (18/10/2026)
 *
 * - Fuente de acento "Bangers" (stickers, quiz, palabra que rota).
 * - Barra de anuncios animada arriba de la cabecera.
 * - Tarjetas: corazón de favoritos y botón "Vista rápida".
 * - Cabecera: acceso a "Mis favoritos" con contador.
 * - Vista rápida: el producto se abre en una ventana (iframe) sin cabecera ni pie.
 * - Guía de tallas según el corte del producto (medidas referenciales).
 * - Shortcodes [dfans_quiz] (¿Cuál es tu fandom?) y [dfans_favoritos].
 * - JS: palabra que rota en la portada, animaciones al hacer scroll, cabecera compacta,
 *   volver arriba, favoritos y vistos recientemente (en el navegador del cliente),
 *   barra fija de compra en móvil.
 * Todo respeta "reducir movimiento" del sistema. Estilos en el CSS global ("Fandom e interacción").
 */

/** ¿Es la vista rápida (producto dentro de la ventana)? */
function dfans_es_vista_rapida() {
	return isset( $_GET['dfans_vista'] ) && is_product();
}

// Fuente de acento.
add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'dfans-bangers', 'https://fonts.googleapis.com/css2?family=Bangers&display=swap', array(), null );
} );

// Barra de anuncios (no en el checkout ni en la vista rápida).
add_action( 'wp_body_open', function () {
	if ( is_admin() || is_checkout() || dfans_es_vista_rapida() ) {
		return;
	}
	$avisos = array(
		'Envíos a todo el Perú',
		'Paga con tarjeta, Yape o transferencia',
		'Colección Match: el mismo polo para toda la familia',
		'Nuevos exclusivos para él, ella, niño y niña',
		'Recojo gratis en Gamarra',
	);
	$lista = '';
	foreach ( $avisos as $aviso ) {
		$lista .= '<li>' . esc_html( $aviso ) . '</li>';
	}
	// La lista va dos veces para que el desplazamiento sea continuo; la copia se oculta a lectores de pantalla.
	echo '<div class="dfans-anuncios" role="region" aria-label="Novedades de la tienda"><div class="dfans-anuncios__pista">'
		. '<ul>' . $lista . '</ul><ul aria-hidden="true">' . $lista . '</ul></div></div>';
} );

// Tarjetas: corazón de favoritos y "Vista rápida" (fuera del enlace de la imagen).
add_filter( 'render_block_woocommerce/product-image', function ( $html, $block, $instance ) {
	$product_id = $instance->context['postId'] ?? 0;
	if ( ! $product_id || ! $html || false === strrpos( $html, '</div>' ) ) {
		return $html;
	}
	$url    = preg_match( '/<a\b[^>]*href="([^"]+)"/', $html, $m ) ? html_entity_decode( $m[1] ) : get_permalink( $product_id );
	$nombre = get_the_title( $product_id );
	$botones = sprintf(
		'<button type="button" class="dfans-fav" data-id="%1$d" aria-pressed="false" aria-label="%2$s"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s-7.5-4.6-9.6-9.2C.9 8.4 3 4.5 6.7 4.5c2.1 0 3.6 1.2 4.3 2.4.7-1.2 2.2-2.4 4.3-2.4 3.7 0 5.8 3.9 4.3 7.3C19.5 16.4 12 21 12 21z"/></svg></button>'
		. '<button type="button" class="dfans-vista" data-url="%3$s" aria-label="%4$s"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5C6.5 5 2.7 9.4 1.5 12c1.2 2.6 5 7 10.5 7s9.3-4.4 10.5-7C21.3 9.4 17.5 5 12 5zm0 11a4 4 0 110-8 4 4 0 010 8z"/></svg><span>Vista rápida</span></button>',
		$product_id,
		esc_attr( sprintf( 'Guardar %s en favoritos', $nombre ) ),
		esc_url( add_query_arg( 'dfans_vista', '1', $url ) ),
		esc_attr( sprintf( 'Vista rápida de %s', $nombre ) )
	);
	$pos = strrpos( $html, '</div>' );
	return substr_replace( $html, $botones . '</div>', $pos, 6 );
}, 20, 3 );

// Cabecera: acceso a "Mis favoritos" con contador (lo llena el JS).
add_filter( 'render_block_woocommerce/customer-account', function ( $html ) {
	return '<a class="dfans-fav-cabecera" href="' . esc_url( home_url( '/favoritos/' ) ) . '" aria-label="Mis favoritos">'
		. '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s-7.5-4.6-9.6-9.2C.9 8.4 3 4.5 6.7 4.5c2.1 0 3.6 1.2 4.3 2.4.7-1.2 2.2-2.4 4.3-2.4 3.7 0 5.8 3.9 4.3 7.3C19.5 16.4 12 21 12 21z"/></svg>'
		. '<span class="dfans-fav-num" hidden>0</span></a>' . $html;
} );

// Vista rápida: clase en el body para ocultar cabecera, pie y extras.
add_filter( 'body_class', function ( $clases ) {
	if ( dfans_es_vista_rapida() ) {
		$clases[] = 'dfans-vista-rapida';
	}
	return $clases;
} );

/** Tablas de medidas (cm, referenciales) según el corte del producto y la sección. */
function dfans_guia_tallas( $product_id ) {
	$tablas = array(
		'adulto-unisex'     => array( 'Adulto unisex (Colección Match)', array( 'S' => array( 48, 70 ), 'M' => array( 51, 72 ), 'L' => array( 54, 74 ), 'XL' => array( 57, 76 ), 'XXL' => array( 60, 78 ) ) ),
		'ninos-unisex'      => array( 'Niños unisex (Colección Match)', array( '10' => array( 40, 52 ), '12' => array( 43, 56 ), '14' => array( 46, 60 ) ) ),
		'oversize'          => array( 'Oversize · hombre', array( 'S' => array( 55, 72 ), 'M' => array( 58, 74 ), 'L' => array( 61, 76 ), 'XL' => array( 64, 78 ), 'XXL' => array( 67, 80 ) ) ),
		'baby-tee-crop'     => array( 'Baby tee (crop) · mujer', array( 'XS' => array( 40, 45 ), 'S' => array( 42, 47 ), 'M' => array( 44, 49 ), 'L' => array( 46, 51 ) ) ),
		'regular-reforzado' => array( 'Regular reforzado · niño', array( '10' => array( 40, 52 ), '12' => array( 43, 56 ), '14' => array( 46, 60 ) ) ),
		'manga-mariposa'    => array( 'Manga mariposa · niña', array( '10' => array( 38, 50 ), '12' => array( 41, 54 ), '14' => array( 44, 58 ) ) ),
	);
	$corte = wp_get_post_terms( $product_id, 'pa_corte', array( 'fields' => 'slugs' ) );
	if ( $corte && ! is_wp_error( $corte ) && isset( $tablas[ $corte[0] ] ) ) {
		return array( $tablas[ $corte[0] ] );
	}
	$seccion = function_exists( 'dfans_seccion' ) ? dfans_seccion() : '';
	if ( 'adultos' === $seccion ) {
		return array( $tablas['adulto-unisex'] );
	}
	if ( 'ninos' === $seccion ) {
		return array( $tablas['ninos-unisex'] );
	}
	return array( $tablas['adulto-unisex'], $tablas['ninos-unisex'] );
}

// Guía de tallas: ventana con la tabla (el botón lo coloca el JS junto a "Talla").
add_action( 'wp_footer', function () {
	if ( ! is_product() ) {
		return;
	}
	echo '<dialog class="dfans-modal dfans-modal--guia" id="dfans-guia" aria-labelledby="dfans-guia-titulo">'
		. '<button type="button" class="dfans-modal__cerrar" aria-label="Cerrar">×</button>'
		. '<h2 id="dfans-guia-titulo">Guía de tallas</h2>'
		. '<p class="dfans-guia__nota">Medidas referenciales en centímetros, con el polo extendido. Pueden variar ±2 cm.</p>';
	foreach ( dfans_guia_tallas( get_the_ID() ) as list( $titulo, $filas ) ) {
		echo '<table class="dfans-guia__tabla"><caption>' . esc_html( $titulo ) . '</caption>'
			. '<thead><tr><th scope="col">Talla</th><th scope="col">Ancho de pecho</th><th scope="col">Largo total</th></tr></thead><tbody>';
		foreach ( $filas as $talla => list( $ancho, $largo ) ) {
			echo '<tr><th scope="row">' . esc_html( $talla ) . '</th><td>' . (int) $ancho . ' cm</td><td>' . (int) $largo . ' cm</td></tr>';
		}
		echo '</tbody></table>';
	}
	echo '<p class="dfans-guia__nota"><strong>¿Cómo medir?</strong> Ancho: de axila a axila. Largo: desde el hombro hasta la basta. Compara con un polo que te quede bien.</p></dialog>';
} );

// Quiz "¿Cuál es tu fandom?" (lo arma el JS).
add_shortcode( 'dfans_quiz', function () {
	return '<div class="dfans-quiz alignwide" data-adultos="' . esc_url( home_url( '/product-category/ropa-de-adultos/' ) ) . '" data-ninos="' . esc_url( home_url( '/product-category/ropa-para-ninos/' ) ) . '">'
		. '<div class="dfans-quiz__cabecera"><h2>¿Cuál es tu fandom?</h2>'
		. '<p>Responde 3 preguntas rápidas y te recomendamos tus polos.</p></div>'
		. '<div class="dfans-quiz__cuerpo" aria-live="polite"><button type="button" class="dfans-quiz__empezar">¡Empezar!</button></div></div>';
} );

// Página "Mis favoritos" (los datos están en el navegador del cliente).
add_shortcode( 'dfans_favoritos', function () {
	return '<div class="dfans-favoritos" data-tienda="' . esc_url( home_url( '/shop/' ) ) . '"><p class="dfans-favoritos__cargando">Cargando tus favoritos…</p></div>';
} );

// JS de la tienda.
add_action( 'wp_footer', function () {
	if ( is_admin() ) {
		return;
	}
	?>
	<script>
	(function () {
		'use strict';

		var reducir = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
		var enVista = document.body.classList.contains('dfans-vista-rapida');

		function leer(clave) {
			try { return JSON.parse(localStorage.getItem(clave)) || []; } catch (e) { return []; }
		}
		function guardar(clave, valor) {
			try { localStorage.setItem(clave, JSON.stringify(valor)); } catch (e) {}
		}
		function esc(t) {
			return String(t == null ? '' : t).replace(/[&<>"']/g, function (c) {
				return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
			});
		}
		// Texto visible de un precio (sin el texto solo para lectores de pantalla).
		function textoPrecio(el) {
			if (!el) { return ''; }
			var copia = el.cloneNode(true);
			copia.querySelectorAll('.screen-reader-text').forEach(function (s) { s.remove(); });
			return copia.textContent.replace(/\s+/g, ' ').trim();
		}
		function urlLimpia(u) {
			try {
				var x = new URL(u, location.href);
				x.searchParams.delete('dfans_vista');
				x.searchParams.delete('woo-share');
				return x.pathname + x.search;
			} catch (e) { return u; }
		}

		// Aviso flotante.
		var avisoTimer;
		function aviso(html) {
			var el = document.querySelector('.dfans-aviso');
			if (!el) {
				el = document.createElement('div');
				el.className = 'dfans-aviso';
				el.setAttribute('role', 'status');
				document.body.appendChild(el);
			}
			el.innerHTML = html;
			el.classList.add('is-visible');
			clearTimeout(avisoTimer);
			avisoTimer = setTimeout(function () { el.classList.remove('is-visible'); }, 3500);
		}

		// Tarjeta simple para favoritos y vistos recientemente.
		function miniTarjeta(p, quitar) {
			return '<article class="dfans-mini">'
				+ '<a class="dfans-mini__img" href="' + esc(p.url) + '"><img src="' + esc(p.img) + '" alt="" loading="lazy"></a>'
				+ '<div class="dfans-mini__info"><a class="dfans-mini__nombre" href="' + esc(p.url) + '">' + esc(p.nombre) + '</a>'
				+ '<span class="dfans-mini__precio">' + esc(p.precio) + '</span></div>'
				+ (quitar ? '<button type="button" class="dfans-mini__quitar" data-id="' + esc(p.id) + '">Quitar</button>' : '')
				+ '</article>';
		}

		// En la vista rápida: los enlaces abren en la ventana principal y el carrito avisa a la tienda.
		if (enVista) {
			document.querySelectorAll('a[href]').forEach(function (a) { a.target = '_top'; });
			document.querySelectorAll('form.cart').forEach(function (f) { f.action = location.href; });
			var msg = document.querySelector('.woocommerce-message, .wc-block-components-notice-banner.is-success');
			if (msg && window.parent !== window) {
				window.parent.postMessage({ dfans: 'agregado', texto: msg.textContent.replace(/\s+/g, ' ').trim() }, location.origin);
			}
		}

		// 1. Portada: la palabra destacada del título va rotando.
		var marca = document.querySelector('.dfans-hero-titulo mark');
		if (marca && !reducir) {
			var titulo = marca.closest('h1');
			titulo.setAttribute('aria-label', titulo.textContent.trim());
			Array.prototype.forEach.call(titulo.childNodes, function (n) {
				if (n.nodeType === 1) { n.setAttribute('aria-hidden', 'true'); }
			});
			var palabras = ['actitud', 'fandom', 'estilo', 'pasión', 'anime', 'rock'];
			var i = 0;
			marca.classList.add('dfans-rotativa');
			setInterval(function () {
				marca.classList.add('is-saliendo');
				setTimeout(function () {
					i = (i + 1) % palabras.length;
					marca.textContent = palabras[i];
					marca.classList.remove('is-saliendo');
				}, 350);
			}, 2600);
		}

		// 2. Animaciones al hacer scroll.
		if (!reducir && !enVista && 'IntersectionObserver' in window) {
			var selectores = '.dfans-seccion-cabecera, .dfans-cat, .dfans-familia-card, .dfans-paso, .dfans-quiz, .dfans-destacado .wp-block-column, .dfans-seccion > h2, li.wc-block-product, .dfans-mini';
			var obs = new IntersectionObserver(function (entradas) {
				entradas.forEach(function (e) {
					if (e.isIntersecting) {
						e.target.classList.add('is-revelado');
						obs.unobserve(e.target);
					}
				});
			}, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
			document.documentElement.classList.add('dfans-anim');
			document.querySelectorAll(selectores).forEach(function (el) {
				var hermanos = el.parentElement ? Array.prototype.indexOf.call(el.parentElement.children, el) : 0;
				el.style.setProperty('--dfans-retraso', (hermanos % 4) * 80 + 'ms');
				el.classList.add('dfans-revelar');
				obs.observe(el);
			});
		}

		// 3. Cabecera compacta y botón "volver arriba".
		if (!enVista) {
			var arriba = document.createElement('button');
			arriba.type = 'button';
			arriba.className = 'dfans-arriba';
			arriba.setAttribute('aria-label', 'Volver arriba');
			arriba.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5l-7 7h4v7h6v-7h4z"/></svg>';
			arriba.addEventListener('click', function () { window.scrollTo({ top: 0, behavior: reducir ? 'auto' : 'smooth' }); });
			document.body.appendChild(arriba);
			var alHacerScroll = function () {
				document.body.classList.toggle('dfans-scroll', window.scrollY > 60);
				arriba.classList.toggle('is-visible', window.scrollY > 700);
			};
			window.addEventListener('scroll', alHacerScroll, { passive: true });
			alHacerScroll();
		}

		// 4. Favoritos.
		var FAV = 'dfans_favoritos';
		function pintarFavoritos() {
			var favs = leer(FAV);
			var ids = favs.map(function (f) { return String(f.id); });
			document.querySelectorAll('.dfans-fav').forEach(function (b) {
				var activo = ids.indexOf(String(b.dataset.id)) !== -1;
				b.classList.toggle('is-activo', activo);
				b.setAttribute('aria-pressed', activo ? 'true' : 'false');
			});
			document.querySelectorAll('.dfans-fav-num').forEach(function (n) {
				n.textContent = favs.length;
				n.hidden = favs.length === 0;
			});
		}
		function datosDeTarjeta(boton) {
			var card = boton.closest('li.wc-block-product');
			if (card) {
				var img = card.querySelector('.wc-block-components-product-image img:not(.dfans-img-alt)');
				return {
					id: boton.dataset.id,
					nombre: (card.querySelector('.wp-block-post-title') || {}).textContent || '',
					url: urlLimpia((card.querySelector('a[href]') || {}).href || '#'),
					img: img ? (img.currentSrc || img.src) : '',
					precio: textoPrecio(card.querySelector('.wc-block-components-product-price'))
				};
			}
			return datosDelProducto();
		}
		document.addEventListener('click', function (ev) {
			var boton = ev.target.closest('.dfans-fav');
			if (!boton) { return; }
			ev.preventDefault();
			var favs = leer(FAV);
			var id = String(boton.dataset.id);
			var ya = favs.findIndex(function (f) { return String(f.id) === id; });
			if (ya !== -1) {
				favs.splice(ya, 1);
				aviso('Quitado de tus favoritos');
			} else {
				var d = datosDeTarjeta(boton);
				d.nombre = d.nombre.trim();
				favs.unshift(d);
				aviso('Guardado en favoritos. <a href="<?php echo esc_url( home_url( '/favoritos/' ) ); ?>">Ver favoritos</a>');
				if (!reducir) {
					boton.classList.remove('is-pop');
					void boton.offsetWidth;
					boton.classList.add('is-pop');
				}
			}
			guardar(FAV, favs.slice(0, 60));
			pintarFavoritos();
			pintarListaFavoritos();
		});
		function pintarListaFavoritos() {
			var caja = document.querySelector('.dfans-favoritos');
			if (!caja) { return; }
			var favs = leer(FAV);
			caja.innerHTML = favs.length
				? '<div class="dfans-mini-grid">' + favs.map(function (p) { return miniTarjeta(p, true); }).join('') + '</div>'
				: '<div class="dfans-favoritos__vacio"><p>Aún no guardas polos. Toca el ♥ en cualquier polo para tenerlo aquí.</p><a class="dfans-boton" href="' + esc(caja.dataset.tienda) + '">Ir a la tienda</a></div>';
		}
		document.addEventListener('click', function (ev) {
			var q = ev.target.closest('.dfans-mini__quitar');
			if (!q) { return; }
			guardar(FAV, leer(FAV).filter(function (f) { return String(f.id) !== String(q.dataset.id); }));
			pintarFavoritos();
			pintarListaFavoritos();
		});
		pintarFavoritos();
		pintarListaFavoritos();

		// 5. Vista rápida.
		var modalVista;
		document.addEventListener('click', function (ev) {
			var b = ev.target.closest('.dfans-vista');
			if (!b) { return; }
			ev.preventDefault();
			if (!modalVista) {
				modalVista = document.createElement('dialog');
				modalVista.className = 'dfans-modal dfans-modal--vista';
				modalVista.setAttribute('aria-label', 'Vista rápida');
				modalVista.innerHTML = '<button type="button" class="dfans-modal__cerrar" aria-label="Cerrar">×</button><iframe title="Vista rápida del producto"></iframe>';
				document.body.appendChild(modalVista);
				modalVista.addEventListener('close', function () { modalVista.querySelector('iframe').src = 'about:blank'; });
			}
			modalVista.querySelector('iframe').src = b.dataset.url;
			modalVista.showModal();
		});
		window.addEventListener('message', function (ev) {
			if (ev.origin === location.origin && ev.data && ev.data.dfans === 'agregado') {
				aviso(esc(ev.data.texto.replace(/Ver carrito\s*$/, '')) + ' <a href="<?php echo esc_url( wc_get_cart_url() ); ?>">Ver carrito</a>');
			}
		});
		// Cerrar ventanas: botón × o clic fuera del contenido.
		document.addEventListener('click', function (ev) {
			var dlg = ev.target.closest('dialog.dfans-modal');
			if (!dlg) { return; }
			if (ev.target.closest('.dfans-modal__cerrar')) { dlg.close(); return; }
			if (ev.target === dlg) {
				var r = dlg.getBoundingClientRect();
				var fuera = ev.clientX < r.left || ev.clientX > r.right || ev.clientY < r.top || ev.clientY > r.bottom;
				if (fuera) { dlg.close(); }
			}
		});

		// 6. Quiz "¿Cuál es tu fandom?".
		var quiz = document.querySelector('.dfans-quiz');
		if (quiz) {
			var PREGUNTAS = [
				{ t: 'Un viernes por la noche prefieres…', o: [['🍜', 'Maratón de anime', 'anime'], ['🎸', 'Un concierto en vivo', 'rock'], ['🎮', 'Partida online con amigos', 'videojuegos'], ['🍿', 'Noche de películas y series', 'peliculas-y-series']] },
				{ t: '¿Qué no puede faltar en tu playlist?', o: [['🎤', 'Rap, trap o reggaetón', 'artistas'], ['🤘', 'Guitarras pesadas y batería a fondo', 'metal'], ['🎶', 'Openings de anime', 'anime'], ['📻', 'Clásicos del rock', 'rock']] },
				{ t: 'Si tuvieras un superpoder sería…', o: [['🔁', 'Reaparecer infinitas veces', 'videojuegos'], ['⏳', 'Viajar entre mundos y épocas', 'peliculas-y-series'], ['⚡', 'Hacer temblar un estadio', 'metal'], ['✨', 'Ponerle ritmo a todo', 'artistas']] }
			];
			var RESULTADOS = {
				'anime': ['Anime', 'Lo tuyo son los ninjas, los cazadores de demonios y las batallas épicas.', 'del'],
				'rock': ['Rock', 'Riffs legendarios y bandas que nunca pasan de moda.', 'del'],
				'metal': ['Metal', 'Volumen al máximo, cuernos arriba y actitud de escenario.', 'del'],
				'artistas': ['Rap y Trap', 'Rap, trap y reggaetón: tu estilo suena fuerte.', 'del'],
				'peliculas-y-series': ['Películas y Series', 'Maratones, mundos fantásticos y personajes que marcaron época.', 'de las'],
				'videojuegos': ['Videojuegos', 'Game on: respawn, level up y partidas hasta tarde.', 'de los']
			};
			var cuerpo = quiz.querySelector('.dfans-quiz__cuerpo');
			var paso = 0, puntos = {}, primero = {};
			var pintarPregunta = function () {
				var p = PREGUNTAS[paso];
				cuerpo.innerHTML = '<p class="dfans-quiz__paso">Pregunta ' + (paso + 1) + ' de ' + PREGUNTAS.length + '</p>'
					+ '<div class="dfans-quiz__barra"><span style="width:' + ((paso + 1) / PREGUNTAS.length * 100) + '%"></span></div>'
					+ '<h3 class="dfans-quiz__pregunta">' + esc(p.t) + '</h3><div class="dfans-quiz__opciones">'
					+ p.o.map(function (o) {
						return '<button type="button" class="dfans-quiz__opcion" data-t="' + o[2] + '"><span aria-hidden="true">' + o[0] + '</span>' + esc(o[1]) + '</button>';
					}).join('') + '</div>';
				var b = cuerpo.querySelector('.dfans-quiz__opcion');
				if (b && paso > 0) { b.focus(); }
			};
			var pintarResultado = function () {
				var mejor = Object.keys(puntos).sort(function (a, b) { return (puntos[b] - puntos[a]) || (primero[a] - primero[b]); })[0];
				var r = RESULTADOS[mejor];
				cuerpo.innerHTML = '<div class="dfans-quiz__resultado dfans-tema--' + mejor + '">'
					+ '<p class="dfans-quiz__eres">¡Eres fan ' + r[2] + '</p><p class="dfans-quiz__tema">' + esc(r[0]) + '!</p>'
					+ '<p>' + esc(r[1]) + '</p><div class="dfans-quiz__acciones">'
					+ '<a class="dfans-boton" href="' + quiz.dataset.adultos + '?tematica=' + mejor + '">Ver polos de adulto</a>'
					+ '<a class="dfans-boton dfans-boton--borde" href="' + quiz.dataset.ninos + '?tematica=' + mejor + '">Ver polos de niños</a>'
					+ '<button type="button" class="dfans-quiz__repetir">Repetir quiz</button></div></div>';
				cuerpo.querySelector('a').focus();
			};
			quiz.addEventListener('click', function (ev) {
				if (ev.target.closest('.dfans-quiz__empezar, .dfans-quiz__repetir')) {
					paso = 0; puntos = {}; primero = {};
					pintarPregunta();
					return;
				}
				var op = ev.target.closest('.dfans-quiz__opcion');
				if (!op) { return; }
				var t = op.dataset.t;
				puntos[t] = (puntos[t] || 0) + 1;
				if (primero[t] === undefined) { primero[t] = paso; }
				paso++;
				if (paso < PREGUNTAS.length) { pintarPregunta(); } else { pintarResultado(); }
			});
			// La primera pregunta se muestra de entrada (sin pantalla de "Empezar").
			pintarPregunta();
		}

		// 7. Ficha de producto.
		function datosDelProducto() {
			var form = document.querySelector('form.cart');
			var id = (form && (form.dataset.product_id || (form.querySelector('[name="add-to-cart"]') || {}).value)) || ((document.body.className.match(/postid-(\d+)/) || [])[1]);
			var img = document.querySelector('.woocommerce-product-gallery__image img');
			var titulo = document.querySelector('.single-product main h1');
			return {
				id: String(id || ''),
				nombre: titulo ? titulo.textContent.trim() : document.title,
				url: urlLimpia(location.href),
				img: img ? (img.getAttribute('data-src') || img.currentSrc || img.src) : '',
				precio: textoPrecio(document.querySelector('.wp-block-woocommerce-product-price, .summary .price'))
			};
		}
		if (document.body.classList.contains('single-product') && !enVista) {
			var actual = datosDelProducto();

			// Corazón junto al nombre del producto.
			var h1 = document.querySelector('.single-product main h1');
			if (h1 && actual.id) {
				var fav = document.createElement('button');
				fav.type = 'button';
				fav.className = 'dfans-fav dfans-fav--ficha';
				fav.dataset.id = actual.id;
				fav.setAttribute('aria-label', 'Guardar ' + actual.nombre + ' en favoritos');
				fav.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s-7.5-4.6-9.6-9.2C.9 8.4 3 4.5 6.7 4.5c2.1 0 3.6 1.2 4.3 2.4.7-1.2 2.2-2.4 4.3-2.4 3.7 0 5.8 3.9 4.3 7.3C19.5 16.4 12 21 12 21z"/></svg><span>Favorito</span>';
				h1.insertAdjacentElement('afterend', fav);
				pintarFavoritos();
			}

			// Vistos recientemente: se guarda este y se muestran los anteriores.
			if (actual.id) {
				var vistos = leer('dfans_vistos').filter(function (v) { return String(v.id) !== actual.id; });
				mostrarVistos(vistos);
				vistos.unshift(actual);
				guardar('dfans_vistos', vistos.slice(0, 12));
			}

			// Barra fija de compra en móvil.
			var formCompra = document.querySelector('form.cart');
			if (formCompra && 'IntersectionObserver' in window) {
				var barra = document.createElement('div');
				barra.className = 'dfans-barra-compra';
				barra.innerHTML = '<div class="dfans-barra-compra__info"><strong>' + esc(actual.nombre) + '</strong><span class="dfans-barra-compra__precio">' + esc(actual.precio) + '</span></div><button type="button" class="dfans-barra-compra__boton">Elegir talla</button>';
				document.body.appendChild(barra);
				var botonBarra = barra.querySelector('button');
				var variacion = formCompra.querySelector('input.variation_id');
				var elegido = function () { return !variacion || (variacion.value && variacion.value !== '0'); };
				var actualizarBoton = function () { botonBarra.textContent = elegido() ? 'Añadir al carrito' : 'Elegir talla'; };
				botonBarra.addEventListener('click', function () {
					if (elegido()) {
						formCompra.querySelector('.single_add_to_cart_button').click();
					} else {
						formCompra.scrollIntoView({ behavior: reducir ? 'auto' : 'smooth', block: 'center' });
						var primera = formCompra.querySelector('.dfans-opcion:not(:disabled)');
						if (primera) { setTimeout(function () { primera.focus(); }, 400); }
					}
				});
				if (window.jQuery) {
					jQuery(formCompra).on('found_variation', function (e, v) {
						var tmp = document.createElement('div');
						tmp.innerHTML = v.price_html || '';
						barra.querySelector('.dfans-barra-compra__precio').textContent = textoPrecio(tmp) || actual.precio;
						setTimeout(actualizarBoton, 0);
					}).on('reset_data', function () {
						barra.querySelector('.dfans-barra-compra__precio').textContent = actual.precio;
						setTimeout(actualizarBoton, 0);
					});
				}
				new IntersectionObserver(function (e) {
					barra.classList.toggle('is-visible', !e[0].isIntersecting && e[0].boundingClientRect.top < 0);
				}).observe(formCompra);
			}

		}

		// Guía de tallas junto a "Talla" (también dentro de la vista rápida).
		var guia = document.getElementById('dfans-guia');
		var etiquetaTalla = document.querySelector('.variations label[for="pa_talla"]');
		if (guia && etiquetaTalla) {
			var bGuia = document.createElement('button');
			bGuia.type = 'button';
			bGuia.className = 'dfans-guia__boton';
			bGuia.textContent = 'Guía de tallas';
			bGuia.addEventListener('click', function () { guia.showModal(); });
			etiquetaTalla.insertAdjacentElement('afterend', bGuia);
			// Enlace directo a la guía: /producto/...#guia-tallas
			if (location.hash === '#guia-tallas') { guia.showModal(); }
		}

		// 8. Vistos recientemente (ficha de producto y carrito).
		function mostrarVistos(lista) {
			lista = lista.slice(0, 6);
			var main = document.querySelector('main');
			if (!lista.length || !main || enVista) { return; }
			var s = document.createElement('section');
			s.className = 'dfans-vistos alignwide';
			s.setAttribute('aria-label', 'Vistos recientemente');
			s.innerHTML = '<h2>Vistos recientemente</h2><div class="dfans-mini-grid">' + lista.map(function (p) { return miniTarjeta(p, false); }).join('') + '</div>';
			main.appendChild(s);
		}
		if (document.body.classList.contains('woocommerce-cart')) {
			mostrarVistos(leer('dfans_vistos'));
		}
	})();
	</script>
	<?php
}, 30 );

// Tallas agotadas: WooCommerce las marca como no disponibles y los botones se ven tachados.
add_filter( 'woocommerce_variation_is_active', function ( $activa, $variacion ) {
	return $activa && $variacion->is_in_stock();
}, 10, 2 );

