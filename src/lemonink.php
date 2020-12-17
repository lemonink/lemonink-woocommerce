<?php

/**
 * Plugin Name: LemonInk Ebook Watermarking for WooCommerce
 * Plugin URI: https://www.lemonink.co/
 * Description: Watermark EPUB, MOBI and PDF files in your WooCommerce store using the LemonInk service.
 * Version: 0.4.2
 * Author: LemonInk
 * Author URI: https://www.lemonink.co/
 * Requires at least: 4.4
 * Tested up to: 5.6
 *
 * Text Domain: lemonink
 * Domain Path: /languages/
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

			load_plugin_textdomain( 'lemonink', false, basename( dirname( __FILE__ ) ) . '/languages' );

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

			include_once plugin_dir_path( __FILE__ ) . 'includes/class-wc-lemonink-download-handler.php';
			new WC_LemonInk_Download_Handler( $this->settings );
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
