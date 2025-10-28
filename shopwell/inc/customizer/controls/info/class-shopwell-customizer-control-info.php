<?php
/**
 * Shopwell Customizer info control class.
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

if ( ! class_exists( 'Shopwell_Customizer_Control_Info' ) ) :
	/**
	 * Shopwell Customizer info control class.
	 */
	class Shopwell_Customizer_Control_Info extends Shopwell_Customizer_Control {

		/**
		 * The control type.
		 *
		 * @var string
		 */
		public $type = 'shopwell-info';

		/**
		 * Custom URL.
		 *
		 * @since  1.0.0
		 * @var    string
		 */
		public $url = '';

		/**
		 * Link target.
		 *
		 * @since  1.0.0
		 * @var    string
		 */
		public $target = '_blank';

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
			wp_enqueue_style(
				'shopwell-' . $shopwell_type . '-control-style',
				SHOPWELL_THEME_URI . '/inc/customizer/controls/' . $shopwell_type . '/' . $shopwell_type . $shopwell_suffix . '.css',
				false,
				SHOPWELL_THEME_VERSION,
				'all'
			);
		}

		/**
		 * Refresh the parameters passed to the JavaScript via JSON.
		 *
		 * @see WP_Customize_Control::to_json()
		 */
		public function to_json() {
			parent::to_json();

			$this->json['url']    = $this->url;
			$this->json['target'] = $this->target;
		}

		/**
		 * An Underscore (JS) template for this control's content (but not its container).
		 *
		 * Class variables for this control class are available in the `data` JS object;
		 * export custom variables by overriding {@see WP_Customize_Control::to_json()}.
		 *
		 * @see WP_Customize_Control::print_template()
		 */
		protected function content_template() {
			?>
			<div class="shopwell-info-wrapper shopwell-control-wrapper">

				<# if ( data.label ) { #>
					<span class="shopwell-control-heading customize-control-title shopwell-field">{{{ data.label }}}</span>
				<# } #>

				<# if ( data.description ) { #>
					<div class="description customize-control-description shopwell-field shopwell-info-description">{{{ data.description }}}</div>
				<# } #>

				<a href="{{ data.url }}" class="button button-primary" target="{{ data.target }}" rel="noopener noreferrer"><?php esc_html_e( 'Learn More', 'shopwell' ); ?></a>

			</div><!-- END .shopwell-control-wrapper -->
			<?php
		}
	}
endif;
