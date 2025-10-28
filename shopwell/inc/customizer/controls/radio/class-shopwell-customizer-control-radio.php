<?php
/**
 * Shopwell Customizer custom radio control class.
 *
 * @package     Shopwell
 * @since       1.0.0
 */

/**
 * Do not allow direct script access.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Shopwell_Customizer_Control_Radio' ) ) :
	/**
	 * Shopwell Customizer custom radio control class.
	 */
	class Shopwell_Customizer_Control_Radio extends Shopwell_Customizer_Control {

		/**
		 * The control type.
		 *
		 * @var string
		 */
		public $type = 'shopwell-radio';

		/**
		 * The control tooltip.
		 *
		 * @var string
		 */
		public $tooltip = '';

		/**
		 * Choices for the radio buttons.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		public $choices = array();

		/**
		 * Refresh the parameters passed to the JavaScript via JSON.
		 *
		 * @see WP_Customize_Control::to_json()
		 */
		public function to_json() {
			parent::to_json();
			$this->json['choices'] = $this->choices;
			$this->json['tooltip'] = $this->tooltip;
		}

		/**
		 * An Underscore (JS) template for this control's content (but not its container).
		 *
		 * Class variables for this control class are available in the data JS object;
		 * export custom variables by overriding {@see WP_Customize_Control::to_json()}.
		 *
		 * @see WP_Customize_Control::print_template()
		 */
		protected function content_template() {
			?>
			<div class="shopwell-control-wrapper shopwell-radio-wrapper">

				<# if ( data.label ) { #>
					<div class="customize-control-title">
						<span>{{{ data.label }}}</span>
						<# if ( data.tooltip ) { #>
							<i class="shopwell-info-icon">
								<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-help-circle">
									<circle cx="12" cy="12" r="10"></circle>
									<path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
									<line x1="12" y1="17" x2="12" y2="17"></line>
								</svg>
								<span class="shopwell-tooltip">{{{ data.tooltip }}}</span>
							</i>
						<# } #>

					</div>
				<# } #>
				<# if ( data.description ) { #>
					<span id="_customize-description-{{ data.id }}" class="description customize-control-description">{{{ data.description }}}</span>
				<# } #>

				<# _.each( data.choices, function( choiceLabel, choiceValue ) { #>
					<span class="customize-inside-control-row">
						<input type="radio" name="_customize-radio-{{ data.id }}" id="_customize-radio-{{ data.id }}-{{ choiceValue }}" value="{{ choiceValue }}" {{{ data.link }}} aria-describedby="_customize-description-shopwell_maintenance_mode" <# if ( data.value === choiceValue ) { #> checked="checked" <# } #>>
						<label for="_customize-radio-{{ data.id }}-{{ choiceValue }}">{{{ choiceLabel }}}</label>
					</span>
				<# } ); #>

			</div><!-- END .shopwell-control-wrapper -->
			<?php
		}
	}
endif;
?>
