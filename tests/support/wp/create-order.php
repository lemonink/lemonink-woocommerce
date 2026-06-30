<?php
/**
 * Create a completed order for a product and print "<order_id> <order_key>".
 * Completing the order grants download access, which fires the plugin's
 * transaction-creation hook. Run via wp-cli:
 *
 *   wp eval-file wp-content/lemonink-dev/create-order.php <product_id> <email>
 */

$product_id = isset( $args[0] ) ? (int) $args[0] : 0;
$email       = isset( $args[1] ) ? trim( $args[1] ) : '';
if ( ! $product_id || '' === $email ) {
	WP_CLI::error( 'Usage: eval-file create-order.php <product_id> <email>' );
}

$order = wc_create_order();
$order->add_product( wc_get_product( $product_id ), 1 );
$order->set_address(
	array( 'email' => $email, 'first_name' => 'Buy', 'last_name' => 'Er' ),
	'billing'
);
$order->calculate_totals();
$order->update_status( 'completed' ); // grants downloads -> transaction hook

echo $order->get_id() . ' ' . $order->get_order_key();
