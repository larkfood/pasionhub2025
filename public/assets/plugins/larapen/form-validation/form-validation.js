/* Form Validation Plugin */
(function () {
	function formValidate(formElement, options) {
		const defaultSelectorsList = [
			'input[type="text"]',
			'input[type="email"]',
			'input[type="password"]',
			'input[type="tel"]',
			'input[type="url"]',
			'input[type="number"]',
			'input[type="date"]',
			'input[type="month"]',
			'input[type="week"]',
			'input[type="time"]',
			'input[type="datetime-local"]',
			'input[type="color"]',
			'input[type="search"]',
			'textarea',
		];
		
		// OPTIONS
		const defaults = {
			fieldElSelector: defaultSelectorsList.join(","),
			fieldElErrorClass: "is-invalid",
			formErrorElSelector: "formErrorElementSelector",
			formErrorMessage: "This form contains invalid data.",
			fieldErrorElClasses: ["text-danger"],
			defaultErrors: {
				required: "This field is required",
				validator: "This field is not valid",
			},
			errors: {
				alphanumeric: "Enter an alphanumeric value",
				numeric: "Enter a positive numeric value",
				email: "Enter a valid email address",
				url: "Enter a valid URL",
				username: "Enter an alphanumeric with underscores, 3-16 characters",
				password: "Enter a strong password requirements",
				date: "Enter a valid date (YYYY-MM-DD) format",
				time: "Enter a valid time (HH 24-hour) format",
				cardExpiry: "Enter a valid card expiry",
				cardCvc: "Enter a valid card CVC",
			},
			callback: () => formElement.submit(),
		};
		const settings = Object.assign({}, defaults, options);
		
		// Init.
		/* const formElement = document.querySelector(formElementSelector); */
		const inputs = formElement.querySelectorAll(settings.fieldElSelector);
		const formErrorElement = document.getElementById(settings.formErrorElSelector);
		
		const validators = {
			email: {
				regex: /^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$/,
			},
			// Numeric (positive integers)
			numeric: {
				regex: /^\d+$/,
			},
			url: {
				regex: /^(ftp|http|https):\/\/[^ "]+$/,
			},
			cardExpiry: {
				regex: /^(0[1-9]|1[0-2])\\s?\/\\s?([0-9]{4}|[0-9]{2})$/,
			},
			cardCvc: {
				regex: /^[0-9]{3,4}$/,
			},
			// Alphanumeric (letters and numbers only)
			alphanumeric: {
				regex: /^[a-zA-Z0-9]+$/,
			},
			// Date (YYYY-MM-DD format)
			date: {
				regex: /^\d{4}-\d{2}-\d{2}$/,
			},
			// Time (HH 24-hour format)
			time: {
				regex: /^([01]?[0-9]|2[0-3]):[0-5][0-9]$/,
			},
			// Username (alphanumeric with underscores, 3-16 characters)
			username: {
				regex: /^[a-zA-Z0-9_]{3,16}$/,
			},
			// Password (strong password requirements)
			password: {
				regex: /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*]).{8,}$/,
			},
		};
		
		const createInputErrorEl = (input, text) => {
			const errorEl = document.createElement('div');
			errorEl.classList.add('err-message');
			if (settings.fieldErrorElClasses) {
				errorEl.classList.add(...settings.fieldErrorElClasses);
			}
			errorEl.textContent = text;
			input.insertAdjacentElement('afterend', errorEl);
		};
		
		const validate = (input) => {
			const validType = input.dataset.validType;
			const value = input.value;
			
			const inputGroupEl = input.closest('.input-group');
			const possibleErrorEl = inputGroupEl ? inputGroupEl.nextElementSibling : input.nextElementSibling;
			
			let isValid = true;
			let error = '';
			
			if (value) {
				const validator = validators[validType] ?? null;
				if (validator) {
					if (!validator.regex.test(value)) {
						isValid = false;
						
						let fieldErrorMessage = settings.defaultErrors.validator ?? null;
						fieldErrorMessage = settings.errors[validType] ?? fieldErrorMessage;
						fieldErrorMessage = validator.error ?? fieldErrorMessage;
						
						error = fieldErrorMessage ?? '';
					}
				}
			} else {
				if (isRequired(input)) {
					error = settings.defaultErrors.required;
					isValid = false;
					/* console.log(input) // debug */
				}
			}
			
			// Display
			input.classList.remove(settings.fieldElErrorClass);
			if (formErrorElement) {
				formErrorElement.classList.add("d-none");
			}
			
			if (!isValid) {
				if (formErrorElement) {
					formErrorElement.classList.remove("d-none");
				}
				
				input.classList.add(settings.fieldElErrorClass);
				if (inputGroupEl) {
					inputGroupEl.classList.add(settings.fieldElErrorClass);
				}
				
				const errorMessage = error ?? "Invalid data";
				const errorElExists = (possibleErrorEl && possibleErrorEl.classList.contains('err-message'));
				if (!errorElExists) {
					if (inputGroupEl) {
						createInputErrorEl(inputGroupEl, errorMessage);
					} else {
						createInputErrorEl(input, errorMessage);
					}
				} else {
					const oldErrorMessage = possibleErrorEl.innerHTML;
					if (errorMessage !== oldErrorMessage) {
						possibleErrorEl.remove();
						if (inputGroupEl) {
							createInputErrorEl(inputGroupEl, errorMessage);
						} else {
							createInputErrorEl(input, errorMessage);
						}
					}
				}
			} else {
				if (possibleErrorEl && possibleErrorEl.classList.contains('err-message')) {
					possibleErrorEl.remove();
				}
			}
			
			return {
				isValid: isValid,
				error: error
			}
		};
		
		const handleGlobalError = (isFormValid) => {
			if (!formErrorElement) {
				return false;
			}
			
			formErrorElement.classList.add("d-none");
			if (!isFormValid) {
				formErrorElement.classList.remove("d-none");
			}
		};
		
		const hasParentMarkedAsRequired = (input) => {
			if (!isDomElement(input)) return false;
			
			// Parent marked as required
			const parentEl = input.closest('.required');
			if (!isDomElement(parentEl)) return false;
			
			// Unselect parent marked as required if it is not displayed
			const invisibilityClasses = ['hidden', 'hide', 'd-none'];
			const parentElHasInvisibilityClass = invisibilityClasses.some(className => parentEl.classList.contains(className));
			
			return (parentEl.style.display !== 'none' && !parentElHasInvisibilityClass);
		};
		
		const isRequired = (input) => {
			return (input.required || hasParentMarkedAsRequired(input));
		};
		
		inputs.forEach((input) => {
			input.addEventListener("blur", (e) => validate(e.target));
		});
		
		formElement.addEventListener("submit", (e) => {
			/* Retrieve again the inputs list on form submit */
			const inputs = formElement.querySelectorAll(settings.fieldElSelector);
			
			let isFormValid = true;
			let firstInvalidField = null;
			
			inputs.forEach((input) => {
				let test = validate(input);
				
				if (!test.isValid) {
					isFormValid = test.isValid;
					
					// Get the first invalid field. The browser will be scrolled to it.
					if (firstInvalidField === null) {
						firstInvalidField = input;
					}
				}
			});
			
			handleGlobalError(isFormValid);
			
			/* Form is not valid */
			if (!isFormValid) {
				e.preventDefault();
				
				// Scroll to first invalid field
				if (firstInvalidField !== null) {
					firstInvalidField.scrollIntoView({behavior: 'smooth'});
				}
				return false;
			}
			
			/* Form is valid and a callback is provided */
			if (settings.callback && typeof settings.callback === 'function') {
				e.preventDefault();
				settings.callback();
				return false;
			}
		});
	}
	
	// Adding the function to the global scope to use it similarly to jQuery plugin
	window.formValidate = function (selector, options) {
		document.querySelectorAll(selector).forEach((element) => formValidate(element, options));
	};
})();
