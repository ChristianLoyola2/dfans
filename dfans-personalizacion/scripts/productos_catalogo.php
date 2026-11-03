<?php
/**
 * D'FANS - Publica los polos de Rock, Metal, Artistas, Películas y Series y Videojuegos.
 * Cada diseño tiene una sola foto (polo en percha), usada como imagen principal.
 * Las variaciones Modelo + Talla heredan esa imagen hasta tener fotos por modelo.
 *
 * Uso: php productos_catalogo.php [--apply]
 */
define( 'WP_USE_THEMES', false );
$_SERVER['HTTP_HOST']   = 'dfans.local';
$_SERVER['REQUEST_URI'] = '/';
require 'C:/Users/GRLL/Local Sites/dfans/app/public/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

wp_set_current_user( 1 );
$apply    = in_array( '--apply', $argv, true );
$recortes = __DIR__ . '/recortes/';

const STOCK = 20;

/*
 * Categoría => [padre adultos, categoría niños, prefijo de imagen, sku, productos].
 * Producto: [título, nombre de subcategoría, slug, descripción].
 * El orden de cada lista sigue el orden de los archivos en la carpeta.
 */
$catalogo = array(
	'Rock' => array(
		16, 69, 'bandas', 'ROCK',
		array(
			array( 'Polo Guns N\' Roses', 'Guns N\' Roses', 'guns-n-roses', 'El logo clásico de Guns N\' Roses con las rosas y el lema Appetite for Destruction.' ),
			array( 'Polo AC/DC', 'AC/DC', 'acdc', 'Angus Young con su guitarra y el clásico Highway to Hell de AC/DC.' ),
			array( 'Polo Nirvana', 'Nirvana', 'nirvana', 'La carita sonriente de Nirvana con el lema Come as You Are.' ),
			array( 'Polo Pink Floyd', 'Pink Floyd', 'pink-floyd', 'El prisma de The Dark Side of the Moon con un paisaje cósmico de Pink Floyd.' ),
			array( 'Polo Queen', 'Queen', 'queen', 'El emblema real de Queen con el fénix y los leones.' ),
			array( 'Polo The Beatles', 'The Beatles', 'the-beatles', 'The Beatles cruzando Abbey Road con el lema All You Need Is Love.' ),
		),
	),
	'Metal' => array(
		17, 70, 'metal', 'METAL',
		array(
			array( 'Polo Iron Maiden', 'Iron Maiden', 'iron-maiden', 'Eddie con la bandera británica y The Number of the Beast de Iron Maiden.' ),
			array( 'Polo Black Sabbath', 'Black Sabbath', 'black-sabbath', 'La figura encapuchada y la cruz del clásico de Black Sabbath.' ),
			array( 'Polo Metallica', 'Metallica', 'metallica', 'La calavera y la balanza de ...And Justice for All de Metallica.' ),
			array( 'Polo Megadeth', 'Megadeth', 'megadeth', 'Vic Rattlehead y el lema Peace Sells... but Who\'s Buying? de Megadeth.' ),
			array( 'Polo Slipknot', 'Slipknot', 'slipknot', 'Los integrantes enmascarados de Slipknot con su logo en rojo.' ),
			array( 'Polo Motörhead', 'Motörhead', 'motorhead', 'El Snaggletooth de Motörhead con el lema Ace of Spades.' ),
		),
	),
	'Artistas' => array(
		21, 71, 'artistas', 'ART',
		array(
			array( 'Polo Bad Bunny', 'Bad Bunny', 'bad-bunny', 'Collage de Bad Bunny con sus frases y portadas más conocidas.' ),
			array( 'Polo Eminem', 'Eminem', 'eminem', 'Eminem y Slim Shady en un diseño con frases de sus canciones.' ),
			array( 'Polo 2Pac', 'Tupac', 'tupac', 'Tupac Shakur con los lemas Thug Life y All Eyez on Me.' ),
			array( 'Polo Travis Scott', 'Travis Scott', 'travis-scott', 'Travis Scott con referencias a Astroworld y Utopia.' ),
			array( 'Polo Snoop Dogg', 'Snoop Dogg', 'snoop-dogg', 'Snoop Dogg con un lowrider y el estilo de la West Coast.' ),
			array( 'Polo J Álvarez', 'J Álvarez', 'j-alvarez', 'J Álvarez, El Dueño del Sistema, con sus frases más conocidas.' ),
		),
	),
	'Películas y Series' => array(
		20, 72, 'peliculas_y_series', 'PYS',
		array(
			array( 'Polo Stranger Things', 'Stranger Things', 'stranger-things', 'Los protagonistas de Stranger Things con el Upside Down de Hawkins.' ),
			array( 'Polo Breaking Bad', 'Breaking Bad', 'breaking-bad', 'Walter White, Heisenberg y Jesse con la caravana de Breaking Bad.' ),
			array( 'Polo Prison Break', 'Prison Break', 'prison-break', 'Michael y Lincoln con la prisión de Fox River de Prison Break.' ),
			array( 'Polo The Last of Us', 'The Last of Us', 'the-last-of-us', 'Joel y Ellie en la ciudad abandonada de The Last of Us.' ),
			array( 'Polo Game of Thrones', 'Game of Thrones', 'game-of-thrones', 'Daenerys, Jon Snow, Tyrion y Cersei con el Trono de Hierro.' ),
			array( 'Polo El Señor de los Anillos', 'El Señor de los Anillos', 'senor-de-los-anillos', 'Gandalf, Frodo y la Comunidad del Anillo camino a Mordor.' ),
		),
	),
	'Videojuegos' => array(
		19, 73, 'videojuegos', 'VJ',
		array(
			array( 'Polo Dota 2', 'Dota 2', 'dota-2', 'Los Ancestros Radiant y Dire frente a frente en un diseño de Dota 2.' ),
			array( 'Polo Fortnite', 'Fortnite', 'fortnite', 'Los personajes de Fortnite con el Battle Bus y el lema Victory Royale.' ),
			array( 'Polo Free Fire', 'Free Fire', 'free-fire', 'Los personajes de Free Fire en plena batalla, con el grito ¡Booyah!' ),
			array( 'Polo GTA V', 'GTA', 'gta', 'Los protagonistas de Grand Theft Auto V con Los Santos de fondo.' ),
			array( 'Polo League of Legends', 'League of Legends', 'league-of-legends', 'Campeones de League of Legends en la Grieta del Invocador.' ),
			array( 'Polo Minecraft', 'Minecraft', 'minecraft', 'Steve con su espada de diamante y los mobs de Minecraft.' ),
		),
	),
);

