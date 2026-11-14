<?php
/**
 * Snippet #30: Google Analytics
 * Alcance: global · prioridad 10
 * Exportado de Code Snippets el 14/11/2026. La copia activa está en la base de datos.
 */

// Google Analytics 4 (gtag.js) en el <head> de todas las páginas de la tienda.
add_action( 'wp_head', function () {
	?>
	<!-- Google tag (gtag.js) -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=G-VDXZFSSXDC"></script>
	<script>
	  window.dataLayer = window.dataLayer || [];
	  function gtag(){dataLayer.push(arguments);}
	  gtag('js', new Date());
	  gtag('config', 'G-VDXZFSSXDC');
	</script>
	<?php
}, 1 );
