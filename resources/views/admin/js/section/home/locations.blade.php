<script>
	onDocumentReady((event) => {
		let showCitiesEl = document.querySelector("input[type=checkbox][name=show_cities]");
		if (showCitiesEl) {
			toggleShowCitiesElFields(showCitiesEl);
			showCitiesEl.addEventListener("change", e => toggleShowCitiesElFields(e.target));
		}
		
		let showMapEl = document.querySelector("input[type=checkbox][name=enable_map]");
		if (showMapEl) {
			toggleShowMapElFields(showMapEl);
			showMapEl.addEventListener("change", e => toggleShowMapElFields(e.target));
		}
	});
	
	function toggleShowCitiesElFields(showCitiesEl) {
		let action = showCitiesEl.checked ? "show" : "hide";
		setElementsVisibility(action, ".cities-field");
	}
	
	function toggleShowMapElFields(showMapEl) {
		let action = showMapEl.checked ? "show" : "hide";
		setElementsVisibility(action, ".map-field");
	}
</script>
