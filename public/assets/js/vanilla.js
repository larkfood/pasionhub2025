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

/* Polyfill (https://en.wikipedia.org/wiki/Polyfill_(programming)) */
/* Array.isArray() */
if (!Array.isArray) {
	Array.isArray = function (arg) {
		return Object.prototype.toString.call(arg) === '[object Array]';
	};
}

/* Number.isNaN() */
Number.isNaN = Number.isNaN || function (value) {
	return typeof value === 'number' && isNaN(value);
}

/* Number.isInteger() */
Number.isInteger = Number.isInteger || function (value) {
	return typeof value === 'number' && isFinite(value) && Math.floor(value) === value;
};

/* Number.isSafeInteger() */
if (!Number.MAX_SAFE_INTEGER) {
	Number.MAX_SAFE_INTEGER = 9007199254740991; /* Math.pow(2, 53) - 1; */
}
Number.isSafeInteger = Number.isSafeInteger || function (value) {
	return Number.isInteger(value) && Math.abs(value) <= Number.MAX_SAFE_INTEGER;
};

/* str.endsWith() */
if (!String.prototype.endsWith) {
	String.prototype.endsWith = function (searchString, position) {
		var subjectString = this.toString();
		if (typeof position !== 'number' || !isFinite(position) || Math.floor(position) !== position || position > subjectString.length) {
			position = subjectString.length;
		}
		position -= searchString.length;
		var lastIndex = subjectString.lastIndexOf(searchString, position);
		return lastIndex !== -1 && lastIndex === position;
	};
}

/* --- */

/**
 * Execute callback function after page is loaded
 * @param callback
 * @param isFullyLoaded
 */
if (!window.onDocumentReady) {
	function onDocumentReady(callback, isFullyLoaded = true) {
		switch (document.readyState) {
			case "loading":
				/* The document is still loading, attach the event listener */
				document.addEventListener("DOMContentLoaded", callback);
				break;
			case "interactive": {
				if (!isFullyLoaded) {
					/*
					 * The document has finished loading, and we can access DOM elements.
					 * Sub-resources such as scripts, images, stylesheets and frames are still loading.
					 * Call the callback (on next available tick (in 500 milliseconds))
					 */
					setTimeout(callback, 500);
				}
				break;
			}
			case "complete":
				/* The page is fully loaded, call the callback directly */
				callback();
				break;
			default:
				document.addEventListener("DOMContentLoaded", callback);
		}
	}
}

function onDomElementsAdded(selector, callback) {
	let existingElements = document.querySelectorAll(selector);
	
	// Array to store matched elements
	let matchedElements = existingElements.length > 0 ? Array.from(existingElements) : [];
	
	// Create a new MutationObserver instance
	const observer = new MutationObserver(function (mutationsList) {
		for (let mutation of mutationsList) {
			if (mutation.type === 'childList') {
				for (let addedNode of mutation.addedNodes) {
					if (addedNode.matches && addedNode.matches(selector)) {
						matchedElements.push(addedNode);
					}
					// If the added node has descendants that match the selector
					let descendants = addedNode.querySelectorAll ? addedNode.querySelectorAll(selector) : [];
					descendants.forEach(descendant => matchedElements.push(descendant));
				}
			}
		}
		
		// Callback to return the matched elements
		if (typeof callback === 'function') {
			callback(matchedElements);
		}
	});
	
	// Configuration of the observer
	const config = {childList: true, subtree: true};
	
	// Start observing the body for configured mutations
	observer.observe(document.body, config);
	
	return matchedElements;
}

/**
 * Submit a form on click its submit button
 * @param formSelector
 * @param submitButtonSelector
 */
function setupFormSubmit(formSelector, submitButtonSelector) {
	const form = document.querySelector(formSelector);
	const submitButton = document.querySelector(submitButtonSelector);
	
	if (form && submitButton) {
		submitButton.addEventListener('click', (event) => {
			event.preventDefault();
			form.submit();
		});
	} else {
		console.error('Form or submit button not found');
	}
}

/**
 * During or after typing, Check if an input field changed
 * @param inputElement
 * @param callback
 */
function addInputChangeListeners(inputElement, callback) {
	// The 'input' event is triggered immediately whenever the value of the input field changes
	inputElement.addEventListener('input', (event) => callback(event));
	
	// The 'change' event is triggered when the input field loses focus after its value has been changed
	inputElement.addEventListener('change', (event) => callback(event));
}

