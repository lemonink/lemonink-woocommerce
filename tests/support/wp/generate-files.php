<?php
/**
 * Generate a lemoninkable product's downloadable files from its master,
 * using the plugin's own logic. Run via wp-cli:
 *
 *   wp eval-file wp-content/lemonink-dev/generate-files.php <product_id>
 */

$product_id = isset( $args[0] ) ? (int) $args[0] : 0;
if ( ! $product_id ) {
	WP_CLI::error( 'Usage: eval-file generate-files.php <product_id>' );
}

$integration = WC()->integrations->get_integrations()['lemonink'];
$product      = new WC_LemonInk_Product( $integration );
$product->generate_product_files( $product_id );

$files = wc_get_product( $product_id )->get_downloads();
WP_CLI::log( count( $files ) . ' download(s) generated for product ' . $product_id );
foreach ( $files as $id => $file ) {
	WP_CLI::log( '  - ' . $id . ': ' . $file['name'] . ' (' . $file['file'] . ')' );
}
