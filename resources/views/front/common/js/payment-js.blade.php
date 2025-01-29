@php
	$packages ??= collect();
	$paymentMethods ??= collect();
	
	$selectedPackage ??= null;
	$currentPackagePrice = $selectedPackage->price ?? 0;
@endphp
@if ($packages->count() > 0 && $paymentMethods->count() > 0)
	{{--
		JS variable required in parent views:
		- packageType: 'promotion' or 'subscription'
		- formType: 'multiStep' or 'singleStep'
		- isCreationFormPage: true or false (where false is for update form page)
	--}}
	<script>
		
		const packagesRowElsSelector = '#packagesTable tbody tr:not(:last-child)';
		const packagesElsSelector = '#packagesTable input[type="radio"][name="package_id"]';
		
		var currentPackagePrice = {{ $currentPackagePrice ?? 0 }};
		var paymentIsActive = {{ $paymentIsActive ?? 0 }};
		var forceDisplayPaymentMethods = {{ !empty($selectedPackage) ? 'true' : 'false' }};
		
		const submitBtnLabel = {
			pay: langLayout.payment.submitBtnLabel.pay ?? 'Pay',
			submit: langLayout.payment.submitBtnLabel.submit ?? 'Submit',
		};
		
		onDocumentReady((event) => {
			
			const selectedPackageEl = document.querySelector(packagesElsSelector + ':checked');
			const paymentMethodEl = document.getElementById('paymentMethodId');
			
			if (!selectedPackageEl || !paymentMethodEl) {
				if (packageType === 'promotion') {
					if (!selectedPackageEl) {
						if (hasQueryParameter('package')) {
							let urlWithoutPackage = removeURLParameter('package');
							redirect(urlWithoutPackage);
						}
					}
				}
				return false;
			}
			
			{{-- Get the selected package ID & info --}}
			let selectedPackage = selectedPackageEl.value;
			let packagePrice = getPackagePrice(selectedPackage);
			let packageCurrencySymbol = selectedPackageEl.dataset.currencySymbol;
			let packageCurrencyInLeft = selectedPackageEl.dataset.currencyInLeft;
			
			{{-- Get the selected payment method info --}}
			let paymentMethodSelectedOption = paymentMethodEl.options[paymentMethodEl.selectedIndex];
			let paymentMethod = paymentMethodSelectedOption.dataset.name;
			
			showPaymentMethods(packagePrice, forceDisplayPaymentMethods);
			showAmount(packagePrice, packageCurrencySymbol, packageCurrencyInLeft);
			if (formType === 'multiStep') {
				showPaymentSubmitButton(currentPackagePrice, packagePrice, paymentIsActive, paymentMethod, isCreationFormPage);
			}
			
			{{-- On package button radio (input) OR row (table > tbody > tr) clicked --}}
			const packagesRowEls = document.querySelectorAll(`${packagesElsSelector}, ${packagesRowElsSelector}`);
			if (packagesRowEls.length > 0) {
				packagesRowEls.forEach((element) => {
					element.style.cursor = 'pointer';
					element.addEventListener('click', (e) => {
						let thisEl = e.target;
						
						thisEl = selectPackageRadioButton(thisEl);
						if (!thisEl) return;
						
						selectedPackage = thisEl.value;
						packagePrice = getPackagePrice(selectedPackage);
						packageCurrencySymbol = thisEl.dataset.currencySymbol;
						packageCurrencyInLeft = thisEl.dataset.currencyInLeft;
						
						showPaymentMethods(packagePrice);
						showAmount(packagePrice, packageCurrencySymbol, packageCurrencyInLeft);
						if (formType === 'multiStep') {
							showPaymentSubmitButton(currentPackagePrice, packagePrice, paymentIsActive, paymentMethod, isCreationFormPage);
						}
					});
				});
			}
			
			{{-- On payment method (select2) changed --}}
			$(paymentMethodEl).on('change', (e) => {
				let selectedOption = paymentMethodEl.options[paymentMethodEl.selectedIndex];
				paymentMethod = selectedOption.dataset.name;
				
				if (formType === 'multiStep') {
					showPaymentSubmitButton(currentPackagePrice, packagePrice, paymentIsActive, paymentMethod, isCreationFormPage);
				}
			});
			
			{{--
			/*
			 * On payable form submit button clicked ==================================================
			 *
			 * Form default submission (i.e. Without a Payment Gateway)
			 * In other words, this will be fired only when packagePrice <= 0
			 *
			 * NOTE: When packagePrice > 0, the payable form submission
			 * will be handled by the selected payment gateway submission event.
			 *
			 * ATTENTION: Several events of the same type have been attached to this element.
			 * And the only conditions that ensure that they don't overlap is:
			 * - The package price (packagePrice <= 0 or packagePrice > 0), related to the base event.
			 * - The second condition is in the payment gateway selection, to use its local event.
			 * ========================================================================================
			 */
			 --}}
			const formSubmitBtnEl = document.getElementById('payableFormSubmitButton');
			if (formSubmitBtnEl) {
				formSubmitBtnEl.addEventListener('click', (e) => {
					e.preventDefault();
					
					const formEl = document.getElementById('payableForm');
					if (formEl && packagePrice <= 0) {
						formEl.submit();
					}
					
					return false;
				});
			}
			
		});
		
	</script>
@endif