/**
 * Prevent the page to load in IFRAME by redirecting it to the top-level window
 */
function preventPageLoadingInIframe() {
	try {
		if (window.top.location !== window.location) {
			window.top.location.replace(siteUrl);
		}
	} catch (e) {
		console.error(e);
	}
}

/**
 * Set|Create cookie
 * @param name
 * @param value
 * @param expires (in Minutes)
 */
function createCookie(name, value, expires = null) {
	/* Get app's cookie parameters */
	expires = (!isEmpty(expires)) ? expires : cookieParams.expires;
	let path = cookieParams.path;
	let domain = cookieParams.domain;
	let secure = cookieParams.secure;
	let sameSite = cookieParams.sameSite;
	
	/* Build JS cookie parts string */
	// let dataStr = name + '=' + value;
	let dataStr = encodeURIComponent(name) + "=" + encodeURIComponent(value);
	let expiresStr;
	if (expires) {
		let date = new Date();
		date.setTime(date.getTime() + (expires * 60 * 1000));
		expiresStr = '; expires=' + date.toUTCString();
	} else {
		expiresStr = '';
	}
	let pathStr = path ? '; path=' + path : '';
	let domainStr = domain ? '; domain=' + domain : '';
	let secureStr = secure ? '; secure' : '';
	let sameSiteStr = sameSite ? '; SameSite=' + sameSite : '';
	
	document.cookie = dataStr + expiresStr + pathStr + domainStr + secureStr + sameSiteStr;
}

/**
 * Get|Read cookie
 * @param name
 * @returns {string|null}
 */
function readCookie(name) {
	let encName = encodeURIComponent(name) + "=";
	let ca = document.cookie.split(';');
	
	for (let i = 0; i < ca.length; i++) {
		let c = ca[i];
		while (c.charAt(0) === ' ') {
			c = c.substring(1, c.length);
		}
		if (c.indexOf(encName) === 0) {
			return decodeURIComponent(c.substring(encName.length, c.length));
		}
	}
	
	return null;
}

/**
 * Check if cookie exists
 * @param name
 * @returns {boolean}
 */
function cookieExists(name) {
	return isFilled(readCookie(name));
}

/**
 * Delete cookie
 * @param name
 */
function eraseCookie(name) {
	createCookie(name, '', -1);
}

/**
 * Redirect URL
 * @param url
 */
function redirect(url) {
	window.location.replace(url);
	window.location.href = url;
}

/**
 * Raw URL encode
 * @param value
 * @returns {string}
 */
