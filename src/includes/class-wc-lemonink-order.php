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
			
			add_action( 'woocommerce_grant_product_download_access', array( $this, 'create_transaction' ), 10, 1);

			return true;
		}
		
		public function create_transaction( $download_data ) {
			$meta_prefix = "_li_product_{$download_data['product_id']}_";
			$transaction_exists = get_post_meta( $download_data['order_id'], $meta_prefix . 'transaction_id', yes );

			if ( !$transaction_exists && $this->is_lemoninkable_download( $download_data['download_id'] ) ) {
				$product = wc_get_product( $download_data['product_id'] );
				$master_id = get_post_meta( $product->get_id(), '_li_master_id', yes );
					
				$transaction = new LemonInk\Models\Transaction();
				$transaction->setMasterId( $master_id );
				$transaction->setWatermarkValue( $download_data['user_email'] );

				$this->settings->get_api_client()->save($transaction);

				add_post_meta( $download_data['order_id'], $meta_prefix . 'transaction_id', $transaction->getId(), true );
				add_post_meta( $download_data['order_id'], $meta_prefix . 'transaction_token', $transaction->getToken(), true );
			}
		}
	}

endif;
