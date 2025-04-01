<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_LemonInk_Order_Metadata' ) ) :

	class WC_LemonInk_Order_Metadata {
		public static function is_custom_orders_table_enabled() {
			static $is_enabled = null;

			if ($is_enabled === null) {
				$is_enabled = get_option('woocommerce_custom_orders_table_enabled');
			}

			return $is_enabled;
		}

		public static function get_transaction_data( $order_id, $prefix ) {
			if ( self::is_custom_orders_table_enabled() ) {
				$order = wc_get_order( $order_id );
				if ( !$order ) {
					return null;
				}

        $transaction_id = $order->get_meta( $prefix . 'transaction_id' );
        $transaction_token = $order->get_meta( $prefix . 'transaction_token' );

        if ( empty( $transaction_id ) || empty( $transaction_token ) ) {
          return null;
        }

				return array(
					'id' => $transaction_id,
					'token' => $transaction_token,
				);
			} else {
				$transaction_id = get_post_meta( $order_id, $prefix . 'transaction_id', true );
				$transaction_token = get_post_meta( $order_id, $prefix . 'transaction_token', true );

				if ( $transaction_id ) {
					return array(
						'id' => $transaction_id,
						'token' => $transaction_token,
					);
				} else {
					return null;
				}
			}
		}

		public static function set_transaction_data( $order_id, $prefix, $id, $token ) {
			if ( self::is_custom_orders_table_enabled()  ) {
				$order = wc_get_order( $order_id );
				if ( !$order ) {
					return null;
				}

				$order->update_meta_data( $prefix . 'transaction_id', $id );
				$order->update_meta_data( $prefix . 'transaction_token', $token );
				$order->save();
			} else {
				add_post_meta( $order_id, $prefix . 'transaction_id', $id, true );
				add_post_meta( $order_id, $prefix . 'transaction_token', $token, true );
			}
		}

		public static function get_watermark_value( $order_id ) {
			return get_post_meta( $order_id, '_li_watermark_value', true );
		}
	}

endif;
