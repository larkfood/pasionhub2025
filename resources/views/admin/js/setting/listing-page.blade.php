<script>
	onDocumentReady((event) => {
		let showSecurityTipsEl = document.querySelector("input[type=checkbox][name=show_security_tips]");
		if (showSecurityTipsEl) {
			toggleSecurityTipsFields(showSecurityTipsEl);
			showSecurityTipsEl.addEventListener("change", e => toggleSecurityTipsFields(e.target));
		}
		
		let enableWhatsappBtnEl = document.querySelector("input[type=checkbox][name=enable_whatsapp_btn]");
		if (enableWhatsappBtnEl) {
			toggleWhatsappBtnFields(enableWhatsappBtnEl);
			enableWhatsappBtnEl.addEventListener("change", e => toggleWhatsappBtnFields(e.target));
		}
		
		let hideDateEl = document.querySelector("input[type=checkbox][name=hide_date]");
		if (hideDateEl) {
			toggleDateFields(hideDateEl);
			hideDateEl.addEventListener("change", e => toggleDateFields(e.target));
		}
		
		let similarListingsEl = document.querySelector("select[name=similar_listings].select2_from_array");
		if (similarListingsEl) {
			toggleSimilarListingsFields(similarListingsEl);
			$(similarListingsEl).on("change", e => toggleSimilarListingsFields(e.target));
		}
	});
	
	function toggleSecurityTipsFields(showSecurityTipsEl) {
		let action = !showSecurityTipsEl.checked ? "show" : "hide";
		setElementsVisibility(action, ".security-tips-field");
	}
	
	function toggleWhatsappBtnFields(enableWhatsappBtnEl) {
		let action = enableWhatsappBtnEl.checked ? "show" : "hide";
		setElementsVisibility(action, ".whatsapp-btn-field");
	}
	
	function toggleDateFields(hideDateEl) {
		let action = !hideDateEl.checked ? "show" : "hide";
		setElementsVisibility(action, ".date-field");
	}
	
	function toggleSimilarListingsFields(similarListingsEl) {
		setElementsVisibility("hide", ".similar-listings-field");
		if (similarListingsEl.value !== 0 && similarListingsEl.value !== '0') {
			setElementsVisibility("show", ".similar-listings-field");
		}
	}
</script>
