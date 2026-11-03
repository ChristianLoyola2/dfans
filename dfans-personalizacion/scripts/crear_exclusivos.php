<?php
/**
 * D'FANS - Fase 2: líneas exclusivas por público (01/10/2026)
 * Crea 16 polos con corte propio y diseño original D'FANS:
 *  - Hombre: Oversize, S-XXL, S/45.     - Mujer: Baby tee (crop), XS-L, S/40.
 *  - Niño: Regular reforzado, 10-14, S/32. - Niña: Manga mariposa, 10-14, S/35.
 * Imágenes en backups/generadas/exclusivos/<prefijo>-{modelo,modelo-espalda,frente,espalda}.png.
 * No son Match: van a Adultos o Niños + temática, con "Para" (pa_modelo) y "Corte" (pa_corte).
 * Uso: php crear_exclusivos.php [--lote=2] [--apply]
 *   --lote=2: 32 polos más (01/10/2026), lista en exclusivos-lote2.php, fotos en generadas/exclusivos2/ (solo con modelo).
 */
define( 'WP_USE_THEMES', false );
$_SERVER['HTTP_HOST']   = 'dfans.local';
$_SERVER['REQUEST_URI'] = '/';
require 'C:/Users/GRLL/Local Sites/dfans/app/public/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
wp_set_current_user( 1 );

$apply = in_array( '--apply', $argv, true );
$dir   = 'C:/Users/GRLL/Local Sites/dfans/backups/generadas/exclusivos/';

$lineas = array(
	'hombre' => array( 'corte' => 'Oversize', 'precio' => 45, 'tallas' => array( 's', 'm', 'l', 'xl', 'xxl' ), 'seccion' => 'ropa-de-adultos', 'sku' => 'H',
		'texto' => 'Corte oversize para hombre: hombros caídos, manga ancha hasta el codo, largo extra y cuello grueso acanalado.' ),
	'mujer'  => array( 'corte' => 'Baby tee (crop)', 'precio' => 40, 'tallas' => array( 'xs', 's', 'm', 'l' ), 'seccion' => 'ropa-de-adultos', 'sku' => 'M',
		'texto' => 'Baby tee para mujer: corte entallado a la cintura, largo crop, manga corta ajustada y cuello fino.' ),
	'nino'   => array( 'corte' => 'Regular reforzado', 'precio' => 32, 'tallas' => array( '10', '12', '14' ), 'seccion' => 'ropa-para-ninos', 'sku' => 'NO',
		'texto' => 'Corte regular para niño: holgado para jugar, con cuello y bastas de doble costura que aguantan el uso diario.' ),
	'nina'   => array( 'corte' => 'Manga mariposa', 'precio' => 35, 'tallas' => array( '10', '12', '14' ), 'seccion' => 'ropa-para-ninos', 'sku' => 'NA',
		'texto' => 'Polo para niña con manga mariposa (con vuelo), cintura ligeramente entallada y cuello redondo suave.' ),
);

