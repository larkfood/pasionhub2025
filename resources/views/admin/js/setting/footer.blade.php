<script>
	onDocumentReady((event) => {
		let hidePoweredByEl = document.querySelector("input[type=checkbox][name=hide_powered_by]");
		if (hidePoweredByEl) {
			togglePoweredByFields(hidePoweredByEl);
			hidePoweredByEl.addEventListener("change", e => togglePoweredByFields(e.target));
		}
	});
	
	function togglePoweredByFields(hidePoweredByEl) {
		let action = !hidePoweredByEl.checked ? "show" : "hide";
		setElementsVisibility(action, ".powered-by-field");
	}
</script>
