<?php

/**
 * Shopwell Customizer radio buttonset control class.
 *
 * @package     Shopwell
 * @author      Peregrine Themes
 * @see         https://github.com/aristath/kirki
 * @since       1.0.0
 */

/**
 * Do not allow direct script access.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Shopwell_Customizer_Control_Radio_Buttonset' ) ) :
	/**
	 * Shopwell Customizer radio buttonset control class.
	 */
	class Shopwell_Customizer_Control_Radio_Buttonset extends Shopwell_Customizer_Control {

		/**
		 * The type of control being rendered
		 */
		public $type = 'shopwell-radio-buttonset';

		/**
		 * Refresh the parameters passed to the JavaScript via JSON.
		 *
		 * @see WP_Customize_Control::to_json()
		 */
		public function to_json() {
			parent::to_json();

			$this->json['choices'] = $this->choices;
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
			<div class="text_radio_button_control">
				<# if ( data.label ) { #>
					<span class="shopwell-control-heading customize-control-title shopwell-field">{{{ data.label }}}</span>
				<# } #>
				
				<# if ( data.description ) { #>
					<div class="description customize-control-description shopwell-field">{{{ data.description }}}</div>
				<# } #>

				<div class="radio-buttons">
					<# for ( key in data.choices ) { #>
						<label class="radio-button-label">
							<input {{{ data.inputAttrs }}} type="radio" value="{{ key }}" name="shopwell_radio_group-{{ data.id }}" id="{{ data.id }}-{{ key }}" {{{ data.link }}}<# if ( data.value === key ) { #> checked="checked"<# } #> />
							<span>{{ data.choices[ key ] }}</span>
						</label>
					<# } #>                   
				</div>
			</div> 
			<?php
		}
	}
endif;
