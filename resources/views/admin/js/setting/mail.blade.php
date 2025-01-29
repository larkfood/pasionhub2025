<script>
	let mailDriversSelectors = {!! $mailDriversSelectorsJson !!};
	let mailDriversSelectorsList = Object.values(mailDriversSelectors);
	
	const emailAlwaysToActivated = (alwaysToValue) =>`{{ trans('admin.email_always_to_activated') }}`;
	const emailToAdminActivated = `{{ trans('admin.email_to_admin_activated') }}`;
	const emailAlwaysToDisabled = `{{ trans('admin.email_always_to_disabled') }}`;
	
	const alwaysToElSelector = "input[name=email_always_to]";
	
	onDocumentReady((event) => {
		let driverEl = document.querySelector("select[name=driver].select2_from_array");
		if (driverEl) {
			getDriverFields(driverEl);
			$(driverEl).on("change", e => getDriverFields(e.target));
		}
		
		let driverTestEl = document.querySelector("input[type=checkbox][name=driver_test]");
		if (driverTestEl) {
			applyDriverTestChanges(driverTestEl, event.type);
			driverTestEl.addEventListener("change", e => applyDriverTestChanges(e.target, e.type));
		}
		
		let alwaysToEl = document.querySelector(alwaysToElSelector);
		if (alwaysToEl) {
			alwaysToEl.addEventListener("blur", e => applyDriverTestChanges(driverTestEl, e.type));
		}
	});
	
	function getDriverFields(driverEl) {
		const selectedDriverSelector = mailDriversSelectors[driverEl.value] ?? "";
		const driversSelectorsListToHide = mailDriversSelectorsList.filter(item => item !== selectedDriverSelector);
		
		setElementsVisibility("hide", driversSelectorsListToHide);
		setElementsVisibility("show", selectedDriverSelector);
	}
	
	function applyDriverTestChanges(driverTestEl, eventType) {
		if (!driverTestEl) return;
		
		let alwaysToEl = document.querySelector(alwaysToElSelector);
		if (!alwaysToEl) return;
		
		let driverTestElSelector = ".driver-test";
		
		if (driverTestEl.checked) {
			setElementsVisibility("show", driverTestElSelector);
			
			if (eventType !== "DOMContentLoaded") {
				const alwaysToValue = alwaysToEl.value;
				if (alwaysToValue !== "" && isEmailAddress(alwaysToValue)) {
					pnAlert(emailAlwaysToActivated(alwaysToValue), "notice");
				} else {
					pnAlert(emailToAdminActivated, "info");
				}
			}
		}
		if (!driverTestEl.checked) {
			setElementsVisibility("hide", driverTestElSelector);
			
			if (eventType !== "DOMContentLoaded") {
				pnAlert(emailAlwaysToDisabled, "info");
			}
		}
	}
</script>
