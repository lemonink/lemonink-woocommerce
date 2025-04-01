<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function get_downloads( $product) {
	if ( method_exists( $product, 'get_downloads' ) ){
		return $product->get_downloads();
	} else {
		return $product->get_files();
	}
}

if ( ! class_exists( 'WC_LemonInk_Download_Handler' ) ) :

	class WC_LemonInk_Download_Handler {
		/**
		 * @var WC_LemonInk_Integration
		 */
		private $settings;

		public function __construct( WC_LemonInk_Integration $settings ) {
			$this->settings = $settings;

			add_action( 'woocommerce_download_product', array( $this, 'download_product' ), 10, 6);

			add_filter( 'woocommerce_file_download_method', array( $this, 'download_method' ), 10, 2);

			return true;
		}

		public function download_product( $user_email, $order_key, $product_id, $user_id, $download_id, $order_id ) {
			if ( $this->is_lemoninkable_download( $download_id ) ) {
				$meta_prefix = "_li_product_{$product_id}_";

				$transaction_data = WC_LemonInk_Order_Metadata::get_transaction_data( $order_id, $meta_prefix );
				if (!$transaction_data || !$transaction_data['id']) {
					return;
				}

				$transaction = $this->settings->get_api_client()->find( 'transaction', $transaction_data['id'] );

				$product = wc_get_product( $product_id );
				$files = get_downloads( $product );

				$format = strtolower($files[$download_id]['name']);

				WC_Download_Handler::download( $transaction->getUrl($format), $product_id );
				exit;
			}
		}

		public function download_method( $method, $product_id ) {
			if ( $method == "redirect" ) {
				return $method;
			}

			$product = wc_get_product( $product_id );
			$files = get_downloads( $product );

			foreach ($files as $download_id => $file) {
				if ( $this->is_lemoninkable_download( $download_id ) ) {
					return "redirect";
				}
			}

			return $method;
		}

		private function is_lemoninkable_download( $download_id ) {
				return substr( $download_id, 0, 4 ) === '_li_';
		}
	}

endif;