$modelos = array(
	array( 'hombre', array( 's', 'm', 'l', 'xl', 'xxl' ), 'H' ),
	array( 'mujer', array( 's', 'm', 'l', 'xl', 'xxl' ), 'M' ),
	array( 'nino', array( '10', '12', '14' ), 'NO' ),
	array( 'nina', array( '10', '12', '14' ), 'NA' ),
);

function dfans_precio( $talla ) {
	if ( in_array( $talla, array( '10', '12', '14' ), true ) ) {
		return '30';
	}
	return in_array( $talla, array( 'xl', 'xxl' ), true ) ? '40' : '35';
}

/** Devuelve la subcategoría del diseño bajo su categoría adulta, creándola si no existe. */
function dfans_subcategoria( $nombre, $slug, $padre, $apply ) {
	$t = get_term_by( 'slug', $slug, 'product_cat' );
	if ( $t ) {
		return array( $t->term_id, 'existe' );
	}
	if ( ! $apply ) {
		return array( 0, 'se creará' );
	}
	$r = wp_insert_term( $nombre, 'product_cat', array( 'slug' => $slug, 'parent' => $padre ) );
	if ( is_wp_error( $r ) ) {
		exit( "ERROR creando categoría $nombre: " . $r->get_error_message() . "\n" );
	}
	return array( $r['term_id'], 'creada' );
}

function dfans_subir( $origen, $nombre, $titulo, $post_id ) {
	$tmp = wp_tempnam( $nombre );
	copy( $origen, $tmp );
	$id = media_handle_sideload( array( 'name' => "$nombre.jpg", 'tmp_name' => $tmp ), $post_id, $titulo );
	if ( is_wp_error( $id ) ) {
		@unlink( $tmp );
		exit( "ERROR subiendo $origen: " . $id->get_error_message() . "\n" );
	}
	update_post_meta( $id, '_wp_attachment_image_alt', $titulo );
	return $id;
}