// prefijo de archivos => [linea, nombre, temática, código SKU, descripción del diseño]
$productos = array(
	'h-maldicion'         => array( 'hombre', 'Polo Oversize Maldición', 'anime', 'MALDICION', 'Hechicero de pelo plateado con máscara oni rota y llamas carmesí; en la espalda, el kanji 呪 (maldición) gigante. Estampado en relieve (puff).' ),
	'h-metal-tour'        => array( 'hombre', 'Polo Oversize Metal Tour 86', 'metal', 'METALTOUR', 'Guitarrista esqueleto con guitarra flying-V y rayos, estilo polo de gira de los 80; en la espalda, la gira mundial "World Tour 86". Estampado vintage craquelado.' ),
	'h-street-poetry'     => array( 'hombre', 'Polo Oversize Street Poetry', 'artistas', 'STREET', 'Collage rap de los 90: letras cromadas, micrófono, boombox, cadena de oro y llamas moradas; en la espalda, el tag D\'FANS con corona.' ),
	'h-respawn'           => array( 'hombre', 'Polo Oversize Respawn', 'videojuegos', 'RESPAWN', 'Pixel art de 8 bits: control, barra de vida y "GG RESPAWN" en neón; en la espalda, "Player 1 · Press Start".' ),
	'm-sakura-blade'      => array( 'mujer', 'Baby Tee Sakura Blade', 'anime', 'SAKURA', 'Chica anime con katana entre pétalos de cerezo, en línea fina roja y rosada; en la espalda, una rama de cerezo con el kanji 桜.' ),
	'm-kpop-stan'         => array( 'mujer', 'Baby Tee Stan Forever', 'artistas', 'STAN', 'Para fans del K-pop: "Stan Forever" con lightsticks de corazón en rosa y lila; en la espalda, "Fan Club D\'FANS".' ),
	'm-dark-kawaii'       => array( 'mujer', 'Baby Tee Dark Kawaii', 'anime', 'KAWAII', 'Conejita kawaii con alas de murciélago y moño de calavera en rosa pastel y lila; en la espalda, alitas y "Stay cute, stay dark".' ),
	'm-rock-roses'        => array( 'mujer', 'Baby Tee Rock & Roses', 'rock', 'ROSES', 'Rosa roja enredada en una guitarra eléctrica, en línea fina estilo tatuaje; en la espalda, ramo de rosas y "born to rock".' ),
	'no-glow-block'       => array( 'nino', 'Polo Niño Glow Block', 'videojuegos', 'GLOW', 'Monstruo de bloques pixelado en tinta verde neón que brilla en la oscuridad; en la espalda, un gran número 10 de bloques.' ),
	'no-ki-blast'         => array( 'nino', 'Polo Niño Ki Blast', 'anime', 'KIBLAST', 'Pequeño ninja con vincha roja lanzando una bola de energía; en la espalda, "Ki Power" con la vincha al viento.' ),
	'no-level-up'         => array( 'nino', 'Polo Niño Level Up', 'videojuegos', 'LEVELUP', 'Héroe gamer de bloques con jetpack y espada, "Level Up!"; en la espalda, "Player 1" y la barra de experiencia.' ),
	'no-turbo-fox'        => array( 'nino', 'Polo Niño Turbo', 'videojuegos', 'TURBO', 'Zorro veloz con zapatillas rojas y rayos azules; en la espalda, número 10 estilo camiseta de carreras.' ),
	'na-mini-espia'       => array( 'nina', 'Polo Niña Mini Espía', 'anime', 'ESPIA', 'Pequeña detective con boina roja y lupa de corazón, con detalles en glitter; en la espalda, "Top Secret".' ),
	'na-galaxy-kitty'     => array( 'nina', 'Polo Niña Galaxy Kitty', 'anime', 'KITTY', 'Gatita astronauta entre estrellas y planetas con glitter plateado; en la espalda, "To the stars".' ),
	'na-zorrito-magico'   => array( 'nina', 'Polo Niña Zorrito Mágico', 'anime', 'ZORRITO', 'Zorrito con cola de cristal en colores arcoíris y glitter; en la espalda, la cola mágica en espiral.' ),
	'na-espiritu-bosque'  => array( 'nina', 'Polo Niña Espíritu del Bosque', 'peliculas-y-series', 'BOSQUE', 'Espíritu del bosque con paraguas de hoja, flores y luciérnagas con glitter dorado; en la espalda, una guirnalda de hojas.' ),
);

// Fotos de cada polo: lote 1 con 4 (modelo y percha); lote 2 solo con modelo, frente y espalda.
$fotos = array( 'modelo' => 'con modelo', 'modelo-espalda' => 'con modelo, espalda', 'frente' => 'delantera', 'espalda' => 'espalda' );
if ( in_array( '--lote=2', $argv, true ) ) {
	$dir       = 'C:/Users/GRLL/Local Sites/dfans/backups/generadas/exclusivos2/';
	$productos = require __DIR__ . '/exclusivos-lote2.php';
	$fotos     = array_slice( $fotos, 0, 2, true );
}

// Revisión: archivos y categorías.
$faltan = array();
foreach ( $productos as $pre => $p ) {
	foreach ( array_keys( $fotos ) as $f ) {
		if ( ! file_exists( "$dir$pre-$f.png" ) ) {
			$faltan[] = "$pre-$f.png";
		}
	}
	$cat = 'ropa-para-ninos' === $lineas[ $p[0] ]['seccion'] ? $p[2] . '-ninos' : $p[2];
	if ( ! get_term_by( 'slug', $cat, 'product_cat' ) ) {
		$faltan[] = "categoría $cat";
	}
}
echo count( $productos ), ' productos. ', $faltan ? 'Faltan: ' . implode( ', ', $faltan ) : 'Todo listo.', "\n";
if ( ! $apply || $faltan ) {
	exit( $apply ? "No se aplicó nada.\n" : "Modo revisión: no se aplicó nada.\n" );
}

// Talla XS (antes de S).
if ( ! term_exists( 'xs', 'pa_talla' ) ) {
	$t = wp_insert_term( 'XS', 'pa_talla', array( 'slug' => 'xs' ) );
	update_term_meta( $t['term_id'], 'order', -1 );
}

// Atributo "Corte".
if ( ! taxonomy_exists( 'pa_corte' ) ) {
	wc_create_attribute( array( 'name' => 'Corte', 'slug' => 'corte', 'type' => 'select', 'order_by' => 'menu_order', 'has_archives' => false ) );
	register_taxonomy( 'pa_corte', array( 'product' ), array( 'hierarchical' => false ) );
}
foreach ( $lineas as $l ) {
	if ( ! term_exists( sanitize_title( $l['corte'] ), 'pa_corte' ) ) {
		wp_insert_term( $l['corte'], 'pa_corte', array( 'slug' => sanitize_title( $l['corte'] ) ) );
	}
}

