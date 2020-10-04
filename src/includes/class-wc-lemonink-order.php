<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_LemonInk_Order' ) ) :

	class WC_LemonInk_Order {
		/**
		 * @var WC_LemonInk_Integration
		 */
		private $settings;

		public function __construct( WC_LemonInk_Integration $settings ) {
			$this->settings = $settings;
			
			add_action( 'woocommerce_grant_product_download_access', array( $this, 'create_transaction' ), 10, 1 );

			add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'update_order_meta' ), 10, 2 );

			return true;
		}
		
		public function create_transaction( $download_data ) {
			$meta_prefix = "_li_product_{$download_data['product_id']}_";
			$transaction_exists = get_post_meta( $download_data['order_id'], $meta_prefix . 'transaction_id', 'yes' );

			if ( !$transaction_exists && $this->is_lemoninkable_download( $download_data['download_id'] ) ) {
				$product = wc_get_product( $download_data['product_id'] );
				$master_id = get_post_meta( $product->get_id(), '_li_master_id', 'yes' );

				$watermark_value = get_post_meta( $download_data['order_id'], '_li_watermark_value', 'yes' );
				if ( !isset($watermark_value) ) {
					$watermark_value = $this->watermark_value( $download_data['order_id'], $download_data['user_email'] );
				}
					
				$transaction = new LemonInk\Models\Transaction();
				$transaction->setMasterId( $master_id );
				
				$transaction->setWatermarkValue( $watermark_value );

				$this->settings->get_api_client()->save($transaction);

				add_post_meta( $download_data['order_id'], $meta_prefix . 'transaction_id', $transaction->getId(), true );
				add_post_meta( $download_data['order_id'], $meta_prefix . 'transaction_token', $transaction->getToken(), true );
			}
		}

		public function update_order_meta( $order_id, $data )
		{
			add_post_meta( $order_id, '_li_watermark_value', $this->watermark_value( $order_id, $data['billing_email'] ) );
		}

		private function is_lemoninkable_download( $download_id ) {
				return substr( $download_id, 0, 4 ) === '_li_';
		}

		private function watermark_value( $order_id, $email ) {
			// translators: first %s: order ID, second %s: order email
			$value = __( 'Order #%s (%s)', 'lemonink' );
			return sprintf( $value, $order_id, $this->obfuscate_email( $email ) );
		}

		private function obfuscate_email( $email ) {
			$parts = explode( '@', $email );
			$parts[0] = substr( $parts[0], 0, 1 ) . '***' . substr( $parts[0], -1, 1 );
			return implode( '@', $parts );
		}
	}

endif;
