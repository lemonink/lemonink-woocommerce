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

			add_action( 'woocommerce_product_options_downloads', array( $this, 'add_input_fields' ) );

			add_action( 'admin_enqueue_scripts', array( $this, 'load_scripts' ) );

			add_action( 'woocommerce_process_product_meta', array( $this, 'save_product' ) );

			add_action( 'woocommerce_process_product_meta_simple', array( $this, 'generate_product_files' ) );

			add_filter( 'woocommerce_downloadable_file_allowed_mime_types', array( $this, 'allow_ebook_mime_types' ) );

			add_filter( 'woocommerce_downloadable_file_exists', array( $this, 'validate_master_file_exists' ), 10, 2);

			return true;
		}

		/**
		 *
		 */
		public function add_input_fields() {
			global $thepostid;

			$li_lemoninkable  = get_post_meta( $thepostid, '_li_lemoninkable', true );
			$li_master_id     = get_post_meta( $thepostid, '_li_master_id', true );

			if ( $li_lemoninkable === '' ) {
				$li_lemoninkable = 'yes';
			}

			woocommerce_wp_checkbox(
				array(
					'id'          => '_li_lemoninkable',
					'label'       => __( 'Watermark downloads using LemonInk', 'woocommerce_lemonink' ),
					'type'        => 'checkbox',
					'value'       => $li_lemoninkable,
					'default'     => '1'
				)
			);

			echo '<div class="options_group show_if_lemoninkable">';

			woocommerce_wp_text_input(
				array(
					'id'                => '_li_master_id',
					'label'             => __( 'Master file ID', 'woocommerce_lemonink' ),
					'desc_tip'          => 'true',
					'description'       => __( 'You\'ll find the ID in "Your Files" section after logging in to LemonInk', 'woocommerce_lemonink' ),
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

			if ( 'product' === $post->post_type ) {
				if ( isset( $_POST['_li_lemoninkable'] ) ) {
					update_post_meta( $product_id, '_li_lemoninkable', 'yes' );
				} else {
					update_post_meta( $product_id, '_li_lemoninkable', 'no' );
				}

				if ( isset ( $_POST['_li_master_id'] ) ) {
					update_post_meta( $product_id, '_li_master_id', $_POST['_li_master_id'] );
				}

				if ( get_post_meta( $product_id, '_li_lemoninkable', true ) == "yes" && !$this->validate_master_file_exists( false, $_POST['_li_master_id']) ) {
					WC_Admin_Meta_Boxes::add_error( sprintf( __( 'The master file with ID %s does not exist on the server.', 'woocommerce' ), '<code>' . $_POST['_li_master_id'] . '</code>' ) );
				}
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
