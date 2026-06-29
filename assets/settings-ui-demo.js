( function ( window ) {
	'use strict';

	var settingsUI =
		window.wcSettingsUI ||
		( window.wc && window.wc.settingsUi ? window.wc.settingsUi : null );
	var wpElement = window.wp && window.wp.element;
	var wpComponents = window.wp && window.wp.components;
	var wpI18n = window.wp && window.wp.i18n;

	if (
		! settingsUI ||
		typeof settingsUI.registerSettingsExtension !== 'function' ||
		! wpElement ||
		! wpComponents ||
		! wpI18n
	) {
		return;
	}

	var createElement = wpElement.createElement;
	var BaseControl = wpComponents.BaseControl;
	var __ = wpI18n.__;
	var sprintf = wpI18n.sprintf;

	function getSelectedValues( value ) {
		return Array.isArray( value ) ? value : [];
	}

	function ChannelPicker( props ) {
		var field = props.field;
		var selectedValues = getSelectedValues( props.value );
		var options = Array.isArray( field.options ) ? field.options : [];

		function toggleValue( optionValue ) {
			var selected = selectedValues.indexOf( optionValue ) !== -1;

			props.onChange(
				selected
					? selectedValues.filter( function ( item ) {
							return item !== optionValue;
					  } )
					: selectedValues.concat( optionValue )
			);
		}

		return createElement(
			BaseControl,
			{
				id: field.id,
				label: field.label,
				help: field.description || undefined,
				className: 'wsuid-channel-picker-control',
				__nextHasNoMarginBottom: true,
			},
			createElement(
				'div',
				{
					className: 'wsuid-channel-picker',
					role: 'group',
					'aria-label': field.label,
				},
				options.map( function ( option ) {
					var checked = selectedValues.indexOf( option.value ) !== -1;

					return createElement(
						'button',
						{
							key: option.value,
							type: 'button',
							className:
								'wsuid-channel-picker__option' +
								( checked ? ' is-selected' : '' ),
							'aria-pressed': checked,
							onClick: function () {
								toggleValue( option.value );
							},
						},
						createElement( 'span', {
							className: 'wsuid-channel-picker__indicator',
							'aria-hidden': 'true',
						} ),
						createElement(
							'span',
							{ className: 'wsuid-channel-picker__label' },
							option.label
						)
					);
				} )
			),
			createElement(
				'p',
				{ className: 'wsuid-channel-picker__summary' },
				selectedValues.length
					? sprintf(
							/* translators: %d: number of selected channels. */
							__( '%d selected', 'woo-settings-ui-demo' ),
							selectedValues.length
					  )
					: __( 'No channels selected', 'woo-settings-ui-demo' )
			)
		);
	}

	settingsUI.registerSettingsExtension( {
		scope: {
			page: 'settings_ui_demo',
		},
		components: {
			'woo-settings-ui-demo/channel-picker': ChannelPicker,
		},
	} );
} )( window );
