/**
 * WordPress dependencies
 */
import { store, getContext, getElement } from '@wordpress/interactivity';

let didRunInitially = false;

const updateURLParameter = ( url, urlParameters ) => {
	const newUrl = new URL(url);

	urlParameters.forEach( urlParameter => {
		newUrl.searchParams.set(urlParameter.identifier, urlParameter.value);
	});

	return newUrl;
};

store( 'ctlt-query-custom-field-filter', {
	actions: {
		onChangeField: ( event ) => {
			event.preventDefault();

			const context = getContext();
			
			// Check the element tag, if it's a checkbox, get the all the checked values.
			if ( event.target.tagName === 'INPUT' && event.target.type === 'checkbox' ) {
				// Get the name of the checkbox
				const checkboxName = event.target.name;
				// Get all the checked values
				const checkedValues = document.querySelectorAll( `input[name="${checkboxName}"]:checked` );
				// Add the checked values to the selecteds array
				context.selected = Array.from( checkedValues ).map( checkbox => checkbox.value );
				console.log( context.selected );
			} else {
				context.selected = event.target.value;
			}
		},
	},
	callbacks: {
		navigateToDestination: function* () {
			const { ref } = getElement();
			const { selected } = getContext();

			if ( null === ref ) {
				return;
			}

			if ( ! didRunInitially ) {
				didRunInitially = true;
				return; // Skip the first run on node creation
			}

			const queryRef = ref.closest(
				'.wp-block-query[data-wp-router-region]'
				);

			const { actions } = yield import(
				'@wordpress/interactivity-router'
			);

			let navigateTo = updateURLParameter(
				window.location,
				[
					{ identifier: queryRef.getAttribute( 'data-wp-router-region' ) + '-cf-' + ref.getAttribute( 'filter-id' ), value: Array.isArray( selected ) ? selected.join( ',' ) : selected },
					{ identifier: queryRef.getAttribute( 'data-wp-router-region' ) + '-page', value: '1' },
				]
			);

			yield actions.navigate( navigateTo );

		},
	  },
	
} );