function rawurlencode(value) {
	value = (value + '').toString();
	
	return encodeURIComponent(value)
	.replace(/!/g, '%21')
	.replace(/'/g, '%27')
	.replace(/\(/g, '%28')
	.replace(/\)/g, '%29')
	.replace(/\*/g, '%2A');
}

/**
 * Check if variable is defined
 * @param value
 * @returns {boolean}
 */
function isDefined(value) {
	return (typeof value !== 'undefined');
}

/**
 * Check if variable is not defined
 * @param value
 * @returns {boolean}
 */
function isNotDefined(value) {
	return (typeof value === 'undefined');
}

/**
 * Check if pure JS DOM element is found (defined)
 * @param el
 * @returns {boolean}
 */
function isElDefined(el) {
	return (typeof el !== 'undefined' && el !== null);
}

/**
 * Check if pure JS DOM element is not found (not defined)
 * @param el
 * @returns {boolean}
 */
function isElNotDefined(el) {
	return !isElDefined(el);
}

/**
 * Check if variable is undefined, null, 0, or blank
 * @param value
 * @returns {boolean}
 */
function isEmpty(value) {
	if (isBlank(value)) {
		return true;
	}
	
	if (isNumeric(value, true)) {
		return value === 0 || value === '0';
	}
	
	return false;
}

/**
 * Check if variable is blank
 * Support: undefined, null, array, object, date, number and string
 *
 * @param value
 * @returns {boolean}
 */
function isBlank(value) {
	if (!isDefined(value) || value === null) {
		return true;
	}
	
	if (isArray(value)) {
		return value.length === 0;
	}
	
	if (value instanceof Date) {
		return false;
	}
	
	if (isObject(value)) {
		/* 'value' is a JS HTML element */
		if (isDefined(value.nodeName)) {
			return value.nodeName.length === 0;
		}
		
		/* 'value' is a jQuery HTML element */
		if (isDefined(value.get)) {
			return value.get(0).tagName.length === 0;
		}
		
		/* Classic JSON object */
		return Object.keys(value).length === 0;
	}
	
	return [''].includes(value);
}

/**
 * Check if variable is filled
 * @param value
 * @returns {boolean}
 */
function isFilled(value) {
	return !isBlank(value);
}

/**
 * Check if variable is blank or null
 *
 * @param value
 * @returns {boolean}
 */
function isBlankString(value) {
	return (isEmpty(value) || /^\s*$/.test(value));
}

/**
 * Check if variable is a string
 * @param value
 * @returns {boolean}
 */
function isString(value) {
	if (isDefined(value)) {
		return (typeof value === 'string' || value instanceof String);
	}
	
	return false;
}

/**
 * Check if variable is an array
 *
 * @param value
 * @returns {arg is any[]}
 */
function isArray(value) {
	return Array.isArray(value);
}

/**
 * Check if variable is an object
 * Note: Since 'null' is an object in JS, exclude it
 *
 * @param value
 * @returns {boolean}
 */
function isObject(value) {
	return (typeof value === 'object' && value !== null);
}

/**
 * Check if an element is a DOM element
 * @param value
 * @returns {boolean}
 */
function isDomElement(value) {
	return (isElDefined(value) && (value instanceof HTMLElement || value instanceof Element));
}

/**
 * Check if variable is a jQuery object
 * @param value
 * @returns {boolean}
 */
function isJQueryObject(value) {
	return (typeof jQuery !== 'undefined' && value instanceof jQuery);
}

/**
 * Check if variable is a JSON object
 * @param value
 * @returns {boolean}
 */
function isJsonObject(value) {
	return (
		typeof isObject(value)
		&& !isArray(value)
		&& !isJQueryObject(value)
		&& !isDomElement(value)
	);
}

/**
 * Check if variable is numeric (Integer or Float)
 * Note: Second argument to check if string containing an integer
 *
 * @param value
 * @param checkIfStringContainingAnInteger
 * @returns {boolean}
 */
function isNumeric(value, checkIfStringContainingAnInteger = false) {
	let isNumeric = (typeof value === 'number' && !Number.isNaN(value));
	
	if (checkIfStringContainingAnInteger) {
		let parsedValue;
		if (!isNumeric) {
			parsedValue = parseInt(value, 10);
			isNumeric = (value == parsedValue && !Number.isNaN(parsedValue));
		}
		if (!isNumeric) {
			parsedValue = parseFloat(value);
			isNumeric = (value == parsedValue && !Number.isNaN(parsedValue));
		}
	}
	
	return isNumeric;
}

/**
 * Check if variable is an integer (strictly)
 * @param value
 * @returns {boolean}
 */
function isInt(value) {
	return isNumeric(value) && Number.isSafeInteger(value);
}

/**
 * Check if variable is a float number (strictly)
 * @param value
 * @returns {boolean}
 */
function isFloat(value) {
	return isNumeric(value) && !Number.isInteger(value);
}

/**
 * Check if variable is string of JSON or not
 * @param value
 * @returns {boolean}
 */
function isJsonString(value) {
	if (isString(value)) {
		try {
			JSON.parse(value);
			return true;
		} catch (e) {
		}
	}
	return false;
}

/**
 * Check if variable is array of JSON objects
 * @param value
 * @returns {*}
 */
function isArrayOfJsonObjects(value) {
	return isArray(value) && value.every(item => isJsonObject(item));
}

/**
 * Check if variable is array of DOM Elements
 * @param value
 * @returns {*}
 */
function isArrayOfDomElements(value) {
	return isArray(value) && value.every(item => isDomElement(item));
}

/**
 * Get the DOM HTML element
 * @returns {HTMLElement}
 */
function getHtmlElement() {
	return document.documentElement;
}

/**
 * Convert a string to lowercase
 * @param value
 * @returns {string}
 */
function strToLower(value) {
	if (isString(value)) {
		value = value.toLowerCase();
	}
	
	return value;
}

/**
 * Convert a string to uppercase
 * @param value
 * @returns {string}
 */
function strToUpper(value) {
	if (isString(value)) {
		value = value.toUpperCase();
	}
	
	return value;
}

/**
 * sleep() version in JS
 * https://stackoverflow.com/a/39914235
 *
 * Usage:
 * await sleep(2000);
 * or
 * sleep(2000).then(() => {
 *     // Do something after the sleep!
 * });
 *
 * @param ms
 * @returns {Promise<unknown>}
 */
function sleep(ms) {
	return new Promise(resolve => setTimeout(resolve, ms));
}

/**
 * Array each
 *
 * Usage:
 * forEach(array, function(item, i) {});
 *
 * @param array
 * @param fn
 */
function forEach(array, fn) {
	for (let i = 0; i < array.length; i++) {
		fn(array[i], i);
	}
}

/**
 * Array map
 *
 * Usage:
 * map(array, function(value, index) {});
 *
 * @param arr
 * @param fn
 * @returns {*[]}
 */
function map(arr, fn) {
	let results = [];
	for (let i = 0; i < arr.length; i++) {
		results.push(fn(arr[i], i));
	}
	return results;
}

/**
 * Get Query String (as JSON object)
 *
 * Example:
 * getQueryParams('https://foo.tld/search?q=node&page=2')
 * => { q: 'node', page: '2' }
 *
 * @param url
 * @returns {{}}
 */
function getQueryParams(url = window.location.href) {
	const paramArr = url.slice(url.indexOf('?') + 1).split('&');
	const params = {};
	paramArr.map(param => {
		const [key, val] = param.split('=');
		params[key] = decodeURIComponent(val);
	});
	return params;
}

/**
 * Get URL query string by name
 *
 * Usage:
 * query string: ?foo=lorem&bar=&baz
 * var foo = getQueryParameter('foo'); // "lorem"
 * var bar = getQueryParameter('bar'); // "" (present with empty value)
 * var baz = getQueryParameter('baz'); // "" (present with no value)
 * var qux = getQueryParameter('qux'); // null (absent)
 *
 * @param name
 * @param url
 * @returns {string|null}
 */
function getQueryParameter(name, url = window.location.href) {
	name = name.replace(/[\[\]]/g, '\\$&');
	
	let regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)');
	let results = regex.exec(url);
	
	if (!results) return null;
	if (!results[2]) return '';
	
	return decodeURIComponent(results[2].replace(/\+/g, ' '));
}

