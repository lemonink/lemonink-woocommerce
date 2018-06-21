<?php

/**
 * Plugin Name: LemonInk
 * Plugin URI: https://lemonink.co/
 * Description: Woocommerce plugin for watermarking ebooks using LemonInk
 * Version: 0.2.6
 * Author: LemonInk
 * Author URI: https://lemonink.co/
 * Requires at least: 4.4
 * Tested up to: 4.7
 *
 * Text Domain: lemonink_woocommerce
 * Domain Path: /i18n/languages/
 *
 * @package LemonInk
 * @category Core
 * @author LemonInk
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_LemonInk' ) ) :

	class WC_LemonInk {
		protected static $instance = null;
		private $settings = null;

		public function __construct() {
			if ( in_array( 'woocommerce/woocommerce.php',
				apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
				add_action( 'plugins_loaded', array( $this, 'init' ) );
			}
		}

		public static function get_instance() {
			if ( null == self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;
		}

		public function init() {
			// Load integration
			if ( class_exists( 'WC_Integration' ) ) {
				// Include our integration class.
				include_once plugin_dir_path( __FILE__ ) . 'includes/class-wc-lemonink-integration.php';
				// Register the integration.
				add_filter( 'woocommerce_integrations', array( $this, 'add_integration' ) );

			} else {
				// @todo: handle this?
			}

			// Initialize the rest of the plugin
			add_action( 'woocommerce_init', array( $this, 'load_classes' ) );
		}

		/**
		 * Load customer settings and classes
		 */
		public function load_classes() {
			// load settings
			global $woocommerce;

			// get integration
			$integrations   = $woocommerce->integrations->get_integrations();
			$this->settings = $integrations['lemonink'];

			include_once plugin_dir_path( __FILE__ ) . "vendor/autoload.php";

			include_once plugin_dir_path( __FILE__ ) . 'includes/class-wc-lemonink-product.php';
			new WC_LemonInk_Product( $this->settings );

			include_once plugin_dir_path( __FILE__ ) . 'includes/class-wc-lemonink-order.php';
			new WC_LemonInk_Order( $this->settings );
		}

		/**
		 * @param $integrations
		 *
		 * @return array
		 */
		public function add_integration( $integrations ) {
			$integrations[] = 'WC_LemonInk_Integration';

			return $integrations;
		}
	}

	// load class when plugins are loaded
	add_action( 'plugins_loaded', array( 'WC_LemonInk', 'get_instance' ), 0 );

endif;
