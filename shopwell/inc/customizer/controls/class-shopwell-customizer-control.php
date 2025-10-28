<?php

/**
 * Shopwell Customizer custom control class. To be extended in other controls.
 *
 * @package     Shopwell
 * @author      Peregrine Themes
 * @since       1.0.0
 */

/**
 * Do not allow direct script access.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Shopwell_Customizer_Control' ) ) :
	/**
	 * Shopwell Customizer custom control class. To be extended in other controls.
	 */
	class Shopwell_Customizer_Control extends WP_Customize_Control {


		/**
		 * Whitelisting the "required" argument.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		public $required = array();

		/**
		 * Whitelisting the "responsive" argument.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		public $responsive = array();

		/**
		 * Set the default options.
		 *
		 * @since 1.0.0
		 * @param WP_Customize_Manager $manager Customizer bootstrap instance.
		 * @param string               $id      Control ID.
		 * @param array                $args    Default parent's arguments.
		 */
		public function __construct( $manager, $id, $args = array() ) {

			parent::__construct( $manager, $id, $args );

			if ( isset( $args['responsive'] ) && true === $args['responsive'] ) {
				$this->responsive = apply_filters(
					'shopwell_customizer_responsive_breakpoints',
					array(
						'desktop' => array(
							'title' => esc_html__( 'Desktop', 'shopwell' ),
							'icon'  => 'dashicons dashicons-desktop',
						),
						'tablet'  => array(
							'title' => esc_html__( 'Tablet', 'shopwell' ),
							'icon'  => 'dashicons dashicons-tablet',
						),
						'mobile'  => array(
							'title' => esc_html__( 'Mobile', 'shopwell' ),
							'icon'  => 'dashicons dashicons-smartphone',
						),
					)
				);
			}
		}

		/**
		 * Enqueue control related scripts/styles.
		 *
		 * @access public
		 */
		public function enqueue() {

			// Script debug.
			$shopwell_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			// Control type.
			$shopwell_type = str_replace( 'shopwell-', '', $this->type );

			/**
			 * Enqueue control stylesheet
			 */
			if ( file_exists( SHOPWELL_THEME_PATH . '/inc/customizer/controls/' . $shopwell_type . '/' . $shopwell_type . $shopwell_suffix . '.css' ) ) {
				wp_enqueue_style(
					'shopwell-' . $shopwell_type . '-control-style',
					SHOPWELL_THEME_URI . '/inc/customizer/controls/' . $shopwell_type . '/' . $shopwell_type . $shopwell_suffix . '.css',
					false,
					SHOPWELL_THEME_VERSION,
					'all'
				);
			}

			/**
			 * Enqueue our control script.
			 */
			if ( file_exists( SHOPWELL_THEME_PATH . '/inc/customizer/controls/' . $shopwell_type . '/' . $shopwell_type . $shopwell_suffix . '.js' ) ) {
				wp_enqueue_script(
					'shopwell-' . $shopwell_type . '-js',
					SHOPWELL_THEME_URI . '/inc/customizer/controls/' . $shopwell_type . '/' . $shopwell_type . $shopwell_suffix . '.js',
					array( 'jquery', 'customize-base' ),
					SHOPWELL_THEME_VERSION,
					true
				);
			}
		}

		/**
		 * Refresh the parameters passed to the JavaScript via JSON.
		 *
		 * @see WP_Customize_Control::to_json()
		 * @since  1.0.0
		 */
		public function to_json() {

			$this->json['settings'] = array();
			foreach ( $this->settings as $key => $setting ) {
				$this->json['settings'][ $key ] = $setting->id;
			}

			$this->json['type']           = $this->type;
			$this->json['priority']       = $this->priority;
			$this->json['active']         = $this->active();
			$this->json['section']        = $this->section;
			$this->json['label']          = $this->label;
			$this->json['description']    = $this->description;
			$this->json['instanceNumber'] = $this->instance_number;

			if ( 'dropdown-pages' === $this->type ) {
				$this->json['allow_addition'] = $this->allow_addition;
			}

			if ( isset( $this->default ) ) {
				$this->json['default'] = $this->default;
			} elseif ( isset( $this->setting->default ) ) {
				$this->json['default'] = $this->setting->default;
			}

			$this->json['value']      = $this->value();
			$this->json['link']       = $this->get_link();
			$this->json['id']         = $this->id;
			$this->json['required']   = $this->required;
			$this->json['responsive'] = $this->responsive;
			$this->json['inputAttrs'] = '';

			foreach ( $this->input_attrs as $attr => $value ) {
				$this->json['inputAttrs'] .= $attr . '="' . esc_attr( $value ) . '" ';
			}
		}

		/**
		 * An Underscore (JS) template for this control's responsive devices buttons.
		 *
		 * @since 1.0.0
		 */
		protected function responsive_devices() {
			?>
			<ul class="shopwell-responsive-switchers">

				<# _.each( data.responsive, function( settings, device ) { #>

					<li class="{{ device }}">
						<span class="preview-{{ device }}" data-device="{{ device }}">
							<i class="{{{ settings.icon }}}"></i>
						</span>
					</li>

					<# } ); #>
			</ul>
			<?php
		}

		/**
		 * Print the JavaScript templates used throughout custom controls.
		 *
		 * Templates are imported into the JS use wp.template.
		 *
		 * @since 1.0.0
		 */
		public static function template_units() {
			?>
			<script type="text/template" id="tmpl-shopwell-control-unit">

				<# if ( _.isObject( data.unit ) ) { #>

					<div class="shopwell-control-unit">

						<# _.each( data.unit, function( unit ){ #>
							<input
								type="radio"
								id="{{ data.id }}-{{ unit.id }}-unit"
								<# if ( false !== data.option ) { #>data-option="{{ data.option }}-unit"<# } #>
								name="{{ data.id }}-unit"
								data-min="{{ unit.min }}"
								data-max="{{ unit.max }}"
								data-step="{{ unit.step }}"
								value="{{ unit.id }}"
								<# if ( unit.id === data.selected ) { #> checked="checked"<# } #> />

							<label for="{{ data.id }}-{{ unit.id }}-unit">{{{ unit.name }}}</label>

						<# }); #>
					</div>
				<# } #>
			</script>
			<?php
		}
	}
endif;
