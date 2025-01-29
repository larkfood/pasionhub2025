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

if (typeof siteUrl === 'undefined') {
	var siteUrl = '';
}
if (typeof isLogged === 'undefined') {
	var isLogged = false;
}
if (typeof phoneCountries === 'undefined') {
	var phoneCountries = [];
}
if (typeof phoneCountry === 'undefined') {
	var phoneCountry = 'us';
}
if (typeof defaultAuthField === 'undefined') {
	var defaultAuthField = 'email';
}

onDocumentReady((event) => {
	
	/* Apply the 'intl-tel-input' plugin to the modal phone field */
	let mPhoneInput = document.querySelector('#mPhone');
	let mIti = applyIntlTelInput(mPhoneInput, phoneCountries, phoneCountry);
	
	/* Apply the 'intl-tel-input' plugin to the phone field */
	let phoneInput = document.querySelector("input[name='phone']:not([type=hidden]):not(.m-phone)");
	let iti = applyIntlTelInput(phoneInput, phoneCountries, phoneCountry);
	
	/* Apply the 'intl-tel-input' plugin to the 2nd modal phone field */
	let fromPhoneInput = document.querySelector('#fromPhone');
	let fIti = applyIntlTelInput(fromPhoneInput, phoneCountries, phoneCountry);
	
	/* Get all forms elements */
	const formEls = document.querySelectorAll('form');
	
	/* Get all forms that have an auth field */
	const authFormEls = Array.from(formEls).filter(form => form.querySelector('.auth-field-item'));
	
	if (authFormEls.length > 0) {
		authFormEls.forEach(formEl => {
			
			/* Select an auth field */
			selectAuthField(formEl, null, defaultAuthField);
			
			/* Add event listener for click events on elements with class 'auth-field' */
			const authFieldLinkEls = formEl.querySelectorAll('a.auth-field');
			if (authFieldLinkEls.length > 0) {
				authFieldLinkEls.forEach(element => {
					element.addEventListener('click', e => {
						e.preventDefault();
						selectAuthField(formEl, e.target);
					});
				});
			}
			
			/* Add event listener for change events on elements with class 'auth-field-input' */
			const authFieldRadioBtnEls = formEl.querySelectorAll('input.auth-field-input');
			if (authFieldRadioBtnEls.length > 0) {
				authFieldRadioBtnEls.forEach(element => {
					element.addEventListener('change', e => {
						selectAuthField(formEl, e.target);
					});
				});
			}
			
		});
	}
	
});

/**
 * Apply the 'intl-tel-input' plugin to the phone field
 *
 * @param inputEl
 * @param countries
 * @param phoneCountry
 * @returns {null|*}
 */
