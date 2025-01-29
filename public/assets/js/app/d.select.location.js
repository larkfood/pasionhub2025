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

/* Prevent errors, If these variables are missing. */
if (typeof countryCode === 'undefined') {
	var countryCode = '--';
}
if (typeof adminType === 'undefined') {
	var adminType = 0;
}
if (typeof selectedAdminCode === 'undefined') {
	var selectedAdminCode = 0;
}
if (typeof cityId === 'undefined') {
	var cityId = 0;
}
if (typeof languageCode === 'undefined') {
	var languageCode = 'en';
}

var select2Language = languageCode;
if (typeof langLayout !== 'undefined' && typeof langLayout.select2 !== 'undefined') {
	select2Language = langLayout.select2;
}

onDocumentReady((event) => {
	
	/* The adminType possible values are: 0, 1 or 2. Check the 'admin_type' enum column in 'countries' table in DB */
	if (
		[0, 1, 2].includes(adminType) !== true &&
		['0', '1', '2'].includes(adminType) !== true
	) {
		adminType = 0;
	}
	
	/* Get and Bind administrative divisions */
	getAdminDivisions(countryCode, adminType, selectedAdminCode);
	$(document).on('change', '#countryCode', function (e) {
		let thisEl = $(this);
		countryCode = thisEl.val();
		adminType = thisEl.find(':selected').data('adminType');
		getAdminDivisions(countryCode, adminType, 0, true);
	});
	
	/* Get and Bind the selected city */
	if (adminType === 0 || adminType === '0') {
		getSelectedCity(countryCode, cityId);
	}
	
	/* Get AJAX's URL */
	let url = function () {
		/* Get the current country code */
		let selectedCountryCode = $('#countryCode').val();
		if (typeof selectedCountryCode !== "undefined") {
			countryCode = selectedCountryCode;
		}
		
		/* Get the current admin code */
		let selectedAdminCode = $('#adminCode').val();
		if (typeof selectedAdminCode === "undefined") {
			selectedAdminCode = 0;
		}
		
		return siteUrl + '/ajax/countries/' + strToLower(countryCode) + '/admins/' + adminType + '/' + strToLower(selectedAdminCode) + '/cities';
	};
	
	/* Get and Bind cities */
	$('#cityId').select2({
		language: select2Language,
		ajax: {
			url: url,
			dataType: 'json',
			delay: 50,
			data: function (params) {
				return {
					languageCode: languageCode,
					q: params.term, /* search term */
					page: params.page
				};
			},
			processResults: function (data, params) {
				/*
				// parse the results into the format expected by Select2
				// since we are using custom formatting functions we do not need to
				// alter the remote JSON data, except to indicate that infinite
				// scrolling can be used
				*/
				params.page = params.page || 1;
				
				return {
					results: data.items,
					pagination: {
						more: (params.page * 10) < data.totalEntries
					}
				};
			},
			error: function (jqXHR, status, error) {
				bsModalAlert(jqXHR, error);
				
				return { results: [] }; /* Return dataset to load after error */
			},
			cache: true
		},
		escapeMarkup: function (markup) {
			return markup;
		}, /* let our custom formatter work */
		minimumInputLength: 2,
		templateResult: function (data) {
			return data.text;
		},
		templateSelection: function (data, container) {
			return data.text;
		}
	});
	
});

/**
 * Get and Bind Administrative Divisions
 *
 * @param countryCode
 * @param adminType
 * @param selectedAdminCode
 * @param countryChanged
 * @returns {boolean|*}
 */
function getAdminDivisions(countryCode, adminType, selectedAdminCode, countryChanged = false) {
	if (countryCode === 0 || countryCode === '') return false;
	
	let adminElOptions = {'0': lang.select.admin};
	let cityElOptions = {'0': lang.select.city};
	
	let locationBoxEl = $('#locationBox');
	if (isElDefined(locationBoxEl)) {
		if ([1, 2].includes(adminType) !== true && ['1', '2'].includes(adminType) !== true) {
			updateSelect2Options('#adminCode', adminElOptions, '0');
			locationBoxEl.hide();
			
			return 0;
		} else {
			locationBoxEl.show();
		}
	}
	
	let url = siteUrl + '/ajax/countries/' + strToLower(countryCode) + '/admins/' + adminType + '?languageCode=' + languageCode;
	
	let ajax = $.ajax({method: 'GET', url: url});
	ajax.done(function (xhr) {
		/* Init. */
		updateSelect2Options('#adminCode', adminElOptions, '0');
		updateSelect2Options('#cityId', cityElOptions, '0');
		
		/* Bind data into Select list */
		let adminCodeEl = $('#adminCode');
		if (typeof xhr.error !== 'undefined') {
			updateSelect2Options('#adminCode', {'0': xhr.error.message}, '0');
			adminCodeEl.addClass('is-invalid');
			return false;
		} else {
			adminCodeEl.removeClass('is-invalid');
		}
		
		if (typeof xhr.data === 'undefined') {
			return false;
		}
		
		let xhrData = assocObjectToKeyValue(xhr.data, 'name', 'code');
		adminElOptions = {...adminElOptions, ...xhrData};
		
		updateSelect2Options('#adminCode', adminElOptions, selectedAdminCode);
		
		/* Get and Bind the selected city */
		getSelectedCity(countryCode, cityId, countryChanged);
	});
	ajax.fail(function(xhr) {
		let message = getErrorMessageFromXhr(xhr);
		if (message !== null) {
			jsAlert(message, 'error');
		}
	});
	
	return selectedAdminCode;
}

/**
 * Get and Bind (Selected) City by ID
 *
 * @param countryCode
 * @param cityId
 * @param countryChanged
 * @returns {number}
 */
function getSelectedCity(countryCode, cityId, countryChanged = false) {
	let cityElOptions = {'0': lang.select.city};
	
	/* Clear by administrative divisions selection */
	$('#adminCode').on('click, change', function () {
		updateSelect2Options('#cityId', cityElOptions, '0');
	});
	
	if (isEmpty(cityId) || countryChanged) {
		return 0;
	}
	
	let url = siteUrl + '/ajax/countries/' + strToLower(countryCode) + '/cities/' + cityId + '?languageCode=' + languageCode;
	
	let ajax = $.ajax({method: 'GET', url: url});
	ajax.done(function (xhr) {
		updateSelect2Options('#cityId', {[xhr.id]: xhr.text}, xhr.id);
		
		return xhr.id;
	});
	ajax.fail(function (xhr) {
		updateSelect2Options('#cityId', cityElOptions, '0');
		
		let message = getErrorMessageFromXhr(xhr);
		if (message !== null) {
			jsAlert(message, 'error');
		}
		
		return 0;
	});
	
	return 0;
}
