<?php
/**
 * D'FANS - Mercado Pago: consulta cada 5 minutos el estado de los pedidos con tarjeta que siguen pendientes (10/11/2026).
 * Necesario en local (los avisos de Mercado Pago no llegan a dfans.local) y útil en el hosting como respaldo.
 * Uso: php mp_sincronizar.php   (activa el cron y sincroniza una vez en el momento)
 */
define( 'WP_USE_THEMES', false );
$_SERVER['HTTP_HOST'] = 'dfans.local';
require 'C:/Users/GRLL/Local Sites/dfans/app/public/wp-load.php';
wp_set_current_user( 1 );
$accion = 'mercadopago_sync_pending_status_order_action';
update_option( '_mp_cron_sync_mode', '5minutes' );
if ( ! wp_next_scheduled( $accion ) ) {
	wp_schedule_event( time() + 300, '5minutes', $accion );
}
echo 'Sincronización: cada 5 minutos (próxima ', wp_date( 'H:i', wp_next_scheduled( $accion ) ), ")\n";
do_action( $accion );
echo "Sincronizado ahora.\n";