function applyIntlTelInput(inputEl, countries, phoneCountry = null) {
	if (isEmpty(inputEl)) {
		return null;
	}
	
	let params = {
		/* hiddenInput: 'phone_intl', */
		initialCountry: '',
		separateDialCode: true,
		preferredCountries: [],
	};
	
	if (!isEmpty(siteUrl)) {
		params.utilsScript = siteUrl + '/assets/plugins/intl-tel-input/17.0.18/js/utils.js';
	}
	
	if (!isEmpty(phoneCountry)) {
		/* Is the current country's item/object? */
		let isCurrPhoneCountryItem = function (e) {
			return (!isEmpty(e.iso2) && e.iso2.toLowerCase() === phoneCountry.toLowerCase());
		};
		/*
		 * Check the (eventual) initial country exists in the countries list,
		 * If so, set it as initial country.
		 */
		if (countries.filter(e => isCurrPhoneCountryItem(e)).length > 0) {
			params.initialCountry = phoneCountry.toLowerCase();
		}
	}
	
	/* Replace dynamically the countries list */
	if (!isEmpty(countries)) {
		/* Get all the countries data */
		let allCountries = window.intlTelInputGlobals.getCountryData();
		allCountries.length = 0;
		
		countries.forEach(function (country) {
			allCountries.push(country);
		});
		
		if (allCountries.length > 1 && !isEmpty(phoneCountry)) {
			params.preferredCountries = [phoneCountry.toLowerCase()];
		}
	}
	
	/*
	 * Store the instance variable in 'window.iti',
	 * so we can access it in the console e.g. window.iti.getNumber()
	 */
	let iti = window.intlTelInput(inputEl, params);
	
	/* Populate phone hidden inputs */
	const populatePhoneHiddenInputs = function () {
		/* phone_intl */
		let phoneIntlEls = document.querySelectorAll("input[name='phone_intl']");
		if (phoneIntlEls.length) {
			let phoneIntl = iti.getNumber();
			phoneIntlEls.forEach(function (phoneIntlEl) {
				if (!isEmpty(phoneIntlEl)) {
					phoneIntlEl.value = phoneIntl;
				}
			});
		}
		
		/* phone_country */
		let phoneCountryEls = document.querySelectorAll("input[name='phone_country']");
		if (phoneCountryEls.length) {
			let countryData = iti.getSelectedCountryData();
			phoneCountryEls.forEach(function (phoneCountryEl) {
				if (!isEmpty(phoneCountryEl)) {
					if (!isEmpty(countryData.iso2)) {
						phoneCountryEl.value = countryData.iso2;
					}
				}
			});
		}
	};
	
	inputEl.addEventListener('focus', populatePhoneHiddenInputs);
	inputEl.addEventListener('blur', populatePhoneHiddenInputs);
	inputEl.addEventListener('change', populatePhoneHiddenInputs);
	inputEl.addEventListener('keyup', populatePhoneHiddenInputs);
	
	return iti;
}

/**
 * Select an auth field (email or phone)
 *
 * @param formEl
 * @param thisEl
 * @param defaultAuthField
 * @returns {boolean}
 */
function selectAuthField(formEl, thisEl = null, defaultAuthField = null) {
	defaultAuthField = defaultAuthField || 'email';
	
	/* Select default auth field */
	let authFieldTagName;
	let authField;
	if (thisEl) {
		authFieldTagName = thisEl.tagName.toLowerCase();
		authField = (authFieldTagName === 'input')
			? thisEl.value
			: thisEl.dataset.authField ?? defaultAuthField;
	} else {
		authField = defaultAuthField;
	}
	
	if (!authField || authField.length <= 0) {
		jsAlert('Impossible to get the auth field!', 'error', false);
		return false;
	}
	
	/* Update the 'auth_field' field value */
	if (authFieldTagName && authFieldTagName === 'a') {
		const authFieldEls = formEl.querySelectorAll("input[name='auth_field']:not([type=radio], [type=checkbox])");
		if (authFieldEls.length > 0) {
			authFieldEls.forEach(input => {
				input.value = authField;
			});
		}
	}
	
	/* Get the auth field items (email|phone) & the selected item elements */
	const itemsEls = formEl.querySelectorAll('.auth-field-item');
	const canBeHiddenItemsEls = formEl.querySelectorAll('.auth-field-item:not(.force-to-display)');
	
	let selectedItemParentEl;
	const selectedItemEl = formEl.querySelector("input[name='" + authField + "']");
	if (selectedItemEl) {
		selectedItemParentEl = selectedItemEl.closest('.auth-field-item');
	}
	
	/* Manage required '<sup>' tag in the auth field items' label */
	if (itemsEls.length > 0) {
		itemsEls.forEach(item => {
			item.classList.remove('required');
			let sup = item.querySelector('label sup');
			if (sup) {
				sup.remove();
			}
		});
	}
	
	if (selectedItemParentEl) {
		selectedItemParentEl.classList.add('required');
		let label = selectedItemParentEl.querySelector('label');
		if (label) {
			label.innerHTML += ' <sup>*</sup>';
		}
	}
	
	/* Manage auth field items display */
	if (typeof isLogged !== 'undefined' && isLogged !== true) {
		if (canBeHiddenItemsEls.length > 0) {
			canBeHiddenItemsEls.forEach(item => {
				item.classList.add('d-none');
			});
		}
		if (selectedItemParentEl) {
			selectedItemParentEl.classList.remove('d-none');
		}
	}
}
