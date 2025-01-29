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

/**
 * Get the Selected Package Price
 * @param selectedPackage
 * @returns {number}
 */
function getPackagePrice(selectedPackage) {
	const priceElSelector = '#price-' + selectedPackage + ' .price-int';
	const priceEl = document.querySelector(priceElSelector);
	
	if (priceEl) {
		let price = priceEl.innerHTML;
		return parseFloat(price);
	}
	
	return 0;
}

/**
 * Show Payment Methods SelectBox
 * @param packagePrice
 * @param forceDisplay
 */
function showPaymentMethods(packagePrice, forceDisplay = false) {
	const lastTableRowEl = document.querySelector('#packagesTable tbody tr:last-child');
	if (!lastTableRowEl) {
		return;
	}
	
	if (forceDisplay) {
		lastTableRowEl.style.display = '';
		return;
	}
	
	/* If price <= 0 hide the Payment Method selection */
	if (packagePrice <= 0) {
		lastTableRowEl.style.display = 'none';
	} else {
		lastTableRowEl.style.display = '';
	}
}

/**
 * Show Amount
 * @param packagePrice
 * @param packageCurrencySymbol
 * @param packageCurrencyInLeft
 */
function showAmount(packagePrice, packageCurrencySymbol, packageCurrencyInLeft) {
	/* Show Amount */
	const payableAmountEls = document.querySelectorAll('.payable-amount');
	payableAmountEls.forEach((el) => el.innerHTML = packagePrice);
	
	/* Show Amount Currency */
	const amountCurrencyEls = document.querySelectorAll('.amount-currency');
	amountCurrencyEls.forEach((el) => el.innerHTML = packageCurrencySymbol);
	
	const currencyInLeftEls = document.querySelectorAll('.amount-currency.currency-in-left');
	const currencyInRightEls = document.querySelectorAll('.amount-currency.currency-in-right');
	
	const isCurrencyInLeft = (
		packageCurrencyInLeft === 1
		|| packageCurrencyInLeft === '1'
		|| packageCurrencyInLeft === true
	);
	
	if (isCurrencyInLeft) {
		currencyInLeftEls.forEach((el) => el.style.display = '');
		currencyInRightEls.forEach((el) => el.style.display = 'none');
	} else {
		currencyInLeftEls.forEach((el) => el.style.display = 'none');
		currencyInRightEls.forEach((el) => el.style.display = '');
	}
}

/**
 * Show or Hide the Payment Submit Button
 * NOTE: Prevent Package's Downgrading
 * Hide the 'Skip' button if Package price > 0
 *
 * @param currentPackagePrice
 * @param packagePrice
 * @param paymentIsActive
 * @param paymentMethod
 * @param isCreationFormPage
 */
function showPaymentSubmitButton(currentPackagePrice, packagePrice, paymentIsActive, paymentMethod, isCreationFormPage = true) {
	const submitBtn = document.getElementById('payableFormSubmitButton');
	if (!submitBtn) return;
	
	const skipBtn = document.getElementById('skipBtn');
	if (!skipBtn) return;
	
	if (packagePrice > 0) {
		submitBtn.innerHTML = submitBtnLabel.pay;
		submitBtn.style.display = '';
		skipBtn.style.display = 'none';
		
		if (currentPackagePrice > packagePrice) {
			submitBtn.style.display = 'none';
			submitBtn.innerHTML = submitBtnLabel.submit;
		}
		if (currentPackagePrice === packagePrice) {
			if (paymentMethod === 'offlinepayment') {
				if (!isCreationFormPage && paymentIsActive !== 1) {
					submitBtn.style.display = 'none';
					submitBtn.innerHTML = submitBtnLabel.submit;
					skipBtn.style.display = '';
				}
			}
		}
	} else {
		skipBtn.style.display = '';
		submitBtn.innerHTML = submitBtnLabel.submit;
	}
}

/* Call from Payment Gateways views */

/**
 * Load the payment gateway
 * @param gatewayName
 * @param params
 * @param callback
 */