/**
 * Check if URL query string has a specific parameter
 * @param name
 * @param url
 * @returns {boolean}
 */
function hasQueryParameter(name, url = window.location.href) {
	return getQueryParameter(name, url) !== null;
}

/**
 * Remove a parameter from a URL query string
 * @param name
 * @param url
 * @returns {string}
 */
function removeURLParameter(name, url = window.location.href) {
	/* prefer to use l.search if you have a location/link object */
	let urlParts = url.split('?');
	if (urlParts.length >= 2) {
		let prefix = encodeURIComponent(name) + '=';
		let pars = urlParts[1].split(/[&;]/g);
		
		/* reverse iteration as may be destructive */
		for (let i = pars.length; i-- > 0;) {
			/* idiom for string.startsWith */
			if (pars[i].lastIndexOf(prefix, 0) !== -1) {
				pars.splice(i, 1);
			}
		}
		
		return urlParts[0] + (pars.length > 0 ? '?' + pars.join('&') : '');
	}
	return url;
}

/**
 * Get a DOM element coordinates
 * @param el
 * @returns {{top: *, left: *, bottom: *, width, right: *, height}|null}
 */
function getElementCoords(el) {
	if (!isDomElement(el)) {
		return null;
	}
	
	const scrollY = window.scrollY;
	const scrollX = window.scrollX;
	const rect = el.getBoundingClientRect();
	
	return {
		top: rect.top + scrollY,
		right: rect.right + scrollX,
		bottom: rect.bottom + scrollY,
		left: rect.left + scrollX,
		width: rect.width,
		height: rect.height,
	};
}

/**
 * Extract error message
 * @param value
 * @param defaultMessage
 * @returns {*|null}
 */
function getErrorMessage(value, defaultMessage = null) {
	if (!isDefined(value)) {
		return defaultMessage;
	}
	
	let message = getErrorMessageFromXhr(value);
	if (isEmpty(message)) {
		message = getErrorMessageFromJson(value);
	}
	if (isEmpty(message)) {
		message = isString(value) ? value : null;
	}
	
	return !isEmpty(message) ? message : defaultMessage;
}

/**
 * Get error message from a XHR object
 * @param value
 * @param defaultMessage
 * @returns {*|null}
 */
function getErrorMessageFromXhr(value, defaultMessage = null) {
	let message = null;
	
	if (isDefined(value.responseJSON)) {
		message = getErrorMessageFromJson(value.responseJSON);
	}
	
	if (isEmpty(message)) {
		let responseText;
		if (isDefined(value.responseText)) {
			responseText = !isObject(value.responseText) ? JSON.parse(value.responseText) : value.responseText;
			message = getErrorMessageFromJson(responseText);
		}
	}
	
	return !isEmpty(message) ? message : defaultMessage;
}