$attr_modelo = new WC_Product_Attribute();
$attr_modelo->set_id( wc_attribute_taxonomy_id_by_name( 'pa_modelo' ) );
$attr_modelo->set_name( 'pa_modelo' );
$attr_modelo->set_options( array( 74, 75, 76, 77 ) );
$attr_modelo->set_position( 0 );
$attr_modelo->set_visible( true );
$attr_modelo->set_variation( true );

$attr_talla = new WC_Product_Attribute();
$attr_talla->set_id( wc_attribute_taxonomy_id_by_name( 'pa_talla' ) );
$attr_talla->set_name( 'pa_talla' );
$attr_talla->set_options( array( 24, 25, 26, 27, 28, 64, 63, 62 ) );
$attr_talla->set_position( 1 );
$attr_talla->set_visible( true );
$attr_talla->set_variation( true );

$orden = 6; // Los 6 polos de Anime ocupan del 1 al 6.

foreach ( $catalogo as $categoria => list( $cat_adulto, $cat_nino, $prefijo, $sku_cat, $productos ) ) {
	echo "== $categoria\n";
	foreach ( $productos as $i => list( $titulo, $sub_nombre, $sub_slug, $texto ) ) {
		$orden++;
		$img = $recortes . $prefijo . '_' . ( $i + 1 ) . '.jpg';
		if ( ! file_exists( $img ) ) {
			exit( "FALTA imagen: $img\n" );
		}
		list( $sub_id, $sub_estado ) = dfans_subcategoria( $sub_nombre, $sub_slug, $cat_adulto, $apply );

		$existe = get_posts( array( 'post_type' => 'product', 'title' => $titulo, 'post_status' => array( 'publish', 'draft', 'pending', 'private' ), 'fields' => 'ids' ) );
		if ( $existe ) {
			echo "  $titulo: YA EXISTE (ID {$existe[0]}), se omite\n";
			continue;
		}
		if ( ! $apply ) {
			echo "  $titulo  <- " . basename( $img ) . "  | subcategoría $sub_nombre ($sub_estado) | orden $orden\n";
			continue;
		}

		$diseno = str_replace( 'Polo ', '', $titulo );
		$prod   = new WC_Product_Variable();
		$prod->set_name( $titulo );
		$prod->set_status( 'draft' );
		$prod->set_menu_order( $orden );
		$prod->set_short_description( "Polo negro D’FANS con diseño de $diseno. Disponible en modelos Hombre, Mujer, Niño y Niña." );
		$prod->set_description( "$texto\n\n<strong>Modelos:</strong> Hombre, Mujer, Niño y Niña.\n<strong>Tallas adultos:</strong> S, M, L, XL y XXL.\n<strong>Tallas niños:</strong> 10, 12 y 14." );
		$prod->set_category_ids( array( $cat_adulto, $sub_id, 65, 66, $cat_nino ) );
		$prod->set_attributes( array( $attr_modelo, $attr_talla ) );
		$prod->set_manage_stock( false );
		$prod->set_tax_status( 'taxable' );
		$pid = $prod->save();

		$slug = sanitize_title( $titulo );
		$prod->set_image_id( dfans_subir( $img, $slug, $titulo, $pid ) );
		$prod->save();

		$n     = 0;
		$clave = strtoupper( preg_replace( '/[^a-z0-9]/', '', sanitize_title( $diseno ) ) );
		foreach ( $modelos as list( $modelo, $tallas, $sku_m ) ) {
			foreach ( $tallas as $talla ) {
				$v = new WC_Product_Variation();
				$v->set_parent_id( $pid );
				$v->set_attributes( array( 'pa_modelo' => $modelo, 'pa_talla' => $talla ) );
				$v->set_regular_price( dfans_precio( $talla ) );
				$v->set_manage_stock( true );
				$v->set_stock_quantity( STOCK );
				$v->set_stock_status( 'instock' );
				$v->set_sku( "DF-$clave-$sku_m-" . strtoupper( $talla ) );
				$v->set_menu_order( ++$n );
				$v->save();
			}
		}

		WC_Product_Variable::sync( $pid );
		$prod = wc_get_product( $pid );
		$prod->set_status( 'publish' );
		$prod->save();
		echo "  $titulo: ID $pid, " . count( $prod->get_children() ) . " variaciones, subcategoría $sub_nombre ($sub_estado)\n";
	}
}

if ( $apply ) {
	wc_delete_product_transients();
	wc_recount_all_terms();
	echo "Listo.\n";
} else {
	echo "Modo revisión: no se aplicó nada.\n";
}
