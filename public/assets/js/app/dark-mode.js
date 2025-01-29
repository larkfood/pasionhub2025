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

if (typeof isSettingsAppDarkModeEnabled === 'undefined') {
	var isSettingsAppDarkModeEnabled = false;
}
if (typeof isDarkModeEnabledForCurrentUser === 'undefined') {
	var isDarkModeEnabledForCurrentUser = false;
}
if (typeof isDarkModeEnabledForCurrentDevice === 'undefined') {
	var isDarkModeEnabledForCurrentDevice = false;
}

onDocumentReady((event) => {
	
	/* Load the dark mode */
	loadDarkMode();
	
	/* Set or unset the dark mode */
	const themeSwitcherEl = document.querySelector('.theme-switcher');
	if (isDomElement(themeSwitcherEl)) {
		retrieveDarkModeButtonStatus(themeSwitcherEl);
		themeSwitcherEl.addEventListener('click', e => {
			e.preventDefault();
			
			setDarkMode(e.target);
		});
	}
	
});

/**
 * Check if dark mode is set in the DOM
 * @returns {boolean}
 */
function isDarkThemeEnabledInHtml() {
	const htmlEl = getHtmlElement();
	return (isSettingsAppDarkModeEnabled && htmlEl.getAttribute('theme') === 'dark');
}

/**
 * Check if the dark mode in not set in the DOM
 * @returns {boolean}
 */
function isDarkThemeNotEnabledInHtml() {
	const htmlEl = getHtmlElement();
	return (htmlEl.getAttribute('theme') !== 'dark');
}

/**
 * Set the dark mode for a given user in the Database
 * @param themeSwitcherEl
 */
function setDarkModeServer(themeSwitcherEl) {
	if (!isDomElement(themeSwitcherEl)) {
		return;
	}
	
	if (themeSwitcherEl.tagName.toLowerCase() === 'span') {
		themeSwitcherEl = themeSwitcherEl.parentElement;
	}
	
	let csrfToken = themeSwitcherEl.dataset.csrfToken;
	let requestUserId = themeSwitcherEl.dataset.userId;
	let requestDarkMode = isDarkThemeNotEnabledInHtml() ? 1 : 0;
	
	let url = siteUrl + '/ajax/user/dark-mode';
	let data = {
		'user_id': requestUserId,
		'dark_mode': requestDarkMode,
		'_token': csrfToken
	};
	
	httpRequest('post', url, data).then(json => {
		
		if (typeof json.darkMode === 'undefined') {
			jsAlert(langLayout.darkMode.error, 'error');
			return;
		}
		
		let message = (json.darkMode === 1) ? langLayout.darkMode.successSet : langLayout.darkMode.successDisabled;
		jsAlert(message, 'success');
		
	}).catch(error => jsAlert(error, 'error', false, true));
}

/**
 * Set the dark mode in the DOM
 * @param themeSwitcherEl
 */
function setDarkMode(themeSwitcherEl) {
	if (!isDomElement(themeSwitcherEl)) {
		return;
	}
	
	if (themeSwitcherEl.tagName.toLowerCase() === 'span') {
		themeSwitcherEl = themeSwitcherEl.parentElement;
	}
	
	setDarkModeServer(themeSwitcherEl);
	
	const htmlEl = getHtmlElement();
	const logoDarkEl = document.querySelector('.navbar-identity .navbar-brand .dark-logo');
	const logoLightEl = document.querySelector('.navbar-identity .navbar-brand .light-logo');
	
	if (!isDomElement(logoDarkEl) || !isDomElement(logoLightEl)) {
		return;
	}
	
	if (!isDarkThemeEnabledInHtml()) {
		htmlEl.setAttribute('theme', 'dark');
		htmlEl.dataset.bsTheme = 'dark';
		logoDarkEl.style.display = 'block';
		logoLightEl.style.display = 'none';
		
		themeSwitcherEl.classList.add('active');
	} else {
		htmlEl.setAttribute('theme', 'light');
		delete htmlEl.dataset.bsTheme;
		logoDarkEl.style.display = 'none';
		logoLightEl.style.display = 'block';
		
		themeSwitcherEl.classList.remove('active');
	}
}

/**
 * Load the dark mode
 */
function loadDarkMode() {
	const htmlEl = getHtmlElement();
	const logoDarkEl = document.querySelector('.navbar-identity .navbar-brand .dark-logo');
	const logoLightEl = document.querySelector('.navbar-identity .navbar-brand .light-logo');
	
	if (!isDomElement(logoDarkEl) || !isDomElement(logoLightEl)) {
		return;
	}
	
	if (isDarkModeEnabledForCurrentDevice) {
		htmlEl.setAttribute('theme', 'dark');
		htmlEl.dataset.bsTheme = 'dark';
		
		logoDarkEl.style.display = 'block';
		logoLightEl.style.display = 'none';
	} else {
		htmlEl.setAttribute('theme', 'light');
		delete htmlEl.dataset.bsTheme;
		
		logoDarkEl.style.display = 'none';
		logoLightEl.style.display = 'block';
	}
}

/**
 * Retrieve the dark mode button status
 * @param themeSwitcherEl
 */
function retrieveDarkModeButtonStatus(themeSwitcherEl) {
	if (!isDomElement(themeSwitcherEl)) {
		return;
	}
	
	if (isDarkModeEnabledForCurrentDevice) {
		themeSwitcherEl.classList.add('active');
	} else {
		themeSwitcherEl.classList.remove('active');
	}
}