/**
 * Get error message from a JSON object
 * @param value
 * @param defaultMessage
 * @returns {*|null}
 */
function getErrorMessageFromJson(value, defaultMessage = null) {
	if (!isObject(value)) {
		return defaultMessage;
	}
	
	let message = isDefined(value.message) ? value.message : null;
	if (isEmpty(message)) {
		message = isDefined(value.error) ? value.error : null;
	}
	
	message = isString(message) ? message : null;
	
	return !isEmpty(message) ? message : defaultMessage;
}

/**
 * Check if string is an email address
 * @param str
 * @returns {boolean}
 */
function isEmailAddress(str) {
	/* Regular expression to match email addresses */
	const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
	return emailPattern.test(str);
}

/**
 * Find all email addresses containing in a string
 * @param str
 * @returns {*|*[]}
 */
function findEmailAddresses(str) {
	/* Regular expression to match email addresses */
	const emailPattern = /[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/g;
	const matches = str.match(emailPattern);
	return matches || [];
}

/**
 * Resolves a selector, DOM element, or an array of selectors/DOM elements
 * into an array of DOM elements.
 *
 * This function accepts a string representing a CSS selector, a single DOM element,
 * or an array of such selectors/elements, including nested arrays. It returns an
 * array of the matched DOM elements. If an invalid input is provided, the function
 * will log a warning to the console and return an empty array.
 *
 * @param {string | Element | Array<string | Element | Array>} selectors - A CSS selector, DOM element,
 * or an array of selectors/DOM elements, including nested arrays.
 * @returns {Element[]} An array of DOM elements corresponding to the provided selectors.
 * If no matches are found or if invalid inputs are provided, an empty array is returned.
 *
 * @example
 * // Get elements by CSS selector
 * resolveDomElements('.example-class');
 *
 * @example
 * // Get a single DOM element
 * const element = document.querySelector('#example-id');
 * resolveDomElements(element);
 *
 * @example
 * // Get elements by an array of selectors
 * resolveDomElements(['.example-class', '#example-id']);
 *
 * @example
 * // Handle nested arrays of selectors
 * resolveDomElements(['.example-class', ['#another-id', element]]);
 */
function resolveDomElements(selectors) {
	if (!selectors || (Array.isArray(selectors) && selectors.length === 0)) {
		return [];
	}
	
	if (!Array.isArray(selectors)) {
		selectors = [selectors];
	}
	
	return selectors.flatMap(selector => {
		if (Array.isArray(selector)) {
			return resolveDomElements(selector); // Recursive call
		} else if (typeof selector === 'string') {
			return Array.from(document.querySelectorAll(selector));
		} else if (selector instanceof Element) {
			return [selector];
		} else {
			console.warn(`Invalid selector: "${selector}". Use a string, a DOM element, or an array of them.`);
			return [];
		}
	});
}

/**
 * Set one or more elements' visibility (by passing their selector in argument)
 * Note: @action can be: hide or show
 * @param action
 * @param selectors
 */
function setElementsVisibility(action, selectors) {
	const elements = resolveDomElements(selectors);
	if (elements.length <= 0) return;
	
	elements.forEach((element) => {
		if (action === 'show') {
			element.style.display = ''; /* Default to empty string to show element */
		} else if (action === 'hide') {
			element.style.display = 'none';
		} else {
			console.warn(`Invalid action: "${action}". Use 'show' or 'hide'.`);
		}
	});
}

/**
 * Toggle one or more elements' classes by adding/removing a specified class
 * Note: @action can be: add or remove
 * @param selectors
 * @param action
 * @param className
 */
function toggleElementsClass(selectors, action, className) {
	const elements = resolveDomElements(selectors);
	if (elements.length <= 0) return;
	
	elements.forEach(function (element) {
		if (action === 'add') {
			element.classList.add(className);
		} else if (action === 'remove') {
			element.classList.remove(className);
		} else {
			console.warn(`Invalid action: "${action}". Use 'add' or 'remove'.`);
		}
	});
}

/**
 * Initialize all tooltips in the DOM element (including the new one)
 *
 * Enable tooltips everywhere in the DOM element
 * Usage: initElementTooltips(domElement, {html: true});
 *        initElementTooltips(domElement, {trigger: 'hover'});
 *        initElementTooltips(domElement, {trigger: 'hover focus'});
 *
 * @param element
 * @param config
 * @param toggle
 */
function initElementTooltips(element, config = {}, toggle = 'tooltip') {
	if (!element) return;
	
	const tooltipTriggerList = [].slice.call(element.querySelectorAll(`[data-bs-toggle="${toggle}"]`));
	const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
		return new bootstrap.Tooltip(tooltipTriggerEl, config);
	});
}

