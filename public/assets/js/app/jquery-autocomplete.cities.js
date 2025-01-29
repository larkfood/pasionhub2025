/*
 * LaraClassifier - Classified Ads Web Application
 * Copyright (c) BeDigit. All Rights Reserved
 *
 * Website: https://laraclassifier.com
 * Author: Mayeul Akpovi (BeDigit - https://bedigit.com)
 *
 * LICENSE
 * -------
 * This software is provided under a license agreement and may only be used or copied
 * in accordance with its terms, including the inclusion of the above copyright notice.
 * As this software is sold exclusively on CodeCanyon,
 * please review the full license details here: https://codecanyon.net/licenses/standard
 */

if (typeof noResultsText === 'undefined') {
	var noResultsText = 'No results';
	if (
		typeof langLayout.select2 !== 'undefined'
		&& typeof langLayout.select2.noResults !== 'undefined'
		&& typeof langLayout.select2.noResults === 'function'
	) {
		noResultsText = langLayout.select2.noResults();
	}
}
if (typeof fakeLocationsResults === 'undefined') {
	var fakeLocationsResults = '0';
}
if (typeof isLoggedAdmin === 'undefined') {
	isLoggedAdmin = false;
}
if (typeof errorText === 'undefined') {
	var errorText = {
		errorFound: 'Error Found'
	};
}

/*
 * Check if "No Results" can be shown or not
 * -----
 * NOTE:
 * Typically, don't display "No results found" when the app can:
 * - Use the most populate city when searched city cannot be found
 * - City filter can be ignored when searched city cannot be found
 *
 * For more information, check out the:
 * Admin panel → Settings → General → Listings List → Fake locations results
 */
const showNoSuggestionNotice = (['1', '2'].indexOf(fakeLocationsResults) === -1);

// Auto Complete Global Parameters
const searchFormSelector = "#searchForm ";
const locationFieldSelector = searchFormSelector + "input[type=text][name=location].autocomplete-enabled";
const locationIdFieldSelector = searchFormSelector + "input[type=hidden][name=l]";
const adminDivisionFieldSelector = searchFormSelector + "input[type=hidden][name=r]";
const tooltipTriggerElSelector = locationFieldSelector + ".tooltipHere";

const threshold = 1;
const suggestionsZIndex = 1492;

/*
 * Documentation: https://github.com/devbridge/jQuery-Autocomplete
 */
onDocumentReady((event) => {
	
	const tooltipTriggerEl = document.querySelector(tooltipTriggerElSelector);
	
	if (isString(countryCode) && countryCode !== '0' && countryCode !== '') {
		noResultsText = '<div class="p-2">' + noResultsText + '</div>';
		
		const locationEl = $(locationFieldSelector);
		
		// Get the Laravel CSRF Token
		const csrfToken = getCsrfToken(document.querySelector(locationFieldSelector));
		
		// AutoComplete Configuration
		let options = {
			zIndex: suggestionsZIndex,
			maxHeight: 333,
			serviceUrl: siteUrl + '/ajax/countries/' + strToLower(countryCode) + '/cities/autocomplete',
			type: 'post',
			data: {
				'city': $(this).val(),
				'_token': csrfToken
			},
			minChars: threshold,
			showNoSuggestionNotice: showNoSuggestionNotice,
			noSuggestionNotice: noResultsText,
			onSearchStart: function (params) {
				disableTooltipForElement(tooltipTriggerEl);
			},
			transformResult: function (response, originalQuery) {
				response = $.parseJSON(response);
				
				let suggestions = $.map(response.suggestions, function (dataItem) {
					let adminName = isDefined(dataItem.admin) ? ', ' + dataItem.admin : '';
					let cityName = dataItem.name + adminName;
					
					return {
						data: dataItem.id,
						value: cityName
					};
				});
				
				return {suggestions: suggestions};
			},
			beforeRender: function (container, suggestions) {
				const query = locationEl.val();
				const suggestionsEl = $('.autocomplete-suggestions');
				hideResultsListWhenAreaTextIsFilledJQuery(suggestionsEl, suggestions, query);
			},
			formatResult: function (suggestion, currentValue) {
				const icon = `<i class="bi bi-geo-alt text-secondary"></i>`;
				const formattedLabel = $.Autocomplete.defaults.formatResult(suggestion, currentValue);
				
				return icon + ' ' + formattedLabel;
			},
			onSearchError: function (query, xhr, textStatus, errorThrown) {
				bsModalAlert(xhr, errorThrown);
			},
			onSelect: function (suggestion) {
				$(locationIdFieldSelector).val(suggestion.data);
				enableTooltipForElement(tooltipTriggerEl);
			}
		};
		
		// Apply the AutoComplete Config
		// locationEl.devbridgeAutocomplete(options);
	}
	
});

function hideResultsListWhenAreaTextIsFilledJQuery(listEl, results, query) {
	if (typeof results === 'undefined' || typeof query === 'undefined') {
		return false;
	}
	
	const areaText = langLayout.location.area;
	const queryExtractedText = query.substring(0, areaText.length);
	
	if (results.length <= 0) {
		if (queryExtractedText === areaText) {
			listEl.addClass('d-none');
		} else {
			listEl.removeClass('d-none');
		}
	} else {
		listEl.removeClass('d-none');
	}
}
