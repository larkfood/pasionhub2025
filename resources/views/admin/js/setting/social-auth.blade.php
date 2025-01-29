<script>
	const checkboxElsSelector = "input[type=checkbox][data-social-network]";
	const socialNetworkElsSelector = `${checkboxElsSelector}:not([data-social-network=all])`;
	
	const facebookElSelector = "input[type=checkbox][data-social-network=facebook]";
	const linkedinElSelector = "input[type=checkbox][data-social-network=linkedin]";
	const twitterOauth1ElSelector = "input[type=checkbox][data-social-network=twitter-oauth-1]";
	const twitterOauth2ElSelector = "input[type=checkbox][data-social-network=twitter-oauth-2]";
	const googleElSelector = "input[type=checkbox][data-social-network=google]";
	const globalElSelector = "input[type=checkbox][data-social-network=all]";
	
	onDocumentReady((event) => {
		const facebookEl = document.querySelector(facebookElSelector);
		if (facebookEl) {
			applySocialNetworkActions(facebookEl);
			facebookEl.addEventListener("change", e => applySocialNetworkActions(e.target));
		}
		
		const linkedinEl = document.querySelector(linkedinElSelector);
		if (linkedinEl) {
			applySocialNetworkActions(linkedinEl);
			linkedinEl.addEventListener("change", e => applySocialNetworkActions(e.target));
		}
		
		const twitterOauth2El = document.querySelector(twitterOauth2ElSelector);
		if (twitterOauth2El) {
			applySocialNetworkActions(twitterOauth2El);
			twitterOauth2El.addEventListener("change", e => applySocialNetworkActions(e.target));
		}
		
		const twitterOauth1El = document.querySelector(twitterOauth1ElSelector);
		if (twitterOauth1El) {
			applySocialNetworkActions(twitterOauth1El);
			twitterOauth1El.addEventListener("change", e => applySocialNetworkActions(e.target));
		}
		
		const googleEl = document.querySelector(googleElSelector);
		if (googleEl) {
			applySocialNetworkActions(googleEl);
			googleEl.addEventListener("change", e => applySocialNetworkActions(e.target));
		}
		
		const globalEl = document.querySelector(globalElSelector);
		if (globalEl) {
			applyGlobalActions(globalEl);
			globalEl.addEventListener("change", e => applyGlobalActions(e.target));
		}
	});
	
	function applySocialNetworkActions(socialNetworkEl) {
		if (!socialNetworkEl) return;
		let socialNetwork = socialNetworkEl.dataset.socialNetwork;
		
		if (socialNetwork === 'twitter-oauth-2') {
			const twitterOauth1El = document.querySelector(twitterOauth1ElSelector);
			if (twitterOauth1El) {
				if (socialNetworkEl.checked) {
					if (twitterOauth1El.checked) {
						twitterOauth1El.checked = false;
						twitterOauth1El.dispatchEvent(new Event('change'));
					}
				}
			}
		}
		
		if (socialNetwork === 'twitter-oauth-1') {
			const twitterOauth2El = document.querySelector(twitterOauth2ElSelector);
			if (twitterOauth2El) {
				if (socialNetworkEl.checked) {
					if (twitterOauth2El.checked) {
						twitterOauth2El.checked = false;
						twitterOauth2El.dispatchEvent(new Event('change'));
					}
				}
			}
		}
		
		if (socialNetwork === 'all') {
			applyGlobalActions(socialNetworkEl);
			return;
		}
		
		toggleSocialNetworkFields(socialNetworkEl);
		
		// Handle the global checkbox when a social network is checked/unchecked
		const globalEl = document.querySelector(globalElSelector);
		if (globalEl) {
			// When one of social network is enabled, enable the global checkbox
			if (socialNetworkEl.checked) {
				if (!globalEl.checked) {
					globalEl.checked = true;
					globalEl.dispatchEvent(new Event('change'));
				}
			} else {
				// When one of social network is disabled,
				// disable also the global checkbox if all the other social network are disabled
				let socialNetworksAreDisabled = true;
				let socialNetworkEls = document.querySelectorAll(socialNetworkElsSelector);
				if (socialNetworkEls.length > 0) {
					socialNetworkEls.forEach(element => {
						if (element.checked) {
							socialNetworksAreDisabled = false;
						}
					});
					if (socialNetworksAreDisabled) {
						if (globalEl.checked) {
							globalEl.checked = false;
							globalEl.dispatchEvent(new Event('change'));
						}
					}
				}
			}
		}
	}
	
	function applyGlobalActions(socialNetworkEl) {
		if (!socialNetworkEl) return;
		
		let socialNetwork = socialNetworkEl.dataset.socialNetwork;
		if (socialNetwork !== 'all') return;
		
		let socialNetworkEls = document.querySelectorAll(socialNetworkElsSelector);
		if (socialNetworkEls.length > 0) {
			socialNetworkEls.forEach(element => {
				if (element.checked) {
					element.checked = socialNetworkEl.checked;
					element.dispatchEvent(new Event('change'));
				}
			});
		}
	}
	
	function applyChangeEventToElement(element) {
		if (!isDomElement(element)) return;
		/*
		 * Apply change event to the element
		 * 1. Create a new 'change' event. 2. Dispatch the event
		 */
		element.dispatchEvent(new Event('change'));
	}
	
	function toggleSocialNetworkFields(socialNetworkEl) {
		if (!socialNetworkEl) return;
		let socialNetwork = socialNetworkEl.dataset.socialNetwork;
		
		let action = socialNetworkEl.checked ? "show" : "hide";
		setElementsVisibility(action, `.${socialNetwork}`);
	}
</script>
