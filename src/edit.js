/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */

import { useInstanceId } from '@wordpress/compose';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, SelectControl, TextControl } from '@wordpress/components';
import { Fragment } from '@wordpress/element';
import { useEffect, useState } from '@wordpress/element';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @param {Object}   props               Properties passed to the function.
 * @param {Object}   props.attributes    Available block attributes.
 * @param {Function} props.setAttributes Function that updates individual attributes.
 *
 * @return {Element} Element to render.
 */
export default function Edit( { attributes, setAttributes } ) {
	const blockProps = useBlockProps();
	const { instanceId, label, selectedField, inputType, accessibleLabel } = attributes;
	const newInstanceId = useInstanceId( Edit );
	const [ metaKeys, setMetaKeys ] = useState([]);

	const inputTypes = [
		{ label: 'Select (Single)', value: 'select' },
		{ label: 'Checkboxes (Multiple)', value: 'checkboxes' },
	];

	useEffect(() => {
		const metaKeys = async() => {

			const data = new FormData();

			data.append( 'action', 'query_filter_get_meta_keys' );
			data.append( 'nonce', query_custom_field_filter.nonce );

			const response = await fetch( ajaxurl, {
			  method: "POST",
			  credentials: 'same-origin',
			  body: data
			} );
			const responseJson = await response.json();

			if( responseJson.success ) {
				setMetaKeys( responseJson.data );

				if( '' === selectedField && responseJson.data.length > 0 ) {
					setAttributes({
						selectedField: responseJson.data[0]
					});
				}
			}
		};

		metaKeys();
	}, []);

	if ( null === instanceId ) {
		setAttributes( { instanceId: newInstanceId } );
	}

	return (
		<Fragment>
			<InspectorControls>
				<PanelBody title="Settings" initialOpen={ true }>
					{ metaKeys.length > 0 ? (
						<Fragment>
							<SelectControl
								label="Custom Field Name"
								value={ selectedField }
								options={ metaKeys.map(key => {
									return {
										label: key,
										value: key
									};
								}) }
								onChange={ ( newselectedField ) => {
									setAttributes({
										selectedField: newselectedField
									});
								} }
								__nextHasNoMarginBottom
							/>
							<SelectControl
								label="Input Type"
								value={inputType}
								options={inputTypes}
								onChange={(newInputType) => {
									setAttributes({ inputType: newInputType });
								}}
							/>
							<TextControl
								label="Accessible Label"
								value={accessibleLabel}
								onChange={(newAccessibleLabel) => {
									setAttributes({ accessibleLabel: newAccessibleLabel });
								}}
							/>
							<TextControl
								label="Default 'All' Label"
								value={label}
								onChange={(newLabel) => {
									setAttributes({ label: newLabel });
								}}
							/>
						</Fragment>
					) : ''
					}
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps }>
				{(() => {
					switch (inputType) {
						case 'select':
							return (
								<select className='wp-query-filter__select'>
									<option>{label}</option>
								</select>
							);
						case 'checkboxes':
							return (
								<div className='wp-query-filter__checkboxes'>
									<ul>
										<li>
											<label>
												<input type='checkbox' />
												Example Custom Field 1
											</label>
										</li>
										<li>
											<label>
												<input type='checkbox' />
												Example Custom Field 2
											</label>
										</li>
										<li>
											<label>
												<input type='checkbox' />
												Example Custom Field 3
											</label>
										</li>
									</ul>
								</div>
							);
						default:
							return null;
					}
				})()}

			</div>
		</Fragment>
	);
}
