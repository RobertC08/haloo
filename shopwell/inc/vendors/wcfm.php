<?php

/**
 * WooCommerce wcfm functions
 *
 * @package shopwell
 */

namespace Shopwell\Vendors;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


/**
 * Class of Vendor Dokan
 *
 * @version 1.0
 */
class WCFM {
	/**
	 * Instance
	 *
	 * @var $instance
	 */
	protected static $instance = null;

	/**
	 * Initiator
	 *
	 * @since 1.0.0
	 * @return object
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Construction function
	 *
	 * @since  1.0
	 * @return Shopwell_Vendor
	 */
	public function __construct() {
		add_filter( 'body_class', array( $this, 'body_classes' ) );
		add_filter( 'shopwell_product_summary_open_classes', array( $this, 'summary_classes' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 30 );

		// Page header
		add_filter( 'shopwell_get_default_page_header_elements', array( $this, 'page_header_elements' ) );

		$this->product_card_layout();

		add_action( 'template_redirect', array( $this, 'product_sold_by_template' ), 20 );
		add_filter( 'wcfm_enquiry_custom_fields', array( $this, 'enquiry_custom_fields' ) );
		add_filter( 'wcfmmp_is_allow_full_sold_by_linked', '__return_true' );

		add_action( 'shopwell_woocommerce_product_quickview_summary', array( $this, 'sold_by_quick_view' ), 65 );

		add_action( 'wcfm_init', array( $this, 'store_style_settings' ), 20 );

		add_action( 'after_wcfm_products_manage_linked', array( $this, 'products_custom_fields' ), 20, 2 );

		add_action( 'after_wcfm_products_manage_meta_save', array( $this, 'product_meta_save' ), 500, 2 );
	}

	/**
	 * Adds custom classes to the array of body classes.
	 *
	 * @since 1.0.0
	 *
	 * @param array $classes Classes for the body element.
	 *
	 * @return array
	 */
	public function body_classes( $classes ) {
		if ( ( function_exists( 'wcfmmp_is_stores_list_page' ) && wcfmmp_is_stores_list_page() )
			|| ( function_exists( 'wcfm_is_store_page' ) && wcfm_is_store_page() )
			&& intval( \Shopwell\Helper::get_option( 'vendor_store_style_theme' ) ) ) {
				$classes[] = 'shopwell-store-style-theme';
		}

		return $classes;
	}

	/**
	 * Adds custom classes to the array of product summary classes.
	 *
	 * @since 1.0.0
	 *
	 * @param array $classes Classes for the product summary element.
	 *
	 * @return array
	 */
	public function summary_classes( $classes ) {
		$classes = ' show-vendor';

		return $classes;
	}

	/**
	 * Enqueue styles and scripts.
	 */
	public function enqueue_scripts() {
		wp_enqueue_style( 'shopwell-wcfm', get_template_directory_uri() . '/assets/css/vendors/wcfm.css', array(), '20230203' );
	}

	/**
	 * Product Card layout
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function product_card_layout() {
		if ( ! intval( \Shopwell\Helper::get_option( 'product_card_vendor_name' ) ) ) {
			return;
		}

		$loop_layout = apply_filters( 'shopwell_product_card_layout', \Shopwell\Helper::get_option( 'product_card_layout' ) );

		switch ( $loop_layout ) {
			// Layout 1
			case '1':
				add_action( 'woocommerce_after_shop_loop_item_title', array( $this, 'vendor_name' ), 15 );
				break;

			// Layout 2
			case '2':
				add_action( 'woocommerce_after_shop_loop_item_title', array( $this, 'vendor_name' ), 51 );
				break;

			// Layout 3
			case '3':
				add_action( 'woocommerce_after_shop_loop_item', array( $this, 'vendor_name' ), 0 );
				break;

			// Layout 4
			case '4':
				add_action( 'woocommerce_shop_loop_item_title', array( $this, 'vendor_name' ), 15 );
				break;

			// Layout 5
			case '5':
				add_action( 'woocommerce_after_shop_loop_item_title', array( $this, 'vendor_name' ), 30 );
				break;

			case '6':
				add_action( 'woocommerce_after_shop_loop_item_title', array( $this, 'vendor_name' ), 30 );
				break;

			default:
				break;
		}
	}

	/**
	 * Vendor name.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function vendor_name() {
		global $product;

		if ( ! $product ) {
			return;
		}

		if ( ! function_exists( 'wcfm_get_vendor_id_by_post' ) ) {
			return;
		}

		$vendor_id = wcfm_get_vendor_id_by_post( $product->get_ID() );

		if ( ! $vendor_id ) {
			return;
		}

		$store_name = function_exists( 'wcfm_get_vendor_store_name' ) ? wcfm_get_vendor_store_name( absint( $vendor_id ) ) : '';

		$store_link = function_exists( 'wcfmmp_get_store_url' ) ? wcfmmp_get_store_url( $vendor_id ) : '#';
		$store_logo = function_exists( 'wcfm_get_vendor_store_logo_by_vendor' ) ? wcfm_get_vendor_store_logo_by_vendor( $vendor_id ) : '';
		$store_logo = $store_logo ? sprintf( "<img alt='%s' src='%s'/>", esc_attr( $store_name ), $store_logo ) : '';
		$classes    = $product->get_attributes() ? 'show-attributes' : '';

		?>

		<div class="sold-by-meta <?php echo esc_attr( $classes ); ?>">
			<a href="<?php echo esc_url( $store_link ); ?>">
				<?php echo ! empty( $store_logo ) ? $store_logo : ''; ?>
				<span class="vendor-name"><?php echo esc_html( $store_name ); ?></span>
			</a>
		</div>

		<?php
	}

	/**
	 * Enquiry Custom Fields
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function enquiry_custom_fields( $fields ) {
		if ( array_key_exists( 'wcfm_enquiry_button_position', $fields ) ) {
			unset( $fields['wcfm_enquiry_button_position'] );
		}

		return $fields;
	}

	/**
	 * Product sold by template
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function sold_by_quick_view() {
		global $WCFMmp, $product;

		$product_id = $product->get_id();

		$vendor_id = wcfm_get_vendor_id_by_post( $product_id );

		$WCFMmp->template->get_template(
			'sold-by/wcfmmp-view-sold-by-advanced.php',
			array(
				'product_id' => $product_id,
				'vendor_id'  => $vendor_id,
			)
		);
	}

	/**
	 * Product sold by template
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function product_sold_by_template() {
		global $WCFM, $WCFMmp;

		if ( ! is_singular( 'product' ) ) {
			return;
		}

		$vendor_sold_by_template = ! empty( $WCFMmp->wcfmmp_vendor ) ? $WCFMmp->wcfmmp_vendor->get_vendor_sold_by_template() : '';

		if ( $vendor_sold_by_template == 'tab' ) {
			return;
		}
		$vendor_sold_by_position = isset( $WCFMmp->wcfmmp_marketplace_options['vendor_sold_by_position'] ) ? $WCFMmp->wcfmmp_marketplace_options['vendor_sold_by_position'] : 'below_atc';

		if ( ! empty( $WCFM->wcfm_enquiry ) ) {
			remove_action( 'woocommerce_single_product_summary', array( $WCFM->wcfm_enquiry, 'wcfm_enquiry_button' ), 15 );
			remove_action( 'woocommerce_single_product_summary', array( $WCFM->wcfm_enquiry, 'wcfm_enquiry_button' ), 25 );
			remove_action( 'woocommerce_single_product_summary', array( $WCFM->wcfm_enquiry, 'wcfm_enquiry_button' ), 35 );
		}

		if ( $vendor_sold_by_position == 'bellow_title' ) {
			add_action( 'woocommerce_single_product_summary', array( $this, 'sold_by_single_product_open' ), 5 );
			if ( ! empty( $WCFMmp->frontend ) ) {
				remove_action( 'woocommerce_single_product_summary', array( $WCFMmp->frontend, 'wcfmmp_sold_by_single_product' ), 6 );
				add_action( 'woocommerce_single_product_summary', array( $WCFMmp->frontend, 'wcfmmp_sold_by_single_product' ), 5 );
			}
			if ( ! empty( $WCFM->wcfm_enquiry ) ) {
				add_action( 'woocommerce_single_product_summary', array( $WCFM->wcfm_enquiry, 'wcfm_enquiry_button' ), 5 );
			}
			add_action( 'woocommerce_single_product_summary', array( $this, 'sold_by_single_product_close' ), 5 );
		} elseif ( $vendor_sold_by_position == 'bellow_price' ) {
			add_action( 'woocommerce_single_product_summary', array( $this, 'sold_by_single_product_open' ), 20 );
			if ( ! empty( $WCFMmp->frontend ) ) {
				remove_action( 'woocommerce_single_product_summary', array( $WCFMmp->frontend, 'wcfmmp_sold_by_single_product' ), 15 );
				add_action( 'woocommerce_single_product_summary', array( $WCFMmp->frontend, 'wcfmmp_sold_by_single_product' ), 20 );
			}
			if ( ! empty( $WCFM->wcfm_enquiry ) ) {
				add_action( 'woocommerce_single_product_summary', array( $WCFM->wcfm_enquiry, 'wcfm_enquiry_button' ), 20 );
			}
			add_action( 'woocommerce_single_product_summary', array( $this, 'sold_by_single_product_close' ), 20 );
		} elseif ( $vendor_sold_by_position == 'bellow_sc' ) {
			add_action( 'woocommerce_single_product_summary', array( $this, 'sold_by_single_product_open' ), 24 );
			if ( ! empty( $WCFM->wcfm_enquiry ) ) {
				add_action( 'woocommerce_single_product_summary', array( $WCFM->wcfm_enquiry, 'wcfm_enquiry_button' ), 26 );
			}
			add_action( 'woocommerce_single_product_summary', array( $this, 'sold_by_single_product_close' ), 26 );
		} else {
			add_action( 'woocommerce_product_meta_start', array( $this, 'sold_by_single_product_open' ), 49 );
			if ( ! empty( $WCFM->wcfm_enquiry ) ) {
				add_action( 'woocommerce_product_meta_start', array( $WCFM->wcfm_enquiry, 'wcfm_enquiry_button' ), 51 );
			}
			add_action( 'woocommerce_product_meta_start', array( $this, 'sold_by_single_product_close' ), 51 );
		}
	}

	/**
	 *  Product sold by open
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function sold_by_single_product_open() {
		echo '<div class="shopwell-sold-by-template">';
	}

	/**
	 *  Product sold by open
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function sold_by_single_product_close() {
		echo '</div>';
	}

	/**
	 * Vendor header.
	 *
	 *  @return void
	 */
	public function page_header_elements( $items ) {
		if ( function_exists( 'wcfmmp_is_stores_list_page' ) && wcfmmp_is_stores_list_page() ) {
			$items = \Shopwell\Helper::get_option( 'store_list_page_header' );
		} elseif ( function_exists( 'wcfm_is_store_page' ) && wcfm_is_store_page() ) {
			$items = \Shopwell\Helper::get_option( 'store_page_page_header' );
		}

		return $items;
	}

	/**
	 * Remove style settings wcfm when enable option from theme
	 *
	 *  @return void
	 */
	public function store_style_settings( $items ) {
		global $WCFM, $WCFMmp;

		if ( ! intval( \Shopwell\Helper::get_option( 'vendor_store_style_theme' ) ) ) {
			return;
		}

		if ( empty( $WCFMmp->wcfmmp_settings ) ) {
			return;
		}

		remove_action( 'begin_wcfm_settings_form_style', array( $WCFMmp->wcfmmp_settings, 'wcfm_store_style_settings' ), 14 );
	}

	function products_custom_fields( $product_id, $product_type ) {
		global $WCFM;
		if ( in_array( 'video', (array) \Shopwell\Helper::get_option( 'wcfm_dashboard_custom_fields' ) ) ) {
			?>
			<!-- collapsible 8 - Product Video -->
			<div class="page_collapsible products_manage_video <?php echo apply_filters( 'wcfm_pm_block_class_linked', 'simple variable external grouped' ); ?> <?php echo apply_filters( 'wcfm_pm_block_custom_class_linked', '' ); ?>"
				id="wcfm_products_manage_form_linked_head"><label
					class="wcfmfa fa-video"></label><?php esc_html_e( 'Product Video', 'shopwell' ); ?><span></span>
			</div>
			<div class="wcfm-container simple variable external grouped <?php echo apply_filters( 'wcfm_pm_block_custom_class_linked', '' ); ?>">
				<div id="wcfm_products_manage_form_linked_expander" class="wcfm-content">
					<?php

					$video_url = get_post_meta( $product_id, 'video_url', true );
					$WCFM->wcfm_fields->wcfm_generate_form_field(
						apply_filters(
							'wcfm_product_manage_fields_video',
							array(
								'video_url' => array(
									'label'       => esc_html__( 'Video URL', 'shopwell' ),
									'type'        => 'text',
									'class'       => 'wcfm-text wcfm_ele simple variable external grouped booking',
									'label_class' => 'wcfm_title',
									'value'       => $video_url,
								),
							),
							$product_id
						)
					);
					?>
				</div>
			</div>
			<!-- end collapsible -->
			<div class="wcfm_clearfix"></div>
		<?php } ?>
		<?php
	}

	function product_meta_save( $new_product_id, $wcfm_products_manage_form_data ) {
		global $WCFM;

		// Video
		$video_url = ( isset( $wcfm_products_manage_form_data['video_url'] ) ) ? $wcfm_products_manage_form_data['video_url'] : '';
		update_post_meta( $new_product_id, 'video_url', $video_url );
	}
}