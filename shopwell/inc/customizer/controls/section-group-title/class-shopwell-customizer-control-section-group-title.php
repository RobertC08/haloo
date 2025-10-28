<?php

class Shopwell_Customizer_Control_Section_Group_Title extends WP_Customize_Section {

	/**
	 * The type of customize section being rendered.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    string
	 */
	public $type = 'shopwell-section-group-title';

	/**
	 * Special categorization for the section.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    string
	 */
	public string $kind = 'default';

	/**
	 * Add custom parameters to pass to the JS via JSON.
	 *
	 * @since  1.0.0
	 * @access public
	 */

	/**
	 * Shopwell_Customizer_Control_Section_Group_Title constructor.
	 *
	 * @param WP_Customize_Manager $manager Customizer Manager.
	 * @param string               $id Control id.
	 * @param array                $args Arguments.
	 */
	public function __construct( WP_Customize_Manager $manager, $id, array $args = array() ) {
		parent::__construct( $manager, $id, $args );

		add_action( 'customize_controls_enqueue_scripts', array( $this, 'enqueue' ) );
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
		wp_enqueue_style(
			'shopwell-' . $shopwell_type . '-control-style',
			SHOPWELL_THEME_URI . '/inc/customizer/controls/' . $shopwell_type . '/' . $shopwell_type . $shopwell_suffix . '.css',
			false,
			SHOPWELL_THEME_VERSION,
			'all'
		);
	}
	public function json() {
		$json         = parent::json();
		$json['kind'] = $this->kind;
		return $json;
	}

	/**
	 * Outputs the Underscore.js template.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	protected function render_template() {
		?>
		<li id="accordion-section-{{ data.id }}" class="accordion-section <# if (data.kind==='divider') { #> shopwell-group-divider <# } else if (data.kind==='option') { #> shopwell-option-title <# } else { #> shopwell-group-title <# } #>">
		<# if ( data.title && data.title.indexOf('</div>') === -1 || data.kind === 'divider' ) { #>
				<h3>{{ data.title }}</h3>
			<# }else{ #>
				{{ data.title }}
			<# } #>

			<# if ( data.description && data.description_hidden ) { #>
			<span class="description">{{ data.description }}</span>
			<# } #>
		</li>

		<?php
	}
}
