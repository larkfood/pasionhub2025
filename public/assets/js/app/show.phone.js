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

if (typeof showSecurityTips === 'undefined') {
	var showSecurityTips = '0';
}

onDocumentReady((event) => {
	
	const phoneBlockEls = document.querySelectorAll('.phoneBlock');
	if (phoneBlockEls.length > 0) {
		phoneBlockEls.forEach((element) => {
			element.addEventListener('click', (e) => {
				e.preventDefault(); /* Prevents submission or reloading */
				
				return showPhone(e.target, showSecurityTips);
			});
		});
	}
	
});

/**
 * Show the Contact's phone
 * @param el
 * @param showSecurityTips
 * @returns {Promise<boolean>}
 */
async function showPhone(el, showSecurityTips) {
	if (el.tagName.toLowerCase() === 'i') {
		el = el.parentElement;
	}
	
	let securityTipsEl;
	let modalEl;
	const phoneModalEl = document.getElementById('phoneModal');
	const phoneModalLinkEl = document.getElementById('phoneModalLink');
	
	const postId = el.dataset.postId ?? 0;
	
	// When cache is true, the postId is updated to 0 after the first HTTP request
	// to prevent any other one, since the modal can be show with the updated DOM
	const resultCanBeCached = true;
	
	// Use the cache and open the modal without making an HTTP request
	if (resultCanBeCached) {
		if (postId === 0 || postId === '0' || postId === '') {
			if (showSecurityTips === 1 || showSecurityTips === '1') {
				return false;
			}
			
			// Lunch a call (if link stars with "tel:")
			const link = el.getAttribute('href');
			if (link.startsWith('tel:')) {
				window.location.href = link;
			}
			
			return false;
		}
	}
	
	const iconEl = el.querySelector('i');
	
	let url = siteUrl + '/ajax/post/phone';
	let _tokenEl = document.querySelector('input[name=_token]');
	let data = {
		'post_id': postId,
		'_token': _tokenEl.value ?? null
	};
	
	try {
		if (showSecurityTips === 1 || showSecurityTips === '1') {
			phoneModalEl.innerHTML = '<i class="spinner-border text-primary"></i>';
		} else {
			/* Change the button indicator */
			if (iconEl) {
				iconEl.classList.remove('fa-solid', 'fa-mobile-screen-button');
				iconEl.classList.add('spinner-border', 'spinner-border-sm');
				iconEl.style.verticalAlign = 'middle';
				iconEl.setAttribute('role', 'status');
				iconEl.setAttribute('aria-hidden', 'true');
			}
		}
		
		const json = await httpRequest('POST', url, data);
		
		if (typeof json.phoneModal === 'undefined' || typeof json.phone === 'undefined') {
			return false;
		}
		
		if (showSecurityTips === 1 || showSecurityTips === '1') {
			phoneModalEl.innerHTML = json.phoneModal;
			phoneModalLinkEl.setAttribute('href', json.link);
			
			securityTipsEl = document.getElementById('securityTips');
			if (securityTipsEl && !securityTipsEl.classList.contains('show')) {
				modalEl = new bootstrap.Modal(securityTipsEl);
				modalEl.show();
			}
		} else {
			el.innerHTML = '<i class="fa-solid fa-mobile-screen-button"></i> ' + json.phone;
			el.setAttribute('href', json.link);
			
			/* Disable Tooltip */
			let tooltip = bootstrap.Tooltip.getInstance(el);
			if (tooltip) {
				tooltip.dispose();
			}
		}
		
		// If cache is activated, update the postId to 0
		if (resultCanBeCached) {
			el.dataset.postId = '0';
		}
		
	} catch (error) {
		let message = getErrorMessage(error);
		if (message !== null) {
			jsAlert(message, 'error');
		}
		
		if (showSecurityTips !== 1 && showSecurityTips !== '1') {
			/* Reset the button indicator */
			if (iconEl) {
				iconEl.classList.remove('spinner-border', 'spinner-border-sm');
				iconEl.style.verticalAlign = '';
				iconEl.classList.add('fa-solid', 'fa-mobile-screen-button');
				iconEl.removeAttribute('role');
				iconEl.removeAttribute('aria-hidden');
			}
		}
	}
	
	return false;
}
