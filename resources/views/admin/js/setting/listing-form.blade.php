<script>
	onDocumentReady((event) => {
		let utf8mb4EnabledEl = document.querySelector("input[type=checkbox][name=utf8mb4_enabled]");
		if (utf8mb4EnabledEl) {
			toggle4ByteCharsFields(utf8mb4EnabledEl);
			utf8mb4EnabledEl.addEventListener("change", e => toggle4ByteCharsFields(e.target));
		}
	});
	
	function toggle4ByteCharsFields(utf8mb4EnabledEl) {
		let action = utf8mb4EnabledEl.checked ? "show" : "hide";
		setElementsVisibility(action, ".utf8mb4-field");
	}
</script>
