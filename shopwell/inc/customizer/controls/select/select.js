;( function ( $ ) {

	'use strict';

	wp.customize.controlConstructor['shopwell-select'] = wp.customize.Control.extend(
		{

			ready: function () {

				'use strict';

				var control = this;

				if ( control.params.is_select2 ) {

					// Init select2.
					control.container.find( '.shopwell-select-control' ).select2(
						{
							placeholder: control.params.placeholder ?? shopwell_customizer_localized.strings.selectCategory,
							allowClear: true
						}
					);

					// Populate select2 field.
					control.container.on(
						'select2:opening',
						'.shopwell-select-control',
						function () {
							control.populate_select2();
							control.container.off( 'select2:opening', '.shopwell-select-control' );
						}
					);

					control.container.on(
						'select2:select select2:unselect select2:clear',
						'.shopwell-select-control',
						function () {
							if ( ! $( this ).val() ) {
								control.setting.set( [] );
							}
						}
					);
				}

			},

			// Populate select2.
			populate_select2: function ( e ) {

				var self = this,
				options  = '',
				selected = '',
				setting  = self.setting.get();

				if ( '' === setting['font-family']) {
					selected = ' selected="selected"';
				}

				$.each(
					self.params.choices,
					function ( id, name ) {

						selected = '';

						if ( setting && self.params.multiple && -1 !== setting.indexOf( id ) || ! self.params.multiple && id === setting ) {
							selected = ' selected="selected"';
						}

						options += '<option value="' + id + '"' + selected + '>' + name + '</option>';
					}
				);

				self.container.find( '.shopwell-select-control' ).html( options );
			}

		}
	);

}( jQuery ) );
