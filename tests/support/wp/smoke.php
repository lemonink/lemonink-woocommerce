<?php
/**
 * Compatibility smoke check, run inside WordPress via wp-cli:
 *
 *   wp eval-file wp-content/lemonink-dev/smoke.php <master_id>
 *
 * Assumes WooCommerce + lemonink are active and the LemonInk API key has
 * already been stored in the integration settings. Exercises the plugin's
 * real hooks against whatever WP/WC version is running and hits the real
 * LemonInk API to generate downloadable files. Exits non-zero on failure.
 */

$master_id = isset( $args[0] ) ? trim( $args[0] ) : '';
if ( '' === $master_id ) {
	WP_CLI::error( 'Usage: eval-file smoke.php <master_id>' );
}

$fail = function ( $msg ) {
	WP_CLI::error( $msg ); // exits non-zero
};
$ok = function ( $msg ) {
	WP_CLI::log( '  ok  ' . $msg );
};

// 1. Integration is registered and reports connected (API key present).
$integration = WC()->integrations->get_integrations()['lemonink'] ?? null;
if ( ! $integration ) {
	$fail( 'LemonInk integration is not registered' );
}
if ( ! $integration->connected ) {
	$fail( 'integration is not connected — API key missing?' );
}
$ok( 'integration registered and connected' );

// 2. Collaborator classes loaded.
foreach ( array( 'WC_LemonInk_Product', 'WC_LemonInk_Order', 'WC_LemonInk_Download_Handler', 'WC_LemonInk_Order_Metadata' ) as $class ) {
	if ( ! class_exists( $class ) ) {
		$fail( "class $class is missing" );
	}
}
$ok( 'collaborator classes loaded' );

// 3. Master-id validation accepts a UUID-style master file.
$product_helper = new WC_LemonInk_Product( $integration );
if ( ! $product_helper->validate_master_file_is_present( false, $master_id . '.epub' ) ) {
	$fail( 'validate_master_file_is_present rejected a valid master id' );
}
$ok( 'master file id validation works' );

// 4. Configure a throwaway product and generate its downloadable files from
//    the real master (the core integration path).
$product = new WC_Product_Simple();
$product->set_name( 'LemonInk smoke test' );
$product->set_virtual( true );
$product->set_downloadable( true );
$product->set_regular_price( '10' );
$product_id = $product->save();

update_post_meta( $product_id, '_li_lemoninkable', 'yes' );
update_post_meta( $product_id, '_li_master_id', $master_id );

$product_helper->generate_product_files( $product_id );

$downloads = wc_get_product( $product_id )->get_downloads();
if ( count( $downloads ) < 1 ) {
	wp_delete_post( $product_id, true );
	$fail( 'generate_product_files produced no downloads for master ' . $master_id );
}
foreach ( $downloads as $id => $file ) {
	if ( strpos( $id, '_li_' ) !== 0 ) {
		wp_delete_post( $product_id, true );
		$fail( "download id '$id' is missing the _li_ prefix" );
	}
}
$ok( count( $downloads ) . ' watermarked download(s) generated from master' );

// 5. Download method is forced to redirect for lemoninkable products.
$handler = new WC_LemonInk_Download_Handler( $integration );
if ( 'redirect' !== $handler->download_method( 'force', $product_id ) ) {
	wp_delete_post( $product_id, true );
	$fail( 'download method was not forced to redirect' );
}
$ok( 'download method forced to redirect' );

wp_delete_post( $product_id, true );
WP_CLI::success( 'smoke checks passed' );
