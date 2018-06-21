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

			// check for connection
			// if ( ! $this->settings->connected ) {
			// 	// @todo: handle error, admin notice?
			// 	return false;
			// }

			add_action( 'woocommerce_grant_product_download_access', array( $this, 'create_transaction' ), 10, 1);

			add_filter( 'woocommerce_get_item_downloads', array( $this, 'get_item_downloads' ), 10, 3 );

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

		public function get_item_downloads( $files, $item, $order ) {
			$product_id = $item['variation_id'] > 0 ? $item['variation_id'] : $item['product_id'];
			$meta_prefix = "_li_product_{$product_id}_";

			$transaction = new LemonInk\Models\Transaction();
			$transaction->setId( get_post_meta( $order->id, $meta_prefix . 'transaction_id', true ) );
			$transaction->setToken(  get_post_meta( $order->id, $meta_prefix . 'transaction_token', true ) );

			foreach ( $files as $download_id => $file ) {
				if ( $this->is_lemoninkable_download( $download_id ) ) {
					$format = strtolower($files[$download_id]['name']);
					$files[$download_id]['download_url'] = $transaction->getUrl($format);
				}
			}

			return $files;
		}

		private function is_lemoninkable_download( $download_id ) {
				return substr( $download_id, 0, 4 ) === '_li_';
		}
	}

endif;
