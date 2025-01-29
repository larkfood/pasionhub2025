<script>
	let geoipDriversSelectors = {!! $geoipDriversSelectorsJson !!};
	let geoipDriversSelectorsList = Object.values(geoipDriversSelectors);
	
	const activatingGeolocation = `{{ trans('admin.activating_geolocation') }}`;
	const disablingGeolocation = `{{ trans('admin.disabling_geolocation') }}`;
	const specifyingDefaultCountry = `{{ trans('admin.specifying_default_country') }}`;
	const removingDefaultCountry = `{{ trans('admin.removing_default_country') }}`;
	
	const geoipActivationElSelector = "input[type=checkbox][name=geoip_activation]";
	const defaultCountryElSelector = "select[name=default_country_code].select2_field";
	
	onDocumentReady((event) => {
		let driverEl = document.querySelector("select[name=geoip_driver].select2_from_array");
		if (driverEl) {
			getDriverFields(driverEl);
			$(driverEl).on("change", e => getDriverFields(e.target));
		}
		
		let geoipActivationEl = document.querySelector(geoipActivationElSelector);
		if (geoipActivationEl) {
			geoipActivationEl.addEventListener("change", e => unsetDefaultCountry(e.target));
		}
		
		$(defaultCountryElSelector).on("change", e => toggleGeolocation(e.target));
	});
	
	function getDriverFields(driverEl) {
		const selectedDriverSelector = geoipDriversSelectors[driverEl.value] ?? "";
		const driversSelectorsListToHide = geoipDriversSelectorsList.filter(item => item !== selectedDriverSelector);
		
		setElementsVisibility("hide", driversSelectorsListToHide);
		setElementsVisibility("show", selectedDriverSelector);
	}
	
	function unsetDefaultCountry(geoipActivationEl) {
		if (!geoipActivationEl) return;
		
		let defaultCountryEl = document.querySelector(defaultCountryElSelector);
		if (!defaultCountryEl) return;
		
		if (geoipActivationEl.checked) {
			defaultCountryEl.value = "";
			/*
			 * Trigger Change event when the Input value changed programmatically (for select2)
			 * https://stackoverflow.com/a/36084475
			 */
			defaultCountryEl.dispatchEvent(new Event("change"));
			
			pnAlert(activatingGeolocation, "info");
		} else {
			/* Focus on the Default Country field */
			defaultCountryEl.focus();
			
			pnAlert(disablingGeolocation, "notice");
		}
	}
	
	function toggleGeolocation(defaultCountryEl) {
		if (!defaultCountryEl) return;
		
		let geoipActivationEl = document.querySelector(geoipActivationElSelector);
		if (!geoipActivationEl) return;
		
		if (geoipActivationEl.checked && defaultCountryEl.value !== "") {
			geoipActivationEl.checked = false;
			
			pnAlert(specifyingDefaultCountry, "info");
		}
		if (!geoipActivationEl.checked && defaultCountryEl.value === "") {
			geoipActivationEl.checked = true;
			
			pnAlert(removingDefaultCountry, "notice");
		}
	}
</script>
