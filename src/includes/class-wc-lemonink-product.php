<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_LemonInk_Product' ) ) :

	class WC_LemonInk_Product {
		/**
		 * @var WC_LemonInk_Integration
		 */
		private $settings;

		public function __construct( WC_LemonInk_Integration $settings ) {
			$this->settings = $settings;

			if ( ! $this->settings->connected ) {
				return false;
			}

			add_action( 'woocommerce_product_options_downloads', array( $this, 'add_input_fields' ), 10, 0 );
			add_action( 'woocommerce_variation_options_download', array( $this, 'add_input_fields' ), 10, 3 );

			add_action( 'admin_enqueue_scripts', array( $this, 'load_scripts' ) );

			add_action( 'woocommerce_process_product_meta', array( $this, 'save_product' ) );
			add_action( 'woocommerce_save_product_variation', array( $this, 'save_product_variation' ), 10, 2 );

			add_action( 'woocommerce_process_product_meta_simple', array( $this, 'generate_product_files' ) );
			add_action( 'woocommerce_save_product_variation', array( $this, 'generate_product_files' ) );

			add_filter( 'woocommerce_downloadable_file_allowed_mime_types', array( $this, 'allow_ebook_mime_types' ) );

			add_filter( 'woocommerce_downloadable_file_exists', array( $this, 'validate_master_file_is_present' ), 10, 2);

			return true;
		}

		/**
		 *
		 */
		public function add_input_fields( $loop = null, $variation_data = null, $variation = null ) {
			global $thepostid;

			$post_id = $variation ? $variation->ID : $thepostid;

			$li_lemoninkable  = get_post_meta( $post_id, '_li_lemoninkable', true );
			$li_master_id     = get_post_meta( $post_id, '_li_master_id', true );

			if ( $li_lemoninkable === '' ) {
				$li_lemoninkable = 'yes';
			}

			woocommerce_wp_checkbox(
				array(
					'id'          => is_null( $loop ) ? '_li_lemoninkable' : "variable_li_lemoninkable${loop}",
					'name'        => is_null( $loop ) ? '_li_lemoninkable' : "variable_li_lemoninkable[${loop}]",
					'label'       => __( 'Watermark downloads using LemonInk', 'lemonink' ),
					'type'        => 'checkbox',
					'value'       => $li_lemoninkable,
					'default'     => '1'
				)
			);

			echo '<div class="options_group show_if_lemoninkable">';

			woocommerce_wp_text_input(
				array(
					'id'                => is_null( $loop ) ? '_li_master_id' : "variable_li_master_id${loop}",
					'name'              => is_null( $loop ) ? '_li_master_id' : "variable_li_master_id[${loop}]",
					'label'             => __( 'Master file ID', 'lemonink' ),
					'desc_tip'          => 'true',
					'description'       => __( 'You\'ll find the ID in "Your Files" section after logging in to LemonInk', 'lemonink' ),
					'type'              => 'text',
					'value'             => $li_master_id
				)
			);

			echo '</div>';

		}

		/**
		 *
		 */
		public function load_scripts() {
			wp_enqueue_script(
				'wc-admin-lemonink-meta-boxes',
				plugins_url( 'js/lemonink.js', dirname( __FILE__ ) ),
				array( 'jquery' ),
				'0.9.4',
				true
			);

		}
		
		public function save_product( $product_id ) {
			$post = get_post( $product_id );

			if ( 'product' === $post->post_type || 'product_variation' === $post->post_type ) {
				$params = array(
					'_li_lemoninkable' => $_POST['_li_lemoninkable'],
					'_li_master_id'    => $_POST['_li_master_id']
				);

				$this->_save_product( $product_id, $params );
			}
		}

		public function save_product_variation( $product_id, $i ) {
			$params = array(
				'_li_lemoninkable' => $_POST['variable_li_lemoninkable'][ $i ],
				'_li_master_id'    => $_POST['variable_li_master_id'][ $i ]
			);
			
			$this->_save_product( $product_id, $params );
		}

		private function _save_product( $product_id, $params ) {
			if ( isset( $params['_li_lemoninkable'] ) ) {
				update_post_meta( $product_id, '_li_lemoninkable', 'yes' );
			} else {
				update_post_meta( $product_id, '_li_lemoninkable', 'no' );
			}

			if ( isset ( $params['_li_master_id'] ) ) {
				update_post_meta( $product_id, '_li_master_id', $params['_li_master_id'] );
			}

			if ( get_post_meta( $product_id, '_li_lemoninkable', true ) == "yes" && !$this->validate_master_file_exists( false, $params['_li_master_id']) ) {
				WC_Admin_Meta_Boxes::add_error( sprintf( __( 'The master file with ID %s does not exist on the server.', 'lemonink' ), '<code>' . $params['_li_master_id'] . '</code>' ) );
			}
		}

		public function generate_product_files( $product_id ) {
			if ( get_post_meta( $product_id, '_li_lemoninkable', true ) == "yes" ) {
				$master_id = get_post_meta( $product_id, '_li_master_id', true );
				$master = $this->settings->get_api_client()->find( 'master', $master_id );

				$files = array();
				
				if ( $master ) {
					foreach ( $master->getFormats() as $format ) {
						$file = $master_id . '.' . $format;
						$download_id = substr( md5( "$file" ), 4 );
						$files["_li_$download_id"] = array(
							'name' => strtoupper($format),
							'file' => $file
						);
					}
				}

				update_post_meta( $product_id, '_downloadable_files', $files );
			}
		}

		public function allow_ebook_mime_types( $types ) {
			$ebook_types = array(
				'epub' => 'application/epub+zip',
				'mobi' => 'application/x-mobipocket-ebook'
			);

			return array_merge( $types, $ebook_types );
		}

		public function validate_master_file_is_present( $exists_on_disk, $file_url ) {
			if ( !$exists_on_disk ) {
				$master_id = preg_replace( '/\.([^.]+)$/', '', $file_url);
				
				return !!preg_match( '/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i', $master_id );
			} else {
				return $exists_on_disk;
			}
		}

		public function validate_master_file_exists( $exists_on_disk, $file_url ) {
			if ( !$exists_on_disk ) {
				$master_id = preg_replace( '/\.([^.]+)$/', '', $file_url);
				
				$master = $this->settings->get_api_client()->find( 'master', $master_id );

				return !!$master;
			} else {
				return $exists_on_disk;
			}
		}
	}

endif;
