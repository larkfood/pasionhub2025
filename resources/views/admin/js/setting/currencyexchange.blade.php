<script>
	let currencyexchangeDriversSelectors = {!! $currencyexchangeDriversSelectorsJson !!};
	let currencyexchangeDriversSelectorsList = Object.values(currencyexchangeDriversSelectors);
	
	onDocumentReady((event) => {
		let activationEl = document.querySelector("input[type=checkbox][name=activation]");
		if (activationEl) {
			toggleExchangeFields(activationEl);
			activationEl.addEventListener("change", e => toggleExchangeFields(e.target));
		}
		
		let driverEl = document.querySelector("select[name=driver].select2_from_array");
		if (driverEl) {
			getDriverFields(driverEl);
			$(driverEl).on("change", e => getDriverFields(e.target));
		}
	});
	
	function toggleExchangeFields(activationEl) {
		let action = activationEl.checked ? "show" : "hide";
		setElementsVisibility(action, ".ex-enabled");
		
		if (activationEl.checked) {
			let driverEl = document.querySelector("select[name=driver].select2_from_array");
			if (driverEl) {
				getDriverFields(driverEl);
			}
		}
	}
	
	function getDriverFields(driverEl) {
		const selectedDriverSelector = currencyexchangeDriversSelectors[driverEl.value] ?? "";
		const driversSelectorsListToHide = currencyexchangeDriversSelectorsList.filter(item => item !== selectedDriverSelector);
		
		setElementsVisibility("hide", driversSelectorsListToHide);
		setElementsVisibility("show", selectedDriverSelector);
	}
</script>
