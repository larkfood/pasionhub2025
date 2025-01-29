<script>
	onDocumentReady((event) => {
		let catDisplayTypeEl = document.querySelector("select[name=cat_display_type].select2_from_array");
		if (catDisplayTypeEl) {
			getCatDisplayTypeFields(catDisplayTypeEl);
			$(catDisplayTypeEl).on("change", e => getCatDisplayTypeFields(e.target));
		}
	});
	
	function getCatDisplayTypeFields(catDisplayTypeEl) {
		let catDisplayTypeElValue = catDisplayTypeEl.value;
		
		setElementsVisibility("hide", ".normal-type, .nested-type");
		
		if (
			catDisplayTypeElValue === "c_normal_list"
			|| catDisplayTypeElValue === "c_border_list"
			|| catDisplayTypeElValue === "c_bigIcon_list"
			|| catDisplayTypeElValue === "c_picture_list"
		) {
			setElementsVisibility("show", ".normal-type");
		}
		if (catDisplayTypeElValue === "cc_normal_list" || catDisplayTypeElValue === "cc_normal_list_s") {
			setElementsVisibility("show", ".nested-type");
		}
	}
</script>
