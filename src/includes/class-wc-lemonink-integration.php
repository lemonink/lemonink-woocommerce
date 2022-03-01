<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_LemonInk_Integration' ) ) :

	class WC_LemonInk_Integration extends WC_Integration {
		private $_api_key;

		public function __construct() {
			$this->id           = 'lemonink';
			$this->method_title = __( 'LemonInk Settings', 'lemonink' );

			$this->get_settings();
			$this->init_form_fields();

			add_action( 'woocommerce_update_options_integration_' . $this->id, array( $this, 'process_admin_options' ) );
		}

		public function get_settings() {
			$api_key = $this->get_api_key();

			$this->connected = !empty($api_key);

			if ( $this->connected ) {
				$this->method_description = __( 'Your store is now properly linked with LemonInk.', 'lemonink' );
			} else {
				$this->method_description = __( 'Please enter your LemonInk API key. You can generate one in your <a href="https://lemonink.co/settings/api-keys" target="_blank">account settings</a>.', 'lemonink' );
			}
		}

		public function init_form_fields() {
			$this->form_fields = array();

			if ( $this->connected ) {
				$this->form_fields['unlink'] = array(
					'label'   => __( 'Unlink from LemonInk', 'lemonink' ),
					'default' => '',
					'type'    => 'checkbox'
				);
			} else {
				$this->form_fields['api_key'] = array(
					'title'             => __( 'API key', 'lemonink' ),
					'type'              => 'string',
					'default'           => '',
					'custom_attributes' => array(
						'auto-complete'   => 'off'
					)
				);
			}
		}

		public function process_admin_options() {
			$post_data = $this->get_post_data();
			$fields = $this->get_form_fields();
			$unlink = $this->get_field_value( 'unlink', $fields['unlink'], $post_data );

			$result = parent::process_admin_options();

			if ( $unlink == 'yes' ) {
				$this->settings['api_key'] = '';
				$this->settings['unlink'] = 'no';
			}

			$this->get_settings();
			$this->init_form_fields();

			return parent::process_admin_options() || $result;
		}

		public function get_api_client() {
			return new LemonInk\Client( $this->get_api_key() );
		}

		private function get_api_key() {
			if ( is_null( $this->_api_key ) ) {
				$this->_api_key = $this->get_option( 'api_key' );
			}
			return $this->_api_key;
		}
	}

endif;
