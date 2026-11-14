<?php
/**
 * D'FANS - Página privada "Opciones de portada" (13/11/2026) para que el usuario elija:
 * 4 modelos de hero y 3 módulos para el resto del inicio. Usa fotos reales de la tienda.
 * Publicada solo en el sitio local (dfans.local), no se sube a la tienda. Uso: php opciones_portada.php
 */
define( 'WP_USE_THEMES', false );
$_SERVER['HTTP_HOST']   = 'dfans.local';
$_SERVER['REQUEST_URI'] = '/';
require 'C:/Users/GRLL/Local Sites/dfans/app/public/wp-load.php';
wp_set_current_user( 1 );

$u  = function ( $id, $t = 'full' ) { return esc_url( wp_get_attachment_image_url( $id, $t ) ); };
$h  = function ( $ruta ) { return esc_url( home_url( $ruta ) ); };
$th = function ( $slug ) { $p = get_page_by_path( $slug, OBJECT, 'product' ); return $p ? (int) get_post_thumbnail_id( $p->ID ) : 0; };

$demon = '/product/polo-demon-slayer/';
$match = '/product-category/match-familiar/';

// Productos para el carrusel.
$cards = '';
foreach ( wc_get_products( array( 'limit' => 10, 'orderby' => 'menu_order', 'order' => 'ASC', 'status' => 'publish' ) ) as $i => $p ) {
	$f   = get_post_meta( $p->get_id(), '_dfans_fotos', true );
	$img = ( is_array( $f ) && isset( $f['hombre'] ) ) ? $f[ 0 === $i % 2 ? 'hombre' : 'mujer' ]['delantera'] : $p->get_image_id();
	$cards .= '<a class="dop-card" href="' . esc_url( get_permalink( $p->get_id() ) ) . '"><span class="dop-card__img"><img loading="lazy" src="' . $u( $img, 'medium_large' ) . '" alt="' . esc_attr( $p->get_name() ) . '"></span>'
		. '<span class="dop-card__nombre">' . esc_html( $p->get_name() ) . '</span><span class="dop-card__precio">' . wp_strip_all_tags( preg_replace( '#<span class="screen-reader-text">.*?</span>#s', '', $p->get_price_html() ) ) . '</span></a>';
}

// Círculos de fandom.
$fandoms = array(
	array( 'Anime', 'anime', $th( 'baby-tee-chica-magica-luna' ) ),
	array( 'Rock', 'rock', $th( 'polo-oversize-highway-77' ) ),
	array( 'Metal', 'metal', $th( 'polo-oversize-thrash-attack' ) ),
	array( 'Artistas', 'artistas', $th( 'polo-oversize-beat-maker' ) ),
	array( 'Películas', 'peliculas-y-series', $th( 'polo-oversize-zona-cero' ) ),
	array( 'Videojuegos', 'videojuegos', $th( 'polo-oversize-boss-fight' ) ),
	array( 'Match', '', 102 ),
	array( 'Niños', '', $th( 'polo-nino-pixel-knight' ) ),
);
$circulos = '';
foreach ( $fandoms as list( $n, $slug, $img ) ) {
	$ruta = $slug ? "/shop/?tematica=$slug" : ( 'Match' === $n ? $match : '/product-category/ropa-para-ninos/' );
	$circulos .= '<a class="dop-circ" href="' . $h( $ruta ) . '"><span><img loading="lazy" src="' . $u( $img, 'medium_large' ) . '" alt=""></span>' . esc_html( $n ) . '</a>';
}

