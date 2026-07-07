/**
 * WordPress dependencies
 */
import { store, getContext, getElement } from '@wordpress/interactivity';

let didRunInitially = false;

const updateURLParameter = (url, urlParameters) => {
	const newUrl = new URL(url);

	urlParameters.forEach(urlParameter => {
		newUrl.searchParams.set(urlParameter.identifier, urlParameter.value);
	});

	return newUrl;
};

const updateLiveRegion = ( element ) => {
	const liveRegion = element.querySelector( '.live-region' );

	if ( ! liveRegion ) {
		return;
	}

	// Screen readers often suppress announcements if the new text is identical to the old text.
	// We alternate by adding a non-breaking space at the end to force a perceived change.
	if ( liveRegion.textContent === "Content updated." ) {
		liveRegion.textContent = "Content updated.\u00A0";
	} else {
		liveRegion.textContent = "Content updated.";
	}
};


store('ctlt-query-custom-field-filter', {
	actions: {
		onChangeField: (event) => {
			event.preventDefault();

			const context = getContext();

			// Check the element tag, if it's a checkbox, get the all the checked values.
			if (event.target.tagName === 'INPUT' && event.target.type === 'checkbox') {
				// Get the name of the checkbox
				const checkboxName = event.target.name;
				// Get all the checked values
				const checkedValues = document.querySelectorAll(`input[name="${checkboxName}"]:checked`);
				// Add the checked values to the selecteds array
				context.selected = Array.from(checkedValues).map(checkbox => checkbox.value);
			} else {
				context.selected = event.target.value;
			}
		},
	},
	callbacks: {
		navigateToDestination: function* () {
			const { ref } = getElement();
			const { selected } = getContext();

			if (null === ref) {
				return;
			}

			if (!didRunInitially) {
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
					{ identifier: queryRef.getAttribute('data-wp-router-region') + '-cf-' + ref.getAttribute('filter-id'), value: Array.isArray(selected) ? selected.join(',') : selected },
					{ identifier: queryRef.getAttribute('data-wp-router-region') + '-page', value: '1' },
				]
			);

			// Dim the existing results while the new content loads.
			queryRef.classList.add('is-loading-content');
			queryRef.setAttribute('aria-busy', 'true');

			try {
				yield actions.navigate(navigateTo);
			} finally {
				queryRef.classList.remove('is-loading-content');
				queryRef.removeAttribute('aria-busy');
			}

			updateLiveRegion(ref);
		},
	},

});