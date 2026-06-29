import { BaseControl } from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';

const getSelectedValues = ( value ) => ( Array.isArray( value ) ? value : [] );

export const ChannelPicker = ( { field, value, onChange } ) => {
	const selectedValues = getSelectedValues( value );
	const options = Array.isArray( field.options ) ? field.options : [];

	const toggleValue = ( optionValue ) => {
		const selected = selectedValues.includes( optionValue );

		onChange(
			selected
				? selectedValues.filter( ( item ) => item !== optionValue )
				: [ ...selectedValues, optionValue ]
		);
	};

	return (
		<BaseControl
			id={ field.id }
			label={ field.label }
			help={ field.description || undefined }
			className="wsuid-channel-picker-control"
			__nextHasNoMarginBottom
		>
			<div
				className="wsuid-channel-picker"
				role="group"
				aria-label={ field.label }
			>
				{ options.map( ( option ) => {
					const checked = selectedValues.includes( option.value );

					return (
						<button
							key={ option.value }
							type="button"
							className={
								'wsuid-channel-picker__option' +
								( checked ? ' is-selected' : '' )
							}
							aria-pressed={ checked }
							onClick={ () => toggleValue( option.value ) }
						>
							<span
								className="wsuid-channel-picker__indicator"
								aria-hidden="true"
							/>
							<span className="wsuid-channel-picker__label">
								{ option.label }
							</span>
						</button>
					);
				} ) }
			</div>
			<p className="wsuid-channel-picker__summary">
				{ selectedValues.length
					? sprintf(
							/* translators: %d: number of selected channels. */
							__( '%d selected', 'woo-settings-ui-demo' ),
							selectedValues.length
					  )
					: __( 'No channels selected', 'woo-settings-ui-demo' ) }
			</p>
		</BaseControl>
	);
};
