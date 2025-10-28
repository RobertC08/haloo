<?php
/**
 * Shopwell Customizer design options control class.
 *
 * @package Shopwell
 * @author Peregrine Themes
 * @since   1.0.0
 */

/**
 * Do not allow direct script access.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Shopwell_Customizer_Control_Design_Options' ) ) :
	/**
	 * Shopwell Customizer design options control class.
	 */
	class Shopwell_Customizer_Control_Design_Options extends Shopwell_Customizer_Control {

		/**
		 * The control type.
		 *
		 * @var string
		 */
		public $type = 'shopwell-design-options';

		/**
		 * The displayed fields.
		 *
		 * @var string
		 */
		public $display = array();

		/**
		 * The control icon.
		 *
		 * @var string
		 */
		public $icon = 'edit';

		/**
		 * Set the default options.
		 *
		 * @since 1.0.0
		 * @param WP_Customize_Manager $manager Customizer bootstrap instance.
		 * @param string               $id      Control ID.
		 * @param array                $args    Default parent's arguments.
		 */
		public function __construct( $manager, $id, $args = array() ) {

			$this->display = array(
				'background' => array(),
				'color'      => array(),
				'border'     => array(),
			);

			parent::__construct( $manager, $id, $args );
		}

		/**
		 * Enqueue control related scripts/styles.
		 *
		 * @access public
		 */
		public function enqueue() {

			parent::enqueue();

			// Script debug.
			$shopwell_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			// Enqueue WordPress color picker styles.
			wp_enqueue_style( 'wp-color-picker' );

			// Enqueue background image stylesheet.
			if ( isset( $this->display['background'] ) ) {
				wp_enqueue_style(
					'shopwell-background-control-style',
					SHOPWELL_THEME_URI . '/inc/customizer/controls/background/background' . $shopwell_suffix . '.css',
					false,
					SHOPWELL_THEME_VERSION,
					'all'
				);
			}
		}

		/**
		 * Refresh the parameters passed to the JavaScript via JSON.
		 *
		 * @see WP_Customize_Control::to_json()
		 */
		public function to_json() {
			parent::to_json();

			$this->json['display'] = $this->display;
			$this->json['icon']    = $this->icon;

			$this->json['l10n'] = array(
				'background-type' => esc_html__( 'Type', 'shopwell' ),
				'gradient'        => array(
					'color-1'         => esc_html__( 'Color 1', 'shopwell' ),
					'color-2'         => esc_html__( 'Color 2', 'shopwell' ),
					'color-location'  => esc_html__( 'Location', 'shopwell' ),
					'type'            => esc_html__( 'Type', 'shopwell' ),
					'linear'          => esc_html__( 'Linear', 'shopwell' ),
					'radial'          => esc_html__( 'Radial', 'shopwell' ),
					'angle'           => esc_html__( 'Angle', 'shopwell' ),
					'position'        => esc_html__( 'Position', 'shopwell' ),
					'radial-position' => array(
						'center center' => esc_html__( 'Center Center', 'shopwell' ),
						'center left'   => esc_html__( 'Center Left', 'shopwell' ),
						'center right'  => esc_html__( 'Center Right', 'shopwell' ),
						'top center'    => esc_html__( 'Top Center', 'shopwell' ),
						'top left'      => esc_html__( 'Top Left', 'shopwell' ),
						'top right'     => esc_html__( 'Top Right', 'shopwell' ),
						'bottom center' => esc_html__( 'Bottom Center', 'shopwell' ),
						'bottom left'   => esc_html__( 'Bottom Left', 'shopwell' ),
						'bottom right'  => esc_html__( 'Bottom Right', 'shopwell' ),
					),
				),
				'image'           => array(
					'placeholder'  => __( 'No image selected', 'shopwell' ),
					'less'         => __( 'Less Settings', 'shopwell' ),
					'more'         => __( 'Advanced', 'shopwell' ),
					'select_image' => __( 'Select Image', 'shopwell' ),
					'use_image'    => __( 'Use This Image', 'shopwell' ),
				),
				'border-styles'   => array(
					'solid'  => esc_html__( 'Solid', 'shopwell' ),
					'dotted' => esc_html__( 'Dotted', 'shopwell' ),
					'dashed' => esc_html__( 'Dashed', 'shopwell' ),
				),
			);
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
			<div class="shopwell-design-options-wrapper shopwell-popup-options shopwell-control-wrapper">

				<div class="shopwell-design-options-heading">
					<# if ( data.label ) { #>
						<span class="customize-control-title">{{{ data.label }}}</span>
					<# } #>

					<# if ( data.description ) { #>
						<span class="description customize-control-description">{{{ data.description }}}</span>
					<# } #>
				</div>

				<a href="#" class="reset-defaults">
					<span class="dashicons dashicons-image-rotate"></span>
				</a>

				<a href="#" class="popup-link">
					<span class="dashicons dashicons-{{data.icon}}"></span>
				</a>

				<div class="popup-content hidden">

					<# if ( 'background' in data.display ) { #>

						<!-- Background Type -->
						<div class="shopwell-select-wrapper popup-element style-1">
							<label for="background-type-{{ data.id }}">{{{ data.l10n['background-type'] }}}</label>
							<div class="popup-input-wrapper">
								<select data-option="background-type" id="background-type-{{ data.id }}">
									<# _.each( data.display['background'], function( value, key ){ #>
										<option value="{{ key }}"<# if ( key === data.value['background-type'] ) { #> selected="selected"<# } #>>{{{ value }}}</option>
									<# }); #>
								</select>
							</div>
						</div>

						<# if ( 'color' in data.display['background'] ) { #>

							<div data-dep-field="background-type" data-dep-value="color">
								<!-- Background Color -->
								<div class="popup-element color-element style-1">
									<label for="background-color-{{ data.id }}">{{{ data.display['background']['color'] }}}</label>

									<input class="shopwell-color-control" data-option="background-color" type="text" value="{{data.value['background-color']}}" data-show-opacity="true" data-default-color="{{data.default['background-color']}}" />
								</div>
							</div>
						<# } #>

						<# if ( 'gradient' in data.display['background'] ) { #>

							<div data-dep-field="background-type" data-dep-value="gradient">

								<!-- Color 1 -->
								<div class="popup-element color-element style-1">
									<label for="gradient-color-1-{{ data.id }}">{{{ data.l10n['gradient']['color-1'] }}}</label>

									<input class="shopwell-color-control" data-option="gradient-color-1" type="text" value="{{data.value['gradient-color-1']}}" data-show-opacity="true" data-default-color="{{data.default['gradient-color-1']}}" />
								</div>

								<!-- Color 1 Location -->
								<div class="shopwell-range-wrapper popup-element color-element style-2" data-option-id="gradient-color-1-location">
									<label for="gradient-color-1-location-{{ data.id }}">{{{
									data.l10n['gradient']['color-location'] }}}</label>

									<div class="shopwell-control-wrap">
										<input
											type="range"
											value="{{data.value['gradient-color-1-location']}}"
											min="0"
											max="100"
											step="1" />

										<input
											type="number"
											class="shopwell-range-input"
											value="{{data.value['gradient-color-1-location']}}"
											data-option="gradient-color-1-location" />
									</div>
								</div>

								<!-- Color 2 -->
								<div class="popup-element color-element style-1">
									<label for="gradient-color-2-{{ data.id }}">{{{ data.l10n['gradient']['color-2'] }}}</label>

									<input class="shopwell-color-control" data-option="gradient-color-2" type="text" value="{{data.value['gradient-color-2']}}" data-show-opacity="true" data-default-color="{{data.default['gradient-color-2']}}" />
								</div>

								<!-- Color 2 Location -->
								<div class="shopwell-range-wrapper popup-element color-element style-2" data-option-id="gradient-color-2-location">
									<label for="gradient-color-2-location-{{ data.id }}">{{{ data.l10n['gradient']['color-location'] }}}</label>

									<div class="shopwell-control-wrap">
										<input
											type="range"
											value="{{data.value['gradient-color-2-location']}}"
											min="0"
											max="100"
											step="1" />

										<input
											type="number"
											class="shopwell-range-input"
											value="{{data.value['gradient-color-2-location']}}"
											data-option="gradient-color-2-location" />
									</div>
								</div>

								<!-- Type -->
								<div class="shopwell-select-wrapper popup-element style-1">
									<label for="gradient-type-{{ data.id }}">{{{ data.l10n['gradient']['type'] }}}</label>
									<div class="popup-input-wrapper">
										<select data-option="gradient-type" id="gradient-type-{{ data.id }}">
											<option value="linear"<# if ( 'linear' === data.value['gradient-type'] ) { #> selected="selected"<# } #>>{{{ data.l10n['gradient']['linear'] }}}</option>
											<option value="radial"<# if ( 'radial' === data.value['gradient-type'] ) { #> selected="selected"<# } #>>{{{ data.l10n['gradient']['radial'] }}}</option>
										</select>
									</div>
								</div>

								<!-- Linear Angle -->
								<div data-dep-field="gradient-type" data-dep-value="linear" class="shopwell-range-wrapper popup-element color-element style-2" data-option-id="gradient-linear-angle">
									<label for="gradient-angle-{{ data.id }}">{{{
									data.l10n['gradient']['angle'] }}}</label>

									<div class="shopwell-control-wrap">
										<input
											type="range"
											value="{{data.value['gradient-linear-angle']}}"
											min="0"
											max="360"
											step="1" />

										<input
											type="number"
											class="shopwell-range-input"
											value="{{data.value['gradient-linear-angle']}}"
											data-option="gradient-linear-angle" />
									</div>
								</div>

								<!-- Radial Position -->
								<div data-dep-field="gradient-type" data-dep-value="radial" class="shopwell-select-wrapper popup-element style-1">
									<label for="gradient-position-{{ data.id }}">{{{ data.l10n['gradient']['position'] }}}</label>
									<div class="popup-input-wrapper">
										<select data-option="gradient-position" id="gradient-position-{{ data.id }}">
											<# _.each( data.l10n['gradient']['radial-position'], function( value, key ){ #>
												<option value="{{ key }}"<# if ( key === data.value['gradient-position'] ) { #> selected="selected"<# } #>>{{{ value }}}</option>
											<# }); #>
										</select>
									</div>
								</div>
							</div>
						<# } #>

						<# if ( 'image' in data.display['background'] ) { #>

							<div class="shopwell-background-wrapper" data-dep-field="background-type" data-dep-value="image">

								<!-- Background Image -->
								<div class="background-image">

									<div class="attachment-media-view background-image-upload">

										<# if ( data.value['background-image'] ) { #>
											<div class="thumbnail thumbnail-image"><img src="{{ data.value['background-image'] }}" alt="" /></div>
										<# } else { #>
											<div class="placeholder"><?php esc_html_e( 'No image selected', 'shopwell' ); ?></div>
										<# } #>

										<input type="hidden" data-option="background-image" value="{{ data.value['background-image'] }}" name="background-image-{{ data.id }}" />

										<div class="actions">

											<button class="button background-image-upload-remove-button<# if ( ! data.value['background-image'] ) { #> hidden<# } #>"><?php esc_html_e( 'Remove', 'shopwell' ); ?></button>

											<button type="button" class="button background-image-upload-button">{{{ data.l10n['image']['select_image'] }}}</button>

											<a href="#" class="advanced-settings<# if ( ! data.value['background-image'] ) { #> hidden<# } #>">
												<span class="message"><?php esc_html_e( 'Advanced', 'shopwell' ); ?></span>
												<span class="dashicons dashicons-arrow-down"></span>
											</a>

										</div>
									</div>
								</div>

								<!-- Background Advanced -->
								<div class="background-image-advanced">

									<!-- Background Repeat -->
									<div class="background-repeat">
										<select {{{ data.inputAttrs }}} data-option="background-repeat">
											<option value="no-repeat"<# if ( 'no-repeat' === data.value['background-repeat'] ) { #> selected <# } #>><?php esc_html_e( 'No Repeat', 'shopwell' ); ?></option>
											<option value="repeat"<# if ( 'repeat' === data.value['background-repeat'] ) { #> selected <# } #>><?php esc_html_e( 'Repeat All', 'shopwell' ); ?></option>
											<option value="repeat-x"<# if ( 'repeat-x' === data.value['background-repeat'] ) { #> selected <# } #>><?php esc_html_e( 'Repeat Horizontally', 'shopwell' ); ?></option>
											<option value="repeat-y"<# if ( 'repeat-y' === data.value['background-repeat'] ) { #> selected <# } #>><?php esc_html_e( 'Repeat Vertically', 'shopwell' ); ?></option>
										</select>
									</div>

									<!-- Background Position -->
									<div class="background-position">

										<h4><?php esc_html_e( 'Background Position', 'shopwell' ); ?></h4>

										<div class="shopwell-range-wrapper" data-option-id="background-position-x">
											<span><?php esc_html_e( 'Horizontal', 'shopwell' ); ?></span>
											<div class="shopwell-control-wrap">
												<input
													type="range"
													data-key="background-position-x"
													value="{{ data.value['background-position-x'] }}"
													min="0"
													max="100"
													step="1" />
												<input
													type="number"
													class="shopwell-range-input"
													data-option="background-position-x"
													value="{{ data.value['background-position-x'] }}"  />
												<span class="shopwell-range-suffix">%</span>
											</div>
										</div>

										<div class="shopwell-range-wrapper" data-option-id="background-position-y">
											<span><?php esc_html_e( 'Vertical', 'shopwell' ); ?></span>
											<div class="shopwell-control-wrap">
												<input
													type="range"
													data-key="background-position-y"
													value="{{ data.value['background-position-y'] }}"
													min="0"
													max="100"
													step="1" />
												<input
													type="number"
													class="shopwell-range-input"
													data-option="background-position-y"
													value="{{ data.value['background-position-y'] }}"  />
												<span class="shopwell-range-suffix">%</span>
											</div>
										</div>

									</div>

									<!-- Background Size -->
									<div class="background-size">
										<h4><?php esc_html_e( 'Background Size', 'shopwell' ); ?></h4>
										<div class="buttonset">
											<input {{{ data.inputAttrs }}} data-option="background-size" class="switch-input screen-reader-text" type="radio" value="cover" name="_customize-bg-{{{ data.id }}}-size" id="{{ data.id }}cover" <# if ( 'cover' === data.value['background-size'] ) { #> checked="checked" <# } #>>
												<label class="switch-label" for="{{ data.id }}cover"><?php esc_html_e( 'Cover', 'shopwell' ); ?></label>
											</input>
											<input {{{ data.inputAttrs }}} data-option="background-size" class="switch-input screen-reader-text" type="radio" value="contain" name="_customize-bg-{{{ data.id }}}-size" id="{{ data.id }}contain" <# if ( 'contain' === data.value['background-size'] ) { #> checked="checked" <# } #>>
												<label class="switch-label" for="{{ data.id }}contain"><?php esc_html_e( 'Contain', 'shopwell' ); ?></label>
											</input>
											<input {{{ data.inputAttrs }}} data-option="background-size" class="switch-input screen-reader-text" type="radio" value="auto" name="_customize-bg-{{{ data.id }}}-size" id="{{ data.id }}auto" <# if ( 'auto' === data.value['background-size'] ) { #> checked="checked" <# } #>>
												<label class="switch-label" for="{{ data.id }}auto"><?php esc_html_e( 'Auto', 'shopwell' ); ?></label>
											</input>
										</div>
									</div>

									<!-- Background Attachment -->
									<div class="background-attachment">
										<h4><?php esc_html_e( 'Background Attachment', 'shopwell' ); ?></h4>
										<div class="buttonset">
											<input {{{ data.inputAttrs }}} data-option="background-attachment" lass="switch-input screen-reader-text" type="radio" value="inherit" name="_customize-bg-{{{ data.id }}}-attachment" id="{{ data.id }}inherit" <# if ( 'inherit' === data.value['background-attachment'] ) { #> checked="checked" <# } #>>
												<label class="switch-label" for="{{ data.id }}inherit"><?php esc_html_e( 'Inherit', 'shopwell' ); ?></label>
											</input>
											<input {{{ data.inputAttrs }}} data-option="background-attachment" class="switch-input screen-reader-text" type="radio" value="scroll" name="_customize-bg-{{{ data.id }}}-attachment" id="{{ data.id }}scroll" <# if ( 'scroll' === data.value['background-attachment'] ) { #> checked="checked" <# } #>>
												<label class="switch-label" for="{{ data.id }}scroll"><?php esc_html_e( 'Scroll', 'shopwell' ); ?></label>
											</input>
											<input {{{ data.inputAttrs }}} data-option="background-attachment" class="switch-input screen-reader-text" type="radio" value="fixed" name="_customize-bg-{{{ data.id }}}-attachment" id="{{ data.id }}fixed" <# if ( 'fixed' === data.value['background-attachment'] ) { #> checked="checked" <# } #>>
												<label class="switch-label" for="{{ data.id }}fixed"><?php esc_html_e( 'Fixed', 'shopwell' ); ?></label>
											</input>
										</div>
									</div>

									<!-- Background Color Overlay -->
									<div class="background-color-overlay popup-element color-element style-1">

										<label for="background-color-overlay-{{ data.id }}"><h4><?php esc_html_e( 'Overlay Color', 'shopwell' ); ?></h4></label>

										<input class="shopwell-color-control" data-option="background-color-overlay" type="text" value="{{data.value['background-color-overlay']}}" data-show-opacity="true" data-default-color="{{data.default['background-color-overlay']}}" />
									</div>

									<!-- Background Image ID -->
									<input type="hidden" data-option="background-image-id" value="{{ data.value['background-image-id'] }}" class="background-image-id" />
								</div>

							</div>
						<# } #>
					<# } #>

					<# if ( 'color' in data.display ) { #>

						<# _.each( data.display['color'], function( title, id ){ #>

							<div class="popup-element color-element style-1">
								<label for="{{ id }}-{{ data.id }}">{{{ title }}}</label>

								<input class="shopwell-color-control" data-option="{{ id }}" type="text" value="{{data.value[ id ]}}" data-show-opacity="true" data-default-color="{{data.default[ id ]}}" />
							</div>
						<# }); #>

					<# } #>

					<# if ( 'border' in data.display ) { #>

						<# if ( 'width' in data.display['border'] && 'positions' in data.display['border'] ) { #>

							<div class="customize-control-shopwell-spacing popup-element style-2">

								<label>{{{ data.display['border']['width'] }}}</label>

								<div class="shopwell-control-wrap">

									<ul class="active">

										<# _.each( data.display['border']['positions'], function( title, id ){ #>
											<li class="spacing-control-wrap spacing-input">
												<input {{{ data.inputAttrs }}} name="spacing-control-{{ id }}" type="number" data-option="border-{{ id }}-width" value="{{{ data.value[ 'border-' + id + '-width' ] }}}" />
												<span class="shopwell-spacing-label">{{{ title }}}</span>
											</li>
										<# }); #>

										<li class="spacing-control-wrap">
											<div class="spacing-link-values">
												<span class="dashicons dashicons-admin-links shopwell-spacing-linked" data-element="{{ data.id }}" title="{{ data.title }}"></span>
												<span class="dashicons dashicons-editor-unlink shopwell-spacing-unlinked" data-element="{{ data.id }}" title="{{ data.title }}"></span>
											</div>
										</li>

									</ul>
								</div>
							</div>

						<# } #>

						<# if ( 'style' in data.display['border'] ) { #>
							<!-- Border Style -->
							<div class="shopwell-select-wrapper popup-element style-1">
								<label for="border-style-{{ data.id }}">{{{ data.display['border']['style'] }}}</label>
								<div class="popup-input-wrapper">
									<select data-option="border-style" id="border-style-{{ data.id }}">
										<# _.each( data.l10n['border-styles'], function( value, key ){ #>
											<option value="{{ key }}"<# if ( key === data.value['border-style'] ) { #> selected="selected"<# } #>>{{{ value }}}</option>
										<# }); #>
									</select>
								</div>
							</div>
						<# } #>

						<# if ( 'color' in data.display['border'] ) { #>
							<!-- Border Color -->
							<div class="popup-element color-element style-1">
								<label for="border-color-{{ data.id }}">{{{ data.display['border']['color'] }}}</label>

								<input class="shopwell-color-control" data-option="border-color" type="text" value="{{data.value['border-color']}}" data-show-opacity="true" data-default-color="{{data.default['border-color']}}" />
							</div>
						<# } #>

						<# if ( 'separator' in data.display['border'] ) { #>
							<!-- Separator Color -->
							<div class="popup-element color-element style-1">
								<label for="separator-color-{{ data.id }}">{{{ data.display['border']['separator'] }}}</label>

								<input class="shopwell-color-control" data-option="separator-color" type="text" value="{{data.value['separator-color']}}" data-show-opacity="true" data-default-color="{{data.default['separator-color']}}" />
							</div>
						<# } #>

					<# } #>

				</div><!-- .popup-content -->

			</div><!-- .shopwell-control-wrapper -->
			<?php
		}
	}
endif;
