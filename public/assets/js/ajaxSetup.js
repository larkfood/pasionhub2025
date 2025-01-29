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

onDocumentReady((event) => {
	
	let xhrOptions = {
		headers: {
			'X-Requested-With': 'XMLHttpRequest',
		},
		async: true,
		cache: true,
		xhrFields: {withCredentials: true},
		crossDomain: true
	};
	
	/* Ajax's calls should always have the CSRF token attached to them; otherwise they won't work */
	const metaTokenEl = document.querySelector('meta[name="csrf-token"]');
	if (metaTokenEl) {
		xhrOptions.headers['X-CSRF-TOKEN'] = metaTokenEl.getAttribute('content');
	}
	
	$.ajaxSetup(xhrOptions);
	
});
