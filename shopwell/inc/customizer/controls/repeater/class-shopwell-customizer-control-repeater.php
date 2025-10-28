<?php
/**
 * Customizer Repeatable control class.
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

if ( ! class_exists( 'Shopwell_Customizer_Control_Repeater' ) ) :

	/**
	 * Customizer Repeatable control class.
	 */
	class Shopwell_Customizer_Control_Repeater extends Shopwell_Customizer_Control {
		/**
		 * The type of customize control being rendered.
		 *
		 * @since  1.0.0
		 * @access public
		 * @var    string
		 */
		public $type = 'shopwell-repeater';

		/**
		 * Repreater fields
		 *
		 * @var array
		 */
		public $fields = array();

		/**
		 * Live title id to update repeater heading
		 *
		 * @var string
		 */
		public $live_title_id = null;

		/**
		 * Title format
		 *
		 * @var string
		 */
		public $title_format = null;
		/**
		 * Defined values
		 *
		 * @var array
		 */
		public $defined_values = null;
		/**
		 * Key Id
		 *
		 * @var string
		 */
		public $id_key = null;
		/**
		 * Limited message
		 *
		 * @var string
		 */
		public $limited_msg = null;
		/**
		 * Add new button text
		 *
		 * @var string
		 */
		public $add_text = null;

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
			if ( empty( $args['fields'] ) || ! is_array( $args['fields'] ) ) {
				$args['fields'] = array();
			}
			foreach ( $args['fields'] as $key => $op ) {
				$args['fields'][ $key ]['id'] = $key;
				if ( ! isset( $op['value'] ) ) {
					if ( isset( $op['default'] ) ) {
						$args['fields'][ $key ]['value'] = $op['default'];
					} else {
						$args['fields'][ $key ]['value'] = '';
					}
				}
			}

			$this->fields         = $args['fields'];
			$this->live_title_id  = isset( $args['live_title_id'] ) ? $args['live_title_id'] : false;
			$this->defined_values = isset( $args['defined_values'] ) ? $args['defined_values'] : false;
			$this->id_key         = isset( $args['id_key'] ) ? $args['id_key'] : false;
			if ( isset( $args['title_format'] ) && '' !== $args['title_format'] ) {
				$this->title_format = $args['title_format'];
			} else {
				$this->title_format = '';
			}
			if ( isset( $args['limited_msg'] ) && '' !== $args['limited_msg'] ) {
				$this->limited_msg = $args['limited_msg'];
			} else {
				$this->limited_msg = '';
			}
			if ( ! isset( $args['max_item'] ) ) {
				$args['max_item'] = 0;
			}
			if ( ! isset( $args['allow_unlimited'] ) || false !== $args['allow_unlimited'] ) {
				$this->max_item = apply_filters( 'shopwell_reepeatable_max_item', absint( $args['max_item'] ) );
			} else {
				$this->max_item = absint( $args['max_item'] );
			}
			$this->changeable          = isset( $args['changeable'] ) && 'no' === $args['changeable'] ? 'no' : 'yes';
			$this->default_empty_title = isset( $args['default_empty_title'] ) && '' !== $args['default_empty_title'] ? $args['default_empty_title'] : esc_html__( 'Item', 'shopwell' );

			add_action( 'customize_controls_print_footer_scripts', array( __CLASS__, 'item_tpl' ), 66 );
			add_action( 'customize_controls_enqueue_scripts', array( $this, 'shopwell_customize_controls_enqueue_scripts' ) );
		}

		/**
		 * Merge fields data
		 *
		 * @param array $array_value field values.
		 * @param array $array_default default field values.
		 * @return array
		 */
		public function merge_data( $array_value, $array_default ) {
			if ( ! $this->id_key ) {
				return $array_value;
			}
			if ( ! is_array( $array_value ) ) {
				$array_value = array();
			}
			if ( ! is_array( $array_default ) ) {
				$array_default = array();
			}
			$new_array = array();
			foreach ( $array_value as $k => $a ) {
				if ( is_array( $a ) ) {
					if ( isset( $a[ $this->id_key ] ) && '' !== $a[ $this->id_key ] ) {
						$new_array[ $a[ $this->id_key ] ] = $a;
					} else {
						$new_array[ $k ] = $a;
					}
				}
			}
			foreach ( $array_default as $k => $a ) {
				if ( is_array( $a ) && isset( $a[ $this->id_key ] ) ) {
					if ( ! isset( $new_array[ $a[ $this->id_key ] ] ) ) {
						$new_array[ $a[ $this->id_key ] ] = $a;
					}
				}
			}
			return array_values( $new_array );
		}

		/**
		 * Refresh the parameters passed to the JavaScript via JSON.
		 *
		 * @see WP_Customize_Control::to_json()
		 */
		public function to_json() {
			parent::to_json();
			$value = $this->value();
			if ( is_string( $value ) ) {
				$value = json_decode( $value, true );
			}
			if ( empty( $value ) ) {
				$value = $this->defined_values;
			} elseif ( is_array( $this->defined_values ) && ! empty( $this->defined_values ) ) {
				$value = $this->merge_data( $value, $this->defined_values );
			}

			$this->json['live_title_id']       = $this->live_title_id;
			$this->json['title_format']        = $this->title_format;
			$this->json['max_item']            = $this->max_item;
			$this->json['limited_msg']         = $this->limited_msg;
			$this->json['changeable']          = $this->changeable;
			$this->json['default_empty_title'] = $this->default_empty_title;
			$this->json['add_text']            = $this->add_text ?? __( 'Add new item', 'shopwell' );
			$this->json['value']               = $value;
			$this->json['id_key']              = $this->id_key;
			$this->json['fields']              = $this->fields;

			$this->json['l10n'] = array(
				'image' => array(
					'placeholder'  => __( 'No image selected', 'shopwell' ),
					'less'         => __( 'Less Settings', 'shopwell' ),
					'more'         => __( 'Advanced', 'shopwell' ),
					'select_image' => __( 'Select Image', 'shopwell' ),
					'use_image'    => __( 'Use This Image', 'shopwell' ),
				),
			);
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

			// Enqueue background image stylesheet.
			if ( in_array( 'button_set', array_values( array_column( $this->fields, 'type' ) ), true ) ) {
				wp_enqueue_style(
					'shopwell-background-alignment-style',
					SHOPWELL_THEME_URI . '/inc/customizer/controls/alignment/alignment' . $shopwell_suffix . '.css',
					false,
					SHOPWELL_THEME_VERSION,
					'all'
				);
			}

			// Enqueue background image stylesheet.
			if ( in_array( 'link', array_values( array_column( $this->fields, 'type' ) ), true ) ) {
				wp_enqueue_script( 'wplink' );
				wp_enqueue_style( 'editor-buttons' );
			}

			// Enqueue background image stylesheet.
			if ( in_array( 'background', array_values( array_column( $this->fields, 'type' ) ), true ) ) {
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
		 * Item template to for repeatable
		 *
		 * @return void
		 */
		public static function item_tpl() {
			?>
		<script type="text/html" id="repeatable-js-item-tpl">
			<?php self::js_item(); ?>
		</script>
			<?php
		}

		/**
		 * Render the control to be displayed in the Customizer.
		 */
		public function content_template() {
			?>

		<# if ( data.label ) {   #>
			<div class="shopwell-control-heading customize-control-title shopwell-field shopwell-control-wrapper">
				<div class="customize-control-title">
				<span>{{{ data.label }}}</span>

				<# if ( data.description ) { #>
					<i class="shopwell-info-icon">
						<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-help-circle">
							<circle cx="12" cy="12" r="10"></circle>
							<path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
							<line x1="12" y1="17" x2="12" y2="17"></line>
						</svg>
						<span class="shopwell-tooltip">{{{ data.description }}}</span>
					</i>
				<# } #>
				</div>
			</div>
		<# } #>
		<input data-hidden-value type="hidden" value="" {{{ data.link }}} />
		<div class="form-data">
			<ul class="list-repeatable"></ul>
		</div>
		<div class="repeatable-actions">
			<span class="button-secondary add-new-repeat-item">
				{{data.add_text}}
			</span>
		</div>

			<?php
		}

		/**
		 * Repeatable field items.
		 *
		 * @return void
		 */
		public static function js_item() {
			?>

		<li class="repeatable-customize-control">
			<div class="widget">
				<div class="widget-top">
					<div class="widget-title-action">
						<a class="widget-action" href="#"></a>
					</div>
					<div class="widget-title">
						<h4 class="live-title"><?php esc_html_e( 'Item', 'shopwell' ); ?></h4>
					</div>
				</div>
				<div class="widget-inside">
					<div class="form">
						<div class="widget-content">
							<# var cond_v; #>
							<# for ( i in data ) { #>
								<# if ( ! data.hasOwnProperty( i ) ) continue; #>
								<# field = data[i]; #>
								<# if ( ! field.type ) continue; #>
								<# if ( field.type ){ #>
									<#
									if ( ! _.isEmpty( field.required  ) ) {
										#>
										<div data-field-id="{{ field.id }}" class="field--item conditionize item item-{{ field.type }} item-{{ field.id }}" data-cond="{{ JSON.stringify( field.required ) }}" >
										<#
									} else {
										#>
										<div data-field-id="{{ field.id }}"  class="field--item item item-{{ field.type }} item-{{ field.id }}" >
										<#
									}
									#>
										<# if ( field.type !== 'checkbox' ) { #>
											<# if ( field.title && field.type != 'design-options' ) { #>
												<div class="shopwell-control-heading shopwell-control-wrapper">
													<div class="customize-control-title">
														<span>{{{ field.title }}}</span>

														<# if ( field.desc ) { #>
															<i class="shopwell-info-icon">
																<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-help-circle">
																	<circle cx="12" cy="12" r="10"></circle>
																	<path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
																	<line x1="12" y1="17" x2="12" y2="17"></line>
																</svg>
																<span class="shopwell-tooltip">{{{ field.desc }}}</span>
															</i>
														<# } #>
													</div>
												</div>
											<# } #>
										<# } #>
										<# if ( field.type === 'hidden' ) { #>
											<input data-live-id="{{ field.id }}" type="hidden" value="{{ field.value }}" data-repeat-name="_items[__i__][{{ field.id }}]" class="">
										<# } else if ( field.type === 'add_by' ) { #>
											<input data-live-id="{{ field.id }}" type="hidden" value="{{ field.value }}" data-repeat-name="_items[__i__][{{ field.id }}]" class="add_by">
										<# } else if ( field.type === 'text' ) { #>
											<input data-live-id="{{ field.id }}" type="text" value="{{ field.value }}" data-repeat-name="_items[__i__][{{ field.id }}]" class="">
										<# } else if ( field.type === 'url' ) { #>
											<input data-live-id="{{ field.id }}" type="url" value="{{ field.value }}" data-repeat-name="_items[__i__][{{ field.id }}]" class="">
										<# } else if ( field.type === 'checkbox' ) { #>
											<# if ( field.title ) { #>
												<label class="checkbox-label">
													<input data-live-id="{{ field.id }}" type="checkbox" <# if ( field.value ) { #> checked="checked" <# } #> value="1" data-repeat-name="_items[__i__][{{ field.id }}]" class="">
													{{ field.title }}</label>
											<# } #>
											<# if ( field.desc ) { #>
											<p class="field-desc description">{{ field.desc }}</p>
											<# } #>
										<# } else if ( field.type === 'select' ) { #>
											<# if ( field.multiple ) { #>
												<select data-live-id="{{ field.id }}"  class="select-multiple" multiple="multiple" data-repeat-name="_items[__i__][{{ field.id }}][]">
											<# } else  { #>
												<select data-live-id="{{ field.id }}"  class="select-one" data-repeat-name="_items[__i__][{{ field.id }}]">
											<# } #>
												<# for ( k in field.options ) { #>
													<# if ( _.isArray( field.value ) ) { #>
														<option <# if ( _.contains( field.value , k ) ) { #> selected="selected" <# } #>  value="{{ k }}">{{ field.options[k] }}</option>
													<# } else { #>
														<option <# if ( field.value == k ) { #> selected="selected" <# } #>  value="{{ k }}">{{ field.options[k] }}</option>
													<# } #>
												<# } #>
											</select>
										<# } else if ( field.type === 'radio' ) { #>
											<# for ( k in field.options ) { #>
												<# if ( field.options.hasOwnProperty( k ) ) { #>
													<label>
														<input data-live-id="{{ field.id }}"  type="radio" <# if ( field.value == k ) { #> checked="checked" <# } #> value="{{ k }}" data-repeat-name="_items[__i__][{{ field.id }}]" class="widefat">
														{{ field.options[k] }}
													</label>
												<# } #>
											<# } #>
										<# } else if(field.type === 'link') { #>

											<div class="shopwell-field shopwell-field-link<# if ( field.value.url ) { #> -value<# } #> <# if ( field.value.target ) { #> -external<# } #>" data-name="link" data-type="link">
												<div class="shopwell-input">
													<div class="shopwell">

														<div class="shopwell-hidden">
															<a class="link-node" href="{{ field.value.url }}" target="{{ field.value.target }}">{{ field.value.title }}</a>
															<input type="hidden" class="input-title widefat" data-repeat-name="_items[__i__][{{ field.id }}][title]" value="{{ field.value.title }}">
															<input type="hidden" class="input-url widefat" data-repeat-name="_items[__i__][{{ field.id }}][url]" value="{{ field.value.url }}" data-live-id="{{ field.id }}">
															<input type="hidden" class="input-target widefat" data-repeat-name="_items[__i__][{{ field.id }}][target]" value="{{ field.value.target }}">
														</div>

														<a href="#" class="button" data-name="add" target=""><?php esc_html_e( 'Select Link', 'shopwell' ); ?></a>

														<div class="link-wrap">
															<span class="link-title">{{ field.value.title }}</span>
															<# var url = field.value.url?.length > 25 ? field.value.url.substring(0, 22) + '...' :  field.value.url #>
															<a class="link-url" href="{{ field.value.url }}" target="{{ field.value.target }}" title="{{ field.value.url }}">{{ url }}</a>
															<i class="shopwell-icon -link-ext" title="Opens in a new window/tab"></i>
															<a class="shopwell-icon -pencil -clear" data-name="edit" href="#" title="Edit"></a>
															<a class="shopwell-icon -cancel -clear" data-name="remove" href="#" title="Remove"></a>
														</div>
													</div>
												</div>
											</div>

										<# } else if ( field.type === 'button_set' ) { #>
											<div class="shopwell-alignment-control button_set">
												<div class="button-group shopwell-middle">
											<# for ( k in field.options ) { #>
												<# if ( field.options.hasOwnProperty( k ) ) { #>
													<label class="shopwell-{{ k }}">
														<input data-live-id="{{ field.id }}" class="screen-reader-text" data-repeat-name="_items[__i__][{{ field.id }}]" type="radio" <# if ( field.value == k ) { #> checked="checked" <# } #> value="{{ k }}" >
														<span class="button display-options position">
															<span class="{{ field.options[k] }}" aria-hidden="true"></span>
														</span>
														<span class="screen-reader-text">{{{ k }}}</span>
													</label>
												<# } #>
											<# } #>
												</div>
											</div>
										<# } else if ( field.type == 'color' || field.type == 'coloralpha'  ) { #>
											<# if ( field.value !='' ) { field.value = '#'+field.value ; }  #>
											<input data-live-id="{{ field.id }}" data-show-opacity="true" type="text" value="{{ field.value }}" data-repeat-name="_items[__i__][{{ field.id }}]" class="color-field c-{{ field.type }} alpha-color-control">
										<# } else if ( field.type == 'media' ) { #>
											<# if ( !field.media  || field.media == '' || field.media =='image' ) {  #>
												<input type="hidden" value="{{ field.value.url }}" data-repeat-name="_items[__i__][{{ field.id }}][url]" class="image_url widefat">
											<# } else { #>
												<input type="text" value="{{ field.value.url }}" data-repeat-name="_items[__i__][{{ field.id }}][url]" class="image_url widefat">
											<# } #>
											<input type="hidden" data-live-id="{{ field.id }}"  value="{{ field.value.id }}" data-repeat-name="_items[__i__][{{ field.id }}][id]" class="image_id widefat">
											<# if ( !field.media  || field.media == '' || field.media =='image' ) {  #>
											<div class="current <# if ( field.value.url !== '' ){ #> show <# } #>">
												<div class="container">
													<div class="attachment-media-view attachment-media-view-image landscape">
														<div class="thumbnail thumbnail-image">
															<# if ( field.value.url !== '' ){ #>
																<img src="{{ field.value.url }}" alt="">
															<# } #>
														</div>
													</div>
												</div>
											</div>
											<# } #>
											<div class="actions">
												<button class="button remove-button " <# if ( ! field.value.url ){ #> style="display:none"; <# } #> type="button"><?php esc_html_e( 'Remove', 'shopwell' ); ?></button>
												<button class="button upload-button" data-media="{{field.media}}" data-add-txt="<?php esc_attr_e( 'Add', 'shopwell' ); ?>" data-change-txt="<?php esc_attr_e( 'Change', 'shopwell' ); ?>" type="button"><# if ( ! field.value.url  ){ #> <?php esc_html_e( 'Add', 'shopwell' ); ?> <# } else { #> <?php esc_html_e( 'Change', 'shopwell' ); ?> <# } #> </button>
												<div style="clear:both"></div>
											</div>
										<# } else if ( field.type == 'textarea' || field.type == 'editor' ) { #>
											<textarea rows="5" data-live-id="{{{ field.id }}}" data-repeat-name="_items[__i__][{{ field.id }}]">{{ field.value }}</textarea>
										<# } else if ( field.type == 'icon'  ) { #>
											<#
												var icon_class = field.value;
												if ( icon_class.indexOf( 'fa-' ) != 0 ) {
													icon_class = 'fa-' + field.value;
												} else {
													icon_class = icon_class.replace( 'fa ', '' );
												}
												icon_class = icon_class.replace( 'fa-fa', '' );
												#>
											<div class="icon-wrapper">
												<i class="fa {{ icon_class }}"></i>
												<input data-live-id="{{ field.id }}" type="hidden" value="{{ field.value }}" data-repeat-name="_items[__i__][{{ field.id }}]" class="">
											</div>
											<a href="#" class="remove-icon"><?php esc_html_e( 'Remove', 'shopwell' ); ?></a>
											<# } else if(field.type == 'gradient'){ #>

												<div data-dep-field="background-type" data-dep-value="gradient">

													<!-- Color 1 -->
													<div class="popup-element color-element style-1">
														<label for="gradient-color-1-{{ field.id }}"><?php esc_html_e( 'Color 1', 'shopwell' ); ?></label>
														<input data-live-id="{{ field.id }}" type="text" value="{{ field.value['gradient-color-1'] }}" data-repeat-name="_items[__i__][{{ field.id }}][gradient-color-1]" class="color-field c-coloralpha alpha-color-control">
													</div>

													<!-- Color 1 Location -->
													<div class="shopwell-range-wrapper popup-element color-element style-2" data-option-id="gradient-color-1-location">
														<label for="gradient-color-1-location-{{ field.id }}"><?php esc_html_e( 'Color location', 'shopwell' ); ?></label>

														<div class="shopwell-control-wrap">
															<input
																type="range"
																value="{{field.value['gradient-color-1-location']}}"
																data-repeat-name="_items[__i__][{{ field.id }}][gradient-color-1-location]"
																min="0"
																max="100"
																step="1" />

															<input
																type="number"
																class="shopwell-range-input"
																value="{{field.value['gradient-color-1-location']}}"
																data-repeat-name="_items[__i__][{{ field.id }}][gradient-color-1-location]"
																data-option="gradient-color-1-location" />
														</div>
													</div>

													<!-- Color 2 -->
													<div class="popup-element color-element style-1">
														<label for="gradient-color-2-{{ field.id }}"><?php esc_html_e( 'Color 2', 'shopwell' ); ?></label>

														<input class="color-field c-coloralpha alpha-color-control" data-repeat-name="_items[__i__][{{ field.id }}][gradient-color-2]"  type="text"  value="{{field.value['gradient-color-2']}}" data-show-opacity="true" />
													</div>

													<!-- Color 2 Location -->
													<div class="shopwell-range-wrapper popup-element color-element style-2" data-option-id="gradient-color-2-location">
														<label for="gradient-color-2-location-{{ field.id }}"><?php esc_html_e( 'Color location', 'shopwell' ); ?></label>

														<div class="shopwell-control-wrap">
															<input
																type="range"
																value="{{field.value['gradient-color-2-location']}}"
																data-repeat-name="_items[__i__][{{ field.id }}][gradient-color-2-location]"
																min="0"
																max="100"
																step="1" />

															<input
																type="number"
																class="shopwell-range-input"
																value="{{field.value['gradient-color-2-location']}}"
																data-repeat-name="_items[__i__][{{ field.id }}][gradient-color-2-location]"
																data-option="gradient-color-2-location" />
														</div>
													</div>

													<!-- Type -->
													<div class="shopwell-select-wrapper popup-element style-1">
														<label for="gradient-type-{{ field.id }}"><?php esc_html_e( 'Gradient type', 'shopwell' ); ?></label>
														<div class="popup-input-wrapper">
															<select data-option="gradient-type" id="gradient-type-{{ field.id }}" data-repeat-name="_items[__i__][{{ field.id }}][gradient-type]">
																<option value="linear"<# if ( 'linear' === field.value['gradient-type'] ) { #> selected="selected"<# } #>><?php esc_html_e( 'Linear', 'shopwell' ); ?></option>
																<option value="radial"<# if ( 'radial' === field.value['gradient-type'] ) { #> selected="selected"<# } #>><?php esc_html_e( 'Radial', 'shopwell' ); ?></option>
															</select>
														</div>
													</div>

													<!-- Linear Angle -->
													<div data-dep-field="gradient-type" data-dep-value="linear" class="shopwell-range-wrapper popup-element color-element style-2" data-option-id="gradient-linear-angle">
														<label for="gradient-angle-{{ field.id }}"><?php esc_html_e( 'Angle', 'shopwell' ); ?></label>

														<div class="shopwell-control-wrap">
															<input
																type="range"
																data-repeat-name="_items[__i__][{{ field.id }}][gradient-angle]"
																value="{{field.value['gradient-linear-angle']}}"
																min="0"
																max="360"
																step="1" />

															<input
																type="number"
																class="shopwell-range-input"
																data-repeat-name="_items[__i__][{{ field.id }}][gradient-angle]"
																value="{{field.value['gradient-linear-angle']}}"
																data-option="gradient-linear-angle" />
														</div>
													</div>

													<!-- Radial Position -->
													<div data-dep-field="gradient-type" data-dep-value="radial" class="shopwell-select-wrapper popup-element style-1">
														<label for="gradient-position-{{ field.id }}"><?php esc_html_e( 'Position', 'shopwell' ); ?></label>
														<div class="popup-input-wrapper">
															<!-- <# var choices =
																{'center center':'Center Center',
																'center left':'Center Left',
																'center right': 'Center Right' ,
																'top center':'Top Center',
																'top left':'Top Left',
																'top right' : 'Top Right',
																'bottom center' : 'Bottom Center',
																'bottom left' : 'Bottom Left',
																'bottom right' : 'Bottom Right'}
																; #>
															<select data-option="gradient-position" id="gradient-position-{{ field.id }}">
															<# _.each( choices, function( value, key ){#>
																<option value="{{ key }}"<# if ( key === field.value['gradient-position'] ) { #> selected="selected"<# } #>>{{{ value }}}</option>
															<# }); #>
															</select> -->

															<?php
																$choices = array(
																	'center center' => esc_html__( 'Center Center', 'shopwell' ),
																	'center left' => esc_html__( 'Center Left', 'shopwell' ),
																	'center right' => esc_html__( 'Center Right', 'shopwell' ),
																	'top center' => esc_html__( 'Top Center', 'shopwell' ),
																	'top left' => esc_html__( 'Top Left', 'shopwell' ),
																	'top right' => esc_html__( 'Top Right', 'shopwell' ),
																	'bottom center' => esc_html__( 'Bottom Center', 'shopwell' ),
																	'bottom left' => esc_html__( 'Bottom Left', 'shopwell' ),
																	'bottom right' => esc_html__( 'Bottom Right', 'shopwell' ),
																);
																?>

															<select data-option="gradient-position" id="gradient-position-{{ field.id }}"  data-repeat-name="_items[__i__][{{ field.id }}][gradient-position]">
																<?php foreach ( $choices  as $key => $value ) { ?>
																	<option value="<?php echo esc_attr( $key ); ?>"<# if ( '<?php echo esc_attr( $key ); ?>' === field.value['gradient-position'] ) { #> selected="selected"<# } #>><?php echo esc_html( $value ); ?></option>
																<?php } ?>
															</select>

														</div>
													</div>
												</div>
												<# } else if(field.type == 'design-options') { #>
													<div class="shopwell-design-options-wrapper shopwell-popup-options shopwell-control-wrapper">
													<div class="shopwell-design-options-heading">
														<# if ( field.title ) { #>
															<span class="customize-control-title">{{{ field.title }}}</span>
														<# } #>

														<# if ( field.desc ) { #>
															<span class="description customize-control-description">{{{ field.desc }}}</span>
														<# } #>
													</div>

													<!-- <a href="#" class="reset-defaults">
														<span class="dashicons dashicons-image-rotate"></span>
													</a> -->

													<a href="#" class="popup-link">
														<span class="dashicons dashicons-edit"></span>
													</a>

													<div class="hidden popup-content">

														<# if ( 'background' in field.display ) { #>

															<!-- Background Type -->
															<div class="shopwell-select-wrapper popup-element style-1">
																<label for="background-type-{{ field.id }}"><?php esc_html_e( 'Background type', 'shopwell' ); ?></label>
																<div class="popup-input-wrapper">
																	<select data-live-id="{{{ field.id }}}" data-repeat-name="_items[__i__][{{ field.id }}][background-type]" data-option="background-type" id="background-type-{{ field.id }}">
																		<# _.each( field.display['background'], function( value, key ){ #>
																			<option value="{{ key }}"<# if ( key === field.value['background-type'] ) { #> selected="selected"<# } #>>{{{ value }}}</option>
																		<# }); #>
																	</select>
																</div>
															</div>

															<# if ( 'color' in field.display['background'] ) { #>

																<div data-dep-field="background-type" data-dep-value="color">
																	<!-- Background Color -->
																	<div class="popup-element color-element style-1">
																		<label for="background-color-{{ field.id }}"><?php esc_html_e( 'Background color', 'shopwell' ); ?></label>

																		<input  class="color-field c-coloralpha alpha-color-control" data-live-id="{{ field.id }}" data-repeat-name="_items[__i__][{{ field.id }}][background-color]" data-option="background-color" type="text" value="{{field.value['background-color']}}" data-show-opacity="true" data-default-color="{{field.value['background-color']}}" />
																	</div>
																</div>
															<# } #>

															<# if ( 'gradient' in field.display['background'] ) { #>

																<div data-dep-field="background-type" data-dep-value="gradient">

																	<!-- Color 1 -->
																	<div class="popup-element color-element style-1">
																		<label for="gradient-color-1-{{ field.id }}"><?php esc_html_e( 'Color 1', 'shopwell' ); ?></label>

																		<input class="color-field c-coloralpha alpha-color-control" data-live-id="{{ field.id }}" data-repeat-name="_items[__i__][{{ field.id }}][gradient-color-1]" data-option="gradient-color-1" type="text" value="{{field.value['gradient-color-1']}}" data-show-opacity="true" data-default-color="{{field.value['gradient-color-1']}}" />
																	</div>

																	<!-- Color 1 Location -->
																	<div class="shopwell-range-wrapper popup-element color-element style-2" data-option-id="gradient-color-1-location">
																		<label for="gradient-color-1-location-{{ field.id }}"><?php esc_html_e( 'Color location', 'shopwell' ); ?></label>

																		<div class="shopwell-control-wrap">
																			<input
																				type="range"
																				value="{{field.value['gradient-color-1-location']}}"
																				min="0"
																				max="100"
																				step="1" />

																			<input
																				type="number"
																				class="shopwell-range-input"
																				value="{{field.value['gradient-color-1-location']}}"
																				data-repeat-name="_items[__i__][{{ field.id }}][gradient-color-1-location]"
																				data-option="gradient-color-1-location" />
																		</div>
																	</div>

																	<!-- Color 2 -->
																	<div class="popup-element color-element style-1">
																		<label for="gradient-color-2-{{ field.id }}"><?php esc_html_e( 'Color 2', 'shopwell' ); ?></label>

																		<input class="color-field c-coloralpha alpha-color-control" data-live-id="{{ field.id }}" data-repeat-name="_items[__i__][{{ field.id }}][gradient-color-2]"  data-option="gradient-color-2" type="text" value="{{field.value['gradient-color-2']}}" data-show-opacity="true" data-default-color="{{field.value['gradient-color-2']}}" />
																	</div>

																	<!-- Color 2 Location -->
																	<div class="shopwell-range-wrapper popup-element color-element style-2" data-option-id="gradient-color-2-location">
																		<label for="gradient-color-2-location-{{ field.id }}"><?php esc_html_e( 'Color location', 'shopwell' ); ?></label>

																		<div class="shopwell-control-wrap">
																			<input
																				type="range"
																				value="{{field.value['gradient-color-2-location']}}"
																				min="0"
																				max="100"
																				step="1" />

																			<input
																				type="number"
																				class="shopwell-range-input"
																				value="{{field.value['gradient-color-2-location']}}"
																				data-repeat-name="_items[__i__][{{ field.id }}][gradient-color-2-location]"
																				data-option="gradient-color-2-location" />
																		</div>
																	</div>

																	<!-- Type -->
																	<div class="shopwell-select-wrapper popup-element style-1">
																		<label for="gradient-type-{{ field.id }}"><?php esc_html_e( 'Gradient type', 'shopwell' ); ?></label>
																		<div class="popup-input-wrapper">
																			<select data-repeat-name="_items[__i__][{{ field.id }}][gradient-type]"  data-option="gradient-type" id="gradient-type-{{ field.id }}" data-live-id="{{{ field.id }}}">
																				<option value="linear"<# if ( 'linear' === field.value['gradient-type'] ) { #> selected="selected"<# } #>><?php esc_html_e( 'Linear', 'shopwell' ); ?></option>
																				<option value="radial"<# if ( 'radial' === field.value['gradient-type'] ) { #> selected="selected"<# } #>><?php esc_html_e( 'Radial', 'shopwell' ); ?></option>
																			</select>
																		</div>
																	</div>

																	<!-- Linear Angle -->
																	<div data-dep-field="gradient-type" data-dep-value="linear" class="shopwell-range-wrapper popup-element color-element style-2" data-option-id="gradient-linear-angle">
																		<label for="gradient-angle-{{ field.id }}"><?php esc_html_e( 'Angle', 'shopwell' ); ?></label>

																		<div class="shopwell-control-wrap">
																			<input
																				type="range"
																				value="{{field.value['gradient-linear-angle']}}"
																				min="0"
																				max="360"
																				step="1" />

																			<input
																				type="number"
																				class="shopwell-range-input"
																				value="{{field.value['gradient-linear-angle']}}"
																				data-repeat-name="_items[__i__][{{ field.id }}][gradient-linear-angle]"
																				data-option="gradient-linear-angle" />
																		</div>
																	</div>

																	<!-- Radial Position -->
																	<div data-dep-field="gradient-type" data-dep-value="radial" class="shopwell-select-wrapper popup-element style-1">
																		<label for="gradient-position-{{ field.id }}"><?php esc_html_e( 'Gradient position', 'shopwell' ); ?></label>
																		<div class="popup-input-wrapper">
																			<?php
																				$choices = array(
																					'center center' => esc_html__( 'Center Center', 'shopwell' ),
																					'center left' => esc_html__( 'Center Left', 'shopwell' ),
																					'center right' => esc_html__( 'Center Right', 'shopwell' ),
																					'top center' => esc_html__( 'Top Center', 'shopwell' ),
																					'top left' => esc_html__( 'Top Left', 'shopwell' ),
																					'top right' => esc_html__( 'Top Right', 'shopwell' ),
																					'bottom center' => esc_html__( 'Bottom Center', 'shopwell' ),
																					'bottom left' => esc_html__( 'Bottom Left', 'shopwell' ),
																					'bottom right' => esc_html__( 'Bottom Right', 'shopwell' ),
																				);
																				?>

																			<select data-option="gradient-position" id="gradient-position-{{ field.id }}"  data-repeat-name="_items[__i__][{{ field.id }}][gradient-position]">
																				<?php foreach ( $choices  as $key => $value ) { ?>
																					<option value="<?php echo esc_attr( $key ); ?>"<# if ( '<?php echo esc_attr( $key ); ?>' === field.value['gradient-position'] ) { #> selected="selected"<# } #> ><?php echo esc_html( $value ); ?></option>
																				<?php } ?>
																			</select>
																		</div>
																	</div>
																</div>
															<# } #>

															<# if ( 'image' in field.display['background'] ) { #>

																<div class="shopwell-background-wrapper" data-dep-field="background-type" data-dep-value="image">

																	<!-- Background Image -->
																	<div class="background-image">

																		<div class="attachment-media-view background-image-upload">

																			<# if ( field.value['background-image'] ) { #>
																				<div class="thumbnail thumbnail-image"><img src="{{ field.value['background-image'] }}" alt="" /></div>
																			<# } else { #>
																				<div class="placeholder"><?php esc_html_e( 'No image selected', 'shopwell' ); ?></div>
																			<# } #>

																			<input type="hidden" data-live-id="{{{ field.id }}}" data-repeat-name="_items[__i__][{{ field.id }}][background-image]" data-option="background-image" value="{{ field.value['background-image'] }}" />

																			<div class="actions">

																				<button class="button background-image-upload-remove-button<# if ( ! field.value['background-image'] ) { #> hidden<# } #>"><?php esc_html_e( 'Remove', 'shopwell' ); ?></button>

																				<button type="button" class="button background-image-upload-button"><?php esc_html_e( 'Select image', 'shopwell' ); ?></button>

																				<a href="#" class="advanced-settings<# if ( ! field.value['background-image'] ) { #> hidden<# } #>">
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
																			<select {{{ data.inputAttrs }}} data-live-id="{{{ field.id }}}" data-option="background-repeat" data-repeat-name="_items[__i__][{{ field.id }}][background-repeat]">
																				<option value="no-repeat"<# if ( 'no-repeat' === field.value['background-repeat'] ) { #> selected <# } #>><?php esc_html_e( 'No Repeat', 'shopwell' ); ?></option>
																				<option value="repeat"<# if ( 'repeat' === field.value['background-repeat'] ) { #> selected <# } #>><?php esc_html_e( 'Repeat All', 'shopwell' ); ?></option>
																				<option value="repeat-x"<# if ( 'repeat-x' === field.value['background-repeat'] ) { #> selected <# } #>><?php esc_html_e( 'Repeat Horizontally', 'shopwell' ); ?></option>
																				<option value="repeat-y"<# if ( 'repeat-y' === field.value['background-repeat'] ) { #> selected <# } #>><?php esc_html_e( 'Repeat Vertically', 'shopwell' ); ?></option>
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
																						value="{{ field.value['background-position-x'] }}"
																						min="0"
																						max="100"
																						step="1" />
																					<input
																						type="number"
																						class="shopwell-range-input"
																						data-option="background-position-x"
																						data-repeat-name="_items[__i__][{{ field.id }}][background-position-x]"
																						value="{{ field.value['background-position-x'] }}"  />
																					<span class="shopwell-range-suffix">%</span>
																				</div>
																			</div>

																			<div class="shopwell-range-wrapper" data-option-id="background-position-y">
																				<span><?php esc_html_e( 'Vertical', 'shopwell' ); ?></span>
																				<div class="shopwell-control-wrap">
																					<input
																						type="range"
																						data-key="background-position-y"
																						value="{{ field.value['background-position-y'] }}"
																						min="0"
																						max="100"
																						step="1" />
																					<input
																						type="number"
																						class="shopwell-range-input"
																						data-option="background-position-y"
																						data-repeat-name="_items[__i__][{{ field.id }}][background-position-y]"
																						value="{{ field.value['background-position-y'] }}"  />
																					<span class="shopwell-range-suffix">%</span>
																				</div>
																			</div>

																		</div>

																		<!-- Background Size -->
																		<div class="background-size">
																			<h4><?php esc_html_e( 'Background Size', 'shopwell' ); ?></h4>
																			<div class="buttonset">
																				<input {{{ data.inputAttrs }}} data-live-id="{{{ field.id }}}" data-repeat-name="_items[__i__][{{ field.id }}][background-size]" data-option="background-size" class="switch-input screen-reader-text" type="radio" value="cover" id="{{ field.id }}cover" <# if ( 'cover' === field.value['background-size'] ) { #> checked="checked" <# } #>>
																					<label class="switch-label" for="{{ field.id }}cover"><?php esc_html_e( 'Cover', 'shopwell' ); ?></label>
																				</input>
																				<input {{{ data.inputAttrs }}} data-live-id="{{{ field.id }}}" data-repeat-name="_items[__i__][{{ field.id }}][background-size]" data-option="background-size" class="switch-input screen-reader-text" type="radio" value="contain" id="{{ field.id }}contain" <# if ( 'contain' === field.value['background-size'] ) { #> checked="checked" <# } #>>
																					<label class="switch-label" for="{{ field.id }}contain"><?php esc_html_e( 'Contain', 'shopwell' ); ?></label>
																				</input>
																				<input {{{ data.inputAttrs }}} data-live-id="{{{ field.id }}}" data-repeat-name="_items[__i__][{{ field.id }}][background-size]" data-option="background-size" class="switch-input screen-reader-text" type="radio" value="auto" id="{{ field.id }}auto" <# if ( 'auto' === field.value['background-size'] ) { #> checked="checked" <# } #>>
																					<label class="switch-label" for="{{ field.id }}auto"><?php esc_html_e( 'Auto', 'shopwell' ); ?></label>
																				</input>
																			</div>
																		</div>

																		<!-- Background Attachment -->
																		<div class="background-attachment">
																			<h4><?php esc_html_e( 'Background Attachment', 'shopwell' ); ?></h4>
																			<div class="buttonset">
																				<input {{{ data.inputAttrs }}} data-live-id="{{{ field.id }}}" data-repeat-name="_items[__i__][{{ field.id }}][background-attachment]" data-option="background-attachment" lass="switch-input screen-reader-text" type="radio" value="inherit" id="{{ field.id }}inherit" <# if ( 'inherit' === field.value['background-attachment'] ) { #> checked="checked" <# } #>>
																					<label class="switch-label" for="{{ field.id }}inherit"><?php esc_html_e( 'Inherit', 'shopwell' ); ?></label>
																				</input>
																				<input {{{ data.inputAttrs }}} data-live-id="{{{ field.id }}}" data-repeat-name="_items[__i__][{{ field.id }}][background-attachment]" data-option="background-attachment" class="switch-input screen-reader-text" type="radio" value="scroll" id="{{ field.id }}scroll" <# if ( 'scroll' === field.value['background-attachment'] ) { #> checked="checked" <# } #>>
																					<label class="switch-label" for="{{ field.id }}scroll"><?php esc_html_e( 'Scroll', 'shopwell' ); ?></label>
																				</input>
																				<input {{{ data.inputAttrs }}} data-live-id="{{{ field.id }}}" data-repeat-name="_items[__i__][{{ field.id }}][background-attachment]" data-option="background-attachment" class="switch-input screen-reader-text" type="radio" value="fixed" id="{{ field.id }}fixed" <# if ( 'fixed' === field.value['background-attachment'] ) { #> checked="checked" <# } #>>
																					<label class="switch-label" for="{{ field.id }}fixed"><?php esc_html_e( 'Fixed', 'shopwell' ); ?></label>
																				</input>
																			</div>
																		</div>

																		<!-- Background Color Overlay -->
																		<div class="background-color-overlay popup-element color-element style-1">

																			<label for="background-color-overlay-{{ field.id }}"><h4><?php esc_html_e( 'Overlay Color', 'shopwell' ); ?></h4></label>

																			<input class="color-field c-coloralpha alpha-color-control" data-live-id="{{ field.id }}" data-repeat-name="_items[__i__][{{ field.id }}][background-color-overlay]" data-option="background-color-overlay" type="text" value="{{field.value['background-color-overlay']}}" data-show-opacity="true" data-default-color="{{field.value['background-color-overlay']}}" />
																		</div>

																		<!-- Background Image ID -->
																		<input type="hidden" data-option="background-image-id" data-repeat-name="_items[__i__][{{ field.id }}][background-image-id]" value="{{ field.value['background-image-id'] }}" class="background-image-id" />
																	</div>

																</div>
															<# } #>
														<# } #>

														<# if ( 'color' in field.display ) { #>

															<# _.each( field.display['color'], function( title, id ){ #>

																<div class="popup-element color-element style-1">
																	<label for="{{ id }}-{{ field.id }}">{{{ title }}}</label>
																	<input class="color-field c-coloralpha alpha-color-control" data-live-id="{{ field.id }}" data-repeat-name="_items[__i__][{{ field.id }}][{{ id }}]" data-option="{{ id }}" type="text" value="{{field.value[ id ]}}" data-show-opacity="true" data-default-color="{{field.value[ id ]}}" />
																</div>
															<# }); #>

														<# } #>

														<# if ( 'border' in field.display ) { #>

															<# if ( 'width' in field.display['border'] && 'positions' in field.display['border'] ) { #>

																<div class="customize-control-shopwell-spacing popup-element style-2">

																	<label>{{{ field.display['border']['width'] }}}</label>

																	<div class="shopwell-control-wrap">

																		<ul class="active">

																			<# _.each( field.display['border']['positions'], function( title, id ){ #>
																				<li class="spacing-control-wrap spacing-input">
																					<input {{{ data.inputAttrs }}} data-live-id="{{{ field.id }}}" data-repeat-name="_items[__i__][{{ field.id }}][border-{{ id }}-width]" type="number" data-option="border-{{ id }}-width" value="{{{ field.value[ 'border-' + id + '-width' ] }}}" />
																					<span class="shopwell-spacing-label">{{{ title }}}</span>
																				</li>
																			<# }); #>

																			<li class="spacing-control-wrap">
																				<div class="spacing-link-values">
																					<span class="dashicons dashicons-admin-links shopwell-spacing-linked" data-element="{{ field.id }}" title="{{ data.title }}"></span>
																					<span class="dashicons dashicons-editor-unlink shopwell-spacing-unlinked" data-element="{{ field.id }}" title="{{ data.title }}"></span>
																				</div>
																			</li>

																		</ul>
																	</div>
																</div>

															<# } #>

															<# if ( 'style' in field.display['border'] ) { #>
																<!-- Border Style -->
																<div class="shopwell-select-wrapper popup-element style-1">
																	<label for="border-style-{{ field.id }}">{{{ field.display['border']['style'] }}}</label>
																	<div class="popup-input-wrapper">
																		<select data-option="border-style" data-live-id="{{{ field.id }}}" id="border-style-{{ field.id }}" data-repeat-name="_items[__i__][{{ field.id }}][border-style]">
																			<?php
																				$choices = array(
																					'solid' => esc_html__( 'Solid', 'shopwell' ),
																					'dotted' => esc_html__( 'Dotted', 'shopwell' ),
																					'dashed' => esc_html__( 'Dashed', 'shopwell' ),
																				);
																				?>
																				<?php foreach ( $choices  as $key => $value ) { ?>
																				<option value="<?php echo esc_attr( $key ); ?>"<# if ( '<?php echo esc_attr( $key ); ?>' === field.value['border-style'] ) { #> selected="selected"<# } #>><?php echo esc_html( $value ); ?></option>
																			<?php } ?>
																		</select>
																	</div>
																</div>
															<# } #>

															<# if ( 'color' in field.display['border'] ) { #>
																<!-- Border Color -->
																<div class="popup-element color-element style-1">
																	<label for="border-color-{{ field.id }}">{{{ field.display['border']['color'] }}}</label>

																	<input class="color-field c-coloralpha alpha-color-control" data-live-id="{{ field.id }}" data-repeat-name="_items[__i__][{{ field.id }}][border-color]" data-option="border-color" type="text" value="{{field.value['border-color']}}" data-show-opacity="true" data-default-color="{{field.value['border-color']}}" />
																</div>
															<# } #>

															<# if ( 'separator' in field.display['border'] ) { #>
																<!-- Separator Color -->
																<div class="popup-element color-element style-1">
																	<label for="separator-color-{{ field.id }}">{{{ field.display['border']['separator'] }}}</label>
																	<input class="color-field c-coloralpha alpha-color-control" data-live-id="{{ field.id }}" data-repeat-name="_items[__i__][{{ field.id }}][seperator-style]" data-option="separator-color" type="text" value="{{field.value['separator-color']}}" data-show-opacity="true" data-default-color="{{field.value['separator-color']}}" />
																</div>
															<# } #>

														<# } #>

														</div><!-- .popup-content -->
													</div>
												<# } #>
												<!-- background end -->
									</div>
								<# } #>
							<# } #>
							<div class="widget-control-actions">
								<div class="alignleft">
									<span class="remove-btn-wrapper">
										<a href="#" class="repeat-control-remove" title=""><?php esc_html_e( 'Remove', 'shopwell' ); ?></a> |
									</span>
									<a href="#" class="repeat-control-close"><?php esc_html_e( 'Close', 'shopwell' ); ?></a>
								</div>
								<br class="clear">
							</div>
						</div>
					</div><!-- .form -->
				</div>
			</div>
		</li>
			<?php
		}

		/**
		 * Customizer Icon picker
		 */
		public function shopwell_customize_controls_enqueue_scripts() {
			wp_localize_script(
				'customize-controls',
				'Shopwell_Icon_Picker',
				apply_filters(
					'shopwell_icon_picker_js_setup',
					array(
						'search' => esc_html__( 'Search', 'shopwell' ),
						'fonts'  => array(
							'font-awesome' => array(
								// Name of icon.
								'name'   => esc_html__( 'Font Awesome', 'shopwell' ),
								// prefix class example for font-awesome fa-fa-{name}.
								'prefix' => '',
								// font url.
								'url'    => esc_url( add_query_arg( array( 'ver' => '6.7.1' ), get_template_directory_uri() . '/assets/css/all.css' ) ),
								// Icon class name, separated by |.
								'icons'  => 'fab fa-500px|fab fa-accessible-icon|fab fa-accusoft|fab fa-adn|fab fa-adversal|fab fa-affiliatetheme|fab fa-algolia|fab fa-amazon|fab fa-amazon-pay|fab fa-angular|fab fa-app-store|fab fa-app-store-ios|fab fa-apple|fab fa-apple-pay|fab fa-artstation|fab fa-asymmetrik|fab fa-atlassian|fab fa-audible|fab fa-autoprefixer|fab fa-avianex|fab fa-aviato|fab fa-aws|fab fa-bandcamp|fab fa-battle-net|fab fa-behance|fab fa-behance-square|fab fa-bilibili|fab fa-bimobject|fab fa-bitbucket|fab fa-bitcoin|fab fa-bity|fab fa-black-tie|fab fa-blackberry|fab fa-blogger|fab fa-blogger-b|fab fa-bluetooth|fab fa-bluetooth-b|fab fa-bootstrap|fab fa-brave|fab fa-brave-reverse|fab fa-btc|fab fa-buffer|fab fa-buromobelexperte|fab fa-buy-n-large|fab fa-buysellads|fab fa-canadian-maple-leaf|fab fa-cc-amazon-pay|fab fa-cc-amex|fab fa-cc-apple-pay|fab fa-cc-diners-club|fab fa-cc-discover|fab fa-cc-jcb|fab fa-cc-mastercard|fab fa-cc-paypal|fab fa-cc-stripe|fab fa-cc-visa|fab fa-centercode|fab fa-centos|fab fa-chrome|fab fa-chromecast|fab fa-cloudflare|fab fa-cloudscale|fab fa-cloudsmith|fab fa-cloudversify|fab fa-codepen|fab fa-codiepie|fab fa-confluence|fab fa-connectdevelop|fab fa-contao|fab fa-cotton-bureau|fab fa-cpanel|fab fa-creative-commons|fab fa-creative-commons-by|fab fa-creative-commons-nc|fab fa-creative-commons-nc-eu|fab fa-creative-commons-nc-jp|fab fa-creative-commons-nd|fab fa-creative-commons-pd|fab fa-creative-commons-pd-alt|fab fa-creative-commons-remix|fab fa-creative-commons-sa|fab fa-creative-commons-sampling|fab fa-creative-commons-sampling-plus|fab fa-creative-commons-share|fab fa-creative-commons-zero|fab fa-critical-role|fab fa-css3|fab fa-css3-alt|fab fa-cuttlefish|fab fa-d-and-d|fab fa-d-and-d-beyond|fab fa-dailymotion|fab fa-dashcube|fab fa-deezer|fab fa-delicious|fab fa-deploydog|fab fa-deskpro|fab fa-dev|fab fa-deviantart|fab fa-dhl|fab fa-diaspora|fab fa-digg|fab fa-digital-ocean|fab fa-discord|fab fa-discourse|fab fa-dochub|fab fa-docker|fab fa-draft2digital|fab fa-dribbble|fab fa-dribbble-square|fab fa-dropbox|fab fa-drupal|fab fa-dyalog|fab fa-earlybirds|fab fa-ebay|fab fa-edge|fab fa-edge-legacy|fab fa-elementor|fab fa-ello|fab fa-ember|fab fa-empire|fab fa-envira|fab fa-erlang|fab fa-ethereum|fab fa-etsy|fab fa-evernote|fab fa-expeditedssl|fab fa-facebook|fab fa-facebook-f|fab fa-facebook-messenger|fab fa-facebook-square|fab fa-fantasy-flight-games|fab fa-fedex|fab fa-fedora|fab fa-figma|fab fa-firefox|fab fa-firefox-browser|fab fa-first-order|fab fa-first-order-alt|fab fa-firstdraft|fab fa-flickr|fab fa-flipboard|fab fa-fly|fab fa-font-awesome|fab fa-font-awesome-alt|fab fa-font-awesome-flag|fab fa-fonticons|fab fa-fonticons-fi|fab fa-fort-awesome|fab fa-fort-awesome-alt|fab fa-forumbee|fab fa-foursquare|fab fa-free-code-camp|fab fa-freebsd|fab fa-fulcrum|fab fa-galactic-republic|fab fa-galactic-senate|fab fa-get-pocket|fab fa-gg|fab fa-gg-circle|fab fa-git|fab fa-git-alt|fab fa-git-square|fab fa-github|fab fa-github-alt|fab fa-github-square|fab fa-gitkraken|fab fa-gitlab|fab fa-gitlab-square|fab fa-gitter|fab fa-glide|fab fa-glide-g|fab fa-gofore|fab fa-golang|fab fa-goodreads|fab fa-goodreads-g|fab fa-google|fab fa-google-drive|fab fa-google-pay|fab fa-google-play|fab fa-google-plus|fab fa-google-plus-g|fab fa-google-plus-square|fab fa-google-wallet|fab fa-gratipay|fab fa-grav|fab fa-gripfire|fab fa-grunt|fab fa-guilded|fab fa-gulp|fab fa-hacker-news|fab fa-hacker-news-square|fab fa-hackerrank|fab fa-hashnode|fab fa-hips|fab fa-hire-a-helper|fab fa-hive|fab fa-hooli|fab fa-hornbill|fab fa-hotjar|fab fa-houzz|fab fa-html5|fab fa-hubspot|fab fa-ideal|fab fa-imdb|fab fa-innosoft|fab fa-instagram|fab fa-instagram-square|fab fa-instalod|fab fa-intercom|fab fa-internet-explorer|fab fa-invision|fab fa-ioxhost|fab fa-itch-io|fab fa-itunes|fab fa-itunes-note|fab fa-java|fab fa-jedi-order|fab fa-jenkins|fab fa-jira|fab fa-joget|fab fa-joomla|fab fa-js|fab fa-js-square|fab fa-jsfiddle|fab fa-kaggle|fab fa-keybase|fab fa-keycdn|fab fa-kickstarter|fab fa-kickstarter-k|fab fa-korvue|fab fa-laravel|fab fa-lastfm|fab fa-lastfm-square|fab fa-leanpub|fab fa-less|fab fa-line|fab fa-linkedin|fab fa-linkedin-in|fab fa-linode|fab fa-linux|fab fa-lyft|fab fa-magento|fab fa-mailchimp|fab fa-mandalorian|fab fa-markdown|fab fa-mastodon|fab fa-maxcdn|fab fa-mdb|fab fa-medapps|fab fa-medium|fab fa-medium-m|fab fa-medrt|fab fa-meetup|fab fa-megaport|fab fa-mendeley|fab fa-meta|fab fa-microblog|fab fa-microsoft|fab fa-mintbit|fab fa-mix|fab fa-mixcloud|fab fa-mixer|fab fa-mizuni|fab fa-modx|fab fa-monero|fab fa-napster|fab fa-neos|fab fa-nimblr|fab fa-node|fab fa-node-js|fab fa-npm|fab fa-ns8|fab fa-nutritionix|fab fa-octopus-deploy|fab fa-odnoklassniki|fab fa-odnoklassniki-square|fab fa-old-republic|fab fa-opencart|fab fa-openid|fab fa-opera|fab fa-optin-monster|fab fa-orcid|fab fa-osi|fab fa-padlet|fab fa-page4|fab fa-pagelines|fab fa-palfed|fab fa-patreon|fab fa-paypal|fab fa-penny-arcade|fab fa-periscope|fab fa-phabricator|fab fa-phoenix-framework|fab fa-phoenix-squadron|fab fa-php|fab fa-pied-piper|fab fa-pied-piper-alt|fab fa-pied-piper-hat|fab fa-pied-piper-pp|fab fa-pied-piper-square|fab fa-pinterest|fab fa-pinterest-p|fab fa-pinterest-square|fab fa-pixiv| fab fa-pix |fab fa-playstation|fab fa-product-hunt|fab fa-pushed|fab fa-python|fab fa-qq|fab fa-quinscape|fab fa-quora|fab fa-r-project|fab fa-raspberry-pi|fab fa-ravelry|fab fa-react|fab fa-reacteurope|fab fa-readme|fab fa-rebel|fab fa-red-river|fab fa-reddit|fab fa-reddit-alien|fab fa-reddit-square|fab fa-redhat|fab fa-renren|fab fa-replyd|fab fa-researchgate|fab fa-resolving|fab fa-rev|fab fa-rocketchat|fab fa-rockrms|fab fa-rust|fab fa-safari|fab fa-salesforce|fab fa-sass|fab fa-schlix|fab fa-scribd|fab fa-searchengin|fab fa-sellcast|fab fa-sellsy|fab fa-servicestack|fab fa-shirtsinbulk|fab fa-shopify|fab fa-shopware|fab fa-signal-messenger|fab fa-simplybuilt|fab fa-sistrix|fab fa-sith|fab fa-sketch|fab fa-skyatlas|fab fa-skype|fab fa-slack|fab fa-slack-hash|fab fa-slideshare|fab fa-snapchat|fab fa-snapchat-ghost|fab fa-snapchat-square|fab fa-soundcloud|fab fa-sourcetree|fab fa-speakap|fab fa-speaker-deck|fab fa-spotify|fab fa-square-behance|fab fa-square-dribbble|fab fa-square-facebook|fab fa-square-font-awesome|fab fa-square-font-awesome-stroke|fab fa-square-git|fab fa-square-github|fab fa-square-gitlab|fab fa-square-google-plus|fab fa-square-hacker-news|fab fa-square-instagram|fab fa-square-js|fab fa-square-lastfm|fab fa-square-letterboxd|fab fa-square-odnoklassniki|fab fa-square-pied-piper|fab fa-square-pinterest|fab fa-square-reddit|fab fa-square-snapchat|fab fa-square-steam|fab fa-square-threads|fab fa-square-twitter|fab fa-square-viadeo|fab fa-square-vimeo|fab fa-square-web-awesome-stroke|fab fa-square-whatsapp|fab fa-square-xing|fab fa-square-youtube|fab fa-squarespace|fab fa-stack-exchange|fab fa-stack-overflow|fab fa-stackpath|fab fa-staylinked|fab fa-steam|fab fa-steam-square|fab fa-steam-symbol|fab fa-sticker-mule|fab fa-strava|fab fa-stripe|fab fa-stripe-s|fab fa-studiovinari|fab fa-stumbleupon|fab fa-stumbleupon-circle|fab fa-superpowers|fab fa-supple|fab fa-suse|fab fa-swift|fab fa-symfony|fab fa-teamspeak|fab fa-telegram|fab fa-telegram-plane|fab fa-tencent-weibo|fab fa-the-red-yeti|fab fa-themeco|fab fa-themeisle|fab fa-think-peaks|fab fa-threads|fab fa-tiktok|fab fa-trade-federation|fab fa-trello|fab fa-tumblr|fab fa-tumblr-square|fab fa-twitch|fab fa-twitter|fab fa-twitter-square|fab fa-typo3|fab fa-uber|fab fa-ubuntu|fab fa-uikit|fab fa-umbraco|fab fa-uncharted|fab fa-uniregistry|fab fa-unity|fab fa-unsplash|fab fa-untappd|fab fa-ups|fab fa-usb|fab fa-usps|fab fa-ussunnah|fab fa-vaadin|fab fa-viacoin|fab fa-viadeo|fab fa-viadeo-square|fab fa-viber|fab fa-vimeo|fab fa-vimeo-square|fab fa-vimeo-v|fab fa-vine|fab fa-vk|fab fa-vnv|fab fa-vuejs|fab fa-watchman-monitoring|fab fa-waze|fab fa-web-awesome|fab fa-weebly|fab fa-weibo|fab fa-weixin|fab fa-whatsapp|fab fa-whatsapp-square|fab fa-whmcs|fab fa-wikipedia-w|fab fa-windows|fab fa-wix|fab fa-wizards-of-the-coast|fab fa-wodu|fab fa-wolf-pack-battalion|fab fa-wordpress|fab fa-wordpress-simple|fab fa-wpbeginner|fab fa-wpexplorer|fab fa-wpforms|fab fa-wpressr|fab fa-xbox|fab fa-xing|fab fa-xing-square|fab fa-y-combinator|fab fa-yahoo|fab fa-yammer|fab fa-yandex|fab fa-yandex-international|fab fa-yarn|fab fa-yelp|fab fa-yoast|fab fa-youtube|fab fa-youtube-square|fab fa-zhihu|fab fa-x-twitter',
							),
						),
					)
				)
			);
		}
	}
endif;
