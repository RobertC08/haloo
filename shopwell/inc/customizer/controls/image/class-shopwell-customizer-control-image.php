<?php
/**
 * Shopwell Customizer custom image control class.
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

if ( ! class_exists( 'Shopwell_Customizer_Control_Image' ) ) :
	/**
	 * Shopwell Customizer custom image control class.
	 */
	class Shopwell_Customizer_Control_Image extends Shopwell_Customizer_Control {

		/**
		 * The control type.
		 *
		 * @var string
		 */
		public $type = 'shopwell-image';

		/**
		 * Media upload strings.
		 *
		 * @since  1.0.0
		 * @var    boolean
		 */
		public $strings = array();

		/**
		 * Set the default typography options.
		 *
		 * @since 1.0.0
		 * @param WP_Customize_Manager $manager Customizer bootstrap instance.
		 * @param string               $id      Control ID.
		 * @param array                $args    Default parent's arguments.
		 */
		public function __construct( $manager, $id, $args = array() ) {

			parent::__construct( $manager, $id, $args );

			$default_strings = array(
				'selectOrUploadImage' => __( 'Select or Upload Image', 'shopwell' ),
				'useThisImage'        => __( 'Use this image', 'shopwell' ),
				'changeImage'         => __( 'Change Image', 'shopwell' ),
				'selectImage'         => __( 'Select Image', 'shopwell' ),
				'remove'              => __( 'Remove', 'shopwell' ),
			);

			$strings = isset( $args['strings'] ) ? $args['strings'] : array();

			$this->strings = wp_parse_args( $strings, $default_strings );
		}

		/**
		 * Refresh the parameters passed to the JavaScript via JSON.
		 *
		 * @see WP_Customize_Control::to_json()
		 */
		public function to_json() {
			parent::to_json();
			$this->json['value'] = $this->value();
			$this->json['link']  = $this->get_link();
			$this->json['l10n']  = $this->strings;
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
			<div class="shopwell-control-wrapper shopwell-image-wrapper">
				<# if ( data.label ) { #>
					<label class="customize-control-title">{{{ data.label }}}</label>
				<# } #>
				<# if ( data.description ) { #>
					<span class="description customize-control-description">{{{ data.description }}}</span>
				<# } #>
				<div class="shopwell-image-preview">
					<# if ( data.value ) { #>
						<img src="{{ data.value }}" alt="" />
					<# } else { #>
						<img src="" alt="" style="display:none;" />
					<# } #>
				</div>
				<div class="shopwell-image-actions attachment-media-view">
					<button type="button" class="button shopwell-upload-button <# if ( ! data.value ) { #> button-add-media <# } #>">{{{ data.value ? '<?php esc_html_e( 'Change Image', 'shopwell' ); ?>' : '<?php esc_html_e( 'Select Image', 'shopwell' ); ?>' }}}</button>
					<button type="button" class="button shopwell-remove-button" <# if ( ! data.value ) { #> style="display:none;" <# } #>><?php esc_html_e( 'Remove', 'shopwell' ); ?></button>
				</div>
			</div>
			<?php
		}
	}
endif;
?>