/**
 * Initialize all popovers in the DOM element (including the new one)
 *
 * Enable popovers everywhere
 * Usage: initElementPopovers(domElement, {html: true});
 *
 * @param element
 * @param config
 * @param toggle
 */
function initElementPopovers(element, config = {}, toggle = 'popover') {
	if (!element) return;
	
	const popoverTriggerList = [].slice.call(element.querySelectorAll(`[data-bs-toggle="${toggle}"]`));
	const popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
		return new bootstrap.Popover(popoverTriggerEl, config);
	});
}

/**
 * Updates a select box with options from a JSON object and optionally sets a default selected option
 *
 * Example usage:
 * const options = {
 * 	"option1": "Option 1",
 * 	"option2": "Option 2",
 * 	"option3": "Option 3"
 * };
 *
 * Update the select box with ID 'mySelectBox' and set 'option2' as the default selected option
 * updateSelectOptions('#mySelectBox', options, 'option2');
 *
 * @param {string|Element} selectElement
 * @param {object} optionsJson
 * @param {null|string} defaultValue
 */
function updateSelectOptions(selectElement, optionsJson, defaultValue = null) {
	/* Get the select box element with selector */
	const selectBox = isString(selectElement) ? document.querySelector(selectElement) : selectElement;
	
	/* If the select box does not exist, log an error and exit the function */
	if (!isDomElement(selectBox)) {
		if (isString(selectElement)) {
			console.error(`Select box with selector "${selectElement}" not found.`);
		}
		return;
	}
	
	/* Clear the existing options */
	selectBox.innerHTML = '';
	
	/* Iterate through the JSON object and create option elements */
	for (const [value, text] of Object.entries(optionsJson)) {
		const option = document.createElement('option');
		option.value = value;
		option.text = text;
		
		/* If a default value is provided and matches the current option value, set it as selected */
		if (defaultValue !== null && value === defaultValue) {
			option.selected = true;
		}
		
		selectBox.appendChild(option);
	}
}

/**
 * Updates a Select2 select box with options from a JSON object and optionally sets a default selected option
 * Note: This is the select2 version of the updateSelectOptions() function
 *
 * @param {string|Element} selectElement
 * @param {object} optionsJson
 * @param {null|string} defaultValue
 */
function updateSelect2Options(selectElement, optionsJson, defaultValue = null) {
	if (typeof jQuery === 'undefined' || typeof $ === 'undefined') {
		console.error(`jQuery is not available.`);
		return;
	}
	
	/* Get the select box element with selector */
	let selectBox = (isString(selectElement) || isDomElement(selectElement))
		? $(selectElement)
		: selectElement;
	
	/* If the select box does not exist, log an error and exit the function */
	if (!isJQueryObject(selectBox)) {
		if (isString(selectElement)) {
			console.error(`Select box with selector "${selectElement}" not found.`);
		}
		return;
	}
	
	/* Clear the existing options */
	selectBox.empty();
	
	/* Iterate through the JSON object and create option elements */
	for (const [value, text] of Object.entries(optionsJson)) {
		if (value === defaultValue) {
			selectBox.append(`<option value="${value}" selected="selected">${text}</option>`);
		} else {
			selectBox.append(`<option value="${value}">${text}</option>`);
		}
	}
	
	/* Set the default value if provided */
	if (defaultValue !== null) {
		selectBox.val(defaultValue).trigger('change');
	}
}

/**
 * Convert associative JSON object to key:value object (for Select options for example)
 * @param jsonObject
 * @param {string} valueProperty
 * @param {string|null} keyProperty
 * @returns {{}}
 */
function assocObjectToKeyValue(jsonObject, valueProperty, keyProperty = null) {
	const newObject = {};
	
	for (const key in jsonObject) {
		if (jsonObject.hasOwnProperty(key)) {
			const newKey = !isEmpty(keyProperty) ? jsonObject[key][keyProperty] : key;
			newObject[newKey] = jsonObject[key][valueProperty];
		}
	}
	
	return newObject;
}
