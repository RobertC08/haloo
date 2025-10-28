//--------------------------------------------------------------------//
// Shopwell script that handles our admin functionality.
//--------------------------------------------------------------------//

;( function( $ ) {

	'use strict';

	/**
	 * Holds most important methods that bootstrap the whole admin area.
	 *
	 * @type {Object}
	 */
	var ShopwellAdmin = {

		/**
		 * Start the engine.
		 *
		 * @since 1.0.0
		 */
		init: function() {

			// Document ready
			$( document ).ready( ShopwellAdmin.ready );

			// Window load
			$( window ).on( 'load', ShopwellAdmin.load );

			// Bind UI actions
			ShopwellAdmin.bindUIActions();

			// Trigger event when Shopwell fully loaded
			$( document ).trigger( 'shopwellReady' );
		},

		//--------------------------------------------------------------------//
		// Events
		//--------------------------------------------------------------------//

		/**
		 * Document ready.
		 *
		 * @since 1.0.0
		 */
		ready: function() {
		},


		/**
		 * Window load.
		 *
		 * @since 1.0.0
		 */
		load: function() {

			// Trigger resize once everything loaded.
			window.dispatchEvent( new Event( 'resize' ) );
		},


		/**
		 * Window resize.
		 *
		 * @since 1.0.0
		*/
		resize: function() {
		},


		//--------------------------------------------------------------------//
		// Functions
		//--------------------------------------------------------------------//


		/**
		 * Bind UI actions.
		 *
		 * @since 1.0.0
		*/
		bindUIActions: function() {
			var $wrap = $( '#wpwrap' );
			var $body = $( 'body' );
			var $this;

			$wrap.on( 'click', '.plugins .hester-btn:not(.active)', function( e ) {

				e.preventDefault();

				if ( $wrap.find( '.plugins .hester-btn.in-progress' ).length ) {
					return;
				}

				$this = $( this );

				ShopwellAdmin.pluginAction( $this );
			});

			$( document ).on( 'wp-plugin-install-success', ShopwellAdmin.pluginInstallSuccess );
			$( document ).on( 'wp-plugin-install-error',   ShopwellAdmin.pluginInstallError );
		},

		pluginAction: function( $button ) {

			$button.addClass( 'in-progress' ).attr( 'disabled', 'disabled' ).html( hester_strings.texts[ $button.data( 'action' ) + '-inprogress' ]);

			if ( 'install' === $button.data( 'action' ) ) {

				if ( wp.updates.shouldRequestFilesystemCredentials && ! wp.updates.ajaxLocked ) {
					wp.updates.requestFilesystemCredentials( event );

					$( document ).on( 'credential-modal-cancel', function() {

						$button.removeAttr( 'disabled' ).removeClass( 'in-progress' ).html( hester_strings.texts.install );

						wp.a11y.speak( wp.updates.l10n.updateCancel, 'polite' );
					});
				}

				wp.updates.installPlugin({
					slug: $button.data( 'plugin' )
				});

			} else {

				var data = {
					_ajax_nonce: hester_strings.wpnonce, // eslint-disable-line camelcase
					plugin: $button.data( 'plugin' ),
					action: 'hester-plugin-' + $button.data( 'action' )
				};

				$.post( hester_strings.ajaxurl, data, function( response ) {
					if ( response.success ) {
						if ( $button.data( 'redirect' ) ) {
							window.location.href = $button.data( 'redirect' );
						} else {
							location.reload();
						}
					} else {
						$( '.plugins .hester-btn.in-progress' ).removeAttr( 'disabled' ).removeClass( 'in-progress primary' ).addClass( 'secondary' ).html( hester_strings.texts.retry );
					}
				});
			}
		},

		pluginInstallSuccess: function( event, response ) {

			event.preventDefault();

			var $message = jQuery( event.target );
			var $init = $message.data( 'init' );
			var activatedSlug;

			if ( 'undefined' === typeof $init ) {
				activatedSlug = response.slug;
			} else {
				activatedSlug = $init;
			}

			var $button = $( '.plugins a[data-plugin="' + activatedSlug + '"]' );

			$button.data( 'action', 'activate' );

			ShopwellAdmin.pluginAction( $button );
		},

		pluginInstallError: function( event, response ) {

			event.preventDefault();

			var $message = jQuery( event.target );
			var $init = $message.data( 'init' );
			var activatedSlug;

			if ( 'undefined' === typeof $init ) {
				activatedSlug = response.slug;
			} else {
				activatedSlug = $init;
			}

			var $button = $( '.plugins a[data-plugin="' + activatedSlug + '"]' );

			$button.attr( 'disabled', 'disabled' ).removeClass( 'in-progress primary' ).addClass( 'secondary' ).html( wp.updates.l10n.installFailedShort );
		}

	}; // END var ShopwellAdmin

	ShopwellAdmin.init();
	window.shopwelladmin = ShopwellAdmin;

}( jQuery ) );
