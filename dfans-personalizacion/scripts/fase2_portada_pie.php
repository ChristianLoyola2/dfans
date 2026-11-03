<?php
/**
 * D'FANS - Portada y pie al día con Match y exclusivos (02/10/2026)
 * Reemplazos exactos de texto y enlaces en la página de Inicio (60) y el pie (parte 112).
 * Cada texto a cambiar debe aparecer una sola vez; si no, no se toca nada. Copias en backups/fase2/.
 * Uso: php fase2_portada_pie.php [--apply]
 */
define( 'WP_USE_THEMES', false );
$_SERVER['HTTP_HOST']   = 'dfans.local';
$_SERVER['REQUEST_URI'] = '/';
require 'C:/Users/GRLL/Local Sites/dfans/app/public/wp-load.php';
wp_set_current_user( 1 );

$apply = in_array( '--apply', $argv, true );
$copia = 'C:/Users/GRLL/Local Sites/dfans/backups/fase2/';

// Enlaces de temáticas: de la subcategoría de adultos a la sección Adultos con el filtro.
$tematicas = array();
foreach ( array( 'anime', 'rock', 'metal', 'artistas', 'peliculas-y-series', 'videojuegos' ) as $t ) {
	$tematicas[ 'href="http://dfans.local/product-category/ropa-de-adultos/' . $t . '/"' ] = 'href="/product-category/ropa-de-adultos/?tematica=' . $t . '"';
}

$cambios = array(
	60  => array_merge(
		array(
			'<p class="dfans-hero-texto">Polos con diseños de anime, rock, metal, artistas, películas y videojuegos. Para adultos y niños.</p>'
				=> '<p class="dfans-hero-texto">Polos con diseños de anime, rock, metal, artistas, películas y videojuegos. El mismo diseño para toda la familia o exclusivos para él, ella, niño y niña.</p>',
			'href="http://dfans.local/product-category/ropa-para-ninos/">Ropa para niños</a>'
				=> 'href="/product-category/match-familiar/">Match familiar</a>',
			'<p>Modelos Hombre y Mujer · Tallas S, M, L, XL y XXL</p>'
				=> '<p>Colección Match unisex (S a XXL) y exclusivos: oversize para él y baby tee para ella (XS a L)</p>',
			'<p>Modelos Niño y Niña · Tallas 10, 12 y 14</p>'
				=> '<p>Colección Match unisex y exclusivos: polo reforzado para niño y manga mariposa para niña · Tallas 10, 12 y 14</p>',
			'<p class="dfans-precio">S/ 30</p>'
				=> '<p class="dfans-precio">Desde S/ 30</p>',
			'<p class="dfans-eyebrow">Diseño destacado</p>'
				=> '<p class="dfans-eyebrow">Colección Match · Diseño destacado</p>',
			'<p>Un mismo diseño, cuatro modelos. Elige Hombre, Mujer, Niño o Niña y mira el polo tal como te quedará, de frente y de espalda.</p>'
				=> '<p>Un mismo diseño para toda la familia: tallas de adulto unisex y tallas de niños. Míralo puesto en hombre, mujer, niño y niña, de frente y de espalda.</p>',
			'<p>Explora por categoría: anime, rock, metal, artistas, películas y videojuegos.</p>'
				=> '<p>Entra a Match familiar, Adultos o Niños y filtra por temática: anime, rock, metal, artistas, películas y videojuegos.</p>',
			'<h3 class="wp-block-heading">Modelo y talla</h3>'
				=> '<h3 class="wp-block-heading">Elige tu talla</h3>',
			'<p>Hombre, Mujer, Niño o Niña. Solo verás las tallas que corresponden a cada modelo.</p>'
				=> '<p>Adultos de XS a XXL y niños de 10 a 14. En cada sección solo verás las tallas que te corresponden.</p>',
		),
		$tematicas
	),
	112 => array_merge(
		array(
			"<li><a href=\"http://dfans.local/shop/\">Toda la tienda</a></li>\n<!-- /wp:list-item -->"
				=> "<li><a href=\"http://dfans.local/shop/\">Toda la tienda</a></li>\n<!-- /wp:list-item -->\n\n<!-- wp:list-item -->\n<li><a href=\"/product-category/match-familiar/\">Match familiar</a></li>\n<!-- /wp:list-item -->",
		),
		$tematicas
	),
);

$nuevos = array();
$error  = false;
foreach ( $cambios as $id => $pares ) {
	$contenido = get_post( $id )->post_content;
	foreach ( $pares as $viejo => $nuevo ) {
		$n = substr_count( $contenido, $viejo );
		if ( 1 !== $n ) {
			echo "#$id: aparece $n veces -> " . mb_substr( $viejo, 0, 70 ) . "\n";
			$error = true;
		}
	}
	$nuevos[ $id ] = strtr( $contenido, $pares );
	echo "#$id: " . count( $pares ) . " cambios preparados.\n";
}
if ( $error ) {
	exit( "Hay textos que no coinciden: no se aplicó nada.\n" );
}
if ( ! $apply ) {
	exit( "Modo revisión: no se aplicó nada.\n" );
}
wp_mkdir_p( $copia );
foreach ( $nuevos as $id => $contenido ) {
	file_put_contents( $copia . "post-$id.ANTES-PORTADA-PIE.html", get_post( $id )->post_content );
	wp_update_post( array( 'ID' => $id, 'post_content' => wp_slash( $contenido ) ) );
	echo "#$id actualizado.\n";
}
