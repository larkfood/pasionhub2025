<script>
	onDocumentReady((event) => {
		let messengerEl = document.querySelector("input[type=checkbox][name=messenger]");
		if (messengerEl) {
			toggleMessengerFields(messengerEl);
			messengerEl.addEventListener("change", e => toggleMessengerFields(e.target));
		}
	});
	
	function toggleMessengerFields(extendedSearchesEl) {
		let action = extendedSearchesEl.checked ? "show" : "hide";
		setElementsVisibility(action, ".messenger");
	}
</script>
