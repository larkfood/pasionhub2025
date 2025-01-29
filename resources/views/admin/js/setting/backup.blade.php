<script>
	onDocumentReady((event) => {
		let takingBackupEl = document.querySelector("select[name=taking_backup].select2_from_array");
		if (takingBackupEl) {
			getTakingBackupFields(takingBackupEl);
			$(takingBackupEl).on("change", e => getTakingBackupFields(e.target));
		}
	});
	
	function getTakingBackupFields(displayModeEl) {
		setElementsVisibility("hide", ".taking-backup-field");
		if (displayModeEl.value !== "none") {
			setElementsVisibility("show", ".taking-backup-field");
		}
	}
</script>
