<script>
	onDocumentReady((event) => {
		let enableExtendedFormAreaEl = document.querySelector("input[type=checkbox][name=enable_extended_form_area]");
		if (enableExtendedFormAreaEl) {
			toggleExtendedFormAreaFields(enableExtendedFormAreaEl);
			enableExtendedFormAreaEl.addEventListener("change", e => toggleExtendedFormAreaFields(e.target));
		}
	});
	
	function toggleExtendedFormAreaFields(extFormEl) {
		let action = extFormEl.checked ? "show" : "hide";
		setElementsVisibility(action, ".extended");
	}
</script>