function loadPaymentGateway(gatewayName, params = {}, callback = null) {
	if (isNotDefined(params.hasForm) || isNotDefined(params.hasLocalAction)) {
		console.warn(`The "params" argument is not valid for the "${gatewayName}" gateway.`);
		return;
	}
	
	const selectedPackageEl = document.querySelector(packagesElsSelector + ':checked');
	const paymentMethodEl = document.getElementById('paymentMethodId');
	
	if (!selectedPackageEl || !paymentMethodEl) {
		return;
	}
	
	/* Get the selected package ID & info */
	let selectedPackage = selectedPackageEl.value;
	let packagePrice = getPackagePrice(selectedPackage);
	let packageName = selectedPackageEl.dataset.name;
	
	/* Get the selected payment method info */
	let paymentMethodSelectedOption = paymentMethodEl.options[paymentMethodEl.selectedIndex];
	let paymentMethod = paymentMethodSelectedOption.dataset.name;
	
	/* Check Payment Method */
	displayPaymentGateway(gatewayName, paymentMethod, packagePrice, packageName, params);
	
	/* On package button radio (input) OR row (table > tbody > tr) clicked */
	const packagesRowEls = document.querySelectorAll(`${packagesElsSelector}, ${packagesRowElsSelector}`);
	if (packagesRowEls.length > 0) {
		packagesRowEls.forEach((element) => {
			element.addEventListener('click', (e) => {
				let thisEl = e.target;
				
				thisEl = selectPackageRadioButton(thisEl);
				if (!thisEl) return;
				
				selectedPackage = thisEl.value;
				packagePrice = getPackagePrice(selectedPackage);
				packageName = thisEl.dataset.name;
				
				paymentMethodSelectedOption = paymentMethodEl.options[paymentMethodEl.selectedIndex];
				paymentMethod = paymentMethodSelectedOption.dataset.name;
				
				displayPaymentGateway(gatewayName, paymentMethod, packagePrice, packageName, params);
			});
		});
	}
	
	/* On payment method (select2) changed */
	$(paymentMethodEl).on('change', (e) => {
		let selectedOption = paymentMethodEl.options[paymentMethodEl.selectedIndex];
		paymentMethod = selectedOption.dataset.name;
		
		displayPaymentGateway(gatewayName, paymentMethod, packagePrice, packageName, params);
	});
	
	/* On payable form submit button clicked */
	const formSubmitBtnEl = document.getElementById('payableFormSubmitButton');
	if (formSubmitBtnEl) {
		formSubmitBtnEl.addEventListener('click', (e) => {
			e.preventDefault();
			
			let selectedOption = paymentMethodEl.options[paymentMethodEl.selectedIndex];
			paymentMethod = selectedOption.dataset.name;
			
			if (paymentMethod !== gatewayName) {
				return false;
			}
			
			if (typeof callback === 'function') {
				return callback(selectedPackage, packagePrice);
			}
			
			const formEl = document.getElementById('payableForm');
			if (formEl && packagePrice > 0) {
				formEl.submit();
			}
			
			return false;
		});
	}
	
	if (params.hasForm) {
		/* If fields content changed, activate back the submit button */
		const formControlEls = document.querySelectorAll(`#${gatewayName}Payment .form-control`);
		formControlEls.forEach((element) => {
			element.addEventListener('change', (e) => {
				const formEl = document.getElementById('payableForm');
				if (formEl) {
					const formSubmitBtnEl = formEl.querySelector('#payableFormSubmitButton');
					if (formSubmitBtnEl) {
						formSubmitBtnEl.disabled = false;
					}
				}
			});
		});
	}
}

/**
 * Check the selected Payment Method
 * @param gatewayName
 * @param paymentMethod
 * @param packagePrice
 * @param packageName
 * @param params
 */
function displayPaymentGateway(gatewayName, paymentMethod, packagePrice, packageName = null, params = {}) {
	if (!isString(gatewayName)) return;
	gatewayName = gatewayName.toLowerCase();
	
	const formEl = document.getElementById('payableForm');
	
	if (params.hasForm) {
		/* Update the submit button */
		const formSubmitBtnEl = formEl.querySelector('#payableFormSubmitButton');
		if (formSubmitBtnEl) {
			formSubmitBtnEl.innerHTML = (packagePrice > 0) ? submitBtnLabel.pay : submitBtnLabel.submit;
			formSubmitBtnEl.disabled = false;
		}
		
		if (params.hasLocalAction) {
			/* Hide errors on the form */
			let gatewayErrorsEl = formEl.querySelector(`#${gatewayName}PaymentErrors`);
			if (gatewayErrorsEl) {
				gatewayErrorsEl.style.display = 'none';
				const gatewayErrorsChildEl = gatewayErrorsEl.querySelector('.payment-errors');
				if (gatewayErrorsChildEl) {
					gatewayErrorsChildEl.innerHTML = '';
				}
			}
		}
	}
	
	/* Show the payment gateway logo & form */
	let gatewayPaymentEl = document.getElementById(`${gatewayName}Payment`);
	if (gatewayPaymentEl) {
		if (gatewayName === 'offlinepayment') {
			if (packagePrice > 0) {
				const packageNameEl = document.querySelector('#offlinepaymentDescription .package-name');
				if (packageNameEl) {
					packageNameEl.innerHTML = packageName;
				}
			}
		}
		gatewayPaymentEl.style.display = (paymentMethod === gatewayName && packagePrice > 0) ? '' : 'none';
	}
}

/**
 * Select the package radio button
 * @param el
 * @returns {*|null}
 */
function selectPackageRadioButton(el) {
	if (!isDomElement(el)) return null;
	
	let elTagName = el.tagName.toLowerCase();
	if (elTagName !== 'tr' || elTagName !== 'input') {
		el = el.closest('tr');
	}
	if (el.tagName.toLowerCase() === 'tr') {
		el = el.querySelector('input[type="radio"][name="package_id"]');
		if (el) {
			el.checked = true;
		}
	}
	return el;
}
