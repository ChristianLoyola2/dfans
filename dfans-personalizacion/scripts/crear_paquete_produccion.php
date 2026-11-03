<?php
/**
 * Crea con Duplicator el paquete de migración a producción (03/11/2026): archivo .zip + installer.php.
 * Deja fuera los plugins desactivados. El paquete contiene la base de datos completa (datos de clientes y
 * ajustes de plugins): es solo para subirlo al hosting propio, nunca a GitHub ni a otro sitio.
 * Uso: php crear_paquete_produccion.php [--apply]
 */
define( 'WP_USE_THEMES', false );
$_SERVER['HTTP_HOST']   = 'dfans.local';
$_SERVER['REQUEST_URI'] = '/';
require 'C:/Users/GRLL/Local Sites/dfans/app/public/wp-load.php';
wp_set_current_user( 1 );

use Duplicator\Models\FullBackupTemplate;
use Duplicator\Package\AbstractPackage;
use Duplicator\Package\DupPackage;
use Duplicator\Package\PackageExecutionInfo;
use Duplicator\Package\PackageUtils;
use Duplicator\Package\Runner;
use Duplicator\Utils\Lock\LockUtil;

$plugins  = WP_PLUGIN_DIR;
$fuera    = array();
$activos  = (array) get_option( 'active_plugins' );
foreach ( array_keys( get_plugins() ) as $f ) {
	if ( ! in_array( $f, $activos, true ) && false !== strpos( $f, '/' ) ) {
		$fuera[] = wp_normalize_path( $plugins . '/' . dirname( $f ) );
	}
}
echo "Plugins desactivados que quedan fuera:\n  ", implode( "\n  ", $fuera ), "\n";
$fuera[] = wp_normalize_path( untrailingslashit( ABSPATH ) . '/.git' ); // historial de Git: no va al hosting
if ( ! in_array( '--apply', $argv, true ) ) {
	exit( "Modo revisión.\n" );
}

// Paquetes fallidos anteriores: se borran sus registros y temporales (no hay archivos útiles en ellos).
foreach ( DupPackage::getPackagesByStatus( array( array( 'op' => '<', 'status' => 0 ) ) ) as $viejo ) {
	echo 'Borrando paquete fallido ', $viejo->getId(), "\n";
	$viejo->delete( true );
}

$tpl                      = new FullBackupTemplate();
$tpl->archive_filter_on   = true;
$tpl->archive_filter_dirs = implode( ';', $fuera );

if ( LockUtil::lockProcessOrThrow() === false ) {
	exit( "Otro paquete se está creando.\n" );
}
try {
	$pkg = PackageUtils::createBackgroundPackage( $tpl, array(), new PackageExecutionInfo( AbstractPackage::EXECUTION_TYPE_API, 'D’FANS', 'Migración a producción' ) );
	$id  = $pkg->getId();
} finally {
	LockUtil::unlockProcess();
}
echo "Paquete $id en cola. Lo construye el servidor web de LocalWP...\n";

// Desde la consola solo se vigila: si también procesara, chocaría con el proceso web y dañaría el archivo.
Runner::kickOffWorker();
$inicio = time();
while ( time() - $inicio < 3000 ) {
	sleep( 3 );
	wp_remote_get( home_url( '/' ), array( 'timeout' => 30, 'blocking' => true ) ); // cada visita reanuda el trabajo
	wp_cache_flush();
	$p = DupPackage::getById( $id );
	if ( ! $p ) {
		exit( "El paquete desapareció.\n" );
	}
	$st = $p->getStatus();
	static $ultimo = null;
	if ( $st !== $ultimo ) {
		echo '  estado ', $st, ' (', time() - $inicio, " s)\n";
		$ultimo = $st;
	}
	if ( $st >= AbstractPackage::STATUS_COMPLETE || $st < 0 ) {
		break;
	}
	usleep( 300000 );
}
echo $st >= AbstractPackage::STATUS_COMPLETE ? "Paquete listo.\n" : "El paquete no terminó (estado $st).\n";
