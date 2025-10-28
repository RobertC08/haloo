// Toggle control
wp.customize.controlConstructor[ 'shopwell-toggle' ] = wp.customize.Control.extend(
	{
		ready: function () {
			"use strict";

			var control = this;

			// Change the value
			control.container.on(
				'click',
				'.shopwell-toggle-switch',
				function () {
					control.setting.set( ! control.setting.get() );
				}
			);
		}
	}
);