/** Convierte a JPG, sube a la biblioteca y devuelve el ID. */
function dfans_subir( $png, $nombre, $titulo, $post_id ) {
	$tmp = wp_tempnam( $nombre ) . '.jpg';
	$im  = imagecreatefrompng( $png );
	imagejpeg( $im, $tmp, 90 );
	imagedestroy( $im );
	$id = media_handle_sideload( array( 'name' => "$nombre.jpg", 'tmp_name' => $tmp ), $post_id, $titulo );
	if ( is_wp_error( $id ) ) {
		exit( "ERROR subiendo $png: " . $id->get_error_message() . "\n" );
	}
	update_post_meta( $id, '_wp_attachment_image_alt', $titulo );
	return $id;
}

foreach ( $productos as $pre => list( $linea, $nombre, $tematica, $codigo, $diseno ) ) {
	$l    = $lineas[ $linea ];
	$slug = sanitize_title( $nombre );
	if ( get_page_by_path( $slug, OBJECT, 'product' ) ) {
		echo "$nombre ya existe, se omite.\n";
		continue;
	}
	$cats = array(
		get_term_by( 'slug', $l['seccion'], 'product_cat' )->term_id,
		get_term_by( 'slug', 'ropa-para-ninos' === $l['seccion'] ? "$tematica-ninos" : $tematica, 'product_cat' )->term_id,
	);
	$tallas_txt = strtoupper( implode( ', ', $l['tallas'] ) );

	$prod = new WC_Product_Variable();
	$prod->set_name( $nombre );
	$prod->set_slug( $slug );
	$prod->set_status( 'publish' );
	$prod->set_short_description( "Polo negro D’FANS de diseño exclusivo. Corte: {$l['corte']}. Tallas: $tallas_txt." );
	$prod->set_description( "$diseno\n\n<strong>Corte:</strong> {$l['texto']}\n<strong>Tallas:</strong> $tallas_txt.\n<strong>Diseño:</strong> original D’FANS, exclusivo de esta línea (no es parte de la Colección Match)." );
	$prod->set_category_ids( $cats );

	$atr_talla = new WC_Product_Attribute();
	$atr_talla->set_id( wc_attribute_taxonomy_id_by_name( 'pa_talla' ) );
	$atr_talla->set_name( 'pa_talla' );
	$atr_talla->set_options( array_map( fn( $t ) => get_term_by( 'slug', $t, 'pa_talla' )->term_id, $l['tallas'] ) );
	$atr_talla->set_position( 0 );
	$atr_talla->set_visible( true );
	$atr_talla->set_variation( true );

	$atr_corte = new WC_Product_Attribute();
	$atr_corte->set_id( wc_attribute_taxonomy_id_by_name( 'pa_corte' ) );
	$atr_corte->set_name( 'pa_corte' );
	$atr_corte->set_options( array( get_term_by( 'slug', sanitize_title( $l['corte'] ), 'pa_corte' )->term_id ) );
	$atr_corte->set_position( 1 );
	$atr_corte->set_visible( true );
	$atr_corte->set_variation( false );

	$atr_para = new WC_Product_Attribute();
	$atr_para->set_id( wc_attribute_taxonomy_id_by_name( 'pa_modelo' ) );
	$atr_para->set_name( 'pa_modelo' );
	$atr_para->set_options( array( get_term_by( 'slug', $linea, 'pa_modelo' )->term_id ) );
	$atr_para->set_position( 2 );
	$atr_para->set_visible( false );
	$atr_para->set_variation( false );

	$prod->set_attributes( array( $atr_talla, $atr_corte, $atr_para ) );
	$pid = $prod->save();

	// Imágenes: foto con modelo como principal; espalda con modelo y fotos en percha en la galería.
	$img = array();
	foreach ( $fotos as $f => $t ) {
		$img[ $f ] = dfans_subir( "$dir$pre-$f.png", "$slug-$f", "$nombre ($t)", $pid );
	}
	$prod = wc_get_product( $pid );
	$prod->set_image_id( $img['modelo'] );
	$prod->set_gallery_image_ids( array_values( array_diff_key( $img, array( 'modelo' => 0 ) ) ) );
	$prod->save();

	foreach ( $l['tallas'] as $i => $t ) {
		$v = new WC_Product_Variation();
		$v->set_parent_id( $pid );
		$v->set_attributes( array( 'pa_talla' => $t ) );
		$v->set_regular_price( $l['precio'] );
		$v->set_sku( 'DF-EX-' . $codigo . '-' . strtoupper( $t ) );
		$v->set_manage_stock( true );
		$v->set_stock_quantity( 20 );
		$v->set_image_id( $img['modelo'] );
		$v->set_menu_order( $i );
		$v->save();
	}
	WC_Product_Variable::sync( $pid );
	wc_delete_product_transients( $pid );
	$p = wc_get_product( $pid );
	echo "$pid $nombre: ", count( $p->get_children() ), ' tallas, ', html_entity_decode( strip_tags( wc_price( $l['precio'] ) ) ), "\n";
}
