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

preventPageLoadingInIframe();

onDocumentReady((event) => {
	/* Confirm Actions Links */
	$(document).on('click', '.confirm-simple-action', function(e) {
		e.preventDefault(); /* Prevents submission or reloading */
		
		try {
			let showCancelInfo = false;
			if (isAdminPanel) {
				if (isDemoDomain()) {
					return false;
				}
				showCancelInfo = true;
			}
			
			confirmSimpleAction(this, showCancelInfo);
		} catch (e) {
			jsAlert(e, 'error', false);
		}
	});
});

/**
 * Open Login Modal
 */
function openLoginModal() {
	const quickLoginEl = document.getElementById('quickLogin');
	if (quickLoginEl) {
		const loginModal = new bootstrap.Modal(quickLoginEl, {});
		loginModal.show();
	}
}

/**
 * Confirm Simple Action (Links or forms without AJAX)
 * Usage: Add 'confirm-simple-action' in the element class attribute
 *
 * @param clickedEl
 * @param showCancelInfo
 * @param cancelInfoAutoDismiss
 * @returns {boolean}
 */
function confirmSimpleAction(clickedEl, showCancelInfo = true, cancelInfoAutoDismiss = true) {
	if (typeof Swal === 'undefined') {
		return false;
	}
	
	Swal.fire({
		text: langLayout.confirm.message.question,
		icon: 'warning',
		showCancelButton: true,
		confirmButtonText: langLayout.confirm.button.yes,
		cancelButtonText: langLayout.confirm.button.no
	}).then((result) => {
		if (result.isConfirmed) {
			
			try {
				if ($(clickedEl).is('a')) {
					let actionUrl = $(clickedEl).attr('href');
					if (actionUrl !== 'undefined') {
						console.log(actionUrl);
						redirect(actionUrl);
					}
				} else {
					let actionForm = $(clickedEl).parents('form:first');
					$(actionForm).submit();
				}
			} catch (e) {
				console.log(e);
			}
			
		} else if (result.dismiss === Swal.DismissReason.cancel) {
			if (showCancelInfo === true) {
				jsAlert(langLayout.confirm.message.cancel, 'info', cancelInfoAutoDismiss);
			}
		}
	});
	
	return false;
}

/**
 * Show JS Alert Messages (Swal)
 * @param message
 * @param type
 * @param cancelAlertAutoDismiss
 * @param reloadPageIfConfirmed
 * @param blockUi
 * @returns {boolean}
 */
function jsAlert(message, type = 'info', cancelAlertAutoDismiss = true, reloadPageIfConfirmed = false, blockUi = false) {
	if (typeof Swal === 'undefined') {
		return false;
	}
	
	let alertParams = {
		html: message,
		icon: type,
		position: 'center'
	};
	
	if (cancelAlertAutoDismiss === true) {
		alertParams.showCancelButton = false;
		alertParams.showConfirmButton = false;
		alertParams.timer = 3000;
	} else {
		alertParams.showCancelButton = true;
		if (reloadPageIfConfirmed === true) {
			alertParams.confirmButtonText = langLayout.refresh;
		} else {
			alertParams.confirmButtonText = langLayout.confirm.button.ok;
			alertParams.cancelButtonText = langLayout.confirm.button.cancel;
		}
	}
	if (blockUi) {
		alertParams.showCancelButton = false;
		alertParams.allowOutsideClick = false;
		alertParams.allowEscapeKey = false;
	}
	
	let alertObj = Swal.fire(alertParams);
	
	if (reloadPageIfConfirmed === true) {
		alertObj.then((result) => {
			if (result.isConfirmed) {
				/* Reload Page */
				/* JS 1.1 - Does not create a history entry */
				window.location.replace(window.location.pathname + window.location.search + window.location.hash);
				
				/* JS 1.0 - Creates a history entry */
				window.location.href = window.location.pathname + window.location.search + window.location.hash;
			}
		});
	}
}

/**
 * Show JS Alert Messages (PNotify)
 * PNotify: https://github.com/sciactive/pnotify
 *
 * @param message
 * @param type
 * @param icon
 * @returns {boolean}
 */
function pnAlert(message, type = 'notice', icon = null) {
	if (typeof PNotify === 'undefined') {
		return false;
	}
	
	if (type === 'warning') {
		type = 'notice';
	}
	
	if (typeof window.stackTopRight === 'undefined') {
		window.stackTopRight = new PNotify.Stack({
			dir1: 'down',
			dir2: 'left',
			firstpos1: 25,
			firstpos2: 25,
			spacing1: 10,
			spacing2: 25,
			modal: false,
			maxOpen: Infinity
		});
	}
	let alertParams = {
		text: message,
		type: type,
		stack: window.stackTopRight
	};
	if (icon !== null) {
		alertParams.icon = icon;
	}
	
	new PNotify.alert(alertParams);
}

/**
 * Show JS Alert Messages (Bootstrap Modal)
 * Note: Need to create an empty modal HTML code in the pages layout
 *
 * @param error
 * @param errorTitle
 * @returns {boolean}
 */
function bsModalAlert(error, errorTitle = null) {
	let message = getErrorMessage(error);
	let title = !isEmpty(errorTitle) ? errorTitle : null;
	
	if (isEmpty(message)) {
		return false;
	}
	
	const modalEl = document.getElementById("errorModal");
	const modalTitleEl = document.getElementById("errorModalTitle");
	const modalBodyEl = document.getElementById("errorModalBody");
	
	if (!isDomElement(modalEl) || !isDomElement(modalTitleEl) || !isDomElement(modalBodyEl)) {
		return false;
	}
	
	/* Set up the Modal */
	if (!isEmpty(title)) {
		modalTitleEl.innerHTML = title;
	}
	message = '<code>' + message + '</code>';
	modalBodyEl.innerHTML = message;
	
	/* Open the Modal */
	const myModal = new bootstrap.Modal(modalEl, {});
	myModal.show();
}

/**
 * Disable the field's Tooltip (Need to be hidden first)
 * @param tooltipTriggerEl
 */
function disableTooltipForElement(tooltipTriggerEl)
{
	if (isElDefined(tooltipTriggerEl)) {
		const tooltip = new bootstrap.Tooltip(tooltipTriggerEl);
		tooltip.hide();
		tooltip.disable();
	}
}

/**
 * Enable the field's Tooltip
 * @param tooltipTriggerEl
 */
function enableTooltipForElement(tooltipTriggerEl)
{
	if (isElDefined(tooltipTriggerEl)) {
		const tooltip = new bootstrap.Tooltip(tooltipTriggerEl);
		tooltip.enable();
	}
}

/**
 * Check user is on demo domain
 * @returns {boolean}
 */
function isDemoDomain() {
	try {
		if (demoMode) {
			jsAlert(demoMessage, 'error');
			
			return true;
		}
	} catch (e) {
		jsAlert(e, 'error', false);
		
		return true;
	}
	
	return false;
}