$css = <<<'CSS'
.dop{--rojo:#e0262f;--tinta:#ededea;--gris:#9b9b96;--panel:#151515;--linea:#2a2a28;color:var(--tinta);font-family:'Archivo',system-ui,sans-serif;width:100vw;max-width:none!important;margin-left:calc(50% - 50vw)!important;margin-right:0!important}
body:has(.dop){overflow-x:clip}
body:has(.dop) main .wp-block-post-title{display:none}
.dop *{box-sizing:border-box}
.dop a{color:inherit;text-decoration:none}
.dop-etq{max-width:1340px;margin:4.5rem auto 1.2rem;padding:0 1.5rem;display:flex;align-items:center;gap:.8rem;font-size:.8rem;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:var(--gris)}
.dop-etq b{display:inline-grid;place-items:center;width:2rem;height:2rem;border-radius:50%;background:var(--rojo);color:#fff;letter-spacing:0}
.dop-h{position:relative;overflow:hidden;background:#0b0b0b}
.dop-titulo{margin:0 0 1rem;font-stretch:85%;font-weight:800;text-transform:uppercase;line-height:.94;letter-spacing:-.005em;font-size:clamp(2.4rem,4.4vw,4.4rem);color:#fff;max-width:12ch}
.dop-chip{display:inline-block;margin:0 0 1.1rem;padding:.35rem .7rem;border-radius:3px;background:var(--rojo);color:#fff;font-size:.68rem;font-weight:700;letter-spacing:.16em;text-transform:uppercase}
.dop-bajada{margin:0 0 1.8rem;max-width:30rem;color:#cfcfcb;font-size:1rem;line-height:1.6}
.dop-botones{display:flex;flex-wrap:wrap;gap:.6rem}
.dop-btn{display:inline-flex;align-items:center;justify-content:center;min-height:50px;padding:0 1.6rem;border-radius:4px;font-size:.76rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;transition:background .2s,transform .2s}
.dop-btn:active{transform:translateY(1px)}
.dop-btn--rojo{background:var(--rojo);color:#fff}.dop-btn--rojo:hover{background:#c41d25}
.dop-btn--borde{border:1px solid #5a5a57;color:#fff}.dop-btn--borde:hover{background:#fff;color:#000}
.dop-img-zoom{overflow:hidden}.dop-img-zoom img{transition:transform .7s cubic-bezier(.2,.7,.2,1)}.dop-img-zoom:hover img{transform:scale(1.03)}

/* 1. Cartelera */
.dop-cartelera{height:calc(100svh - 110px);min-height:600px;max-height:900px}
.dop-cartelera__foto{position:absolute;top:0;right:0;bottom:0;width:64%}
.dop-cartelera__foto img{width:100%;height:100%;object-fit:cover;object-position:center 18%;-webkit-mask-image:linear-gradient(to right,transparent 0,#000 30%);mask-image:linear-gradient(to right,transparent 0,#000 30%)}
.dop-cartelera__card{position:absolute;right:3%;bottom:7%;width:min(22%,260px);aspect-ratio:3/4;border-radius:10px;overflow:hidden;border:4px solid #0b0b0b;box-shadow:0 20px 50px rgba(0,0,0,.5)}
.dop-cartelera__card img{width:100%;height:100%;object-fit:cover;object-position:center 20%}
.dop-cartelera__card span{position:absolute;left:.7rem;bottom:.6rem;font-size:.66rem;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:#fff;text-shadow:0 1px 6px rgba(0,0,0,.7)}
.dop-cartelera__texto{position:relative;z-index:2;height:100%;max-width:1340px;margin:0 auto;padding:0 1.5rem;display:flex;flex-direction:column;justify-content:center}
.dop-tambien{display:flex;align-items:center;gap:.6rem;margin-top:2.2rem;font-size:.72rem;letter-spacing:.12em;text-transform:uppercase;color:var(--gris)}
.dop-tambien a{width:54px;height:54px;border-radius:50%;overflow:hidden;border:2px solid var(--linea)}
.dop-tambien a:hover{border-color:var(--rojo)}
.dop-tambien img{width:100%;height:100%;object-fit:cover;object-position:center 15%}

/* 2. Mosaico */
.dop-mosaico{padding:1rem 1.5rem 0}
.dop-mosaico__grid{max-width:1340px;margin:0 auto;display:grid;grid-template-columns:2fr 1fr 1fr;grid-template-rows:1fr 1fr;gap:.8rem;height:calc(100svh - 130px);min-height:600px;max-height:880px}
.dop-tile{position:relative;border-radius:12px;overflow:hidden;background:var(--panel)}
.dop-tile img{width:100%;height:100%;object-fit:cover;object-position:center 20%}
.dop-tile::after{content:"";position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.75),rgba(0,0,0,0) 50%);pointer-events:none}
.dop-tile__etq{position:absolute;left:1rem;bottom:.9rem;z-index:1;font-size:.74rem;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:#fff}
.dop-tile--grande{grid-row:1/3}
.dop-tile--grande .dop-tile__texto{position:absolute;left:clamp(1.2rem,3vw,2.5rem);right:1.5rem;bottom:clamp(1.2rem,3vw,2.5rem);z-index:1}
.dop-tile--grande::after{background:linear-gradient(to top,rgba(0,0,0,.85),rgba(0,0,0,0) 60%)}
.dop-tile--cta{display:flex;flex-direction:column;justify-content:space-between;padding:1.4rem;background:var(--rojo)}
.dop-tile--cta::after{display:none}
.dop-tile--cta p{margin:0;font-size:1.5rem;font-stretch:85%;font-weight:800;text-transform:uppercase;line-height:1;color:#fff}
.dop-tile--cta small{display:block;margin-top:.6rem;font-size:.85rem;color:#ffe3e4;line-height:1.4}
.dop-tile--cta .dop-btn{background:#0b0b0b;color:#fff;align-self:flex-start}

/* 3. Slider */
.dop-slider{height:calc(100svh - 110px);min-height:600px;max-height:880px}
.dop-slides{display:flex;height:100%;overflow-x:auto;scroll-snap-type:x mandatory;scrollbar-width:none;scroll-behavior:smooth}
.dop-slides::-webkit-scrollbar{display:none}
.dop-slide{flex:0 0 100%;scroll-snap-align:start;position:relative;display:grid;grid-template-columns:.9fr 1.5fr}
.dop-slide__texto{display:flex;flex-direction:column;justify-content:center;padding:2rem clamp(1.5rem,4vw,4rem) 4rem max(1.5rem,calc((100vw - 1340px)/2 + 1.5rem))}
.dop-slide__fotos{display:grid;grid-template-columns:1fr 1fr;gap:2px}
.dop-slide__fotos img{width:100%;height:100%;object-fit:cover;object-position:center 20%}
.dop-slide--b{background:#14100f}.dop-slide--c{background:#0d1013}
.dop-slider__ctrl{position:absolute;left:max(1.5rem,calc((100vw - 1340px)/2 + 1.5rem));bottom:2rem;z-index:3;display:flex;align-items:center;gap:.8rem}
.dop-slider__ctrl button{width:42px;height:42px;border-radius:50%;border:1px solid #5a5a57;background:transparent;color:#fff;font-size:1.1rem;cursor:pointer}
.dop-slider__ctrl button:hover{background:#fff;color:#000}
.dop-dots{display:flex;gap:.4rem;margin:0 .4rem}
.dop-dots i{display:block;width:26px;height:3px;border-radius:2px;background:#4a4a47}
.dop-dots i.on{background:var(--rojo)}

/* 4. Producto destacado */
.dop-prod{padding:1.5rem}
.dop-prod__grid{max-width:1340px;margin:0 auto;display:grid;grid-template-columns:1.25fr 1fr;gap:clamp(1.5rem,4vw,4rem);align-items:center;min-height:calc(100svh - 160px)}
.dop-prod__galeria{display:grid;grid-template-columns:90px 1fr;gap:.8rem}
.dop-prod__mini{display:flex;flex-direction:column;gap:.6rem}
.dop-prod__mini button{padding:0;border:2px solid transparent;border-radius:8px;overflow:hidden;background:none;cursor:pointer;aspect-ratio:3/4}
.dop-prod__mini button.on,.dop-prod__mini button:hover{border-color:var(--rojo)}
.dop-prod__mini img{width:100%;height:100%;object-fit:cover;object-position:center 15%;display:block}
.dop-prod__grande{border-radius:12px;overflow:hidden;aspect-ratio:4/5;max-height:calc(100svh - 190px)}
.dop-prod__grande img{width:100%;height:100%;object-fit:cover;object-position:center 18%}
.dop-prod__nombre{margin:0 0 .5rem;font-stretch:85%;font-weight:800;text-transform:uppercase;font-size:clamp(2rem,3.4vw,3.2rem);line-height:.95;color:#fff}
.dop-prod__precio{margin:0 0 1.2rem;font-size:1.3rem;font-weight:700}
.dop-tallas{margin:1.2rem 0 1.6rem}
.dop-tallas p{margin:0 0 .5rem;font-size:.7rem;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:var(--gris)}
.dop-tallas div{display:flex;flex-wrap:wrap;gap:.4rem;margin-bottom:.9rem}
.dop-tallas a{min-width:3.2rem;height:44px;display:grid;place-items:center;border:1px solid #3b3b39;border-radius:4px;font-size:.8rem;font-weight:700}
.dop-tallas a:hover{border-color:#fff}
.dop-confianza{display:flex;flex-wrap:wrap;gap:.4rem 1.2rem;margin-top:1.4rem;font-size:.82rem;color:var(--gris)}

/* Módulos del inicio */
.dop-mod{max-width:1340px;margin:0 auto;padding:0 1.5rem}
.dop-mod h3{margin:0 0 1.4rem!important;font-stretch:85%;font-weight:800!important;text-transform:uppercase!important;font-size:clamp(1.4rem,2.2vw,2rem)!important;letter-spacing:0!important;color:#fff}
.dop-circulos{display:grid;grid-template-columns:repeat(8,minmax(0,1fr));gap:1rem}
.dop-circ{display:flex;flex-direction:column;align-items:center;gap:.7rem;font-size:.74rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase}
.dop-circ span{width:100%;aspect-ratio:1;border-radius:50%;overflow:hidden;border:2px solid var(--linea);transition:border-color .2s}
.dop-circ:hover span{border-color:var(--rojo)}
.dop-circ img{width:100%;height:100%;object-fit:cover;object-position:center 18%;transition:transform .5s}
.dop-circ:hover img{transform:scale(1.05)}
.dop-dobles{display:grid;grid-template-columns:1fr 1fr;gap:.9rem}
.dop-banner{position:relative;display:block;height:440px;border-radius:12px;overflow:hidden;background:var(--panel)}
.dop-banner img{width:100%;height:100%;object-fit:cover;object-position:center 22%}
.dop-banner::after{content:"";position:absolute;inset:0;background:linear-gradient(to right,rgba(0,0,0,.8),rgba(0,0,0,0) 65%)}
.dop-banner__t{position:absolute;left:2rem;top:50%;transform:translateY(-50%);z-index:1;max-width:60%}
.dop-banner__t strong{display:block;margin-bottom:.6rem;font-stretch:85%;font-weight:800;text-transform:uppercase;font-size:2rem;line-height:.95;color:#fff}
.dop-banner__t span{display:block;margin-bottom:1.2rem;color:#d2d2ce;font-size:.95rem;line-height:1.5}
.dop-banner__t .dop-btn{display:inline-flex;margin:0;color:#fff;font-size:.76rem;line-height:1}
.dop-carrusel__cab{display:flex;justify-content:space-between;align-items:center}
.dop-carrusel__flechas{display:flex;gap:.5rem}
.dop-carrusel__flechas button{width:42px;height:42px;border-radius:50%;border:1px solid #5a5a57;background:transparent;color:#fff;cursor:pointer;font-size:1.1rem}
.dop-carrusel__flechas button:hover{background:#fff;color:#000}
.dop-fila{display:grid;grid-auto-flow:column;grid-auto-columns:calc((100% - 3*.8rem)/4.3);gap:.8rem;overflow-x:auto;scroll-snap-type:x mandatory;scrollbar-width:none;scroll-behavior:smooth;padding-bottom:.3rem}
.dop-fila::-webkit-scrollbar{display:none}
.dop-card{scroll-snap-align:start;display:flex;flex-direction:column;gap:.35rem}
.dop-card__img{display:block;border-radius:10px;overflow:hidden;aspect-ratio:3/4;background:var(--panel)}
.dop-card__img img{width:100%;height:100%;object-fit:cover;object-position:center 20%;transition:transform .6s}
.dop-card:hover .dop-card__img img{transform:scale(1.03)}
.dop-card__nombre{margin-top:.55rem;font-size:.76rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase}
.dop-card__precio{font-size:.85rem;color:var(--gris)}

@media (max-width:900px){
.dop-cartelera__foto{width:100%}.dop-cartelera__foto img{-webkit-mask-image:linear-gradient(to top,transparent 0,#000 55%);mask-image:linear-gradient(to top,transparent 0,#000 55%)}
.dop-cartelera__texto{justify-content:flex-end;padding-bottom:2rem}.dop-cartelera__card{display:none}
.dop-mosaico__grid{grid-template-columns:1fr 1fr;grid-template-rows:auto;height:auto;max-height:none}
.dop-tile--grande{grid-column:1/3;grid-row:auto;height:72svh}.dop-tile{min-height:220px}
.dop-slide{grid-template-columns:1fr;grid-template-rows:1fr auto}.dop-slide__fotos{grid-row:1}.dop-slide__texto{grid-row:2;padding:1.4rem 1.25rem 5rem}
.dop-prod__grid{grid-template-columns:1fr}.dop-prod__galeria{grid-template-columns:1fr}.dop-prod__mini{flex-direction:row;order:2}.dop-prod__mini button{width:70px}
.dop-circulos{grid-template-columns:none;grid-auto-flow:column;grid-auto-columns:96px;overflow-x:auto;scrollbar-width:none}
.dop-dobles{grid-template-columns:1fr}.dop-banner{height:340px}.dop-banner__t{max-width:80%}
.dop-fila{grid-auto-columns:62%}
}
@media (prefers-reduced-motion:reduce){.dop *{transition:none!important;scroll-behavior:auto!important}}
CSS;

$js = <<<'JS'
(function(){
var s=document.querySelector('.dop-slides'); if(s){var n=s.children.length,i=0,dots=document.querySelectorAll('.dop-dots i'),t;
function ir(k){i=(k+n)%n;s.scrollTo({left:i*s.clientWidth});dots.forEach(function(d,j){d.classList.toggle('on',j===i)});}
document.querySelector('.dop-prev').onclick=function(){ir(i-1)};document.querySelector('.dop-next').onclick=function(){ir(i+1)};
var quieto=window.matchMedia('(prefers-reduced-motion: reduce)').matches;function play(){if(!quieto){t=setInterval(function(){ir(i+1)},6000)}}
s.parentNode.addEventListener('mouseenter',function(){clearInterval(t)});s.parentNode.addEventListener('mouseleave',play);play();}
document.querySelectorAll('.dop-prod__mini button').forEach(function(b){b.onclick=function(){document.querySelector('.dop-prod__grande img').src=b.dataset.src;document.querySelectorAll('.dop-prod__mini button').forEach(function(x){x.classList.remove('on')});b.classList.add('on')}});
var f=document.querySelector('.dop-fila');if(f){document.querySelector('.dop-izq').onclick=function(){f.scrollBy({left:-f.clientWidth*.8})};document.querySelector('.dop-der').onclick=function(){f.scrollBy({left:f.clientWidth*.8})}}
})();
JS;

$botones = '<div class="dop-botones"><a class="dop-btn dop-btn--rojo" href="' . $h( $match ) . '">Comprar Match</a><a class="dop-btn dop-btn--borde" href="' . $h( '/shop/' ) . '">Ver toda la tienda</a></div>';
$bajada  = '<p class="dop-bajada">Cada diseño en tallas de niño (10 a 14) y de adulto (S a XXL). Desde S/ 30, hechos en Gamarra.</p>';
$titulo  = '<h2 class="dop-titulo">El mismo polo para toda la familia</h2>';

$html  = '<div class="dop"><style>' . $css . '</style>';
$html .= '<p class="dop-etq"><b>1</b> Cartelera: una foto grande que se funde con el fondo, otra en tarjeta y accesos por persona</p>';
$html .= '<section class="dop-h dop-cartelera"><div class="dop-cartelera__foto"><img src="' . $u( 100 ) . '" alt="Hombre con el polo Demon Slayer"></div>'
	. '<a class="dop-cartelera__card" href="' . $h( $demon . '?seccion=ninos&para=nina' ) . '"><img src="' . $u( 106, 'large' ) . '" alt="Niña con el mismo polo"><span>Mismo diseño, talla niña</span></a>'
	. '<div class="dop-cartelera__texto"><p><span class="dop-chip">Colección Match</span></p>' . $titulo . $bajada . $botones
	. '<div class="dop-tambien">También para<a href="' . $h( '/product-category/ropa-de-adultos/?para=mujer' ) . '" aria-label="Mujer"><img src="' . $u( 102, 'medium' ) . '" alt=""></a><a href="' . $h( '/product-category/ropa-para-ninos/?para=nino' ) . '" aria-label="Niño"><img src="' . $u( 104, 'medium' ) . '" alt=""></a><a href="' . $h( '/product-category/ropa-para-ninos/?para=nina' ) . '" aria-label="Niña"><img src="' . $u( 106, 'medium' ) . '" alt=""></a></div></div></section>';

$html .= '<p class="dop-etq"><b>2</b> Mosaico: varias fotos en bloques, con una tarjeta roja de compra</p>';
$html .= '<section class="dop-h dop-mosaico"><div class="dop-mosaico__grid">'
	. '<div class="dop-tile dop-tile--grande dop-img-zoom"><img src="' . $u( 100 ) . '" alt="Hombre con el polo Demon Slayer"><div class="dop-tile__texto"><p><span class="dop-chip">Colección Match</span></p>' . $titulo . $botones . '</div></div>'
	. '<a class="dop-tile dop-img-zoom" href="' . $h( '/product-category/ropa-de-adultos/?para=mujer' ) . '"><img src="' . $u( 102, 'large' ) . '" alt="Mujer con el polo Demon Slayer"><span class="dop-tile__etq">Mujer</span></a>'
	. '<a class="dop-tile dop-img-zoom" href="' . $h( '/product-category/ropa-para-ninos/?para=nina' ) . '"><img src="' . $u( 106, 'large' ) . '" alt="Niña con el polo Demon Slayer"><span class="dop-tile__etq">Niña</span></a>'
	. '<a class="dop-tile dop-img-zoom" href="' . $h( '/product-category/ropa-para-ninos/?para=nino' ) . '"><img src="' . $u( 104, 'large' ) . '" alt="Niño con el polo Demon Slayer"><span class="dop-tile__etq">Niño</span></a>'
	. '<div class="dop-tile dop-tile--cta"><div><p>Tallas para todos</p><small>Niño 10 a 14 y adulto S a XXL. Desde S/ 30.</small></div><a class="dop-btn" href="' . $h( $match ) . '">Ver la colección</a></div>'
	. '</div></section>';

$slide = function ( $mod, $chip, $tit, $txt, $btn, $ruta, $a, $b ) use ( $u, $h ) {
	return '<div class="dop-slide ' . $mod . '"><div class="dop-slide__texto"><p><span class="dop-chip">' . $chip . '</span></p><h2 class="dop-titulo">' . $tit . '</h2><p class="dop-bajada">' . $txt . '</p><div class="dop-botones"><a class="dop-btn dop-btn--rojo" href="' . $h( $ruta ) . '">' . $btn . '</a></div></div>'
		. '<div class="dop-slide__fotos"><img src="' . $u( $a, 'large' ) . '" alt=""><img src="' . $u( $b, 'large' ) . '" alt=""></div></div>';
};
$html .= '<p class="dop-etq"><b>3</b> Slider de campañas: tres banners que cambian solos (Match, Exclusivos y Niños)</p>';
$html .= '<section class="dop-h dop-slider"><div class="dop-slides">'
	. $slide( 'dop-slide--a', 'Colección Match', 'El mismo polo para toda la familia', 'Cada diseño en tallas de niño y de adulto. Desde S/ 30.', 'Comprar Match', $match, 100, 106 )
	. $slide( 'dop-slide--b', 'Exclusivos D’FANS', 'Diseños que solo están aquí', 'Oversize, baby tee, regular reforzado y manga mariposa.', 'Ver exclusivos', '/product-category/ropa-de-adultos/', $th( 'polo-oversize-boss-fight' ), $th( 'baby-tee-chica-magica-luna' ) )
	. $slide( 'dop-slide--c', 'Niños', 'Sus personajes favoritos, en su talla', 'Tallas 10, 12 y 14. Polos con doble costura y manga mariposa.', 'Ver ropa de niños', '/product-category/ropa-para-ninos/', $th( 'polo-nino-pixel-knight' ), $th( 'polo-nina-boba-panda' ) )
	. '</div><div class="dop-slider__ctrl"><button class="dop-prev" aria-label="Banner anterior">‹</button><span class="dop-dots"><i class="on"></i><i></i><i></i></span><button class="dop-next" aria-label="Banner siguiente">›</button></div></section>';

$html .= '<p class="dop-etq"><b>4</b> Producto destacado: el polo con sus fotos por persona y las tallas a la vista</p>';
$mini = '';
foreach ( array( array( 100, 'Hombre' ), array( 102, 'Mujer' ), array( 104, 'Niño' ), array( 106, 'Niña' ) ) as $k => list( $id, $n ) ) {
	$mini .= '<button class="' . ( 0 === $k ? 'on' : '' ) . '" data-src="' . $u( $id ) . '" aria-label="Ver en ' . $n . '"><img src="' . $u( $id, 'medium' ) . '" alt=""></button>';
}
$tallas = function ( $lista ) use ( $h, $demon ) {
	$o = '';
	foreach ( $lista as $t ) {
		$o .= '<a href="' . $h( $demon . '?attribute_pa_talla=' . strtolower( $t ) ) . '">' . $t . '</a>';
	}
	return $o;
};
$html .= '<section class="dop-h dop-prod"><div class="dop-prod__grid"><div class="dop-prod__galeria"><div class="dop-prod__mini">' . $mini . '</div><div class="dop-prod__grande"><img src="' . $u( 100 ) . '" alt="Polo Demon Slayer"></div></div>'
	. '<div><p><span class="dop-chip">Colección Match</span></p><h2 class="dop-prod__nombre">Polo Demon Slayer</h2><p class="dop-prod__precio">S/ 30 – S/ 40</p>' . $bajada
	. '<div class="dop-tallas"><p>Adultos</p><div>' . $tallas( array( 'S', 'M', 'L', 'XL', 'XXL' ) ) . '</div><p>Niños</p><div>' . $tallas( array( '10', '12', '14' ) ) . '</div></div>'
	. '<div class="dop-botones"><a class="dop-btn dop-btn--rojo" href="' . $h( $demon ) . '">Comprar ahora</a><a class="dop-btn dop-btn--borde" href="' . $h( $match ) . '">Ver colección Match</a></div>'
	. '<p class="dop-confianza"><span>Envíos a todo el Perú</span><span>Recojo gratis en Gamarra</span><span>Tarjeta, Yape o transferencia</span></p></div></div></section>';

$html .= '<p class="dop-etq"><b>A</b> Módulo para el inicio: categorías en círculos</p><div class="dop-mod"><h3>Explora por fandom</h3><div class="dop-circulos">' . $circulos . '</div></div>';
$html .= '<p class="dop-etq"><b>B</b> Módulo para el inicio: dos banners lado a lado</p><div class="dop-mod"><div class="dop-dobles">'
	. '<a class="dop-banner" href="' . $h( $match ) . '"><img src="' . $u( 102 ) . '" alt=""><span class="dop-banner__t"><strong>Colección Match</strong><span>El mismo diseño en talla de niño y de adulto.</span><span class="dop-btn dop-btn--rojo">Comprar Match</span></span></a>'
	. '<a class="dop-banner" href="' . $h( '/product-category/ropa-de-adultos/' ) . '"><img src="' . $u( $th( 'polo-oversize-boss-fight' ) ) . '" alt=""><span class="dop-banner__t"><strong>Exclusivos D’FANS</strong><span>Cuatro cortes propios: oversize, baby tee, regular y manga mariposa.</span><span class="dop-btn dop-btn--borde">Ver exclusivos</span></span></a>'
	. '</div></div>';
$html .= '<p class="dop-etq"><b>C</b> Módulo para el inicio: carrusel de productos con flechas</p><div class="dop-mod"><div class="dop-carrusel__cab"><h3>Lo nuevo</h3><div class="dop-carrusel__flechas"><button class="dop-izq" aria-label="Anteriores">‹</button><button class="dop-der" aria-label="Siguientes">›</button></div></div><div class="dop-fila">' . $cards . '</div></div>';
$html .= '<div style="height:5rem"></div><script>' . $js . '</script></div>';

$contenido = "<!-- wp:html -->\n" . $html . "\n<!-- /wp:html -->";
$pagina    = get_page_by_path( 'opciones-portada' );
$datos     = array( 'post_title' => 'Opciones de portada', 'post_name' => 'opciones-portada', 'post_status' => 'publish', 'post_type' => 'page', 'post_content' => wp_slash( $contenido ) );
if ( $pagina ) {
	$datos['ID'] = $pagina->ID;
}
$id = wp_insert_post( $datos );
echo "Página privada $id: ", get_permalink( $id ), "\n";
