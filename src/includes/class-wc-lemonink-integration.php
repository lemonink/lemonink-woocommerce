<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_LemonInk_Integration' ) ) :

	class WC_LemonInk_Integration extends WC_Integration {
		private $_api_key;
		public $connected = false;

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
			$this->init_settings();

			$post_data = $this->get_post_data();
			$fields = $this->get_form_fields();
			$unlink = $this->get_field_value( 'unlink', $fields['unlink'], $post_data );

			if ( $unlink == 'yes' ) {
				$this->settings['api_key'] = '';
				$this->settings['unlink'] = 'no';
			} else {
				$this->settings['api_key'] = $this->get_field_value( 'api_key', $fields['api_key'], $post_data );
			}		
		
			$option_key = $this->get_option_key();
			do_action( 'woocommerce_update_option', array( 'id' => $option_key ) );
			$result = update_option( $option_key, apply_filters( 'woocommerce_settings_api_sanitized_fields_' . $this->id, $this->settings ), 'yes' );
	
			// Fetch API key from settings and reload form fields to reflect changes
			$this->forget_api_key();
			$this->get_settings();
			$this->init_form_fields();
	
			return $result;
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

		private function forget_api_key() {
			$this->_api_key = null;
		}
	}

endif;
