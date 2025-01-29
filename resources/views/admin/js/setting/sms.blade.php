<script>
	let smsDriversSelectors = {!! $smsDriversSelectorsJson !!};
	let smsDriversSelectorsList = Object.values(smsDriversSelectors);
	
	const smsToActivated = (smsToValue) => `{{ trans('admin.sms_to_activated') }}`;
	const smsToAdminActivated = `{{ trans('admin.sms_to_admin_activated') }}`;
	const smsToDisabled = `{{ trans('admin.sms_to_disabled') }}`;
	
	const alwaysToElSelector = "input[name=sms_to]";
	
	onDocumentReady(function(event) {
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
		
		let smsToEl = document.querySelector(alwaysToElSelector);
		if (smsToEl) {
			smsToEl.addEventListener("blur", e => applyDriverTestChanges(driverTestEl, e.type));
		}
		
		let phoneAsAuthFieldEl = document.querySelector("input[type=checkbox][name=enable_phone_as_auth_field]");
		enablePhoneNumberAsAuthField(phoneAsAuthFieldEl);
		if (phoneAsAuthFieldEl) {
			phoneAsAuthFieldEl.addEventListener("change", e => enablePhoneNumberAsAuthField(e.target));
		}
	});
	
	function getDriverFields(driverEl) {
		const selectedDriverSelector = smsDriversSelectors[driverEl.value] ?? "";
		const driversSelectorsListToHide = smsDriversSelectorsList.filter(item => item !== selectedDriverSelector);
		
		setElementsVisibility("hide", driversSelectorsListToHide);
		setElementsVisibility("show", selectedDriverSelector);
	}
	
	function applyDriverTestChanges(driverTestEl, eventType) {
		if (!driverTestEl) return;
		
		let smsToEl = document.querySelector(alwaysToElSelector);
		if (!smsToEl) return;
		
		let driverTestElSelector = ".driver-test";
		
		if (driverTestEl.checked) {
			setElementsVisibility("show", driverTestElSelector);
			
			if (eventType !== "DOMContentLoaded") {
				const smsToValue = smsToEl.value;
				if (smsToValue !== "") {
					pnAlert(smsToActivated(smsToValue), "info");
				} else {
					pnAlert(smsToAdminActivated, "info");
				}
			}
		}
		if (!driverTestEl.checked) {
			setElementsVisibility("hide", driverTestElSelector);
			
			if (eventType !== "DOMContentLoaded") {
				pnAlert(smsToDisabled, "info");
			}
		}
	}
	
	function enablePhoneNumberAsAuthField(phoneAsAuthFieldEl) {
		if (!phoneAsAuthFieldEl) return;
		
		let action = phoneAsAuthFieldEl.checked ? "show" : "hide";
		setElementsVisibility(action, ".auth-field-el");
	}
	
	function setDefaultAuthField(defaultValue = "email") {
		let defaultAuthFieldEl = document.querySelector("select[name=default_auth_field]");
		if (defaultAuthFieldEl) {
			defaultAuthFieldEl.value = defaultValue;
		}
	}
</script>
